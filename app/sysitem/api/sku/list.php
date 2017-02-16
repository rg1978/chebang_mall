<?php
/**
 * 获取单个商品的详细信息
 * sku.list
 */
class sysitem_api_sku_list {

    /**
     * 接口作用说明
     */
    public $apiDescription = 'sku列表查询';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        $return['params'] = array(
            'sku_ids' => ['type'=>'string','valid'=>'required','description'=>'sku_id列表, id之间用","分割','example'=>'1,2,3,4,5','default'=>''],
            'fields' => ['type'=>'field_list','valid'=>'required','description'=>'要获取的商品字段集 item_id必填','example'=>'item_id,title,item_store.store,item_status.approve_status','default'=>''],
        );


        return $return;
    }

    public function get($params)
    {
        $fields = $params['fields'];
        $filter['sku_id'] = split(',', $params['sku_ids']);

        $skuMdl = app::get('sysitem')->model('sku');
        $skus = $skuMdl->getList($fields, $filter);

        $skuStoreList = array();
        $skuStoreMdl = app::get('sysitem')->model('sku_store');
        $skuStoreList = $skuStoreMdl->getList('sku_id,store,freez');
        $skuStoreList = array_bind_key($skuStoreList, 'sku_id');


        if(isset($skus[0]['item_id']))
        {
            $itemIds = array_column($skus, 'item_id');

            $objMdlItem = app::get('sysitem')->model('item');
            $itemList = $objMdlItem->getList('*', ['item_id'=>$itemIds]);
            $itemList = array_bind_key($itemList, 'item_id');

            $objMdlIemStatus = app::get('sysitem')->model('item_status');
            $itemStatusList = $objMdlIemStatus->getList('item_id,approve_status', ['item_id'=>$itemIds]);
            $itemStatusList = array_bind_key($itemStatusList, 'item_id');
        }

        foreach($skus as &$sku)
        {
            $itemId = $sku['item_id'];
            $skuId  = $sku['sku_id'];
            if(isset($itemList[$itemId]))
            {
                $sku['item'] = $itemList[$itemId];
                $sku['item']['approve_status'] = $itemStatusList[$itemId]['approve_status'];
            }
            $sku['store'] = $skuStoreList[$skuId]['store'] - $skuStoreList[$skuId]['freez'];
        }

        return $skus;
    }
}
