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

class sysim_webcall_request
{
    /**
     * configure accountid(saas service domain)
     *
     * @var String
     */
    private $__accountid = '';

    /**
     * url to add account
     *
     * @var String
     */
    private $__addAccountUrl = 'http://p.365webcall.com/invite.aspx';

    /**
     * url to add sub account
     *
     * @var String
     */
    private $__addSubAccountUrl = 'http://p.365webcall.com/addSubAccount.aspx';

    /**
     * url to delete sub account
     *
     * @var String
     */
    private $__deleteSubAccountUrl = 'http://p.365webcall.com/deleteAccount.aspx';

    /**
     * url to enter account control center
     *
     * @var String
     */
    private $__controlAccountUrl = 'http://p.365webcall.com/manage_index.aspx';

    /**
     * url to check online
     *
     * @var String
     */
    private $__checkOnlineUrl = 'http://p.webcall.com/getUserStatus.aspx';

    public function __construct()
    {
        $this->accountid = config::get('im.365webcall.accountId');
        return null;
    }

    /**
     * 新增一个客服账号
     *
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
    public function addAccount($email, $pwd, $name, $url = '', $area = '', $corpName = '', $phone = '', $qq = '', $contact = '')
    {
        $requestParams = [
            'email' => $email,
            'accountid' => $this->accountid,
            'pwd' => $pwd,
            'name' => $name,
            'url' => $url,
            'area' => $area,
            'CorpName' => $corpName,
            'phone' => $phone,
            'qq' => $qq,
            'Contact' => $contact,
        ];

        $url = $this->__addAccountUrl . '?' . http_build_query($requestParams);


      //return $url;
        $result = client::get($url)->json();
        return $result;
    }

    /**
     * 为聊天用户增加子账户
     *
     * @param string email 申请账号的email
     * @param string pwd 申请客服账号的密码
     * @param string name 申请账号的昵称
     * @param string mainAccount 主账号
     * @return bool
     */
    public function addSubAccount($email, $pwd, $name, $mainAccount)
    {

        return true;
    }

    /**
     * 删除某个账号下面的子账号
     *
     * @param string mainAccount 主账号
     * @param string email 申请账号的email
     * @return bool
     */
    public function deleteSubAccount($mainAccount, $email)
    {

        return true;
    }

    public function controlAccount()
    {

        return $url;
    }

    public function checkOnline($userId)
    {

        return $status;
    }
}

