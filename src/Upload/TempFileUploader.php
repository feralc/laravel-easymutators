<?php

namespace Webeleven\EasyMutators\Upload;

use Illuminate\Http\File;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

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

        if ($file instanceof SymfonyFile) {

            return new File($file->getRealPath());

        } else if (is_string($file)) {

            $path = $this->getNewTempFileName();

            $data = file_get_contents($file);

            file_put_contents($path, $data);

            return new File($path);
        }

        throw new InvalidArgumentException('Invalid media type');
    }

}