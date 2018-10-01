<?php

// Exporting a chart

require __DIR__ . '/vendor/autoload.php';

// Use the sdk
use FusionExport\ExportManager;
use FusionExport\ExportConfig;

define("__AWS_BUCKET_NAME", "");
define("__AWS_ACCESS_KEY", "");
define("__AWS_SECRET_KEY", "");

// Instantiate the ExportConfig class and add the required configurations
$exportConfig = new ExportConfig();
$exportConfig->set('chartConfig', realpath('resources/single.json'));

// Instantiate the ExportManager class
$exportManager = new ExportManager();
// Call the export() method with the export config
$exportManager->export($exportConfig, '.', true);