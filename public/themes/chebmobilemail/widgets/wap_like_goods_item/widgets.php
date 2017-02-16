<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/*基础配置项*/
$setting['author']='gongjiapeng@shopex.cn';
$setting['version']='v1.0';
$setting['name']='首页猜你喜欢';
$setting['order']=0;
$setting['stime']='2016-01';
$setting['catalog']='新建挂件';
$setting['description'] = '随机展示商品';
$setting['userinfo'] = '';
$setting['usual']    = '1';
$setting['tag']    = 'auto';
$setting['template'] = array(
                            'default.html'=>app::get('topwap')->_('默认')
                        );

$setting['limit']    = '20';
?>