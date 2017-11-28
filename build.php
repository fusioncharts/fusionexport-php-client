<?php

$src = [
    'src/FusionExport/Constants.php',
    'src/FusionExport/ExportConfig.php',
    'src/FusionExport/Exporter.php',
    'src/FusionExport/ExportManager.php',
];

$dest = 'build/FusionExport.php';

function delete_files($target) {
    if(is_dir($target)){
        $files = glob($target . '/*', GLOB_MARK);

        foreach ($files as $file) {
            delete_files( $file );
        }

        rmdir($target);
    } elseif(is_file($target)) {
        unlink($target);
    }
}

function remove_line($content, $line) {
    echo('Removing ' . $line . "\n");
    $start = strpos($content, $line);
    if ($start !== false) {
        $end = $start;
        while (substr($content, $end, 1) !== "\n") {
            $end++;
        }
        $content = str_replace(substr($content, $start, $end), '', $content);
    }
    return $content;
}

$targetLines = [
    '<?php',
    'namespace',
];

delete_files(dirname($dest));
mkdir(dirname($dest), 0777, true);

foreach ($src as $index => $file) {
    $content = file_get_contents($file);

    echo('Processing ' . $file . "\n");
    if ($index > 0) {
        foreach($targetLines as $line) {
            while (strpos($content, $line) !== false) {
                $content = remove_line($content, $line);
            }
        }
    }

    file_put_contents($dest, $content, FILE_APPEND);
}
