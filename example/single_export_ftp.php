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
        ExportManager::uploadToFTP(__FTP_HOST, __FTP_PORT, __FTP_LOGIN_USERNAME, __FTP_LOGIN_PASSWORD, 'exported_images', $export);
    }
};

// Instantiate the ExportManager class
$exportManager = new ExportManager();
// Call the export() method with the export config and the respective callbacks
$exportManager->export($exportConfig, $onDone, $onStateChange);
