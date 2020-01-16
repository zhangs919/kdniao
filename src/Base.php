<?php

namespace Laravelvip\Kdniao;


use GuzzleHttp\Client;
use Laravelvip\Kdniao\Exceptions\InvalidArgumentException;

class Base
{
    // 正式地址
    protected $api = 'http://api.kdniao.com/api/dist';

    // 测试地址
//    protected $api = 'http://sandboxapi.kdniao.com:8080/kdniaosandbox/gateway/exterfaceInvoke.json';

    protected $app_id;
    protected $app_key;

    protected $guzzleOptions = [];

    public function __construct($app_id, $app_key, $api = null)
    {
        if (!$api) {
            $api = $this->api;
        }
        if (empty($app_id)) {
            throw new InvalidArgumentException('APP Id Can not be empty');
        }

        if (empty($app_key)) {
            throw new InvalidArgumentException('APP key Can not be empty');
        }

        $this->api = $api;
        $this->app_id = $app_id;
        $this->app_key = $app_key;
    }

    /**
     * 数据签名
     * @param $data
     * @param $appkey
     * @return string
     */
    protected function encrypt($data, $appkey)
    {
        return urlencode(base64_encode(md5($data . $appkey)));
    }

    /**
     * @return Client
     */
    public function getHttpClient()
    {
        return new Client($this->guzzleOptions);
    }

    /**
     * @return Client
     */
    public function getGuzzleOptions()
    {
        return new Client($this->guzzleOptions);
    }

    /**
     * @param $options
     */
    public function setGuzzleOptions($options)
    {
        $this->guzzleOptions = $options;
    }
}