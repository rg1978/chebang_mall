<?php
/**
 * 生成优惠规则
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class syspromotion_promotiondesc {
    
    //封顶
    const CANJOIN_REPEAT = 1;
    //免邮类型
    const CONDITON_TYPE_MONEY = 'money';
    const CONDITON_TYPE_QUANTITY = 'quantity';
    
    /**
     * 生成优惠规则
     * @param array $promotionInfo
     * @param string $type
     * @return string
     * */
    
    public function promotionRule($promotionInfo, $type)
    {
        $ruleStr = '';
        //处理优惠规则
        if($promotionInfo['condition_value'])
        {
            $conditionValue = $this->__getConditionValue($promotionInfo['condition_value']);
        }
        switch ($type)
        {
            //处理满减优惠
            case 'fullminus':
                foreach ($conditionValue as $role)
                {
                    $ruleStr .= sprintf('满%d元减%d元，', $role[0], $role[1]);
                }
                
                if($promotionInfo['canjoin_repeat'] == self::CANJOIN_REPEAT)
                {
                    $ruleStr .= '上不封顶。';
                }
            break;
            
            //处理满折优惠
            case 'fulldiscount':
                foreach ($conditionValue as $role)
                {
                    //处理折扣
                    $role[1] = $role[1] / 10;
                    $ruleStr .= '满'. $role[0] .'元给予'. $role[1] .'折优惠，';
                }
            break;
            
            //处理XY折优惠
            case 'xydiscount':
                foreach ($conditionValue as $role)
                {
                    $role[1] = $role[1] / 10;
                    $ruleStr .= '满'. $role[0] .'件给予'. $role[1] .'折优惠，';
                }
            break;
            
            //处理免邮优惠
            case 'freepostage':
                $condType = $promotionInfo['condition_type'];
                
                if($condType == self::CONDITON_TYPE_MONEY)
                {
                    $ruleStr .= sprintf('消费满%.2f元免邮，', $promotionInfo['limit_money']);
                }
                
                if($condType == self::CONDITON_TYPE_QUANTITY)
                {
                    $ruleStr .= sprintf('消费满%.0f件免邮，', $promotionInfo['limit_quantity']);
                }
            break;
            
        }
        
        //处理会员
        $gradeArr = explode(',',$promotionInfo['valid_grade']);
        $gradeStr = $this->__getGradeStr($gradeArr);
        if($type == 'freepostage')
        {
            $ruleStr = sprintf('%s%s可参加。', $ruleStr, $gradeStr);
        }else
        {
            $ruleStr = sprintf('%s%s可参加，可参加次数为%d次。', $ruleStr, $gradeStr, $promotionInfo['join_limit']);
        }        
        
        
        return $ruleStr;
    }
    
    /**
     * 处理会员等级
     *  @param array $gradeArr
     *  @return string
     * */
    private function __getGradeStr($gradeArr)
    {
        //生成会员优惠规则
        $gradeStr = '';
        //获取会员列表
        $gradeList = app::get('syspromotion')->rpcCall('user.grade.list');
        $gradeIds = array_column($gradeList, 'grade_id');
        //查看是否所有的会有都可以参加
        if(!array_diff($gradeIds, $gradeArr))
        {
            $gradeStr = '所有会员都';
        }else
        {
            foreach ($gradeList as $mem)
            {
                if(in_array($mem['grade_id'], $gradeArr))
                {
                    $gradeStr .= $mem['grade_name'].'，';
                }
            }
            $gradeStr = rtrim($gradeStr, '，');
        }
        
        return $gradeStr;
    }
    
    /**
     * 处理优惠数据
     * @param string $data
     * @return array
     * */
    private function __getConditionValue($data)
    {
        $conditionValue = explode(",",$data);
        foreach ($conditionValue as $key => $value)
        {
            $fmt[$key] = explode("|",$value);
        }
        return $fmt;
    
    }
}