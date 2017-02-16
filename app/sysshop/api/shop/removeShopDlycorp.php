<?php
class sysshop_api_shop_removeShopDlycorp{
    public $apiDescription = "店铺解除被签约的物流公司";
    public function getParams()
    {
        $return['params'] = array(
            'shop_id' =>['type'=>'int','valid'=>'required', 'description'=>'物流公司编号id','default'=>'','example'=>'1'],
            'corp_id' =>['type'=>'int','valid'=>'required', 'description'=>'物流公司编号id','default'=>'','example'=>'1'],
        );
        return $return;
    }
    public function remove($params)
    {

        $objMdlDlycorpShop = app::get('sysshop')->model('shop_rel_dlycorp');
        try{

            $pagedata = $objMdlDlycorpShop->delete($params);
        }
        catch( \LogicException $e )
        {
            throw new \LogicException('删除失败');
        }
        return $pagedata;
    }


}
