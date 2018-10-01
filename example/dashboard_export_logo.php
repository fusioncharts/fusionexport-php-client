<?php

// Adding a logo or heading to the dashboard

require __DIR__ . '/../vendor/autoload.php';

// Use the sdk
use FusionExport\ExportManager;
use FusionExport\ExportConfig;

// Instantiate the ExportConfig class and add the required configurations
$exportConfig = new ExportConfig();
$exportConfig->set('chartConfig', realpath('resources/multiple.json'));
$exportConfig->set('templateFilePath', realpath('resources/template.html'));
$exportConfig->set('dashboardLogo', realpath('resources/logo.jpg'));
$exportConfig->set('dashboardHeading', 'FusionCharts');
$exportConfig->set('dashboardSubheading', 'The best charting library in the world');

// Instantiate the ExportManager class
$exportManager = new ExportManager();
// Call the export() method with the export config
$exportManager->export($exportConfig, '.', true);
