<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2014 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysapp_ctl_admin_utils extends desktop_controller {

    public $linktype = [
        'cat' => ['object'=>'cat@syscategory','textcol'=>'cat_name','emptytext'=>'请选择三级分类'],
        'item' => ['object'=>'item@sysitem','textcol'=>'title','emptytext'=>'请选择商品'],
        'article' => ['object'=>'article@syscontent','textcol'=>'title','emptytext'=>'请选择文章'],
        'shop' => ['object'=>'shop@sysshop','textcol'=>'shop_name','emptytext'=>'请选择店铺'],
        'activity' => ['object'=>'activity@syspromotion','textcol'=>'activity_name','emptytext'=>'请选择活动'],
        'promotions' => ['object'=>'promotions@syspromotion','textcol'=>'promotion_name','emptytext'=>'请选择普通促销'],
        'coupon' => ['object'=>'coupon@syspromotion','textcol'=>'coupon_name','emptytext'=>'请选择优惠券'],
    ];

    public function ajax_get_object()
    {
        $params = input::get();

        if( $params['linktype']=='h5' )
        {
            // return '<input type="text" name="' . $params['name'] . '" value="' . $params['value'] . '">';
            return '';
        }

        // $pagedata['name'] = $params['name'];
        // $pagedata['value'] = $params['value'];
        $pagedata['filter'] = http_build_query($params['filter']);
        $pagedata['callback'] = $params['callback'];
        $pagedata['object'] = $params['object'] ? : $this->linktype[$params['linktype']]['object'];
        $pagedata['textcol'] = $params['textcol'] ? : $this->linktype[$params['linktype']]['textcol'];
        $pagedata['emptytext'] = $params['emptytext'] ? : $this->linktype[$params['linktype']]['emptytext'];

        return view::make('sysapp/ui/obj.html', $pagedata);
    }


    public function ajax_get_applink()
    {
        $params = input::get();

        // $pagedata['filter'] = http_build_query($params['filter']);
        $pagedata['name'] = $params['name'];
        $pagedata['value'] = $params['value'];
        $pagedata['linktypename'] = $params['linktypename'];
        $pagedata['linktypevalue'] = $params['linktypevalue'];

        return view::make('sysapp/ui/getapplink.html', $pagedata);
    }

}
