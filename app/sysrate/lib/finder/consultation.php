<?php
class sysrate_finder_consultation{

    public $detail_basic = '基本信息';

    public function __construct()
    {
        // 咨询类型对应表
	$this->consultationType =array(
	    'item' => '商品咨询',
	    'store_delivery' => '库存/配送咨询',
	    'payment' =>' 支付方式咨询',
	    'invoice' => '发票保修咨询',
	    );
    
    }
    public function detail_basic($Id)
    {
        $objMdlConsultation = app::get('sysrate')->model('consultation');
        $row = "consultation_id,be_reply_id,item_title,shop_name,author,consultation_type,contack,content,created_time,is_display,shop_id,item_id,author_id,ip";
        $consultation = $objMdlConsultation->getRow($row,array('consultation_id'=>$Id));
        $consultation['consultation_type'] = $this->consultationType[$consultation['consultation_type']];
        $itemId = $consultation['item_id'];
        $shopId = $consultation['shop_id'];
        // 获取店铺子域名
        $subdomain = app::get('sysrate')->rpcCall('shop.subdomain.get',array('shop_id'=>$shopId))['subdomain'];

        $item = app::get('sysrate')->rpcCall('item.get',array('item_id'=>$itemId,'fields'=>'title,image_default_id'));
        if($item)
        {
            $consultation['item_title'] = $item['title'];
            $consultation['image_default_id'] = $item['image_default_id'];
            $url = url::action('topc_ctl_item@index',array('item_id'=>$itemId));
            $consultation['item_url'] = $url;
            $url = url::action('topc_ctl_shopcenter@index',array('shop_id'=>$shopId,'subdomain'=>$subdomain));
            $consultation['shop_url'] = $url;
        }
        $pagedata['comment'] = $consultation;
        $row = "consultation_id,be_reply_id,author,content,created_time,is_display";
        $reply = $objMdlConsultation->getList($row,array('be_reply_id'=>$Id));
        $pagedata['reply'] = $reply;
        return view::make('sysrate/consultation/detail.html',$pagedata)->render();
    }


}
