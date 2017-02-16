<?php
/**
 * topapi
 *
 * -- member.rate.list
 * -- 会员中心我的评价列表
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_rate_list implements topapi_interface_api {

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '会员中心我的评价列表';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        //接口传入的参数
        $return = array(
            'page_no'   => ['type'=>'int',    'valid'=>'numeric',  'example'=>'1', 'desc'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int',    'valid'=>'numeric',  'example'=>'10', 'desc'=>'每页数据条数,默认20条'],
        );

        return $return;
    }

    public function handle($params)
    {
        $params['role'] = 'buyer';
        $params['fields'] = '*,append';
        $data = app::get('topapi')->rpcCall('rate.list.get', $params);

        $defaultImageId = kernel::single('image_data_image')->getImageSetting('item');

        $result['list']= $data['trade_rates'];
        foreach( $result['list'] as $k=>$row)
        {
            if( !$row['item_pic'] )
            {
                $result['list'][$k]['item_pic'] = $row['item_pic'] ? base_storager::modifier($row['item_pic'], 't') : base_storager::modifier($defaultImageId['t']['default_image']);
            }

            if( $row['rate_pic'] )
            {
                $result['list'][$k]['rate_pic'] = explode(',', $row['rate_pic']);
                foreach( $result['list'][$k]['rate_pic'] as &$ratePic )
                {
                    $ratePic = base_storager::modifier($ratePic, 't');
                }
            }

            if( $row['append'] )
            {
                if( $row['append']['append_rate_pic'] )
                {
                    $result['list'][$k]['append']['append_rate_pic'] = explode(',', $row['append']['append_rate_pic']);
                    foreach( $result['list'][$k]['append']['append_rate_pic'] as &$appendRatePic )
                    {
                        $appendRatePic = base_storager::modifier($appendRatePic, 't');
                    }
                }
            }
            else
            {
                $result['list'][$k]['append'] = null;
            }
        }

        $result['pagers']['total'] = $data['total_results'];
        return $result;
    }

    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"list":[{"rate_id":14,"tid":"1608041129420004","oid":"1608041129430004","user_id":4,"shop_id":3,"item_id":22,"item_title":"ONLY冬装新品宽松圆领底摆开叉设计针织连衣裙女","item_price":"299.000","item_pic":"http://images.bbc.shopex123.com/images/29/e5/22/670cf312b0aaace1ebf6305d6f346ee147f29c16.jpg","spec_nature_info":"颜色：白色、尺码：s","result":"bad","content":"sdfghjkl","rate_pic":null,"is_reply":1,"reply_content":"xxsw222ff4ss","reply_time":1472009726,"anony":0,"role":"buyer","is_lock":0,"is_append":0,"is_appeal":0,"appeal_status":"SUCCESS","appeal_again":0,"appeal_time":1472006385,"trade_end_time":1470289679,"created_time":1472006355,"modified_time":1472009726,"disabled":0,"append":[]}],"pagers":{"total":3}}}';
    }
}
