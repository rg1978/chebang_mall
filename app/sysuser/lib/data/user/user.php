<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2014-2014 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysuser_data_user_user
{
	/**
     * 会员中心用户个人资料修改
     * @param string user id
     * @param
     * @param array postdata 不能为空
     * @return boolean true or false
     */
	public function saveInfoSet($postdata,$userId)
	{

        if($postdata)
        {
            foreach ($postdata as $key=>$value)
            {
                if($key == "birthday")
                {
                    $value = strtotime($value);
                }
                $sysdata[$key] = $value;
            }
        }
        $sysdata['user_id'] = $userId;

        if( !app::get('sysuser')->model('user')->save($sysdata) )
        {
            throw new \LogicException(app::get('sysuser')->_('修改失败'));
		}

		return true;
	}

}
