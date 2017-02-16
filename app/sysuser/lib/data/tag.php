<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2014-2014 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class sysuser_data_tag{

    //获取所有的tag，并且格式化掉（其实就是每个tag以id为key）
    public function getFormatAllTags()
    {
        $tags = app::get('sysuser')->model('tag')->getList('*');

        $fmtTags = [];
        foreach($tags as $tag)
        {
            $tag['status'] = 0;
            $tagId = $tag['tag_id'];
            $fmtTags[$tagId] = $tag;
        }

        return $fmtTags;
    }

    //批量获取tag
    public function getTagByUsers($userIds = [])
    {
        if(count($userIds) == 0)
            throw new LogicException(app::get('sysuser')->_('用户数量不能为0'));

        $tags = [];
        foreach($userIds as $uid)
        {
            $tags[$uid] = kernel::single('sysuser_data_user_tag')->get($uid);
        }

        return $tags;
    }

    /**
     * status 0 没有会员与这个tag绑定
     * status 1 部分会员与这个tag绑定
     * status 2 每个会员与这个tag绑定
     **/
    public function getTagsWithStatus($userIds = [])
    {
        $tagsByUsers = $this->getTagByUsers($userIds);
        $fmtTags = $this->getFormatAllTags();

        //用一个标记表示这是否是第一次循环
        $flag = 0;
        foreach($tagsByUsers as $uid => $tags)
        {
            foreach($fmtTags as $tid => $fmtTag)
            {
                //如果是第一次，如果有这个标签，就给tag标记为全部选中
                if(in_array($tid, $tags) && $fmtTags[$tid]['status'] == 0 && $flag == 0)
                {
                    $fmtTags[$tid]['status'] = 2;
                }
                //如果不是第一次，如果有这个标签,并且以前是全部未选中，就给tag标记为部分选中
                elseif(in_array($tid, $tags) && $fmtTags[$tid]['status'] == 0 && $flag == 1)
                {
                    $fmtTags[$tid]['status'] = 1;
                }
                //如果以前是全部选中,但是这次没选中，就给他标记为部分选中
                elseif( ( !in_array($tid, $tags) ) && $fmtTags[$tid]['status'] == 2)
                {
                    $fmtTags[$tid]['status'] = 1;
                }

            }
            $flag = 1;
        }

        return $fmtTags;
    }

    /**
     *
     * @param userIds [id1, id2, id3, id4]
     * @param tags [[tag_id=>tid1, status=>0], [tag_id=>tid2, status=>1], [tag_id=>tid3, status=>2]]
     *
     */
    public function bindTagsForUsers($userIds, $tags)
    {

        $userTags = $this->getTagByUsers($userIds);
        $fmtUserTags = [];

        foreach($userTags as $uid=>$uts)
        {
            foreach($tags as $tis=>$tag)
            {
                if($tag['status'] == 0)
                {
                    continue;
                }
                elseif($tag['status'] == 2)
                {
                    $fmtUserTags[$uid][] = $tag['tag_id'];
                }
                elseif($tag['status'] == 1)
                {
                    if(in_array($tag['tag_id'], $uts))
                        $fmtUserTags[$uid][] = $tag['tag_id'];
                }
            }
        }

        foreach($userIds as $uid)
        {
            kernel::single('sysuser_data_user_tag')->set($uid, $fmtUserTags[$uid]);
        }

        return true;
    }

    public function saveTag($tag_name, $tag_color = '#cccccc', $tag_id=0)
    {
        $tag['tag_id']    = $tag_id;
        $tag['tag_name']  = trim($tag_name);
        $tag['tag_color'] = $tag_color;
        if(!$tag['tag_name'])
        {
            throw new LogicException(app::get('sysuser')->_('标签名称不能为空或空格'));
        }
        if(!$tag_id)
        {
            $count = app::get('sysuser')->model('tag')->count();
            if($count >= 1000)
                throw new LogicException('标签数量太多了，请删除无用的标签');
            $count = app::get('sysuser')->model('tag')->count(['tag_name'=>$tag_name]);
            if($count)
                throw new LogicException('标签'.$tag_name.'已存在！');
        }
        app::get('sysuser')->model('tag')->save($tag);

        $tid = app::get('sysuser')->model('tag')->getRow('tag_id', ['tag_name'=>$tag_name]);

        return $tid['tag_id'];
    }
}
