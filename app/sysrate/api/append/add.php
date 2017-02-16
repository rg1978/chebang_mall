<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 */
class sysrate_api_append_add {

    /**
     * 接口作用说明
     */
    public $apiDescription = '卖家对评价进行追评';

    public function getParams()
    {
        $return['params'] = array(
            'user_id' => ['type'=>'int','valid'=>'required', 'description'=>'追评用户ID'],
            'rate_id' => ['type'=>'int','valid'=>'required', 'description'=>'评论ID'],
            'content' => ['type'=>'string','valid'=>'required|min:5|max:300', 'description'=>'追评内容'],
            'pic' => ['type'=>'string','valid'=>'', 'description'=>'追评图片，通过图片上传API返回的图片ID,多个用逗号隔开'],
        );

        return $return;
    }

    public function add($params)
    {
        $objMdlTraderate = app::get('sysrate')->model('traderate');
        $data = $objMdlTraderate->getRow('rate_id,is_append,user_id,shop_id,tid,trade_end_time,created_time', ['user_id'=>$params['user_id'],'rate_id'=>$params['rate_id'],'is_append'=>'0']);
        if( empty($data) )
        {
            throw new Exception(app::get('sysrate')->_('追评的评价不存在或已追评'));
        }

        $day = (int)app::get('sysconf')->getConf('rate.append.time') ?: 30;
        if( round((time() - $data['created_time'])/86400) >= $day )
        {
            throw new Exception(app::get('sysrate')->_('超出追评时间限制，不可以在追评'));
        }

        //新增追评功能，以前的评价没有存储订单结束时间，这里兼容
        if( !$data['trade_end_time'] )
        {
            $params['tid'] = $data['tid'];
            $params['fields'] = 'tid,end_time';
            $tradeData = app::get('sysrate')->rpcCall('trade.get', $params);
        }

        $insertData['rate_id'] = $params['rate_id'];
        $insertData['append_content'] = $params['content'];
        $insertData['append_rate_pic'] = $params['pic'];
        $insertData['shop_id'] = $data['shop_id'];
        $insertData['trade_end_time'] = $data['trade_end_time'] ?: $tradeData['end_time'] ;
        $insertData['created_time'] = time();
        $insertData['modified_time'] = time();

        $db = app::get('systrade')->database();
        $db->beginTransaction();
        try
        {
            $appendRateId = app::get('sysrate')->model('append')->insert($insertData);
            $update = $objMdlTraderate->update(['is_append'=>'1'], ['rate_id'=>$params['rate_id']]);
            if(!$appendRateId || !$update )
            {
                throw new Exception('保存失败');
            }
            $db->commit();
        }
        catch(Exception $e)
        {
            $db->rollback();
            throw new Exception($e->getMessage());
        }

        return ['append_rate_id'=>$appendRateId];
    }
}

