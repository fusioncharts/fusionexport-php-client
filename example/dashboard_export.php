<?php

// Exporting a dashboard

require __DIR__ . '/../vendor/autoload.php';
// Use the sdk
use FusionExport\ExportManager;
use FusionExport\ExportConfig;

// Instantiate the ExportConfig class and add the required configurations
$exportConfig = new ExportConfig();
$exportConfig->set('chartConfig', realpath('resources/multiple.json'));
$exportConfig->set('templateFilePath', realpath('resources/template.html'));


// Instantiate the ExportManager class
$exportManager = new ExportManager();

$exportManager->export($exportConfig);
