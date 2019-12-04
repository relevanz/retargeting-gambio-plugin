<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
require_once(__DIR__.'/../../../autoload.php');

use Releva\Retargeting\Base\RelevanzApi;
use Releva\Retargeting\Gambio\Configuration as GambioConfiguration;

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
        $url_base = RelevanzApi::RELEVANZ_TRACKER_URL.'?cid=' . $userid.'&t=d&';
        $current_page = strtolower($this->get_page());
        switch ($current_page) {
            // FRONT PAGE (index)
            case 'index': {
                $url_js = $url_base.'action=s';
                break;
            }
            // CATEGORY PAGE
            case 'cat': {
                if (isset($this->v_data_array['cPath'])
                    && ($cPath = explode('_', $this->v_data_array['cPath']))
                    && (($id = (int)array_pop($cPath)) > 0)
                ) {
                    $url_js = $url_base.'action=c&id=' . $id;
                }
                break;
            }
            // PRODUCT PAGE
            case 'productinfo': {
                if (isset($this->v_data_array['products_id'])
                    && (($id = (int)$this->v_data_array['products_id']) > 0)
                ) {
                    $url_js = $url_base.'action=p&id=' . $id;
                }
                break;
            }
        }

        if (empty($url_js)) {
            return;
        }

        if (!is_array($this->v_output_buffer)) {
            $this->v_output_buffer = [];
        }

        $this->v_output_buffer[] = '
            <!-- Start of releva.nz tracking code -->
            <script type="text/javascript" src="' . htmlspecialchars($url_js) . '" async="true"></script>
            <!-- End of releva.nz tracking code -->';
    }

}
