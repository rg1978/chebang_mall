<?php
class sysshop_api_applycat_saveApplyCat{
    public $apiDescription = "店铺申请类目保存";
    public function getParams()
    {
        $return['params'] = array(
            'shop_id' =>['type'=>'int','valid'=>'int|required', 'description'=>'店铺id','default'=>'','example'=>'1'],
            'cat_id' =>['type'=>'string','valid'=>'required', 'description'=>'申请的类目ids','default'=>'','example'=>'1'],
            'apply_reason' => ['type'=>'string','valid'=>'required', 'description'=>'商家申请类目的原因','default'=>'','example'=>''],
        );
        return $return;
    }
    public function saveData($params)
    {
        $objMdlApplyCat = app::get('sysshop')->model('shop_apply_cat');
        try{
            $count = $objMdlApplyCat->count(['shop_id'=>$params['shop_id'],'cat_id'=>$params['cat_id'],'check_status|in'=>['pending','adopt']]);
            if($count >=1)
            {
                $msg = app::get('sysshop')->_('该类目已经被申请');
                throw new \LogicException($msg);
            }
            $params['apply_time'] = time();
            $result =  $objMdlApplyCat->save($params);
        }catch( \LogicException $e ){
            $msg = $e->getMessage();
            throw new \LogicException($msg);
        }
        return $result;
    }
}

