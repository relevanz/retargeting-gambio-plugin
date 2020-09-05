<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
namespace Releva\Retargeting\Base;

use Releva\Retargeting\Base\Exception\RelevanzException;
use Releva\Retargeting\Base\Exception\RelevanzExceptionMessage;

use Releva\Retargeting\Base\Credentials;

class RelevanzApi
{
    const RELEVANZ_STATS_FRAME = 'https://frontend.releva.nz/token?token=';
    const RELEVANZ_KEY_URL     = 'https://backend.releva.nz/v1/campaigns/get?apikey=';
    const RELEVANZ_TRACKER_URL = 'https://pix.hyj.mobi/rt';
    const RELEVANZ_CONV_URL    = 'https://d.hyj.mobi/conv';

    public static function makeWebRequest($url, $timeout = 5) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $result = array(
            'response' => curl_exec($ch),
            'info'  => curl_getinfo($ch),
            'errno' => curl_errno($ch),
            'error' => curl_error($ch),
        );
        curl_close($ch);
        return $result;
    }

    public static function verifyApiKey($apikey, $addParams = []) {
        $apikey = trim(preg_replace('/[^a-zA-Z0-9_-]/', '', $apikey));
        if (empty($apikey)) {
            throw new RelevanzException('The provided API-Key is invalid. Please provide a correct API-Key.', 1553934614);
        }
        $response = self::makeWebRequest(
            self::RELEVANZ_KEY_URL.$apikey.(!empty($addParams) ? '&'.http_build_query($addParams) : '')
        );
        if ($response['errno'] !== 0) {
            throw new RelevanzException(
                new RelevanzExceptionMessage(
                    'Unable to connect to API-Server. Error Details [%1$d]: %2$s',
                    [$response['errno'], $response['error']]
                ),
                1553935480
            );
        }

        $apiErr = 'The API key cannot be verified. Please make sure that the API key is correct.';
        if (!isset($response['info']['http_code'])
            || ($response['info']['http_code'] !== 200)
        ) {
            throw new RelevanzException($apiErr, 1553935569);
        }
        $apiResponse = json_decode($response['response'], true);
        if (!is_array($apiResponse) || !isset($apiResponse['user_id']) || !((int)$apiResponse['user_id'] > 0)) {
            throw new RelevanzException($apiErr, 1553935786);
        }
        // As of 2020-06-17 the response contains the following informations:
        //    ["user_id"]     => int
        return new Credentials($apikey, (int)$apiResponse['user_id']);
    }

}
