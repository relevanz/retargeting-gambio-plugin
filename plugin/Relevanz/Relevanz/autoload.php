<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the GNU General Public License (Version 2)
[http://www.gnu.org/licenses/gpl-2.0.html]
--------------------------------------------------------------
*/
require_once(__DIR__.'/src/lib/ClassLoader.php');
RelevanzTracking\Lib\ClassLoader::init()->addClassMap([
    'RelevanzTracking\\GambioConfiguration' => 'src/GambioConfiguration.php',
    'RelevanzTracking\\Lib\\ExportGenerator' => 'src/lib/ExportGenerator.php',
    'RelevanzTracking\\Lib\\CSVWriter' => 'src/lib/CSVWriter.php',
    'RelevanzTracking\\Lib\\CSVExporter' => 'src/lib/CSVExporter.php',
    'RelevanzTracking\\Lib\\JsonExporter' => 'src/lib/JsonExporter.php',
    'RelevanzTracking\\Lib\\Configuration' => 'src/lib/Configuation.php',
    'RelevanzTracking\\Lib\\Credentials' => 'src/lib/Credentials.php',
    'RelevanzTracking\\Lib\\HttpResponse' => 'src/lib/HttpResponse.php',
    'RelevanzTracking\\Lib\\RelevanzApi' => 'src/lib/RelevanzApi.php',
    'RelevanzTracking\\Lib\\RelevanzException' => 'src/lib/RelevanzException.php',
    'RelevanzTracking\\Lib\\RelevanzExceptionMessage' => 'src/lib/RelevanzExceptionMessage.php',
], __DIR__);
