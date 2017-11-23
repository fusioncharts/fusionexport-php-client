<?php

namespace FusionExport;

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
