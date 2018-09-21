<?php

// D3 export

require __DIR__ . '/../vendor/autoload.php';

// Use the sdk
use FusionExport\ExportManager;
use FusionExport\ExportConfig;

// Instantiate the ExportConfig class and add the required configurations
$exportConfig = new ExportConfig();
$exportConfig->set('templateFilePath', realpath('resources/template_d3.html'));
$exportConfig->set('type', 'jpg');
$exportConfig->set('asyncCapture', 'true');


// Instantiate the ExportManager class
$exportManager = new ExportManager();

$exportManager->export($exportConfig);
