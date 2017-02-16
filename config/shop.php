<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 * 商家管理中心菜单定义
 */

return array(
    /*
    |--------------------------------------------------------------------------
    | 商家管理中心之首页
    |--------------------------------------------------------------------------
     */
    'index' => array(
        'label' => '首页',
        'display' => true,
        'shopIndex' => true,
        'action' => 'topshop_ctl_index@index',
        'icon' => 'glyphicon glyphicon-home',
        'menu' => array(
            array(
                'label'=>'首页',
                'display'=>false,
                'as'=>'topshop.index',
                'action'=>'topshop_ctl_index@index',
                'url'=>'/',
                'method'=>'get'
            ),
            array(
                'label'=>'浏览器检测',
                'display'=>false,
                'as'=>'topshop.browserTip',
                'action'=>'topshop_ctl_index@browserTip',
                'url'=>'browserTip.html',
                'method'=>'get'
            ),
        )
    ),

    /*
    |--------------------------------------------------------------------------
    | 商家管理中心之交易管理
    |--------------------------------------------------------------------------
     */
    'trade' => array(
        'label' => '交易',
        'display' => true,
        'action' => 'topshop_ctl_trade_list@index',
        'icon' => 'glyphicon glyphicon-stats',
        'menu' => array(
            array('label'=>'订单管理','display'=>true,'as'=>'topshop.trade.index','action'=>'topshop_ctl_trade_list@index','url'=>'list.html','method'=>'get'),
            array('label'=>'订单搜索','display'=>false,'as'=>'topshop.trade.search','action'=>'topshop_ctl_trade_list@search','url'=>'trade/search.html','method'=>['get','post']),
            array('label'=>'订单详情','display'=>false,'as'=>'topshop.trade.detail','action'=>'topshop_ctl_trade_detail@index','url'=>'detail.html','method'=>'get'),
            array('label'=>'订单物流','display'=>false,'as'=>'topshop.trade.detail.logi','action'=>'topshop_ctl_trade_detail@ajaxGetTrack','url'=>'detail.html','method'=>'post'),
            array('label'=>'添加订单备注','display'=>false,'as'=>'topshop.trade.detail.memo','action'=>'topshop_ctl_trade_detail@setTradeMemo','url'=>'setMemo.html','method'=>'post','middleware'=>['topshop_middleware_developerMode']),
            array('label'=>'修改订单价格页面','display'=>false,'as'=>'topshop.trade.modifyPrice','action'=>'topshop_ctl_trade_list@modifyPrice','url'=>'modifyprice.html','method'=>'get'),
            array('label'=>'保存修改订单价格','display'=>false,'as'=>'topshop.trade.modifyPrice.post','action'=>'topshop_ctl_trade_list@updatePrice','url'=>'updateprice.html','method'=>'post'),
            array('label'=>'订单发货','display'=>false,'as'=>'topshop.trade.delivery','action'=>'topshop_ctl_trade_flow@goDelivery','url'=>'delivery.html','method'=>'get'),

            //订单货到付款时订单完成操作
            array('label'=>'ajax请求订单完成页面','display'=>false,'as'=>'topshop.trade.finish','action'=>'topshop_ctl_trade_list@ajaxFinishTrade','url'=>'ajaxfinish.html','method'=>'get'),
            array('label'=>'订单收钱并收货','display'=>false,'as'=>'topshop.trade.postfinish','action'=>'topshop_ctl_trade_list@finishTrade','url'=>'finish.html','method'=>'post'),

            //订单取消列表
            //ajax 请求订单信息以取消
            array('label'=>'ajax请求订单取消页面','display'=>false,'as'=>'topshop.trade.close','action'=>'topshop_ctl_trade_list@ajaxCloseTrade','url'=>'ajaxclose.html','method'=>'get','middleware'=>['topshop_middleware_developerMode']),
            array('label'=>'ajax请求订单拒收页面','display'=>false,'as'=>'topshop.trade.rejection','action'=>'topshop_ctl_trade_list@ajaxCloseRejection','url'=>'ajaxrejection.html','method'=>'get'),
            array('label'=>'ajax请求发送自提提货码页面','display'=>false,'as'=>'topshop.trade.ajaxSendDeliverySms','action'=>'topshop_ctl_trade_list@ajaxSendDeliverySms','url'=>'ajaxSendDeliverySms.html','method'=>'get'),
            array('label'=>'ajax请求验证自提提货码页面','display'=>false,'as'=>'topshop.trade.ajaxCheckDeliveryVcode','action'=>'topshop_ctl_trade_list@ajaxCheckDeliveryVcode','url'=>'ajaxCheckDeliveryVcode.html','method'=>'get'),
            array('label'=>'发送自提提货码页面','display'=>false,'as'=>'topshop.trade.sendDeliverySms','action'=>'topshop_ctl_trade_list@sendDeliverySms','url'=>'sendDeliverySms.html','method'=>'post'),
            array('label'=>'验证自提提货码页面','display'=>false,'as'=>'topshop.trade.checkDeliveryVcode','action'=>'topshop_ctl_trade_list@checkDeliveryVcode','url'=>'checkDeliveryVcode.html','method'=>'post'),
            array('label'=>'订单取消','display'=>false,'as'=>'topshop.trade.postclose','action'=>'topshop_ctl_trade_list@closeTrade','url'=>'close.html','method'=>'post'),
            array('label'=>'订单取消管理','display'=>true,'as'=>'topshop.trade.cancel.index','action'=>'topshop_ctl_trade_cancel@index','url'=>'cancel/list.html','method'=>'get'),
            array('label'=>'订单取消详情','display'=>false,'as'=>'topshop.trade.cancel.detail','action'=>'topshop_ctl_trade_cancel@detail','url'=>'cancel/detail.html','method'=>'get'),
            array('label'=>'订单取消搜索','display'=>false,'as'=>'topshop.trade.cancel.search','action'=>'topshop_ctl_trade_cancel@ajaxSearch','url'=>'trade/cancel/search.html','method'=>['get','post']),
            array('label'=>'审核取消订单','display'=>false,'as'=>'topshop.trade.cancel.check','action'=>'topshop_ctl_trade_cancel@shopCheckCancel','url'=>'trade/cancel/check.html','method'=>'post','middleware'=>['topshop_middleware_developerMode']),

            //店铺模板配置
            array('label'=>'快递模板配置','display'=>true,'as'=>'topshop.dlytmpl.index','action'=>'topshop_ctl_shop_dlytmpl@index','url'=>'wuliu/logis/templates.html','method'=>'get'),
            array('label'=>'快递模板配置编辑','display'=>false,'as'=>'topshop.dlytmpl.edit','action'=>'topshop_ctl_shop_dlytmpl@editView','url'=>'wuliu/logis/templates/create.html','method'=>'get'),
            array('label'=>'快递运费模板保存','display'=>false,'as'=>'topshop.dlytmpl.save','action'=>'topshop_ctl_shop_dlytmpl@savetmpl','url'=>'wuliu/logis/templates.html','method'=>'post'),
            array('label'=>'快递运费模板删除','display'=>false,'as'=>'topshop.dlytmpl.delete','action'=>'topshop_ctl_shop_dlytmpl@remove','url'=>'wuliu/logis/remove.html','method'=>'post'),
            array('label'=>'判断快递运费模板名称是否存在','display'=>false,'as'=>'topshop.dlytmpl.isExists','action'=>'topshop_ctl_shop_dlytmpl@isExists','url'=>'wuliu/logis/isExists.html','method'=>'post'),
            array('label'=>'物流公司','display'=>true,'as'=>'topshop.dlycorp.index','action'=>'topshop_ctl_shop_dlycorp@index','url'=>'wuliu/logis/dlycorp.html','method'=>'get'),
            array('label'=>'物流公司签约','display'=>false,'as'=>'topshop.dlycorp.save','action'=>'topshop_ctl_shop_dlycorp@signDlycorp','url'=>'wuliu/logis/savecorp.html','method'=>'post'),
            array('label'=>'物流公司解约','display'=>false,'as'=>'topshop.dlycorp.cancel','action'=>'topshop_ctl_shop_dlycorp@cancelDlycorp','url'=>'wuliu/logis/cancelcorp.html','method'=>'post'),
        ),
    ),

    /*
    |--------------------------------------------------------------------------
    | 商家管理中心之商家商品管理
    |--------------------------------------------------------------------------
     */
    'item' => array(
        'label' => '商品',
        'display' => true,
        'action'=> 'topshop_ctl_item@itemList',
        'icon' => 'glyphicon glyphicon-edit',
        'menu' => array(
            array('label'=>'商品列表','display'=>true,'as'=>'topshop.item.list','action'=>'topshop_ctl_item@itemList','url'=>'item/itemList.html','method'=>'get'),
            array('label'=>'商品搜索','display'=>false,'as'=>'topshop.item.search','action'=>'topshop_ctl_item@searchItem','url'=>'item/search.html','method'=>['get','post']),
            array('label'=>'发布商品','display'=>true,'as'=>'topshop.item.add','action'=>'topshop_ctl_item@add','url'=>'item/add.html','method'=>'get'),
            array('label'=>'编辑商品','display'=>false,'as'=>'topshop.item.edit','action'=>'topshop_ctl_item@edit','url'=>'item/edit.html','method'=>'get'),
            array('label'=>'商品库存报警','display'=>true,'as'=>'topshop.storepolice.add','action'=>'topshop_ctl_item@storePolice','url'=>'item/storepolice.html','method'=>'get'),
            array('label'=>'保存商品库存报警','display'=>false,'as'=>'topshop.storepolice.save','action'=>'topshop_ctl_item@saveStorePolice','url'=>'item/savestorepolice.html','method'=>'post'),
            array('label'=>'设置商品状态','display'=>false,'as'=>'topshop.item.setStatus','action'=>'topshop_ctl_item@setItemStatus','url'=>'item/setItemStatus.html','method'=>'post'),
            array('label'=>'删除商品','display'=>false,'as'=>'topshop.item.delete','action'=>'topshop_ctl_item@deleteItem','url'=>'item/deleteItem.html','method'=>'post'),
            array('label'=>'创建商品','display'=>false,'as'=>'topshop.item.create','action'=>'topshop_ctl_item@storeItem','url'=>'item/storeItem.html','method'=>'post'),

            array('label'=>'店铺分类','display'=>true,'as'=>'topshop.item.cat.index','action'=>'topshop_ctl_item_cat@index','url'=>'categories.html','method'=>'get'),
            array('label'=>'店铺分类保存','display'=>false,'as'=>'topshop.item.cat.store','action'=>'topshop_ctl_item_cat@storeCat','url'=>'categories.html','method'=>'post'),
            array('label'=>'店铺分类删除','display'=>false,'as'=>'topshop.item.cat.delete','action'=>'topshop_ctl_item_cat@removeCat','url'=>'categories/remove.html','method'=>'post'),
            array('label'=>'获取店铺支持品牌','display'=>false,'as'=>'topshop.item.brand','action'=>'topshop_ctl_item@ajaxGetBrand','url'=>'categories/getbrand.html','method'=>'post'),
            array('label'=>'获取店铺的运费模板','display'=>false,'as'=>'topshop.item.dlytmpls','action'=>'topshop_ctl_item@ajaxGetDlytmpls','url'=>'getdlytmpls.html','method'=>'get'),
            array('label'=>'更新商品的运费模板','display'=>false,'as'=>'topshop.item.update.dlytmpls','action'=>'topshop_ctl_item@updateItemDlytmpl','url'=>'updatedlytmpls.html','method'=>'post'),

            //图片管理
            array('label'=>'图片管理','display'=>true,'as'=>'topshop.image.index','action'=>'topshop_ctl_shop_image@index','url'=>'image.html','method'=>'get'),
            array('label'=>'根据条件搜索图片,tab切换','as'=>'topshop.image.search','display'=>false,'action'=>'topshop_ctl_shop_image@search','url'=>'image/search.html','method'=>'post'),
            array('label'=>'删除图片','display'=>false,'as'=>'topshop.image.delete','action'=>'topshop_ctl_shop_image@delImgLink','url'=>'image/delimglink.html','method'=>'post'),
            array('label'=>'修改图片名称','display'=>false,'as'=>'topshop.image.upname','action'=>'topshop_ctl_shop_image@upImgName','url'=>'image/upimgname.html','method'=>'post'),
            array('label'=>'商家使用图片加载modal','display'=>false,'as'=>'topshop.image.loadModal','action'=>'topshop_ctl_shop_image@loadImageModal','url'=>'image/loadimagemodal.html','method'=>'get'),
            array('label'=>'加载图片移动文件夹弹出框','display'=>false,'as'=>'topshop.image.move.cat.loadModal','action'=>'topshop_ctl_shop_image@loadImageMoveCatModal','url'=>'image/loadImageMoveCatModal.html','method'=>'post'),
            array('label'=>'图片移动文件夹','display'=>false,'as'=>'topshop.image.move.cat','action'=>'topshop_ctl_shop_image@moveImageCat','url'=>'image/loadImageMoveCat.html','method'=>'post'),

            array('label'=>'加载文件夹管理弹出框','display'=>false,'as'=>'topshop.image.cat.loadImgCatModal','action'=>'topshop_ctl_shop_image@loadImgCatModal','url'=>'image/loadImgCatModal.html','method'=>'post'),
            array('label'=>'创建图片文件夹','display'=>false,'as'=>'topshop.image.add.cat','action'=>'topshop_ctl_shop_image@addImgCat','url'=>'image/loadImageCreateCat.html','method'=>'post'),
            array('label'=>'删除图片文件夹','display'=>false,'as'=>'topshop.image.del.cat','action'=>'topshop_ctl_shop_image@delImgCat','url'=>'image/loadImageDelCat.html','method'=>'post'),
            array('label'=>'编辑图片文件夹','display'=>false,'as'=>'topshop.image.update.cat','action'=>'topshop_ctl_shop_image@editImgCat','url'=>'image/loadImageEditCat.html','method'=>'post'),
        ),
    ),

    /*
    |--------------------------------------------------------------------------
    | 商家管理中心之营销管理
    |--------------------------------------------------------------------------
     */
    'promotion' => array(
        'label' => '营销',
        'display' => true,
        'action' => 'topshop_ctl_promotion_fullminus@list_fullminus',
        'icon' => 'glyphicon glyphicon-bookmark',
        'menu' => array(
            //满减促销
            array('label'=>'满减管理','display'=>true,'as'=>'topshop.fullminus.list','action'=>'topshop_ctl_promotion_fullminus@list_fullminus','url'=>'list_fullminus.html','method'=>'get'),
            array('label'=>'添加/编辑满减','display'=>false,'as'=>'topshop.fullminus.edit','action'=>'topshop_ctl_promotion_fullminus@edit_fullminus','url'=>'edit_fullminus.html','method'=>'get'),
            array('label' =>'查看满减','display'=>false,'as'=>'topshop.fullminus.show','action'=>'topshop_ctl_promotion_fullminus@show_fullminus','url'=>'show_fullminus.html','method'=>'get'),
            array('label'=>'保存满减','display'=>false,'as'=>'topshop.fullminus.save','action'=>'topshop_ctl_promotion_fullminus@save_fullminus','url'=>'save_fullminus.html','method'=>'post'),
            array('label'=>'删除满减','display'=>false,'as'=>'topshop.fullminus.delete','action'=>'topshop_ctl_promotion_fullminus@delete_fullminus','url'=>'delete_fullminus.html','method'=>'post'),
            array('label'=>'取消满减活动','display'=>false,'as'=>'topshop.fullminus.cancel','action'=>'topshop_ctl_promotion_fullminus@cancel_fullminus','url'=>'cancel_fullminus.html','method'=>'post'),
            array('label'=>'提交满减促销审核','display'=>false,'as'=>'topshop.fullminus.submitApprove','action'=>'topshop_ctl_promotion_fullminus@submit_approve','url'=>'submit_fullminus.html','method'=>'post'),


            //满折促销
            array('label'=>'满折管理','display'=>true,'as'=>'topshop.fulldiscount.list','action'=>'topshop_ctl_promotion_fulldiscount@list_fulldiscount','url'=>'list_fulldiscount.html','method'=>'get'),
            array('label'=>'添加/编辑满折','display'=>false,'as'=>'topshop.fulldiscount.edit','action'=>'topshop_ctl_promotion_fulldiscount@edit_fulldiscount','url'=>'edit_fulldiscount.html','method'=>'get'),
            array('label'=>'查看满折','display'=>false,'as'=>'topshop.fulldiscount.show','action'=>'topshop_ctl_promotion_fulldiscount@show_fulldiscount','url'=>'show_fulldiscount.html','method'=>'get'),
            array('label'=>'保存满折','display'=>false,'as'=>'topshop.fulldiscount.save','action'=>'topshop_ctl_promotion_fulldiscount@save_fulldiscount','url'=>'save_fulldiscount.html','method'=>'post'),
            array('label'=>'删除满折','display'=>false,'as'=>'topshop.fulldiscount.delete','action'=>'topshop_ctl_promotion_fulldiscount@delete_fulldiscount','url'=>'delete_fulldiscount.html','method'=>'post'),
            array('label'=>'取消满折活动','display'=>false,'as'=>'topshop.fulldiscount.cancel','action'=>'topshop_ctl_promotion_fulldiscount@cancel_fulldiscount','url'=>'cancel_fulldiscount.html','method'=>'post'),
            array('label'=>'提交满折促销审核','display'=>false,'as'=>'topshop.fulldiscount.submitApprove','action'=>'topshop_ctl_promotion_fulldiscount@submit_approve','url'=>'submit_fulldiscount.html','method'=>'post'),

            // 优惠券促销
            array('label'=>'优惠券管理','display'=>true,'as'=>'topshop.coupon.list','action'=>'topshop_ctl_promotion_coupon@list_coupon','url'=>'list_coupon.html','method'=>'get'),
            array('label'=>'添加/编辑优惠券','display'=>false,'as'=>'topshop.coupon.edit','action'=>'topshop_ctl_promotion_coupon@edit_coupon','url'=>'edit_coupon.html','method'=>'get'),
            array('label'=>'查看优惠券','display'=>false,'as'=>'topshop.coupon.show','action'=>'topshop_ctl_promotion_coupon@show_coupon','url'=>'show_coupon.html','method'=>'get'),
            array('label'=>'保存优惠券','display'=>false,'as'=>'topshop.coupon.save','action'=>'topshop_ctl_promotion_coupon@save_coupon','url'=>'save_coupon.html','method'=>'post'),
            array('label'=>'删除优惠券','display'=>false,'as'=>'topshop.coupon.delete','action'=>'topshop_ctl_promotion_coupon@delete_coupon','url'=>'delete_coupon.html','method'=>'post'),
            array('label'=>'取消优惠券','display'=>false,'as'=>'topshop.coupon.cancel','action'=>'topshop_ctl_promotion_coupon@cancel_coupon','url'=>'cancel_coupon.html','method'=>'post'),
            array('label'=>'提交优惠券审核','display'=>false,'as'=>'topshop.coupon.submitApprove','action'=>'topshop_ctl_promotion_coupon@submit_approve','url'=>'submit_coupon.html','method'=>'post'),
            // 免邮促销
            array('label'=>'免邮管理','display'=>true,'as'=>'topshop.freepostage.list','action'=>'topshop_ctl_promotion_freepostage@list_freepostage','url'=>'list_freepostage.html','method'=>'get'),
            array('label'=>'添加/编辑免邮','display'=>false,'as'=>'topshop.freepostage.edit','action'=>'topshop_ctl_promotion_freepostage@edit_freepostage','url'=>'edit_freepostage.html','method'=>'get'),
            array('label'=>'查看免邮','display'=>false,'as'=>'topshop.freepostage.show','action'=>'topshop_ctl_promotion_freepostage@show_freepostage','url'=>'show_freepostage.html','method'=>'get'),
            array('label'=>'保存免邮','display'=>false,'as'=>'topshop.freepostage.save','action'=>'topshop_ctl_promotion_freepostage@save_freepostage','url'=>'save_freepostage.html','method'=>'post'),
            array('label'=>'删除免邮','display'=>false,'as'=>'topshop.freepostage.delete','action'=>'topshop_ctl_promotion_freepostage@delete_freepostage','url'=>'delete_freepostage.html','method'=>'post'),
            // X件Y折促销
            array('label'=>'X件Y折管理','display'=>true,'as'=>'topshop.xydiscount.list','action'=>'topshop_ctl_promotion_xydiscount@list_xydiscount','url'=>'list_xydiscount.html','method'=>'get'),
            array('label'=>'添加/编辑X件Y折','display'=>false,'as'=>'topshop.xydiscount.edit','action'=>'topshop_ctl_promotion_xydiscount@edit_xydiscount','url'=>'edit_xydiscount.html','method'=>'get'),
            array('label'=>'查看X件Y折','display'=>false,'as'=>'topshop.xydiscount.show','action'=>'topshop_ctl_promotion_xydiscount@show_xydiscount','url'=>'show_xydiscount.html','method'=>'get'),
            array('label'=>'保存X件Y折','display'=>false,'as'=>'topshop.xydiscount.save','action'=>'topshop_ctl_promotion_xydiscount@save_xydiscount','url'=>'save_xydiscount.html','method'=>'post'),
            array('label'=>'删除X件Y折','display'=>false,'as'=>'topshop.xydiscount.delete','action'=>'topshop_ctl_promotion_xydiscount@delete_xydiscount','url'=>'delete_xydiscount.html','method'=>'post'),
            array('label'=>'取消X件Y折','display'=>false,'as'=>'topshop.xydiscount.cancel','action'=>'topshop_ctl_promotion_xydiscount@cancel_xydiscount','url'=>'cancel_xydiscount.html','method'=>'post'),
            array('label'=>'提交X件Y折促销审核','display'=>false,'as'=>'topshop.xydiscount.submitApprove','action'=>'topshop_ctl_promotion_xydiscount@submit_approve','url'=>'submit_xydiscount.html','method'=>'post'),
            // 活动报名
            array('label'=>'活动报名','display'=>true,'as'=>'topshop.activity.registeredlist','action'=>'topshop_ctl_promotion_activity@registered_activity','url'=>'registered.html','method'=>'get'),
            array('label'=>'活动列表','display'=>false,'as'=>'topshop.activity.activitylist','action'=>'topshop_ctl_promotion_activity@activity_list','url'=>'activitylist.html','method'=>'get'),
            array('label'=>'历史报名','display'=>false,'as'=>'topshop.activity.historyregisteredlist','action'=>'topshop_ctl_promotion_activity@historyregistered_activity','url'=>'historyregistered.html','method'=>'get'),
            array('label'=>'历史报名详情','display'=>false,'as'=>'topshop.activity.historyregistereddetial','action'=>'topshop_ctl_promotion_activity@historyregistered_detail','url'=>'historyregistered_detail.html','method'=>'get'),
            array('label'=>'添加/编辑活动申请','display'=>false,'as'=>'topshop.activity.edit','action'=>'topshop_ctl_promotion_activity@canregistered_apply','url'=>'edit_activity.html','method'=>'get'),
            array('label'=>'已报名不活动详情','display'=>false,'as'=>'topshop.activity.canregistered.detail','action'=>'topshop_ctl_promotion_activity@canregistered_detail','url'=>'canregistered_detail.html','method'=>'get'),
            array('label'=>'保存申请活动','display'=>false,'as'=>'topshop.activity.save','action'=>'topshop_ctl_promotion_activity@canregistered_apply_save','url'=>'save_activity.html','method'=>'post'),
            array('label'=>'活动列表页活动详情','display'=>false,'as'=>'topshop.activity.noregistered.detail','action'=>'topshop_ctl_promotion_activity@noregistered_detail','url'=>'noregistered_detail.html','method'=>'get'),
            //组合促销
            array('label'=>'组合促销管理','display'=>true,'as'=>'topshop.package.list','action'=>'topshop_ctl_promotion_package@list_package','url'=>'list_package.html','method'=>'get'),
            array('label'=>'添加/编辑组合促销','display'=>false,'as'=>'topshop.package.edit','action'=>'topshop_ctl_promotion_package@edit_package','url'=>'edit_package.html','method'=>'get'),
            array('label'=>'查看组合促销','display'=>false,'as'=>'topshop.package.show','action'=>'topshop_ctl_promotion_package@show_package','url'=>'show_package.html','method'=>'get'),
            array('label'=>'保存组合促销','display'=>false,'as'=>'topshop.package.save','action'=>'topshop_ctl_promotion_package@save_package','url'=>'save_package.html','method'=>'post'),
            array('label'=>'删除组合促销','display'=>false,'as'=>'topshop.package.delete','action'=>'topshop_ctl_promotion_package@delete_package','url'=>'delete_package.html','method'=>'post'),
            array('label'=>'取消组合促销','display'=>false,'as'=>'topshop.package.cancel','action'=>'topshop_ctl_promotion_package@cancel_package','url'=>'cancel_package.html','method'=>'post'),
            array('label'=>'提交组合促销审核','display'=>false,'as'=>'topshop.package.submitApprove','action'=>'topshop_ctl_promotion_package@submit_approve','url'=>'submit_package.html','method'=>'post'),
            //赠品促销
            array('label'=>'赠品促销管理','display'=>true,'as'=>'topshop.gift.list','action'=>'topshop_ctl_promotion_gift@list_gift','url'=>'list_gift.html','method'=>'get'),
            array('label'=>'添加/编辑赠品促销','display'=>false,'as'=>'topshop.gift.edit','action'=>'topshop_ctl_promotion_gift@edit_gift','url'=>'edit_gift.html','method'=>'get'),
            array('label'=>'查看赠品促销','display'=>false,'as'=>'topshop.gift.show','action'=>'topshop_ctl_promotion_gift@show_gift','url'=>'show_gift.html','method'=>'get'),
            array('label'=>'保存赠品促销','display'=>false,'as'=>'topshop.gift.save','action'=>'topshop_ctl_promotion_gift@save_gift','url'=>'save_gift.html','method'=>'post'),
            array('label'=>'删除赠品促销','display'=>false,'as'=>'topshop.gift.delete','action'=>'topshop_ctl_promotion_gift@delete_gift','url'=>'delete_gift.html','method'=>'post'),
            array('label'=>'取消赠品促销','display'=>false,'as'=>'topshop.gift.cancel','action'=>'topshop_ctl_promotion_gift@cancel_gift','url'=>'cancel_gift.html','method'=>'post'),
            array('label'=>'提交赠品促销审核','display'=>false,'as'=>'topshop.gift.submitApprove','action'=>'topshop_ctl_promotion_gift@submit_approve','url'=>'submit_gift.html','method'=>'post'),
        )
    ),

    /*
    |--------------------------------------------------------------------------
    | 商家管理中心之店铺管理
    |--------------------------------------------------------------------------
     */
    'shop' => array(
        'label' => '店铺',
        'display' => true,
        'action' => 'topshop_ctl_shop_setting@index',
        'icon' => 'glyphicon glyphicon-cog',
        'menu' => array(
            //店铺配置
            array('label'=>'店铺配置','display'=>true,'as'=>'topshop.shopsetting.index','action'=>'topshop_ctl_shop_setting@index','url'=>'setting.html','method'=>'get'),
            array('label'=>'店铺配置保存','display'=>false,'as'=>'topshop.shopsetting.save','action'=>'topshop_ctl_shop_setting@saveSetting','url'=>'setting/save.html','method'=>'post'),

            array('label'=>'二级域名','display'=>true,'as'=>'topshop.subdomain.index','action'=>'topshop_ctl_shop_subdomain@index','url'=>'subdomain.html','method'=>'get'),
            array('label'=>'二级域名保存','display'=>false,'as'=>'topshop.subdomain.save','action'=>'topshop_ctl_shop_subdomain@saveSubdomain','url'=>'subdomain/save.html','method'=>'post'),

            array('label'=>'商家通知','display'=>true,'as'=>'topshop.shopnotice','action'=>'topshop_ctl_shop_notice@index','url'=>'shop/shopnotice.html','method'=>'get'),
            array('label'=>'商家通知详情','display'=>false,'as'=>'topshop.shopnotice.detail','action'=>'topshop_ctl_shop_notice@noticeInfo','url'=>'shop/shopnoticeinto.html','method'=>'get'),

            //店铺装修
            array('label'=>'店铺装修','display'=>true,'as'=>'topshop.decorate.index','action'=>'topshop_ctl_shop_decorate@index','url'=>'decorate.html','method'=>'get'),
            array('label'=>'店铺装修弹出框','display'=>false,'as'=>'topshop.decorate.dialog','action'=>'topshop_ctl_shop_decorate@dialog','url'=>'decorate/dialog.html','method'=>'get'),
            array('label'=>'店铺装修配置','display'=>false,'as'=>'topshop.decorate.save','action'=>'topshop_ctl_shop_decorate@save','url'=>'decorate/save.html','method'=>'post'),

            //wap端店铺配置
            array('label'=>'wap端店铺装修','display'=>true,'as'=>'topshop.wap.decorate.index','action'=>'topshop_ctl_wap_decorate@index','url'=>'wapdecorate.html','method'=>'get'),
            array('label'=>'wap端店铺装修弹出框','display'=>false,'as'=>'topshop.wap.decorate.dialog','action'=>'topshop_ctl_wap_decorate@dialog','url'=>'wapdecorate/dialogs.html','method'=>'get'),
            array('label'=>'wap端店铺装修顺序保存','display'=>false,'as'=>'topshop.wap.decorate.saveSort','action'=>'topshop_ctl_wap_decorate@saveSort','url'=>'wapdecorate/saveSort.html','method'=>'post'),
            array('label'=>'wap端店铺装修标签配置','display'=>false,'as'=>'topshop.wap.decorate.addTags','action'=>'topshop_ctl_wap_decorate@addTags','url'=>'wapAddTags.html','method'=>'get'),
            array('label'=>'wap店铺装修配置','display'=>false,'as'=>'topshop.wap.decorate.save','action'=>'topshop_ctl_wap_decorate@save','url'=>'wapdecorate/save.html','method'=>'post'),
            array('label'=>'wap店铺装修标签配置删除','display'=>false,'as'=>'topshop.wap.decorate.ajaxWidgetsDel','action'=>'topshop_ctl_wap_decorate@ajaxWidgetsDel','url'=>'wapdecorate/ajaxWidgetsDel.html','method'=>'post'),
            array('label'=>'wap店铺装修标签配置开启','display'=>false,'as'=>'topshop.wap.decorate.openTags','action'=>'topshop_ctl_wap_decorate@openTags','url'=>'wapdecorate/opentags.html','method'=>'post'),
            array('label'=>'wap店铺装修前台商品显示','display'=>false,'as'=>'topshop.wap.decorate.ajaxCheckShowItems','action'=>'topshop_ctl_wap_decorate@ajaxCheckShowItems','url'=>'wapdecorate/ajaxCheckShowItems.html','method'=>'post'),
            array('label'=>'wap店铺装修前台广告商品显示检查','display'=>false,'as'=>'topshop.wap.decorate.checkImageSlider','action'=>'topshop_ctl_wap_decorate@checkImageSlider','url'=>'wapdecorate/checkImageSlider.html','method'=>'post'),

            array('label'=>'商家入驻信息','display'=>true,'as'=>'topshop.shopapply.info','action'=>'topshop_ctl_shop_shopinfo@index','url'=>'shop/shopapplyinfo.html','method'=>'get'),

            //开发者中心
            array('label'=>'开发者中心','display'=>true,'as'=>'topshop.open.developer.center','action'=>'topshop_ctl_open@index','url'=>'developer.html','method'=>'get', 'middleware'=>['topshop_middleware_selfManagement']),
            array('label'=>'开发者中心商家参数配置保存','display'=>false,'as'=>'topshop.open.developer.shop.conf.save','action'=>'topshop_ctl_open@setConf','url'=>'saveDevelopConf.html','method'=>'post', 'middleware'=>['topshop_middleware_selfManagement']),
            array('label'=>'开发者中心商家申请开通','display'=>false,'as'=>'topshop.open.developer.shop.apply','action'=>'topshop_ctl_open@applyForOpen','url'=>'applyDevelop.html','method'=>'post', 'middleware'=>['topshop_middleware_selfManagement']),

            //安全中心
            array('label'=>'安全中心','display'=>true,'as'=>'topshop.auth.safe.index','action'=>'topshop_ctl_auth_index@index','url'=>'authsafe.html','method'=>'get'),
            array('label'=>'验证登录密码','display'=>false,'as'=>'topshop.auth.safe.checkpwd','action'=>'topshop_ctl_auth_index@checkPassword','url'=>'authpwd.html','method'=>'get'),
            array('label'=>'验证登录密码','display'=>false,'as'=>'topshop.auth.safe.docheckpwd','action'=>'topshop_ctl_auth_index@doCheckPassword','url'=>'doauthpwd.html','method'=>'post'),
            array('label'=>'验证信息','display'=>false,'as'=>'topshop.auth.safe.auth','action'=>'topshop_ctl_auth_index@auth','url'=>'authing.html','method'=>'get'),
            array('label'=>'发送验证码','display'=>false,'as'=>'topshop.auth.send.code','action'=>'topshop_ctl_auth_code@send','url'=>'sendcode.html','method'=>'post'),
            array('label'=>'验证认证信息','display'=>false,'as'=>'topshop.auth.check.code','action'=>'topshop_ctl_auth_code@checkAuth','url'=>'checkauth.html','method'=>'post'),
            array('label'=>'修改认证信息','display'=>false,'as'=>'topshop.auth.update.code','action'=>'topshop_ctl_auth_code@updateAuth','url'=>'updateauth.html','method'=>'get'),
            array('label'=>'修改认证信息','display'=>false,'as'=>'topshop.auth.updatecheck.code','action'=>'topshop_ctl_auth_code@updateAuthCheck','url'=>'updatecheck.html','method'=>'post'),
            array('label'=>'验证输入的数据','display'=>false,'as'=>'topshop.auth.isError.code','action'=>'topshop_ctl_auth_code@isErrorInfo','url'=>'auth/iserrorinfo.html','method'=>'get'),

            array('label'=>'申请类目权限','display'=>true,'as'=>'topshop.applycat.list','action'=>'topshop_ctl_shop_applycat@index','url'=>'applycat.html','method'=>'get'),
            array('label'=>'申请类目权限弹框','display'=>false,'as'=>'topshop.applycat.ajax','action'=>'topshop_ctl_shop_applycat@goApplyCat','url'=>'goapplycat.html','method'=>'get'),
            array('label'=>'申请类目权限提交','display'=>false,'as'=>'topshop.applycat.save','action'=>'topshop_ctl_shop_applycat@doApplyCat','url'=>'doapplycat.html','method'=>'post'),
            array('label'=>'申请类目权限删除','display'=>false,'as'=>'topshop.applycat.remove','action'=>'topshop_ctl_shop_applycat@removeApplyCat','url'=>'removeapplycat.html','method'=>'post'),
            array('label'=>'申请类目权限验证','display'=>false,'as'=>'topshop.applycat.get','action'=>'topshop_ctl_shop_applycat@getApplyCat','url'=>'apply-cat.html','method'=>'post'),

            // 店铺文章
            ['label'=>'文章管理','display'=>true,'as'=>'topshop.article.list','action'=>'topshop_ctl_shop_article@index','url'=>'article.html','method'=>'get'],
            ['label'=>'文章分类','display'=>false,'as'=>'topshop.article.nodes.list','action'=>'topshop_ctl_shop_article@nodes','url'=>'article-nodes.html','method'=>'get'],
            ['label'=>'保存分类','display'=>false,'as'=>'topshop.article.nodes.save','action'=>'topshop_ctl_shop_article@saveNode','url'=>'article-save-node.html','method'=>'post'],
            ['label'=>'添加/编辑分类','display'=>false,'as'=>'topshop.article.nodes.edit','action'=>'topshop_ctl_shop_article@editNode','url'=>'article-edit-node.html','method'=>'get'],
            ['label'=>'删除分类','display'=>false,'as'=>'topshop.article.nodes.del','action'=>'topshop_ctl_shop_article@delNode','url'=>'article-del-node.html','method'=>'post'],
            ['label'=>'添加/编辑文章','display'=>false,'as'=>'topshop.article.edit','action'=>'topshop_ctl_shop_article@editArticle','url'=>'article-edit.html','method'=>'get'],
            ['label'=>'保存文章','display'=>false,'as'=>'topshop.article.save','action'=>'topshop_ctl_shop_article@saveArticle','url'=>'article-save.html','method'=>'post'],
            ['label'=>'删除文章','display'=>false,'as'=>'topshop.article.del','action'=>'topshop_ctl_shop_article@delArticle','url'=>'article-del.html','method'=>'post'],
        )
    ),

    /*
    |--------------------------------------------------------------------------
    | 商家管理中心之客户服务
    |--------------------------------------------------------------------------
     */
    'aftersales' => array(
        'label' => '客服',
        'display' => true,
        'action' => 'topshop_ctl_aftersales@index',
        'icon' => 'icon-chatbubbles',
        'menu' => array(
            array('label'=>'退换货管理','display'=>true,'as'=>'topshop.aftersales.list','action'=>'topshop_ctl_aftersales@index','url'=>'aftersales-list.html','method'=>'get'),
            array('label'=>'退换货详情','display'=>false,'as'=>'topshop.aftersales.detail','action'=>'topshop_ctl_aftersales@detail','url'=>'aftersales-detail.html','method'=>'get'),
            array('label'=>'退换货搜索','display'=>false,'as'=>'topshop.aftersales.search','action'=>'topshop_ctl_aftersales@search','url'=>'aftersales-search.html','method'=>'post'),
            array('label'=>'退换货搜索','display'=>false,'as'=>'topshop.aftersales.search','action'=>'topshop_ctl_aftersales@search','url'=>'aftersales-search.html','method'=>'get'),
            array('label'=>'审核退换货申请','display'=>false,'as'=>'topshop.aftersales.verification','action'=>'topshop_ctl_aftersales@verification','url'=>'aftersales-verification.html','method'=>'post','middleware'=>['topshop_middleware_developerMode'] ),
            array('label'=>'换货重新发货','display'=>false,'as'=>'topshop.aftersales.sendConfirm','action'=>'topshop_ctl_aftersales@sendConfirm','url'=>'aftersales-send.html','method'=>'post'),

            //评价管理&DSR管理
            array('label'=>'评价列表','display'=>true,'as'=>'topshop.rate.list','action'=>'topshop_ctl_rate@index','url'=>'rate-list.html','method'=>'get'),
            array('label'=>'评价搜索','display'=>false,'as'=>'topshop.rate.search','action'=>'topshop_ctl_rate@search','url'=>'rate-search.html','method'=>'get'),
            array('label'=>'评价详情','display'=>false,'as'=>'topshop.rate.detail','action'=>'topshop_ctl_rate@detail','url'=>'rate-detail.html','method'=>'get'),
            array('label'=>'评价回复','display'=>false,'as'=>'topshop.rate.reply','action'=>'topshop_ctl_rate@reply','url'=>'rate-reply.html','method'=>'post'),

            array('label'=>'申诉列表','display'=>true,'as'=>'topshop.rate.appeal.list','action'=>'topshop_ctl_rate_appeal@appealList','url'=>'rate-appeal-list.html','method'=>'get'),
            array('label'=>'申诉搜索','display'=>false,'as'=>'topshop.rate.appeal.search','action'=>'topshop_ctl_rate_appeal@search','url'=>'rate-appeal-search.html','method'=>'get'),
            array('label'=>'申诉详情','display'=>false,'as'=>'topshop.rate.appeal.detail','action'=>'topshop_ctl_rate_appeal@appeaInfo','url'=>'rate-appeal-info.html','method'=>'get'),
            array('label'=>'评价申诉','display'=>false,'as'=>'topshop.rate.appeal','action'=>'topshop_ctl_rate_appeal@appeal','url'=>'rate-appeal.html','method'=>'post'),

            array('label'=>'评价概况','display'=>true,'as'=>'topshop.rate.count','action'=>'topshop_ctl_rate_count@index','url'=>'rate-count.html','method'=>'get'),

            //咨询管理
            array('label'=>'咨询列表','display'=>true,'as'=>'topshop.gask.list','action'=>'topshop_ctl_consultation@index','url'=>'gask-list.html','method'=>'get'),
            array('label'=>'咨询回复','display'=>false,'as'=>'topshop.gask.reply','action'=>'topshop_ctl_consultation@doReply','url'=>'gask-reply.html','method'=>'post'),
            array('label'=>'咨询筛选','display'=>false,'as'=>'topshop.gask.screening','action'=>'topshop_ctl_consultation@screening','url'=>'gask-screening.html','method'=>'get'),
            array('label'=>'回复删除','display'=>false,'as'=>'topshop.gask.delete','action'=>'topshop_ctl_consultation@doDelete','url'=>'gask-del.html','method'=>'post'),
            array('label'=>'显示或关闭咨询与回复','display'=>false,'as'=>'topshop.gask.display','action'=>'topshop_ctl_consultation@doDisplay','url'=>'gask-display.html','method'=>'post'),

            //im相关管理-365webcall配置
            array('label'=>'365webcall配置','display'=>true,'as'=>'topshop.im.webcall.index','action'=>'topshop_ctl_im_webcall@index','url'=>'im-webcall.html','method'=>'get'),
            array('label'=>'365webcall配置','display'=>false,'as'=>'topshop.im.webcall.applyPage','action'=>'topshop_ctl_im_webcall@applyPage','url'=>'im-webcall-apply.html','method'=>'get'),
            array('label'=>'365webcall配置','display'=>false,'as'=>'topshop.im.webcall.save','action'=>'topshop_ctl_im_webcall@save','url'=>'im-webcall-save.html','method'=>'post'),
            array('label'=>'365webcall申请','display'=>false,'as'=>'topshop.im.webcall.apply','action'=>'topshop_ctl_im_webcall@apply','url'=>'im-webcall-apply.html','method'=>'post'),

        ),
    ),

    'shopinfo' => array(
        'label' => '结算',
        'display' => true,
        'action' => 'topshop_ctl_shop_shopinfo@index',
        'icon' => 'glyphicon glyphicon-cloud',
        'menu' => array(
            array('label'=>'商家结算汇总','display'=>true,'as'=>'topshop.settlement','action'=>'topshop_ctl_clearing_settlement@index','url'=>'shop/settlement.html','method'=>'get'),
            array('label'=>'商家结算明细','display'=>true,'as'=>'topshop.settlement.detail','action'=>'topshop_ctl_clearing_settlement@detail','url'=>'shop/settlement_detail.html','method'=>'get'),
        )
    ),

    'sysstat' => array(
        'label' => '报表',
        'display' => true,
        'action' => 'topshop_ctl_sysstat_sysstat@index',
        'icon' => 'glyphicon glyphicon-list-alt',
        'menu' => array(
            array('label'=>'商家运营概况','display'=>true,'as'=>'topshop.sysstat','action'=>'topshop_ctl_sysstat_sysstat@index','url'=>'sysstat/sysstat.html','method'=>'get'),
            array('label'=>'交易数据分析','display'=>true,'as'=>'topshop.stattrade','action'=>'topshop_ctl_sysstat_stattrade@index','url'=>'sysstat/stattrade.html','method'=>'get'),
            array('label'=>'业务数据分析','display'=>true,'as'=>'topshop.sysbusiness','action'=>'topshop_ctl_sysstat_sysbusiness@index','url'=>'sysstat/sysbusiness.html','method'=>'get'),
            array('label'=>'商品销售分析','display'=>true,'as'=>'topshop.sysstat.itemtrade.index','action'=>'topshop_ctl_sysstat_itemtrade@index','url'=>'sysstat/itemtrade.html','method'=>'get'),
        )
    ),

    'account' => array(
        'label' => '账号',
        'display' => true,
        'action' => 'topshop_ctl_account_list@index',
        'icon' => 'glyphicon glyphicon-lock',
        'menu' => array(
            array('label'=>'账号管理','display'=>true,'as'=>'topshop.account.list','action'=>'topshop_ctl_account_list@index','url'=>'account/list.html','method'=>'get'),
            array('label'=>'编辑账号','display'=>false,'as'=>'topshop.account.edit','action'=>'topshop_ctl_account_list@edit','url'=>'account/edit.html','method'=>'get'),
            array('label'=>'修改账号密码','display'=>false,'as'=>'topshop.account.modifyPwd','action'=>'topshop_ctl_account_list@modifyPwd','url'=>'account/modifypwd.html','method'=>'post'),
            array('label'=>'保存账号','display'=>false,'as'=>'topshop.account.save','action'=>'topshop_ctl_account_list@save','url'=>'account/add.html','method'=>'post'),
            array('label'=>'删除账号','display'=>false,'as'=>'topshop.account.delete','action'=>'topshop_ctl_account_list@delete','url'=>'account/del.html','method'=>'get'),

            array('label'=>'角色管理','display'=>true,'as'=>'topshop.roles.list','action'=>'topshop_ctl_account_roles@index','url'=>'roles/list.html','method'=>'get'),
            array('label'=>'保存角色保存','display'=>false,'as'=>'topshop.roles.save','action'=>'topshop_ctl_account_roles@save','url'=>'roles/save.html','method'=>'post'),
            array('label'=>'编辑角色页面','display'=>false,'as'=>'topshop.roles.edit','action'=>'topshop_ctl_account_roles@edit','url'=>'roles/edit.html','method'=>'get'),
            array('label'=>'删除角色','display'=>false,'as'=>'topshop.roles.delete','action'=>'topshop_ctl_account_roles@delete','url'=>'roles/del.html','method'=>'get'),

            array('label'=>'操作日志','display'=>true,'as'=>'topshop.oplog.list','action'=>'topshop_ctl_account_log@index','url'=>'account/loglist.html','method'=>'get'),
        )
    ),
);

