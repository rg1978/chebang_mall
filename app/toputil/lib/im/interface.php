<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

interface toputil_im_interface
{

    //根据列表获取html的字符串，返回一个array，key应该和传入数据的key相同。
    //每个array的结构与getRow方法相同。实现原理可以但不推荐使用foreach后通过getRow方法
    public function getList($list);

    //根据传入参数获取html, params是smarty模板中im标签的全部参数
    public function getRow($shop_id, $type, $content, $user_id, $params);

}


