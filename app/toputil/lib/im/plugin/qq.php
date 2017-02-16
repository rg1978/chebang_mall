<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class toputil_im_plugin_qq implements toputil_im_interface
{
    var $url = 'http://wpa.qq.com/msgrd';

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
            $qq = app::get('sysconf')->getConf('im.account.qq');
        }else{
            $shopdata = app::get('toputil')->rpcCall('shop.get',array('shop_id'=>$shop_id));
            $qq = $shopdata['qq'];
            if(empty($qq)) return null;
        }
        return $this->genHtml($qq, $content, $params);
    }

    public function genHtml($qq, $content, $params)
    {
        $queryArr =[
            'v' => 3,
            'uin' => $qq,
            'site' => 'qq',
            'menu' => 'yes',
        ];
        $url = $this->url;
        $href = $url . '?' . http_build_query($queryArr);
        $class = $params['class'];
        $html = "<a target='_blank' class='$class' href='$href'>$content</a>";

        return $html;
    }
}
