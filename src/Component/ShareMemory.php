<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/9/10
 * Time: 下午5:30
 */

namespace Core\Component;


use Core\Component\IO\FileIO;
use Core\Component\Spl\SplArray;

/**
 * Class ShareMemory
 * @package Core\Component
 */
class ShareMemory
{
    /**
     * @var null|string
     */
    private $file;
    /**
     * @var FileIO
     */
    private $fileStream;
    /**
     * @var
     */
    private static $instance;
    /**
     * @var int
     */
    private $ioTimeOut = 200000;
    /**
     * @var bool
     */
    private $isStartTransaction = false;
    /**
     * @var SplArray
     */
    private $data = null;
    /**
     * @var string
     */
    private $serializeType;
    /**
     *
     */
    const SERIALIZE_TYPE_JSON = 'SERIALIZE_TYPE_JSON';
    /**
     *
     */
    const SERIALIZE_TYPE_SERIALIZE = 'SERIALIZE_TYPE_SERIALIZE';

    /**
     * 通过文件+锁的方式来实现数据共享，建议将文件设置到/dev/shm下
     * ShareMemory constructor.
     * @param string $serializeType
     * @param null   $file
     */
    function __construct($serializeType = self::SERIALIZE_TYPE_JSON, $file = null)
    {
        $this->serializeType = $serializeType;
        if ($file == null) {
            $file = Di::getInstance()->get(SysConst::SHARE_MEMORY_FILE);
            if (empty($file)) {
                $file = Di::getInstance()->get(SysConst::TEMP_DIRECTORY) . "/shareMemory.men";
            }
        }
        $this->file = $file;
    }

    /**
     * 默认等待2秒
     * @param string $serializeType
     * @param null   $file
     * @return static
     */
    static function getInstance($serializeType = self::SERIALIZE_TYPE_JSON, $file = null)
    {
        if (!isset(self::$instance)) {
            self::$instance = new static($serializeType, $file);
        }
        return self::$instance;
    }

    /**
     * @param $ioTimeOut
     */
    function setIoTimeOut($ioTimeOut)
    {
        $this->ioTimeOut = $ioTimeOut;
    }

    /**
     * @return bool
     */
    function startTransaction()
    {
        if ($this->isStartTransaction) {
            return true;
        } else {
            $this->fileStream = new FileIO($this->file);
            if ($this->fileStream->getStreamResource()) {
                //是否阻塞
                if ($this->ioTimeOut) {
                    $takeTime = 0;
                    while (!$this->fileStream->lock(LOCK_EX | LOCK_NB)) {
                        if ($takeTime > $this->ioTimeOut) {
                            $this->fileStream->close();
                            unset($this->fileStream);
                            return false;
                        }
                        usleep(5);
                        $takeTime = $takeTime + 5;
                    }
                    $this->isStartTransaction = true;
                    $this->read();
                    return true;
                } else {
                    if ($this->fileStream->lock()) {
                        $this->isStartTransaction = true;
                        $this->read();
                        return true;
                    } else {
                        $this->fileStream->close();
                        unset($this->fileStream);
                        return false;
                    }
                }
            } else {
                return false;
            }
        }
    }

    /**
     * @return bool
     */
    function commit()
    {
        if ($this->isStartTransaction) {
            $this->write();
            if ($this->fileStream->unlock()) {
                $this->data               = null;
                $this->isStartTransaction = false;
                $this->fileStream->close();
                unset($this->fileStream);
                return true;
            } else {
                return false;
            }

        } else {
            return false;
        }
    }

    /**
     * @param bool $autoCommit
     * @return bool
     */
    function rollback($autoCommit = false)
    {
        if ($this->isStartTransaction) {
            $this->read();
            if ($autoCommit) {
                $this->commit();
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $key
     * @param $val
     * @return bool
     */
    function set($key, $val)
    {
        if ($this->isStartTransaction) {
            $this->data->set($key, $val);
            return true;
        } else {
            if ($this->startTransaction()) {
                $this->data->set($key, $val);
                return $this->commit();
            } else {
                return false;
            }
        }
    }

    /**
     * @param $key
     * @return bool
     */
    function del($key)
    {
        return $this->set($key, null);
    }

    /**
     * @param $key
     * @return bool
     */
    function get($key)
    {
        if ($this->isStartTransaction) {
            return $this->data->get($key);
        } else {
            if ($this->startTransaction()) {
                $data = $this->data->get($key);
                $this->commit();
                return $data;
            } else {
                return false;
            }
        }
    }

    /**
     * @return bool
     */
    function clear()
    {
        if ($this->isStartTransaction) {
            $this->data = new SplArray();
            return true;
        } else {
            if ($this->startTransaction()) {
                $this->data = new SplArray();
                return $this->commit();
            } else {
                return false;
            }
        }
    }

    /**
     * @return null
     */
    function all()
    {
        if ($this->isStartTransaction) {
            return $this->data->getArrayCopy();
        } else {
            if ($this->startTransaction()) {
                $data = $this->data->getArrayCopy();
                $this->commit();
                return $data;
            } else {
                return null;
            }
        }
    }

    /**
     * @return bool
     */
    private function read()
    {
        if ($this->isStartTransaction) {
            $data = $this->fileStream->getContents();
            if ($this->serializeType == self::SERIALIZE_TYPE_JSON) {
                $data       = json_decode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                $this->data = is_array($data) ? new SplArray($data) : new SplArray();
            } else {
                $data       = unserialize($data);
                $this->data = is_a($data, SplArray::class) ? $data : new SplArray();
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    private function write()
    {
        if ($this->isStartTransaction) {
            $this->fileStream->truncate();
            $this->fileStream->rewind();
            if ($this->serializeType == self::SERIALIZE_TYPE_JSON) {
                $data = json_encode($this->data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            } else {
                $data = serialize($this->data);
            }
            $this->fileStream->write($data);
            return true;
        } else {
            return false;
        }
    }
}