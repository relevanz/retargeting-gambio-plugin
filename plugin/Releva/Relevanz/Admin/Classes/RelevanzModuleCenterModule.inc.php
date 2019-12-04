<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
require_once(__DIR__.'/../../autoload.php');

use Releva\Retargeting\Gambio\Configuration as GambioConfiguration;

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

        $columnsQuery = $this->db->query("DESCRIBE `admin_access` 'relevanz'");

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

    /**
     * Uninstalls the module
     */
    public function uninstall() {
        parent::uninstall();

        $this->db->where_in('configuration_key', GambioConfiguration::CONF_USERID)->delete(TABLE_CONFIGURATION);
        $this->db->where_in('configuration_key', GambioConfiguration::CONF_APIKEY)->delete(TABLE_CONFIGURATION);

        $columnsQuery = $this->db->query("DESCRIBE `admin_access` 'relevanz'");
        if ($columnsQuery->num_rows()) {
            $this->db->query('ALTER TABLE ' . TABLE_ADMIN_ACCESS . ' DROP `relevanz`');
        }
    }
}
