<?php
class systrade_api_cart_addCart{
    public $apiDescription = "加入购物车";
    public function getParams()
    {
        $return['params'] = array(
            'user_id' => ['type'=>'int','valid'=>'required','description'=>'会员id','default'=>'','example'=>'3'],
            'quantity' => ['type'=>'int','valid'=>'','description'=>'商品数量','default'=>'','example'=>'3'],
            'sku_id' => ['type'=>'int','valid'=>'required_if:goodsType,item','description'=>'货品id','default'=>'','example'=>'3'],
            'package_sku_ids' => ['type'=>'string','valid'=>'required_if:goodsType,package','description'=>'组合促销sku_id','default'=>'','example'=>'11,21,45'],
            'package_id' => ['type'=>'integer','valid'=>'sometimes|required|integer','description'=>'组合促销id','default'=>'','example'=>'3'],
            'obj_type' =>['type'=>'string','valid'=>'','description'=>'对象类型','default'=>'','example'=>'item'],
            'mode' => ['type'=>'string','valid'=>'required','description'=>'购物车类型','default'=>'','example'=>'cart'],
        );
        return $return;
    }
    public function addCart($params)
    {
        $user_id = $params['user_id'];
        $data = kernel::single('systrade_data_cart', $user_id)->addCart($params);
        return $data;
    }
}
