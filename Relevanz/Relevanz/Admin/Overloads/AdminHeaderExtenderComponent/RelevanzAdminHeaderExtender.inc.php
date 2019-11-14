<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the GNU General Public License (Version 2)
[http://www.gnu.org/licenses/gpl-2.0.html]
--------------------------------------------------------------
*/

/**
 * Class RelevanzAdminHeaderExtender
 */
class RelevanzAdminHeaderExtender extends RelevanzAdminHeaderExtender_parent
{
    /**
     * Overloaded "proceed" method.
     * Adds custom logo font for admin menu icon.
     */
    public function proceed() {
        parent::proceed();
        if (!is_array($this->v_output_buffer)) {
            $this->v_output_buffer = array();
        }

        if (!(bool)gm_get_conf('MODULE_CENTER_RELEVANZ_INSTALLED')) {
            return;
        }

        $this->v_output_buffer[] = MainFactory::create(
            'Asset',
            DIR_WS_CATALOG.'GXModules/Relevanz/Relevanz/Admin/Styles/relevanz-font.css'
        );
    }

}