<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class toputil_im_plugin_webcall implements toputil_im_interface
{
    var $accountid = '';

    //浮动系列
    //浮动图标或者浮动列表模式下使用
    var $floatUrl = "http://p.365webcall.com/IMMeForPartner.aspx";

    //固定图标系列
    //这个是打开聊天页面的地址
    //这个暂时弃用
    var $chatUrl = "http://p.365webcall.com/chat/ChatWinForPartner.aspx";

    //固定图标2系列
    //不显示在线状态
    var $chatUrlLess = "http://p.365webcall.com/chat/ChatWinCorp.aspx";

    public function __construct()
    {
        $this->accountid = config::get('im.365webcall.accountId');
        return null;
    }

    //暂时不用批量处理，因为批量数据验证还没有想好
    public function getList($list)
    {
        $return = [];
        foreach($list as $k => $v)
        {
            $shop_id = $v['shop_id'];
            $type = $v['type'];
            $user_id = $v['user_id'];
            $params = $v['params'];
            $content = $v['content'];
            $return[$k] = $this->getRow($shop_id, $type, $content, $user_id, $params);
        }
        return $return;
    }

    public function getRow($shop_id, $type = 'default', $content, $user_id, $params)
    {

        $email = $this->getWebcallEmail($shop_id);
        if(empty($email))
            return null;

        $accountid = $this->accountid;
        $email = $email;
        $note = $params['note'];
        $loc = $params['loc'] ? $params['loc'] : null;

        if(in_array($type, ['index']))
        {
            return $this->getHtmlFloat($accountid, $email, $user_id, $note, 0, $loc);
        }
     // elseif(in_array($type, ['itemInfo']))
     // {
     //     return $this->getHtmlMore($accountid, $email, $user_id, $note, 0, $loc, $content);
     // }
        else
        {
            return $this->getHtmlLess($accountid, $email, $user_id, $note, 0, $loc, $content, $params);
        }

    }

    public function getWebcallEmail($shop_id)
    {
        $wc = app::get('toputil')->rpcCall('im.shop.webcall.get', ['shop_id'=>$shop_id, 'fields'=>'email,use_im']);

        if(empty($wc['email']) || $wc['use_im']<1)
            return null;

        return $wc['email'];
    }

    //生成带有登陆状态的按钮
    //暂时弃用
    public function getHtmlMore($accountid, $email, $user_id = 0, $note = null, $LL = 0, $loc = null, $content = '联系客服')
    {
        $params = [];
        $params['accountid'] = $accountid;
        $params['Email']     = $email;
        if($loc != null)
            $params['Loc']       = $loc;
        if($user_id > 0)
            $params['user_id']   = $user_id;
        if($note != null)
            $params['Note']      = $note;
        $params['LL']        = $LL;

        $url = $this->chatUrl;

        $queryStr = http_build_query($params);
        $html = "<span style=\"cursor:pointer\" onclick=\"javascript:window.open( '$url?$queryStr', '$this->accountid', 'width=770,height=710, toolbar=no, menubar=no, scrollbars=no, resizable=yes,location=no, status=no,center=yes')\" defer=ture>$content</span>";

        return $html;
    }

    //生成不带有登陆状态的按钮
    public function getHtmlLess($accountid, $email, $user_id = 0, $note, $LL = 0, $loc, $content, $paramsInfo)
    {
        $params = [];
        $params['accountid'] = $accountid;
        $params['Email']     = $email;
        $params['Loc']       = $loc;
        if($user_id > 0)
            $params['user_id']   = $user_id;
        $params['Note']      = $note;
        $params['LL']        = $LL;
        $class = $paramsInfo['class'];

        $url = $this->chatUrlLess;

        $queryStr = http_build_query($params);
        if($paramsInfo['is_wap'] == true)
            $html = "<a class='$class' href=\"$url?$queryStr\">$content</a>";
        else
            $html = "<a class='$class' style=\"cursor:pointer\" onclick=\"javascript:window.open( '$url?$queryStr', '$this->accountid', 'width=770,height=710, toolbar=no, menubar=no, scrollbars=no, resizable=yes,location=no, status=no,center=yes')\" defer=ture>$content</a>";

        return $html;
    }

    //生成浮动窗口
    public function getHtmlFloat($accountid, $email, $user_id = 0, $note, $LL = 0, $loc)
    {
        $params = [];
        $params['accountid'] = $accountid;
        $params['Email']     = $email;
        $params['Loc']       = $loc;
        if($user_id > 0)
            $params['user_id']   = $user_id;
        $params['Note']      = $note;
        $params['LL']        = $LL;

        $url = $this->floatUrl;

        $queryStr = http_build_query($params);
        $html = "<script type='text/javascript' src='$url?$queryStr'></script>";

        return $html;
    }

  //public function getHtmlLarge()
  //{
  //
  //}


}

