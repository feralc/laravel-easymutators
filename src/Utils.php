<?php

namespace Webeleven\EasyMutators;

class Utils
{

    public static function shortHash($value = null)
    {
        if (! empty($value)) {
            return substr(md5($value), 0, 8);
        }

        return substr(md5(uniqid(microtime())), 0, 8);
    }

}