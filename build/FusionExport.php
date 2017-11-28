<?php


class Constants
{
    const EXPORT_DATA = 'EXPORT_DATA:';
    const EXPORT_EVENT = 'EXPORT_EVENT:';
    const UNIQUE_BORDER = ':8780dc3c41214695ae96b3432963d744:';
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT = 1337;
}


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
            default:
                return "\"" . $value . "\"";

        }
    }
}


class Exporter
{
    private $exportDoneListener;

    private $exportStateChangedListener;

    private $exportConfig;

    private $exportServerHost;

    private $exportServerPort;

    private $tcpClient;

    public function __construct(ExportConfig $exportConfig, $exportDoneListener, $exportStateChangedListener)
    {
        $this->exportConfig = $exportConfig;
        $this->exportDoneListener = $exportDoneListener;
        $this->exportStateChangedListener = $exportStateChangedListener;
    }

    public function setExportConnectionConfig($exportServerHost, $exportServerPort)
    {
        $this->exportServerHost = $exportServerHost;
        $this->exportServerPort = $exportServerPort;
    }

    public function start()
    {
        try {

            $this->tcpClient = socket_create(AF_INET, SOCK_STREAM, 0);

            socket_connect($this->tcpClient, $this->exportServerHost, $this->exportServerPort);

            $payload = $this->getFormattedExportConfigs();

            socket_write($this->tcpClient, $payload, strlen($payload));

            $data = '';

            do {

                $inboundData = socket_read($this->tcpClient, 4096);
                $data .= $inboundData;
                $data = $this->processDataReceived($data);

            } while (strlen($inboundData) > 0);

        } catch (\Exception $e) {

            $this->onExportDone(null, $e);

        } finally {

            if (!is_null($this->tcpClient)) {
                socket_close($this->tcpClient);
            }

        }
    }

    public function cancel()
    {
        if (!is_null($this->tcpClient)) {
            socket_close($this->tcpClient);
        }
    }

    private function processDataReceived($data)
    {
        $parts = explode(Constants::UNIQUE_BORDER, $data);

        foreach ($parts as $part) {

            if ($this->startsWith($part, Constants::EXPORT_EVENT)) {

                $this->processExportStateChangedData($part);

            } else if ($this->startsWith($part, Constants::EXPORT_DATA)) {

                $this->processExportDoneData($part);

            }

        }

        return $parts[count($parts) - 1];
    }

    private function processExportStateChangedData($data)
    {
        $state = substr($data, strlen(Constants::EXPORT_EVENT));
        $exportError = $this->checkExportError($state);

        if (is_null($exportError)) {
            $this->onExportSateChanged(json_decode($state));
        } else {
            $this->onExportDone(null, new \Exception($exportError));
        }
    }

    private function processExportDoneData($data)
    {
        $exportResult = substr($data, strlen(Constants::EXPORT_DATA));
        $this->onExportDone(json_decode($exportResult)->data);
    }

    private function checkExportError($exportResult)
    {
        $exportResult = json_decode($exportResult);

        if (array_key_exists('error', $exportResult)) {
            return $exportResult->error;
        }
    }

    private function onExportSateChanged($data)
    {
        if (is_null($this->exportStateChangedListener)) return;

        call_user_func($this->exportStateChangedListener, $data);
    }

    private function onExportDone($data, \Exception $e = null)
    {
        if (is_null($this->exportDoneListener)) return;

        call_user_func($this->exportDoneListener, $data, $e);
    }

    private function getFormattedExportConfigs()
    {
        return 'ExportManager.export<=:=>' . $this->exportConfig->getFormattedConfigs();
    }

    private function startsWith($haystack, $needle)
    {
        if (strpos($haystack, $needle) === 0) {
            return true;
        }

        return false;
    }
}


class ExportManager
{
    private $host;

    private $port;

    public function __construct(
        $host = Constants::DEFAULT_HOST,
        $port = Constants::DEFAULT_PORT
    )
    {
        $this->host = $host;
        $this->port = $port;
    }

    public function export(ExportConfig $exportConfig, $exportDoneListener = null, $exportStateChangedListener = null)
    {
        $exporter = new Exporter($exportConfig, $exportDoneListener, $exportStateChangedListener);

        $exporter->setExportConnectionConfig($this->host, $this->port);

        $exporter->start();

        return $exporter;
    }
}
