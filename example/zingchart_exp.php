<?php

// D3 export

require __DIR__ . '/../vendor/autoload.php';

// Use the sdk
use FusionExport\ExportManager;
use FusionExport\ExportConfig;

// Instantiate the ExportConfig class and add the required configurations
$exportConfig = new ExportConfig();
$exportConfig->set('templateFilePath', realpath('resources/template_zingchart.html'));
$exportConfig->set('type', 'jpg');
$exportConfig->set('asyncCapture', 'true');

// Called on each export state change
$onStateChange = function ($event) {
    $state = $event->state;
    echo('STATE: [' . $state->reporter . '] ' . $state->customMsg . "\n");
};

// Called when export is done
$onDone = function ($event, $e) {
    $export = $event->export;
    if ($e) {
        echo('ERROR: ' . $e->getMessage());
    } else {
        foreach ($export as $file) {
            echo('DONE: ' . $file->realName. "\n");
        }

        ExportManager::saveExportedFiles($export);
    }
};

// Instantiate the ExportManager class
$exportManager = new ExportManager();
// Call the export() method with the export config and the respective callbacks
$exportManager->export($exportConfig, $onDone, $onStateChange);
