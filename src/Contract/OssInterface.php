<?php


namespace surprisephp\Oss\Contract;


use Phalcon\Http\Request\File;

Interface OssInterface
{


    /**
     * @param File $file
     * @param $newFileName
     * @return mixed
     */
    public function uploadFile(File $file, $newFileName);
}