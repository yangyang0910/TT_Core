<?php
/**
 * Created by PhpStorm.
 * User: safer
 * Date: 2018/12/2
 * Time: 22:22:33
 */

namespace Core\Utility;


//本ArrayToTextTable改编自网络，兼容适配了utf8和不再依赖第三方包
/*
 * $data = [
    [
        '姓名' => 'James',
        '年龄' => '20',
        'sex'=>'男'
    ],
    [
        '姓名' => '这是测试姓名啊',
        '年龄' => 50,
        'email' => '291323003@qq.com',
    ],
];

$renderer = new \EasySwoole\Utility\ArrayToTextTable($data);
////$renderer->setDisplayHeader(false);
//$renderer->setFormatter(function (&$value,$key){
//    if($key == 'sex'){
//        if(empty($value)){
//            $value = '未知性别';
//        }
//    }elseif($key == 'email'){
//        if(empty($value)){
//            $value = '未知邮箱';
//        }
//    }
//});
$table =  $renderer->getTable();
 */

/**
 * Class ArrayToTextTable
 * @package Core\Utility
 */
class ArrayToTextTable
{
    /**
     *
     */
    const AlignLeft   = STR_PAD_RIGHT;
    /**
     *
     */
    const AlignCenter = STR_PAD_BOTH;
    /**
     *
     */
    const AlignRight = STR_PAD_LEFT;

    /**
     * @var array
     */
    protected $data;
    /**
     * @var array
     */
    protected $keys;
    /**
     * @var array
     */
    protected $widths;
    /**
     * @var int|string
     */
    protected $indentation;
    /**
     * @var bool
     */
    protected $displayHeader = true;
    /**
     * @var
     */
    protected $keysAlignment;
    /**
     * @var
     */
    protected $valuesAlignment;
    /**
     * @var
     */
    protected $formatter;

    /**
     * ArrayToTextTable constructor.
     *
     * @param array $data
     */
    public function __construct($data = [])
    {
        $this->setData($data)
            ->setIndentation('')
            ->setKeysAlignment(self::AlignCenter)
            ->setValuesAlignment(self::AlignLeft)
            ->setFormatter(null);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getTable();
    }

