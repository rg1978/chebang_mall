<?php
/**
 * topapi
 *
 * -- member.point.detail
 * -- 会员积分明细
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_point_detail implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '会员积分明细';

    /**
     * 定义API传入的应用数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'page_no'   => ['type'=>'int','valid'=>'', 'example'=>'', 'desc'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int','valid'=>'', 'example'=>'', 'desc'=>'每页数据条数,默认10条'],
        ];
    }

    /**
     * @return int user.point_count 会员总积分值
     * @return int user.expired_point 将要过期积分
     * @return time user.modified_time 记录时间
     * @return time user.expired_time 过期时间
     * @return time list.modified_time 记录时间
     * @return string list.behavior_type obtain获得积分 consume消费积分
     * @return string list.behavior 行为描述
     * @return string list.point 变更积分值
     * @return string list.remark 备注
     * @return string list.expiration_time 积分过期时间
     * @return string pagers.total 积分明细总条数
     */
    public function handle($params)
    {
        $page = $params['page_no'] ? $params['page_no'] : 1;
        $pageSize = $params['page_size'] ? $params['page_size'] : 10;
        $params['orderBy'] = 'modified_time desc';

        $data = app::get('topapi')->rpcCall('user.pointGet',$params);

        $result['point_total'] = $data['datalist']['user'];
        $result['list'] = $data['datalist']['point'];
        $result['pagers']['total'] = $data['totalnum'];
        return $result;
    }
}

