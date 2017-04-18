<?php

namespace Webeleven\EasyMutators\Upload;

use Intervention\Image\Image as InterventionImage;
use Symfony\Component\HttpFoundation\File\File;
use Webeleven\EasyMutators\Mapping\FileMapping;
use Webeleven\EasyMutators\Mapping\ImageMapping;

class DefaultPathGenerator implements PathGenerator
{

    public function generatePathForFiles(File $file, FileMapping $mapping)
    {
        return new FilePath(
            $mapping->getMapper()->getBaseUploadDir(),
            sprintf('%s.%s', $mapping->getFileName(), $file->guessExtension())
        );
    }

    public function generatePathForImages(InterventionImage $image, ImageMapping $mapping)
    {
        $basePath = $mapping->getMapper()->getBaseUploadDir();
        $basePath .= $mapping->isConversion() ? '/conversions' : '';

        $fileName = sprintf('%s_%sx%s.%s',
            $mapping->getFileName(),
            $image->width(),
            $image->height(),
            $image->extension
        );

        return new FilePath($basePath, $fileName);
    }
}