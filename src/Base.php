<?php

namespace Laravelvip\Kdniao;


use GuzzleHttp\Client;
use Laravelvip\Kdniao\Exceptions\InvalidArgumentException;

class Base
{
    protected $app_id;
    protected $app_key;

    protected $guzzleOptions = [];

    public function __construct()
    {
        $app_id = config('kdniao.app_id');
        $app_key = config('kdniao.app_key');

        if (empty($app_id)) {
            throw new InvalidArgumentException('APP Id Can not be empty');
        }

        if (empty($app_key)) {
            throw new InvalidArgumentException('APP key Can not be empty');
        }

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