<?php

class sysshop_api_shop_getStorePolice {

    public $apiDescription = "根据店铺ID获取店铺设置的库存报警值";

    public function getParams()
    {
        $return['params'] = array(
            'shop_id' => ['type'=>'int','valid'=>'required','description'=>'店铺ID','default'=>'','example'=>''],
            'fields' => ['type'=>'field_list','valid'=>'','description'=>'店铺数据字段','default'=>'','example'=>''],
        );
        return $return;
    }

    public function getStorePolice($params)
    {
        $storePolice = app::get('sysshop')->model('store_police');
        if(!$params['fields'])
        {
            $params['fields'] = '*';
        }
        $storePolice = $storePolice->getRow($params['fields'],array('shop_id'=>$params['shop_id']));
        return  $storePolice;
    }
}

