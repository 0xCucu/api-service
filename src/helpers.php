<?php

use Muskid\Service\ApiService;

$GLOBALS['api'] = [
    'production' => 'https://open.musikid.com', //线上api 默认
    'staging' => 'http://api.musikid.wang', //测试环境
    'development' => 'http://api.music.io', //开发环境
    'local' => 'http://api.music.io', //本地环境
];

$GLOBALS['debug'] = false;
$GLOBALS['env'] = 'production';

if (!function_exists('apiServiceInt')) {
    function apiServiceInt($debug, $env = 'production')
    {
        $GLOBALS['debug'] = $debug;
        $GLOBALS['env'] = $env;
    }
}

if (!function_exists('apiService')) {
    function apiService()
    {
        $ins = ApiService::getInstance();
        //不是debug 模式直接走线上api
        if (!$GLOBALS['debug']) {
            $ins->apiUrl = $GLOBALS['api']['production'];
        } else {
            //debug模式 根据环境选api
            $apiUrl = isset($GLOBALS['api'][$GLOBALS['env']]) ? $GLOBALS['api'][$GLOBALS['env']] : $GLOBALS['api']['staging'];
            $ins->apiUrl = $apiUrl;
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
        //不是debug 模式直接走线上api
        if (!$GLOBALS['debug']) {
            $ins->apiUrl = $GLOBALS['api']['production'];
        } else {
            //debug模式 根据环境选api
            $apiUrl = isset($GLOBALS['api'][$GLOBALS['env']]) ? $GLOBALS['api'][$GLOBALS['env']] : $GLOBALS['api']['staging'];
            $ins->apiUrl = $apiUrl;
        }
        $ins->debug = $GLOBALS['debug'];
        return $ins;
    }

}
