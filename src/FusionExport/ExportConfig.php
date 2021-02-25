<?php

namespace FusionExport;

use FusionExport\Converters\NumberConverter;
use FusionExport\Converters\BooleanConverter;
use FusionExport\Converters\EnumConverter;
use FusionExport\Converters\ChartConfigConverter;
use FusionExport\Converters\ObjectConverter;
use FusionExport\Exceptions\InvalidConfigurationException;
use FusionExport\Exceptions\InvalidDataTypeException;
use PHPHtmlParser\Dom;
use mikehaertl\tmp\File as TmpFile;
use \DOMDocument;

class ResourcePathInfo
{
    public $internalPath;
    public $externalPath;
}

class ExportConfig
{
    protected $configs;

    public function __construct()
    {
        $this->typingsFile = __DIR__ . '/../config/fusionexport-typings.json';
        $this->configs = [];
        $this->formattedConfigs = [];

        $this->readTypingsConfig();
        $this->collectedResources = array();
    }

    public function set($name, $value)
    {
        $parsedValue = $this->parseConfig($name, $value);

        $this->configs[$name] = $parsedValue;

        return $this;
    }

    public function get($name)
    {
        return $this->configs[$name];
    }

    public function remove($name)
    {
        unset($this->configs[$name]);
        return $this;
    }

    public function has($name)
    {
        return array_key_exists($name, $this->configs);
    }

    public function clear()
    {
        $this->configs = [];
    }

    public function count()
    {
        return count($this->configs);
    }

    public function configNames()
    {
        return array_keys($this->configs);
    }

    public function configValues()
    {
        return array_values($this->configs);
    }

    public function cloneConfig()
    {
        $newExportConfig = new ExportConfig();

        foreach ($this->configs as $key => $value) {
            $newExportConfig->set($key, $value);
        }

        return $newExportConfig;
    }

    public function getFormattedConfigs($exportBulk=true)
    {
        $this->formatConfigs($exportBulk);
        return $this->formattedConfigs;
    }

    private function parseConfig($name, $value)
    {
        if (!property_exists($this->typings, $name)) {
            throw new InvalidConfigurationException($name);
        }

        $supportedTypes = $this->typings->$name->supportedTypes;

        $isSupported = false;
        foreach ($supportedTypes as $supportedType) {
            if (gettype($value) === $supportedType) {
                $isSupported = true;
                break;
            }
        }

        if (!$isSupported) {
            throw new InvalidDataTypeException($name, $value, $supportedTypes);
        }

        $parsedValue = $value;

        if (property_exists($this->typings->$name, 'converter')) {
            $converter = $this->typings->$name->converter;

            if ($converter === 'ChartConfigConverter') {
                $parsedValue = ChartConfigConverter::convert($value);
            } elseif ($converter === 'BooleanConverter') {
                $parsedValue = BooleanConverter::convert($value);
            } elseif ($converter === 'ObjectConverter') {
                $parsedValue = ObjectConverter::convert($value);
            } elseif ($converter === 'NumberConverter') {
                $parsedValue = NumberConverter::convert($value);
            } elseif ($converter === 'EnumConverter') {
                $dataset = $this->typings->$name->dataset;
                $parsedValue = EnumConverter::convert($value, $dataset);
            }
        }

        return $parsedValue;
    }

