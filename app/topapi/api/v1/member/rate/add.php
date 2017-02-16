<?php
/**
 * ShopEx licence
 *
 ** -- member.rate.add
 * -- 对已完成的订单新增商品评论和店铺评分
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 */
class topapi_api_v1_member_rate_add {

    /**
     * 接口作用说明
     */
    public $apiDescription = '对已完成的订单新增商品评论和店铺评分';

    public function setParams()
    {
        return array(
            'tid' => ['type'=>'string', 'valid'=>'required', 'example'=>'1608151930150004', 'desc'=>'新增评论的订单ID'],

            'rate_data' => ['type'=>'json', 'valid'=>'required', 'example'=>'', 'desc'=>'对子订单评论的参数', 'params' => [
                //单个子订单评论需要的参数
                'oid'      => ['type'=>'string',  'valid'=>'required', 'example'=>'', 'desc'=>'新增评论的子订单号'],
                'result'   => ['type'=>'string',  'valid'=>'required|in:good,neutral,bad', 'example'=>'', 'desc'=>'评价结果,good 好评 neutral 中评 bad 差评'],
                'content'  => ['type'=>'string',  'valid'=>'max:300', 'example'=>'', 'desc'=>'评价内容'],
                'rate_pic' => ['type'=>'string',  'valid'=>'', 'example'=>'', 'desc'=>'晒单图片，多个图片用逗号隔开'],
            ]],
           'anony'    => ['type'=>'boolean','valid'=>'required', 'example'=>'true', 'desc'=>'是否匿名'],

            //店铺动态评分参数
            'tally_score'               => ['type'=>'int','valid'=>'required', 'example'=>'5', 'desc'=>'商品与描述相符'],
            'attitude_score'            => ['type'=>'int','valid'=>'required', 'example'=>'5', 'desc'=>'服务态度评分'],
            'delivery_speed_score'      => ['type'=>'int','valid'=>'required', 'example'=>'5', 'desc'=>'发货速度评分'],
        );

        return $return;
    }

    public function handle($params)
    {
        foreach( $params['rate_data'] as &$row )
        {
            $row['anony'] = ($params['anony'] == 'true') ? 1 : 0;
        }
        unset($params['anony']);

        $result = app::get('topapi')->rpcCall('rate.add', $params);

        return $result;
    }
}


