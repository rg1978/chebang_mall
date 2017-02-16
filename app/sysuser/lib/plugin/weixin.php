
<?php
use GuzzleHttp\Exception\ClientException;
class sysuser_plugin_weixin extends sysuser_plugin_abstract implements sysuser_interface_trust
{


    public $name = '微信登陆';
    public $flag = 'weixin';
    public $version = '2.0';
    public $platform = ['web'];


    public $authUrls = ['web' => ['authorize' => 'https://open.weixin.qq.com/connect/qrconnect',
                                  'token' => 'https://api.weixin.qq.com/sns/oauth2/access_token',
                                  'userinfo' => 'https://api.weixin.qq.com/sns/userinfo']];
    protected $tmpOpenId = null;

    /**
   * 获取plugin autherize url
   *
   * @param string $state
   * @return string
   */
    public function getAuthorizeUrl($state)
    {
        return $this->getUrl('authorize').'?'.http_build_query(['appid' => $this->getAppKey(),
                                                                 'redirect_uri' => $this->getCallbackUrl(),
                                                                 'response_type' => 'code',
                                                                 'scope' => 'snsapi_login',
                                                                 'state' => $state]);
    }

    /**
   * 通过调用信任登陆accesstoken接口生成access token
   *
   * @param string $code
     * @return string
   */
    public function generateAccessToken($code)
    {
        $args = ['appid' => $this->getAppKey(),
                 'secret'=> $this->getAppSecret(),
                 'grant_type' => 'authorization_code',
                 'code' => $code];
        try
        {
            $msg = client::get($this->getUrl('token'), ['query' => $args])->json();
        }
        //ClientException
        catch (ClientException $e)
        {
            $msg = $e->getResponse()->json();
            throw new \LogicException("error :" . $msg['errcode']. "msg  :". $msg['errmsg']);
        }

        $this->tmpOpenId = $msg['openid'];
        return $msg['access_token'];
    }



    public function generateOpenId()
    {
        return $this->tmpOpenId;
    }

    public function generateUserInfo()
    {
        $args = ['access_token' => $this->getAccessToken(),
                 'openid' => $this->tmpOpenId];
        $msg = client::get($this->getUrl('userinfo'), ['query' => $args])->json();


        if($msg['errcode']) throw new \LogicException(app::get('sysuser')->_('参数错误！'));

        return $this->convertStandardUserInfo($msg);
    }

    protected function convertStandardUserInfo($trustUserInfo)
    {
        return $userInfo = ['openid' => $this->tmpOpenId,
                            'access_token' => $this->getAccessToken(),
                            'nickname' => $trustUserInfo['nickname'],
                            'figureurl' => $trustUserInfo['headimgurl']];
    }

}