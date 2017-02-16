<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


$setting = array(
    'site.name'=>array('type'=>SET_T_STR,'vtype'=>'maxLength','default'=>'ONex-B2B2C商城','desc'=>'商城名称','javascript'=>'validatorMap.set("maxLength",["最大长度32个字",function(el,v){return v.length < 33;}]);'),
    'site.logo'=>array('type'=>SET_T_IMAGE,'default'=>'http://images.bbc.shopex123.com/images/e4/64/42/37eff0aeba30e897184f510c248deebd79cff488.png','desc'=>'商城Logo','backend'=>'public','extends_attr'=>array('width'=>200,'height'=>95)),
    'site.loginlogo'=>array('type'=>SET_T_IMAGE,'default'=>'http://images.bbc.shopex123.com/images/92/ea/db/c305b27c2f5c305ec0d37fbb0782c5b0c0e4eeb9.jpg','desc'=>'登录注册页左侧大图','backend'=>'public','extends_attr'=>array('width'=>200,'height'=>95),'helpinfo'=>'<span class=\'notice-inline\'>图片标准宽度为600*600</span>'),
    'page.default_title'=>array('type'=>SET_T_STR, 'default'=>'', 'desc'=>app::get('site')->_('网页默认标题')),
    'page.default_keywords'=>array('type'=>SET_T_STR, 'default'=>'', 'desc'=>app::get('site')->_('网页默认关键字')),
    'page.default_description'=>array('type'=>SET_T_TXT, 'default'=>'', 'desc'=>app::get('site')->_('网页默认简介')),
    'system.foot_edit' => array('type'=>SET_T_HTML, 'desc'=>app::get('site')->_('网页底部信息'), 'default'=>'<div class="theme-footer">
    <p>'.app::get('site')->_('有任何购物问题请联系我们在线客服 | 电话：800-800-8000 | 工作时间：周一至周五 8:00－18:00').' </p>
    <p>© 2013 All rights reserved.</p>
    </div>'),
    'system.site_icp'=>array('type'=>SET_T_STR, 'default'=>'', 'desc'=>app::get('site')->_('备案号')),

    'site.appId'=>array('type'=>SET_T_STR,'vtype'=>'maxLength','default'=>'','desc'=>'appId','javascript'=>'validatorMap.set("maxLength",["最大长度32个字",function(el,v){return v.length < 33;}]);'),
    'site.appSecret'=>array('type'=>SET_T_STR,'vtype'=>'maxLength','default'=>'','desc'=>'appSecret','javascript'=>'validatorMap.set("maxLength",["最大长度50个字",function(el,v){return v.length < 51;}]);'),

    'site.subdomain_enabled'=>array('type'=>SET_T_BOOL, 'default'=>0, 'desc'=>app::get('site')->_('开启二级域名'),'helpinfo'=>'<span class=\'notice-inline\'>开启二级域名需要web服务器配置域名泛解析</span>'),
    'site.subdomain_limits'=>array('type'=>SET_T_INT, 'default'=>3, 'desc'=>app::get('site')->_('二级域名修改次数'),'helpinfo'=>'<span class=\'notice-inline\'>商户最多可以修改几次域名</span>'),
    'site.subdomain_basic'=>array('type'=>SET_T_STR, 'default'=>'', 'desc'=>app::get('site')->_('二级域名尾部'),'helpinfo'=>'<span class=\'notice-inline\'>如您的域名是www.example.com，这里就填写example.com</span>'),
);
