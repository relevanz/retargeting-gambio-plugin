<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the GNU General Public License (Version 2)
[http://www.gnu.org/licenses/gpl-2.0.html]
--------------------------------------------------------------
*/
namespace RelevanzTracking\Lib;

/**
 * JSON Export Generator
 *
 * Provides an interface for exporting the products data in the JSON format.
 */
class JsonExporter implements ExportGenerator {
    protected $data = [];

    public function __construct() {}

    public function addRow(array $row) {
        $this->data[] = $row;
        return $this;
    }

    public function getContents() {
        return json_encode($this->data, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);
    }

    public function getHttpHeaders() {
        return [
            'Content-Type: application/json; charset="utf-8"',
        ];
    }

}
