<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
namespace Releva\Retargeting\Base\Export\Helper;

use Exception;
use RuntimeException;

/**
 * CSV Writer
 *
 * Provides an easy-to-use interface for writing csv-formatted text files. It
 * does not make use of the PHP5 function fputcsv. It provides quite a bit of
 * flexibility. You can specify just about everything about how it writes csv
 * files.
 */
class CsvWriter {

    /**
     * Instructs CSVWriter to quote only columns with special characters such as the
     * delimiter character, quote character or any of the characters in line terminator
     */
    const QUOTE_MINIMAL = 0;

    /**
     * Instructs CSVWriter to quote all columns
     */
    const QUOTE_ALL = 1;

    /**
     * Instructs CSVWriter to quote all columns that aren't numeric
     */
    const QUOTE_NONNUMERIC = 2;

    /**
     * Instructs CSVWriter to quote all columns that are strings
     */
    const QUOTE_STRINGS = 3;

    /**
     * Instructs CSVWriter to quote no columns
     */
    const QUOTE_NONE = 4;


    /**
     * The filename of the file we're working on
     * @var string
     * @access protected
     */
    protected $filename = '';

    /**
     * Holds an array that describes the current csv dialect - tells writer how to write
     * @var array
     * @access protected
     */
    protected $dialect = [
        'delimiter' => ';',
        'quotechar' => '"',
        'escapechar' => '\\',
        'lineterminator' => "\n",
        'quoting' => self::QUOTE_MINIMAL,
        'charset' => [
            'out' => null,
            'in' => null,
        ],
    ];

    /**
     * Holds the file resource
     * @var resource
     * @access protected
     */
    protected $handle;

    /**
     * Contains the in-menory data waiting to be written to disk
     * @var array
     * @access protected
     */
    protected $data = [];

    /**
     * Class constructor
     *
     * @param resource|string Either a valid filename or a valid file resource
     * @param array A csv dialect array
     */
    public function __construct($file, $dialect = []) {
        $this->setDialect($dialect);

        if (is_resource($file)) {
            $this->handle = $file;
        } else {
            $this->filename = $file;

            try {
                if (is_string($file)) {
                    $this->handle = fopen($this->filename, 'wb');
                } else {
                    $this->handle = fopen('php://memory', 'rwb');
                }
            } catch (Exception $e) {}

            if ($this->handle === false) {
                throw new RuntimeException('Unable to create/access file: '.$this->filename, 1449173661);
            }
        }
    }

    /**
     * Closes any open file handles if they haven't been closed before.
     * @access public
     */
    public function __destruct() {
        $this->close();
    }

    /**
     * Closes the current file stream.
     *
     * @return bool
     *    true if successfull, false otherwise.
     */
    public function close() {
        if (is_resource($this->handle)) {
            return fclose($this->handle);
        }
        return true;
    }

    /**
     * Get the current csv dialect array
     *
     * @returns array The current csv dialect array
     * @access public
     */
    public function getDialect() {
        return $this->dialect;
    }

    /**
     * Change the dialect this csv reader is using
     *
     * @param array the current csv dialect array
     * @access public
     */
    public function setDialect(array $dialect) {
        $this->dialect = array_replace_recursive($this->dialect, $dialect);
    }

    /**
     * Get the filename attached to this writer (unless none was specified)
     *
     * @return ?string
     *     The filename this writer is attached to or null if it
     *     was passed a resource and no filename
     */
    public function getPath() {
        return $this->filename;
    }

    /**
     * Quotes a column with quotechar
     *
     * @param string A single value to be quoted for output
     * @return string Quoted input value
     * @access protected
     */
    protected function quote($input) {
        return $this->dialect['quotechar'] . $input . $this->dialect['quotechar'];
    }

    /**
     * Escapes a column (escapes quotechar with escapechar)
     *
     * @param string A single value to be escaped for output
     * @return string Escaped input value
     * @access protected
     */
    protected function escape($input) {
        return str_replace(
            $this->dialect['quotechar'],
            $this->dialect['escapechar'] . $this->dialect['quotechar'],
            $input
        );
    }

