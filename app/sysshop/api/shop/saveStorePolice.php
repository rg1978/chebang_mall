<?php
class sysshop_api_shop_saveStorePolice{

    public $apiDescription = "保存店铺分类";
    public function getParams()
    {
        $return['params'] = array(
            'policevalue' => ['type'=>'int','valid'=>'required','description'=>'库存报警数量','default'=>'','example'=>''],
            'shop_id' => ['type'=>'int','valid'=>'required','description'=>'店铺id','default'=>'','example'=>''],
            'police_id' => ['type'=>'int','valid'=>'','description'=>'库存报警Id','default'=>'','example'=>''],
        );
        return $return;
    }
    public function saveStorePolice($params)
    {
        $storePolice = app::get('sysshop')->model('store_police');
        try
        {
            $result = $storePolice->save($params);
        }
        catch( \LogicException $e )
        {
            throw new \LogicException('保存失败');
        }
        return $result;
    }
}
