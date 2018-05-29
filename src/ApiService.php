<?php

namespace Muskid\Service;

use GuzzleHttp\Client;

class ApiService
{
    public static $_ins;
    private $verison;
    private $prefix;
    private $data;
    private $appid = "musikid_web";
    private $serectKey = "4675678127e967418d6c13c7e2a6c4f6";
    private $apiUrl = "http://open.musikid.com";
    private $defaultHeader = [
        'Accept' => 'application/vnd.musikid.{version}+json',
        'Content-Type' => 'application/json',
        'appid' => 'musikid_web',
        'deviceid' => '{device_id}',
    ];
    private $header;

    public static function getInstance()
    {
        if (isset(self::$_ins) && self::$_ins instanceof self) {
            return self::$_ins;
        } else {
            return self::$_ins = new self();
        }
    }

    public function __call($name, $arguments)
    {
        $makeSign = true;
        if (isset($arguments[1])) {
            $makeSign = true;
        }
        $this->data['method'] = $name;
        $this->addHeader($this->defaultHeader);
        $header = $this->buildHeader($this->header);
        $appendsData = [];
        if (isset($arguments[2])) {
            $appendsData = $arguments[2];
        }
        $uri = $this->buildUri();
        $res = $this->sendRequest($uri, $header, $arguments[0], $makeSign, $appendsData);
        if ($res) {
            $result = json_decode($res, true);
            $this->data = array();
            $this->header = array();
            return $result;
        }
        return false;

        # code...
    }

    public function addHeader(array $header)
    {
        if ($this->header) {
            $this->header = array_merge($this->header, $header);
            return true;
        }
        $this->header = $header;
        return true;
    }

    public function setHeader(array $header)
    {
        $this->header = $header;
    }

    public function generateSign($params)
    {
        ksort($params);
        $tmps = [];
        foreach ($params as $k => $v) {
            $tmps[] = $k . $v;
        }
        $serectKey = $this->serectKey;

        $string = $serectKey . implode('', $tmps) . $serectKey;
        return strtoupper(md5($string));

    }

    public function getVariables()
    {
        return $this->data;
    }

    public function setVariables(array $variables)
    {
        if (!$this->data) {
            $this->data = $variables;
        } else {
            $this->data = array_merge($this->data, $variables);
        }
    }

    public function __get($value)
    {
        //设置版本
        if (!isset($this->data['version'])) {
            $this->data['version'] = $value;
            return $this;
        }

        $this->data['url'][] = $value;
        return $this;
    }

    public function buildUri()
    {
        if (isset($this->data['url'])) {
            $url = implode("/", $this->data['url']);
            return $url . '/' . $this->data['method'];
        }

        return $this->data['method'];

    }

    public function sendRequest($uri, $header, array $data, $makeSign, array $appendsData)
    {
        try{
            if ($makeSign) {
                $build = array();
                $build['format'] = 'json';
                $build['app_id'] = $this->appid;
                $build['sign_method'] = 'md5';
                $build['timestamp'] = (string)time();
                $build['sign'] = $this->generateSign($build);
                $build = array_merge($build,$data);
                $data = null;
                $data = $build;

            }
            if (count($appendsData) > 0) {
                $data = array_merge($data, $appendsData);
            }
            $client = new client(
                [
                    'timeout' => 6.5,
                    'base_uri' => $this->apiUrl,
                    'headers' => $header
                ]
            );

            $response = $client->request(
                'POST',
                $uri,
                [
                    'json' => $data,
                    'http_errors' => false,
                    'verify' => false
                ]
            );
            if ($response->getStatusCode() != 200) {
                throw new \Exception("请求失败");
            }
            return $response->getBody()->getContents();
        } catch (\GuzzleHttp\Exception\GuzzleException $e){

           return json_encode([
                'code' => '500',
                'msg' => '请求超时',
                'status' => false
            ]);

        } catch (\Exception $e) {
            return json_encode([
                'code' => '500',
                'msg' => '请求超时',
                'status' => false
            ]);
        }

    }

    public function buildHeader(array $api_header)
    {

        $header = preg_replace_callback(
            '/\{([\s\S]*?)\}/',
            function ($matches) {
                $var = $matches[1];
                if (!isset($this->data[$var])) {
                    return "";
                }
                return $this->data[$var];
            },
            $api_header
        );
        return $header;
    }
}