    private function formatConfigs($exportBulk=true)
    {
        if (isset($this->configs['templateFilePath']) && isset($this->configs['template'])) {
            print("Both 'templateFilePath' and 'template' is provided. 'templateFilePath' will be ignored.\n");
            unset($this->configs['templateFilePath']);
        }

        $zipBag = array();

        foreach ($this->configs as $key=> $value) {
            switch ($key) {
                case "chartConfig":
                    if (Helpers::endswith($this->configs['chartConfig'], '.json')) {
                        $this->formattedConfigs['chartConfig'] = Helpers::readFile($this->configs['chartConfig']);
                    } else {
                        $this->formattedConfigs['chartConfig'] = $this->configs['chartConfig'];
                    }
                    break;
                case "inputSVG":
                    $obj = new ResourcePathInfo;
                    $internalFilePath = "inputSVG.svg";
                    $obj->internalPath = $internalFilePath;
                    $obj->externalPath = $this->configs['inputSVG'];
                    $this->formattedConfigs['inputSVG'] = $internalFilePath;
                    array_push($zipBag, $obj);
                    break;
                case "callbackFilePath":
                    $obj = new ResourcePathInfo;
                    $internalFilePath = "callbackFile.js";
                    $this->formattedConfigs['callbackFilePath'] = $internalFilePath;
                    $obj->internalPath = $internalFilePath;
                    $obj->externalPath = $this->configs['callbackFilePath'];
                    array_push($zipBag, $obj);
                    break;
                case "dashboardLogo":
                    $obj = new ResourcePathInfo;
                    $internalFilePath = "logo." . pathinfo($this->configs['dashboardLogo'], PATHINFO_EXTENSION);
                    $obj->internalPath = $internalFilePath;
                    $obj->externalPath = $this->configs['dashboardLogo'];
                    $this->formattedConfigs['dashboardLogo'] = $internalFilePath;
                    array_push($zipBag, $obj);
                    break;
                case "templateFilePath":
                    $templatePathWithinZip = '';
                    $zipPaths = array();
                    $this->createTemplateZipPaths($zipPaths, $templatePathWithinZip);
                    $this->formattedConfigs['templateFilePath'] = $templatePathWithinZip;
                    foreach ($zipPaths as $path) {
                        array_push($zipBag, $path);
                    }
                    break;
                case "outputFileDefinition":
                    $this->formattedConfigs['outputFileDefinition'] = Helpers::readFile($this->configs['outputFileDefinition']);
                    break;
                case "asyncCapture":
                    if (empty($this->configs['asyncCapture']) < 1) {
                        if (strtolower($this->configs['asyncCapture']) == "true") {
                            $this->formattedConfigs['asyncCapture'] = "true";
                        }
                    }
                    break;
                default:
                    $this->formattedConfigs[$key] = $this->configs[$key];
            }
        }

        if (count($zipBag) > 0) {
            $zipFile = $this->generateZip($zipBag);
            $this->formattedConfigs['payload'] = $zipFile;
        }

        $platform = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'win32' : PHP_OS;

        $this->formattedConfigs['platform'] = $platform;
        $this->formattedConfigs['clientName'] = 'PHP';
        if(!$exportBulk) $this->formattedConfigs['exportBulk'] = '';
    }

    private function createTemplateZipPaths(&$outZipPaths, &$outTemplatePathWithinZip)
    {
        $templatePathWithinZip ='';
        $listExtractedPaths = array();
        $listExtractedPaths = $this->findResources();
        $listResourcePaths = array();
        $baseDirectoryPath = null;
        if (isset($this->configs['resourceFilePath'])) {
            Helpers::globResolve($listResourcePaths, $baseDirectoryPath, $this->configs[resourceFilePath]);
        }
        $templateFilePath = realpath($this->configs['templateFilePath']);
        if (!isset($baseDirectoryPath)) {
            array_push($listExtractedPaths, $templateFilePath);
            $commonDirectoryPath = Helpers::findCommonPath($listExtractedPaths);
            if (isset($commonDirectoryPath)) {
                $baseDirectoryPath = $commonDirectoryPath;
            }
            if (strlen($baseDirectoryPath) == 0) {
                $baseDirectoryPath = dirname($templateFilePath);
            }
        }
        $mapExtractedPathAbsToRel = array();
        foreach ($listExtractedPaths as $tmpPath) {
            $mapExtractedPathAbsToRel[$tmpPath] = Helpers::removeCommonPath($tmpPath, $baseDirectoryPath);
        }
        foreach ($listResourcePaths as $tmpPath) {
            $mapExtractedPathAbsToRel[$tmpPath] = Helpers::removeCommonPath($tmpPath, $baseDirectoryPath);
        }
        $templateFilePathWithinZipRel = Helpers::removeCommonPath($templateFilePath, $baseDirectoryPath);
        $mapExtractedPathAbsToRel[$templateFilePath] = $templateFilePathWithinZipRel;
        $zipPaths = array();
        $zipPaths = $this->generatePathForZip($mapExtractedPathAbsToRel, $baseDirectoryPath);
        $templatePathWithinZip = $templatePathWithinZip . DIRECTORY_SEPARATOR . $templateFilePathWithinZipRel;
        $outZipPaths = $zipPaths;
        $outTemplatePathWithinZip = $templatePathWithinZip;
    }

