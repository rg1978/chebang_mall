<?php
class sysshop_api_applycat_removeApply{
    public $apiDescription = "店铺删除类目申请";
    public function getParams()
    {
        $return['params'] = array(
            'apply_id' =>['type'=>'int','valid'=>'int|required', 'description'=>'申请编号','default'=>'','example'=>'1'],
            'shop_id' =>['type'=>'int','valid'=>'int|required', 'description'=>'店铺id','default'=>'','example'=>'1'],
        );
        return $return;
    }
    public function removeApply($params)
    {
        $objMdlApplyCat = app::get('sysshop')->model('shop_apply_cat');
        try{
            $result =  $objMdlApplyCat->delete($params);
        }catch( \LogicException $e ){
            $msg = $e->getMessage();
            throw new \LogicException($msg);
        }
        return $result;
    }

}
