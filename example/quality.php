<?php

// Exporting a chart with best quality

require __DIR__ . '/../vendor/autoload.php';

// Use the sdk
use FusionExport\ExportManager;
use FusionExport\ExportConfig;

// Instantiate the ExportConfig class and add the required configurations
$exportConfig = new ExportConfig();
$exportConfig->set('chartConfig', realpath('resources/single.json'));
$exportConfig->set('quality', 'best');


// Instantiate the ExportManager class
$exportManager = new ExportManager();

$exportManager->export($exportConfig);
