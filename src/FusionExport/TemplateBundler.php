<?php

namespace FusionExport;

use PHPHtmlParser\Dom;

class TemplateBundler
{
    public function __construct($template, $resources = null)
    {
        $this->template = $template;
        $this->resources = $resources;
    }

    public function process()
    {
        if (!isset($this->template)) return;
            
        $this->findResources();
        
        $this->parseResources();

        $this->sanitizeBasePath();

        $this->zipResourcesFiles();
    }

    public function getTemplatePathInZip()
    {
        return $this->templatePathInZip;
    }

    public function getResources() 
    {
        return $this->resourcesZipFile;
    }

    public function getResourcesZipAsBase64() 
    {
        return base64_encode(file_get_contents($this->getResources()));
    }

    private function findResources()
    {
        $dom = new Dom();
        $dom->setOptions([ 
            'removeScripts' => false,
        ]);

        $dom->load(file_get_contents($this->template));

        $links = $dom->find('link')->toArray();
        $scripts = $dom->find('script')->toArray();
        $imgs = $dom->find('img')->toArray();

        $links = array_map(function ($link) {
            return $link->getAttribute('href');
        }, $links);

        $scripts = array_map(function ($script) {
            return $script->getAttribute('src');
        }, $scripts);

        $imgs = array_map(function ($img) {
            return $img->getAttribute('src');
        }, $imgs);

        $this->collectedResources = array_merge($links, $scripts, $imgs);

        $this->collectedResources = Helpers::resolvePaths(
            $this->collectedResources, 
            dirname(realpath($this->template))
        );

        $this->collectedResources = array_unique($this->collectedResources);
        
        $this->removeRemoteResources();
    }

    private function removeRemoteResources() 
    {
        $this->collectedResources = array_filter(
            $this->collectedResources, 
            function ($res) {
                if (Helpers::startsWith($res, 'http://')) return false;

                if (Helpers::startsWith($res, 'https://')) return false;

                if (Helpers::startsWith($res, 'file://')) return false;

                return true;
            }
        );
    }

    private function parseResources()
    {
        if (!isset($this->resources)) return;

        $this->resourcesData = json_decode(file_get_contents($this->resources));
    }

    private function sanitizeBasePath()
    {
        if (isset($this->resourcesData)) {
            $this->basePath = $this->resourcesData->basePath;
        } else {
            $this->basePath = Helpers::findCommonPath($this->collectedResources);
        }

        $this->basePath = realpath($this->basePath);
    }

    private function generateZipFiles()
    {
        $files = [];

        if (isset($this->resourcesData)) {
            $includeFiles = [];
            $excludeFiles = [];

            $templateDir = dirname(realpath($this->template));

            if (isset($this->resourcesData->include)) {
                $includeFiles = Helpers::globResolve(
                    $this->resourcesData->include, 
                    $templateDir
                );
            }

            if (isset($this->resourcesData->exclude)) {
                $excludeFiles = Helpers::globResolve(
                    $this->resourcesData->exclude, 
                    $templateDir
                );
            }

            $files = array_filter($includeFiles, function ($file) use ($excludeFiles) {
                if (in_array($file, $excludeFiles)) return false;
                return true;
            });
        }

        $files = array_merge($files, $this->collectedResources);

        $files = array_map(function ($file) {
            return [
                'path' => $file,
                'zipPath' => Helpers::removeCommonPath($file, $this->basePath)
            ];
        }, $files);

        $this->resourcesFiles = $files;

        $this->insertTemplateInZip();
    }

    private function insertTemplateInZip()
    {
        $absTemplatePath = realpath($this->template);

        if (count($this->resourcesFiles) === 0) {
            $this->templatePathInZip = basename($this->template);
        } else {
            $this->templatePathInZip = Helpers::removeCommonPath(
                $absTemplatePath, 
                $this->basePath
            );
        }

        $this->resourcesFiles[] = [
            'path' => $absTemplatePath,
            'zipPath' => $this->templatePathInZip,
        ];
    }

    public function zipResourcesFiles()
    {
        $this->generateZipFiles();

        $zip = new \ZipArchive();
        $this->resourcesZipFile = stream_get_meta_data(tmpfile())['uri'];

        $zip->open($this->resourcesZipFile, \ZipArchive::CREATE);

        foreach ($this->resourcesFiles as $file) {
            $zip->addFile($file['path'], $file['zipPath']);
        }
        
        $zip->close();
    }
}