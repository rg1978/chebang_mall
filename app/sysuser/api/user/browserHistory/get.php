<?php
/**
 * ShopEx licence
 * - user.browserHistory.set
 * - 获取指定消费者浏览商品历史纪录
 *
 * @copyright Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license   http://ecos.shopex.cn/ ShopEx License
 * @link      http://www.shopex.cn/
 * @author    shopex 2016-05-17
 */
class sysuser_api_user_browserHistory_get {

    public $apiDescription = "获取指定消费者浏览商品历史纪录";

    public function getParams()
    {
        $return['params'] = [
            'user_id' => ['type'=>'int', 'valid'=>'required|numeric', 'title'=>'用户ID', 'example'=>'24','desc'=>'商城用户的注册ID'],
        ];
        return $return;
    }

    /**
     * 获取指定消费者浏览商品历史纪录
     *
     * @desc 获取指定消费者浏览商品历史纪录
     *
     * @return array list 消费者浏览商品ID列表，最新浏览的排在最前面
     */
    public function get($params)
    {
        try{
            $data = kernel::single('sysuser_data_browserHistory')->getUserBrowserHistory($params['user_id']);
        }
        catch(\LogicException $e)
        {
            throw new \LogicException($e->getMessage());
        }

        $data = array_reverse($data);

        return ['itemIds' => $data];
    }
}
