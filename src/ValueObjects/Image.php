<?php

namespace Webeleven\EasyMutators\ValueObjects;

use Illuminate\Support\Collection;

class Image extends File
{

    protected $width;

    protected $height;

    protected $conversions;

    public function __construct($value = null)
    {
        $this->conversions = new Collection;

        parent::__construct($value);
    }

    public function addConversion($name, Image $image)
    {
        $this->conversions->put($name, $image);

        return $this;
    }

    public function getConversions()
    {
        return $this->conversions;
    }

    public function hasConversions()
    {
        return $this->conversions->count() > 0;
    }

    public function hasConversion($name)
    {
        return $this->conversions->has($name);
    }

    public function getConversion($name)
    {
        return $this->conversions->get($name);
    }

    protected function setData(array $data)
    {
        parent::setData($data);

        $this->width = array_get($data, 'width');
        $this->height = array_get($data, 'height');

        collect(array_get($data, 'conversions'))->map(function($image, $name) {
            $this->addConversion($name, new static($image));
        });
    }

    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'width' => $this->width,
            'height' => $this->height,
            'conversions' => $this->conversions->toArray()
        ]);
    }

    public function __get($key)
    {
        if ($this->hasConversion($key)) {
            return $this->getConversion($key);
        }

        return parent::__get($key);
    }

}