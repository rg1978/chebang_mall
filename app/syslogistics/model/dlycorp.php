<?php

/**
 * ShopEx licence
 * @author ajx
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class syslogistics_mdl_dlycorp extends dbeav_model {

    public $defaultOrder = array('order_sort',' ASC');

    public function doDelete($ids)
    {
        $objMdldlycorp = app::get('syslogistics')->model('dlycorp');
        $dlycorp = app::get('syslogistics')->rpcCall('shop.dlycorp.getlist',['corp_id'=>implode(',',$ids)]);
        if($dlycorp = $dlycorp['list'])
        {
            $dlycorp = array_bind_key($dlycorp,'corp_id');
            $name = array_column($dlycorp,'corp_name');
            $name = implode(',',$name);
            $msg = app::get('syslogistics')->_($name.'等快递公司被店铺开启，不可删除');
            throw new \logicException($msg);
            return false;
        }
        $result = $objMdldlycorp->delete(array('corp_id'=>$ids));
        if(!$result)
        {
            $msg = app::get('syslogistics')->_('快递公司删除失败');
            throw new \logicException($msg);
            return false;
        }
        return true;
    }

}
