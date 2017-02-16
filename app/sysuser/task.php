<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysuser_task{

    public function post_install($options)
    {
        kernel::single('base_initial', 'sysuser')->init();
        pamAccount::registerAuthType('sysuser','member',app::get('sysuser')->_('商城会员用户系统'));
    }

    public function post_uninstall()
    {
        pamAccount::unregisterAuthType('sysuser');
    }

    public function post_update($dbver)
    {
        if($dbver['dbver'] < 0.2)
        {
            $db = app::get('sysuser')->database();
            $db->executeQuery('INSERT INTO sysuser_account SELECT * FROM pam_user');
        }
        if($dbver['dbver'] < 0.3)
        {
            $db = app::get('sysuser')->database();
            $userList = $db->executeQuery('SELECT user_id,point FROM sysuser_user');
            foreach ($userList as $key => $value) {
                $id = $value['user_id'];
                $point = $value['point'] ? $value['point'] : 0;
                $time = time();
                if($point)
                {
                    $db->executeUpdate('insert into sysuser_user_points(user_id,point_count,modified_time) value (?,?,?)',[$id,$point,$time]);
                }
            }

            $userList = $db->executeQuery('SELECT * FROM sysuser_user_point');
            foreach ($userList as $key => $value) {
                app::get('sysuser')->model('user_pointlog')->save($value);
            }

        }
        if($dbver['dbver'] < 0.4)
        {
            $db = app::get('syspromotion')->database();
            $couponList = $db->executeQuery('SELECT coupon_id,deduct_money,canuse_start_time,canuse_end_time FROM syspromotion_coupon');
            foreach ($couponList as $key => $value) {
                $price = $value['deduct_money'];
                $start_time = $value['canuse_start_time'];
                $end_time = $value['canuse_end_time'];
                $db->executeUpdate('UPDATE sysuser_user_coupon SET price = ?, start_time = ?, end_time = ? WHERE coupon_id='.$value['coupon_id'], [$price, $start_time, $end_time]);
            }
        }
    }

}

