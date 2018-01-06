<?php 

namespace FusionExport;

class Helpers
{
    public static function startsWith($string, $query)
    {
        return substr($string, 0, strlen($query)) === $query;
    }

    public static function findCommonPath($paths) {
        sort($paths, SORT_STRING);
        if (count($paths) === 0) return '';
        $paths = array_map(function ($pa) {
            return explode(DIRECTORY_SEPARATOR, $pa);
        }, $paths);
        $p1 = $paths[0];
        $p2 = $paths[count($paths) - 1];
        $l = count($p1);
        $i = 0;
        while ($i < $l && @$p1[$i] === @$p2[$i]) $i += 1;
        return implode(DIRECTORY_SEPARATOR, array_slice($p1, 0, $i));
    }
      
    public static function removeCommonPath($path, $base) {
        $pathSpl = explode(DIRECTORY_SEPARATOR, $path);
        $baseSpl = explode(DIRECTORY_SEPARATOR, $base);
        $l = count($pathSpl);
        $i = 0;
        while ($i < $l && @$pathSpl[$i] === @$baseSpl[$i]) $i += 1;
        return implode(DIRECTORY_SEPARATOR, array_slice($pathSpl, $i));
    }

    public static function resolvePaths($paths, $base)
    {
        if (count($paths) === 0) return [];

        $cwd = getcwd();
        $basePath = realpath($base);
        
        chdir($basePath);

        $resolvedPaths = array_map(function ($p) {
            return realpath($p);
        }, $paths);

        chdir($cwd);

        return $resolvedPaths;
    }

    public static function globResolve($paths, $base)
    {
        if (count($paths) === 0) return [];

        $cwd = getcwd();
        $basePath = realpath($base);
        
        chdir($basePath);

        $resolvedPaths = array_map(function ($p) {
            return glob($p);
        }, $paths);

        $resolvedPaths = array_merge(...$resolvedPaths);

        $resolvedPaths = array_map(function ($p) {
            return realpath($p);
        }, $resolvedPaths);

        chdir($cwd);

        return $resolvedPaths;
    }
}