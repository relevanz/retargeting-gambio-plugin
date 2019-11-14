<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the GNU General Public License (Version 2)
[http://www.gnu.org/licenses/gpl-2.0.html]
--------------------------------------------------------------
*/
namespace RelevanzTracking\Lib;

interface Configuration
{
    /**
     * @return string
     */
    public static function getUrlCallback();

    /**
     * @return string
     */
    public static function getUrlExport();

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
