<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
namespace Releva\Retargeting\Base\Export\Item;

/**
 * Export Item
 *
 * Provides an interface for exportable entities.
 */
interface ExportItemInterface
{
    /**
     * Associative array representation of the item.
     *
     * @return array<string,mixed>
     */
    public function getData();

    /**
     * List of the keys that getData() returns.
     *
     * @return array<string>
     */
    public function getKeys();
}
