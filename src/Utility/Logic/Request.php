<?php
/**
 * Created by PhpStorm.
 * User: safer
 * Date: 2018/6/6
 * Time: 0:11
 */

namespace Core\Utility\Logic;

/**
 * Class Request
 * @package Core\Utility\Logic\ALogic
 */
class Request
{
//    const PAGE_DEFAULT = [
//        'page'     => 1,
//        'limit'    => 10,
//        'start'    => 0,
//        'total'    => 0,
//        'is_first' => 0,
//    ];

    const PAGE_DEFAULT = [
        'p_num'   => 1,
        'p_start' => 0,
        'p_limit' => 10,
        'p_total' => 0,
    ];

    protected $id;
    protected $data;
    protected $where;
    protected $field;
    protected $order;
    protected $extend;
    protected $page;

    private static $instance;

    static function getInstance()
    {
        // if (!self::$instance) {
        //     self::$instance = new self();
        // }
        // return self::$instance;
        return new self();
    }

    /**
     * @param null|string|int $key
     *
     * @return array|null|string
     */
    public function getId($key = null)
    {
        return $this->_resolveData($this->id, $key);
    }

    /**
     * @param string|int $id
     *
     * @return Request
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param null|string|int $key
     *
     * @return array|null|string
     */
    public function getData($key = null)
    {
        return $this->_resolveData($this->data, $key);
    }

    /**
     * @param array $data
     *
     * @return Request
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param null|string|int $key
     *
     * @return array|null|string
     */
    public function getWhere($key = null)
    {
        return $this->_resolveData($this->where, $key);
    }

    /**
     * @param array $where
     *
     * @return Request
     */
    public function setWhere(array $where)
    {
        $this->where = $where;
        return $this;
    }

    /**
     * @param null $key
     *
     * @return null|string
     */
    public function getField($key = null)
    {
        if (!$data = $this->field) {
            return null;
        }
        if ($key === null) {
            return !empty($data)
                ? \join(',', $data)
                : null;
        }
        return isset($data[$key])
            ? \join(',', $data[$key])
            : null;
    }

    /**
     * @param array $field
     *
     * @return Request
     */
    public function setField(array $field)
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @param string|null $key
     *
     * @return array|null|string
     */
    public function getOrder($key = null)
    {
        return $this->_resolveData($this->order, $key);
    }

//    /**
//     * @param string|null $key
//     * @return null|string
//     */
//    public function getOrder($key = NULL)
//    {
//        if (!$data = $this->order) {
//            return NULL;
//        }
//        if ($key === NULL) {
//            return !empty($data)
//                ? \join(',', $data)
//                : NULL;
//        }
//        return isset($data[$key])
//            ? \join(',', $data[$key])
//            : NULL;
//    }

    /**
     * @param array $order
     *
     * @return Request
     */
    public function setOrder(array $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param null|string|int $key
     *
     * @return array|null|string
     */
    public function getExtend($key = null)
    {
        return $this->_resolveData($this->extend, $key);
    }

    /**
     * @param array $extend
     *
     * @return Request
     */
    public function setExtend(array $extend)
    {
        $this->extend = $extend;
        return $this;
    }

    /**
     * @param null|string|int $key
     *
     * @return array|null|string
     */
    public function getPage($key = null)
    {
        return $this->_resolveData($this->page, $key);
    }

    /**
     * @param array $page
     *
     * @return Request
     */
    public function setPage(array $page)
    {
        if ($page['p_limit'] > 300) {
            $page['p_limit'] = 300;
        }
        $this->page = \array_merge(self::PAGE_DEFAULT, $page);
        return $this;
    }

    /**
     * @param $data
     * @param $key
     *
     * @return null|array|string
     */
    private function _resolveData($data, $key)
    {
        if (null === $data) {
            return null;
        }
        if ($key === null) {
            return !empty($data)
                ? $data
                : null;
        }
        return isset($data[$key])
            ? $data[$key]
            : null;
    }
}