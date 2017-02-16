<?php
return array(
    'columns' => array(
        'log_id' => array(
            'type' => 'number',
            'autoincrement'=>true,

        ),
        'token'=>array(
            'type'=>'string',
            'required'=>true,
        ),
        'created_at'=>array(
                'type' => 'time',
        ),
        'cp_id' =>array(
            'type'=>'number',
            'required'=>'true'
        ),
        //是否成功访问，及错误的原因；
        'desc' =>array(
            'type'=>'string',
        ),
    ),
    'primary' => 'log_id',
    'engine' => 'innodb',
    'comment' => app::get('topapi')->_('合作伙伴表访问日志'),
);