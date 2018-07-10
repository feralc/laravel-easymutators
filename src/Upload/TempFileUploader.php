<?php

namespace Webeleven\EasyMutators\Upload;

use Symfony\Component\HttpFoundation\File\File;

class TempFileUploader
{

    public function getTempFile($file)
    {
        return $this->normalizeFile($file);
    }

    protected function getNewTempFileName()
    {
        return tempnam(sys_get_temp_dir(), md5(uniqid(rand(1, 1000))));
    }

    /**
     * @param $file
     * @return File
     */
    private function normalizeFile($file)
    {

        if ($file instanceof File) {

            return $file;

        } else if (is_string($file) && filter_var($file, FILTER_VALIDATE_URL)) {

            $path = $this->getNewTempFileName();

            $data = file_get_contents($file);

            file_put_contents($path, $data);

            return new File($path);
        } else if ($this->isBase64Image($file)){
            $path = $this->getNewTempFileName();

            $data = base64_decode($this->sanitizeBase64Image($file));

            file_put_contents($path, $data);

            return new File($path);
        }

        return null;
    }

    public function sanitizeBase64Image($file)
    {
        return str_replace("data:svg+xml/svg;base64,", "", str_replace("data:image/svg;base64,", "", str_replace("data:image/jpeg;base64,", "", str_replace("data:image/jpg;base64,", "", str_replace("data:image/png;base64,", "", $file)))));
    }

    public function isBase64Image($file)
    {

        $explode = explode(',', $file);
        $allow = ['png', 'jpg', 'jpeg', 'svg', 'svg+xml'];
        $format = str_replace(
            [
                'data:image/',
                ';',
                'base64',
            ],
            [
                '', '', '',
            ],
            $explode[0]
        );
        // check file format
        if (!in_array($format, $allow)) {
            return false;
        }
        // check base64 format
        if (!preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $explode[1])) {
            return false;
        }
        return true;


    }

}
