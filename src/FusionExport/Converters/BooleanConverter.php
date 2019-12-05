<?php

namespace FusionExport\Converters;

class BooleanConverter
{
    public static function convert($value)
    {
        if ($value === 'false') {
            $value = false;
        }
        else if ($value === 'true') {
            $value = true;
        }
        else if ($value === '1' || $value === 1) { 
            $value = true;
        }
        else if ($value === '0' || $value === 0) { 
            $value = true;
        }
        else if ( trim($value) === '') { 
            $value = false;
        }
        
        return (bool)$value;
    }
}
