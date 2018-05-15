<?php

use Muskid\Api\ApiService;

if (!function_exists('apiService')) {
    function apiService()
    {
        return ApiService::getInstance();
    }

}

