<?php

// Export in Bulk

require __DIR__ . '/../vendor/autoload.php';

// Use the sdk
use FusionExport\ExportManager;
use FusionExport\ExportConfig;

// Instantiate the ExportConfig class and add the required configurations
$exportConfig = new ExportConfig();
$exportConfig->set('chartConfig', realpath('resources/multiple.json'));
$exportConfig->set('outputFile', 'php-export-<%= number(5) %>');

// Instantiate the ExportManager class
$exportManager = new ExportManager();
// Call the export() method with the export config
$exportManager->export($exportConfig, '.', true);
