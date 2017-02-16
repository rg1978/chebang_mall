<?php
class syslogistics_api_dlytmpl_delete{

    public $apiDescription = "运费模板更新";
    public function getParams()
    {
        $return['params'] = array(
            'template_id' =>['type'=>'string','valid'=>'required', 'description'=>'模板id','default'=>'','example'=>'1'],
            'shop_id' =>['type'=>'string','valid'=>'required', 'description'=>'店铺id','default'=>'','example'=>'1'],
        );
        return $return;
    }

    public function delete($params)
    {
        $filter['template_id'] = $params['template_id'];
        $filter['shop_id']     = $params['shop_id'];

        $searchParams['shop_id']    = $params['shop_id'];
        $searchParams['dlytmpl_id'] = $params['template_id'];
        $searchParams['fields'] = 'item_id';
        $searchParams['page_no'] = 0;
        $searchParams['page_size'] = 1;

        $itemList = app::get('topshop')->rpcCall('item.search', $searchParams);
        if($itemList['total_found'])
        {
             throw new \LogicException('该快递模板还有商品绑定，不能删除！');
        }

        $objDataDlyTmpl = kernel::single('syslogistics_data_dlytmpl');
        $result = $objDataDlyTmpl->remove($filter);
        return $result;
    }
}
