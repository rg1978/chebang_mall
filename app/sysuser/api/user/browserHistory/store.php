<?php
/**
 * ShopEx licence
 * - user.browserHistory.set
 * - 存储消费者浏览商品历史纪录
 *
 * @copyright Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license   http://ecos.shopex.cn/ ShopEx License
 * @link      http://www.shopex.cn/
 * @author    shopex 2016-05-17
 */
class sysuser_api_user_browserHistory_store {

    public $apiDescription = "存储消费者浏览商品历史纪录";

    public function getParams()
    {
        $return['params'] = [
            'user_id' => ['type'=>'int', 'valid'=>'required|numeric', 'title'=>'用户ID', 'example'=>'24','desc'=>'商城用户的注册ID'],
            'itemIds' => [
                'type'=>'string', 'valid'=>'required', 'title'=>'浏览的商品ID', 'example'=>'140,30',
                'desc'=>'消费者浏览的商品ID，多个商品ID用,逗号隔开，最多存储5条商品数据,不可重复。数据由左到右代表由旧到新，最左的数据会最早被替换'
            ],
        ];
        return $return;
    }

    /**
     * 存储消费者浏览商品历史纪录
     *
     * @desc 存储消费者浏览商品历史纪录
     *
     * @return bool result true存储成功或者false失败
     */
    public function store($params)
    {
        try{
            $itemIds = explode(',',$params['itemIds']);
            kernel::single('sysuser_data_browserHistory')->store($params['user_id'], $itemIds);
        }
        catch(\LogicException $e)
        {
            throw new \LogicException($e->getMessage());
        }

        return true;
    }
}

