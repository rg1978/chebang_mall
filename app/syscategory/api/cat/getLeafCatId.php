<?php
// category.cat.get.leafCatId
class syscategory_api_cat_getLeafCatId{

    public $apiDescription = '根据任意类目id获取对应类目的叶子类目ID';

    public function getParams()
    {
        $return['params'] = array(
            'cat_id' => ['type'=>'int','valid'=>'int|required', 'description'=>app::get('syscategory')->_('类目id'),'default'=>'','example'=>'23'],
        );
        return $return;
    }

    /**
     * 一级类目、二级类目分别会返回其下的所有三级分类；三级分类则直接返回自己
     * @todo 由于一般分类数据不是特别多，这里查询性能影响稍小，有需要则改进
     * @param  array $params 入参
     * @return string 返回用逗号组合的类目id字符串，如 1,2,5 
     */
    public function getLeafCatId($params)
    {
        $catId = $params['cat_id'];
        $qb = app::get('syscategory')->database()->createQueryBuilder();
        $qb->select('cat_id')
            ->from('syscategory_cat')
            ->where("is_leaf=1 AND cat_path LIKE '%,{$catId},%'")
            ->orwhere("cat_id = {$catId}");
        $catIds = $qb->execute()->fetchAll();
        $catIds = array_column($catIds, 'cat_id');
        $data = implode(',', $catIds);

        return $data;
    }
}
