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
use Releva\Retargeting\Gambio\CookieConsentHelper;

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

        $urlJs = '';
        $urlBase = RelevanzApi::RELEVANZ_TRACKER_URL.'?cid=' . $userid.'&t=d&';
        $currentPage = strtolower($this->get_page());
        switch ($currentPage) {
            case 'cat': { // category
                if (isset($this->v_data_array['cPath'])
                    && ($cPath = explode('_', $this->v_data_array['cPath']))
                    && (($id = (int)array_pop($cPath)) > 0)
                ) {
                    $urlJs = $urlBase.'action=c&id=' . $id;
                }
                break;
            }
            case 'productinfo': {
                if (isset($this->v_data_array['products_id'])
                    && (($id = (int)$this->v_data_array['products_id']) > 0)
                ) {
                    $urlJs = $urlBase.'action=p&id=' . $id;
                }
                break;
            }
            case 'checkout': {
                // Let's make RelevanzCheckoutSuccessExtender do it's magic.
                break;
            }
            default: {
                $urlJs = $urlBase.'action=s';
                break;
            }
        }

        if (empty($urlJs)) {
            return;
        }

        if (!is_array($this->v_output_buffer)) {
            $this->v_output_buffer = [];
        }

        $this->v_output_buffer[] =
             '<!-- Start of releva.nz tracking code -->'."\n"
            .(new CookieConsentHelper())->getScriptTag($urlJs)."\n"
            .'<!-- End of releva.nz tracking code -->';
    }

}
