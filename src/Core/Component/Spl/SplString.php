<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/4/1
 * Time: 下午2:52
 */

namespace Core\Component\Spl;


/**
 * Class SplString
 * @package Core\Component\Spl
 */
class SplString
{
    /**
     * @var string
     */
    private $rawString;

    /**
     * SplString constructor.
     * @param null $rawString
     */
    function __construct($rawString = null)
    {
        $this->rawString = (string)$rawString;
    }

    /**
     * @param $string
     * @return $this
     */
    function setString($string)
    {
        $this->rawString = (String)$string;
        return $this;
    }

    /**
     * @param int $length
     * @return $this
     */
    function split($length = 1)
    {
        $this->rawString = str_split($this->rawString, $length);
        return $this;
    }

    /**
     * @param       $desEncoding
     * @param array $detectList
     * @return $this
     */
    function encodingConvert(
        $desEncoding,
        $detectList = [
            'UTF-8',
            'ASCII',
            'GBK',
            'GB2312',
            'LATIN1',
            'BIG5',
            "UCS-2",
        ]
    ) {
        $fileType = mb_detect_encoding($this->rawString, $detectList);
        if ($fileType != $desEncoding) {
            $this->rawString = mb_convert_encoding($this->rawString, $desEncoding, $fileType);
            return $this;
        } else {
            return $this;
        }
    }

    /**
     * @return SplString
     */
    function toUtf8()
    {
        return $this->encodingConvert("UTF-8");
    }

    /**
     * @return $this
     */
    function unicodeToUtf8()
    {
        $this->rawString = preg_replace_callback(
            '/\\\\u([0-9a-f]{4})/i',
            function ($matches) {
                return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");
            },
            $this->rawString
        );
        return $this;
    }

    /**
     * @return $this
     */
    function toUnicode()
    {
        $raw = (string)$this->encodingConvert("UCS-2");
        $len = strlen($raw);
        $str = '';
        for ($i = 0; $i < $len - 1; $i = $i + 2) {
            $c  = $raw[$i];
            $c2 = $raw[$i + 1];
            if (ord($c) > 0) {   //两个字节的文字
                $str .= '\u' . base_convert(ord($c), 10, 16) . str_pad(base_convert(ord($c2), 10, 16), 2, 0,
                        STR_PAD_LEFT);
            } else {
                $str .= '\u' . str_pad(base_convert(ord($c2), 10, 16), 4, 0, STR_PAD_LEFT);
            }
        }
        $this->rawString = strtoupper($str);//转换为大写
        return $this;
    }

    /**
     * @param $separator
     * @return SplArray
     */
    function explode($separator)
    {
        return new SplArray(explode($separator, $this->rawString));
    }

    /**
     * @param $start
     * @param $length
     * @return $this
     */
    function subString($start, $length)
    {
        $this->rawString = substr($this->rawString, $start, $length);
        return $this;
    }

    /**
     * @param     $str
     * @param int $ignoreCase
     * @return int
     */
    function compare($str, $ignoreCase = 0)
    {
        if ($ignoreCase) {
            return strcasecmp($str, $this->rawString);
        } else {
            return strcmp($str, $this->rawString);
        }
    }

    /**
     * @param string $charList
     * @return $this
     */
    function lTrim($charList = " \t\n\r\0\x0B")
    {
        $this->rawString = ltrim($this->rawString, $charList);
        return $this;
    }

    /**
     * @param string $charList
     * @return $this
     */
    function rTrim($charList = " \t\n\r\0\x0B")
    {
        $this->rawString = rtrim($this->rawString, $charList);
        return $this;
    }

    /**
     * @param string $charList
     * @return $this
     */
    function trim($charList = " \t\n\r\0\x0B")
    {
        $this->rawString = trim($this->rawString, $charList);
        return $this;
    }

    /**
     * @param      $length
     * @param null $padString
     * @param int  $pad_type
     * @return $this
     */
    function pad($length, $padString = null, $pad_type = STR_PAD_RIGHT)
    {
        $this->rawString = str_pad($this->rawString, $length, $padString, $pad_type);
        return $this;
    }

    /**
     * @param $times
     * @return $this
     */
    function repeat($times)
    {
        $this->rawString = str_repeat($this->rawString, $times);
        return $this;
    }

    /**
     * @return int
     */
    function length()
    {
        return strlen($this->rawString);
    }

    /**
     * @return $this
     */
    function toUpper()
    {
        $this->rawString = strtoupper($this->rawString);
        return $this;
    }

    /**
     * @return $this
     */
    function toLower()
    {
        $this->rawString = strtolower($this->rawString);
        return $this;
    }

    /**
     * @param null $allowable_tags
     * @return $this
     */
    function stripTags($allowable_tags = null)
    {
        $this->rawString = strip_tags($this->rawString, $allowable_tags);
        return $this;
    }

    /**
     * @param $find
     * @param $replaceTo
     * @return $this
     */
    function replace($find, $replaceTo)
    {
        $this->rawString = str_replace($find, $replaceTo, $this->rawString);
        return $this;
    }


    /**
     * @param $startStr
     * @param $endStr
     * @return $this
     */
    function betweenInStr($startStr, $endStr)
    {
        $st = stripos($this->rawString, $startStr);
        $ed = stripos($this->rawString, $endStr);
        if (($st == false || $ed == false) || $st >= $ed) {
            $this->rawString = '';
        } else {
            $this->rawString = substr($this->rawString, ($st + 1), ($ed - $st - 1));
        }
        return $this;
    }

    /**
     * @param      $regex
     * @param bool $rawReturn
     * @return null
     */
    function regex($regex, $rawReturn = false)
    {
        preg_match($regex, $this->rawString, $result);
        if (!empty($result)) {
            if ($rawReturn) {
                return $result;
            } else {
                return $result[0];
            }
        } else {
            return null;
        }
    }

    /**
     * @param      $find
     * @param bool $ignoreCase
     * @return bool
     */
    function exist($find, $ignoreCase = true)
    {
        if ($ignoreCase) {
            $label = stripos($this->rawString, $find);
        } else {
            $label = strpos($this->rawString, $find);
        }
        return $label === false ? false : true;
    }

    /**
     * @return string
     */
    function __toString()
    {
        return (String)$this->rawString;
    }


}