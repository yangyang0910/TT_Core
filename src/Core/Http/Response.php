<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/6/15
 * Time: 下午8:44
 */

namespace Core\Http;

use Core\Conf\Config;
use Core\Conf\Event;
use Core\Http\Message\Response as HttpResponse;
use Core\Http\Message\Status;
use Core\Http\Session\Response as SessionResponse;
use Core\Http\Session\Session;
use Core\Utility\Curl\Cookie;

/**
 * Class Response
 * @package Core\Http
 */
class Response extends HttpResponse
{
    /**
     *
     */
    const STATUS_NOT_END = 0;
    /**
     *
     */
    const STATUS_LOGICAL_END = 1;
    /**
     *
     */
    const STATUS_REAL_END = 2;
    /**
     * @var int
     */
    private $isEndResponse = 0;//1 逻辑end  2真实end
    /**
     * @var null|\swoole_http_response
     */
    private $swoole_http_response = null;
    /**
     * @var null
     */
    private $session = null;
    /**
     * @var null
     */
    private $writeData = null;
    /**
     * @var
     */
    private static $instance;

    /**
     * @param \swoole_http_response|NULL $response
     * @return Response
     */
    static function getInstance(\swoole_http_response $response = null)
    {
        if ($response !== null) {
            self::$instance = new Response($response);
        }
        return self::$instance;
    }

    /**
     * Response constructor.
     * @param \swoole_http_response $response
     */
    function __construct(\swoole_http_response $response)
    {
        parent::__construct();
        $this->swoole_http_response = $response;
    }

    /**
     *
     */
    function setDataSchema()
    {

    }

    /**
     * @param bool $realEnd
     */
    function end($realEnd = false)
    {
        if ($this->isEndResponse == self::STATUS_NOT_END) {
            Session::getInstance()->close();
            $this->isEndResponse = self::STATUS_LOGICAL_END;
        }
        if ($realEnd === true && $this->isEndResponse !== self::STATUS_REAL_END) {
            $this->isEndResponse = self::STATUS_REAL_END;
            //结束处理
            $status = $this->getStatusCode();
            $this->swoole_http_response->status($status);
            $headers = $this->getHeaders();
            foreach ($headers as $header => $val) {
                foreach ($val as $sub) {
                    $this->swoole_http_response->header($header, $sub);
                }
            }
            if (Config::getInstance()->getConf('APP_COOKIE_SESSION_ENABLE')) {
                $cookies = $this->getCookies();
                /** @var \Core\Utility\Curl\Cookie $cookie */
                foreach ($cookies as $cookie) {
                    $this->swoole_http_response->cookie(
                        $cookie->getName(),
                        $cookie->getValue(),
                        $cookie->getExpire(),
                        $cookie->getPath(),
                        $cookie->getDomain(),
                        $cookie->getSecure(),
                        $cookie->getHttponly()
                    );
                }
            }
            $write = $this->getBody()->__toString();
            if (!empty($write)) {
                $this->swoole_http_response->write($write);
            }
            $this->getBody()->close();
            $this->swoole_http_response->end();
        }
    }

    /**
     * @return int
     */
    function isEndResponse()
    {
        return $this->isEndResponse;
    }

    /**
     * @param $obj
     * @return bool
     */
    function write($obj)
    {
        if (!$this->isEndResponse()) {
            if (is_object($obj)) {
                if (method_exists($obj, "__toString")) {
                    $obj = $obj->__toString();
                } elseif (method_exists($obj, 'jsonSerialize')) {
                    $obj = json_encode($obj, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                } else {
                    $obj = var_export($obj, true);
                }
            } elseif (is_array($obj)) {
                $obj = json_encode($obj, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            $this->writeData = $obj;
            $this->getBody()->write($obj);
            return true;
        } else {
//            trigger_error("response has end");
            return false;
        }
    }

    /**
     * @param int  $statusCode
     * @param null $result
     * @param null $msg
     * @return bool
     */
    function writeJson($statusCode = 200, $result = null, $msg = null)
    {
        if (!$this->isEndResponse()) {
            $data = $this->writeData = $result;
            $this->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->withHeader('Content-type', 'application/json;charset=utf-8');
            $this->withStatus($statusCode);
            return true;
        } else {
            trigger_error("response has end");
            return false;
        }
    }

    /**
     * @return null|array|string
     */
    function getWriteData()
    {
        return $this->writeData;
    }

    /**
     * @param     $url
     * @param int $status
     */
    function redirect($url, $status = Status::CODE_MOVED_TEMPORARILY)
    {
        if (!$this->isEndResponse()) {
            //仅支持header重定向  不做meta定向
            $this->withStatus($status);
            $this->withHeader('Location', $url);
        } else {
            trigger_error("response has end");
        }
    }

    /**
     * @param        $name
     * @param null   $value
     * @param null   $expire
     * @param string $path
     * @param string $domain
     * @param bool   $secure
     * @param bool   $httponly
     * @return bool
     */
    public function setCookie(
        $name,
        $value = null,
        $expire = null,
        $path = '/',
        $domain = '',
        $secure = false,
        $httponly = false
    ) {
        if (Config::getInstance()->getConf('APP_COOKIE_SESSION_ENABLE')) {
            if (!$this->isEndResponse()) {
                $cookie = new Cookie();
                $cookie->setName($name);
                $cookie->setValue($value);
                $cookie->setExpire($expire);
                $cookie->setPath($path);
                $cookie->setDomain($domain);
                $cookie->setSecure($secure);
                $cookie->setHttponly($httponly);
                $this->withAddedCookie($cookie);
                return true;
            } else {
                trigger_error("response has end");
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * @param       $pathTo
     * @param array $attribute
     */
    function forward($pathTo, array $attribute = [])
    {
        $pathTo = UrlParser::pathInfo($pathTo);
        if (!$this->isEndResponse()) {
            if ($pathTo == UrlParser::pathInfo()) {
                trigger_error("you can not forward a request in the same path : {$pathTo}");
            } else {
                $request = Request::getInstance();
                $request->getUri()->withPath($pathTo);
                $response = Response::getInstance();
                foreach ($attribute as $key => $value) {
                    $request->withAttribute($key, $value);
                }
                Event::getInstance()->onRequest($request, $response);
                Dispatcher::getInstance()->dispatch();
            }
        } else {
            trigger_error("response has end");
        }
    }

    /**
     * @return SessionResponse|null
     */
    function session()
    {
        if (Config::getInstance()->getConf('APP_COOKIE_SESSION_ENABLE')) {
            if (!isset($this->session)) {
                $this->session = new SessionResponse();
            }
            return $this->session;
        }
        return null;
    }

    /**
     * @return null|\swoole_http_response
     */
    function getSwooleResponse()
    {
        return $this->swoole_http_response;
    }
}