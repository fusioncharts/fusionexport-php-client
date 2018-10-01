<?php

// Exporting a chart

require __DIR__ . '/vendor/autoload.php';

// Use the sdk
use FusionExport\ExportManager;
use FusionExport\ExportConfig;

define("__FTP_HOST", "");
define("__FTP_PORT", 21);
define("__FTP_LOGIN_USERNAME", "");
define("__FTP_LOGIN_PASSWORD", "");

// Instantiate the ExportConfig class and add the required configurations
$exportConfig = new ExportConfig();
$exportConfig->set('chartConfig', realpath('resources/single.json'));

// Instantiate the ExportManager class
$exportManager = new ExportManager();
// Call the export() method with the export config
$exportManager->export($exportConfig, '.', true);