<?php

namespace FusionExport;

use FusionExport\Converters\NumberConverter;
use FusionExport\Converters\BooleanConverter;

class ExportConfig
{
    protected $configs;

    public function __construct()
    {
        $this->typingsFile = __DIR__ . '/../config/fusionexport-typings.json';
        $this->metaFile = __DIR__ . '/../config/fusionexport-meta.json';
        $this->configs = [];
        $this->formattedConfigs = [];

        $this->readTypingsConfig();
        $this->readMetaConfig();
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

        if (!property_exists($this->typings, $name)) {
            throw new \Exception($name . ' is not a valid config.');
        }

        $type = $this->typings->$name->type;

        if (property_exists($this->typings->$name, 'converter')) {
            $converter = $this->typings->$name->converter;

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
                @$this->configs['templateFilePath']
            );

            $tmplBundler->process();

            $this->formattedConfigs['templateFilePath'] = $tmplBundler->getTemplatePathInZip();
            $this->formattedConfigs['resourceFilePath'] = $tmplBundler->getResourcesZip();
        }

        if (isset($this->configs['chartConfig'])) {
            $this->formattedConfigs['chartConfig'] = $this->formatChartConfig($this->configs['chartConfig']);
        }

        foreach ($this->configs as $key => $value) {
            switch ($key) {
                case 'templateFilePath': 
                case 'resourceFilePath':
                case 'chartConfig':
                    break;
                default:
                    $this->formattedConfigs[$key] = $value;
            }
        }

        foreach ($this->formattedConfigs as $key => $value) {
            if (
                property_exists($this->meta, $key) && 
                $this->meta->$key->isBase64Required
            ) {
                $this->formattedConfigs[$key] = Helpers::convertFilePathToBase64($value);
            }
        }

        $this->formattedConfigs['clientName'] = 'PHP';
    }

    private function formatChartConfig($value)
    {
        if (Helpers::endsWith($value, '.json')) {
            $value = file_get_contents($value);
        }

        return $value;
    }

    private function readTypingsConfig()
    {
        $this->typings = json_decode(file_get_contents($this->typingsFile));
    }

    private function readMetaConfig()
    {
        $this->meta = json_decode(file_get_contents($this->metaFile));
    }
}
