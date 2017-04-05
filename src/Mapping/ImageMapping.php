<?php

namespace Webeleven\EasyMutators\Mapping;

use Illuminate\Support\Collection;
use Webeleven\EasyMutators\ValueObjects\Image;

class ImageMapping extends FileMapping
{

    protected $conversions;

    protected $width;

    protected $height;

    protected $canvasWidth;

    protected $canvasHeight;

    protected $aspectRatio = true;

    protected $quality = 100;

    protected $conversion = false;

    public function __construct(MediaMapper $mapper)
    {
        parent::__construct($mapper);

        $this->conversions = new Collection;
    }

    public function addConversion($name, array $settings = [])
    {
        $mapping = new static($this->getMapper());
        $mapping->setAsConversion();

        $mapping->setKey($this->getKey());
        $mapping->name(array_get($settings, 'name', $this->fileName));
        $mapping->width(array_get($settings, 'width'));
        $mapping->height(array_get($settings, 'height'));
        $mapping->canvasWidth(array_get($settings, 'canvas_width'));
        $mapping->canvasHeight(array_get($settings, 'canvas_height'));

        $aspectRatio = (bool) array_get($settings, 'aspect_ratio');

        $aspectRatio ? $this->keepAspectRatio() : $this->dontKeepAspectRatio();

        $this->conversions->put($name, $mapping);

        return $mapping;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function width($width)
    {
        $this->width = $width;
        return $this;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function height($height)
    {
        $this->height = $height;
        return $this;
    }

    public function getMappedClass()
    {
        return Image::class;
    }

    public function getConversions()
    {
        return $this->conversions;
    }

    public function hasConversions()
    {
        return $this->conversions->count() > 0;
    }

    public function keepAspectRatio()
    {
        $this->aspectRatio = true;
        return $this;
    }

    public function dontKeepAspectRatio()
    {
        $this->aspectRatio = false;
        return $this;
    }

    public function shouldKeepAspectRatio()
    {
        return $this->aspectRatio;
    }

    public function shouldResize()
    {
        return ! empty($this->width) || ! empty($this->height);
    }

    public function shouldResizeCanvas()
    {
        return ! empty($this->canvasWidth) || ! empty($this->canvasHeight);
    }

    public function getCanvasWidth()
    {
        return $this->canvasWidth;
    }

    public function canvasWidth($canvasWidth)
    {
        $this->canvasWidth = $canvasWidth;
        return $this;
    }

    public function getCanvasHeight()
    {
        return $this->canvasHeight;
    }

    public function canvasHeight($canvasHeight)
    {
        $this->canvasHeight = $canvasHeight;
        return $this;
    }

    public function setAsConversion()
    {
        $this->conversion = true;
        return $this;
    }

    public function isConversion()
    {
        return $this->conversion;
    }

    public function getQuality()
    {
        return $this->quality;
    }

    public function quality($quality)
    {
        $this->quality = intval($quality);
        return $this;
    }

}