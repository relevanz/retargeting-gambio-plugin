<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
namespace Releva\Retargeting\Gambio;

use DateTime;

use Gambio\Core\Configuration\ConfigurationService as GmConfigurationService;
use Gambio\Core\Configuration\Models\Write\Configuration as GmConfigurationWrite;

use Releva\Retargeting\Base\ConfigurationInterface;
use Releva\Retargeting\Base\Credentials;

class Configuration implements ConfigurationInterface
{
    const PLUGIN_VERSION = '1.2.3';
    const CONF_PREFIX = 'configuration/';
    const CONF_APIKEY = 'RELEVANZ_APIKEY';
    const CONF_USERID = 'RELEVANZ_USERID';

    protected static $instance = null;

    protected $configurationService = null;
    protected $databaseQueryBuilder = null;

    protected function __construct() {
        if (interface_exists(GmConfigurationService::class)) {
            // >= Gambio GX4.1
            $this->configurationService = \LegacyDependencyContainer::getInstance()->get(GmConfigurationService::class);
        } else {
            // Gambio GX3 and GX4.0
            $this->databaseQueryBuilder = \StaticGXCoreLoader::getDatabaseQueryBuilder();
        }
    }

    protected function _read($key) {
        if ($this->configurationService !== null) {
            $c = $this->configurationService->find(self::CONF_PREFIX.$key);
            return ($c !== null) ? $c->value() : null;
        } else {
            $row = $this->databaseQueryBuilder
                ->select('*')->from(TABLE_CONFIGURATION)
                ->where('configuration_key', $key)->get()->row_array();
            return (isset($row['configuration_value']) && !empty($row['configuration_value']))
                ? $row['configuration_value']
                : null;
        }
    }

    protected function _write($key, $value) {
        if ($this->configurationService !== null) {
            if (class_exists(GmConfigurationWrite::class)) {
                $this->configurationService->save(GmConfigurationWrite::create(self::CONF_PREFIX.$key, (string)$value));
            } else {
                $this->configurationService->save(self::CONF_PREFIX.$key, (string)$value);
            }

        } else {
            $configRows = $this->databaseQueryBuilder
                ->select('*')->from(TABLE_CONFIGURATION)
                ->where('configuration_key', $key)
                ->get()->num_rows();

            if ($configRows === 0) {
                $now = new DateTime();
                $this->databaseQueryBuilder->insert(TABLE_CONFIGURATION, [
                    'configuration_key'   => $key,
                    'configuration_value' => $value,
                    'sort_order' => 0,
                    'date_added' => $now->format('Y-m-d H:i:s'),
                ]);
            } else {
                $this->databaseQueryBuilder->where('configuration_key', $key)
                    ->update(TABLE_CONFIGURATION, ['configuration_value' => $value]);
            }
        }
    }

    protected function _delete($key) {
        if ($this->configurationService !== null) {
            $this->configurationService->delete(self::CONF_PREFIX.$key);
        } else {
            $this->databaseQueryBuilder
                ->delete(TABLE_CONFIGURATION)
                ->where('configuration_key = :key')
                ->setParameter('key', $key)
                ->execute();
        }
    }

    protected static function gi() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function read($key) {
        return self::gi()->_read($key);
    }

    public static function write($key, $value) {
        return self::gi()->_write($key, $value);
    }

    public static function getCredentials() {
        return new Credentials(
            (string)self::read(self::CONF_APIKEY),
            (int)self::read(self::CONF_USERID)
        );
    }

    public static function updateCredentials(Credentials $credentials) {
        self::write(self::CONF_APIKEY, $credentials->getApiKey());
        self::write(self::CONF_USERID, $credentials->getUserId());
    }

    public static function deleteAll() {
        self::gi()->_delete(self::CONF_APIKEY);
        self::gi()->_delete(self::CONF_USERID);
    }

    public static function getPluginVersion() {
        return self::PLUGIN_VERSION;
    }

}
