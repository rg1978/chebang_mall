<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$currency = kernel::single('ectools_data_currency')->getCurrency('all');
$setting = array(
'system.money.decimals'=>array('type'=>SET_T_ENUM,'default'=>2,'desc'=>app::get('ectools')->_('金额精度保留位数'),'options'=>array(0=>app::get('ectools')->_('无小数位'),1=>app::get('ectools')->_('1位小数'),2=>app::get('ectools')->_('2位小数'),3=>app::get('ectools')->_('3位小数')),'helpinfo'=>'<span class=\'notice-inline\'>'.app::get('ectools')->_('注意：如果原来设置成2位小数，现在改成1位小数，可能导致页面显示不正常！如：原来0.01，改成1位小数则变成0.0了。').'</span>'),
'system.money.operation.carryset'=>array('type'=>SET_T_ENUM,'default'=>0,'desc'=>app::get('ectools')->_('金额精度取整方式'),'options'=>array(0=>app::get('ectools')->_('四舍五入'),1=>app::get('ectools')->_('向上取整'),2=>app::get('ectools')->_('向下取整'))),
'system.currency.default'=>array('type'=>SET_T_ENUM,'default'=>'CNY','desc'=>app::get('ectools')->_('商城交易货币符号'),'options'=>$currency),//没有税率
'site.paycenter.pay_succ'=>array('type'=>SET_T_TXT,'default'=>'<a href="'.kernel::base_url(1).'/index.php" type="url" title="返回首页">返回首页</a><br/>（此为默认内容，具体内容可以在后台“页面管理-提示信息管理”中修改）','desc'=>app::get('ectools')->_('支付成功提示自定义信息')),
'site.paycenter.pay_failure'=>array('type'=>SET_T_TXT,'default'=>'<a href="'.kernel::base_url(1).'/index.php" type="url" title="返回首页">返回首页</a><br/>
（此为默认内容，具体内容可以在后台“页面管理-提示信息管理”中修改）','desc'=>app::get('ectools')->_('支付失败提示自定义信息')),
);
