<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
namespace RelevanzTracking\Lib\Exception;

class RelevanzExceptionMessage {
    protected $message = '';
    protected $sprintf = [];

    public function __construct($message, array $sprintf = []) {
        $this->message = $message;
        $this->sprintf = $sprintf;
    }

    public function getMessage() {
        return $this->message;
    }

    public function getSprintfArgs() {
        return $this->sprintf;
    }
}
