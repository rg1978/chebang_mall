<?php
/**
 * topapi
 *
 * -- promotion.activity.detail
 * -- 获取平台活动详情
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_promotion_activityDetail implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取平台活动详情及其商品列表';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'activity_id'   => ['type'=>'int','valid'=>'required|min:1', 'example'=>'active', 'desc'=>'活动id', 'msg'=>'活动id必须是正整数'],
            //分页参数
            'page_no'   => ['type'=>'int','valid'=>'min:1|numeric', 'example'=>'1', 'desc'=>'分页当前页数,默认为1', 'msg'=>''],
            'page_size' => ['type'=>'int','valid'=>'', 'example'=>'10', 'desc'=>'每页数据条数,默认10条', 'msg'=>''],
            'orderBy'   => ['type'=>'string','valid'=>'', 'example'=>'', 'desc'=>'商品列表排序，默认 sales_count desc', 'msg'=>''],

            //返回字段
            'info_fields'    => ['type'=>'field_list','valid'=>'', 'example'=>'*', 'desc'=>'活动详情需要返回的字段', 'msg'=>''],
            'item_fields'    => ['type'=>'field_list','valid'=>'', 'example'=>'*', 'desc'=>'活动商品列表需要返回的字段', 'msg'=>''],
        ];
        return $return;
    }

    /**
     * @return
     */
    public function handle($params)
    {
        // 获取活动详情基本信息
        $default_info_fields = 'activity_id,activity_name,slide_images,activity_tag,start_time,end_time,release_time,discount_max,discount_min,remind_enabled';
        $info_fields = $params['info_fields'] ? : $default_info_fields;
        $info_params = ['activity_id' => $params['activity_id'], 'fields'=>$info_fields];

        $pagedata['info'] = app::get('topapi')->rpcCall('promotion.activity.info', $info_params);

        // 获取活动的商品列表
        $default_item_fields = 'title,item_default_image,price,item_id,activity_id,sales_count,activity_price';
        $item_fields = $params['item_fields'] ? : $default_item_fields;
        $item_params = [
            'status' => 'agree',
            'activity_id' => $params['activity_id'],
            'page_no' => $params['page_no'] ? (int)$params['page_no'] : 1,
            'page_size' => $params['page_size'] ? (int)$params['page_size'] : 10,
            'order_by' => $params['orderBy'] ? : ' sales_count DESC',
            'fields' => $item_fields,
        ];
        if($params['cat_id'])
        {
            $item_params['cat_id'] = intval($params['cat_id']);
        }

        $item = app::get('topapi')->rpcCall('promotion.activity.item.list', $item_params);

        $pagedata['list'] = $item['list'] ? : (object)[];

        $pagedata['pagers']['total'] = $item['count'] ? : 0;

        return $pagedata;
    }

    /**
     * 返回json格式的例子
     * @return string 结果json串
     */
    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"info":{"activity_id":1,"activity_name":"年欢惠","slide_images":"http://images.bbc.shopex123.com/images/bd/12/4e/f71b06d423f37dae13d5c3bc6d1ca97bb771f61f.jpg","activity_tag":"团购","start_time":1454083200,"end_time":1483113600,"release_time":1453996800,"discount_max":90,"discount_min":70,"remind_enabled":1},"list":[{"title":"纳兰小猪童装男童卫衣加厚冬款 中大儿童加绒套头卫衣","item_default_image":"http://images.bbc.shopex123.com/images/89/65/45/220308767e11239cdd860754a6621536780f46d3.png","price":"59.000","item_id":68,"activity_id":3,"sales_count":2,"activity_price":"19.000"},{"title":"纳兰小猪童装男童衬衫加绒加厚中大儿童长袖秋装2015新款衬衣","item_default_image":"http://images.bbc.shopex123.com/images/fc/04/43/c7d244e2c104d6d2f40b9064aab188411099134d.png","price":"50.000","item_id":67,"activity_id":3,"sales_count":2,"activity_price":"39.990"}],"pagers":{"total":22}}}';
    }

}
