<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2014-2014 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysuser_data_user_tag
{

    /**
     * @param domain redis使用哪个scene
     */
    private $domain = 'sysuser';

    /**
     * @param set在redis上的key
     */
    private $setKey = 'tags';

    /**
     * @brief 保存会员的标签
     *
     * @param int iserId 会员id
     * @param array tags 会员拥有的标签
     *
     * @return bool
     */
    public function set($userId, $tags)
    {
        logger::debug('set user tags : ' . json_encode(['uid'=>$userId, 'tags'=>$tags]));
        $oldTags = $this->get($userId);
        redis::scene($this->__getDomain())->hset( $this->__getSetKey(), $this->__genKey($userId), json_encode($tags));
        kernel::single('sysuser_data_tag_index')->changeIndexByUserId($userId, $tags, $oldTags);
        return true;
    }

   /**
     * @brief 获取会员的标签
     *
     * @param int iserId 会员id
     *
     * @return array
     */
    public function get($userId)
    {
        logger::debug('get user tags : ' . json_encode(['uid'=>$userId]));
        $tags = redis::scene($this->__getDomain())->hget(
            $this->__getSetKey(),
            $this->__genKey($userId)
        );
        return json_decode($tags, 1);
    }

   /**
     * @brief 在会员的标签集中加一个标签
     *
     * @param int iserId 会员id
     * @param string tag 会员标签
     *
     * @return bool
     */
    public function add($userId, $tag)
    {
        $tags = $this->get($userId);
        $tags[] = $tag;

        return $this->set($userId, $tags);
    }

    /**
     * @brief 在会员的标签集中删除一个标签
     *
     * @param int iserId 会员id
     * @param string tag 会员标签
     *
     * @return bool
     */
    public function del($userId, $tag)
    {
        $tags = $this->get($userId);
        foreach($tags as $k=>$v)
            if($v == $tag)
                unset($tags[$k]);

        return $this->set($userId, $tags);
    }

   /**
     * @brief 清空会员的标签
     *
     * @param int iserId 会员id
     *
     * @return bool
     */
    public function clean($userId)
    {
        return $this->set($userId, []);
    }

    /**
     * @brief 获取redis的scene
     *
     * @return string
     */
    private function __getDomain()
    {
        return $this->domain;
    }

    /**
     * @brief 获取集合对应的key
     *
     * @return string
     */
    private function __getSetKey()
    {
        return $this->setKey;
    }


     /**
     * @brief 根据user生成对应的key
     *
     * @param int iserId 会员id
     *
     * @return string
     */
    private function __genKey($userId)
    {
        return $userId;
    }
}
