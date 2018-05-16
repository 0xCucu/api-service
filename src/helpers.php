<?php

use Muskid\Service\ApiService;

if (!function_exists('apiService')) {
    function apiService()
    {
        return ApiService::getInstance();
    }

}

