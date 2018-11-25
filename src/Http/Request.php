<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/6/15
 * Time: 下午8:05
 */

namespace Core\Http;


use Core\Http\Message\ServerRequest;
use Core\Http\Message\Stream;
use Core\Http\Message\UploadFile;
use Core\Http\Message\Uri;
use Core\Utility\Validate\Validate;
use Core\Http\Session\Request as SessionRequest;

/**
 * Class Request
 * @package Core\Http
 */
class Request extends ServerRequest
{
    /**
     *
     */
    const REST_SPECIFICATION = 'REST_SPECIFICATION';

    /**
     * @var
     */
    private static $instance;
    /**
     * @var null|\swoole_http_request
     */
    private $swoole_http_request = null;
    /**
     * @var
     */
    private $session;
    /**
     * @var
     */
    private $specification;

    /**
     * @param \swoole_http_request|NULL $request
     * @return Request
     */
    static function getInstance(\swoole_http_request $request = null)
    {
        if ($request !== null) {
            self::$instance = new Request($request);
        }
        return self::$instance;
    }

    /**
     * Request constructor.
     * @param \swoole_http_request $request
     */
    function __construct(\swoole_http_request $request)
    {
        $this->swoole_http_request = $request;
        $this->initHeaders();
        $protocol = str_replace('HTTP/', '', $this->swoole_http_request->server['server_protocol']);
        $body     = new Stream($this->swoole_http_request->rawContent());
        $uri      = $this->initUri();
        $files    = $this->initFiles();
        $method   = $this->swoole_http_request->server['request_method'];
        parent::__construct($method, $uri, null, $body, $protocol, $this->swoole_http_request->server);
        $this->withCookieParams($this->initCookie())->withQueryParams($this->initGet())->withParsedBody($this->initPost())->withUploadedFiles($files);
    }

    /**
     * @param null $keyOrKeys
     * @param null $default
     * @return array|mixed|null
     */
    function getRequestParam($keyOrKeys = null, $default = null)
    {
        if (null !== $keyOrKeys) {
            if (is_string($keyOrKeys)) {
                if (null === $ret = $this->getParsedBody($keyOrKeys)) {
                    if (null === $ret = $this->getQueryParam($keyOrKeys)) {
                        if (null !== $default) {
                            $ret = $default;
                        }
                    }
                }
                return $ret;
            } elseif (is_array($keyOrKeys)) {
                if (!is_array($default)) {
                    $default = [];
                }
                $data     = $this->getRequestParam();
                $keysNull = array_fill_keys(array_values($keyOrKeys), null);
                if (null === $keysNull) {
                    $keysNull = [];
                }
                $all = array_merge($keysNull, $default, $data);
                $all = array_intersect_key($all, $keysNull);
                return $all;
            } else {
                return null;
            }
        } else {
            return array_merge($this->getParsedBody(), $this->getQueryParams());
        }
    }

    /**
     * @param Validate $validate
     * @return \Core\Utility\Validate\Message
     */
    function requestParamsValidate(Validate $validate)
    {
        return $validate->validate($this->getRequestParam());
    }

    /**
     * @param $specification
     */
    function setExtendSpecification($specification)
    {
        $this->specification = $specification;
    }

    /**
     * @return null|\swoole_http_request
     */
    function getSwooleRequest()
    {
        return $this->swoole_http_request;
    }

    /**
     * @param null $name
     * @return null
     */
    function getPostData($name = null)
    {
        if (
            $this->specification === self::REST_SPECIFICATION
            &&
            in_array($this->getMethod(), ['PUT', 'PATCH', 'POST'], true)
        ) {
            $data = json_decode($this->getSwooleRequest()->rawContent(),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $this->withParsedBody($data);
        }
        return $this->getParsedBody($name);
    }

    /**
     * @return SessionRequest
     */
    function session()
    {
        if (!isset($this->session)) {
            $this->session = new SessionRequest();
        }
        return $this->session;
    }

    /**
     * @return Uri
     */
    private function initUri()
    {
        $uri = new Uri();
        $uri->withScheme("http");
        $uri->withPath($this->swoole_http_request->server['path_info']);
        $query = isset($this->swoole_http_request->server['query_string']) ? $this->swoole_http_request->server['query_string'] : '';
        $uri->withQuery($query);
        $host = $this->swoole_http_request->header['host'];
        $host = explode(":", $host);
        $uri->withHost($host[0]);
        $port = isset($host[1]) ? $host[1] : 80;
        $uri->withPort($port);
        return $uri;
    }

    /**
     *
     */
    private function initHeaders()
    {
        $headers = $this->swoole_http_request->header;
        foreach ($headers as $header => $val) {
            $this->withAddedHeader($header, $val);
        }
    }

    /**
     * @return array
     */
    private function initFiles()
    {
        if (isset($this->swoole_http_request->files)) {
            $normalized = [];
            foreach ($this->swoole_http_request->files as $key => $value) {
                if (is_array($value) && !isset($value['tmp_name'])) {
                    $normalized[$key] = [];
                    foreach ($value as $file) {
                        $normalized[$key][] = $this->initFile($file);
                    }
                    continue;
                }
                $normalized[$key] = $this->initFile($value);
            }
            return $normalized;
        } else {
            return [];
        }
    }

    /**
     * @param array $file
     * @return UploadFile
     */
    private function initFile(array $file)
    {
        return new UploadFile(
            $file['tmp_name'],
            (int) $file['size'],
            (int) $file['error'],
            $file['name'],
            $file['type']
        );
    }

    /**
     * @return array
     */
    private function initCookie()
    {
        return isset($this->swoole_http_request->cookie) ? $this->swoole_http_request->cookie : [];
    }

    /**
     * @return array
     */
    private function initPost()
    {
        return isset($this->swoole_http_request->post) ? $this->swoole_http_request->post : [];
    }

    /**
     * @return array
     */
    private function initGet()
    {
        return isset($this->swoole_http_request->get) ? $this->swoole_http_request->get : [];
    }
}