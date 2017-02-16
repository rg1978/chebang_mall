<?php
/**
 * ShopEx licence
 * - aftersales.apply
 * - 创建售后申请接口
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 */
class sysaftersales_api_apply {

    /**
     * 接口作用说明
     */
    public $apiDescription = '创建售后服务';

    /**
     * 设定申请售后服务接口的参数
     */
    public function getParams()
    {
        $return['params'] = array(
            'tid'             => ['type'=>'int',    'valid'=>'required|numeric',  'title'=>'订单号',           'desc'=>'申请售后的订单编号'],
            'oid'             => ['type'=>'int',    'valid'=>'required|numeric',  'title'=>'子订单号',         'desc'=>'申请售后的子订单编号'],
            'reason'          => ['type'=>'string', 'valid'=>'required',          'title'=>'申请售后原因',     'desc'=>'申请售后原因'],
            'description'     => ['type'=>'string', 'valid'=>'max:300',           'title'=>'申请售后详细说明', 'desc'=>'申请售后详细说明|描述不能大于300'],
            'evidence_pic'    => ['type'=>'string', 'valid'=>'',                  'title'=>'照片凭证',         'desc'=>'照片凭证,imageId逗号隔开,最多五张照片'],
            'aftersales_type' => ['type'=>'int',    'valid'=>'',                  'title'=>'售后服务类型',     'desc'=>'售后服务类型(ONLY_REFUND:只退款，REFUND_GOODS:退货退款，EXCHANGING_GOODS:换货) 默认为只退款'],
        );

        return $return;
    }

    /**
     * 申请售后服务具体实现接口
     *
     * @desc 创建售后申请接口
     * @return bool true 返回执行成功状态
     */
    public function create($params)
    {
        if($params['oauth']['auth_type'] == "member")
        {
            $params['user_id'] = $params['oauth']['account_id'];
            unset($params['oauth']);
        }
        else
        {
            throw new \LogicException('登录信息有误');
        }

        if(!$params['user_id'])
        {
            throw new \LogicException('登录信息user_id有误');
        }

        $db = app::get('sysaftersales')->database();
        $db->beginTransaction();
        try
        {
            $result = kernel::single('sysaftersales_apply')->create($params);
            if(!$result)
            {
                $db->rollback();
                return false;
            }
            $db->commit();
        }
        catch(\Exception $e)
        {
            $db->rollback();
            throw $e;
        }
        return true;
    }
}
