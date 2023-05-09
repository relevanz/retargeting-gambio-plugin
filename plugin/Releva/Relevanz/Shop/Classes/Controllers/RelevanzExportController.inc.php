<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
require_once(__DIR__.'/../../../autoload.php');

use Releva\Retargeting\Base\Exception\RelevanzException;
use Releva\Retargeting\Base\Export\Item\ProductExportItem;
use Releva\Retargeting\Base\Export\ProductCsvExporter;
use Releva\Retargeting\Base\Export\ProductJsonExporter;
use Releva\Retargeting\Gambio\ShopInfo as GambioShopInfo;

/**
 * This controller exports the shops products for the releva.nz service.
 */
class RelevanzExportController extends AbstractRelevanzHttpViewController
{
    const ITEMS_PER_PAGE = 2500;

    protected $langId;

    protected $taxes = [];

    protected $cache = [];

    protected function cachePopulate($cacheName, $queryBuilder) {
        if (!isset($this->cache[$cacheName])) {
            $this->cache[$cacheName] = [];
        }
        foreach ($queryBuilder->get()->result_array() as $row) {
            $this->cache[$cacheName][$row['k']] = $row['v'];
        }
    }

     protected function cacheKeyExists($cacheName, $key = false) {
        return $key === false
            ? isset($this->cache[$cacheName])
            : isset($this->cache[$cacheName][$key]);
    }

    protected function cacheGetValue($cacheName, $key, $default = false) {
        return $this->cacheKeyExists($cacheName, $key)
            ? $this->cache[$cacheName][$key]
            : $default;
    }

    protected function getVpeUnitById($id) {
        if (!$this->cacheKeyExists('BasePriceUnit')) {
            $this->cachePopulate(
                'BasePriceUnit',
                $this->db
                    ->select('products_vpe_id as `k`, products_vpe_name as `v`')
                    ->from(TABLE_PRODUCTS_VPE)
                    ->where('language_id', $this->langId)
            );
        }
        return $this->cacheGetValue('BasePriceUnit', $id, '');
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
                pr.products_price as `price`, sp.specials_new_products_price as specials_price,
                pr.products_tax_class_id,
                pr.products_vpe as products_vpe_unit, pr.products_vpe_value, pr.products_vpe_status,
                pr.products_image as `image`
            ')
            ->from(TABLE_PRODUCTS.' AS pr')
            ->join(TABLE_PRODUCTS_DESCRIPTION.' AS pd', 'pr.products_id = pd.products_id', 'left')
            ->join(TABLE_SPECIALS.' AS sp', ' pr.products_id = sp.products_id AND sp.status = "1"', 'left')
            ->where('pd.language_id', $this->langId)
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
        if (empty($image)) {
            return '';
        }
        $imageUrl = HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_INFO_IMAGES . $image;

        if (file_exists(DIR_WS_ORIGINAL_IMAGES . $image)) {
            $imageUrl = HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_ORIGINAL_IMAGES . $image;
        }
        return $imageUrl;
    }

    protected function loadTaxes() {
        $zoneIds = $this->db
            ->select('geo_zone_id')
            ->from(TABLE_GEO_ZONES)
            ->order_by('geo_zone_name="Deutschland"', 'DESC', false)
            ->order_by('geo_zone_name="Germany"', 'DESC', false)
            ->order_by('geo_zone_name="Steuerzone EU"', 'DESC', false)
            ->order_by('geo_zone_name="Österreich"', 'DESC', false)
            ->order_by('geo_zone_name="Austria"', 'DESC', false)
            ->order_by('geo_zone_id', 'ASC')
            ->limit(5)
            ->get()
            ->result_array();

        $taxQb = $this->db
            ->select('tax_class_id, tax_rate')
            ->from(TABLE_TAX_RATES);
        foreach ($zoneIds as $row) {
            $taxQb->order_by(sprintf('tax_zone_id = %d', $row['geo_zone_id']), 'DESC', false);
        }
        $taxQb
            ->order_by('tax_priority')
            ->order_by('tax_rate', 'DESC');

        foreach ($taxQb->get()->result_array() as $row) {
            if (isset($this->taxes[(int)$row['tax_class_id']])) {
                continue;
            }
            $this->taxes[(int)$row['tax_class_id']] = (float)$row['tax_rate'];
        }
    }

    public function actionDefault() {
        $pCount = $this->getProductCount();
        if (empty($pCount)) {
            return new HttpControllerResponse('No products found.', [
                'HTTP/1.0 404 Not Found'
            ]);
        }

        $this->langId = MainFactory::create('LanguageProvider', $this->db)->getDefaultLanguageId();

        $exporter = null;
        switch ($this->_getQueryParameter('format')) {
            case 'json': {
                $exporter = new ProductJsonExporter();
                break;
            }
            default: {
                $exporter = new ProductCsvExporter();
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

        $this->loadTaxes();

        foreach ($result as $product) {
            $tax = isset($this->taxes[$product['products_tax_class_id']])
                ? $this->taxes[$product['products_tax_class_id']]
                : 0.0;
            $price = $product['price'] + $product['price'] / 100 * $tax;
            $priceOffer = ($product['specials_price'] === null)
                ? $price
                : $product['specials_price'] + $product['specias_price'] / 100 * $tax;

            $item = new ProductExportItem();
            $item
                ->setId((int)$product['id'])
                ->setCategoryIds($this->getCategoryIdsByProductId($product['id']))
                ->setName($product['name'])
                ->setDescriptionShort($product['shortDescription'])
                ->setDescriptionLong(preg_replace('/\[TAB:([^\]]*)\]/', '<h1>${1}</h1>', $product['longDescription']))
                ->setPrice($product['price'], null, $tax)
                ->setPriceOffer($product['specials_price'], null, $tax)
                ->setLink(HTTP_SERVER . DIR_WS_CATALOG . 'product_info.php?info=p' . xtc_get_prid($product['id']))
                ->setImage($this->productImageUrl($product['image']))
            ;

            if ((bool)$product['products_vpe_status']) {
                $product['products_vpe_unit'] = $this->getVpeUnitById($product['products_vpe_unit']);
                if (!empty($product['products_vpe_unit']) && ((float)$product['products_vpe_value'] > 0)) {
                    $item->setBasePrice($product['products_vpe_unit'], (float)$product['products_vpe_value']);
                }
            }
            $exporter->addItem($item);
        }

        $headers = [];
        foreach ($exporter->getHttpHeaders() as $hkey => $hval) {
            $headers[] = $hkey.': '.$hval;
        }
        $headers[] = 'Cache-Control: must-revalidate';
        $headers[] = 'X-Relevanz-Product-Count: '.$pCount;
        #$headers[] = 'Content-Type: text/plain; charset="utf-8"'; $headers[] = 'Content-Disposition: inline';

        return new HttpControllerResponse($exporter->getContents(), $headers);
    }

    public static function discover() {
        return [
            'url' => GambioShopInfo::getUrlProductExport(),
            'parameters' => [
                'format' => [
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
