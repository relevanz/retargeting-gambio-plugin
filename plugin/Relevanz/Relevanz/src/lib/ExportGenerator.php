<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the GNU General Public License (Version 2)
[http://www.gnu.org/licenses/gpl-2.0.html]
--------------------------------------------------------------
*/
namespace RelevanzTracking\Lib;

/**
 * Export Generator
 * 
 * Provides an interface for exporting the products data.
 */
interface ExportGenerator {
    public function addRow(array $row);
    public function getContents();
    public function getHttpHeaders();
}
