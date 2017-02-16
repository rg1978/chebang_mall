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
class topapi_api_v1_item_itemDesc implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取移动端商品描述';

    public function __construct()
    {
        $this->objLibSearch = kernel::single('topapi_item_search');
    }

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'item_id' => ['type'=>'int', 'valid'=>'required|numeric|min:1','example'=>'1', 'desc'=>'商品id。必须是正整数', 'msg'=>'商品id必须为正整数'],
        ];
        return $return;
    }

    /**
     * @return
     */
    public function handle($params)
    {
        $itemId = intval($params['item_id']);

        $filter['item_id'] = $itemId;
        $filter['fields'] = "item_id,params,item_nature,bn,brand_id,sub_title,item_desc.wap_desc";
        $detailData = app::get('topapi')->rpcCall('item.get', $filter);

        if(!$detailData['params'])
        {
            $detailData['params'] = (object)[];
        }
        if(!$detailData['natureProps'])
        {
            $detailData['natureProps'] = (object)[];
        }
        $detailData['remark'] = [
            '品牌' => $detailData['brand_name'],
            '编号' => $detailData['bn'],
            '备注' => $detailData['sub_title'],
        ];
        unset($detailData['bn']);
        unset($detailData['brand_id']);
        unset($detailData['sub_title']);
        unset($detailData['sub_title']);
        unset($detailData['brand_name']);
        unset($detailData['brand_alias']);
        unset($detailData['brand_logo']);

        return $detailData;
    }

    /**
     * 返回json格式的例子
     * @return string 结果json串
     */
    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"item_id":130,"wap_desc":"\u003Cp style=\u0022line-height: 20px;\u0022\u003E\u003C\/p\u003E\u003Cp style=\u0022line-height: 20px;\u0022\u003E\u003Cbr\u003E\u003Cimg src=\u0022http:\/\/images.bbc.shopex123.com\/images\/5d\/7c\/23\/aa461ffb145e94922c1036e5aa8edbbb138f1bf6.png\u0022 style=\u0022width: 864px;\u0022\u003E\u003C\/p\u003E\u003Cimg src=\u0022http:\/\/images.bbc.shopex123.com\/images\/0e\/a9\/d6\/25d3985eeb7d566fafdd5a174c6ef31e42007b9a.png\u0022 style=\u0022line-height: 20px; width: 864px;\u0022\u003E\u003Cbr\u003E"}}';
    }

}
