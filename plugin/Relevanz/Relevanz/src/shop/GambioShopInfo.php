<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
namespace RelevanzTracking\Shop;

use StaticGXCoreLoader;

use RelevanzTracking\Lib\AbstractShopInfo;

class GambioShopInfo extends AbstractShopInfo
{
    const ROUTE_CALLBACK = 'shop.php?do=RelevanzCallback&auth=:auth';
    const ROUTE_EXPORT   = 'shop.php?do=RelevanzExport&auth=:auth';

    /**
     * Technical name of the shop system.
     *
     * @return string
     */
    public static function getShopSystem() {
        return 'gambio';
    }

    /**
     * Version of the shop as a string.
     */
    public static function getShopVersion() {
        return ltrim(gm_get_conf('INSTALLED_VERSION'), 'v');
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
        $db = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $default = [
            'version' => null,
            'server' => null,
        ];
        if (!is_object($db)) {
            return $default;
        }
        // Please make the query() method throw exceptions instead of generating an error and calling exit.
        $foo = $db->simple_query(
            'SELECT @@version AS `version`, @@version_comment AS `server`'
        );
        if (!is_object($foo)) {
            return $default;
        }
        $db->result_id = $foo;
        $dbrd = $db->load_rdriver();
        $res = new $dbrd($db);
        $row = $res->row_array();
        if (is_array($row)) {
            return $row;
        }
        return $default;
    }

    public static function getUrlCallback() {
        return HTTP_SERVER.str_replace('//', '/', DIR_WS_CATALOG.'/'.self::ROUTE_CALLBACK);
    }

    public static function getUrlProductExport() {
        return HTTP_SERVER.str_replace('//', '/', DIR_WS_CATALOG.'/'.self::ROUTE_EXPORT);
    }

}
