<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * @author guocheng
 */

class sysopen_key extends system_prism_init_base
{

    public function suspend($shop_id, $mark='')
    {
        $keyBindModel = app::get('sysopen')->model('keys');
        $key['shop_id'] = $shop_id;
        $key['contact_type'] = 'notallowopen';
        $key['mark'] = $mark;
        return $keyBindModel->save($key);
    }

    //重新开启商家的开放权限
    public function open($shop_id)
    {
        $keyBindModel = app::get('sysopen')->model('keys');
        $data = $keyBindModel->getRow('*', ['shop_id'=>$shop_id]);
        $key = $data['key'];
        return $keyBindModel->update(['contact_type'=>'openstandard'],['key'=>$key]);
    }

    //用来生成申请状态的
    public function apply($shopId, $key, $secret)
    {
        $developMdl = app::get('sysopen')->model('develop');
        if( ! $developMdl->count(['key'=>$key,'secret'=>$secret]) )
        {
            throw new LogicException('绑定的KEY或者Secret错误');
        }


        $keyBindModel = app::get('sysopen')->model('keys');
        $data['shop_id'] = $shopId;
        $data['key'] = $key;
        $data['secret'] = $secret;
        $data['contact_type'] = 'applyforopen';
        $result = $keyBindModel->save($data);

        if( $result )
        {
            //设置prism通知路由
            kernel::single('system_prism_notify')->setRouting($shopId);
        }

        return $result;
    }


    //申请key通过的时候调用这个方法，就可以添加好了
    public function create($name)
    {
        $type = 'openstandard';
        $keySecret = kernel::single('sysopen_prism')->create($type);

        $key = $keySecret['key'];
        $secret = $keySecret['secret'];

        $keyBindModel = app::get('sysopen')->model('develop');

        $keySdf = array(
            'key' => $key,
            'secret' => $secret,
            'name' => $name,
            'contact_type' => $type,
        );

        return $keyBindModel->save($keySdf);
    }

    private function __checkCreate($shop_id, $type)
    {
        $keyBindModel = app::get('sysopen')->model('keys');
        $shopKey = $keyBindModel->getRow(['shop_id'=>$shop_id]);
        if($shopKey['key'] != null)
            throw new LogicException('The shop has a key, please check the key.');
        else
            return true;
    }


}

