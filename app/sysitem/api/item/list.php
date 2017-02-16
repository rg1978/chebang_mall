<?php
/**
 * 获取单个商品的详细信息
 * item.list.get
 */
class sysitem_api_item_list {

    /**
     * 接口作用说明
     */
    public $apiDescription = '商品id列表，多个item_id用逗号隔开 调用一次不超过20个';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        $return['params'] = array(
            'item_id' => ['type'=>'string','valid'=>'required','description'=>'商品编号','example'=>'2,3,4'],
            'shop_id' => ['type'=>'string','valid'=>'','description'=>'店铺id','example'=>'18'],
            'fields' => ['type'=>'field_list','valid'=>'required','description'=>'要获取的商品字段集 item_id必填','example'=>'item_id,title,item_store.store,item_status.approve_status','default'=>''],
        );

        $return['extendsFields'] = ['item_store','item_status','sku'];

        return $return;
    }

    public function get($params)
    {
        $filter['item_id'] = explode(',',$params['item_id']);
        if( count($filter['item_id']) > 20 )
        {
            throw new Exception('一次调用的商品ID，不能超过20');
        }

        if($params['shop_id'])
        {
            $filter['shop_id'] = $params['shop_id'];
        }

        $row = $params['fields']['rows'];
        $extends = $params['fields']['extends'];

        $objMdlItem = app::get('sysitem')->model('item');
        $itemList = $objMdlItem->simpleGetList($row, $filter);
        foreach( $itemList as $k=>$rows )
        {
            $itemList[$k]['list_image'] = $rows['list_image'] ? explode(',',$rows['list_image']) : null;
        }

        if( empty($itemList) ) return array();
        $itemIds = array_column($itemList, 'item_id');
        $itemListData = array_bind_key($itemList, 'item_id');

        $objectItemInfo = kernel::single('sysitem_item_info');
        if( $extends['item_store'] )
        {
           $storeData =  $objectItemInfo->getItemStore($itemIds);
           if( $storeData )
            {
                foreach( $storeData as $itemId =>$storeVal)
                {
                    $itemListData[$itemId]['store'] = $storeVal['store'];
                    $itemListData[$itemId]['freez'] = $storeVal['freez'];
                }
            }
        }

        if( $extends['item_status'] )
        {
           $itemStatus =  $objectItemInfo->getItemStatus($itemIds);
           if( $itemStatus )
            {
                foreach( $itemStatus as $itemId =>$val)
                {
                    $itemListData[$itemId]['item_status']['approve_status'] = $val['approve_status'];
                    $itemListData[$itemId]['item_status']['delist_time'] = $val['delist_time'];
                    $itemListData[$itemId]['item_status']['list_time'] = $val['list_time'];
                }
            }
        }

        if( $extends['sku'] )
        {
            $objMdlItemSku = app::get('sysitem')->model('sku');
            $skuList = $objMdlItemSku->getList('*', ['item_id'=>$itemIds]);
            $skuIds = array_column($skuList, 'sku_id');
            foreach( $skuList as $val )
            {
                $itemId = $val['item_id'];
                $skuId = $val['sku_id'];
                $itemListData[$itemId]['sku'][$skuId] = $val;
            }

            $objMdlSkuStore = app::get('sysitem')->model('sku_store');
            $storeList = $objMdlSkuStore->getList('store,freez,sku_id,item_id',array('sku_id'=>$skuIds));
            if( $storeList )
            {
                foreach($storeList as $row)
                {
                    $itemId = $row['item_id'];
                    $skuId  = $row['sku_id'];
                    $itemListData[$itemId]['sku'][$skuId]['store'] = $row['store'];
                    $itemListData[$itemId]['sku'][$skuId]['freez'] = $row['freez'];
                }
            }
        }

        return $itemListData;
    }
}
