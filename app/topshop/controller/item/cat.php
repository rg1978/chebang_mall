<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topshop_ctl_item_cat extends topshop_controller {

    public function index()
    {
        $data = app::get('topshop')->rpcCall('shop.cat.get',array('shop_id'=>$this->shopId));
        $pagedata['cat'] = $data;
        $pagedata['nowtime'] = time();

        $this->contentHeaderTitle = app::get('topshop')->_('店铺分类列表');
        return $this->page('topshop/item/category.html', $pagedata);
    }

    /**
     * @brief 保存店铺分类数据
     *
     * @return json
     */
    public function storeCat()
    {
        //$shopId = $this->shopId;
        //$data = input::get();
        $params['shop_id'] = $this->shopId;
        $params['catlist'] = serialize(input::get());
        //echo '<pre>';print_r($data);exit();
        $url = url::action('topshop_ctl_item_cat@index');
        try
        {
            $flag = app::get('topshop')->rpcCall('shop.save.cat',$params);
            //$flag = kernel::single('sysshop_data_cat')->storeCat($data,$shopId);
            if( $flag )
            {
                $status = 'success';
                $msg = app::get('topshop')->_('保存成功');
            }
            else
            {
                $status = 'error';
                $msg = app::get('topshop')->_('保存失败');
            }
            $this->sellerlog('保存店铺分类');
            return $this->splash($status,$url,$msg,true);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',$url,$msg,true);
        }
    }

    public function removeCat()
    {
        return $this->splash('success',null,$msg,true);
    }
}

