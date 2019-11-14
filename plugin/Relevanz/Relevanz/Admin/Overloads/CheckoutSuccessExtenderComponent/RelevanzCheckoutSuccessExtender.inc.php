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

        if (!isset($this->v_data_array['orders_id']) || empty($this->v_data_array['orders_id'])
            || !isset($this->v_data_array['coo_order']) || !is_object($this->v_data_array['coo_order'])
        ) {
            return;
        }

        $orderId = $this->v_data_array['orders_id'];
        $orderTotal = sprintf("%0.2f", round($this->v_data_array['coo_order']->info['pp_total'], 2));
        $productIds = [];

        foreach($this->v_data_array['coo_order']->products as $item) {
            $productIds[] = $item['id'];
        }

        $url_js = RelevanzApi::RELEVANZ_CONV_URL.'?cid='.$userid.'&orderId='.$orderId
            .'&amount='.$orderTotal.'&products='.implode(',', $productIds);

        if (!is_array($this->html_output_array)) {
            $this->html_output_array = array();
        }

        $this->html_output_array[] = '
            <!-- Start of releva.nz tracking code -->
            <script type="text/javascript" src="' . htmlspecialchars($url_js) . '" async="true"></script>
            <!-- End of releva.nz tracking code -->';
    }
}
