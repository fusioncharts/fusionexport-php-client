<?php
// Include the library
namespace FusionExport;

require_once __DIR__ . '/../../vendor/shinsenter/defer.php/defer.php';
class MinifyConfig
{
    function minifyData($files)
    {

        $newPath = $files->externalPath;
        

        // Create a Defer object
        $defer = new \AppSeeds\Defer();
        $defer->manually_add_deferjs = true;

        // Then get the optimized output
        $result = $defer->fromHtml(file_get_contents($files->externalPath))->toHtml();
        //var_dump($result);
        $data_html = file_get_contents($files->externalPath);
        $files->data_html = $data_html;
        if (file_put_contents($newPath, $result) !== false) echo "" ;


       return $files ;
    }
}
