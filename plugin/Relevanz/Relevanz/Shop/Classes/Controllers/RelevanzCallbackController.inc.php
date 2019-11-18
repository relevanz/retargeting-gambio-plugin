<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
require_once(__DIR__.'/../../../autoload.php');

use RelevanzTracking\Shop\GambioConfiguration;
use RelevanzTracking\Shop\GambioShopInfo;


/**
 * Class RelevanzExportController
 *
 * This controller exports the shops products for the releva.nz service.
 *
 * @category System
 * @package  AdminHttpViewControllers
 */
class RelevanzCallbackController extends AbstractRelevanzHttpViewController
{
    protected function discoverCallbacks() {
        $callbacks = [];
        $dir = new DirectoryIterator(__DIR__);
        foreach ($dir as $fileinfo) {
            $m = [];
            if (!preg_match('/^(Relevanz([A-Za-z0-9]+)Controller).inc.php$/', $fileinfo->getFilename(), $m)) {
                continue;
            }
            $class = $m[1];
            $cbname = strtolower($m[2]);
            if (class_exists($class) && is_callable($class .'::discover')) {
                $callbacks[$cbname] = call_user_func($class .'::discover');
            }
        }
        return $callbacks;
    }

    public function actionDefault() {
        $data = [
            'plugin-version' => GambioConfiguration::getPluginVersion(),
            'shop' => [
                'system' => GambioShopInfo::getShopSystem(),
                'version' => GambioShopInfo::getShopVersion(),
                'is-cloud' => gm_get_conf('IS_CLOUD') === 'true',
            ],
            'environment' => GambioShopInfo::getServerEnvironment(),
            'callbacks' => $this->discoverCallbacks()
        ];
        return new HttpControllerResponse(json_encode($data, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION), [
            'Content-Type: application/json; charset="utf-8"',
            'Cache-Control: must-revalidate',
        ]);
    }
}
