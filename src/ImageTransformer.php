<?php

namespace Webeleven\EasyMutators;

use Intervention\Image\Image as InterventionImage;
use Webeleven\EasyMutators\Mapping\ImageMapping;

class ImageTransformer
{

    public function transformWith(InterventionImage $image, ImageMapping $mapping)
    {
        if ($mapping->shouldResize()) {
            $image->resize(
                $mapping->getWidth(),
                $mapping->getHeight(),
                function ($constraint) use ($mapping) {
                    if ($mapping->shouldKeepAspectRatio()) {
                        $constraint->aspectRatio();
                    }
                }
            );
        }

        if ($mapping->shouldResizeCanvas()) {
            $image->resizeCanvas(
                $mapping->getCanvasWidth(),
                $mapping->getCanvasHeight()
            );
        }
        
        if ($mapping->shouldFit()) {
            list($width, $height) = $mapping->getFit();
            $image->fit($width, $height);
        }

        return $image;
    }

}
