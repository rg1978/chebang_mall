<?php
class syslogistics_api_delivery_getInfo {

    public $apiDescription = "获取订单发货信息";

    public function getParams()
    {
        $return['params'] = array(
            'tid' =>['type'=>'string','valid'=>'required', 'description'=>'订单号','default'=>'','example'=>''],
        );
        return $return;
    }

    public function getInfo($params)
    {
        $tid = $params['tid'];
        $rows = 'logi_name,logi_no,corp_code,delivery_id,receiver_name,t_begin';
        $data = app::get('syslogistics')->model('delivery')->getRow($rows, array('tid'=>$tid,'status'=>'succ'), 0, 1);
        if(!$data)
        {
            return false;
        }
        return $data;
    }
}
