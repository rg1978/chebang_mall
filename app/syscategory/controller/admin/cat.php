<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class syscategory_ctl_admin_cat extends desktop_controller {

    public $workground = 'syscategory.workground.category';

    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    public function index()
    {
        $objMdlCat = app::get('syscategory')->model('cat');

        $tree = $objMdlCat->getCatList();
        $pagedata['tree_number'] = count($tree);
        if($tree)
        {
            foreach($tree as $k=>$v)
            {
                $tree[$k]['link'] = array('cat_id' => array(
                    'v' => $v['cat_id'],
                    't' => app::get('syscategory')->_('商品类别').app::get('syscategory')->_('是').$v['cat_name']
                ));
            }
        }

        $pagedata['tree'] = $tree;
        return $this->page('syscategory/admin/category/map.html', $pagedata);
    }

    /**
     * 三级(叶子)类目列表页
     * @return
     */
    public function leaf()
    {
        $parentId = input::get('parent_id');
        if(!$parentId) return false;
        $objMdlCat = app::get('syscategory')->model('cat');
        $level2 = $objMdlCat->getRow('cat_name,parent_id,level',array('cat_id'=>intval($parentId)));
        if($level2['level'] != '2')
        {
            $msg = '只能查看二级分类下的三级分类！';
            throw new \LogicException($msg);
        }
        $level1 = $objMdlCat->getRow('cat_name',array('cat_id'=>$level2['parent_id']));
        return $this->finder('syscategory_mdl_cat', array(
            'title' => app::get('syscategory')->_('三级分类('.$level1['cat_name'].'->'.$level2['cat_name'].')'),
            'use_buildin_delete'=>false,
            'base_filter' => array('parent_id' => $parentId),
        ));
    }

    /**
     * 添加分类
     * @param integer $nCatId 分类id
     */
    public function add($nCatId = 0)
    {
        $objMdlCat = app::get('syscategory')->model('cat');

        $catList[0] = array('cat_id'=>0, 'cat_name'=>app::get('syscategory')->_('----无----'), 'step'=>1);

        $catInfo = $objMdlCat->getRow('cat_path, level, cat_name, cat_id, parent_id', array('cat_id'=>$nCatId));
        if($catInfo['level']=='1')
        {
            $catList[1] = array('cat_id'=>$catInfo['cat_id'],'cat_name'=>$catInfo['cat_name'], 'step'=>'1');
        }
        if($catInfo['level']=='2')
        {
            $pagedata['cat']['level'] = 3;
            $level1CatInfo = $objMdlCat->getRow('cat_path, level, cat_name, cat_id, parent_id', array('cat_id'=>$catInfo['parent_id']));
            $catList[1] = array('cat_id'=>$level1CatInfo['cat_id'], 'cat_name'=>$level1CatInfo['cat_name'], 'step'=>1);
            $catList[2] = array('cat_id'=>$catInfo['cat_id'], 'cat_name'=>$catInfo['cat_name'], 'step'=>2);
        }

        $pagedata['cat']['parent_id'] = $nCatId;
        if(!$nCatId)
        {
            $pagedata['cat']['level'] = 1;
        }
        $pagedata['cat']['level'] = $catInfo['level']+1;
        $pagedata['catList'] = $catList;
        return view::make('syscategory/admin/category/info.html', $pagedata);
    }

    /**
     * 编辑分类
     * @param  integer  $nCatId  分类id
     * @param  integer $is_leaf 是否叶子节点
     * @return
     */
    public function edit($nCatId, $is_leaf=0)
    {
        $pagedata['is_leaf'] = input::get('is_leaf');

        $objMdlCat = app::get('syscategory')->model('cat');

        $catInfo = $objMdlCat->getRow('cat_path, level, cat_name, cat_logo, cat_id, parent_id, guarantee_money, platform_fee, cat_service_rates, order_sort,cat_template', array('cat_id'=>$nCatId));

        // 组织上级分类数据，1级分类显示无，2级分类显示1级分类,3级分类则显示对应的2级和1级分类
        $catList[0] = array('cat_id'=>0, 'cat_name'=>app::get('syscategory')->_('----无----'), 'step'=>1);
        if($catInfo['level']=='2')
        {
            $level1CatInfo = $objMdlCat->getRow('cat_path, level, cat_name, cat_id, parent_id, guarantee_money, platform_fee, cat_service_rates, order_sort', array('cat_id'=>$catInfo['parent_id']));
            $catList[1] = array('cat_id'=>$level1CatInfo['cat_id'], 'cat_name'=>$level1CatInfo['cat_name'], 'step'=>1);
        }
        if($catInfo['level']=='3')
        {
            $level2CatInfo = $objMdlCat->getRow('cat_path, level, cat_name, cat_id, parent_id, guarantee_money, platform_fee, cat_service_rates, order_sort', array('cat_id'=>$catInfo['parent_id']));
            $level1CatInfo = $objMdlCat->getRow('cat_path, level, cat_name, cat_id, parent_id, guarantee_money, platform_fee, cat_service_rates, order_sort', array('cat_id'=>$level2CatInfo['parent_id']));
            $catList[1] = array('cat_id'=>$level1CatInfo['cat_id'], 'cat_name'=>$level1CatInfo['cat_name'], 'step'=>1);
            $catList[2] = array('cat_id'=>$level2CatInfo['cat_id'], 'cat_name'=>$level2CatInfo['cat_name'], 'step'=>2);
        }
        $pagedata['catList'] = $catList;

        $pagedata['cat']['cat_id'] = $catInfo['cat_id'];
        $pagedata['cat']['cat_logo'] = $catInfo['cat_logo'];
        $pagedata['cat']['cat_name'] = $catInfo['cat_name'];
        $pagedata['cat']['parent_id'] = $catInfo['parent_id'];
        $pagedata['cat']['order_sort'] = $catInfo['order_sort'];
        $pagedata['cat']['level'] = $catInfo['level'];
        $pagedata['cat']['guarantee_money'] = $catInfo['guarantee_money'];
        $pagedata['cat']['platform_fee'] = $catInfo['platform_fee'];
        $pagedata['cat']['cat_service_rates'] = $catInfo['cat_service_rates'];
        $pagedata['cat']['cat_template'] = $catInfo['cat_template'];

        return view::make('syscategory/admin/category/info.html', $pagedata);
    }

    /**
     * 保存节点信息
     * @return
     */
    public function save()
    {
        $postData = $_POST;
        if($postData['is_leaf'])
        {
            $this->begin('?app=syscategory&ctl=admin_cat&act=leaf&parent_id='.$postData['cat']['parent_id']);
        }
        else
        {
            $this->begin('?app=syscategory&ctl=admin_cat&act=index');
        }

        if( $postData['cat']['order_sort'] === '' )
        {
            $postData['cat']['order_sort'] = 0;
        }
        if(!trim($postData['cat']['cat_name']))
        {
            $this->end(false, app::get('syscategory')->_('分类名称不能超为空!'));
        }

        if( mb_strlen($postData['cat']['cat_name'])>100)
        {
            $this->end(false, app::get('syscategory')->_('分类名称不能超过100个字符!'));
        }
        $objMdlCat = app::get('syscategory')->model('cat');

        $catInfo = $objMdlCat->getRow('cat_id', array('cat_name'=>$postData['cat']['cat_name'],'parent_id'=>$postData['cat']['parent_id']));
        if( $catInfo && intval($postData['cat']['cat_id']) != $catInfo['cat_id'] )
        {
            $this->end(false, app::get('syscategory')->_('同级分类下名称不能重复!'));
        }
        else
        {
            try{
                kernel::single('syscategory_data_cat')->toSave($postData['cat']);
                $this->adminlog("添加、编辑商品类目[ID:{$postData['cat']['cat_name']}]", 1);
                $this->end(true, app::get('syscategory')->_('保存成功'));
            } catch(Exception $e) {
                $this->adminlog("添加、编辑商品类目[ID:{$postData['cat']['cat_name']}]", 0);
                $this->end(false, app::get('syscategory')->$e->getMessage());
            }
        }

    }

    public function delPage($nCatId){
        $pagedata['nCatId'] = $nCatId;
        $parentId = input::get('parent_id');
        $pagedata['parent_id'] = $parentId;
        $pagedata['from_finder'] = $_GET['from_finder'];
        return $this->page('syscategory/admin/cat_rel/cat_rel_del.html', $pagedata);
    }
    /**
     * 删除分类(一级和二级)
     * @param  int $nCatId 分类id
     * @return
     */
    public function toRemove()
    {
        $nCatId = input::get('nCatId');
        if($_GET['from_finder'])
        {
            $this->begin('?app=syscategory&ctl=admin_cat&act=leaf&parent_id='.input::get('parent_id'));
        }
        else
        {
            $this->begin('?app=syscategory&ctl=admin_cat&act=index');
        }
        try
        {
            $params['cat_id'] = intval($nCatId);
            $flag = app::get('syscategory')->rpcCall('category.cat.remove',$params);
            $this->adminlog("删除商品类目[ID:{$nCatId}]", 1);

        }
        catch(\LogicException $e)
        {
            $this->adminlog("删除商品类目[ID:{$nCatId}]", 0);
            $msg = $e->getMessage();
            $this->end(false,$msg);
        }

        $objMdlCat = app::get('syscategory')->model('cat');
        $catInfo = $objMdlCat->getRow('cat_name', array('cat_id'=>intval($nCatId)));

        $this->end(true, $catInfo['cat_name'].app::get('syscategory')->_('已删除'));
    }

    /**
     * 主要用用于更新菜单排序
     * @return
     */
    public function updateSort()
    {
        $postData = $_POST['order_sort'];
        $this->begin('?app=syscategory&ctl=admin_cat&act=index');
        $objLibCat = kernel::single('syscategory_data_cat');
        $objLibCat->updateSort($postData);
        $this->adminlog("更新商品类目排序", 1);
        $this->end(true, app::get('syscategory')->_('更新排序操作完成'));
    }

    /**
     * 判断是否是三级节点，即叶子节点
     * @param  int  $cat_id 分类id(三级分类)
     * @param  int $parent_id 返回三级分类父类id
     * @return boolean
     */
    public function isLeafCat($cat_id, &$parent_id)
    {
        if(!$cat_id) return false;

        $objMdlCat = app::get('syscategory')->model('cat');
        $catInfo = $objMdlCat->getRow('is_leaf, parent_id', array('cat_id'=>$cat_id));
        $parent_id = $catInfo['parent_id'];
        return $catInfo['is_leaf'] ? true : false;
    }

    /**
     * 编辑三级分类关联的品牌页面
     * @param  int $cat_id 三级分类id
     * @return
     */
    public function relBrand($cat_id)
    {
        if(!$cat_id) return false;

        $objMdlCat = app::get('syscategory')->model('cat');
        $leafCatInfo = $objMdlCat->getRow('cat_name,cat_path,is_leaf',array('cat_id'=>intval($cat_id)));
        if(!$leafCatInfo['is_leaf'])
        {
            $msg = '只有叶子节点(三级分类)才能关联品牌';
            throw new \LogicException($msg);
        }
        $levelCatInfo = $objMdlCat->getList('cat_name',array('cat_id'=>explode(',', $leafCatInfo['cat_path'])));
        $prevsCatName = array_column($levelCatInfo, 'cat_name');
        $pagedata['nav_catname'] = implode('->', $prevsCatName).'->'.$leafCatInfo['cat_name'];

        $objMdlCatRelBrand = app::get('syscategory')->model('cat_rel_brand');
        $brandData = $objMdlCatRelBrand->getList('brand_id',array('cat_id'=>$cat_id));
        $checkedBrand = array();
        foreach ($brandData as $v)
        {
            $checkedBrand[$v['brand_id']] = $v['brand_id'];
        }
        $pagedata['checkedBrand'] = $checkedBrand;
        $pagedata['cat_id'] = $cat_id;
        $pagedata['from_finder'] = $_GET['from_finder'];

        return $this->page('syscategory/admin/cat_rel/cat_rel_brand.html', $pagedata);
    }

    /**
     * 保存三级分类关联的品牌
     * @return
     */
    public function saveRelBrand()
    {
        $postData = $_POST;

        if(!$this->isLeafCat($postData['cat_id'], $parent_id))
        {
            $msg = '只有三级分类才能添加编辑关联品牌';
            throw new \LogicException($msg);
        }

        if($_GET['from_finder'])
        {
            $this->begin('?app=syscategory&ctl=admin_cat&act=leaf&parent_id='.$parent_id);
        }
        else
        {
            $this->begin('?app=syscategory&ctl=admin_cat&act=index');
        }
        if(!$postData['checkedBrand']) $postData['checkedBrand'] = null;
        $objCatRelBrand = app::get('syscategory')->model('cat_rel_brand');
        $brandData = $objCatRelBrand->getList("brand_id",array('cat_id'=>$postData['cat_id']));
        if($brandData)
        {
            //获取该三级分类的一级分类id
            $objMdlCat = app::get('syscategory')->model('cat');
            $catData = $objMdlCat->getList('cat_path',['cat_id'=>$postData['cat_id']]);
            $catId = array_filter(explode(',',$catData[0]['cat_path']));
            if(count($catId) == 2)
            {
                $lv1CatId = $catId[1];

                $newBrand = $postData['checked_brand_ids'];
                foreach($brandData as $value)
                {
                    if(!in_array($value['brand_id'],$newBrand))
                    {
                        $oldBrand[] = $value['brand_id'];
                    }
                }
                if($oldBrand)
                {
                    $shopbrand = app::get('syscategory')->rpcCall('shop.get.by.brand',array('brand_id'=>implode(',',$oldBrand)));
                    $brand = array_column($shopbrand['list'],'shop_id');
                    $shopcat = app::get('syscategory')->rpcCall('shop.get.by.cat',array('cat_id'=>$lv1CatId));
                    $cat = array_column($shopcat['list'],'shop_id');
                    $shop = array_intersect($brand,$cat);
                    if($shop)
                    {
                        $this->end(false, app::get('syscategory')->_('关联品牌失败，您取消关联的品牌有开通的店铺，不能取消关联'));
                    }
                }
            }
        }

        $objCatRelBrand->delete(array('cat_id'=>$postData['cat_id']));
        foreach ($postData['checked_brand_ids'] as $brand_id)
        {
            $data = array('cat_id'=>$postData['cat_id'], 'brand_id'=>$brand_id);
            $objCatRelBrand->save( $data );
        }
        $this->adminlog(" 保存三级分类关联的品牌[分类ID:{$postData['cat_id']}]", 1);
        $this->end(true, app::get('syscategory')->_('操作成功'));
    }

    /**
     * 编辑三级分类关联的属性页面
     * @param  int $cat_id 三级分类id
     * @return
     */
    public function relProp($cat_id)
    {
        if(!$cat_id) return false;

        $objMdlCatRelProp = app::get('syscategory')->model('cat_rel_prop');
        $objMdlProps = app::get('syscategory')->model('props');

        $catRelPropInfo = $objMdlCatRelProp->getList('*',array('cat_id'=>$cat_id),0,-1,'order_sort ASC');
        $props = array();
        foreach ($catRelPropInfo as $v)
        {
            $props[$v['prop_id']] = $v;
            $propsInfo = $objMdlProps->getRow('prop_name,prop_memo,prop_type',array('prop_id'=>$v['prop_id']));
            $props[$v['prop_id']]['prop'] = $propsInfo;
        }

        $pagedata['props'] = $props;
        $pagedata['cat_id'] = $cat_id;
        $pagedata['from_finder'] = $_GET['from_finder'];

        return $this->page('syscategory/admin/cat_rel/cat_rel_prop.html', $pagedata);
    }

    /**
     * 保存三级分类关联的属性
     * @return
     */
    public function saveRelProp()
    {
        $postData = $_POST;

        if(!$this->isLeafCat($postData['cat_id'], $parent_id))
        {
            $msg = '只有三级分类才能添加编辑关联属性';
            throw new \LogicException($msg);
        }

        if($_GET['from_finder'])
        {
            $this->begin('?app=syscategory&ctl=admin_cat&act=leaf&parent_id='.$parent_id);
        }
        else
        {
            $this->begin('?app=syscategory&ctl=admin_cat&act=index');
        }

        if(!$postData['prop'])
        {
            $objMdlCatRelProp = app::get('syscategory')->model('cat_rel_prop');
            $objMdlCatRelProp->delete(['cat_id'=>$postData['cat_id']]);
            $this->end(true, app::get('syscategory')->_('操作成功'));
        }

        $prop = array();
        $propIds = array();

        foreach( $postData['prop']['prop_id'] as $k => $aProp )
        {
            $propIds[] = $aProp;
            $prop[] = array(
                'prop_id' => $aProp,
                'show_type' => $postData['prop']['show_type'][$k],
                'cat_id' => $postData['cat_id'],
                'order_sort' => $k
            );
        }
        $objMdlProps = app::get('syscategory')->model('props');
        $propInfoList = $objMdlProps->getList('prop_id, prop_type', array('prop_id'=>$propIds));

        $nature_num = 0; //统计自然属性数量
        $spec_num = 0; //统计销售属性数量
        foreach ($propInfoList as $v)
        {
            if($v['prop_type']=='nature')
            {
                $nature_num++;
            }
            if($v['prop_type']=='spec')
            {
                $spec_num++;
            }
        }
        if( $nature_num>10 )
        {
            $msg = '最多关联10个自然属性';
            throw new \LogicException($msg);
        }
        if( $spec_num>3 )
        {
            $msg = '最多关联三个销售属性';
            throw new \LogicException($msg);
        }
        $saveData['cat_id'] = $postData['cat_id'];
        $saveData['prop'] = $prop;
        $objMdlCat = app::get('syscategory')->model('cat');
        $objMdlCat->save($saveData);
        $this->adminlog(" 保存三级分类关联的属性[分类ID:{$postData['cat_id']}]", 1);
        $this->end(true, app::get('syscategory')->_('操作成功'));
    }

    /**
     * 编辑三级分类关联的参数页面
     * @param  int $cat_id 三级分类id
     * @return
     */
    public function relParam($cat_id)
    {
        if(!$cat_id) return false;

        $objMdlCat = app::get('syscategory')->model('cat');
        $params = $objMdlCat->getRow('params',array('cat_id'=>$cat_id));
        $pagedata['params'] = $params['params'];
        $pagedata['cat_id'] = $cat_id;
        $pagedata['from_finder'] = $_GET['from_finder'];

        return $this->page('syscategory/admin/cat_rel/cat_rel_params.html', $pagedata);
    }

    /**
     * 保存三级分类关联的参数
     * @return
     */
    public function saveRelParam()
    {
        $postData = $_POST;
        if(!$this->isLeafCat($postData['cat_id'], $parent_id))
        {
            $msg = '只有三级分类才能添加编辑关联参数';
            throw new \LogicException($msg);
        }

        if($_GET['from_finder'])
        {
            $this->begin('?app=syscategory&ctl=admin_cat&act=leaf&parent_id='.$parent_id);
        }
        else
        {
            $this->begin('?app=syscategory&ctl=admin_cat&act=index');
        }

        $params = array();
        foreach( $postData['params'] as $aParams )
        {
            if( !$aParams['name'] )
            {
                $msg = app::get('syscategory')->_('请为参数表中参数组添加参数名');
                $this->end(false, $msg);
                break;
            }
            $paramsItem = array();
            foreach( $aParams['name'] as $piKey => $piName )
            {
                if(!$piName)
                {
                    $msg = app::get('syscategory')->_('请完成参数表中参数名');
                    $this->end(false, $msg);
                    break 2;
                }
                $paramsItem[$piName] = $aParams['alias'][$piKey];
            }
            if(!$aParams['group'])
            {
                $msg = app::get('syscategory')->_('请完成参数表中参数组名称');
                $this->end(false, $msg);
                break;
            }
            $params[$aParams['group']] = $paramsItem;
        }
        $saveData['cat_id'] = $postData['cat_id'];
        $saveData['params'] = $params;
        $objMdlCat = app::get('syscategory')->model('cat');
        $objMdlCat->save($saveData);
        $this->adminlog(" 保存三级分类关联的参数[分类ID:{$postData['cat_id']}]", 1);
        $this->end(true, app::get('syscategory')->_('操作成功'));
    }

    function checkCatName()
    {
        $objMdlCat = app::get('syscategory')->model('cat');
        $catId = $objMdlCat->getRow('cat_name, cat_id', array( 'name'=>$_POST['name'] ) );
        if( $catId && $_POST['cat_id'] != $catId )
        {
            echo 'false';
        }
        else
        {
            echo 'true';
        }
    }

}
