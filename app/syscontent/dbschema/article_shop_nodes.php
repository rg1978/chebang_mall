<?php
/**
 * article_shop_nodes.php 
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
return array (
        'columns' =>
        array (
                'node_id' =>array (
                        'type' => 'number',
                        'required' => true,
                        'comment'=> app::get('syscontent')->_('节点id'),
                        //'pkey' => true,
                        'autoincrement' => true,
                        'width' => 10,
                        'editable' => false,
                        'in_list' => true,
                ),
                'shop_id' =>array (
                        'type' => 'number',
                        'required' => true,
                        'default' => 0,
                        'comment'=> app::get('syscontent')->_('商家ID'),
                        'width' => 10,
                        'editable' => true,
                        'in_list' => true,
                ),
                'parent_id' =>array (
                        'type' => 'number',
                        'required' => true,
                        'default' => 0,
                        'comment'=> app::get('syscontent')->_('父节点'),
                        'width' => 10,
                        'editable' => true,
                        'in_list' => true,
                ),
                'node_name' =>array (
                        'type' => 'string',
                        'required' => true,
                        'default'=>'',
                        'comment'=> app::get('syscontent')->_('节点名称'),
                        'is_title' => true,
                        'editable' => true,
                        'in_list' => true,
                        'default_in_list' => true,
                ),
                'order_sort'=> array (
                        'type' => 'number',
                        'required' => true,
                        'default' => 0,
                        'editable' => true,
                        'comment' => app::get('syscontent')->_('排序'),
                ),
                'modified'=> array (
                        'type' => 'time',
                        'editable' => true,
                        'comment' => app::get('syscontent')->_('修改时间'),
                ),

        ),
        'primary' => 'node_id',
        'index' => array(
                'ind_node_name' => ['columns' => ['node_name']],
                'ind_order_sort' => ['columns' => ['order_sort']],
        ),
        'comment' => app::get('syscontent')->_('文章节点表'),
);
 