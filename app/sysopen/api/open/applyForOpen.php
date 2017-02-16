<?php
class sysopen_api_open_applyForOpen{
    public $apiDescription = "申请商户开放平台";
    public function getParams()
    {
        $return['params'] = array(
            'shop_id' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'店铺ID'],
            'key' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'key'],
            'secret' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'secret'],
        );
        return $return;
    }

    public function apply($params)
    {
        $shopId = $params['shop_id'];
        $info = kernel::single('sysopen_key')->apply($shopId, $params['key'], $params['secret']);
        return $info;
    }
}


