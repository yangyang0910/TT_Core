<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/6/15
 * Time: 下午1:44
 */

namespace Core\Http\Message;


/**
 * Class ServerRequest
 * @package Core\Http\Message
 */
class ServerRequest extends Request
{
    /**
     * @var array
     */
    private $attributes = [];
    /**
     * @var array
     */
    private $cookieParams = [];
    /**
     * @var
     */
    private $parsedBody;
    /**
     * @var array
     */
    private $queryParams = [];
    /**
     * @var array
     */
    private $serverParams;
    /**
     * @var array
     */
    private $uploadedFiles = [];

    /**
     * ServerRequest constructor.
     * @param string      $method
     * @param Uri|NULL    $uri
     * @param array|NULL  $headers
     * @param Stream|NULL $body
     * @param string      $protocolVersion
     * @param array       $serverParams
     */
    function __construct(
        $method = 'GET',
        Uri $uri = null,
        array $headers = null,
        Stream $body = null,
        $protocolVersion = '1.1',
        $serverParams = []
    ) {
        $this->serverParams = $serverParams;
        parent::__construct($method, $uri, $headers, $body, $protocolVersion);
    }

    /**
     * @return array
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * @param null $name
     * @return array|mixed|null
     */
    public function getCookieParams($name = null)
    {
        if ($name === null) {
            return $this->cookieParams;
        } else {
            if (isset($this->cookieParams[$name])) {
                return $this->cookieParams[$name];
            } else {
                return null;
            }
        }
    }

    /**
     * @param array $cookies
     * @return $this
     */
    public function withCookieParams(array $cookies)
    {
        $this->cookieParams = $cookies;
        return $this;
    }

    /**
     * @return array
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getQueryParam($name)
    {
        $data = $this->getQueryParams();
        if (isset($data[$name])) {
            return $data[$name];
        } else {
            return null;
        }
    }

    /**
     * @param array $query
     * @return $this
     */
    public function withQueryParams(array $query)
    {
        $this->queryParams = $query;
        return $this;
    }

    /**
     * @return array
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getUploadedFile($name)
    {
        if (isset($this->uploadedFiles[$name])) {
            return $this->uploadedFiles[$name];
        } else {
            return null;
        }
    }

    /**
     * @param array $uploadedFiles must be array of UploadFile Instance
     * @return ServerRequest
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $this->uploadedFiles = $uploadedFiles;
        return $this;
    }

    /**
     * @param null $name
     * @return null
     */
    public function getParsedBody($name = null)
    {
        if ($name !== null) {
            if (isset($this->parsedBody[$name])) {
                return $this->parsedBody[$name];
            } else {
                return null;
            }
        } else {
            return $this->parsedBody;
        }
    }

    /**
     * @param $data
     * @return $this
     */
    public function withParsedBody($data)
    {
        $this->parsedBody = $data;
        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param      $name
     * @param null $default
     * @return mixed|null
     */
    public function getAttribute($name, $default = null)
    {
        if (false === array_key_exists($name, $this->attributes)) {
            return $default;
        }
        return $this->attributes[$name];
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function withAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function withoutAttribute($name)
    {
        if (false === array_key_exists($name, $this->attributes)) {
            return $this;
        }
        unset($this->attributes[$name]);
        return $this;
    }
}