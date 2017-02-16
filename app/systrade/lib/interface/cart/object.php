<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * 购物车项处理接口
 * 
 */
interface systrade_interface_cart_object{
    // public function add_object($aData);    // 增加包括追加
    // public function update($sIdent,$quantity); // 更新
    // public function get($sIdent = null,$rich = false);
    // public function getAll($rich = false);
    // public function delete($sIdent = null);
    // public function deleteAll();
    // public function count(&$aData);

    public function getObjType(); //对象类型
    public function getCheckSort(); //检测顺序
    public function checkObject($params, $basicCartData); //加入检查
}

