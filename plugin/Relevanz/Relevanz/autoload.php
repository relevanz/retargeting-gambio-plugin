<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
require_once(__DIR__.'/src/lib/ClassLoader.php');
RelevanzTracking\Lib\ClassLoader::init()->addClassMap([
    'RelevanzTracking\\Lib\\Exception\\RelevanzException' => 'src/lib/exception/RelevanzException.php',
    'RelevanzTracking\\Lib\\Exception\\RelevanzExceptionMessage' => 'src/lib/exception/RelevanzExceptionMessage.php',

    'RelevanzTracking\\Lib\\AbstractShopInfo' => 'src/lib/AbstractShopInfo.php',
    'RelevanzTracking\\Lib\\ConfigurationInterface' => 'src/lib/ConfigurationInterface.php',
    'RelevanzTracking\\Lib\\Credentials' => 'src/lib/Credentials.php',
    'RelevanzTracking\\Lib\\HttpResponse' => 'src/lib/HttpResponse.php',
    'RelevanzTracking\\Lib\\RelevanzApi' => 'src/lib/RelevanzApi.php',

    'RelevanzTracking\\Lib\\Export\\Item\\ExportItemInterface' => 'src/lib/Export/Item/ExportItemInterface.php',
    'RelevanzTracking\\Lib\\Export\\Item\\ProductExportItem' => 'src/lib/Export/Item/ProductExportItem.php',
    'RelevanzTracking\\Lib\\Export\\ExporterInterface' => 'src/lib/Export/ExporterInterface.php',
    'RelevanzTracking\\Lib\\Export\\Helper\\CsvWriter' => 'src/lib/Export/Helper/CsvWriter.php',
    'RelevanzTracking\\Lib\\Export\\AbstractCsvExporter' => 'src/lib/Export/AbstractCsvExporter.php',
    'RelevanzTracking\\Lib\\Export\\AbstractJsonExporter' => 'src/lib/Export/AbstractJsonExporter.php',
    'RelevanzTracking\\Lib\\Export\\ProductCsvExporter' => 'src/lib/Export/ProductCsvExporter.php',
    'RelevanzTracking\\Lib\\Export\\ProductJsonExporter' => 'src/lib/Export/ProductJsonExporter.php',

    'RelevanzTracking\\Shop\\GambioConfiguration' => 'src/shop/GambioConfiguration.php',
    'RelevanzTracking\\Shop\\GambioShopInfo' => 'src/shop/GambioShopInfo.php',
], __DIR__);
