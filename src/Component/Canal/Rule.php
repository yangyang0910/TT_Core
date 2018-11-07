<?php
/**
 * Created by PhpStorm.
 * User: safer
 * Date: 2018/11/6
 * Time: 0:43:16
 */

namespace Core\Component\Canal;

/**
 * 规则
 * Class Rule
 * @package Core\Component\Canal
 */
class Rule
{
    const CUT_FLOW_BY_UID = 'CUT_FLOW_BY_UID'; // 通过 uid 切流
    const CUT_FLOW_BY_IP  = 'CUT_FLOW_BY_IP'; // 通过 ip 切流
    const ASSIGNATION_UID = 'ASSIGNATION_UID'; // 分配指定 uid
    const ASSIGNATION_IP  = 'ASSIGNATION_IP'; // 分配指定 ip

    private $_rule = self::CUT_FLOW_BY_UID;

    /**
     * @return string
     */
    public function getRule()
    {
        return $this->_rule;
    }

    /**
     * @param string $rule
     */
    public function setRule($rule)
    {
        $this->_rule = $rule;
    }
}