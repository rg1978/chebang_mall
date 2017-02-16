<?php
/**
 * topapi
 *
 * -- item.search
 * -- 会员中心首页数据统计
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_item_itemRateList implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取商品评价列表';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'item_id'   => ['type'=>'int', 'valid'=>'required|numeric|min:1','example'=>'1', 'desc'=>'商品id。必须是正整数', 'msg'=>'商品id必须为正整数'],
            'rate_type'   => ['type'=>'int', 'valid'=>'required|numeric|in:0,1,2,3,4','example'=>'1', 'desc'=>'评价类型、晒图。类型值为0：全部，1：好评，2：中评，3：差评，4：晒图', 'msg'=>'类型必须为0：全部，1：好评，2：中评，3：差评，4：晒图'],
            //分页参数
            'page_no'   => ['type'=>'int','valid'=>'', 'example'=>'', 'desc'=>'分页当前页数,默认为1', 'msg'=>''],
            'page_size' => ['type'=>'int','valid'=>'', 'example'=>'', 'desc'=>'每页数据条数,默认10条', 'msg'=>''],
            // 'order_by'   => ['type'=>'string','valid'=>'', 'example'=>'', 'desc'=>'排序，默认created_time desc', 'msg'=>''],

            //返回字段
            // 'fields'    => ['type'=>'field_list','valid'=>'', 'example'=>'', 'desc'=>'需要返回的字段', 'msg'=>''],

        ];
        return $return;
    }

    /**
     * @return
     */
    public function handle($params)
    {
        $itemId = intval($params['item_id']);

        $rate_type_arr = ['1'=>'good','2'=>'neutral','3'=>'bad'];
        $rate_type = $params['rate_type'];
        // $pagedata['rate_type_group'] = $rate_type;
        $apifilter = [
            'item_id'=>$itemId,
            'page_no'=>$params['page_no'] ? (int)$params['page_no'] : 1,
            'page_size'=>$params['page_size'] ? (int)$params['page_size'] : 10,
            'fields'=>'rate_id,user_id,result,content,rate_pic,is_reply,reply_content,reply_time,anony,is_append,created_time,append.rate_id,append.append_content,append.append_rate_pic,append.is_reply,append.append_reply_content,append.reply_time',
        ];
        if( $rate_type == '4' )
        {
            $apifilter['is_pic'] = true;
            // $pagedata['query_type'] = 'pic';
        }
        else
        {
            // $pagedata['query_type'] = 'content';
        }

        if($rate_type)
        {
            $apifilter['result'] = $rate_type_arr[$rate_type];
            // $pagedata['rate_type'] = $rate_type_arr[$rate_type];
        }
        $data = app::get('topapi')->rpcCall('rate.list.get', $apifilter);

        $userId = array_column($data['trade_rates'],'user_id');
        if($userId)
        {
            $userName = app::get('topapi')->rpcCall('user.get.account.name',array('user_id'=>$userId), 'buyer');
        }

        if(!$data['trade_rates'])
        {
            return (object)[];
        }

        foreach($data['trade_rates'] as &$row )
        {
            if($userId)
            {
                $row['user_name'] = $userName[$row['user_id']];
                unset($row['user_id']);
            }

            if($row['rate_pic'])
            {
                $ratepics = explode(",",$row['rate_pic']);
                foreach($ratepics as &$v)
                {
                    $v = base_storager::modifier($v, 't');
                }
                $row['rate_pic'] = $ratepics;
            }

            if( $row['append']  )
            {
                if( $row['append']['append_rate_pic'] )
                {
                    $appendratepics = explode(",", $row['append']['append_rate_pic']);
                    foreach($appendratepics as &$v1)
                    {
                        $v1 = base_storager::modifier($v1, 't');
                    }
                    $row['append']['append_rate_pic'] = $appendratepics;
                }
            }
            else
            {
                $row['append'] = null;
            }
        }

        $pagedata['list'] = $data['trade_rates'];


        $pagedata['pagers']['total'] = $data['total_results'];

        return $pagedata;
    }

    /**
     * 返回json格式的例子
     * @return string 结果json串
     */
    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"list":[{"rate_id":35,"result":"bad","content":"艾格 ETAM 彩色数码印花无袖连衣裙艾格 ETAM 彩色数码印花无袖连衣裙艾格 ETAM 彩色数码印花无袖连衣裙艾格 ETAM 彩色数码印花无袖连衣裙艾格 ETAM 彩色数码印花无袖连衣裙艾格 ETAM 彩色数码印花无袖连衣裙艾格 ETAM 彩色数码印花无袖连衣裙艾格 ETAM 彩色数码印花无袖连衣裙艾格 ETAM 彩色数码印花无袖连衣裙艾格 ETAM 彩色数码印花无袖连衣裙艾格 ETAM 彩色数码印花无袖连衣裙艾格 ETAM 彩色数码印花无袖连衣裙艾格 ETAM 彩色数码印花无袖连衣裙艾格 ETAM 彩色数码印花无袖连衣裙艾格 ETAM 彩色数码印花无袖连衣裙艾格 ETAM 彩色数码印花","rate_pic":["http://192.168.65.145/bbc/public/images/cf/81/cf/d68f5b91d63110be8b8c92618e59640daa33c494.jpg_t.jpg","http://192.168.65.145/bbc/public/images/52/f4/3d/39438d9daafb37d170f0daf43f0ced0b0458a02a.jpg_t.jpg","http://192.168.65.145/bbc/public/images/d6/8f/c7/305c2fa208782f8ba7ac0cfe2cb753372b62dc38.jpg_t.jpg","http://192.168.65.145/bbc/public/images/a5/09/be/b30a7472bc635aa286c1d69c3a44cebfb546caef.png_t.png","http://192.168.65.145/bbc/public/images/41/86/a0/bbca21200654c1817f9890dd3fc7ead666373f6d.gif_t.gif"],"is_reply":1,"reply_content":"阿斯利康决定啦带上垃圾袋了解阿隆索大量时间大家说了德拉吉拉时代啊圣诞节啊的","reply_time":1472109360,"anony":1,"is_append":1,"created_time":1472108456,"append":{"rate_id":35,"append_content":"阿斯顿理解啊圣诞节阿里弹尽粮绝啊我的理解达瓦酒店啊等啦世界的理解啊善良的啊理解的沙拉酱大的撒","append_rate_pic":["http://192.168.65.145/bbc/public/images/e0/5d/22/d97bb10737a54c25d0d28649f90bdb0e52e5cab6.jpg_t.jpg","http://192.168.65.145/bbc/public/images/b4/70/a9/2e660459e9935feaac0f80a05c06239c7a75080a.jpg_t.jpg"],"is_reply":1,"append_reply_content":"阿婆是打算看杜拉斯的杜拉斯的了阿斯顿啊等","reply_time":1472109368},"user_name":"buyer01"},{"rate_id":12,"result":"good","content":"系统默认好评","rate_pic":null,"is_reply":0,"reply_content":null,"reply_time":null,"anony":1,"is_append":0,"created_time":1471499043,"append":null,"user_name":"demo"},{"rate_id":13,"result":"good","content":"系统默认好评","rate_pic":null,"is_reply":0,"reply_content":null,"reply_time":null,"anony":1,"is_append":0,"created_time":1471499043,"append":null,"user_name":"demo"}],"pagers":{"total":3}}}';
    }

}
