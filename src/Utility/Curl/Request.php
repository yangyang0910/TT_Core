<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/23
 * Time: 上午11:17
 */

namespace Core\Utility\Curl;


/**
 * Class Request
 * @package Core\Utility\Curl
 */
class Request
{
    /**
     * @var array
     */
    protected $cookies = [];
    /**
     * @var array
     */
    protected $curlOPt = [
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_AUTOREFERER    => true,
        CURLOPT_USERAGENT      => "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET4.0C; .NET4.0E)",
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HEADER         => true,
    ];

    /**
     * Request constructor.
     * @param null  $url
     * @param array $opt
     */
    function __construct($url = null, array $opt = [])
    {
        $this->curlOPt[CURLOPT_URL] = $url;
        if (!empty($opt)) {
            $this->curlOPt = $opt + $this->curlOPt;
        }
    }

    /**
     * @param $data
     * @return $this
     */
    function setPost($data)
    {
        $this->curlOPt[CURLOPT_POST]       = true;
        $this->curlOPt[CURLOPT_POSTFIELDS] = $data;
        return $this;
    }

    /**
     * @param array $opt
     * @return $this
     */
    function setOpt(array $opt)
    {
        $this->curlOPt = $opt + $this->curlOPt;
        return $this;
    }

    /**
     * @param $url
     * @return $this
     */
    function setUrl($url)
    {
        $this->curlOPt[CURLOPT_URL] = $url;
        return $this;
    }

    /**
     * @return array
     */
    function getOpt()
    {
        return $this->curlOPt;
    }

    /**
     * @param Cookie $cookie
     */
    function addCookie(Cookie $cookie)
    {
        $this->cookies[$cookie->getName()] = $cookie;
    }

    /**
     * @param \Closure|NULL $callBack
     * @return Response|mixed
     */
    function exec(\Closure $callBack = null)
    {
        $curl = curl_init();
        $opt  = $this->getOpt();
        if (!empty($this->cookies)) {
            $str = '';
            foreach ($this->cookies as $cookie) {
                $str .= $cookie->__toString();
            }
            $opt[CURLOPT_COOKIE] = $str;
        }
        curl_setopt_array($curl, $opt);
        $result   = curl_exec($curl);
        $response = new Response($result, $curl, $this->cookies);
        if ($callBack) {
            return call_user_func($callBack, $response);
        } else {
            return $response;
        }
    }
}