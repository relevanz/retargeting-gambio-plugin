<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the GNU General Public License (Version 2)
[http://www.gnu.org/licenses/gpl-2.0.html]
--------------------------------------------------------------
*/
require_once(__DIR__.'/../../../autoload.php');

use RelevanzTracking\GambioConfiguration;
use RelevanzTracking\Lib\RelevanzApi;

/**
 * Class RelevanzApplicationBottomExtender
 *
 * This is a Relevatracking overload for the ApplicationBottomExtenderComponent.
 *
 * @see ApplicationBottomExtenderComponent
 */
class RelevanzApplicationBottomExtender extends RelevanzApplicationBottomExtender_parent
{

    /**
     * Overloaded "proceed" method.
     */
    public function proceed() {
        parent::proceed();

        if (!(bool)gm_get_conf('MODULE_CENTER_RELEVANZ_INSTALLED')) {
            return;
        }
        if (!defined(GambioConfiguration::CONF_USERID)) {
            return;
        }
        $userid = constant(GambioConfiguration::CONF_USERID);
        if (empty($userid)) {
            return;
        }

        $url_js = '';
        $url_base = RelevanzApi::RELEVANZ_TRACKER_URL;
        $current_page = strtolower($this->get_page());
        switch ($current_page) {
            // FRONT PAGE (index)
            case 'index': {
                $url_js = $url_base.'?t=d&action=s&cid=' . $userid;
                break;
            }
            // CATEGORY PAGE
            case 'cat': {
                if (isset($this->v_data_array['cPath']) && ($id = $this->v_data_array['cPath'])) {
                    $url_js = $url_base.'?t=d&action=c&cid=' . $userid . '&id=' . $id;
                }
                break;
            }
            // PRODUCT PAGE
            case 'productinfo': {
                if (isset($this->v_data_array['products_id']) && ($id = $this->v_data_array['products_id'])) {
                    $url_js = $url_base.'?t=d&action=p&cid=' . $userid . '&id=' . $id;
                }
                break;
            }
        }

        if (empty($url_js)) {
            return;
        }

        if (!is_array($this->v_output_buffer)) {
            $this->v_output_buffer = array();
        }

        $this->v_output_buffer[] = '
            <!-- Start of releva.nz tracking code -->
            <script type="text/javascript" src="' . htmlspecialchars($url_js) . '" async="true"></script>
            <!-- End of releva.nz tracking code -->';
    }
}
