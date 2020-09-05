<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
namespace Releva\Retargeting\Base\Export;

use InvalidArgumentException;

use Releva\Retargeting\Base\Export\Item\ProductExportItem;
use Releva\Retargeting\Base\Export\Item\ExportItemInterface;

/**
 * Export Products Exporter (CSV format)
 *
 * Provides methods for exporting the products data.
 */
class ProductCsvExporter extends AbstractCsvExporter
{
    protected $filename = 'products';

    /**
     * Formats a product item for the csv export.
     *
     * @param ExportItemInterface $product
     * @return array<string,string>
     */
    public function formatItemRow(ExportItemInterface $product) {
        if (!($product instanceof ProductExportItem)) {
            throw new InvalidArgumentException(
                'Expected object of type '.ProductExportItem::class.', got '.get_class($product).'.',
                1574007381
            );
        }
        $row = $product->getData();
        $row['categoryIds'] = implode(',', $row['categoryIds']);
        $row['price'] = round($row['price'], 2);
        $row['priceOffer'] = round($row['priceOffer'], 2);
        return $row;
    }

}
