<?php

namespace FusionExport;

class ExportConfig
{
    protected $configs;

    public function __construct()
    {
        $this->configs = [];
    }

    public function set($name, $value)
    {
        $this->configs[$name] = $value;
        return $this;
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
            default:
                return "\"" . $value . "\"";

        }
    }
}
