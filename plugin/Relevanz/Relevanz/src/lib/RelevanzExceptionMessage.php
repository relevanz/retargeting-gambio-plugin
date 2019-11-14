<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the GNU General Public License (Version 2)
[http://www.gnu.org/licenses/gpl-2.0.html]
--------------------------------------------------------------
*/
namespace RelevanzTracking\Lib;

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
