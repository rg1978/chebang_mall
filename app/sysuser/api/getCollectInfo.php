<?php
class sysuser_api_getCollectInfo {

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取会员商品收藏和店铺收藏';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        //接口传入的参数
        $return['params'] = array(
            'user_id' => ['type'=>'int','valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'用户ID必填','default'=>'','example'=>''],
        );

        return $return;
    }

    public function getCollectInfo($params)
    {
        $objMdlItemFav = app::get('sysuser')->model('user_fav');
        $objMdlShopFav = app::get('sysuser')->model('shop_fav');
        $filter = array('user_id'=>$params['user_id']);
        $item = $objMdlItemFav->getList('item_id',$filter);
        $shop = $objMdlShopFav->getList('shop_id',$filter);
        $aData['item'] = array_column($item,'item_id');
        $aData['shop'] = array_column($shop,'shop_id');
        return $aData;
    }


}
