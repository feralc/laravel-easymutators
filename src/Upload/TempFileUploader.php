<?php

namespace Webeleven\EasyMutators\Upload;

use Illuminate\Http\File;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
        $path = $this->getNewTempFileName();

        if ($file instanceof UploadedFile) {

            $file->move($path);

            return new File($path);

        } else if (is_string($file)) {

            $data = file_get_contents($file);

            file_put_contents($path, $data);

            return new File($path);
        }

        throw new InvalidArgumentException('Invalid media type');
    }

}