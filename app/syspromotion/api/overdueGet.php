<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 获取获取过期促销
 * promotion.promotion.get
 */
final class syspromotion_api_overdueGet {

    public $apiDescription = '获取过期促销id';

    public function getParams()
    {
        $return['params'] = array(
            'end_time'       => ['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'活动结束时间'],
            'check_status'       => ['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'活动状态'],
            'fields'    => ['type'=>'field_list', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'需要的字段','default'=>'','example'=>''],
            'page_no' => ['type'=>'int','valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int','valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'每页数据条数,默认20条'],
        );

        return $return;
    }

    /**
     *  获取过期促销列表信息
     * @param  array $params 筛选条件数组
     * @return array         返回促销列表
     */
    public function overdueGet($params)
    {
        $filter = array(
            'end_time|lthan'=>$params['end_time'],
            'check_status|noequal'=>$params['check_status'],
        );
        if(!$params['fields'])
        {
            $params['fields'] = '*';
        }
        $pageSize = $params['page_size'] ? $params['page_size'] : 100;
        $pageNo = $params['page_no'] ? $params['page_no'] : 1;
        $limit = $pageSize;
        $page = ($pageNo-1)*$limit;
        $promotionMdl = app::get('syspromotion')->model('promotions');
        $promotionList = $promotionMdl->getList($params['fields'], $filter, $page,$limit, $orderBy);

        return $promotionList;
    }

}

