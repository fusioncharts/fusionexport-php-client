<?php

ini_set('log_errors', 1);
ini_set('display_errors', 0);

require __DIR__ . '/../vendor/autoload.php';

// use FusionExport\Helpers;

// $p = 'usr/local/foo.jpg';
// $b = 'usr/local';

// $isChildPath = Helpers::isChildPath($p, $b);


// use FusionExport\TemplateBundler;

// $template = 'resources/template.html';
// $resource = 'resources/resource.json';

// $tmplBundler = new TemplateBundler($template, $resource);
// $tmplBundler->process();

// $templateZipPath = $tmplBundler->getTemplatePathInZip();
// $resourcesZipAsBase64 = $tmplBundler->getResourcesZipAsBase64();

// file_put_contents(
//     '/Users/jimutdhali/Desktop/resource.zip', 
//     base64_decode($resourcesZipAsBase64)
// );


use FusionExport\ExportConfig;
use FusionExport\ExportManager;

$ec = new ExportConfig();

$ec->set('chartConfig', 'resources/multiple.json');
$ec->set('templateFilePath', 'resources/template.html');
$ec->set('resourceFilePath', 'resources/resource.json');
$ec->set('dashboardHeading', "Hey there\n col\"gn");

// $payload = $ec->getFormattedConfigs();
// echo($payload);

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
$exportManager->export($ec, $onDone, $onStateChange);
  

echo('Done');