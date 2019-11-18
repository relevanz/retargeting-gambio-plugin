<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
namespace RelevanzTracking\Lib\Export\Item;

/**
 * Export Item
 *
 * Provides an interface for exportable entities.
 */
interface ExportItemInterface {
	
	/**
	 * @return array<string,mixed>
	 */
	public function getData();
}