    /**
     * Returns true if input contains quotechar, delimiter or any of the characters in lineterminator
     *
     * @param string A single value to be checked for special characters
     * @return boolean True if contains any special characters
     * @access protected
     */
    protected function containsSpecialChars($input) {
        $special_chars = str_split($this->dialect['lineterminator'], 1);
        $special_chars[] = $this->dialect['quotechar'];
        $special_chars[] = $this->dialect['delimiter'];
        foreach ($special_chars as $char) {
            if (strpos($input, $char) !== false) return true;
        }
    }

    /**
     * Convert the string to the specified charset.
     *
     * @param string $column
     * @return string
     */
    protected function convertCharset($column) {
        $in = ($this->dialect['charset']['in'] === null)
            ? mb_internal_encoding()
            : $this->dialect['charset']['in'];
        return mb_convert_encoding($column, $this->dialect['charset']['out'], $in);
    }

    /**
     * Accepts a row of data and returns it formatted according to $this->dialect
     * This method is called by writeData()
     *
     * @param array An array of data to be formatted for output to the file
     * @access protected
     * @return array The formatted array (formatting determined by dialect)
     */
    protected function formatRow(array $row, $altQuote = null) {
        if ($altQuote === null) {
            $altQuote = $this->dialect['quoting'];
        }

        foreach ($row as &$column) {
            if ($this->dialect['charset']['out'] !== null) {
                $column = $this->convertCharset($column);
            }
            switch ($altQuote) {
                case self::QUOTE_NONE: {
                    // do nothing... no quoting is happening here
                    break;
                }
                case self::QUOTE_ALL: {
                    $column = $this->quote($this->escape($column));
                    break;
                }
                case self::QUOTE_NONNUMERIC: {
                    if (!is_numeric($column)) {
                        $column = $this->quote($this->escape($column));
                    }
                    break;
                }
                case self::QUOTE_STRINGS: {
                    if (is_string($column) && !empty($column)) {
                        $column = $this->quote($this->escape($column));
                    }
                    break;
                }
                case self::QUOTE_MINIMAL:
                default: {
                    if ($this->containsSpecialChars($column)) {
                        $column = $this->quote($this->escape($column));
                    }
                    break;
                }
            }
        }
        return $row;

    }

    /**
     * Writes the data to the csv file according to the dialect specified
     *
     * @access protected
     */
    protected function writeData($altQuote = null) {
        $rows = [];
        foreach ($this->data as $row) {
            $rows[] = implode($this->dialect['delimiter'], $this->formatRow($row, $altQuote));
        }
        // ensures that there is a line terminator at the end of the file, which is necessary
        $output = implode($this->dialect['lineterminator'], $rows) . $this->dialect['lineterminator'];
        fwrite($this->handle, $output);
        $this->data = []; // data has been written, so empty it
    }

    /**
     * Write a single row to the file
     *
     * @param array An array representing a row of data to be written
     * @access public
     */
    public function writeRow(array $row, $altQuote = null) {
        $this->data[] = $row;
        $this->writeData($altQuote);
    }

    /**
     * Write multiple rows to file
     *
     * @param array An two-dimensional array representing rows of data to be written
     * @access public
     */
    public function writeRows($rows, $altQuote = null) {
        $this->data = $rows;
        $this->writeData($altQuote);
    }

    /**
     * Write a raw line to the csv file (e.g. empty lines, headers, etc.)
     *
     * @param string Some text that will be written to the csv file appended by a line terminator.
     * @access public
     */
    public function writeRawLine($str) {
        $output = $str . $this->dialect['lineterminator'];
        fwrite($this->handle, $output);
    }

    /**
     * Sets the file position indicator to the end of the csv file.
     *
     * @return bool
     *    true if successfull, false otherwise.
     */
    public function seekToEnd() {
        return fseek($this->handle, 0 , SEEK_END) === 0;
    }

    /**
     * Returs the stream contents. Notice that the stream will be rewinded. If you add more
     * rows the old ones will be overwritten and you end up with a corrupted csv file.
     *
     * If you want to continue adding rows to the file call self::seekToEnd().
     *
     * @return string
     */
    public function getStreamContents() {
        rewind($this->handle);
        return stream_get_contents($this->handle);
    }

}
