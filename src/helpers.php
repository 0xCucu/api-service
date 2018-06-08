<?php

use Muskid\Service\ApiService;

if (!function_exists('apiService')) {
    function apiService()
    {
        $ins = ApiService::getInstance();
        $ins->needSign();
        return $ins;
    }

}

if (!function_exists('api')) {
    function api()
    {
        return ApiService::getInstance();
    }

}

