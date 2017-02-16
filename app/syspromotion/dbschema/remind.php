<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return array(
    'columns' => array(
        'remind_id' => array(
            'type' => 'number',
            'autoincrement' => true,
            'required' => true,
            'in_list' => false,
            'label' => app::get('syspromotion')->_('提醒id'),
        ),
        'remind_time' => array(
            'type' => 'time',
            'in_list' => true,
            'default_in_list' => true,
            'width' => '100',
            'order' => 15,
            'label' => app::get('syspromotion')->_('提醒时间'),
            'comment' => app::get('syspromotion')->_('提醒时间'),
        ),
        'start_time' => array(
            'type' => 'time',
            'comment' => app::get('syspromotion')->_('活动开始时间'),
        ),
        'remind_way' => array(
            'type' => array(
                'email' => '邮件',
                'mobile' =>'短信',
            ),
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('syspromotion')->_('提醒方式'),
            'comment' => app::get('syspromotion')->_('提醒方式'),
        ),
        'remind_goal' => array(
            'type' => 'string',
            'required' => true,
            'lenght' => '100',
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('syspromotion')->_('提醒目标'),
            'comment' => app::get('syspromotion')->_('提醒目标'),
        ),
        'user_id' => array(
            'type' => 'number',
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('syspromotion')->_('订阅提醒用户'),
            'comment' => app::get('syspromotion')->_('订阅提醒用户id'),
        ),
        'activity_name' => array(
            'type' => 'string',
            'leaght' => 90,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('syspromotion')->_('订阅提醒的活动名称'),
            'comment' => app::get('syspromotion')->_('订阅提醒的活动名称'),
        ),
        'activity_id' => array(
            'type' => 'number',
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('syspromotion')->_('订阅提醒的活动id'),
            'comment' => app::get('syspromotion')->_('订阅提醒的活动id'),
        ),
        'item_id' => array(
            'type' => 'number',
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('syspromotion')->_('订阅提醒的商品id'),
            'comment' => app::get('syspromotion')->_('订阅提醒的商品id'),
        ),
        'item_name' => array(
            'type' => 'string',
            'leaght' => 90,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('syspromotion')->_('订阅提醒的商品名称'),
            'comment' => app::get('syspromotion')->_('订阅提醒的商品名称'),
        ),
        'remind_status' => array(
            'type' => 'bool',
            'default'=>0,
            'comment' => app::get('syspromotion')->_('是否已提醒'),
        ),
        'add_time' => array(
            'type' => 'time',
            'comment' => app::get('syspromotion')->_('订阅时间'),
        ),
        'platform' => array(
            'type' => array(
                'topc' => 'pc端(topc)',
                'topwap' =>'新wap端(topwap)',
                'topm' =>'旧wap端(topm)',
            ),
            'default' => 'topc',
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('syspromotion')->_('订阅平台'),
            'comment' => app::get('syspromotion')->_('订阅平台'),
        ),
        'url' => array(
            'type' => 'string',
            'in_list' => true,
            'default_in_list' => false,
            'label' => app::get('syspromotion')->_('订阅链接'),
            'comment' => app::get('syspromotion')->_('订阅链接'),
        ),
    ),
    'primary' => 'remind_id',
    'index' => array(
        'ind_remind_time' => ['columns' => ['remind_time']],
        'ind_user_id' => ['columns' => ['user_id']],
        'ind_item_id' => ['columns' => ['item_id']],
        'ind_activity_id' => ['columns' => ['activity_id']],
        'ind_add_time' => ['columns' => ['add_time']],
    ),
    'comment' => app::get('syspromotion')->_('提醒表'),
);