    /**
     * @param null $data
     *
     * @return string
     */
    public function getTable($data = null)
    {

        if (!is_null($data)) {
            $this->setData($data);
        }

        $data = $this->prepare();

        $i = $this->indentation;

        $table = $i . $this->line('┌', '─', '┬', '┐') . PHP_EOL;

        if ($this->displayHeader) {
            //绘制table header
            $headerRows = array_combine($this->keys, $this->keys);
            $table      .= $i . $this->row($headerRows, $this->keysAlignment) . PHP_EOL;
            $table      .= $i . $this->line('├', '─', '┼', '┤') . PHP_EOL;
        }

        foreach ($data as $row) {
            $table .= $i . $this->row($row, $this->valuesAlignment) . PHP_EOL;
        }
        $table .= $i . $this->line('└', '─', '┴', '┘') . PHP_EOL;

        return $table;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return mixed
     */
    public function getIndentation()
    {
        return $this->indentation;
    }

    /**
     * @return bool
     */
    public function getDisplayHeader()
    {
        return $this->displayHeader;
    }

    /**
     * @return mixed
     */
    public function getKeysAlignment()
    {
        return $this->keysAlignment;
    }

    /**
     * @return mixed
     */
    public function getValuesAlignment()
    {
        return $this->valuesAlignment;
    }

    /**
     * @return mixed
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * @param $data
     *
     * @return $this
     */
    public function setData($data)
    {
        if (!is_array($data)) {
            $data = [];
        }
        $arrayData = [];
        foreach ($data as $row) {
            if (is_array($row)) {
                $arrayData[] = $row;
            } elseif (is_object($row)) {
                $arrayData[] = get_object_vars($row);
            }
        }
        $this->data = $arrayData;
        return $this;
    }

    /**
     * @param $indentation
     *
     * @return $this
     */
    public function setIndentation($indentation)
    {
        $this->indentation = $indentation;
        return $this;
    }

    /**
     * @param $displayHeader
     *
     * @return $this
     */
    public function setDisplayHeader($displayHeader)
    {
        $this->displayHeader = $displayHeader;
        return $this;
    }

    /**
     * @param $keysAlignment
     *
     * @return $this
     */
    public function setKeysAlignment($keysAlignment)
    {
        $this->keysAlignment = $keysAlignment;
        return $this;
    }

    /**
     * @param $valuesAlignment
     *
     * @return $this
     */
    public function setValuesAlignment($valuesAlignment)
    {
        $this->valuesAlignment = $valuesAlignment;
        return $this;
    }

    /**
     * @param $formatter
     *
     * @return $this
     */
    public function setFormatter($formatter)
    {
        $this->formatter = $formatter;
        return $this;
    }

    /**
     * @param $left
     * @param $horizontal
     * @param $link
     * @param $right
     *
     * @return string
     */
    protected function line($left, $horizontal, $link, $right)
    {
        $line = $left;
        foreach ($this->keys as $key) {
            $line .= str_repeat($horizontal, $this->widths[$key] + 2) . $link;
        }

        if (mb_strlen($line) > mb_strlen($left)) {
            $line = mb_substr($line, 0, -mb_strlen($horizontal));
        }
        return $line . $right;
    }

    /**
     * @param $row
     * @param $alignment
     *
     * @return string
     */
    protected function row($row, $alignment)
    {
        $line = '│';
        foreach ($this->keys as $key) {
            $value = isset($row[$key]) ? $row[$key] : '';
            $line  .= ' ' . static::mb_str_pad($value, $this->widths[$key], ' ', $alignment) . ' ' . '│';
        }
        if (empty($row)) {
            $line .= '│';
        }
        return $line;
    }

    /**
     * @return mixed
     */
    protected function prepare()
    {
        $this->keys   = [];
        $this->widths = [];
        $data         = $this->data;

        //合并全部数组的key
        foreach ($data as $row) {
            $this->keys = array_merge($this->keys, array_keys($row));
        }
        $this->keys = array_unique($this->keys);

        //补充缺陷数组
        foreach ($data as $index => $row) {
            foreach ($this->keys as $key) {
                if (!array_key_exists($key, $row)) {
                    $data[$index][$key] = null;
                }
            }
        }

        //执行formatter
        if ($this->formatter instanceof \Closure) {
            foreach ($data as &$row) {
                array_walk($row, $this->formatter);
            }
            unset($row);
        }

        foreach ($this->keys as $key) {
            $this->setWidth($key, $key);
        }
        foreach ($data as $row) {
            foreach ($row as $columnKey => $columnValue) {
                $this->setWidth($columnKey, $columnValue);
            }
        }
        return $data;
    }

    /**
     * @param $key
     * @param $value
     */
    protected function setWidth($key, $value)
    {
        if (!isset($this->widths[$key])) {
            $this->widths[$key] = 0;
        }
        $width = (strlen($value) + mb_strlen($value, 'UTF8')) / 2;
        if ($width > $this->widths[$key]) {
            $this->widths[$key] = $width;
        }
    }

    /**
     * @param        $input
     * @param        $pad_length
     * @param string $pad_string
     * @param int    $pad_type
     * @param null   $encoding
     *
     * @return string
     */
    protected static function mb_str_pad(
        $input,
        $pad_length,
        $pad_string = ' ',
        $pad_type = STR_PAD_RIGHT,
        $encoding = null
    ) {
        if ($encoding === null) {
            $encoding = mb_internal_encoding();
        }
        $diff = strlen($input) - (strlen($input) + mb_strlen($input, $encoding)) / 2;
        return str_pad($input, $pad_length + $diff, $pad_string, $pad_type);
    }
}