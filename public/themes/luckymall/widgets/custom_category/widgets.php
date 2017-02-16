<?php

/*基础配置项*/
$setting['author']='shopex';
$setting['version']='v2.0';
$setting['name']='商品主分类-下拉-部分自定义';
$setting['order']=0;
$setting['stime']='2016-01';
$setting['catalog']='商品相关';
$setting['description'] = '支持三级分类展示；支持不弹出状态下展示二级分类；支持关联促销和品牌信息；尺寸可视化编辑；支持左右方向弹出; 经过千个以上分类性能测试';
$setting['userinfo'] = '';
$setting['usual'] = '1';
$setting['tag'] = 'auto';
$setting['template'] = array(
    'default.html'=>app::get('topc')->_('默认')
);
$cur_url = $_SERVER ['HTTP_HOST'].$_SERVER['PHP_SELF'];
$setting['vary'] = $cur_url;
?>
