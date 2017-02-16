<?php
/**
 * topapi
 *
 * -- promotion.activity.list
 * -- 获取平台活动列表
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_promotion_activityList implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取平台活动列表';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'status'   => ['type'=>'string','valid'=>'required|in:active,comming', 'example'=>'active', 'desc'=>'active:活动中的，comming:即将开始的活动', 'msg'=>'值必须是active或者comming'],
            //分页参数
            'page_no'   => ['type'=>'int','valid'=>'min:1|numeric', 'example'=>'1', 'desc'=>'分页当前页数,默认为1', 'msg'=>''],
            'page_size' => ['type'=>'int','valid'=>'', 'example'=>'10', 'desc'=>'每页数据条数,默认10条', 'msg'=>''],

            //返回字段
            // 'fields'    => ['type'=>'field_list','valid'=>'', 'example'=>'*', 'desc'=>'需要返回的字段', 'msg'=>''],
        ];
        return $return;
    }

    /**
     * @return
     */
    public function handle($params)
    {
        $apiparams = array(
            'order_by' => 'mainpush desc',
            'fields' => 'activity_name,activity_id,mainpush,slide_images,end_time,start_time,discount_max,discount_min',
        );
        // 正在进行的活动条件
        if($params['status']=='active')
        {
            $apiparams['start_time'] = 'sthan';
            $apiparams['end_time'] = 'bthan';
        }
        // 即将开始的活动条件
        if($params['status']=='comming')
        {
            $apiparams['release_time'] = 'sthan';
            $apiparams['start_time'] = 'bthan';
        }
        $apiparams['page_no'] = $params['page_no'] ? (int)$params['page_no'] : 1;
        $apiparams['page_size'] = $params['page_size'] ? (int)$params['page_size'] : 10;
        $activitys = app::get('topapi')->rpcCall('promotion.activity.list', $apiparams);

        $pagedata['list'] = $activitys['data'] ? : (object)[];

        $pagedata['pagers']['total'] = $activitys['count'] ? : 0;

        return $pagedata;
    }

    /**
     * 返回json格式的例子
     * @return string 结果json串
     */
    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"list":[{"activity_name":"年欢惠","activity_id":1,"mainpush":0,"slide_images":"http://images.bbc.shopex123.com/images/bd/12/4e/f71b06d423f37dae13d5c3bc6d1ca97bb771f61f.jpg","end_time":1483113600,"start_time":1454083200,"discount_max":90,"discount_min":70},{"activity_name":"路由器专场促销","activity_id":2,"mainpush":0,"slide_images":"http://images.bbc.shopex123.com/images/f8/9c/4a/e1748250afb25c44c436a74453cbd22135bc357b.png","end_time":1609344000,"start_time":1453802340,"discount_max":99,"discount_min":1},{"activity_name":"童装专场","activity_id":3,"mainpush":0,"slide_images":"http://images.bbc.shopex123.com/images/2c/d0/76/d0ddb0699923b3d0761d18fa0d63f80e175d0f4a.jpg","end_time":1609344000,"start_time":1453803060,"discount_max":90,"discount_min":10}],"pagers":{"total":3}}}';
    }

}
