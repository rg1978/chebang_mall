<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


$setting['author']='tylerchao.sh@gmail.com';
$setting['version']='v1.0';
$setting['name']='底部友情链接';
$setting['catalog'] = '文章相关';
$setting['description'] = '底部友情链接的展示';
$setting['usual'] = '0';
$setting['stime'] ='2013-07';
$setting['userinfo'] = '';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                        );
$cur_url = $_SERVER ['HTTP_HOST'].$_SERVER['PHP_SELF'];
$setting['vary'] = $cur_url;
?>
