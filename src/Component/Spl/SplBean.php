<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/4/29
 * Time: 下午1:54
 */

namespace Core\Component\Spl;

use Core\AbstractInterface\TSingleton;

/**
 * Class SplBean
 * @package Core\Component\Spl
 */
abstract class SplBean implements \JsonSerializable
{
    use TSingleton;

    /**
     *
     */
    const FILTER_NOT_NULL = 1;
    /**
     *
     */
    const FILTER_NOT_EMPTY = 2;//0 不算empty

    /**
     * SplBean constructor.
     * @param array|NULL $data
     * @param bool       $autoCreateProperty
     */
    public function __construct(array $data = null, $autoCreateProperty = false)
    {
        if ($data) {
            $this->arrayToBean($data, $autoCreateProperty);
        }
        $this->initialize();
    }

    /**
     * @return array
     */
    final public function allProperty()
    {
        $data = [];
        foreach ($this as $key => $item) {
            array_push($data, $key);
        }
        return $data;
    }

    /**
     * @param array|NULL $columns
     * @param null       $filter
     * @return array
     */
    function toArray(array $columns = null, $filter = null)
    {
        $data = $this->jsonSerialize();
        if ($columns) {
            $data = array_intersect_key($data, array_flip($columns));
        }
        if ($filter === self::FILTER_NOT_NULL) {
            return array_filter($data, function ($val) {
                return !is_null($val);
            });
        } elseif ($filter === self::FILTER_NOT_EMPTY) {
            return array_filter($data, function ($val) {
                if ($val === 0 || $val === '0') {
                    return true;
                } else {
                    return !empty($val);
                }
            });
        } elseif (is_callable($filter)) {
            return array_filter($data, $filter);
        }
        return $data;
    }

    /**
     * @param array $data
     * @param bool  $autoCreateProperty
     * @return SplBean
     */
    final public function arrayToBean(array $data, $autoCreateProperty = false)
    {
        if ($autoCreateProperty == false) {
            $data = array_intersect_key($data, array_flip($this->allProperty()));
        }
        foreach ($data as $key => $item) {
            $this->addProperty($key, $item);
        }
        return $this;
    }

    /**
     * @param      $name
     * @param null $value
     * @return $this
     */
    final public function addProperty($name, $value = null)
    {
        $this->$name = $value;
        return $this;
    }

    /**
     * @param $name
     * @return null
     */
    final public function getProperty($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        } else {
            return null;
        }
    }

    /**
     * @param $name
     * @return null
     */
    final public function removeProperty($name)
    {
        if (isset($this->$name)) {
            unset($this->$name);
        } else {
            return null;
        }
    }

    /**
     * @return array|mixed
     */
    final public function jsonSerialize()
    {
        $data = [];
        foreach ($this as $key => $item) {
            $data[$key] = $item;
        }
        return $data;
    }

    /**
     *
     */
    protected function initialize()
    {

    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->jsonSerialize(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param array $data
     * @return $this
     */
    public function restore(array $data = [])
    {
        $this->arrayToBean($data + get_class_vars(static::class));
        $this->initialize();
        return $this;
    }
}