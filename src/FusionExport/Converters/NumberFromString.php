<?php

namespace FusionExport\Converters;

class NumberFromString
{
    public static function convert($value)
    {
        return (int)$value;
    }
}