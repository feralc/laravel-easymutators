<?php

namespace Webeleven\EasyMutators;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Webeleven\EasyMutators\Mapping\MediaMapper;
use Webeleven\EasyMutators\ValueObjects\File;
use Webeleven\EloquentValueObject\CastsValueObjects;
use Webeleven\EloquentValueObject\ValueObjectInterface;

trait EasyMutatorsDoctrineTrait
{
    use CastsValueObjects;

    protected $mapper;

    public function getKey()
    {
        return $this->getId();
    }

    public function __call($method, $parameters)
    {
        list($action, $field) = $this->parseMethodAndMutatedFieldName($method);

        if ($this->isValueObject($field)) {

            if ($action === 'set') {

                $value = array_get($parameters, 0);

                if (! $value instanceof ValueObjectInterface) {
                    $value = $this->createValueObject($field, $value);
                }

                $this->{$field} = $value->toScalar();

                $this->invalidateValueObjectCache($field);

            } elseif ($action === 'get') {

                if (! $this->isValueObjectCached($field)) {

                    // Allow other mutators and such to do their work first.
                    $value = $this->{$field};

                    // Don't cast empty $value.
                    if ($value === null || $value === '') {
                        return null;
                    }

                    // Cache the instantiated value for future access.
                    // This allows tests such as ($model->casted === $model->casted) to be true.
                    $this->cacheValueObject($field, $this->createValueObject($field, $value));
                }

                return $this->getCachedValueObject($field);
            }

            throw new InvalidArgumentException('Method to manage mutated attribute must starts with get or set.');
        }
    }

    protected function parseMethodAndMutatedFieldName($method)
    {
        $method = Str::snake($method);
        return [substr($method, 0, 3), substr($method, 4)];
    }

    public static function bootEasyMutatorsTrait()
    {
        if (config('easymutators.delete_old_media') === 'on-set') {
            Event::listen('easymutators.new-attribute-set', function ($key, $value, $old) {
                if ($old instanceof File) {
                    app(MediaService::class)->delete($old);
                }
            });
        }

        static::saved(function($model) {

            if (config('easymutators.delete_old_media') === 'on-save') {

                collect($model->getOldValueObjectsAttributes())->map(function($values) {
                    foreach ($values as $old) {
                        if ($old instanceof File) {
                            app(MediaService::class)->delete($old);
                        }
                    }
                });
            }

        });

    }

    public function getMediaMapper()
    {
        if ($this->mapper !== null) {
            return $this->mapper;
        }

        return $this->mapper = new MediaMapper($this);
    }

    private function mapMediaFields(MediaMapper $mapper)
    {
        collect($this->getValueObjects())->map(function($class, $name) use ($mapper) {
            if ($mapper->isMedia($class)) {
                $mapper->mapByClass($name, $class);
            }
        });

        $this->mapMedia($mapper);
    }

    private function determineObjectClass($type)
    {
        $objects = config('easymutators.objects');

        if (isset($objects[$type])) {
            return $objects[$type];
        }

        if (class_exists($type)) {
            return $type;
        }

        throw new InvalidArgumentException(sprintf('Invalid object field type: %s', $type));
    }

    protected function getValueObjects()
    {
        $objects = isset($this->mutations) && is_array($this->mutations) ? $this->mutations : [];

        $objects = collect($objects)->map(function($value, $key) {
            return $this->determineObjectClass($value);
        })->toArray();

        return $objects;
    }

    protected function createValueObject($key, $value)
    {
        if ($value instanceof ValueObjectInterface) {
            return $value;
        }

        if ($value === null) {
            return null;
        }

        $class = $this->getValueObjects()[$key];

        if (is_string($value) && ($data = json_decode($value, true)) !== null) {
            return new $class($data);
        }

        $this->mapMediaFields($this->getMediaMapper());

        $mapping = $this->getMediaMapper()->findMapping($key);

        if ($mapping) {
            return call_user_func([$class, 'make'], $value, $mapping);
        }

        return call_user_func([$class, 'make'], $value);
    }

    protected function mapMedia(MediaMapper $mapper) { }

}