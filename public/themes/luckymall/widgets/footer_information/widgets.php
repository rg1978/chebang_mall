<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
$setting['author']='tylerchao.sh@shopex.cn';
$setting['name']='首页底部文章';
$setting['version']='v1.0';
$setting['stime']='2013-07';
$setting['catalog']='文章相关';
$setting['usual'] = '0';
$setting['description']='展示模板使用的底部文章';
$setting['userinfo']='';
$setting['tag'] = 'auto';
$setting['template'] = array(
    'default.html'=>'默认'
);
$setting['lv'] = 1;             //深度
$setting['styleart'] = 0;       //文章样式统一
$setting['node_id']  = 1;       //默认节点
$selectmaps = app::get('topc')->rpcCall('syscontent.content.map',['fields'=>'*']);
foreach( $selectmaps as $row )
{
    if( $row['node_depth'] == 1 )
    {
        $selectNode[] = $row;
    }
}

array_unshift($selectNode, array('node_id'=>0, 'step'=>1, 'node_name'=>app::get('topc')->_('---所有---')));

$setting['selectNode'] = $selectNode;
$setting['selectmaps'] = $selectmaps;

//$setting['select_order']['order_type'] = array('modified'=>'最后更新时间');
//$setting['select_order']['order'] = array('asc'=>'升序','desc'=>'降序');
//$setting['showuptime'] = 0; //是否显示文章最后更新时间

?>
