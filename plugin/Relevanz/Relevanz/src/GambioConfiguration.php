<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the GNU General Public License (Version 2)
[http://www.gnu.org/licenses/gpl-2.0.html]
--------------------------------------------------------------
*/
namespace RelevanzTracking;

use RelevanzTracking\Lib\Credentials;
use StaticGXCoreLoader;

class GambioConfiguration implements Lib\Configuration
{
    const PLUGIN_VERSION = '1.1.0';
    const CONF_APIKEY = 'RELEVANZ_APIKEY';
    const CONF_USERID = 'RELEVANZ_USERID';

    const ROUTE_CALLBACK = 'shop.php?do=RelevanzCallback&auth=:auth';
    const ROUTE_EXPORT   = 'shop.php?do=RelevanzExport&auth=:auth';

    public static function getUrlCallback() {
        return str_replace('//', '/', HTTP_SERVER . DIR_WS_CATALOG . '/' . self::ROUTE_CALLBACK);
    }

    public static function getUrlExport() {
        return str_replace('//', '/', HTTP_SERVER . DIR_WS_CATALOG . '/' . self::ROUTE_EXPORT);
    }

    public static function getCredentials() {
        $qb = StaticGXCoreLoader::getDatabaseQueryBuilder();

        $row = $qb->select('*')->from(TABLE_CONFIGURATION)
            ->where('configuration_key', self::CONF_APIKEY)->get()->row_array();
        $apikey = (isset($row['configuration_value']) && !empty($row['configuration_value']))
            ? $row['configuration_value']
            : '';

        $row = $qb->select('*')->from(TABLE_CONFIGURATION)
            ->where('configuration_key', self::CONF_USERID)->get()->row_array();
        $userid = (isset($row['configuration_value']) && !empty($row['configuration_value']))
            ? (int)$row['configuration_value']
            : 0;

        return new Credentials($apikey, $userid);
    }

    public static function updateCredentials(Credentials $credentials) {
        $qb = StaticGXCoreLoader::getDatabaseQueryBuilder();

        $configRows = $qb->select('*')->from(TABLE_CONFIGURATION)
            ->where('configuration_key', self::CONF_APIKEY)
            ->get()->num_rows();

        if ($configRows === 0) {
            $now = new \DateTime();
            $qb->insert(TABLE_CONFIGURATION, [
                'configuration_key'   => self::CONF_APIKEY,
                'configuration_value' => $credentials->getApiKey(),
                'sort_order' => 0,
                'date_added' => $now->format('Y-m-d H:i:s'),
            ]);
            $qb->insert(TABLE_CONFIGURATION, [
                'configuration_key'   => self::CONF_USERID,
                'configuration_value' => $credentials->getUserId(),
                'sort_order' => 0,
                'date_added' => $now->format('Y-m-d H:i:s'),
            ]);
        } else {
            $qb->where('configuration_key', self::CONF_APIKEY)
                ->update(TABLE_CONFIGURATION, ['configuration_value' => $credentials->getApiKey()]);
            $qb->where('configuration_key', self::CONF_USERID)
                ->update(TABLE_CONFIGURATION, ['configuration_value' => $credentials->getUserId()]);
        }
    }

    public static function getPluginVersion() {
        return self::PLUGIN_VERSION;
    }

}
