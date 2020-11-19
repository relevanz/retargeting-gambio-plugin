<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
require_once(__DIR__.'/../../autoload.php');

use Releva\Retargeting\Gambio\Configuration as GambioConfiguration;
use Releva\Retargeting\Gambio\CookieConsentHelper;

/**
 * Class RelevanzModuleCenterModule
 *
 * @extends    AbstractModuleCenterModule
 * @category   System
 * @package    Modules
 */
class RelevanzModuleCenterModule extends AbstractModuleCenterModule
{
    protected function _init() {
        $this->title = $this->languageTextManager->get_text('relevanz_title');
        $this->description = $this->languageTextManager->get_text('relevanz_description');
        //$this->sortOrder = 10000;
    }

    /**
     * Installs the module
     */
    public function install() {
        parent::install();

        if (defined('TABLE_ADMIN_ACCESS')
            && ($this->db->query('SHOW TABLES LIKE "'.TABLE_ADMIN_ACCESS.'"')->num_rows() == 1)
        ) {
            $columnsQuery = $this->db->query('DESCRIBE `'.TABLE_ADMIN_ACCESS.'` "relevanz"');

            if (!$columnsQuery->num_rows()) {
                $this->db->query("ALTER TABLE " . TABLE_ADMIN_ACCESS . " ADD `relevanz` INT(1) NOT NULL DEFAULT '0'");
            }

            $this->db
                ->set('relevanz', '1')
                ->where('customers_id', '1')
                ->limit(1)
                ->update(TABLE_ADMIN_ACCESS);

            $this->db
                ->set('relevanz', '1')
                ->where('customers_id', 'groups')
                ->limit(1)
                ->update(TABLE_ADMIN_ACCESS);

            $this->db
                ->set('relevanz', '1')
                ->where('customers_id', $_SESSION['customer_id'])
                ->limit(1)
                ->update(TABLE_ADMIN_ACCESS);
        }

        // Clear some caches.
        $cc = MainFactory::create_object('CacheControl');
        $cc->clear_data_cache();
        if (method_exists($cc, 'clear_menu_cache')) {
            $cc->clear_menu_cache();
        }
        MainFactory::create_object('PhraseCacheBuilder', [])->build();
        if (class_exists('LegacyDependencyContainer') && class_exists('CacheFactory')) {
            LegacyDependencyContainer::getInstance()
            ->get(CacheFactory::class)
            ->createCacheFor('text_cache')
            ->clear();
        }
    }

    /**
     * Uninstalls the module
     */
    public function uninstall() {
        parent::uninstall();

        GambioConfiguration::deleteAll();

        if (defined('TABLE_ADMIN_ACCESS')
            && ($this->db->query('SHOW TABLES LIKE "'.TABLE_ADMIN_ACCESS.'"')->num_rows() == 1)
        ) {
            $columnsQuery = $this->db->query("DESCRIBE `admin_access` 'relevanz'");
            if ($columnsQuery->num_rows()) {
                $this->db->query('ALTER TABLE ' . TABLE_ADMIN_ACCESS . ' DROP `relevanz`');
            }
        }
        if (class_exists('CookieConsentPanelControllerFactory')) {
            (new CookieConsentPanelControllerFactory())
                ->purposeDeleteService()
                 ->deleteByPurposeAlias(CookieConsentHelper::PURPOSE_ALIAS);
        }
    }
}
