<?php

/**
 * @brief 二级域名
 */
class sysshop_ctl_admin_subdomain extends desktop_controller{

    /**
     * @brief 二级域名列表
     *
     * @return
     */
    public function index()
    {
        return $this->finder('sysshop_mdl_subdomain',array(
            'use_buildin_delete' => false,
        ));
    }

    public function edit()
    {
        $shopId = input::get('shop_id');
        try
        {
            $params['shop_id'] = $shopId;
            $domainInfo = app::get('sysshop')->rpcCall('shop.subdomain.get', $params);
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        $pagedata = $domainInfo;
        $this->contentHeaderTitle = '编辑商户二级域名';
        return view::make('sysshop/admin/shop/editsubdomain.html',$pagedata);
    }

    public function saveSubdomain()
    {
        $this->begin();
        $postdata = ['subdomain'=>input::get('subdomain'), 'shop_id'=>input::get('shop_id')];
        $result = app::get('sysshop')->model('subdomain')->save($postdata);
        if($result)
        {
            $msg = app::get('sysshop')->_('修改商户域名成功!');
        }
        else
        {
            $msg = app::get('sysshop')->_('修改商户域名失败!');
        }
        $this->adminlog("编辑商户id：{$postdata['shop_id']}的二级域名[{$postdata['subdomain']}]", $flag ? 1 : 0);
        $this->end($msg);
    }

}
