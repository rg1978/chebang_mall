<?php
class syscategory_api_cat_getData{

    public $apiDescription = "获取指定3级类目的信息以及该父级的所有结构";
    public function getParams()
    {
        $return['params'] = array(
            'cat_id' => ['type'=>'string','valid'=>'int|required', 'description'=>'类目id','default'=>'','example'=>'23'],
            'fields' => ['type'=>'field_list','valid'=>'', 'description'=>'获取类目的指定字段','default'=>'cat_name,level,cat_id','example'=>'cat_name,cat_id'],
        );
        return $return;
    }
    public function getList($params)
    {
        $catId = $params['cat_id'];
        $row = "cat_id,cat_name";
        if($params['fields'])
        {
            $row = $params['fields'];
        }
        $row = str_append($row,'level,cat_path,parent_id');

        $objCatMdl = app::get('syscategory')->model('cat');
        $data = array();
        $cat = $objCatMdl->getRow($row,['cat_id'=>$catId]);

        if($cat['level'] == 1)
        {
            $data['lv1'] = $cat;
        }
        elseif($cat['level'] == 2)
        {

            $catLv1 = $objCatMdl->getRow($row,['cat_id'=>$cat['parent_id']]);
            $data['lv1'] = $catLv2;
            $data['lv2'] = $cat;
        }
        elseif($cat['level'] == 3)
        {

            $catIds = array_filter(explode(',',$cat['cat_path']));
            $list = $objCatMdl->getList($row,['cat_id'=>$catIds]);
            foreach($list as $key=>$value)
            {
                if($value['level'] == 1)
                {
                    $data['lv1'] = $value;
                }
                if($value['level'] == 2)
                {
                    $data['lv2'] = $value;
                }
            }
            $data['lv3'] = $cat;
        }
        return $data;
    }
}
