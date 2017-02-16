<?php
/**
 * ShopEx licence
 * - user.hongbao.use
 * - 用户使用红包接口
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 */
final class sysuser_api_user_hongbao_useHongbao {

    /**
     * 接口作用说明
     */
    public $apiDescription = '用户使用红包接口';

    /**
     * 接口参数
     */
    public function getParams()
    {
        $return['params'] = array(
            'user_id'         => ['type'=>'int',    'valid'=>'required',  'desc'=>'用户ID'],
            'pay_password'    => ['type'=>'string', 'valid'=>'required',  'desc'=>'会员支付密码'],
            'user_hongbao_id' => ['type'=>'string', 'valid'=>'required',  'desc'=>'用户红包列表ID,如果为叠加使用则用逗号(,)隔开'],
            'tid'             => ['type'=>'string', 'valid'=>'required',  'desc'=>'红包使用的订单号'],
            'used_platform'   => ['type'=>'string', 'valid'=>'required',  'desc'=>'使用平台(pc或者wap)'],
        );
        return $return;
    }

    /**
     * 用户使用红包接口
     *
     * @desc 用户使用红包接口
     * @return money result 此次红包总金额
     */
    public function useHongbao($params)
    {
        //确认支付密码是否正确
        $password = $params['pay_password'];
        $flag = kernel::single('sysuser_data_deposit_password')->checkPassword($params['user_id'], $password);
        if( !$flag )
        {
            throw new LogicException(app::get('sysuser')->_('支付密码错误！'));

        }

        $userHongbaoIds = explode(',', $params['user_hongbao_id']);
        $objMdlUserHongbao = app::get('sysuser')->model('user_hongbao');
        $filter = [
            'id'       => $userHongbaoIds,
            'user_id'  => $params['user_id'],
            'is_valid'  => 'active',
            'used_platform' => [$params['used_platform'],'all'],
            'start_time|sthan' => time(),
            'end_time|bthan' => time(),
        ];

        $data = $objMdlUserHongbao->getList('*',$filter);
        if( !$data )
        {
            throw new \LogicException('红包不能使用，请重新选择');
        }
        if( count($data) <  count($userHongbaoIds) )
        {
            throw new \LogicException('部分红包不能使用，请重新选择');
        }

        $useTotalMoney = [];
        $totalHongbao = 0;
        foreach( $data as $row )
        {
            $hongbaoId = $row['hongbao_id'];
            if( $row['hongbao_obtain_type'] == 'aftersales' || $row['hongbao_obtain_type'] == 'cancelTrade' )
            {
                $useTotalMoney['refund'][$hongbaoId] = ecmath::number_plus(array($useTotalMoney['refund'][$hongbaoId], $row['money']));
            }
            else
            {
                $useTotalMoney['get'][$hongbaoId] = ecmath::number_plus(array($useTotalMoney['get'][$hongbaoId], $row['money']));
            }

            $totalHongbao = ecmath::number_plus(array($totalHongbao, $row['money']));
        }

        $tids['user_id'] = $params['user_id'];
        $tids['tid'] = $params['tid'];
        $tids['fields'] = "payment,tid,hongbao_fee";
        $trades = app::get('sysuser')->rpcCall('trade.get.list',$tids);
        $payMoney = 0;//要支付的金额
        $hongbaoFee = 0;//已支付的红包金额
        $paymentTotal = 0;//订单总金额
        $userHongbaoId = $params['user_hongbao_id'];//订单中使用的红包
        foreach( $trades['list'] as $row )
        {
            $paymentTotal = ecmath::number_plus(array($paymentTotal,$row['payment']));
            $payMoney = ecmath::number_plus(array($payMoney,ecmath::number_minus(array($row['payment'],$row['hongbao_fee']))));
            $hongbaoFee = ecmath::number_plus(array($hongbaoFee,$row['hongbao_fee']));
            if( $row['user_hongbao_id'] )
            {
                $userHongbaoId .= ','.$row['user_hongbao_id'];
            }
        }

        if( $payMoney < $totalHongbao )
        {
            throw new \LogicException('红包金额超过订单金额，请重新选择');
        }

        $return['total'] = $totalHongbao;
        $tradeUseHongbaoFee = ecmath::number_plus(array($hongbaoFee,$totalHongbao));//订单红包支付总金额

        $db = app::get('sysuser')->database();
        $transaction = $db->beginTransaction();
        try{
            $percent = bcdiv(strval($tradeUseHongbaoFee), strval($paymentTotal), 6);

            $tmpTradeHongbao = 0;
            $i = 1;
            foreach( $trades['list'] as $value )
            {
                $tradeHongbaoFee = 0;
                if( $trades['count'] == 1 )
                {
                    $tradeHongbaoFee = $tradeUseHongbaoFee;
                }
                else
                {
                    if( $tradeUseHongbaoFee ==  $paymentTotal)
                    {
                        $tradeHongbaoFee = $value['payment'];
                    }
                    else
                    {
                        if( $i == $trades['count'] )
                        {
                            $tradeHongbaoFee = ecmath::number_minus(array($tradeUseHongbaoFee, $tmpTradeHongbao));
                            $tradeHongbaoFee = ($tradeHongbaoFee <= $value['payment']) ? $tradeHongbaoFee : $value['payment'];
                        }
                        else
                        {
                            $tradeHongbaoFee = ecmath::number_multiple(array($value['payment'], $percent) );
                            $tmpTradeHongbao = ecmath::number_plus(array($tmpTradeHongbao, $tradeHongbaoFee));
                            $i++;
                        }
                    }
                }

                //更新订单红包支付金额
                app::get('sysuser')->rpcCall('trade.update.hongbao.money',['tid'=>$value['tid'],'user_id'=>$params['user_id'],'money'=>$tradeHongbaoFee,'user_hongbao_id'=>$userHongbaoId]);
                $return['trade'][$value['tid']]['payment'] = $tradeHongbaoFee;
            }

            $objMdlUserHongbao->update(['is_valid'=>'used','tid'=>$params['tid']], ['id'=>$userHongbaoIds]);

            $result = app::get('sysuser')->rpcCall('promotion.hongbao.use',['user_id'=>$params['user_id'],'use_hongbao_list'=>json_encode($useTotalMoney)]);
            if( !$result )
            {
                $db->rollback();
            }
            else
            {
                $db->commit($transaction);
            }
        }
        catch( Exception $e)
        {
            $db->rollback();
            throw $e;
        }

        return $return;
    }
}

