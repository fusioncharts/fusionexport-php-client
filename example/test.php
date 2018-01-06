<?php

ini_set('log_errors', 1);
ini_set('display_errors', 0);

require __DIR__ . '/../vendor/autoload.php';

// use FusionExport\ExportConfig;

// $ec = new ExportConfig();
// $key = 'asyncCapture';
// $val = file_get_contents('resources/single.json');

// $ec->set($key, $val);

use FusionExport\TemplateBundler;

$template = 'resources/template.html';
$resource = 'resources/resource.json';

$tmplBundler = new TemplateBundler($template, $resource);
$tmplBundler->process();

$templateZipPath = $tmplBundler->getTemplatePathInZip();
$resourcesZipAsBase64 = $tmplBundler->getResourcesZipAsBase64();

file_put_contents(
    '/Users/jimutdhali/Desktop/resource.zip', 
    base64_decode($resourcesZipAsBase64)
);

echo 'Done';