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
    const CUT_FLOW_WITH_UID = 'CUT_FLOW_WITH_UID';
    const CUT_FLOW_WITH_IP  = 'CUT_FLOW_WITH_IP';

    private $_rule = self::CUT_FLOW_WITH_UID;

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