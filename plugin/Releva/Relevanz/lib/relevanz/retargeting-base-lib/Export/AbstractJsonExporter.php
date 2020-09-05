<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
namespace Releva\Retargeting\Base\Export;

use Releva\Retargeting\Base\Export\Item\ExportItemInterface;

/**
 * JSON Export Generator
 *
 * Provides an interface for exporting data in the JSON format.
 */
abstract class AbstractJsonExporter implements ExporterInterface
{
    protected $data = [];

    public function __construct() {}

    public function addItem(ExportItemInterface $item) {
        $this->data[] = $item->getData();
        return $this;
    }

    public function getContents() {
        return json_encode($this->data, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);
    }

    public function getHttpHeaders() {
        return [
            'Content-Type' => 'application/json; charset="utf-8"',
        ];
    }

}
