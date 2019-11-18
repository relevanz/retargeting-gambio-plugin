<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
namespace RelevanzTracking\Lib;

interface ConfigurationInterface
{
    /**
     * @return Credentials
     */
    public static function getCredentials();

    public static function updateCredentials(Credentials $credentials);

    /**
     * @return string
     */
    public static function getPluginVersion();
}
