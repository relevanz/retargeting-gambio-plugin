<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the GNU General Public License (Version 2)
[http://www.gnu.org/licenses/gpl-2.0.html]
--------------------------------------------------------------
*/
require_once(__DIR__.'/../../../autoload.php');

use RelevanzTracking\Lib\RelevanzApi;
use RelevanzTracking\Lib\Credentials;
use RelevanzTracking\Lib\RelevanzException;
use RelevanzTracking\GambioConfiguration;

/**
 * Class RelevanzModuleCenterModuleController
 * @extends    AbstractModuleCenterModuleController
 * @category   System
 * @package    Modules
 * @subpackage Controllers
 */
class RelevanzModuleCenterModuleController extends AbstractModuleCenterModuleController {
    const ROUTE_ADMIN = 'admin.php?do=RelevanzModuleCenterModule/';
    const STATS_FRAME = 'https://customer.releva.nz/?apikey=';

    const ROUTE_EXPORT = 'shop.php?do=RelevanzExport&auth=:auth';

    protected $subTitle = '';
    protected $credentials = [];

    protected function _init() {
        $this->pageTitle = $this->languageTextManager->get_text('relevanz_title');

        $this->credentials = GambioConfiguration::getCredentials();
    }

    private function outputPage($view, $tmplData = []) {
        $this->pageTitle = $this->languageTextManager->get_text('relevanz_title')
            .(!empty($this->subTitle) ? ' - '.$this->subTitle : '');
        $title = new NonEmptyStringType($this->pageTitle);

        $data = MainFactory::create('KeyValueCollection', array_merge_recursive([
            'credentials' => $this->credentials,
        ], $tmplData));

        $assets = MainFactory::create('AssetCollection', [
            MainFactory::create('Asset', DIR_WS_CATALOG.'GXModules/Relevanz/Relevanz/Admin/Styles/relevanz-font.css'),
            MainFactory::create('Asset', DIR_WS_CATALOG.'GXModules/Relevanz/Relevanz/Admin/Styles/'.$view.'.css'),
            MainFactory::create('Asset', 'module_center_module.relevanz.lang.inc.php'),
            MainFactory::create('Asset', 'relevanz.lang.inc.php'),
        ]);
        return MainFactory::create(
            'AdminLayoutHttpControllerResponse',
            $title,
            $this->getTemplateFile('Relevanz/Admin/Html/'.$view.'.php'),
            $data,
            $assets
        );
    }

    /**
     * Invokes an action method by the given action name.
     *
     * @param string $actionName Name of action method to call, without 'action'-Suffix.
     *
     * @throws LogicException If no action method of the given name exists.
     * @return HttpControllerResponseInterface Response message.
     */
    protected function _callActionMethod($actionName) {
        if (!(bool)gm_get_conf('MODULE_CENTER_RELEVANZ_INSTALLED')) {
            return MainFactory::create('RedirectHttpControllerResponse', 'admin.php?do=ModuleCenter');
        }

        if (!$this->credentials->isComplete() && ($actionName !== 'Conf')) {
            return MainFactory::create('RedirectHttpControllerResponse', self::ROUTE_ADMIN.'Conf');
        }

        $methodName = 'actionDefault';
        if (!empty($actionName)) {
            $methodName = 'action' . $actionName;
        }

        if (!method_exists($this, $methodName)) {
            return MainFactory::create('RedirectHttpControllerResponse', self::ROUTE_ADMIN);
        }
        return call_user_func(array($this, $methodName));
    }

    public function actionDefault() {
        return $this->outputPage('statistics', [
            'statsFrame' => RelevanzApi::RELEVANZ_STATS_FRAME.$this->credentials->getApiKey(),
        ]);
    }

    public function actionConf() {
        $this->subTitle = $this->languageTextManager->get_text('relevanz_subtitle_conf');

        $messages = [];
        if (isset($_POST['conf']['apikey'])) {
            try {
                $credentials = RelevanzApi::verifyApiKey($_POST['conf']['apikey'], [
                    'callback-url' => GambioConfiguration::getUrlCallback()
                ]);
                GambioConfiguration::updateCredentials($credentials);

                $msgCode = 1554076968;
                $messages[] = [
                    'type' => 'success',
                    'code' => $msgCode,
                    'msg'  => $this->languageTextManager->get_text('msg_'.$msgCode, 'relevanz'),
                ];

                $this->credentials = $credentials;

            } catch (RelevanzException $re) {
                $sarg = [$this->languageTextManager->get_text('msg_'.$re->getCode(), 'relevanz')];
                $sarg = array_merge($sarg, $re->getSprintfArgs());
                $messages[] = [
                    'type' => 'danger',
                    'code' => $re->getCode(),
                    'msg' => call_user_func_array('sprintf', $sarg).' (E'.$re->getCode().')',
                ];
            }
        }

        $exportUrl = str_replace(':auth', $this->credentials->getAuthHash(), GambioConfiguration::getUrlExport());

        return $this->outputPage('configuration', [
            'action' => self::ROUTE_ADMIN.'Conf',
            'messages' => $messages,
            'urlExport' => $exportUrl,
        ]);
    }

}
