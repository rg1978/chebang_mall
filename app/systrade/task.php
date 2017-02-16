<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class systrade_task{

    public function post_update($dbver)
    {
        if($dbver['dbver'] < 0.2)
        {
            $db = app::get('systrade')->database();
            $tradeList = $db->executeQuery('SELECT tid,pay_time FROM systrade_trade')->fetchAll();
            foreach ($tradeList as $key => $value)
            {
                $db->executeUpdate('UPDATE systrade_order SET pay_time = ? WHERE tid = ?', [$value['pay_time'], $value['tid']]);
            }
        }
        if($dbver['dbver'] < 0.3)
        {
            $db = app::get('systrade')->database();
            $db->executeQuery('ALTER TABLE `systrade_cart` MODIFY `cart_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3');
            $cartList = $db->executeQuery('SELECT cart_id,obj_type,sku_id,package_id,params FROM systrade_cart')->fetchAll();
            foreach ($cartList as $value)
            {
                if($value['obj_type']=='item')
                {
                    $obj_ident = 'item_'.$value['sku_id'];
                }
                if($value['obj_type']=='package')
                {
                    $cartParams = unserialize($value['params']);
                    sort($cartParams['sku_ids']);
                    $obj_ident = 'package_'.$value['package_id'].'_'.implode('-',$cartParams['sku_ids']);
                }
                $objCart = app::get('systrade')->model('cart');
                $objCart->update(array('obj_ident'=>$obj_ident),array('cart_id'=>$value['cart_id']));
            }
        }
        if($dbver['dbver'] < 0.4)
        {
            $db = app::get('systrade')->database();
            $db->executeQuery('UPDATE `systrade_trade` SET `dlytmpl_ids`=`dlytmpl_id`');
        }
        if($dbver['dbver'] < 0.5)
        {
            $db = app::get('systrade')->database();
            $db->executeQuery('UPDATE `systrade_trade` SET `shipping_type`="ziti" WHERE dlytmpl_id="0" ');
            $db->executeQuery('UPDATE `systrade_trade` SET `shipping_type`="post" WHERE dlytmpl_id!="0" ');
        }
    }

}

