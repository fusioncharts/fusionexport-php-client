<?php

// D3 export

require __DIR__ . '/../vendor/autoload.php';

// Use the sdk
use FusionExport\ExportManager;
use FusionExport\ExportConfig;

// Instantiate the ExportConfig class and add the required configurations
$exportConfig = new ExportConfig();
$exportConfig->set('templateFilePath', realpath('resources/template_amcharts.html'));
$exportConfig->set('type', 'jpg');
$exportConfig->set('asyncCapture', 'true');

// Instantiate the ExportManager class
$exportManager = new ExportManager();
// Call the export() method with the export config
$exportManager->export($exportConfig, '.', true);