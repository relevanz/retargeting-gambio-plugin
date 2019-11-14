<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the GNU General Public License (Version 2)
[http://www.gnu.org/licenses/gpl-2.0.html]
--------------------------------------------------------------
*/
use RelevanzTracking\Lib\RelevanzException;
use RelevanzTracking\GambioConfiguration;

/**
 * Class AbstractRelevanzHttpViewController
 *
 * This controller is a common base for all relevanz front controllers.
 *
 * @category System
 */
abstract class AbstractRelevanzHttpViewController extends HttpViewController
{
    /**
     * @var \CI_DB_query_builder
     */
    protected $db;

    protected $credentials = [];

    /**
     * Invokes an action method by the given action name.
     *
     * @param string $actionName Name of action method to call, without 'action'-Suffix.
     *
     * @return HttpControllerResponseInterface Response message.
     */
    protected function _callActionMethod($actionName) {
        try {
            if (!(bool)gm_get_conf('MODULE_CENTER_RELEVANZ_INSTALLED')) {
                throw new RelevanzException('releva.nz module not installed', 1554157518);
            }

            $this->credentials = GambioConfiguration::getCredentials();
            if (!$this->credentials->isComplete()) {
                throw new RelevanzException('releva.nz module is not configured', 1554158425);
            }

            if ($this->_getQueryParameter('auth') !== $this->credentials->getAuthHash()) {
                return new HttpControllerResponse('Missing authentification', [
                    'HTTP/1.0 401 Unauthorized',
                    'Content-Type: text/plain; charset="utf-8"',
                    'Cache-Control: must-revalidate',
                ]);
            }

            $this->db = StaticGXCoreLoader::getDatabaseQueryBuilder();

            return parent::_callActionMethod($actionName);

        } catch (RelevanzException $e) {
            return new HttpControllerResponse($e->getMessage(), [
                'HTTP/1.0 404 Not Found',
                'Content-Type: text/plain; charset="utf-8"',
                'Cache-Control: must-revalidate',
            ]);

        } catch (Exception $e) {
            return new HttpControllerResponse($e->getMessage(), [
                'HTTP/1.0 500 Internal Server Error',
                'Content-Type: text/plain; charset="utf-8"',
                'Cache-Control: must-revalidate',
            ]);
        }
    }

}
