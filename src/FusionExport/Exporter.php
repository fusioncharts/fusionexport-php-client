<?php

namespace FusionExport;

class Exporter
{
    private $exportDoneListener;

    private $exportStateChangedListener;

    private $exportConfig;

    private $exportServerHost;

    private $exportServerPort;

    private $wsClient;

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
        $connString = 'ws://' . $this->exportServerHost . ':' . $this->exportServerPort;

        \Ratchet\Client\connect($connString)->then(function ($conn) {

            $this->wsClient = $conn;

            $this->wsClient->on('message', function ($data) {
                $this->processDataReceived($data);
            });

            $this->wsClient->send($this->getFormattedExportConfigs());

        }, function ($e) {

            $this->onExportDone(null, $e);

        });
    }

    public function cancel()
    {
        if (!is_null($this->wsClient)) {
            $this->wsClient->close();
        }
    }

    private function processDataReceived($data)
    {
        if ($this->startsWith($data, Constants::EXPORT_EVENT)) {

            $this->processExportStateChangedData($data);

        } else if ($this->startsWith($data, Constants::EXPORT_DATA)) {

            $this->processExportDoneData($data);

        }
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

        $event = (object) [
            'state' => $data,
        ];

        call_user_func($this->exportStateChangedListener, $event);
    }

    private function onExportDone($data, \Exception $e = null)
    {
        $this->cancel();

        if (is_null($this->exportDoneListener)) return;

        $event = (object) [
            'export' => $data,
        ];

        call_user_func($this->exportDoneListener, $event, $e);
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
