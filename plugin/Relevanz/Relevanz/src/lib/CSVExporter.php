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
class CSVExporter implements ExportGenerator {
    protected $csv = null;

    public function __construct() {
        $this->csv = new CSVWriter(null, [
            'delimiter' => ';',
            'quotechar' => '"',
            'escapechar' => '"',
            'lineterminator' => "\n",
            'quoting' => CSVWriter::QUOTE_ALL,
            'charset' => array (
                'out' => 'UTF-8',
            ),
        ]);
    }

    public function addRow(array $row) {
    	if (is_array($row['category_ids'])) {
    		$row['category_ids'] = implode(',', $row['category_ids']);
    	}
        $this->csv->writeRow($row);
        return $this;
    }

    public function getContents() {
        return $this->csv->getStreamContents();
    }

    public function getHttpHeaders() {
        return [
            'Content-Type: text/csv; charset="utf-8"',
            'Content-Disposition: attachment; filename="products.csv"',
        ];
    }

}
