<?php
/**
 * page.php 
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
return array(
        'columns' => [
            'page_id'=>[
                    'type' => 'number',
                    'required' => true,
                    'autoincrement' => true,
                    'width' => 110,
                    'label' => app::get('syspromotion')->_('id'),
                    'comment' => app::get('syspromotion')->_('促销专题页id'),
            ],
            'page_name' => [
                    'type' => 'string',
                    'required' => true,
                    'in_list' => true,
                    'default_in_list' => true,
                    'width' => 110,
                    'label' => app::get('syspromotion')->_('专题名称'),
                    'comment' => app::get('syspromotion')->_('专题名称'),
            ],
            'page_tmpl' => [
                    'type' => 'string',
                    'required' => true,
                    'in_list' => true,
                    'default_in_list' => true,
                    'width' => 110,
                    'label' => app::get('syspromotion')->_('专题页面'),
                    'comment' => app::get('syspromotion')->_('专题页面'),
            ],
            'page_desc' => [
                    'type' => 'string',
                    'required' => true,
                    'width' => 110,
                    'label' => app::get('syspromotion')->_('专题描述'),
                    'comment' => app::get('syspromotion')->_('专题描述'),
            ],
            'used_platform' => [
                    'type' => [
                                'pc' => app::get('syspromotion')->_('适用于pc'),
                                'wap' => app::get('syspromotion')->_('适用于wap'),
                            ],
                    'default' => 'pc',
                    'required' => true,
                    'in_list' => true,
                    'default_in_list' => true,
                    'label' => app::get('syspromotion')->_('适用平台'),
                    'comment' => app::get('syspromotion')->_('适用平台'),
            ],
            'display_time' => [
                    'type' => 'time',
                    'default' => 0,
                    'required' => true,
                    'in_list' => true,
                    'default_in_list' => true,
                    'label' => app::get('syspromotion')->_('专题发布时间'),
                    'comment' => app::get('syspromotion')->_('专题发布时间'),
            ],
            'is_display' => [
                    'type' => 'bool',
                    'default' => 0,
                    'required' => true,
                    'in_list' => true,
                    'default_in_list' => true,
                    'label' => app::get('syspromotion')->_('是否开启'),
                    'comment' => app::get('syspromotion')->_('是否开启'),
            ],
            'created_time' => [
                    'type' => 'time',
                    'default' => 0,
                    'required' => true,
                    'in_list' => true,
                    'default_in_list' => true,
                    'label' => app::get('syspromotion')->_('专题创建时间'),
                    'comment' => app::get('syspromotion')->_('专题创建时间'),
            ],
            'updated_time' => [
                    'type' => 'time',
                    'default' => 0,
                    'required' => true,
                    'in_list' => true,
                    'default_in_list' => true,
                    'label' => app::get('syspromotion')->_('专题修改时间'),
                    'comment' => app::get('syspromotion')->_('专题修改时间'),
            ],
        ], 
        'primary' => 'page_id', 
        'index' => [
                'ind_created_time' => ['columns' => ['created_time']],
                'ind_used_platform' => ['columns' => ['used_platform']],
        ], 
        'comment' => app::get('syspromotion')->_('促销专题页') 
);
