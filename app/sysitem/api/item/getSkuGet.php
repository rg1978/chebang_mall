<?php
/**
 * 获取指定商品的货品列表
 * item.sku.get
 */
class sysitem_api_item_getSkuGet{

    public $apiDescription = "根据sku_id获取货品数据";

    public function getParams()
    {
        $return['params'] = array(
            'sku_id' => ['type'=>'string','valid'=>'required','description'=>'货品ID','example'=>'2'],
            'item_id' => ['type'=>'int','valid'=>'','description'=>'商品id','example'=>'2'],
        );
        return $return;
    }

    public function get($params)
    {
        if( is_numeric($params['item_id']) )
        {
            $filter['item_id'] = $params['item_id'];
        }

        $filter['sku_id'] = $params['sku_id'];
        $objMdlItem = app::get('sysitem')->model('sku');
        $data = $objMdlItem->getRow('*', $filter);
        if( empty($data) ) return array();

        $objMdlSkuStore = app::get('sysitem')->model('sku_store');
        $storeInfo = $objMdlSkuStore->getRow('store,freez,sku_id',array('sku_id'=>$data['sku_id']));
        $data['store'] = intval($storeInfo['store']);
        $data['freez'] = intval($storeInfo['freez']);

        return $data;
    }
}

