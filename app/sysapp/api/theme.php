<?php
/**
 * ShopEx licence
 * - sysapp.modules.get
 * - 用于获取app端页面模块配置信息
 * @copyright Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license   http://ecos.shopex.cn/ ShopEx License
 * @link      http://www.shopex.cn/
 * @author    shopex 2016-05-23
 */
final class sysapp_api_theme{

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '获取app端页面模块配置信息';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function getParams()
    {
        $return['params'] = array(
            'tmpl' => ['type'=>'string', 'valid'=>'required|in:index', 'title'=>'页面', 'example'=>'index', 'desc'=>'页面类型'],
        );

        return $return;
    }

    /**
     * 获取app端页面模块配置信息
     * @desc 用于获取app指定页面模块配置信息
     * @return array list 关联商品列表
     * @return int list[].coupon_id 优惠券ID
     * @return int list[].item_id 商品ID
     * @return int total_found 关联商品总数量
     * @return array promotionInfo 优惠券规则信息
     * @return int promotionInfo.coupon_id 优惠券ID
     * @return int promotionInfo.shop_id 店铺ID
     */
    public function modules($params)
    {
        $objMdlWidgetsInstance = app::get('sysapp')->model('widgets_instance');
        $modules = $objMdlWidgetsInstance->getList('tmpl,widget,order_sort,params', [ 'tmpl'=>$params['tmpl'] ], 0, -1, ' order_sort ASC ' );
        return $modules;
    }

}

