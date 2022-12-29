<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
namespace Releva\Retargeting\Base\Export;

use Exception;

use Releva\Retargeting\Base\Export\Item\ExportItemInterface;
use Releva\Retargeting\Base\Export\Helper\CsvWriter;

/**
 * CSV Export Generator
 *
 * Provides methods for exporting data as CSV file.
 */
abstract class AbstractCsvExporter implements ExporterInterface
{
    protected $filename = 'data';
    protected $csv = null;
    protected $headlineWritten = false;

    public function __construct() {
        $this->csv = new CsvWriter(null, [
            'delimiter' => ';',
            'quotechar' => '"',
            'escapechar' => '"',
            'lineterminator' => "\n",
            'quoting' => CsvWriter::QUOTE_ALL,
        ]);
    }

    protected function isOneDimensional($array) {
        foreach ($array as $v) {
            if (is_array($v) || is_object($array)) {
                return false;
            }
        }
        return true;
    }

    protected function formatItemRow(ExportItemInterface $item) {
        $row = [];
        foreach ($item->getData() as $key => $value) {
            if (is_float($value)) {
                $row[$key] = round($value, 4);
            } else if (is_array($value)) {
                if ($this->isOneDimensional($value)) {
                    $row[$key] = implode(',', $value);
                } else {
                    try {
                        $row[$key] = json_encode($value, defined('JSON_THROW_ON_ERROR') ? JSON_THROW_ON_ERROR : 0);
                    } catch (Exception $e) {}
                    if ($row[$key] == null) {
                        $row[$key] = 'encode_error';
                    }
                }
            }
        }
        return $row;
    }

    public function addItem(ExportItemInterface $item) {
        if (!$this->headlineWritten) {
            $this->csv->writeRow($item->getKeys(), CsvWriter::QUOTE_NONE);
            $this->headlineWritten = true;
        }

        $this->csv->writeRow($this->formatItemRow($item));
        return $this;
    }

    public function getContents() {
        return $this->csv->getStreamContents();
    }

    public function getHttpHeaders() {
        return [
            'Content-Type' => 'text/csv; charset="utf-8"',
            'Content-Disposition' => 'attachment; filename="'.$this->filename.'.csv"',
        ];
    }

}
