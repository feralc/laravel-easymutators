<?php

namespace Webeleven\EasyMutators\Mapping;

use Exception;
use InvalidArgumentException;
use Webeleven\EasyMutators\ValueObjects\File;
use Webeleven\EasyMutators\Upload\PathGenerator;

class FileMapping
{

    protected $key;

    protected $pathGenerator;

    protected $fileName;

    private $mapper;

    public function __construct(MediaMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function generatePathWith($pathGenerator = null)
    {
        try {

            if ($pathGenerator instanceof PathGenerator) {
                $this->pathGenerator = $pathGenerator;
            } else if (is_string($pathGenerator)) {
                $this->pathGenerator = app($pathGenerator);
            } else {
                throw new InvalidArgumentException('Invalid path generator');
            }

        } catch (Exception $e) {
            throw new InvalidArgumentException('Invalid path generator');
        }
    }

    /**
     * @return PathGenerator
     */
    public function getPathGenerator()
    {
        if (empty($this->pathGenerator)) {
            $this->generatePathWith(config('easymutators.default_path_generator'));
        }

        return $this->pathGenerator;
    }

    public function getMappedClass()
    {
        return File::class;
    }

    public function isImageMapping()
    {
        return $this instanceof ImageMapping;
    }

    /**
     * @return MediaMapper
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * @return mixed
     */
    public function getFileName()
    {
        if (! empty($this->fileName)) {
            return $this->fileName;
        }

        return $this->fileName = $this->key;
    }

    public function name($fileName)
    {
        $this->fileName = $fileName;
        return $this;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

}