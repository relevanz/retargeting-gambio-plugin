<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the GNU General Public License (Version 2)
[http://www.gnu.org/licenses/gpl-2.0.html]
--------------------------------------------------------------
*/
require_once(__DIR__.'/../../../autoload.php');

use RelevanzTracking\Lib\RelevanzException;
use RelevanzTracking\Lib\CSVExporter;
use RelevanzTracking\Lib\JsonExporter;
use RelevanzTracking\GambioConfiguration;

/**
 * Class RelevanzExportController
 *
 * This controller exports the shops products for the releva.nz service.
 *
 * @category System
 * @package  AdminHttpViewControllers
 */
class RelevanzExportController extends AbstractRelevanzHttpViewController
{
    const ITEMS_PER_PAGE = 5000;

    protected function getMainLang() {
        $langResult = $this->db
            ->select('configuration_value')->from(TABLE_CONFIGURATION)
            ->where('configuration_key', 'DEFAULT_LANGUAGE')
            ->get()->row_array();
        if (!is_array($langResult) || !isset($langResult['configuration_value'])) {
            return null;
        }
        return $langResult['configuration_value'];
    }

    protected function getProductCount() {
        $r = $this->db->select('count(*)', false)
            ->from(TABLE_PRODUCTS)
            ->get()
            ->row_array();
        if (is_array($r)) {
            return (int)current($r);
        }
        return null;
    }

    protected function getProductQuery($lang) {
        return $this->db
            ->select('
                pr.products_id as `id`, pd.products_name as `name`,
                pd.products_short_description as `shortDescription`,
                pd.products_description as `longDescription`,
                pr.products_price as `price`, tr.tax_rate as taxRate,
                pr.products_image as `image`
            ')
            ->from(TABLE_PRODUCTS.' AS pr')
            ->join(TABLE_PRODUCTS_DESCRIPTION.' AS pd', 'pr.products_id = pd.products_id', 'left')
            ->join(TABLE_TAX_RATES.' AS tr', 'pr.products_tax_class_id = tr.tax_class_id', 'left')
            ->join(TABLE_LANGUAGES.' AS ln', 'pd.language_id = ln.languages_id', 'left')
            ->where('ln.code', $lang)
            ->where('pr.products_status', '1')
            ->group_by('pr.products_id')
            ->order_by('pr.products_id');
    }

    protected function getCategoryIdsByProductId($pid) {
        $ids = [];
        $catsql = $this->db
            ->select('categories_id')->from(TABLE_PRODUCTS_TO_CATEGORIES)
            ->where('products_id', $pid)->get();

        $result = $catsql->result_array();
        if (empty($result)) {
            return $ids;
        }

        foreach ($result as $row) {
            $ids[] = (int)$row['categories_id'];
        }
        return $ids;
    }

    protected function productImageUrl($image) {
        $imageUrl = HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_INFO_IMAGES . $image;

        if (file_exists(DIR_WS_ORIGINAL_IMAGES . $image)) {
            $imageUrl = HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_ORIGINAL_IMAGES . $image;
        }
        return $imageUrl;
    }

    public function actionDefault() {
        $pCount = $this->getProductCount();
        if (empty($pCount)) {
            return new HttpControllerResponse('No products found.', [
                'HTTP/1.0 404 Not Found'
            ]);
        }

        $lang = $this->getMainLang();
        if (empty($lang)) {
            throw new RelevanzException('Unable to get default language.', 1554160909);
        }

        $exporter = null;
        switch ($this->_getQueryParameter('type')) {
            case 'json': {
                $exporter = new JsonExporter();
                break;
            }
            default: {
                $exporter = new CSVExporter();
                break;
            }
        }

        $pq = $this->getProductQuery($lang);

        if (($page = (int)$this->_getQueryParameter('page')) > 0) {
            $pq->limit(self::ITEMS_PER_PAGE, ($page - 1) * self::ITEMS_PER_PAGE);
        }

        $result = $pq->get()->result_array();
        if (empty($result)) {
            return new HttpControllerResponse('', [
                'HTTP/1.0 404 Not Found',
                'X-Relevanz-Product-Count: '.$pCount,
            ]);
        }

        foreach ($result as $product) {
            $exporter->addRow([
                'product_id' => (int)$product['id'],
                'category_ids' => $this->getCategoryIdsByProductId($product['id']),
                'product_name' => $product['name'],
                'short_description' => $product['shortDescription'],
                'long_description' => preg_replace('/\[TAB:([^\]]*)\]/', '<h1>${1}</h1>', $product['longDescription']),
                'price' => round($product['price'] + $product['price'] / 100 * $product['taxRate'], 2),
                'link' => HTTP_SERVER . DIR_WS_CATALOG . 'product_info.php?info=p' . xtc_get_prid($product['id']),
                'image' => $this->productImageUrl($product['image'])
            ]);
        }

        return new HttpControllerResponse(
            $exporter->getContents(),
            array_merge(
                $exporter->getHttpHeaders(),
                [
                    'Cache-Control: must-revalidate',
                    'X-Relevanz-Product-Count: '.$pCount,
                    'Content-Type: text/plain; charset="utf-8"', 'Content-Disposition: inline',
                ]
            )
        );
    }

    public static function discover() {
        return [
            'url' => GambioConfiguration::getUrlExport(),
            'parameters' => [
                'type' => [
                    'values' => ['csv', 'json'],
                    'default' => 'csv',
                    'optional' => true,
                ],
                'page' => [
                    'type' => 'integer',
                    'optional' => true,
                    'info' => [
                         'items-per-page' => self::ITEMS_PER_PAGE,
                    ],
                ],
            ]
        ];
    }

}
