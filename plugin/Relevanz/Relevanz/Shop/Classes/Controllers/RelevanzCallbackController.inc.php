<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the GNU General Public License (Version 2)
[http://www.gnu.org/licenses/gpl-2.0.html]
--------------------------------------------------------------
*/
require_once(__DIR__.'/../../../autoload.php');

use RelevanzTracking\Lib\RelevanzException;
use RelevanzTracking\GambioConfiguration;

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

    protected function getDbStats() {
        $r = [];
        // Please make the query() method throw exceptions instead of generating an error and calling exit.
        $foo = $this->db->simple_query('SELECT @@version, @@version_comment');
        if (!is_object($foo)) {
            return null;
        }
        $this->db->result_id = $foo;
        $dbrd = $this->db->load_rdriver();
        $res = new $dbrd($this->db);
        $row = $res->row_array();
        if (isset($row['@@version_comment'])) {
            $r['server'] = $row['@@version_comment'];
        }
        if (isset($row['@@version'])) {
            $r['version'] = $row['@@version'];
        }
        return empty($r) ? null : $r;
    }

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
                'system' => 'gambio',
                'version' => ltrim(gm_get_conf('INSTALLED_VERSION'), 'v'),
                'is-cloud' => gm_get_conf('IS_CLOUD') === 'true',
            ],
            'environment' => [
                'server-software' => isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : null,
                'php' => [
                    'version' => phpversion(),
                    'sapi-name' => php_sapi_name(),
                    'memory-limit' => ini_get('memory_limit'),
                    'max-execution-time' => ini_get('max_execution_time'),
                ],
                'db' => $this->getDbStats(),
            ],
            'callbacks' => $this->discoverCallbacks()
        ];
        return new HttpControllerResponse(json_encode($data, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION), [
            'Content-Type: application/json; charset="utf-8"',
            'Cache-Control: must-revalidate',
        ]);
    }
}