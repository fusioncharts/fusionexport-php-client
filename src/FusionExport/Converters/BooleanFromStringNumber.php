<?php

namespace FusionExport\Converters;

class BooleanFromStringNumber
{
    public static function convert($value)
    {
        if ($value === 'false') $value = false;

        return (bool)$value;
    }
}