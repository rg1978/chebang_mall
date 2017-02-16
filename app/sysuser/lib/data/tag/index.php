<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2014-2014 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class sysuser_data_tag_index
{

    /**
     * @brief 当添加的时候
     * @param int tagId 标签id
     * @param int userId 用户id
     *
     * @return bool
     */
    public function addIndex($tagId, $userId)
    {
        logger::debug('add tag search index :' . json_encode(['tagId'=>$tagId, 'userId'=>$userId]));
        return redis::scene('sysuser')->zadd(
            $this->__genKeyId($tagId),
            $userId,
            $userId
        );
    }

    /**
     * @brief 删除某个tag关于某个会员的索引
     * @param int tagId 标签id
     * @param int userId 用户id
     *
     * @return bool
     */
    public function rmIndex($tagId, $userId)
    {
        logger::debug('rm tag search index :' . json_encode(['tagId'=>$tagId, 'userId'=>$userId]));
        return redis::scene('sysuser')->zrem(
            $this->__genKeyId($tagId),
            $userId
        );
    }

    /**
     * @brief 这个适用于全部id一次性更换的逻辑。就是会员的tag绑定关系进行set的时候处理的
     * @param int userId 用户id
     * @param int tagId 标签id
     * @param int oldTagIds 旧的标签id
     *
     * @return bool
     */
    public function changeIndexByUserId($userId, $tagIds, $oldTagIds)
    {
        foreach($tagIds as $tagId)
        {
            if(!in_array($tagId, $oldTagIds))
            {
                $this->addIndex($tagId, $userId);
            }
        }

        foreach($oldTagIds as $tagId)
        {
            if(!in_array($tagId, $tagIds))
            {
                $this->rmIndex($tagId, $userId);
            }
        }

        return true;
    }

    /**
     * @brief 清除某个tag的索引。这个是用于以后重建索引脚本的
     * @param int tagId 标签id
     *
     * @return bool
     */
    public function destoryIndex($tagId)
    {
        return redis::scene('sysuser')->del(
            $this->__genKeyId($tagId)
        );
    }

    /**
     * @brief 查找某个tad对应的会员
     * @param int tagId 标签id
     * @param int start 从角标是几的会员开始查
     * @param int stop 到角标是几的会员结束查询
     *
     * @return []int userIds 会员id的数组
     */
    public function searchByTagId($tagId, $start, $stop)
    {
        return redis::scene('sysuser')->zrange(
            $this->__genKeyId($tagId),
            $start, $stop
        );
    }

    /**
     * @brief 统计某个tad对应的会员
     * @param int tagId 标签id
     * @param int min 从会员id是几开始
     * @param int max 到会员id是几结束
     *
     * @return []int userIds 会员id的数组
     */
    public function countByTagId($tagId, $min=0, $max=99999999)
    {
        return redis::scene('sysuser')->zcount(
            $this->__genKeyId($tagId),
            $min, $max
        );

    }

    //TODO 清空所有的索引
    public function clearIndex()
    {
        $tags = kernel::single('sysuser_data_tag')->getFormatAllTags();
        foreach($tags as $tag)
        {
            $this->destoryIndex($tag['tag_id']);
        }
        return true;
    }

    //TODO 新建索引
    public function buildIndex()
    {
        $pageSize = 100;
        $userCount = app::get('sysuser')->model('user')->count();
        $pageNumMax = $userCount/$pageSize + 1;
        for($pageNum = 1; $pageNum <= $pageNumMax ; $pageNum++)
        {
            $setoff = ($pageNum - 1) * $pageSize;
            $limit = $pageSize;

            $users = app::get('sysuser')->model('user')->getList('user_id', [], $setoff, $limit);
            $userIds = array_column($users, 'user_id');
            $tags = kernel::single('sysuser_data_tag')->getTagByUsers($userIds);
            foreach($tags as $uid=>$tids)
            {
                foreach($tids as $tid)
                {
                    $this->addIndex($tid, $uid);
                }
            }
        }
    }

    //TODO 重建索引
    public function rebuildIndex()
    {
        $this->clearIndex();
        $this->buildIndex();

        return true;
    }

    /**
     * @brief 统计某些tad对应的会员
     * @param []int tagIds 标签id的数组
     * @param int start 从角标是几的会员开始查
     * @param int stop 到角标是几的会员结束查询
     *
     * @return []int userIds 会员id的数组
     * @TODO 这里暂时不敢开放，因为担心滥用多标签搜索，会导致跨索引搜索重建索引的性能吃不消
     */
    public function searchByTagIdsUserInter($tagIds, $start, $stop)
    {
        $unionId = $this->__genUnionKey($tagIds);
        $param_arr = [$unionId];
        $param_arr[] = count($tagIds);
        foreach($tagIds as $tagId)
        {
            $param_arr[] = $this->__genKeyIdForArgument($tagId, 'sysuser:');
        }
        redis::scene('sysuser')->zinterstore( ...$param_arr);
        return redis::scene('sysuser')->zrange(
            $unionId,
            $start, $stop
        );

    }

    /**
     * @brief 生成索引的key
     * @param int tagId 标签id
     *
     * @return string redis_key 索引在redis里面的key
     */
    private function __genKeyId($tagId)
    {
        if($tagId == '')
            throw new LogicException(app::get('sysuser')->_('标签ID不能为空。'));
        return 'search:tags:' . $tagId;
    }

    /**
     * @brief 生成作为参数的redis-key
     * @param int tagId 标签id
     * @param string prefix 前缀
     *
     * @return string redis_key 索引在redis里面的key
     */
    private function __genKeyIdForArgument($tagId, $prefix = '')
    {
        if($tagId == '')
            throw new LogicException(app::get('sysuser')->_('标签ID不能为空。'));
        return $prefix . 'search:tags:' . $tagId;
    }

    /**
     * @brief 生成联合索引的redis-key
     * @param []int tagIds 标签id数组
     *
     * @return string redis_key 索引在redis里面的key
     */
    private function __genUnionKey($tagIds)
    {
        if(!is_array($tagIds) && count($tagIds) == 0)
            throw new LogicException(app::get('sysuser')->_('标签ID必须多于1个'));
        return 'search:tmp:' . implode('_', $tagIds);
    }
}
