<?php
// logistics.fare.count
class syslogistics_api_fare{
    public $apiDescription = "计算运费";
    public function getParams()
    {
        $return['params'] = array(
            'template_id' =>['type'=>'int','valid'=>'required', 'description'=>'模板id','default'=>'','example'=>'1'],
            // 'weight' =>['type'=>'string','valid'=>'', 'description'=>'商品重量(单位：kg)','default'=>'','example'=>'10'],
            'areaIds' =>['type'=>'string','valid'=>'required', 'description'=>'收货地区的代号集合','default'=>'','example'=>'1'],
            'total_price' =>['type'=>'string','valid'=>'required', 'description'=>'总价格','default'=>'','example'=>'1'],
            'total_quantity' =>['type'=>'string','valid'=>'required', 'description'=>'总数量','default'=>'','example'=>'1'],
            'total_weight' =>['type'=>'string','valid'=>'required', 'description'=>'总重量','default'=>'','example'=>'1'],
        );
        return $return;
    }
    public function countFare($params)
    {
        // $weight = $params['weight'];
        $templateId     = $params['template_id'];
        $areaIds        = $params['areaIds'];
        $total_price    = $params['total_price'];
        $total_quantity = $params['total_quantity'];
        $total_weight   = $params['total_weight'];
        $objDataDlyTmpl = kernel::single('syslogistics_data_dlytmpl');
        $result = $objDataDlyTmpl->countFee($templateId, $areaIds, $total_price, $total_quantity, $total_weight);
        return $result;
    }
}
