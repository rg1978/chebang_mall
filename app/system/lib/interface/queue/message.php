<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


interface system_interface_queue_message{

    /**
     * 执行队列
     */
    public function fire();

    /**
     * 确认已经消费队列
     */
    public function ack();
}
