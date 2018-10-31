<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/6/14
 * Time: 下午12:32
 */

namespace Core\Http\Message;


/**
 * Class Uri
 * @package Core\Http\Message
 */
class Uri
{
    /**
     * @var string
     */
    private $host;
    /**
     * @var string
     */
    private $userInfo;
    /**
     * @var int
     */
    private $port = 80;
    /**
     * @var string
     */
    private $path;
    /**
     * @var string
     */
    private $query;
    /**
     * @var string
     */
    private $fragment;
    /**
     * @var string
     */
    private $scheme;

    /**
     * Uri constructor.
     * @param string $url
     */
    function __construct($url = '')
    {
        if ($url !== '') {
            $parts          = parse_url($url);
            $this->scheme   = isset($parts['scheme']) ? $parts['scheme'] : '';
            $this->userInfo = isset($parts['user']) ? $parts['user'] : '';
            $this->host     = isset($parts['host']) ? $parts['host'] : '';
            $this->port     = isset($parts['port']) ? $parts['port'] : 80;
            $this->path     = isset($parts['path']) ? $parts['path'] : '';
            $this->query    = isset($parts['query']) ? $parts['query'] : '';
            $this->fragment = isset($parts['fragment']) ? $parts['fragment'] : '';
            if (isset($parts['pass'])) {
                $this->userInfo .= ':' . $parts['pass'];
            }
        }
    }

    /**
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @return string
     */
    public function getAuthority()
    {
        $authority = $this->host;
        if ($this->userInfo !== '') {
            $authority = $this->userInfo . '@' . $authority;
        }
        if ($this->port !== null) {
            $authority .= ':' . $this->port;
        }
        return $authority;
    }

    /**
     * @return string
     */
    public function getUserInfo()
    {
        return $this->userInfo;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * @param $scheme
     * @return $this
     */
    public function withScheme($scheme)
    {
        if ($this->scheme === $scheme) {
            return $this;
        }
        $this->scheme = $scheme;
        return $this;
    }

    /**
     * @param      $user
     * @param null $password
     * @return $this
     */
    public function withUserInfo($user, $password = null)
    {
        $info = $user;
        if ($password != '') {
            $info .= ':' . $password;
        }
        if ($this->userInfo === $info) {
            return $this;
        }
        $this->userInfo = $info;
        return $this;
    }

    /**
     * @param $host
     * @return $this
     */
    public function withHost($host)
    {
        $host = strtolower($host);
        if ($this->host === $host) {
            return $this;
        }
        $this->host = $host;
        return $this;
    }

    /**
     * @param $port
     * @return $this
     */
    public function withPort($port)
    {
        if ($this->port === $port) {
            return $this;
        }
        $this->port = $port;
        return $this;
    }

    /**
     * @param $path
     * @return $this
     */
    public function withPath($path)
    {
        if ($this->path === $path) {
            return $this;
        }
        $this->path = $path;
        return $this;
    }

    /**
     * @param $query
     * @return $this
     */
    public function withQuery($query)
    {
        if ($this->query === $query) {
            return $this;
        }
        $this->query = $query;
        return $this;
    }

    /**
     * @param $fragment
     * @return $this
     */
    public function withFragment($fragment)
    {
        if ($this->fragment === $fragment) {
            return $this;
        }
        $this->fragment = $fragment;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $uri = '';
        // weak type checks to also accept null until we can add scalar type hints
        if ($this->scheme != '') {
            $uri .= $this->scheme . ':';
        }
        if ($this->getAuthority() != '' || $this->scheme === 'file') {
            $uri .= '//' . $this->getAuthority();
        }
        $uri .= $this->path;
        if ($this->query != '') {
            $uri .= '?' . $this->query;
        }
        if ($this->fragment != '') {
            $uri .= '#' . $this->fragment;
        }
        return $uri;
    }
}