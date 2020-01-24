<?php

namespace SurprisePhp\Oss\OssClient;

use OSS\Core\OssException;
use OSS\OssClient;
use Phalcon\Di;
use Phalcon\Http\Request\File;
use Phalcon\Mvc\User\Component;
use SurprisePhp\Oss\Contract\OssInterface;

class Alibaba extends Component implements OssInterface
{
    private static $sdkClient;

    private static $clientConfig;

    public static function getSdkClient()
    {
        if (isset(static::$sdkClient)) {
            return static::$sdkClient;
        } else {
            /**
             * @var $di Di
             */
            $di = Di::getDefault();
            if($di->has('config') == false) {
                throw new \InvalidArgumentException('config serivce is lost');
            } else {
                $ossClientConfig = $di->get('config')->get('ossClient');
                if(empty($ossClientConfig) || !isset($ossClientConfig['bucket'])) {
                    throw new \InvalidArgumentException("ossClient config is lost");
                } else {
                    try {
                        static::$clientConfig = $ossClientConfig;
                        static::$sdkClient = new OssClient($ossClientConfig->get('accessKeyId'),
                            $ossClientConfig->get('accessKeySecret'),
                            $ossClientConfig->get('endPoint'));
                        return static::$sdkClient;
                    } catch (OssException $e) {
                        $msg = $e->getMessage();
                        throw new \InvalidArgumentException($msg);
                    }
                }
            }
        }
    }

    /**
     * @param File $file
     * @param $newFileName
     * @return mixed
     * @throws \Exception
     */
    public function uploadFile(File $file, $newFileName)
    {
        try {
            /**
             * @var $sdkClient OssClient
             */
            $sdkClient = self::getSdkClient();
            $res = $sdkClient->uploadFile(self::$clientConfig['bucket'], $newFileName, $file->getTempName());
            return $newFileName;
        } catch (\Throwable $e) {
            throw new \Exception(sprintf("upload file failed, reason : %s", $e->getMessage()));
        }
    }
}