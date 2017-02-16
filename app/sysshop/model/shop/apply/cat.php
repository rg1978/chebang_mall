<?php
class sysshop_mdl_shop_apply_cat extends dbeav_model{

    public function modifier_cat_id(&$colList)
    {
        foreach($colList as $k=>$val)
        {
            $catList = app::get('sysshop')->rpcCall('category.cat.get.info',['cat_id'=>$val]);
            $catName = array_column($catList,'cat_name');
            $colList[$k] = implode(',',$catName);
        }
    }
}
