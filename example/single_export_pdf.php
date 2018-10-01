<?php

// Changing the export type

require __DIR__ . '/../vendor/autoload.php';
// Use the sdk
use FusionExport\ExportManager;
use FusionExport\ExportConfig;

// Instantiate the ExportConfig class and add the required configurations
$exportConfig = new ExportConfig();
$exportConfig->set('chartConfig', realpath('resources/single.json'));
$exportConfig->set('type', 'pdf');

// Instantiate the ExportManager class
$exportManager = new ExportManager();

$exportManager->export($exportConfig, '.', true);
