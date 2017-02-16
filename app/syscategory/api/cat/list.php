<?php
class syscategory_api_cat_list{
    public $apiDescription = "获取类目树形结构";
    public function getParams()
    {
        $return['params'] = array(
        );
        return $return;
    }
    public function getList($params)
    {
        return kernel::single('syscategory_data_cat')->getTree();
    }
}
