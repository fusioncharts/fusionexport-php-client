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

    public function export(ExportConfig $exportConfig, $outputDir = '.', $unzip = false)
    {
        $exporter = new Exporter($exportConfig);

        $exporter->setExportConnectionConfig($this->host, $this->port);

        $exporter->start($outputDir, $unzip);

        return $exporter;
    }

    public static function path_join(...$paths) 
    {
        $saPaths = [];

        foreach ($paths as $path) {
            $saPaths[] = trim($path, DIRECTORY_SEPARATOR);
        }

        $saPaths = array_filter($saPaths);

        return join(DIRECTORY_SEPARATOR, $saPaths);
    }

    public static function saveExportedFiles($export, $dir = '.')
    {
        @mkdir($dir, 0777, true);

        foreach ($export as $file) {
            $filePath = ExportManager::path_join($dir, $file->realName);
            $dirname = dirname($filePath);
            @mkdir($dirname, 0777, true);
            file_put_contents($filePath, base64_decode($file->fileContent));
        }
    }

    public static function getExportedFileNames($export) 
    {
        return array_map(function ($file) {
            return $file->realName;
        }, $export);
    }
}
