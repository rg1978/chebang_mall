<?php
class sysshop_api_shop_getDlycorpInfo{

    public $apiDescription = "获取店铺签约物流详情";
    public function getParams()
    {
        $return['params'] = array(
            'shop_id' =>['type'=>'int','valid'=>'required', 'description'=>'物流公司编号id','default'=>'','example'=>'1'],
            'corp_id' =>['type'=>'int','valid'=>'required', 'description'=>'物流公司编号id','default'=>'','example'=>'1'],
            'fields' => ['type'=>'field_list','valid'=>'', 'description'=>'获取指定字段','default'=>'corp_id,corp_code,corp_name,shop_id','example'=>'corp_id,corp_code,corp_name'],
        );
        return $return;
    }
    public function get($params)
    {

        $filter['shop_id'] = $params['shop_id'];
        $filter['corp_id'] = $params['corp_id'];

        //默认查询字段
        $row = "corp_id,corp_code,corp_name,shop_id";
        if($params['fields'])
        {
            $row = $params['fields'];
        }

        $objMdlDlycorpShop = app::get('sysshop')->model('shop_rel_dlycorp');
        $pagedata = $objMdlDlycorpShop->getRow($row,$filter);
        return $pagedata;
    }

}
