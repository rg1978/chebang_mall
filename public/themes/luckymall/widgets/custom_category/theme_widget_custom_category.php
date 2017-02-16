<?php

function theme_widget_custom_category(&$setting,&$env){
    // 判断是否首页
    $returnData = $setting;
    if (route::currentRouteName() == 'topc')
    {
        $returnData['isindex'] = true;
    }

    $cat_list = app::get('topc')->rpcCall('category.cat.get.list',array('fields'=>'cat_id,cat_name'));

    /*自定义内容插入*/
    foreach($setting['resetcat'] as $key=>$cat)
    {
        // 是否显示
        if($cat['show'])
        {
            $cat_list[$key]['show'] = $cat['show'];
        }
        // 一级分类logo和重定向链接
        if($cat['logo'])
        {
            $cat_list[$key]['newlogo'] = $cat['logo'];
        }
        if($cat['link'])
        {
            $cat_list[$key]['newlink'] = $cat['link'];
        }
        // 推荐的二级分类标题和对应的三级分类链接
        if($cat['recommend_lv2_title'])
        {
            $cat_list[$key]['recommend_lv2_title'] = $cat['recommend_lv2_title'];
        }
        if($cat['recommend_lv2_sub_links'])
        {
            $cat_list[$key]['recommend_lv2_sub_links'] = $cat['recommend_lv2_sub_links'];
        }
        // 一级分类非弹出直接展示的推荐分类标题和链接1
        if($cat['rec1_title'])
        {
            $cat_list[$key]['rec1_title'] = $cat['rec1_title'];
        }
        if($cat['rec1_link'])
        {
            $cat_list[$key]['rec1_link'] = $cat['rec1_link'];
        }
        // 一级分类非弹出直接展示的推荐分类标题和链接1
        if($cat['rec2_title'])
        {
            $cat_list[$key]['rec2_title'] = $cat['rec2_title'];
        }
        if($cat['rec2_link'])
        {
            $cat_list[$key]['rec2_link'] = $cat['rec2_link'];
        }
    }

    // 其他的自定义一级分类
    if($setting['custom'] && is_array($setting['custom'])){
        foreach($setting['custom'] as $k=>$v){
            $returnData['data']['custom'][] = $v;
        }
    }
// echo "<pre>";print_r($cat_list);exit;
    $returnData['data'] = $cat_list;

    return $returnData;
}

?>