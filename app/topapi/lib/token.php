<?php
/**
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topapi_token {

    public function __construct()
    {
        $bcrypt = config::get('topapi.bcrypt','topapi_token_default');
        $this->objBcrypt = kernel::single($bcrypt);
    }

    /**
     * 设置token
     *
     * @param int $userId 用户名Id
     * @param string $data 登录用户信息
     *
     * @return string $token
     */
    public function make($userId, $data)
    {
        return $this->objBcrypt->make($userId, $data);
    }

    /**
     * 验证token
     *
     * @param string $token
     *
     * @return int 成功返回用户id
     */
	public  function check($token)
	{
        return $this->objBcrypt->check($token);
    }

    /**
     * 删除token 退出登录
     *
     * @param string $token
     *
     * @return bool true
     */
    public function delete($token)
    {
        return $this->objBcrypt->delete($token);
    }
}

