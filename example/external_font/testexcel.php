<?php

// Exporting a chart

require __DIR__ . '/../../vendor/autoload.php';

// Use the sdk
use FusionExport\ExportManager;
use FusionExport\ExportConfig;

$arr = '[
    {
    "type": "column2d",
    "renderAt": "chart-container",
    "width": "600",
    "height": "400",
    "dataFormat": "json",
    "dataSource": {
        "chart": {
            "caption": "Number of visitors last week",
            "subCaption": "Bakersfield Central vs Los Angeles Topanga"
        },
        "data": [{
                "label": "Mon",
                "value": "15123"
            },{
                "label": "Tue",
                "value": "14233"
            },{
                "label": "Wed",
                "value": "25507"
            }
        ]
    }
}]';

// Instantiate the ExportConfig class and add the required configurations
$exportConfig = new ExportConfig();
$exportConfig->set('chartConfig', $arr);
$exportConfig->set('type', 'xlsx');

// Instantiate the ExportManager class
$exportManager = new ExportManager();

try {
    // Call the export() method with the export config
    $files = $exportManager->export($exportConfig, '.', true);
} catch (\Exception $err) {
    echo $err->getMessage();
    exit();
}

foreach ($files as $file) {
    echo $file . "\n";
}
