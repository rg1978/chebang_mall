<?php
/**
 * topapi
 *
 * -- member.complaints.get
 * -- 会员中心我的投诉详情
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_complaints_get implements topapi_interface_api {

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '会员中心我的投诉详情';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        //接口传入的参数
        $return = array(
            'oid'           => ['type'=>'int', 'valid'=>'required_without:complaints_id', 'default'=>'', 'example'=>'1507021608160001','desc'=>'子订单ID', 'msg'=>'子订单ID或投诉ID必填一项'],
            'complaints_id' => ['type'=>'int', 'valid'=>'required_without:oid', 'default'=>'', 'example'=>'15','desc'=>'投诉id', 'msg'=>'子订单ID或投诉ID必填一项'],
        );

        return $return;
    }

    /**
     * @return int complaints_id 投诉ID
     * @return int shop_id 投诉的店铺ID
     * @return string image_url 投诉凭证图片
     * @return string status 投诉状态
     * @return string meomo 平台处理备注
     * @return timestamp created_time 投诉创建时间
     * @return string order.item_id 商品id
     * @return string order.title 投诉商品标题
     * @return string order.pic_path 投诉商品图片
     * @return string status_desc 投诉状态说明
     * @return string shop_name 投诉店铺名称
     */
    public function handle($params)
    {
        $params['fields'] = 'complaints_id,image_url,shop_id,user_id,tid,status,tel,complaints_type,content,memo,buyer_close_reasons,created_time,oid';
        $complaints = app::get('topapi')->rpcCall('trade.order.complaints.info', $params);
        if( $complaints )
        {
            $status = $complaints['status'];
            $complaints['status_desc'] = $this->complaints_type[$status];

            if($complaints['oid'])
            {
                $orders = app::get('topapi')->rpcCall('trade.order.list.get', ['oids'=>$complaints['oid'],'fields'=>'item_id,title,pic_path']);
                $complaints['order'] = $orders[0];
            }

            if( $complaints['image_url'] )
            {
                $image = explode(',',$complaints['image_url']);
                unset($complaints['image_url']);
                foreach( $image as $k=>$url )
                {
                    $complaints['image_url'][$k]['url'] = base_storager::modifier($url);
                    $complaints['image_url'][$k]['t_url'] = base_storager::modifier($url,'t');
                }
            }

            if( $complaints['shop_id'] )
            {
                $shopData = app::get('topapi')->rpcCall('shop.get.list', ['shop_id'=>$complaints['shop_id'],'fields'=>'shop_id,shop_name'])[0];
                $complaints['shop_name'] = $shopData['shopname'];
            }
        }
        return $complaints;
    }

    private $complaints_type = array(
                'WAIT_SYS_AGREE' => '等待处理',
                'FINISHED' => '已完成',
                'BUYER_CLOSED' => '买家撤销投诉',
                'CLOSED' => '平台关闭投诉',
            );

    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"complaints_id":1,"shop_id":3,"user_id":2,"tid":1601281135410002,"status":"WAIT_SYS_AGREE","tel":"13456789876","complaints_type":"商品问题","content":"订单号为1601281135410002，购买的衣服不到一天就破了，而且买回来就是脏的，商家以特价商品为由拒绝退货","memo":"","buyer_close_reasons":"","created_time":1472216431,"oid":1601281135420002,"status_desc":"等待处理","order":{"item_id":128,"title":"艾格 ETAM 彩色数码印花无袖连衣裙","pic_path":"http://images.bbc.shopex123.com/images/44/4b/d5/332d15a9ddfd0434a19cbddb374101527100b2af.png"},"image_url":[{"url":"http://192.168.65.145/bbc/public/images/c9/af/fd/6b2897a61dcff091e0021508d96b15702d975345.jpg","t_url":"http://192.168.65.145/bbc/public/images/c9/af/fd/6b2897a61dcff091e0021508d96b15702d975345.jpg_t.jpg"},{"url":"http://192.168.65.145/bbc/public/images/7a/c6/72/e4f524b6af954f25429067a1770603eddc2a5cbe.jpg","t_url":"http://192.168.65.145/bbc/public/images/7a/c6/72/e4f524b6af954f25429067a1770603eddc2a5cbe.jpg_t.jpg"},{"url":"http://192.168.65.145/bbc/public/images/e6/25/cf/d4bc8026654beb75c6cc057e7ce7a2e610d40f97.jpg","t_url":"http://192.168.65.145/bbc/public/images/e6/25/cf/d4bc8026654beb75c6cc057e7ce7a2e610d40f97.jpg_t.jpg"}]}}';
    }
}
