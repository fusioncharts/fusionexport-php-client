# FusionCharts PHP Export Client

This is a PHP Export Client for FC Export Service. It communicates with Export Service through the socket protocol and does the export.

## Installation

```
composer require fusioncharts/fusionexport-php-client
```

## Usage

Everything can be accessed from the `FusionExport` namespace.

You can use the `ExportConfig` class to build the export config for each export.

Build a simple export config

```php
$exportConfig = new ExportConfig();
$exportConfig->set('chartConfig', file_get_contents('single.json'));
```

Use the `ExportManager` class to export multiple charts.

```php
$exportManager = new ExportManager();
$exportManager->export($exportConfig, $onDone, $onStateChange);
```

The format of the `Export` function is

```php
public function export(ExportConfig exportConfig, exportDone, exportStateChanged)
```

`exportDone` callback gets an array of abject which contains the temporary file path and the resolved name of the file as specified in the `output-file` option of the config.

`exportStateChanged` callback gets an object which contains the state of the export on each progress event.
