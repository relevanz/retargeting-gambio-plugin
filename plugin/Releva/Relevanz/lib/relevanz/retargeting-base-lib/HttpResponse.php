<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
namespace Releva\Retargeting\Base;

/**
 * Simple helper class to assist with shopsystem compatibility.
 */
class HttpResponse
{
    protected $content = '';
    protected $headers = [];

    public function __construct($content, $headers = []) {
        $this->content = $content;
        $this->headers = $headers;
    }

    public function out() {
        foreach ($this->headers as $header) {
            header($header);
        }
        echo $this->content;
    }

    public function __toString() {
        return implode("\n", $this->headers)."\n\n".$this->content;
    }

}
