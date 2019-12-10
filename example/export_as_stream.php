<?php

// exporting D3 chart as stream
require __DIR__ . '/../vendor/autoload.php';

// Use the sdk
use FusionExport\ExportManager;
use FusionExport\ExportConfig;

// Instantiate the ExportConfig class and add the required configurations
$exportConfig = new ExportConfig();
$exportConfig->set('templateFilePath', realpath(__DIR__ . '/resources/template_d3.html'));
$exportConfig->set('type', 'jpg');
$exportConfig->set('asyncCapture', true);

// Instantiate the ExportManager class
$exportManager = new ExportManager();
// Call the exportAsStream() method with the export config
$files = $exportManager->exportAsStream($exportConfig);

foreach ($files as $key => $value) {
	echo $key . " => ". strlen($value) . "\n";
	file_put_contents("./" . $key, $value);
}
