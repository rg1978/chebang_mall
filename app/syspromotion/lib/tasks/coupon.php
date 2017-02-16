<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class syspromotion_tasks_coupon extends base_task_abstract implements base_interface_task{

    // 每个队列执行100条订单信息
    var $limit = 100;
    public function exec($params=null)
    {
        $objLibCoupon = kernel::single('syspromotion_coupon');
        return $objLibCoupon->expireCoupon();
    }
}
