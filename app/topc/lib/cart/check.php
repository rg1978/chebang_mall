<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topc_cart_check
{

    public function addCheck($addObject)
    {
        if($addObject['obj_type'] != 'item')
            throw new LogicException(app::get('topc')->_('商品类型错误，请登录后重新加入购物车！'));

        if($addObject['mode'] != 'cart')
            throw new LogicException(app::get('topc')->_('购物车类型错误，请登录后重新加入购物车！'));

        $cookieCartCount = kernel::single('topc_cart_offline')->getCartCount();
        if($cookieCartCount['variety'] >= 50)
            throw new LogicException(app::get('topc')->_('加入购物车失败：购物车装太多东西了！'));

        return true;
    }

    public function updateCheck($updateObject)
    {
        if($updateObject['obj_type'] != 'item')
            throw new LogicException(app::get('topc')->_('商品类型错误，请登录后重新加入购物车！'));

        if($updateObject['mode'] != 'cart')
            throw new LogicException(app::get('topc')->_('购物车类型错误，请登录后重新加入购物车！'));

        $cartId = $updateObject['cart_id'];
        $cookieCart = kernel::single('topc_cart_offline')->getCart();
        if($cookieCart[$cartId] == null)
            throw new LogicException(app::get('topc')->_('购物车错误，找不到需要修改的购物车项！'));

        return true;
    }

}
