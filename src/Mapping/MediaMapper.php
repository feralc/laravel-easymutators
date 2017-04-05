<?php

namespace Webeleven\EasyMutators\Mapping;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use Webeleven\EasyMutators\Utils;
use Webeleven\EasyMutators\ValueObjects\File;
use Webeleven\EasyMutators\ValueObjects\Image;

class MediaMapper
{

    private $mappings;

    private $baseUploadDir;

    private $entity;

    private $mediaClassMappings = [
        File::class => \Webeleven\EasyMutators\Mapping\FileMapping::class,
        Image::class => \Webeleven\EasyMutators\Mapping\ImageMapping::class
    ];

    public function __construct($entity)
    {
        $this->entity = $entity;
        $this->mappings = new Collection;
    }

    public function file($name)
    {
        $mapping = new FileMapping($this);

        $mapping->setKey($name);

        $this->mappings->put($name, $mapping);

        return $mapping;
    }

    public function image($name)
    {
        $mapping = new ImageMapping($this);

        $mapping->setKey($name);

        $this->mappings->put($name, $mapping);

        return $mapping;
    }

    public function getMappings()
    {
        return $this->mappings;
    }

    public function findMapping($key)
    {
        return $this->mappings->get($key);
    }

    public function baseUploadDir($dirName)
    {
        if (Str::endsWith($dirName, '/')) {
            $dirName = substr($dirName, 0, -1);
        }

        $this->baseUploadDir = $dirName;

        return $this;
    }

    public function getBaseUploadDir()
    {
        if ($this->baseUploadDir !== null) {

            preg_match('/\{(.*)\}/', $this->baseUploadDir, $matches);

            if (count($matches)) {
                $field = $matches[1];
                return preg_replace('/\{' . $field . '\}/', $this->entity->{$field}, $this->baseUploadDir);
            }

            return $this->baseUploadDir;
        }

        $reflect = new ReflectionClass($this->entity);

        $dir = ! empty($this->entity->getKey()) ?
            Utils::shortHash($this->entity->getKey()) . '/' . Utils::shortHash() : Utils::shortHash();

        return $this->baseUploadDir = Str::snake(Str::lower($reflect->getShortName())) . '/' . $dir;
    }

    public function getEntity()
    {
        return $this->entity;
    }

    public function isMedia($class)
    {
        return isset($this->mediaClassMappings[$class]);
    }

    public function mapByClass($name, $class)
    {
        $mappingClass = $this->mediaClassMappings[$class];

        $mapping = new $mappingClass($this);

        $mapping->setKey($name);

        $this->mappings->put($name, $mapping);

        return $mapping;
    }

}