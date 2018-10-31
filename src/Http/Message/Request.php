<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/6/14
 * Time: 下午12:25
 */

namespace Core\Http\Message;


/**
 * Class Request
 * @package Core\Http\Message
 */
class Request extends Message
{
    /**
     * @var Uri
     */
    private $uri;
    /**
     * @var string
     */
    private $method;
    /**
     * @var
     */
    private $target;

    /**
     * Request constructor.
     * @param string      $method
     * @param Uri|NULL    $uri
     * @param array|NULL  $headers
     * @param Stream|NULL $body
     * @param string      $protocolVersion
     */
    function __construct(
        $method = 'GET',
        Uri $uri = null,
        array $headers = null,
        Stream $body = null,
        $protocolVersion = '1.1'
    ) {
        $this->method = $method;
        if ($uri != null) {
            $this->uri = $uri;
        }
        parent::__construct($headers, $body, $protocolVersion);
    }

    /**
     * @return string
     */
    public function getRequestTarget()
    {
        if (!empty($this->target)) {
            return $this->target;
        }
        if ($this->uri instanceof Uri) {
            $target = $this->uri->getPath();
            if ($target == '') {
                $target = '/';
            }
            if ($this->uri->getQuery() != '') {
                $target .= '?' . $this->uri->getQuery();
            }
        } else {
            $target = "/";
        }
        return $target;
    }

    /**
     * @param $requestTarget
     * @return $this
     */
    public function withRequestTarget($requestTarget)
    {
        $this->target = $requestTarget;
        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param $method
     * @return $this
     */
    public function withMethod($method)
    {
        $this->method = strtoupper($method);
        return $this;
    }

    /**
     * @return Uri|NULL
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param Uri  $uri
     * @param bool $preserveHost
     * @return $this
     */
    public function withUri(Uri $uri, $preserveHost = false)
    {
        if ($uri === $this->uri) {
            return $this;
        }
        $this->uri = $uri;
        if (!$preserveHost) {
            $host = $this->uri->getHost();
            if (!empty($host)) {
                if (($port = $this->uri->getPort()) !== null) {
                    $host .= ':' . $port;
                }
                if ($this->getHeader('host')) {
                    $header = $this->getHeader('host');
                } else {
                    $header = 'Host';
                }
                $this->withHeader($header, $host);
            }
        }
        return $this;
    }
}