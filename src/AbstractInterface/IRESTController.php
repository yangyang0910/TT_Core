<?php
/**
 * Created by PhpStorm.
 * User: yangcai
 * Date: 2018/8/6
 * Time: 14:19
 */

namespace Core\AbstractInterface;

/**
 * restFul 控制器接口
 * Interface IRESTController
 * @package Core\AbstractInterface
 */
interface IRESTController
{
    /**
     * 获取列表
     */
    function GET_index();

    /**
     * 获取详情
     */
    function GET_info();

    /**
     * 增加
     */
    function POST_index();

    /**
     * 全量更新
     */
    function PUT_index();

    /**
     * 增量更新
     */
    function PATCH_index();

    /**
     * 删除
     */
    function DELETE_index();
}