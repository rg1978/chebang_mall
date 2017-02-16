<?php
class sysshop_api_checkShopName{
    public $apiDescription = "检测店铺名称是否存在";
    public function getParams()
    {
        $return['params'] = array(
            'shop_name' => ['type'=>'string','valid'=>'required','description'=>'店铺名称','default'=>'','example'=>'1'],
            'enterapply_id' => ['type'=>'int','valid'=>'','description'=>'入驻申请id','default'=>'','example'=>'1'],
        );
        return $return;
    }

    public function check($params)
    {
        $objDataShop = kernel::single('sysshop_data_shop');
        try{
            $objDataShop->checkShopName($params);
        }
        catch(LogicException $e)
        {
            throw new LogicException($e->getMessage());
        }
        return true;
    }
}
