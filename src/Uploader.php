<?php
namespace SurprisePhp\Oss;

use mysql_xdevapi\Exception;
use Phalcon\Mvc\User\Component;
use SurprisePhp\Oss\contract\OssInterface;
use Uploader\Helpers\Format;
use Phalcon\Http\Request;

/**
 * Uploader executable class
 *
 * @package   Uploader
 * @since     PHP >=5.4
 * @version   1.0
 * @author    Stanislav WEB | Lugansk <stanisov@gmail.com>
 * @copyright Stanislav WEB
 */
class Uploader extends Component {

    /**
     * Request
     *
     * @var \Phalcon\Http\Request $rules
     */
    private $request;

    /**
     * File
     *
     * @var \Phalcon\Http\Request\File $files
     */
    private $files;

    /**
     * Validation Rules
     *
     * @var array $rules
     */
    private $rules  = [];

    /**
     * Uploaded files array
     *
     * @var array $info
     */
    private $info;

    /**
     * Validator
     *
     * @var \Uploader\Validator
     */
    private $validator;

    /**
     * @var object
     */
    private $ossClient;

    /**
     * @var string
     */
    private $domain;

    /**
     * Initialize rules
     *
     * @param array $rules
     */
    public function __construct($rules = [], OssInterface $ossClient)
    {
        if (empty($rules) === false) {
            $this->setRules($rules);
        }

        // get validator
        $this->validator = new Validator();
        
        $this->request = new Request();
        
        $this->ossClient = $ossClient;
        
        $this->domain = $this->getDI()->get('config')->get('attach')['domain'];
    }

    /**
     * Setting up rules for uploaded files
     *
     * @param array $rules
     * @return Uploader
     */
    public function setRules(array $rules)
    {
        foreach ($rules as $key => $values) {

            if ((is_array($values) === true && empty($values) === false) || is_callable($values)) {
                $this->rules[$key] = $values;
            } else {
                $this->rules[$key] = trim($values);
            }
        }
        return $this;
    }

    /**
     * Check if upload files are valid
     *
     * @return bool
     */
    public function isValid()
    {
        // get files for upload
        $this->files = $this->request->getUploadedFiles();
        if (sizeof($this->files) > 0) {

            // do any actions if files exists

            foreach ($this->files as $n => $file) {

                // apply all the validation rules for each file
                foreach ($this->rules as $key => $rule) {

                    if (method_exists($this->validator, 'check' . ucfirst($key)) === true) {
                        $this->validator->{'check' . ucfirst($key)}($file, $rule);
                    }
                }
            }
        }

        $errors = $this->getErrors();

        return (empty($errors) === true) ? true : false;
    }

    /**
     * Check if upload files are valid
     *
     * @return array
     */
    public function upload()
    {
        $filePathItem = [];
        $checkResult = $this->isValid();
        if($checkResult === false) {
            throw new Exception(end($this->getErrors()));
        } else {
            // do any actions if files exists
            foreach ($this->files as $n => $file) {

                $filename = $file->getName();

                if (isset($this->rules['hash']) === true) {
                    if (empty($this->rules['hash']) === true) {
                        $this->rules['hash'] = 'md5';
                    }

                    if (!is_string($this->rules['hash']) === true) {
                        $filename = call_user_func($this->rules['hash']) . '.' . $file->getExtension();
                    } else {
                        $filename = $this->rules['hash']($filename) . '.' . $file->getExtension();
                    }
                }

                if (isset($this->rules['sanitize']) === true) {
                    $filename = Format::toLatin($filename, '', true);
                }

                $filePathItem[] = $this->ossClient->uploadFile($file, $filename);
            }
        }

        return $filePathItem;
    }

    /**
     * @param $width
     * @param $height
     * @return array
     */
    public function resizeImg($width, $height)
    {
        $resizePathItem = [];

        $filePathItem = $this->upload();
        foreach($filePathItem as $path) {
            $resizePathItem['normal'] = $path;
            $resizePathItem['resize'] = sprintf("%s?x-oss-process=image/resize,m_fixed,h_%s,w_%s", $path, $width, $height);
        }
        
        return $resizePathItem;
    }

    /**
     * 获取路径
     */
    public function getAttachUrl($attachList)
    {
        if (is_string($attachList)) {
            $attachList = str_replace('//', '/', $attachList);
            $attachList = $this->domain . $attachList;
            return $attachList;
        } elseif (is_array($attachList)) {
            foreach ($attachList as $key => $value) {
                $attachList[$key] = $this->getAttachUrl($value);
            }
        }

        return $attachList;
    }

    /**
     * @param $attachPath
     * @param $width
     * @param $height
     */
    public function getResizeFilePath($attachPath, $width, $height)
    {
        if(is_numeric($width) && is_numeric($width) && $width > 0 && $height > 0) {
            $resizePath = sprintf("%s?x-oss-process=image/resize,m_fixed,h_%s,w_%s", $attachPath, $width, $height);
            return $resizePath;
        } else {
            throw new \InvalidArgumentException("params is wrong");
        }
    }

    /**
     * Return errors messages
     *
     * @return array
     */
    public function getErrors()
    {
        // error container
        return $this->validator->errors;
    }

    /**
     * Get uploaded files info
     *
     * @return \Phalcon\Session\Adapter\Files
     */
    public function getInfo()
    {
        // error container
        return $this->info;
    }

    /**
     * Truncate uploaded files
     */
    public function truncate()
    {
        if (empty($this->info) === false) {
            foreach ($this->info as $n => $file) {
                if (file_exists($file['path'])) {
                    unlink($file['path']);
                }
            }
        }
    }
}
