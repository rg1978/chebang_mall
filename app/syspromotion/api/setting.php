<?php
class syspromotion_api_setting{
    public $apiDescription = '获取活动配置值';
    public function getParams()
    {
        $return['params'] = array();
        return $return;
    }
    public function get($params)
    {
        $config = app::get('syspromotion')->getConf('activity.mobile.remind.number');
        $config = $config ? $config : 5;
        return $config;
    }
}
