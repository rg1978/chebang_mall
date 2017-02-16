<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class toputil_im_plugin_wangwang implements toputil_im_interface
{
    var $url = 'http://amos.im.alisoft.com/msg.aw';


    public function getList($list)
    {
        $return = [];
        foreach($list as $k => $v)
        {
            $shop_id = $v['shop_id'];
            $type = $v['type'];
            $user_id = $v['user_id'];
            $params = $v['params'];
            $content = $v['content'];
            $return[$k] = $this->getRow($shop_id, $type, $content, $user_id, $params);
        }
        return $return;
    }

    public function getRow($shop_id, $type = 'default', $content, $user_id, $params)
    {
        if($shop_id == 'platform')
        {
            $wangwang = app::get('sysconf')->getConf('im.account.wangwang');
        }else{
            $shopdata = app::get('toputil')->rpcCall('shop.get',array('shop_id'=>$shop_id));
            $wangwang = $shopdata['wangwang'];
            if(empty($wangwang)) return null;
        }
        return $this->genHtml($wangwang, $content, $params);
    }

    public function genHtml($wangwang, $content, $params)
    {
        $queryArr = [
            'v' => 2,
            'uid' => $wangwang,
            'site' => 'cntaobao',
            's' => 11,
            'charset' => 'utf-8',
        ];
        $url = $this->url;
        $href = $url . '?' . http_build_query($queryArr);
        $class = $params['class'];
        $html = "<a class='$class' target='_blank' href='$href'>$content</a>";

        return $html;

    }

}
