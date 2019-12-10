<?php

namespace FusionExport\Converters;

class ObjectConverter
{
    public static function convert($value)
    {
        if (gettype($value) !== 'object') {
            return $value;
        }

        if (gettype($value) === 'object') {
            return json_encode($value);
        }
    }
}
