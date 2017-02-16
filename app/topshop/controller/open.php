<?php

/**
 * @brief 商家商品管理
 */
class topshop_ctl_open extends topshop_controller {

    public function index()
    {
        $shopId = $this->shopId;
        $this->contentHeaderTitle = app::get('topshop')->_('开发者中心');

        $requestParams = ['shop_id'=>$shopId];
        $openInfo = app::get('topshop')->rpcCall('open.shop.develop.info', $requestParams);
        $shopConf = app::get('topshop')->rpcCall('open.shop.develop.conf', $requestParams);
        $pagedata['openInfo'] = $openInfo;
        $pagedata['shopConf'] = $shopConf;

        return $this->page('topshop/open/index.html', $pagedata);
    }

    public function applyForOpen()
    {
        $url = url::action('topshop_ctl_open@index');
        $shopId = $this->shopId;
        $requestParams = [
            'shop_id'=>$shopId,
            'key' => input::get('key'),
            'secret' => input::get('secret'),
        ];
        try
        {
        $res = app::get('topshop')->rpcCall('open.shop.develop.apply', $requestParams);
        }
        catch( Exception $e )
        {
            return $this->splash('error',$url, $e->getMessage(),true);
        }
        $this->sellerlog('申请绑定开发者');
        return $this->splash('success',$url,'申请成功，等待审核',true);
    }

    public function setConf()
    {
        $shopId = $this->shopId;
        $confs = input::get();

        try
        {
            $requestParams = [
                'shop_id' => $shopId,
                'developMode' => $confs['developer'] ? $confs['developer'] : 'PRODUCT',
                ];
            app::get('topshop')->rpcCall('open.shop.develop.setConf', $requestParams);
        }
        catch(Exception $e)
        {
            return $this->splash('error',$url,$e->getMessage(),true);
        }
        $this->sellerlog('开发者中心商家参数配置保存');
        return $this->splash('success',$url,'修改成功',true);
    }

}


