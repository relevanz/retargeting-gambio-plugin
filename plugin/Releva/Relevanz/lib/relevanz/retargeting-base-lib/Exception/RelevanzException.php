<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
namespace Releva\Retargeting\Base\Exception;

use RuntimeException;

/**
 * An own exception class to differenciate own exceptions from system exceptions.
 */
class RelevanzException extends RuntimeException {
    protected $remessage = null;

    public function __construct($message = null, $code = 0, $previous = null) {
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
