<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
namespace Releva\Retargeting\Base;

/**
 * Helper Class to convert strings to UTF-8
 */
class Utf8Util {
    protected function __contruct() {
    }

    protected static function legacyIsUtf8($str) {
        $len = strlen($str);
        for ($i = 0; $i < $len; ++$i){
            $c = ord($str[$i]);
            if ($c > 128) {
                if (($c > 247)) {
                    return false;
                } elseif ($c > 239) {
                    $bytes = 4;
                } elseif ($c > 223) {
                    $bytes = 3;
                } elseif ($c > 191) {
                    $bytes = 2;
                } else {
                    return false;
                }
                if (($i + $bytes) > $len) {
                    return false;
                }
                while ($bytes > 1) {
                    ++$i;
                    $b = ord($str[$i]);
                    if ($b < 128 || $b > 191) {
                        return false;
                    }
                    --$bytes;
                }
            }
        }
        return true;
    }

    public static function toUtf8($str, $charset = null) {
        if (!is_string($str)) {
            return $str;
        }
        // Check dependencies
        if (!function_exists('mb_detect_encoding') || !function_exists('iconv')) {
            // Fall back to a flawed but in most cases working native implementation.
            if (self::legacyIsUtf8($str)) {
                return $str;
            }
            return utf8_encode($str);
        }

        $charset = strtolower(empty($charset) ? '' : $charset);
        $actualCharset = $charset;
        // The encodings parameter is guesswork.
        $detectedCharset = mb_detect_encoding($str, 'ASCII, UTF-8, ISO-8859-1, ISO-8859-15', true);

        if ($detectedCharset === false) {
            $detectedCharset = mb_detect_encoding($str, 'auto');
        }
        $detectedCharset = strtolower((string)$detectedCharset);

        if (
            empty($charset)
            && !empty($detectedCharset)
            && ($charset !== $detectedCharset)
        ) {
            $actualCharset = $detectedCharset;
        } elseif (
            // Someone is lying.
            ($charset === 'utf-8')
            && ($detectedCharset !== 'utf-8')
            && !mb_check_encoding($str, 'UTF-8')
            && preg_match('//u', $str) !== 1 // this here seems to be the most reliable.
        ) {
            $actualCharset = $detectedCharset;
        }

        if (!empty($actualCharset) && ($actualCharset !== 'utf-8')) {
            $str = iconv($actualCharset, 'UTF-8//TRANSLIT//IGNORE', $str);
        }
        return $str;
    }

}
