<?php

namespace FusionExport;

use FusionExport\Converters\NumberConverter;
use FusionExport\Converters\BooleanConverter;

class ExportConfig
{
    protected $configs;

    public function __construct()
    {
        $this->metaDataFile = __DIR__ . '/../config/fusionexport-typings.json';
        $this->configs = [];
        $this->formattedConfigs = [];

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

            if ($converter === 'BooleanConverter') {
                $value = BooleanConverter::convert($value);
            } else if ($converter === 'NumberConverter') {
                $value = NumberConverter::convert($value);
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

        $this->formatConfigs();

        foreach ($this->formattedConfigs as $key => $value) {
            $keyValuePair = "\"" . $key . "\": " . json_encode($value) . ", ";
            $configsAsJSON .= $keyValuePair;
        }

        if (strlen($configsAsJSON) > 1) {
            $configsAsJSON = rtrim($configsAsJSON, ', ');
        }

        $configsAsJSON = '{' . $configsAsJSON . '}';
        return $configsAsJSON;
    }

    private function formatConfigs()
    {
        if (isset($this->configs['templateFilePath'])) {
            $tmplBundler = new TemplateBundler(
                $this->configs['templateFilePath'],
                @$this->configs['resourceFilePath']
            );

            $tmplBundler->process();

            $this->formattedConfigs['templateFilePath'] = $tmplBundler->getTemplatePathInZip();
            $this->formattedConfigs['resourceFilePath'] = $tmplBundler->getResourcesZipAsBase64();
        }

        foreach ($this->configs as $key => $value) {
            switch ($key) {
                case 'chartConfig': 
                    $formattedValue = $this->formatChartConfig($value);
                    break;
                case 'asyncCapture':
                case 'exportAsZip':
                    $formattedValue = $value ? 'true' : 'false';
                    break;
                case 'outputFileDefinition': 
                case 'dashboardLogo':
                case 'callbackFilePath':
                case 'inputSVG':
                    $formattedValue = Helpers::convertFilePathToBase64($value);
                    break;
                case 'templateFilePath':
                case 'resourceFilePath':
                    $formattedValue = null;
                    break;
                default:
                    $formattedValue = $value;
            }

            if (isset($formattedValue)) {
                $this->formattedConfigs[$key] = $formattedValue;
            }
        }
    }

    private function formatChartConfig($value)
    {
        if (Helpers::endsWith($value, '.json')) {
            $value = file_get_contents($value);
        }

        return $value;
    }

    private function readMetaDataConfig()
    {
        $this->metaData = json_decode(file_get_contents($this->metaDataFile));
    }
}
