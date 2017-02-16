<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return array (
    'columns' =>
    array (
        'id' =>
        array (
            'type' => 'number',
            'required' => true,
            'autoincrement' => true,
            'comment' => app::get('system')->_('ID'),
        ),
        'file_path'=>array(
            'label'=>app::get('system')->_('文件路径'),
            'type' => 'string',
            'length' => 255,
        ),
        'storage'=>array(
            'label'=>app::get('system')->_('存储引擎'),
            'type' => 'string',
            'length' => 50,
            'default' => 'filesystem',
            'required' => true,
            'in_list'=>true,
            'width'=>100,
            'default_in_list'=>true,
        ),
        'file_name'=>array(
            'label'=>app::get('system')->_('文件名称'),
            'type' => 'string',
            'searchtype' => 'nequal', // 简单搜索
            'filtertype' => 'normal', // 高级搜索
            'filterdefault' => 'true',
            'in_list'=>true,
            'length' => 200,
            'required' => false,
            'width'=>100,
            'default_in_list'=>true,
        ),
        'url'=>array(
            'label'=>app::get('system')->_('文件地址'),
            'type' => 'string',
            'searchtype' => 'has', // 简单搜索
            'filtertype' => 'normal', // 高级搜索
            'filterdefault' => 'true',
            'required' => true,
            'width'=>300,
            'in_list'=>false,
        ),
        'ident'=>array(
            'type' => 'string',
            'length' => 200,
            'comment' => app::get('system')->_('唯一标识'),
        ),
        'size' => array(
            'type' => 'number',
            'comment'=>app::get('system')->_('文件大小'),
        ),
        'last_modified' => array (
            'label'=>app::get('system')->_('更新时间'),
            'type' => 'last_modify',
            'width'=>180,
            'required' => true,
            'default' => 0,
            'editable' => false,
            'in_list'=>true,
            'default_in_list'=>true,
            'filtertype' => 'yes'
        ),
        'disabled' => array(
            'type' => 'bool',
            'default' => 0,
            'required' => true,
            'comment' => app::get('sysitem')->_('disabled'),
        ),
    ),
    'primary' => 'id',
    'index' => array(
        'ind_url' => [
            'columns' => ['url'],
            'prefix' => 'unique',
         ],
    ),
    'comment' => app::get('system')->_('sotrager文件存储表'),
);

