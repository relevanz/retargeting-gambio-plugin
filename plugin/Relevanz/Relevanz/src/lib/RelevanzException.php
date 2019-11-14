<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the GNU General Public License (Version 2)
[http://www.gnu.org/licenses/gpl-2.0.html]
--------------------------------------------------------------
*/
namespace RelevanzTracking\Lib;

use RuntimeException;
use Throwable;

/**
 * An own exception class to differenciate own exceptions from system exceptions.
 */
class RelevanzException extends RuntimeException {
    protected $remessage = null;

    public function __construct($message = null, $code = 0, Throwable $previous = null) {
        $m = '';
        if ($message instanceof RelevanzExceptionMessage) {
            $m = $message->getMessage();
            $this->remessage = $message;
        } else {
            $m = $message.'';
            $this->remessage = new RelevanzExceptionMessage($m);
        }

        parent::__construct($m, $code, $previous);
    }

    public function getSprintfArgs() {
        return $this->remessage->getSprintfArgs();
    }
}
