<?php

namespace Webeleven\EasyMutators;

use Illuminate\Contracts\Filesystem\Filesystem;
use Intervention\Image\ImageManager;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\File\File;
use Webeleven\EasyMutators\ValueObjects\File as EasyFile;
use Webeleven\EasyMutators\Mapping\FileMapping;
use Webeleven\EasyMutators\Mapping\ImageMapping;
use Webeleven\EasyMutators\Upload\TempFileUploader;
use Webeleven\EasyMutators\ValueObjects\Image;

class MediaService
{

    private $storage;

    private $tempFileUploader;

    private $intervention;

    private $transformer;

    public function __construct(
        Filesystem $storage,
        TempFileUploader $tempFileUploader,
        ImageManager $intervention,
        ImageTransformer $transformer
    ) {
        $this->storage = $storage;
        $this->tempFileUploader = $tempFileUploader;
        $this->intervention = $intervention;
        $this->transformer = $transformer;
    }

    public function makeMedia($file, $mapping)
    {
        $file = $this->tempFileUploader->getTempFile($file);
        
        if (! $file) {
            return null;
        }

        if ($mapping instanceof ImageMapping) {
            return $this->makeImage($file, $mapping);
        }

        return$this->makeFile($file, $mapping);
    }

    protected function makeFile(File $file, FileMapping $mapping)
    {
        $mediaFile = new EasyFile();

        $filepath = $this->getFilepath($file, $mapping);

        $this->storage->put($filepath->full(), file_get_contents($file));

        $mediaFile->name = $mapping->getFileName();
        $mediaFile->filename = $filepath->filename();
        $mediaFile->path = $filepath->full();
        $mediaFile->basePath = $filepath->base();
        $mediaFile->size = $file->getSize();
        $mediaFile->extension = $file->guessExtension();
        $mediaFile->mimeType = $file->getMimeType();

        return $mediaFile;
    }

    protected function makeImage(File $file, ImageMapping $mapping)
    {
        $size = getimagesize($file->getRealPath());

        if (! $size) {
            throw new InvalidArgumentException('The given file is not an image.');
        }

        $image = $this->makeAndStoreImage($file, $mapping);

        if ($mapping->hasConversions()) {
            $this->makeConversions($image, $file, $mapping);
        }

        return $image;
    }

    protected function makeConversions($image, $file, ImageMapping $mapping)
    {
        $mapping->getConversions()->map(function($mapping, $name) use ($image, $file) {

            $conversion = $this->makeAndStoreImage($file, $mapping);

            $image->addConversion($name, $conversion);

        });
    }

    protected function makeAndStoreImage(File $file, ImageMapping $mapping)
    {
        $interventionImage = $this->getInterventionImage($file);

        $this->transformer->transformWith($interventionImage, $mapping);

        $filepath = $this->getFilepath($interventionImage, $mapping);

        $this->storage->put($filepath->full(), $interventionImage->encode(null, $mapping->getQuality())->getEncoded());

        $image = new Image;
        $image->name = $mapping->getFileName();
        $image->filename = $filepath->filename();
        $image->size = $file->getSize();
        $image->width = $interventionImage->width();
        $image->height = $interventionImage->height();
        $image->path = $filepath->full();
        $image->basePath = $filepath->base();
        $image->extension = $file->guessExtension();
        $image->mimeType = $file->getMimeType();

        return $image;
    }

    protected function getFilepath($file, FileMapping $mapping)
    {
        $pathGenerator = $mapping->getPathGenerator();

        if ($mapping instanceof ImageMapping) {
            return $pathGenerator->generatePathForImages($file, $mapping);
        }

        return $pathGenerator->generatePathForFiles($file, $mapping);
    }

    protected function getInterventionImage($file)
    {
        $image = $this->intervention->make($file);

        $image->extension = $file->guessExtension();

        return $image;
    }

    public function delete(EasyFile $file)
    {
        $this->storage->delete($file->path());

        if ($file instanceof Image && $file->hasConversions()) {

            $file->getConversions()->map(function($image) {
                $this->delete($image);
            });
        }

        if (! count($this->storage->allFiles($file->basePath()))) {
            $this->storage->deleteDirectory($file->basePath());
        }
    }

}
