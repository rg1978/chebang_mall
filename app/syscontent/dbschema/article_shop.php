<?php
/**
 * article_shop.php 
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
return array(
        'columns' => array(
                'article_id' => array(
                        'type' => 'number',
                        'required' => true,
                        'comment' => app::get('syscontent')->_('文章ID'),
                        'autoincrement' => true,
                        'width' => 50,
                        'order' => 1
                ),
                'title' => array(
                        'type' => 'string',
                        'required' => true,
                        'in_list' => true,
                        'default_in_list' => true,
                        'label' => app::get('syscontent')->_('文章标题'),
                        'order' => 2
                ),
                'platform' => array(
                        'type' => array(
                                '0' => app::get('syscontent')->_('电脑端和移动端'),
                                '1' => app::get('syscontent')->_('电脑端'),
                                '2' => app::get('syscontent')->_('移动端')
                        ),
                        'required' => true,
                        'default' => 0,
                        'in_list' => true,
                        'default_in_list' => false,
                        'label' => app::get('syscontent')->_('发布终端'),
                        'comment' => app::get('syscontent')->_('发布终端'),
                        'order' => 3,
                ),
                'node_id' => array(
                        'type' => 'table:article_shop_nodes@syscontent',
                        'required' => true,
                        'default' => 0,
                        'in_list' => true,
                        'default_in_list' => true,
                        'label' => app::get('syscontent')->_('文章所属节点'),
                        'comment' => app::get('syscontent')->_('节点id'),
                        'order' => 4
                ),
                'shop_id' => array(
                        'type' => 'table:shop@sysshop',
                        'label' => app::get('syscontent')->_('所属商家'),
                        'in_list' => true,
                        'default_in_list' => true,
                        'comment' => app::get('syscontent')->_('店铺ID'),
                
                ),
                'pubtime' => array(
                        'type' => 'time',
                        'comment' => app::get('syscontent')->_('发布时间（无需精确到秒）'),
                        'label' => app::get('syscontent')->_('发布时间'),
                        'editable' => true,
                        'width' => 130,
                        'in_list' => true,
                        'default_in_list' => true,
                        'order' => 6
                ),
                'modified' => array(
                        'type' => 'time',
                        'comment' => app::get('syscontent')->_('更新时间（精确到秒）'),
                        'label' => app::get('syscontent')->_('更新时间'),
                        'editable' => false,
                        'width' => 130,
                        'in_list' => true,
                        'default_in_list' => true,
                        'order' => 7
                ),
                'content' => array(
                        'type' => 'text',
                        'comment' => app::get('syscontent')->_('文章内容'),
                        'editable' => true
                )
        ),
        'primary' => 'article_id',
        'index' => array(
                'ind_title' => ['columns' => ['title']],
                'ind_node_id' => ['columns' => ['node_id']],
                'ind_pubtime' => ['columns' => ['pubtime']]
        ),
        'comment' => app::get('syscontent')->_('商家文章主表')
);
 