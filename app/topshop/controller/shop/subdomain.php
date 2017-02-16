<?php
class topshop_ctl_shop_subdomain extends topshop_controller{

    public function index()
    {
        $this->contentHeaderTitle = app::get('topshop')->_('二级域名设置');

        $pagedata = app::get('topshop')->rpcCall('shop.subdomain.get',array('shop_id'=>shopAuth::getShopId()), 'seller');
        
        if(!$pagedata['subdomain_enabled'])
        {
            return $this->splash('error', url::action('topshop_ctl_index@index'), '', false);
        }

        return $this->page('topshop/shop/subdomain.html', $pagedata);
    }

    public function saveSubdomain()
    {
        $postData = ['subdomain'=>input::get('domain'), 'shop_id'=>$this->shopId, 'seller_id'=>$this->sellerId];
        $validator = validator::make(
            // ^[0-9a-zA-Z][0-9a-zA-Z\-]{2,30}[0-9a-zA-Z]$
            [$postData['subdomain']],['regex:/^[\pL\pM\pN][\pL\pM\pN-]{2,30}[\pL\pM\pN]$/u'],['二级域名长度必须为4-32个"字母、数字、-" 组成的字符串，且中杠不能在开头和结尾!']
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
            $result = app::get('topshop')->rpcCall('shop.subdomain.apply', $postData);
            if( $result )
            {
                $msg = app::get('topshop')->_('申请成功');
                $result = 'success';
            }
            else
            {
                $msg = app::get('topshop')->_('申请失败');
                $result = 'error';
            }
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            $result = 'error';
        }
        $this->sellerlog('编辑店铺二级域名');
        $url = url::action('topshop_ctl_shop_subdomain@index');
        return $this->splash($result, $url, $msg, true);

    }

}


