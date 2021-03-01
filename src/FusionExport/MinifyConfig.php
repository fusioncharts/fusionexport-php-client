<?php
// Include the library
namespace FusionExport;
use MatthiasMullie\Minify;
use mikehaertl\tmp\File as TmpFile;
use Wa72\HtmlPrettymin\PrettyMin;
use \DOMDocument;
class MinifyConfig
{   
    private function getExtension($files){
        return pathinfo($files->internalPath, PATHINFO_EXTENSION);
    }
    
    function minifyData($files)
    {
        $ext=$this->getExtension($files);
        $minifier = NULL;
        $tmpFile = new TmpFile('', '.' . $ext);
        $tempFile = $tmpFile->getFileName();
        $externalfileContent = file_get_contents($files->externalPath);
        if($ext == 'html') {
            $minifier = new PrettyMin();
            $output = $minifier->load($externalfileContent)->minify()->saveHtml();
            file_put_contents($tempFile, $output);
        } elseif ($ext == 'css') {
            $minifier = new PrettyMin();
            $dom = new DOMDocument();
            $output = $minifier->load($externalfileContent)->minify()->saveHtml();
            $pieces = array_slice(explode("\n", $output), 1);
            $dom->loadHTML($pieces[0]);
            $dom->getElementsByTagName('P');
            file_put_contents($tempFile, $dom->textContent);
            // $minifier = new Minify\CSS($files->externalPath);
            // $minifier->minify($tempFile);
        } elseif ($ext == 'js') {
            $minifier = new Minify\JS($files->externalPath);
            $minifier->minify($tempFile);
        }
        $tempFileContent = file_get_contents($tempFile);
        file_put_contents($files->externalPath, $tempFileContent);
        $files->data = $externalfileContent;
        return $files ;
    }
}
