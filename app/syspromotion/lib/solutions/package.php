<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * @author  lujunyi@shopex.cn
 */
class syspromotion_solutions_package implements syspromotion_interface_promotions{

    public function apply($params)
    {
        $packageInfo = app::get('syspromotion')->model('package')->getRow('*', array('package_id'=>$params['package_id']));
        if(!$packageInfo || $packageInfo['package_status'] != 'agree')
        {
            return 0;
            // throw new \LogicException('不能使用此促销!');
        }
        $now = time();
        if( $now<=$packageInfo['start_time'] )
        {
            return 0;
            // throw new \LogicException('尚未开始!');
        }
        if( $now>=$packageInfo['end_time'] )
        {
            return 0;
            // throw new \LogicException('已经结束!');
        }
        $applyNum = app::get('syspromotion')->rpcCall('trade.promotion.applynum', array('promotion_id'=>$params['promotion_id']));
 
        if( $applyNum>=$packageInfo['join_limit'] )
        {
            return 0;
            // throw new \LogicException('可参与的组合促销次数已经用完!');
        }
        $valid_grade = explode(',', $packageInfo['valid_grade']);
        $gradeInfo = app::get('syspromotion')->rpcCall('user.grade.basicinfo');
        if( !in_array($gradeInfo['grade_id'], $valid_grade) )
        {
            return 0;
            // throw new \LogicException('您的当前会员等级不可参加此促销!');
        }
        // 组合促销金额的规则检验
        $rule = explode(',', $packageInfo['condition_value']);
        $ruleArray = array();
        foreach($rule as $k => $v)
        {
            $tmpFullMinusValue = explode('|', $v);
            $ruleArray['full'][$k] = $tmpFullMinusValue['0'];
            $ruleArray['minus'][$k] = $tmpFullMinusValue['1'];
        }
        $ruleLength = count($ruleArray['full']);

        if( $params['forPromotionTotalPrice'] >=$ruleArray['full'][$ruleLength-1] )
        {
            if($packageInfo['canjoin_repeat']=='1')
            {
                $ecmath = kernel::single('ectools_math');
                $beishu = floor( $ecmath->number_div( array($params['forPromotionTotalPrice'], $ruleArray['full'][$ruleLength-1]) ) );
                $discount_price = $ecmath->number_multiple( array($ruleArray['minus'][$ruleLength-1], $beishu) );
            }
            else
            {
                $discount_price = $ruleArray['minus'][$ruleLength-1];
            }
        }
        elseif( $params['forPromotionTotalPrice'] < $ruleArray['full']['0'] )
        {
            $discount_price = 0;
        }
        else
        {
            for($i=0; $i<$ruleLength-1; $i++)
            {
                if( $params['forPromotionTotalPrice']>=$ruleArray['full'][$i] && $params['forPromotionTotalPrice']<$ruleArray['full'][$i+1] )
                {
                    $discount_price = $ruleArray['minus'][$i];
                    break;
                }
            }
        }
        if($discount_price<0)
        {
            $discount_price = 0;
        }
        return $discount_price;
    }

}
