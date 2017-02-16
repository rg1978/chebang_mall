<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

return  array(
    'columns' => array(
        'id' => array(
            //'type' => 'int unsigned',
            'type' => 'number',
            'required' => true,
            //'pkey' => true,
            'autoincrement' => true,
            'editable' => false,
            'comment' => app::get('site')->_('挂件ID'),
        ),
        'app' => array(
            //'type' => 'varchar(30)',
            'type' => 'string',
            'length' => 30,
            'required' => true,
            'default' => '',
            'editable' => false,
            'comment' => app::get('site')->_('如果是系统挂件, 此字段为应用名. 如果是模板挂件此字段为空'),
        ),
        'theme' => array(
            //'type' => 'varchar(30)',
            'type' => 'string',
            'length' => 30,
            'required' => true,
            'default' => '',
            'editable' => false,
            'comment' => app::get('site')->_('如果是模板级挂件, 此字段为模板名. 如果是系统挂件此字段为空'),
        ),
        'name' => array(
            //'type' => 'varchar(30)',
            'type' => 'string',
            'length' => 30,
            'required' => true,
            'default' => '',
            'editable' => false,
            'comment' => app::get('site')->_('挂件名'),
        )
    ),
    'index' => array(
        'ind_uniq' => array(
            'columns' => array(
                0 => 'app',
                1 => 'theme',
                2 => 'name',
            ),
        ),
    ),
    
    'primary' => 'id',
    'unbackup' => true,
    'comment' => app::get('site')->_('挂件表'),
);
