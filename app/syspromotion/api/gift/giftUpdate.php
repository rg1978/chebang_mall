<?php
class syspromotion_api_gift_giftUpdate{
	public $apiDescription = '修改赠品促销促销数据';

    public function getParams()
    {
        $return['params'] = array(
        	'gift_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'赠品促销id'],
            'shop_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'店铺编号'],
            'gift_name' => ['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'赠品促销名称'],
            'condition_type' => ['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'送赠品条件标准'],
            'limit_quantity' => ['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'满足条件数量'],
            'gift_desc' => ['type'=>'string', 'valid'=>'string', 'default'=>'', 'example'=>'', 'description'=>'赠品规则描述'],
            'valid_grade' => ['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'适用的会员等级'],
            'gift_item' => ['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'赠品sku'],
            'gift_item_info' => ['type'=>'json', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'赠品数量集合'],
            'start_time' => ['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'赠品促销开始时间'],
            'end_time' => ['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'赠品促销结束时间'],
            'gift_rel_itemids' => ['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'参加赠品促销的商品数据'],
        );

        return $return;
    }

    /**
     *  添加组合促销促销数据
     * @param  array $apiData 组合促销促销各种值
     * @return
     */
    public function giftUpdate($apiData)
    {
        return kernel::single('syspromotion_gift')->saveGift($apiData);
    }
}
