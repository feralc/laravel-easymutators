<?php

namespace Webeleven\EasyMutators\Upload;

use Intervention\Image\Image as InterventionImage;
use Symfony\Component\HttpFoundation\File\File;
use Webeleven\EasyMutators\Mapping\FileMapping;
use Webeleven\EasyMutators\Mapping\ImageMapping;

interface PathGenerator
{

    /**
     * @param File $file
     * @param FileMapping $mapping
     * @return FilePath
     */
    public function generatePathForFiles(File $file, FileMapping $mapping);

    /**
     * @param InterventionImage $image
     * @param ImageMapping $mapping
     * @return FilePath
     */
    public function generatePathForImages(InterventionImage $image, ImageMapping $mapping);

}