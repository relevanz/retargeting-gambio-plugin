<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
namespace Releva\Retargeting\Base;

class Credentials
{
    protected $apiKey = '';
    protected $userId = 0;

    public function __construct($apiKey, $userId) {
        $this->apiKey = $apiKey;
        $this->userId = $userId;
    }

    public function getApiKey() {
        return $this->apiKey;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function getAuthHash() {
        return md5($this->apiKey.':'.$this->userId);
    }

    public function isComplete() {
        return !empty($this->apiKey) && ($this->userId > 0);
    }
}
