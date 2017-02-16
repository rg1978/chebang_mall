<?php
return  array(
	'columns'=>
	    array(
	    	'url_id' => array(
				'type' => 'number',
				'autoincrement' => true,
            	'comment' => app::get('sysopen')->_('链接ID'),
	    	),
	    	'url_name' => array(
				'type' => 'string',
				'length' => 100,
	            'required'=>true,
	            'in_list'=>true,
	            'default_in_list'=>true,
	            'searchtype' => 'has',
	            'filtertype' => false,
	            'filterdefault' => 'true',
	            'label' => app::get('sysopen')->_('链接名称'),
	            'comment' => app::get('sysopen')->_('链接名称'),
	            'order' => 5,
	    	),
	    	'url_tag' => array(
				'type' => 'string',
				'length' => 32,
	            'required'=>true,
	            'in_list'=>true,
	            'default_in_list'=>true,
	            'searchtype' => 'has',
	            'filtertype' => false,
	            'filterdefault' => 'true',
	            'label' => app::get('sysopen')->_('链接标识'),
	            'comment' => app::get('sysopen')->_('链接标识'),
	            'order' => 10,
	    	),
	    	'url' => array(
	    		'type' => 'string',
				'length' => 255,
	            'required'=>true,
	            'in_list'=>true,
	            'default_in_list'=>true,
	            'label' => app::get('sysopen')->_('链接'),
	            'comment' => app::get('sysopen')->_('链接'),
	            'order' => 15,
	    	),
	    	'create_time' => array(
		    	'type'=>'time',
		    	'in_list'=>true,
	            'default_in_list'=>true,
   	            'label' => app::get('sysopen')->_('创建时间'),
	            'comment' => app::get('sysopen')->_('创建时间'),
	           	'order' => 20,
	    	),
	    ),	
	    'primary' => 'url_id',
		'comment' => app::get('sysopen')->_('App链接表'),
	);    	
?>