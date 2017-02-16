<?php
class syspromotion_api_remind_getData {
    public $apiDescription = '根据用户id获取订阅数据';
    public function getParams()
    {
        $return['params'] = array(
            'user_id'       => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'设置提醒的会员id'],
            'activity_id'   => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'设置提醒的活动id'],
            'item_id'       => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'设置提醒的商品id'],
            'time_field'       => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'条件时间字段'],
            'bthan'   => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'大于指定时间'],
            'sthan'       => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'小于指定时间'],
            'fields'       => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'查询的字段'],

        );
        return $return;
    }

    public function get($params)
    {
        $row = $params['fields'] ? $params['fields'] : "*";
        if(isset($params['field'],$params['bthan'],$params['sthan']) && $params['field'] && $params['bthan'] && $params['sthan'])
        {
            $params["'".$params['field']."|bthan'"] = $params['bthan'];
            $params["'".$params['field']."|sthan'"] = $params['sthan'];
            unset($params['field'],$params['bthan'],$params['sthan']);
        }
        $objMdlRemind = app::get('syspromotion')->model('remind');
        $data['list'] = $objMdlRemind->getList($row,$params);
        $data['count'] = $objMdlRemind->count($params);
        return $data;
    }
}
