<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
namespace Releva\Retargeting\Base;

abstract class AbstractShopInfo
{
    /**
     * Technical name of the shop system.
     *
     * @return string
     */
    abstract public static function getShopSystem();

    /**
     * Version of the shop as a string.
     */
    public static function getShopVersion() {
        return 'unknown';
    }

    public static function getPhpVersion() {
        return [
            'version' => phpversion(),
            'sapi-name' => php_sapi_name(),
            'memory-limit' => ini_get('memory_limit'),
            'max-execution-time' => ini_get('max_execution_time'),
        ];
    }

    /**
     * Basically the result of the following sql query:
     *    SELECT @@version AS `version`, @@version_comment AS `server`
     */
    public static function getDbVersion() {
        return [
            'version' => null,
            'server' => null,
        ];
    }

    public static function getServerEnvironment() {
        return [
            'server-software' => isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : null,
            'php' => static::getPhpVersion(),
            'db' => static::getDbVersion(),
        ];
    }

    /**
     * @return string
     */
    abstract public static function getUrlCallback();

    /**
     * @return string
     */
    abstract public static function getUrlProductExport();

}
