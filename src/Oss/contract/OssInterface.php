<?php


namespace SurprisePhp\Oss\contract;


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