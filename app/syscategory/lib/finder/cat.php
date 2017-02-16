<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class syscategory_finder_cat{

    public $column_control = '操作';
    public $column_control_order = COLUMN_IN_HEAD;
    public function column_control(&$colList, $list)
    {
        if($list)
        {
            $ids = array_column($list, 'cat_id');

            $catInfoList = app::get('syscategory')->model('cat')->getList('cat_id,parent_id',array('cat_id'=>$ids));

            foreach($list as $k=>$row)
            {
                $catInfo= $catInfoList[$k];
                $returnValue  = '<a href=\'?app=syscategory&ctl=admin_cat&act=edit&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$catInfo['cat_id'].'&from_finder=1'.'\'" target="dialog::{title:\''.app::get('syscategory')->_('编辑分类').'\', width:550, height:300}">'.app::get('syscategory')->_('编辑').'</a>';
                $delUrl = '?app=syscategory&ctl=admin_cat&act=delPage&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$catInfo['cat_id'].'&parent_id='.$catInfo['parent_id'];
                $target = 'target="dialog::{title:\''.app::get('syscategory')->_('提示').'\', width:200, height:120}"';
                $returnValue .= ' | <a href="'.$delUrl.'" '.$target.'>'.app::get('syscategory')->_('删除').'</a>';
                $colList[$k] = $returnValue;
            }

        }
    }

    public $column_relation = '关联';
    public $column_relation_order = COLUMN_IN_HEAD;
    public $column_relation_width = 120;
    public function column_relation(&$colList, $list)
    {
        if($list)
        {
            $ids = array_column($list, 'cat_id');

            $catInfoList = app::get('syscategory')->model('cat')->getList('is_leaf',array('cat_id'=>$ids));

            foreach($list as $k=>$row)
            {
                $catInfo= $catInfoList[$k];
                //            $catInfo = app::get('syscategory')->model('cat')->getRow('is_leaf',array('cat_id'=>$row['cat_id']));
                if($catInfo['is_leaf']==1)
                {
                    $html  = '  <a href=\'?app=syscategory&ctl=admin_cat&act=relBrand&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['cat_id'].'&from_finder=1'.'\'" target="dialog::{title:\''.app::get('syscategory')->_('关联品牌').'\', width:800, height:480}">'.app::get('syscategory')->_('品牌').'</a>';
                    $html .= '  <a href=\'?app=syscategory&ctl=admin_cat&act=relProp&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['cat_id'].'&from_finder=1'.'\'" target="dialog::{title:\''.app::get('syscategory')->_('关联属性').'\', width:800, height:480}">'.app::get('syscategory')->_('属性').'</a>';
                    $html .= '  <a href=\'?app=syscategory&ctl=admin_cat&act=relParam&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['cat_id'].'&from_finder=1'.'\'" target="dialog::{title:\''.app::get('syscategory')->_('关联参数').'\', width:800, height:480}">'.app::get('syscategory')->_('参数').'</a>';
                }
                $colList[$k] = $html;

            }
        }
    }

    //二级类目
    public $column_guarantee = "保证金";
    public $column_guarantee_width = 150;
    public function column_guarantee(&$colList, $list)
    {
        if($list)
        {
            $ids = array_unique(array_column($list, 'parent_id'));
            $catInfoList = app::get('syscategory')->model('cat')->getList('guarantee_money,cat_id',array('cat_id'=>$ids));
            $catInfoList = array_bind_key($catInfoList,'cat_id');;
            foreach($list as $k=>$row)
            {
                $colList[$k] = $catInfoList[$row['parent_id']]['guarantee_money'];
            }
        }
    }

    //一级类目
    public $column_platform = "平台使用费";
    public $column_platform_width = 140;
    public function column_platform(&$colList, $list)
    {
        if($list)
        {
            $path = array_unique(array_column($list, 'cat_path'));
            foreach($path as $val)
            {
                $arr = explode(',',$val);
                $ids[] = $arr[1];
            }
            $ids = array_unique($ids);
            $catInfoList = app::get('syscategory')->model('cat')->getList('platform_fee,cat_id',array('cat_id'=>$ids));
            $catInfoList = array_bind_key($catInfoList,'cat_id');;
            foreach($list as $k=>$row)
            {
                $arr = explode(',',$row['cat_path']);
                $colList[$k] = $catInfoList[$arr[1]]['platform_fee'];
            }
        }
    }
}


