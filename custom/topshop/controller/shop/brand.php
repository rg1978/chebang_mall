<?php
class topshop_ctl_shop_brand extends topshop_controller{

    public function index()
    {
        $this->contentHeaderTitle = app::get('topshop')->_('申请品牌');
        return $this->page('topshop/item/brandApply.html');
    }

    public function saveBrand()
    {
        $postData = input::get();
        //var_dump($postData);
        //exit();
        try
        {
            //$result = app::get('topshop')->rpcCall('shop.update',$postData);
            $mdlBrand = app::get('syscategory')->model('brand');
            $postData['seller_id'] = pamAccount::getAccountId();
            $shopdata = app::get('topshop')->rpcCall('shop.get',array('shop_id'=>shopAuth::getShopId()),'shop_name');
            $postData['shop_name'] = $shopdata['shop_name'];
            $result = $mdlBrand->save($postData);
            if( $result )
            {
                $msg = app::get('topshop')->_('保存品牌成功，请等待平台审核。');
                $result = 'success';
            }
            else
            {
                $msg = app::get('topshop')->_('保存品牌失败');
                $result = 'error';
            }
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            $result = 'error';
        }
        $this->sellerlog('申请品牌，品牌名：' .$postData['brand_name']);
        $url = url::action('topshop_ctl_shop_brand@index');
        return $this->splash($result,$url,$msg,true);

    }
}


