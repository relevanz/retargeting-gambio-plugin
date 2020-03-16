<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
require_once(__DIR__.'/lib/relevanz/retargeting-base-lib/ClassLoader.php');
Releva\Retargeting\Base\ClassLoader::init()->addPsr4Map([
    'Releva\\Retargeting\\Base\\' => __DIR__.'/lib/relevanz/retargeting-base-lib/',
    'Releva\\Retargeting\\Gambio\\' => __DIR__.'/lib/gambio/',
]);