    private function findResources()
    {
        $links=array();
        $scripts=array();
        $imgs=array();
        $dom = new DOMDocument();
        
        $regex = '~url\(([^\)]+?\.(woff|eot|woff2|ttf|svg|otf)[^)]*)~';
        @$dom->loadHTML(Helpers::readFile($this->configs['templateFilePath']));
        $html = @$dom->saveHTML();
        if($html){
            preg_match_all($regex, $html, $matches,PREG_SET_ORDER);
            foreach($matches as $match){
                if($match[1]){
                    $links[] = str_replace(array("'", "\"", "&quot;"), "", htmlspecialchars($match[1]));
                }
            }
        } 
        
        foreach(@$dom->getElementsByTagName('link') as $node){
            $href = $node->getAttribute('href');
            if($href){
                $links[] = $href;
                $resolvedHref = Helpers::resolvePaths(
                    [$href],
                    dirname(realpath($this->configs['templateFilePath']))
                );
                if(sizeof($resolvedHref) > 0){
                    $css = Helpers::readFile($resolvedHref[0]);
                    preg_match_all($regex, $css, $matches,PREG_SET_ORDER);
                    foreach($matches as $match){
                        if($match[1]){
                            $links[] = str_replace(array("'", "\"", "&quot;"), "", htmlspecialchars($match[1]));
                        }
                    }
                }
            }
        }

        foreach(@$dom->getElementsByTagName('script') as $node){
            $scriptSrc = $node->getAttribute('src');
            if($scriptSrc){
                $scripts[] = $scriptSrc;
            }
        }

        foreach(@$dom->getElementsByTagName('img') as $node){
            $imgSrc = $node->getAttribute('src');
            if($imgSrc){
                $imgs[] = $imgSrc;
            }
        }

        $this->collectedResources = array_merge($links, $scripts, $imgs);

        $this->removeRemoteResources();

        $this->collectedResources = Helpers::resolvePaths(
            $this->collectedResources,
            dirname(realpath($this->configs['templateFilePath']))
        );
        $this->collectedResources = array_unique($this->collectedResources);

        return $this->collectedResources;
    }

    private function removeRemoteResources()
    {
        $this->collectedResources = array_filter(
            $this->collectedResources,
            function ($res) {
                if (Helpers::startsWith($res, 'http://')) {
                    return false;
                }

                if (Helpers::startsWith($res, 'https://')) {
                    return false;
                }

                if (Helpers::startsWith($res, 'file://')) {
                    return false;
                }

                return true;
            }
        );
    }

    private function generatePathForZip($listAllFilePaths, $baseDirectoryPath)
    {
        $listFilePath = array();
        foreach ($listAllFilePaths as $key => $value) {
            $obj = new ResourcePathInfo;
            $obj->internalPath = $value;
            $obj->externalPath = $key;
            array_push($listFilePath, $obj);
        }
        return $listFilePath;
    }

    private function generateZip($fileBag)
    {
        $tmpFile = new TmpFile('', '.zip');
        $tmpFile->delete = false;
        $fileName = $tmpFile->getFileName();

        $zipFile = new \ZipArchive();
        $zipFile->open($fileName, \ZipArchive::OVERWRITE);
        foreach ($fileBag as $files) {
            if (strlen((string)$files->internalPath) > 0 && strlen((string)$files->externalPath) > 0) {
                $zipFile->addFile($files->externalPath, $files->internalPath);
            }
        }
        $zipFile->close();
        return $fileName;
    }

    private function readTypingsConfig()
    {
        $this->typings = json_decode(Helpers::readFile($this->typingsFile));
    }
}
