<?php
/**
 * Created by PhpStorm.
 * User: yangcai
 * Date: 2018/8/2
 * Time: 9:34
 */

namespace Core\Utility\Logic;

/**
 * logic 接口
 * Interface ILogic
 * @package Core\Utility\Logic\ALogic
 */
interface ILogic
{
    /**
     * 获取列表
     * @return \Core\Utility\Logic\Response
     */
    function getList();

    /**
     * 获取详情
     * @return \Core\Utility\Logic\Response
     */
    function getInfo();

    /**
     * 增加
     * @return \Core\Utility\Logic\Response
     */
    function create();

    /**
     * 更新
     * @return \Core\Utility\Logic\Response
     */
    function update();

    /**
     * 删除
     * @return \Core\Utility\Logic\Response
     */
    function delete();
}