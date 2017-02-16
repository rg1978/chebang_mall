<?php
/**
 * ShopEx licence
 * - user.hongbao.refund
 * - 退还红包接口
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 */
final class sysuser_api_user_hongbao_refundHongbao {

    /**
     * 接口作用说明
     */
    public $apiDescription = '退还红包接口';

    /**
     * 接口参数
     */
    public function getParams()
    {
        $return['params'] = array(
            'user_id'            => ['type'=>'string', 'valid'=>'required',  'title'=>'用户ID',       'desc'=>'用户ID'],
            'money'              => ['type'=>'string', 'valid'=>'required',  'title'=>'获取指定红包', 'desc'=>'获取指定红包'],
            'hongbao_obtain_type'=> ['type'=>'string', 'valid'=>'required|in:aftersales,cancelTrade',  'title'=>'获取红包方式', 'desc'=>'获取红包方式,aftersales售后退红包，cancelTrade取消订单退红包'],
            'tid'                => ['type'=>'string', 'valid'=>'required',  'title'=>'订单ID',       'desc'=>'如果是退还红包，则需要给出退还红包的订单id'],
            'user_hongbao_id'    => ['type'=>'string', 'valid'=>'required',  'title'=>'退还红包的用户红包ID', 'desc'=>'退还红包的用户红包ID'],
        );
        return $return;
    }

    /**
     * 用户领取红包接口
     *
     * @desc 用户领取红包接口
     * @return bool true
     */
    public function get($params)
    {
        $userHongbaoId = explode(',',$params['user_hongbao_id']);
        if( count($userHongbaoId) == 1 )
        {
            return $this->__refundHongbao($params);
        }
        else
        {
            return $this->__batchRefundHongbao($userHongbaoId, $params);
        }
    }

    /**
     * 退还单个红包
     */
    private function __refundHongbao($params)
    {
        $objMdlUserHongbao = app::get('sysuser')->model('user_hongbao');
        $hongbaoData = $objMdlUserHongbao->getRow('money,end_time,hongbao_id', ['id'=>$params['user_hongbao_id']]);

        $apiParams['user_id'] = $params['user_id'];
        $apiParams['hongbao_id'] = $hongbaoData['hongbao_id'];
        $apiParams['money'] = $params['money'];
        $apiParams['hongbao_obtain_type'] = $params['hongbao_obtain_type'];
        $data = app::get('sysuser')->rpcCall('promotion.hongbao.issued',$apiParams);
        if( $data )
        {
            $objMdlUserHongbao = app::get('sysuser')->model('user_hongbao');
            $userHongbao['name'] = $data['name'];
            $userHongbao['user_id'] = $params['user_id'];
            $userHongbao['hongbao_id'] = $data['hongbao_id'];
            if( $params['hongbao_obtain_type'] == 'cancelTrade' )
            {
                $userHongbao['obtain_desc'] = '取消订单退还红包';
            }
            elseif( $params['hongbao_obtain_type'] == 'aftersales' )
            {
                $userHongbao['obtain_desc'] = '售后退还红包';
            }
            $userHongbao['hongbao_obtain_type'] = $params['hongbao_obtain_type'];
            $userHongbao['obtain_time'] = time();
            $userHongbao['used_platform'] = $data['used_platform'];
            $userHongbao['hongbao_type'] = $data['hongbao_type'];
            $userHongbao['money'] = $data['money'];
            $userHongbao['start_time'] = $data['use_start_time'];
            $userHongbao['end_time'] =  ($hongbaoData['end_time'] > time() + 2592000 || $params['hongbao_obtain_type'] == 'cancelTrade' ) ? $hongbaoData['end_time'] : time() + 2592000; //$hongbaoData['end_time'];//退还红包自动延长30天
            $userHongbao['refund_hongbao_tid'] = $params['tid'];
            $userHongbao['name'] = $data['name'];

            return $objMdlUserHongbao->insert($userHongbao);
        }
        else
        {
            throw new \LogicException(app::get('退还红包失败'));
        }
    }

    /**
     * 批量退还红包
     */
    private function __batchRefundHongbao($userHongbaoId, $params)
    {
        $objMdlUserHongbao = app::get('sysuser')->model('user_hongbao');
        $hongbaoData = $objMdlUserHongbao->getList('money,end_time,hongbao_id', ['id'=>$userHongbaoId]);
        //需要退还的红包金额
        $refundHongbaoMoney = $params['money'];
        foreach( $hongbaoData as $key => $row )
        {
            if( $refundHongbaoMoney && $row['money'] >= $refundHongbaoMoney )
            {
                $refundHongbao[$key]['hongbao_id'] = $row['hongbao_id'];
                $refundHongbao[$key]['money'] = $refundHongbaoMoney;
                break;
            }
            else
            {
                $refundHongbao[$key]['hongbao_id'] = $row['hongbao_id'];
                $refundHongbao[$key]['money'] = $row['money'];
                $refundHongbaoMoney = ecmath::number_minus(array($refundHongbaoMoney, $row['money']));
                if( !$refundHongbaoMoney ) break;
            }

            $hongbaoEndTime[$row['hongbao_id']] = $row['end_time'];
        }

        $apiParams['user_id'] = $params['user_id'];
        $apiParams['hongbao_list'] = json_encode($refundHongbao);
        $apiParams['hongbao_obtain_type'] = $params['hongbao_obtain_type'];

        $data = app::get('sysuser')->rpcCall('promotion.hongbao.batch.issued',$apiParams);
        $objMdlUserHongbao = app::get('sysuser')->model('user_hongbao');
        if( $data )
        {
            foreach( $data as $hongbaoRow )
            {
                $userHongbao = array();
                $userHongbao['user_id'] = $params['user_id'];
                $userHongbao['hongbao_id'] = $hongbaoRow['hongbao_id'];
                $userHongbao['hongbao_obtain_type'] = $params['hongbao_obtain_type'];
                $userHongbao['obtain_time'] = time();
                $userHongbao['used_platform'] = $hongbaoRow['used_platform'];
                $userHongbao['hongbao_type'] = $hongbaoRow['hongbao_type'];
                $userHongbao['money'] = $hongbaoRow['money'];
                $userHongbao['start_time'] = $hongbaoRow['use_start_time'];
                $userHongbao['end_time'] =  ($hongbaoEndTime[$hongbaoRow['hongbao_id']]['end_time'] > time() + 2592000 || $params['cancelTrade'] == 'aftersales' ) ? $hongbaoEndTime[$hongbaoRow['hongbao_id']]['end_time'] : time() + 2592000;
                $userHongbao['refund_hongbao_tid'] = $params['tid'];
                $userHongbao['name'] = $hongbaoRow['name'];

                $objMdlUserHongbao->insert($userHongbao);
            }

            return true;
        }
        else
        {
            throw new \LogicException(app::get('退还红包失败'));
        }
    }
}

