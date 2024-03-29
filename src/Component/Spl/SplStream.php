<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/5
 * Time: 上午11:58
 */

namespace Core\Component\Spl;


/**
 * Class SplStream
 * @package Core\Component\Spl
 */
class SplStream
{
    /**
     * @var bool|resource
     */
    private $stream;
    /**
     * @var
     */
    private $seekable;
    /**
     * @var bool
     */
    private $readable;
    /**
     * @var bool
     */
    private $writable;
    /**
     * @var array
     */
    private $readList = [
        'r'   => true,
        'w+'  => true,
        'r+'  => true,
        'x+'  => true,
        'c+'  => true,
        'rb'  => true,
        'w+b' => true,
        'r+b' => true,
        'x+b' => true,
        'c+b' => true,
        'rt'  => true,
        'w+t' => true,
        'r+t' => true,
        'x+t' => true,
        'c+t' => true,
        'a+'  => true,
    ];
    /**
     * @var array
     */
    private $writeList = [
        'w'   => true,
        'w+'  => true,
        'rw'  => true,
        'r+'  => true,
        'x+'  => true,
        'c+'  => true,
        'wb'  => true,
        'w+b' => true,
        'r+b' => true,
        'x+b' => true,
        'c+b' => true,
        'w+t' => true,
        'r+t' => true,
        'x+t' => true,
        'c+t' => true,
        'a'   => true,
        'a+'  => true,
    ];

    /**
     * SplStream constructor.
     * @param string $resource
     * @param string $mode
     */
    function __construct($resource = '', $mode = 'r+')
    {
        switch (gettype($resource)) {
            case 'resource':
                {
                    $this->stream = $resource;
                    break;
                }
            case 'object':
                {
                    if (method_exists($resource, '__toString')) {
                        $resource     = $resource->__toString();
                        $this->stream = fopen('php://memory', $mode);
                        if ($resource !== '') {
                            fwrite($this->stream, $resource);
                        }
                        break;
                    } else {
                        throw new \InvalidArgumentException('Invalid resource type: ' . gettype($resource));
                    }
                }
            default:
                {
                    $this->stream = fopen('php://memory', $mode);
                    try {
                        $resource = (string)$resource;
                        if ($resource !== '') {
                            fwrite($this->stream, $resource);
                        }
                    } catch (\Exception $exception) {
                        throw new \InvalidArgumentException('Invalid resource type: ' . gettype($resource));
                    }
                }
        }
        $info           = stream_get_meta_data($this->stream);
        $this->seekable = $info['seekable'];
        $this->readable = isset($this->readList[$info['mode']]);
        $this->writable = isset($this->writeList[$info['mode']]);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        try {
            $this->seek(0);
            return (string)stream_get_contents($this->stream);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     *
     */
    public function close()
    {
        $res = $this->detach();
        if (is_resource($res)) {
            fclose($res);
        }
    }

    /**
     * @return bool|null|resource
     */
    public function detach()
    {
        if (!isset($this->stream)) {
            return null;
        }
        $this->readable = $this->writable = $this->seekable = false;
        $result         = $this->stream;
        unset($this->stream);
        return $result;
    }

    /**
     * @return null
     */
    public function getSize()
    {
        $stats = fstat($this->stream);
        if (isset($stats['size'])) {
            return $stats['size'];
        } else {
            return null;
        }
    }

    /**
     * @return bool|int
     */
    public function tell()
    {
        $result = ftell($this->stream);
        if ($result === false) {
            throw new \RuntimeException('Unable to determine stream position');
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function eof()
    {
        return !$this->stream || feof($this->stream);
    }

    /**
     * @return mixed
     */
    public function isSeekable()
    {
        return $this->seekable;
    }

    /**
     * @param     $offset
     * @param int $whence
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (!$this->seekable) {
            throw new \RuntimeException('Stream is not seekable');
        } elseif (fseek($this->stream, $offset, $whence) === -1) {
            throw new \RuntimeException('Unable to seek to stream position '
                . $offset . ' with whence ' . var_export($whence, true));
        }
    }

    /**
     *
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * @return bool
     */
    public function isWritable()
    {
        return $this->writable;
    }

    /**
     * @param $string
     * @return bool|int
     */
    public function write($string)
    {
        if (!$this->writable) {
            throw new \RuntimeException('Cannot write to a non-writable stream');
        }
        $result = fwrite($this->stream, $string);
        if ($result === false) {
            throw new \RuntimeException('Unable to write to stream');
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function isReadable()
    {
        return $this->readable;
    }

    /**
     * @param $length
     * @return bool|string
     */
    public function read($length)
    {
        if (!$this->readable) {
            throw new \RuntimeException('Cannot read from non-readable stream');
        }
        if ($length < 0) {
            throw new \RuntimeException('Length parameter cannot be negative');
        }
        if (0 === $length) {
            return '';
        }
        $string = fread($this->stream, $length);
        if (false === $string) {
            throw new \RuntimeException('Unable to read from stream');
        }
        return $string;
    }

    /**
     * @return bool|string
     */
    public function getContents()
    {
        $contents = stream_get_contents($this->stream);
        if ($contents === false) {
            throw new \RuntimeException('Unable to read stream contents');
        }
        return $contents;
    }

    /**
     * @param null $key
     * @return array|null
     */
    public function getMetadata($key = null)
    {
        if (!isset($this->stream)) {
            return $key ? null : [];
        } elseif (!$key) {
            return stream_get_meta_data($this->stream);
        } else {
            $meta = stream_get_meta_data($this->stream);
            return isset($meta[$key]) ? $meta[$key] : null;
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @return bool|resource
     */
    function getStreamResource()
    {
        return $this->stream;
    }

    /**
     * @param int $size
     * @return bool
     */
    function truncate($size = 0)
    {
        return ftruncate($this->stream, $size);
    }
}