<?php

namespace Webeleven\EasyMutators\Upload;

use Illuminate\Support\Str;

class FilePath
{

    protected $base;
    protected $filename;

    public function __construct($base, $filename)
    {
        $this->base = $base;
        $this->filename = $filename;
    }

    public function full()
    {
        $base = $this->base;

        if (! Str::endsWith($base, '/')) {
            $base .= '/';
        }

        return $base . $this->filename;
    }

    public function base()
    {
        return $this->base;
    }

    public function filename()
    {
        return $this->filename;
    }

}