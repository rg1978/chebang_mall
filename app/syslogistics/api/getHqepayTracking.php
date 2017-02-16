<?php
class syslogistics_api_getHqepayTracking{
    public $apiDescription = "获取快递鸟物流跟踪";
    public function getParams()
    {
        $return['params'] = array(
            'logi_no' =>['type'=>'string','valid'=>'required', 'description'=>'运单号','default'=>'','example'=>'1'],
            'corp_code' =>['type'=>'string','valid'=>'required', 'description'=>'物流公司编码','default'=>'','example'=>'SF'],
        );
        return $return;
    }
    public function getTracking($params)
    {
        if($params['logi_no'] && $params['corp_code'])
        {
            try{
                $data['tracker'] = kernel::single('syslogistics_data_tracker')->pullFromHqepay($params['logi_no'],$params['corp_code']);
            }catch(Exception $e){
                $data['logmsg'] = $e->getMessage();
            }
        }
        else
        {
            $data['logmsg'] = "暂无跟踪";
        }
        return $data;
    }
}
