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

            return new File($file->getRealPath());

        } else if (is_string($file) && filter_var($file, FILTER_VALIDATE_URL)) {

            $path = $this->getNewTempFileName();

            $data = file_get_contents($file);

            file_put_contents($path, $data);

            return new File($path);
        }

        return null;
    }

}
