<?php

use Muskid\Service\ApiService;

$GLOBALS['debug'] = false;
if (!function_exists('apiServiceInt')) {
    function apiServiceInt($debug)
    {
        $GLOBALS['debug'] = $debug;
    }

}

if (!function_exists('apiService')) {
    function apiService()
    {
        $ins = ApiService::getInstance();
        if (!$GLOBALS['debug']) {
            $ins->apiUrl = "http://api.musikid.wang";
        }
        $ins->debug = $GLOBALS['debug'];
        $ins->needSign();
        return $ins;
    }

}

if (!function_exists('api')) {
    function api()
    {
        $ins = ApiService::getInstance();
        if ($GLOBALS['debug']) {
            $ins->apiUrl = "http://api.musikid.wang";
        }
        $ins->debug = $GLOBALS['debug'];
        return $ins;
    }

}
