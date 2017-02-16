<?php
class sysshop_api_shop_saveShopCat{

    public $apiDescription = "保存店铺分类";
    public function getParams()
    {
        $return['params'] = array(
            'catlist' => ['type'=>'string','valid'=>'','description'=>'店铺分类','default'=>'','example'=>''],
            'shop_id' => ['type'=>'string','valid'=>'required','description'=>'店铺id','default'=>'','example'=>''],
        );
        return $return;
    }
    public function saveShopCat($params)
    {
        $data = unserialize($params['catlist']);
        $shopId = $params['shop_id'];
        $flag = kernel::single('sysshop_data_cat')->storeCat($data,$shopId);
        return $flag;
    }
}
