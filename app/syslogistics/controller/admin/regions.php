<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class syslogistics_ctl_admin_regions extends desktop_controller {

    var $workground = 'syslogistics.workground.logistics';

    /**
     * 展示所有地区
     * @params null
     * @return null
     */
    public function index()
    {
        $pagedata['areaMap'] = area::getMap();
        $pagedata['level'] = 2;
        return $this->page('syslogistics/delivery/area_treeList.html',$pagedata);
    }

    /**
     * 加载地区子节点
     */
    public function getChildNode()
    {
        $id = input::get('regionId');
        $data = area::getAreaIdPath()[$id];
        foreach( $data as $key)
        {
            $childData[$key] = area::areaKvdata()[$key];
            if( area::getAreaIdPath()[$key] )
            {
                $childData[$key]['is_child'] = true;
            }
        }
        $pagedata['step'] = input::get('level');
        $pagedata['level'] = input::get('level')+1;
        $pagedata['childData'] = $childData;
        return view::make('syslogistics/delivery/area_sub_treeList.html', $pagedata);
    }

    /**
     * 删除指定ID，地区
     */
    public function toRemoveArea()
    {
        $this->begin('?app=syslogistics&ctl=admin_regions&act=index');
        $id = input::get('regionId');
        if( empty($id) )
        {
            $this->end(false,app::get('syslogistics')->_('删除地区失败！'));
        }

        try
        {
            $this->adminlog("删除地区[id:{$id}]", 1);
            area::editArea('remove', $id);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            $this->end(false,$msg);
        }

        $this->end(true,app::get('syslogistics')->_('删除地区成功！'));
    }

    //编辑地区
    public function detailDlArea()
    {
        $id = input::get('regionId');
        $pagedata['name'] = area::getAreaNameById($id);
        $pagedata['regionId'] = $id;
        return view::make('syslogistics/delivery/area_edit.html',$pagedata);
    }

    //编辑地区名称
    public function saveDlArea()
    {
        $this->begin('?app=syslogistics&ctl=admin_regions&act=index');
        $id = input::get('regionId');
        $name = input::get('name');
        if( !trim($name) ){
          $this->end(false, app::get('syslogistics')->_('地区名称不能超为空!'));
        }
        try
        {
            $this->adminlog("编辑地区[name:{$name}][id:{$id}]", 1);
            area::editArea('update',$id,$name);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            $this->end(false,$msg);
        }

        $this->end(true, app::get('syslogistics')->_('修改成功'));
    }

    /**
     * 添加新地区界面
     * @params string 父级region id
     * @return null
     */
    public function showNewArea()
    {
        $id = input::get('regionId');
        $pagedata['parent']['name'] = area::getAreaNameById($id);
        $pagedata['parent']['id'] = $id;
        return view::make('syslogistics/delivery/area_new.html', $pagedata);
    }

    public function addDlArea()
    {
        $this->begin('?app=syslogistics&ctl=admin_regions&act=index');
        $parentId = input::get('parentId');
        $name = input::get('name');
        if( !trim($name) ){
          $this->end(false, app::get('syslogistics')->_('地区名称不能超为空!'));
        }
        try
        {
            $this->adminlog("新增地区[name:{$name}][parentId:{$parentId}]", 1);
            area::editArea('add', $parentId, $name);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            $this->end(false,$msg);
        }

        $this->end(true, app::get('syslogistics')->_('添加成功'));
    }

    public function resetFile()
    {
        $this->begin('?app=syslogistics&ctl=admin_regions&act=index');
        try{
            area::resetFile();
        }
        catch( LogicException $e)
        {
            $this->end(false,$e->getMessage());
        }
        $this->end(true,app::get('syslogistics')->_('保存成功！'));
    }

    public function init()
    {
        $this->begin('?app=syslogistics&ctl=admin_regions&act=index');
        $this->adminlog("初始化地区数据", 1);
        area::initFileContents();
        $this->end(true,app::get('syslogistics')->_('初始化成功！'));
    }
}
