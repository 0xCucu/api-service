<?php

namespace Muskid\Service;

use GuzzleHttp\Client;

class ApiService
{
    public static $_ins;
    public $debug = false;
    private $verison;
    private $prefix;
    private $data;
    private $appid = "musikid_web";
    private $serectKey = "4675678127e967418d6c13c7e2a6c4f6";
    public $apiUrl = "http://api.music.io";
    private $defaultHeader = [
        'Accept' => 'application/vnd.musikid.{version}+json',
        'Content-Type' => 'application/json',
        'appid' => 'musikid_web',
        'deviceid' => 'web',
    ];
    private $header;
    private $makeSign;

    public static function getInstance()
    {
        if (isset(self::$_ins) && self::$_ins instanceof self) {
            (self::$_ins)->makeSign = false;
            return self::$_ins;
        } else {
            self::$_ins = new self();
            (self::$_ins)->makeSign = false;
            return self::$_ins;
        }
    }
    public function needSign()
    {
        $this->makeSign = true;
    }
    public function __call($name, $arguments)
    {
        $makeSign = $this->makeSign;
        $this->data['method'] = $name;
        $this->addHeader($this->defaultHeader);
        $header = $this->buildHeader($this->header);
        $appendsData = [];
        $params = $arguments ? $arguments : [0=>[]];
        if (isset($arguments[2])) {
            $appendsData = $arguments[2];
        }
        $uri = $this->buildUri();
        $res = $this->sendRequest($uri, $header, $params[0], $makeSign, $appendsData);
        if ($res) {
            $result = json_decode($res, true);
            $result['status'] = filter_var($result['status'], FILTER_VALIDATE_BOOLEAN);
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
            if (is_array($v)) {
                continue;
            }
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

    public function sendRequest($uri, $header, array $params, $makeSign, array $appendsData)
    {
        try {
            if ($this->makeSign) {
                $tmpData = [];

                $data['device_id'] = 'WEB';
                $data['format'] = 'json';
                $data['app_id'] = $this->appid;
                $data['sign_method'] = 'md5';
                $data['timestamp'] = (string) time();
                $data = $this->array_merge_hold_right($data, $params); //值合并如果重复保留右边的
                if (isset($data['data'])) {
                    $tmpData = $data['data'];
                    unset($data['data']);
                }
                $sign = $this->generateSign($data);
                $data['data'] = $tmpData;
                $data['sign'] = $sign;
            }
            if (count($appendsData) > 0) {
                $data = array_merge($data, $appendsData);
            }

            $clients = new client();
            $response = $clients->post(
                $this->apiUrl . '/' . $uri,
                [
                    'json' => $data,
                    'http_errors' => false,
                    'headers' => $header,
                    'verify' => false,
                ]
            );
            if ($response->getStatusCode() != 200) {
                if ($this->debug) {
                    throw new \Exception($response->getBody()->getContents());
                }
                throw new \Exception("请求失败");
            }
            return $response->getBody()->getContents();
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            if ($this->debug) {
                throw new \Exception($e->getMessage());
            }
            return json_encode([
                'code' => '500',
                'msg' => '请求超时',
                'status' => false,
            ]);

        } catch (\Exception $e) {
            if ($this->debug) {
                throw new \Exception($e->getMessage());
            }
            return json_encode([
                'code' => '500',
                'msg' => '请求超时',
                'status' => false,
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

        # code...
    }
    /**
     * 合并数组如果重复保留右边数组
     * @Author   Cucumber
     * @DateTime 2018-07-12
     * @param    [type]     $left  [description]
     * @param    [type]     $right [description]
     * @return   [type]            [description]
     */
    protected function array_merge_hold_right($left, $right)
    {
        $repeatKeys = [];
        foreach ($left as $key => $value) {
            if (isset($right[$key])) {
                $repeatKeys[$key] = $key;
            }
        }
        $newArray = array_merge($left, $right);
        foreach ($newArray as $key => $value) {
            if (isset($repeatKeys[$key])) {
                $newArray[$key] = $right[$key];
            }
            # code...
        }
        return $newArray;
    }

}
