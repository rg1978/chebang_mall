<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class sysuser_ctl_admin_tag extends desktop_controller{

    public function index()
    {

        return $this->finder('sysuser_mdl_tag',array(
            'title' => app::get('sysuser')->_('会员标签列表'),
            'use_buildin_delete' => true,
            'actions'=>array(
                array(
                    'label'=>app::get('syscategory')->_('添加标签'),
                    'href'=>'?app=sysuser&ctl=admin_tag&act=edit','target'=>'dialog::{title:\''.app::get('syscategory')->_('添加标签').'\',width:500,height:350}'
                ),
            )
        ));
    }

    public function edit()
    {
        $tagId = input::get('tag_id');
        if($tagId > 0)
        {
            $tag = app::get('sysuser')->model('tag')->getRow('*', ['tag_id'=>$tagId]);
        }
        $pagedata['tag'] = $tag;
        return $this->page('sysuser/admin/tag/edit.html', $pagedata);
    }

    public function save()
    {
        $this->begin();
        try{
            $tag['tag_id']    = input::get('tag_id', 0);
            $tag['tag_name']  = input::get('tag_name');
            $tag['tag_color'] = input::get('tag_color');

            kernel::single('sysuser_data_tag')->saveTag($tag['tag_name'], $tag['tag_color'], $tag['tag_id']);
        }catch(Exception $e){
            return $this->end(false, $e->getMessage());
        }
        return $this->end(true, app::get('sysuser')->_('保存成功'));
    }

    public function bindTag()
    {
        if(input::get('isSelectedAll', false))
        {
            return $this->page('sysuser/admin/tag/bind.html', ['error'=>app::get('sysuser')->_('不支持选取所有项目')]);
        }

        $userIds = input::get('user_id', null);
        $pagedata['userIds'] = json_encode($userIds);
        $pagedata['userCount'] = count($userIds);
        $pagedata['tags'] = kernel::single('sysuser_data_tag')->getTagsWithStatus($userIds);
        $pagedata['res_url'] = app::get('sysuser')->res_url;
        return $this->page('sysuser/admin/tag/bind.html', $pagedata);
    }

    public function setTag()
    {
        $this->begin();
        try{
            $userIds = input::get('userIds');
            $userIds = json_decode($userIds, 1);
            $tags = input::get('tags');
            $newTags = input::get('newTags');
            foreach($newTags as $newTag)
            {
                $tid = kernel::single('sysuser_data_tag')->saveTag($newTag['tag_name'], '#cccccc');
                $tags[] = ['tag_id'=>$tid, 'status'=>2];
            }
            kernel::single('sysuser_data_tag')->bindTagsForUsers($userIds, $tags);
        }catch(Exception $e){
            return $this->end(false, $e->getMessage());
        }
        return $this->end(true, app::get('sysuser')->_('保存成功'));
    }

}

