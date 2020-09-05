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
 * Exporter Interface
 * 
 * Provides an interface for exporting the products data.
 */
interface ExporterInterface
{
    public function addItem(ExportItemInterface $item);
    public function getContents();
    public function getHttpHeaders();
}
