<?php

namespace Webeleven\EasyMutators\ValueObjects;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Macroable;
use Webeleven\EloquentValueObject\ValueObject;

class Settings extends ValueObject implements Arrayable, Jsonable, ArrayAccess
{

    use Macroable;

    protected $settings = [];

    public function __construct($settings = null)
    {
        if (is_array($settings)) {
            $this->settings = $settings;
        } elseif (is_string($settings) && ! is_null($settings)) {
            $this->settings = json_decode($settings, true);
            $this->settings = $this->settings ? $this->settings : [];
        }
    }

    public function set($key, $value)
    {
        Arr::set($this->settings, $key, $value);
        return $this;
    }

    public function get($key, $default = null)
    {
        return Arr::get($this->settings, $key, $default);
    }

    public function has($keys)
    {
        return Arr::has($this->settings, $keys);
    }

    public function forget($keys)
    {
        Arr::forget($this->settings, $keys);
        return $this;
    }

    public function merge(array $settings)
    {
        $this->settings = array_merge($this->settings, $settings);
        return $this;
    }

    public function clear()
    {
        $this->settings = [];
        return $this;
    }

    public function all()
    {
        return $this->toArray();
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    public function asBool($key)
    {
        return filter_var($this->get($key), FILTER_VALIDATE_BOOLEAN);
    }

    public function asInteger($key)
    {
        return intval($this->get($key));
    }

    public function asDouble($key)
    {
        return doubleval($this->get($key));
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->settings;
    }

    public static function column(Blueprint $table)
    {
        $table->json('settings')->nullable()->default(null);
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function __set($key, $value)
    {
        return $this->set($key, $value);
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        $this->forget($offset);
    }

    /**
     * @return mixed
     */
    public function toScalar()
    {
        return $this->settings;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
}