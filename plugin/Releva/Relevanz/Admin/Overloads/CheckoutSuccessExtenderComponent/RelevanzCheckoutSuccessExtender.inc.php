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
 * Class RelevanzCheckoutSuccessExtender
 *
 * This is a Relevatracking overload for the CheckoutSuccessExtenderComponent.
 *
 * @see CheckoutSuccessExtenderComponent
 */
class RelevanzCheckoutSuccessExtender extends RelevanzCheckoutSuccessExtender_parent
{
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

        $cch = new CookieConsentHelper();

        $jsUrls = [];

        if (isset($this->v_data_array['orders_id']) && !empty($this->v_data_array['orders_id'])
            && isset($this->v_data_array['coo_order']) && is_object($this->v_data_array['coo_order'])
        ) {
            $orderId = $this->v_data_array['orders_id'];
            $orderTotal = sprintf("%0.2f", round($this->v_data_array['coo_order']->info['pp_total'], 2));
            $productIds = [];

            foreach($this->v_data_array['coo_order']->products as $item) {
                $productIds[] = $item['id'];
            }

            $jsUrls[] = $cch->getScriptTag(
                RelevanzApi::RELEVANZ_CONV_URL.'?cid='.$userid.'&orderId='.$orderId
                    .'&amount='.$orderTotal.'&products='.implode(',', $productIds)
            );
        }

        $jsUrls[] = $cch->getScriptTag(
            RelevanzApi::RELEVANZ_TRACKER_URL.'?cid=' . $userid.'&t=d&action=t'
        );

        if (!is_array($this->html_output_array)) {
            $this->html_output_array = [];
        }

        $this->html_output_array[] =
             '<!-- Start of releva.nz tracking code -->'."\n"
            .implode("\n", $jsUrls)."\n"
            .'<!-- End of releva.nz tracking code -->';
    }

}
