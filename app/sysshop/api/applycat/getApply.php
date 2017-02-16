<?php

class sysshop_api_applycat_getApply{

    public $apiDescription = "获取店铺申请的类目";
    public function getParams()
    {
        $return['params'] = array(
            'shop_id' =>['type'=>'int','valid'=>'int|required', 'description'=>'店铺id','default'=>'','example'=>'1'],
            'cat_id' => ['type'=>'int','valid'=>'int|required', 'description'=>'类目id','default'=>'','example'=>''],
        );
        return $return;
    }
    public function getAppleyList($params)
    {
        $filter['shop_id'] = $params['shop_id'];
        $filter['cat_id'] = $params['cat_id'];
        $filter['check_status|in'] = ['pending','adopt'];
        $objMdlApplyCat = app::get('sysshop')->model('shop_apply_cat');
        $data = $objMdlApplyCat->getList('*',$filter);
        return $data;
    }



}
