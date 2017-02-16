<?php
class topwap_ctl_category extends topwap_controller{
    public function index()
    {
        $this->setLayoutFlag('category');
        $catList = app::get('topwap')->rpcCall('category.cat.get.list',array('fields'=>'cat_id,cat_name'));
        $pagedata['data'] = $catList;
        return $this->page('topwap/category/index.html',$pagedata);
    }
}
