<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
namespace Releva\Retargeting\Gambio;

use DateTime;

use StaticGXCoreLoader;

use Releva\Retargeting\Base\ConfigurationInterface;
use Releva\Retargeting\Base\Credentials;

class Configuration implements ConfigurationInterface
{
    const PLUGIN_VERSION = '1.2.0';
    const CONF_APIKEY = 'RELEVANZ_APIKEY';
    const CONF_USERID = 'RELEVANZ_USERID';

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
            $now = new DateTime();
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
