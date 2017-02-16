<?php
class topshop_ctl_shop_setting extends topshop_controller{

    public function index()
    {
        $shopdata = app::get('topshop')->rpcCall('shop.get',array('shop_id'=>shopAuth::getShopId()),'seller');
        $pagedata['shop'] = $shopdata;
        $pagedata['im_plugin'] = app::get('sysconf')->getConf('im.plugin');
        $this->contentHeaderTitle = app::get('topshop')->_('店铺设置');
        return $this->page('topshop/shop/setting.html', $pagedata);
    }

    public function saveSetting()
    {
        $postData = input::get();
        $validator = validator::make(
            [$postData['shop_descript']],['max:200'],['店铺描述不能超过200个字符!']
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                return $this->splash('error',null,$error[0]);
            }
        }
        try
        {
            $result = app::get('topshop')->rpcCall('shop.update',$postData);
            if( $result )
            {
                $msg = app::get('topshop')->_('设置成功');
                $result = 'success';
            }
            else
            {
                $msg = app::get('topshop')->_('设置失败');
                $result = 'error';
            }
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            $result = 'error';
        }
        $this->sellerlog('编辑店铺配置。如店铺logo,描述等');
        $url = url::action('topshop_ctl_shop_setting@index');
        return $this->splash($result,$url,$msg,true);

    }

}


