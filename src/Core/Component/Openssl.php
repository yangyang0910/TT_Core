<?php
/**
 * Created by PhpStorm.
 * User: safer
 * Date: 2018/10/21
 * Time: 15:13:56
 */

namespace Core\Component;


class Openssl
{
    private $key;
    private $method;

    function __construct($key, $method = 'DES-EDE3')
    {
        $this->key    = $key;
        $this->method = $method;
    }

    public function encrypt($data)
    {
        return openssl_encrypt($data, $this->method, $this->key);
    }

    public function decrypt($raw)
    {
        return openssl_decrypt($raw, $this->method, $this->key);
    }
}