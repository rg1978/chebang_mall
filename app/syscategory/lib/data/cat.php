<?php
/**
 * 分类api数据
 */
class syscategory_data_cat {

    public function __construct()
    {
        $this->objMdlCat = app::get('syscategory')->model('cat');
    }

    public function toSave($category)
    {
        $result = $this->__toSave($category);
        event::fire('category.save', ['category'=>$category]);
        return $result;
    }

    private function __toSave($category)
    {
        $result = $this->objMdlCat->save($category);
        if(!$result)
            throw new RuntimeException(app::get('syscategory')->_('保存失败'));
        return true;
    }

    /**
     * 删除分类
     * @param  int $catId 分类id
     * @return bool
     */
    public function toRemove($catId)
    {
        $result = $this->__toRemove($catId);
        event::fire('category.remove', ['catId'=>$catId]);
        return $result;
    }

    /**
     * 删除分类
     * @param  int $catId 分类id
     * @return bool
     */
    private function __toRemove($catId)
    {
        $aCats = $this->objMdlCat->getRow('cat_id', array('parent_id'=>intval($catId)));
        if($aCats)
        {
            $msg = '删除失败：本分类下面还有子分类';
            throw new \LogicException($msg);
            return false;
        }

        $searchParams['page_no'] = 1;
        $searchParams['page_size'] = 1;
        $searchParams['cat_id'] = intval($catId);
        $searchParams['fields'] = 'item_id';
        $itemsList = app::get('syscategory')->rpcCall('item.search',$searchParams);
        if($itemsList['total_found'] > 0 )
        {
            $msg = '删除失败：本分类下面还有商品';
            throw new \LogicException($msg);
            return false;
        }

        //判断该类目下是否有店铺
        $shopParams = ['cat_id' => intval($catId),'page_no'=>1,'page_size'=>1];
        $shop = app::get('syscategory')->rpcCall('shop.get.by.cat',$shopParams);
        if(isset($shop['list']) && $shop['list'])
        {
            $msg = '删除失败：本类目下面有签约店铺';
            throw new \LogicException($msg);
            return false;
        }

        //判断该类目下是否有为开店的入驻申请
        //$applyParams = ['cat_id' => implode(',',$catId),'page_no'=>1,'page_size'=>1];
        //$apply = app::get('syscategory')->rpcCall('apply.get.by.cat',$applyParams);
        //if($apply)
        //{
        //    $msg = '删除失败：本分类下面有入住申请关联';
        //    throw new \LogicException($msg);
        //    return false;
        //}

        $parentRow = $this->objMdlCat->getRow('parent_id', array('cat_id'=>intval($catId)));

        $db = app::get('syscategory')->database();
        $db->beginTransaction();
        try
        {
            $result = $this->objMdlCat->database()->delete('syscategory_cat', ['cat_id' => $catId], [\PDO::PARAM_INT]);
            if(!$result) throw new \LogicException("删除类目失败");

            if($parentRow['parent_id'])
            {
                $result = $this->objMdlCat->database()->executeUpdate('UPDATE syscategory_cat SET child_count = child_count-1 WHERE cat_id=?', [$parentRow['parent_id']], [\PDO::PARAM_INT]);
                if(!$result) throw new \LogicException("更新父级下的子级数量失败");
            }

            $objMdlProps = app::get('syscategory')->model('cat_rel_prop');
            $relProp = $objMdlProps->getRow('prop_id',array('cat_id'=>$catId));
            if($relProp)
            {
                $result = $objMdlProps->delete(array('cat_id'=>$catId));
                if(!$result) throw new \LogicException("删除关联关系失败");
            }
            $db->commit();
        }
        catch(\LogicException $e)
        {
            $db->rollback();
            throw new \LogicException($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 更新分类排序
     * @param  array $sortData 分类排序数组 array('order_sort'=>array($cat_id=>$sort_number,......))
     * @param  string $msg 返回错误信息
     * @return bool
     */
    public function updateSort($sortData)
    {
        foreach( $sortData as $k => $v )
        {
            $this->objMdlCat->update( array('order_sort'=>($v==='' ? null : $v)), array('cat_id'=>$k) );
        }
        return kernel::single('syscategory_data_cat')->cleanCatsCache();
    }

    /**
     * 获取分类的全部数据
     * @param string fields 数据结构
     *
     * @return list
     */
    public function getAll()
    {
        return $this->objMdlCat->getCatList(true);
    }

    /**
     * 获取分类的树形结构数据
     * @param string fields 数据结构
     *
     * @return tree
     */
    public function getTree()
    {
        return $this->makeCatsCache();
    }

    // 获取分类列表并生成分类树数组
    private function getAndGenTree()
    {
        set_time_limit(2000);
        $data = app::get('syscategory')->model('cat')->getList('cat_id,parent_id,cat_name,cat_logo,cat_path,level,is_leaf,child_count,order_sort');
        $list = $this->genTree($data);

        return $list;
    }

    // 生成分类三维数组
    private function genTree(&$data, $pId)
    {
        $tree = '';
        foreach($data as $k => $v)
        {
           if($v['parent_id'] == $pId)
           {
                if($v['level']==1){
                    $v['lv2'] = $this->genTree($data, $v['cat_id']);
                }
                if($v['level']==2){
                    $v['lv3'] = $this->genTree($data, $v['cat_id']);
                }
                $tree[] = $v;
           }
        }
        return $tree;
    }

    public function makeCatsCache()
    {
        return cache::store('misc')->rememberForever('categorys', function() {
            return $this->getAndGenTree();
        });
    }

    public function cleanCatsCache()
    {
        cache::store('misc')->forget('categorys');
    }

}


