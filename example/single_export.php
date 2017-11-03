<?php

require __DIR__ . '/../vendor/autoload.php';

use FcExportPHPClient\ExportManager;

$chartConfig = '[{"type":"column2d","renderAt":"chart-container","width":"550","height":"350","dataFormat":"json","dataSource":{"chart":{"caption":"Number of visitors last week","subCaption":"Bakersfield Central vs Los Angeles Topanga"},"data":[{"label":"Mon","value":"15123"},{"label":"Tue","value":"14233"},{"label":"Wed","value":"25507"}]}}]';

$exportManager = new ExportManager();
$exportManager->connect();
$export = $exportManager->export('{"chartConfig":' . $chartConfig . '}');
$exportManager->close();

foreach ($export as $file) {
  copy($file->tmpPath, $file->realName);
}
