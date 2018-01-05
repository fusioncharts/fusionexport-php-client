<?php

namespace FusionExport;

use PHPHtmlParser\Dom;

class TemplatePackager
{
    public static function getZip($template, $resources)
    {
        $paTemplate = TemplatePackager::parseTemplate($template);
        $paResources = TemplatePackager::parseTemplate($resources);

        $resourceBag = TemplatePackager::findResources($paTemplate, $paResources);

        TemplatePackager::generateResourceData($resourceBag, $template);
    }

    private static function parseTemplate($template)
    {
        if (!is_set($template)) {
            throw new Exception('TemplateFilePath is required.');
        }

        if (!is_string($template)) {
            throw new Exception('TemplateFilePath must be a string.');
        }

        return file_get_contents($template);
    }

    private static function parseResources($resources)
    {
        if (!is_set($resources)) {
            return;
        }

        if (!is_string($resources)) {
            throw new Exception('ResourceFilePath must be a string.');
        }

        return json_decode(file_get_contents($template));
    }

    private static function findResources($template, $resources)
    {
        $dom = new Dom();
        $dom->load($template);

        $links = $dom->find('link');
        $scripts = $dom->find('script');
        $imgs = $dom->find('img');

        $links = array_map(function ($link) {
            $link->getAttribute('href');
        }, $links);

        $scripts = array_map(function ($script) {
            $script->getAttribute('src');
        }, $scripts);

        $imgs = array_map(function ($img) {
            $img->getAttribute('src');
        }, $imgs);

        $resourcesBag = [
            'images' => $imgs,
            'stylesheets' => $links,
            'javascripts' => $scipts,
        ];

        if (!is_set($resources)) {
            return $resourcesBag;
        }

        array_walk($resourcesBag, function (&$val, $key) {
            if (array_key_exists($key, $resources)) {
                $uval = $resources[$key];
            } else {
                $uval = [];
            }

            $val = array_unique(array_merge($val, $uval));
        });

        return $resourcesBag;
    }

    private static function removeRemoteResources() 
    {

    }

    private static function generateResourceData($resourceBag, $template)
    {
        if (!is_set($resourceBag)) {
            return;
        }

        $resourceBag = TemplatePackager::removeRemoteResources($resourceBag);

        $base = dirname(realpath($template));

        array_walk($resourceBag, function (&$val, $key) {
            
        });
    }
}