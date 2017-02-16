<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 获取多条赠品促销列表
 * promotion.gift.list
 */
final class syspromotion_api_gift_giftList{

	public $apiDescription = '获取指定店铺的赠品促销列表';

    public function getParams()
    {
        $return['params'] = array(
            'page_no' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'每页数据条数,默认10条'],
            'fields'    => ['type'=>'field_list', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'需要的字段','default'=>'','example'=>''],
            'orderBy' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'排序，默认created_time asc'],
            'shop_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'店铺ID,user_id和shop_id必填一个'],
            'gift_id' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'3,4,5', 'description'=>'赠品促销id'],
            'gift_name' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'赠品活动名称'],
        );

        return $return;
    }


	public function giftList($params)
	{
		$objMdlgift = app::get('syspromotion')->model('gift');
        if(!$params['fields'])
        {
            $params['fields'] = '*';
        }
        $filter = array('shop_id'=>$params['shop_id']);

        if($params['gift_id'])
        {
        	$filter['gift_id'] = explode(',',$params['gift_id']);
        }

        if($params['gift_name'])
        {
        	$filter['gift_name'] = $params['gift_id'];
        }

        $giftCount = $objMdlgift->count($filter);
        if(!$giftCount)
        {
            $result = array(
                    'gifts' => array(),
                    'count' => 0,
            );

            return $result;
        }
        $pageTotal = ceil($giftCount/$params['page_size']);
        $page =  $params['page_no'] ? $params['page_no'] : 1;
        $limit = $params['page_size'] ? $params['page_size'] : 10;
        $currentPage = ($pageTotal < $page) ? $pageTotal : $page; //防止传入错误页面，返回最后一页信息
        $offset = ($currentPage-1) * $limit;

        $orderBy  = $params['orderBy'] ? $params['orderBy'] : ' gift_id DESC';
        $giftData = $objMdlgift->getList($params['fields'], $filter, $offset, $limit, $orderBy);
        $result = array(
            'gifts' => $giftData,
            'count' => $giftCount,
        );

        return $result;
	}
}
