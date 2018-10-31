<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/23
 * Time: 上午12:46
 */

namespace Core\Http;


/**
 * Class UrlParser
 * @package Core\Http
 */
class UrlParser
{
    /**
     * @param null $path
     *
     * @return string
     */
    static public function pathInfo($path = null)
    {
        if ($path == null) {
            $path = Request::getInstance()->getUri()->getPath();
        }
        $basePath = dirname($path);
        $info     = pathInfo($path);
        if ($info['filename'] != 'index') {
            if ($basePath == '/') {
                $basePath = $basePath . $info['filename'];
            } else {
                $basePath = $basePath . '/' . $info['filename'];
            }
        }
        return $basePath;
    }

    /**
     * @param        $controllerClass
     * @param string $action
     * @param array  $query
     *
     * @return string
     */
    static public function generateURL($controllerClass, $action = 'index', $query = [])
    {
        $controllerClass = substr($controllerClass, 14);
        $controllerClass = explode('\\', $controllerClass);
        $path            = implode("/", $controllerClass);
        if ($action == 'index') {
            $path = $path . "/index.html";
        } else {
            $path = $path . "/{$action}/index.html";
        }
        if (!empty($query)) {
            return $path . "?" . http_build_cookie($query);
        } else {
            return $path;
        }
    }
}