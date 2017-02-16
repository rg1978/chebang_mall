<?php
/**
 * ShopEx licence
 *
 * - promotion.hongbao.list.get
 * - 获取多条红包列表
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 *
 */
final class syspromotion_api_hongbao_list {

    public $apiDescription = '获取多条红包列表';

    public function getParams()
    {
        $return['params'] = array(
            'hongbao_id'     => ['type'=>'int',         'valid'=>'',         'title'=>'红包ID',        'description'=>'红包ID'],
            'hongbao_status' => ['type'=>'string',      'valid'=>'',         'title'=>'红包状态',      'description'=>'红包状态'],
            'hongbao_name'   => ['type'=>'string',      'valid'=>'',         'title'=>'红包名称',      'description'=>'红包名称'],
            'platform'       => ['type'=>'string',      'valid'=>'',         'title'=>'红包适用平台',  'description'=>'红包适用平台'],
            'page_no'        => ['type'=>'int',         'valid'=>'required', 'title'=>'分页当前页数',  'description'=>'分页当前页数,默认为1'],
            'page_size'      => ['type'=>'int',         'valid'=>'required', 'title'=>'每页数据条数',  'description'=>'每页数据条数,默认10条'],
            'fields'         => ['type'=>'field_list',  'valid'=>'required', 'title'=>'需要的字段',    'description'=>'需要的字段'],
            'orderBy'        => ['type'=>'string',      'valid'=>'',         'title'=>'排序',          'description'=>'排序，默认created_time desc'],
        );
        return $return;
    }

    /**
     * 获取多条红包列表
     */
    public function get($params)
    {
        if( $params['hongbao_id'] )
        {
            $filter['hongbao_id'] = explode(',',$params['hongbao_id']);
        }

        if( in_array($params['platform'], ['all','pc','wap']) )
        {
            $filter['platform'] = $params['platform'];
        }

        if( $params['hongbao_name'] )
        {
            $filter['hongbao_name'] = $params['hongbao_name'];
        }

        if( in_array($params['hongbao_status'], ['pending','active','stop']) )
        {
            $filter['hongbao_status'] = $params['hongbao_status'];
        }

        $objMdlHongbao = app::get('syspromotion')->model('hongbao');
        $hongbaoTotal = $objMdlHongbao->count($filter);

        $limit = $params['page_size'] ? $params['page_size'] : 10;
        $page =  $params['page_no'] ? $params['page_no'] : 1;

        $pageTotal = ceil($hongbaoTotal/$params['page_size']);
        $currentPage = $pageTotal < $page ? $pageTotal : $page;
        $offset = ($currentPage-1) * $limit;

        $orderBy  = $params['orderBy'] ? $params['orderBy'] : ' created_time DESC';
        $hongbaoData = $objMdlHongbao->getList($params['fields'], $filter, $offset, $limit, $orderBy);

        foreach( $hongbaoData as &$row)
        {
            if( $row['hongbao_list'] )
            {
                $row['hongbao_list'] = unserialize($row['hongbao_list']);
            }
        }

        $result['list'] = $hongbaoData;
        $result['pagers']['total'] = $hongbaoTotal;

        return $result;
    }
}

