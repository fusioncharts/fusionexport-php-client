<?php

// Injecting custom JavaScript while exporting

require __DIR__ . '/../vendor/autoload.php';
// Use the sdk
use FusionExport\ExportManager;
use FusionExport\ExportConfig;

// Instantiate the ExportConfig class and add the required configurations
$exportConfig = new ExportConfig();
$exportConfig->set('chartConfig', realpath('resources/multiple.json'));
$exportConfig->set('templateFilePath', realpath('resources/template.html'));
$exportConfig->set('callbackFilePath', realpath('resources/callback.js'));

// Instantiate the ExportManager class
$exportManager = new ExportManager();
// Call the export() method with the export config and the respective callbacks
$exportManager->export($exportConfig, '.', true);
