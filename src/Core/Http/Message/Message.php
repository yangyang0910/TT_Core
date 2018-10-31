<?php

/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/6/13
 * Time: 下午7:01
 */

namespace Core\Http\Message;

use Core\Component\Hook;

/**
 * Class Message
 * @package Core\Http\Message
 */
class Message
{
    /**
     * @var string
     */
    private $protocolVersion = '1.1';
    /**
     * @var array
     */
    private $headers = [];
    /**
     * @var Stream
     */
    private $body;
    /**
     * @var Hook
     */
    private $hook;

    /**
     * Message constructor.
     * @param array|NULL  $headers
     * @param Stream|NULL $body
     * @param string      $protocolVersion
     */
    function __construct(array $headers = null, Stream $body = null, $protocolVersion = '1.1')
    {
        if ($headers != null) {
            $this->headers = $headers;
        }
        if ($body != null) {
            $this->body = $body;
        }
        $this->protocolVersion = $protocolVersion;
        $this->hook            = Hook::getInstance();
    }

    /**
     * @return Hook
     */
    public function hook()
    {
        return $this->hook;
    }

    /**
     * @return string
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * @param $version
     * @return $this
     */
    public function withProtocolVersion($version)
    {
        if ($this->protocolVersion === $version) {
            return $this;
        }
        $this->protocolVersion = $version;
        return $this;
    }

    /**
     * @return array|NULL
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasHeader($name)
    {
        return array_key_exists($name, $this->headers);
    }

    /**
     * @param $name
     * @return array|mixed
     */
    public function getHeader($name)
    {
        if (array_key_exists($name, $this->headers)) {
            return $this->headers[$name];
        } else {
            return [];
        }
    }

    /**
     * @param $name
     * @return string
     */
    public function getHeaderLine($name)
    {
        if (array_key_exists($name, $this->headers)) {
            return implode("; ", $this->headers[$name]);
        } else {
            return '';
        }
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function withHeader($name, $value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }
        if (isset($this->headers[$name]) && $this->headers[$name] === $value) {
            return $this;
        }
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function withAddedHeader($name, $value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }
        if (isset($this->headers[$name])) {
            $this->headers[$name] = array_merge($this->headers[$name], $value);
        } else {
            $this->headers[$name] = $value;
        }
        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function withoutHeader($name)
    {
        if (isset($this->headers[$name])) {
            unset($this->headers[$name]);
            return $this;
        } else {
            return $this;
        }
    }

    /**
     * @return Stream|NULL
     */
    public function getBody()
    {
        if ($this->body == null) {
            $this->body = new Stream('');
        }
        return $this->body;
    }

    /**
     * @param Stream $body
     * @return $this
     */
    public function withBody(Stream $body)
    {
        $this->body = $body;
        return $this;
    }
}