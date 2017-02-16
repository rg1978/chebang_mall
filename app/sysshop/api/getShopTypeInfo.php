<?php
class sysshop_api_getShopTypeInfo{
    public $apiDescription = "获取店铺类型信息";
    public function getParams()
    {
        $return['params'] = array(
            'shop_id' => ['type'=>'int','valid'=>'required','description'=>'店铺id','default'=>'当前登录的商家id','example'=>'1'],
            'fields' => ['type'=>'field_list','valid'=>'','description'=>'店铺类目字段','default'=>'','example'=>'shop_id,cat_name'],
        );
        return $return;
    }
    public function getShopTypeInfo($params)
    {

        $objMdlShoptype = app::get('sysshop')->model('shop_type');
        $objMdlShop = app::get('sysshop')->model('shop');
        $shopType = $objMdlShop->getRow('shop_type',array('shop_id'=>$params['shop_id']));
        if(!$params['fields'])
        {
            $params['fields'] = '*';
        }
        $filter = array('shop_type'=>$shopType['shop_type']);
        
        $shopTypeInfo = $objMdlShoptype->getRow($params['fields'],$filter);
        //echo '<pre>';print_r($shopTypeInfo);exit();
        return $shopTypeInfo;
    }
}
