<?php
/**
 * 用来请求365webcall的接口的封装
 *
 * 通过调用这个类，请求365webcall，创建、删除子账号，并且可以直接打开在线管理平台。还能检查账号是否在线
 *
 * @author Elrond <guocheng@shopex.cn>
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysim_webcall_webcall
{

    /**
     * 新增一个客服账号
     *
     * @param string shop_id 店铺编号，platform指平台
     * @param string email 申请账号的email
     * @param string pwd 申请客服账号的密码
     * @param string name 申请账号的昵称
     * @param string url 网站的地址
     * @param string area 二级地区，例如“浙江省 杭州市”
     * @param string corpName 公司名称
     * @param string phone 电话号码
     * @param string qq qq号码
     * @param string contact 联系人
     * @return int mainAccountId
     */
    public function addAccount($shop_id, $email, $pwd, $name, $url = '', $area = '', $corpName = '', $phone = '', $qq = '', $contact = '', $useIm = 0)
    {
        $accountId = kernel::single('sysim_webcall_request')->addAccount($email, $pwd, $name, $url, $area, $corpName, $phone, $qq, $contact);

        $useIm = $useIm ? 1 : 0 ;

        $accountWebcallShop = ['shop_id' => $shop_id, 'email' => $email, 'use_im' => $useIm];
        $accountWebcallShopMdl = app::get('sysim')->model('account_webcall_shop');
        $accountWebcallShopMdl->save($accountWebcallShop);

        return $accountId;
    }

}

