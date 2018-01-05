<?php

namespace FusionExport;

use FusionExport\Converters\NumberFromString;
use FusionExport\Converters\BooleanFromStringNumber;

class ExportConfig
{
    protected $configs;

    public function __construct()
    {
        $this->metaDataFile = __DIR__ . '/../config/metadata.json';
        $this->configs = [];

        $this->readMetaDataConfig();
    }

    public function set($name, $value)
    {
        $this->configs[$name] = $value;

        $this->sanitizeConfig($name);

        return $this;
    }

    private function sanitizeConfig($name)
    {
        $value = $this->configs[$name];

        if (!property_exists($this->metaData, $name)) {
            throw new \Exception($name . ' is not a valid config.');
        }

        $type = $this->metaData->$name->type;

        if (property_exists($this->metaData->$name, 'converter')) {
            $converter = $this->metaData->$name->converter;

            if ($converter === 'BooleanFromStringNumber') {
                $value = BooleanFromStringNumber::convert($value);
            } else if ($converter === 'NumberFromString') {
                $value = NumberFromString::convert($value);
            }
        }

        if (gettype($value) !== $type) {
            throw new \Exception($name . ' must be a ' . $type . '.');
        }

        $this->configs[$name] = $value;
    }

    public function get($name)
    {
        return $this->configs[$name];
    }

    public function remove($name)
    {
        unset($this->configs[$name]);
        return $this;
    }

    public function has($name)
    {
        return array_key_exists($name, $this->configs);
    }

    public function clear()
    {
        $this->configs = [];
    }

    public function count()
    {
        return count($this->configs);
    }

    public function configNames()
    {
        return array_keys($this->configs);
    }

    public function configValues()
    {
        return array_values($this->configs);
    }

    public function clone()
    {
        $newExportConfig = new ExportConfig();

        foreach ($this->configs as $key => $value) {
            $newExportConfig->set($key, $value);
        }

        return $newExportConfig;
    }

    public function getFormattedConfigs()
    {
        $configsAsJSON = '';

        foreach ($this->configs as $key => $value) {
            $formattedConfigValue = $this->getFormattedConfigValue($key, $value);
            $keyValuePair = "\"" . $key . "\": " . $formattedConfigValue . ', ';

            $configsAsJSON .= $keyValuePair;
        }

        if (strlen($configsAsJSON) > 1) {
            $configsAsJSON = rtrim($configsAsJSON, ', ');
        }

        $configsAsJSON = '{' . $configsAsJSON . '}';
        return $configsAsJSON;
    }

    private function getFormattedConfigValue($name, $value)
    {
        switch ($name) {

            case 'chartConfig':
                return $value;
            case 'asyncCapture':
            case 'exportAsZip':
                return $value ? 'true' : 'false';
            case 'templateFilePath':
                return TemplatePackager::getZip(
                    $this->get('templateFilePath'), 
                    $this->get('resourceFilePath')
                );
            case 'outputFileDefinition': 
            case 'dashboardLogo':
            case 'callbackFilePath':
            case 'inputSVG':
                return ExportConfig::convertFilePathToBase64($value);
            default:
                return "\"" . $value . "\"";

        }
    }

    private static function convertFilePathToBase64($val)
    {
        return base64_encode(file_get_contents($val));
    }

    private function readMetaDataConfig()
    {
        $this->metaData = json_decode(file_get_contents($this->metaDataFile));
    }
}
