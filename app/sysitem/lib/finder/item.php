<?php
class sysitem_finder_item{
    public $column_edit = '商品标题';
    public $column_edit_order = 12;
    public $column_edit_width = 100;


    public function column_edit(&$colList, $list)
    {
        foreach($list as $k=>$row)
        {
            $url = url::action('topc_ctl_item@index',array('item_id'=>$row['item_id']));
            $colList[$k] = "<a href='".$url."' target='_blank' >".$row['title']."</a>";
        }
    }

    public $column_status = '是否上架';
    public $column_status_order = 12;
    public $column_status_width = 10;

    public function column_status(&$colList,$list)
    {
        foreach ($list as $k => $row) {
            $params = array(
                'item_id' => $row['item_id'],
                'fields' => 'title,item_store,item_status',
            );

            $data = app::get('sysitem')->rpcCall('item.get',$params);
            $this->approveStatus = array(
                'pending' => app::get('sysitem')->_('否'),
                'refuse' => app::get('sysitem')->_('否'),
                'onsale' => app::get('sysitem')->_('是'),
                'instock' => app::get('sysitem')->_('否'),
            );
            $colList[$k] = $this->approveStatus[$data['approve_status']]; 
        }
    }

    public $column_op = "操作";
    public $column_op_order = 1;
    public $column_op_width = 10;

    public function column_op(&$col ,$list)
    {
        foreach($list as $k=>$row)
        {
            if(app::get('sysconf')->getConf('shop.goods.examine')){
                if ($row['approve_status'] =='pending') {
                    $title = app::get('sysitem')->_('审核');
                    $url = url::route('shopadmin', ['app'=>'sysitem','act'=>'index','ctl'=>'admin_item','finder_id'=>$_GET['_finder']['finder_id'],'id'=>$row['item_id'],'finderview'=>'detail_basic','action'=>'detail','singlepage'=>'true']);
                    $col[$k] = '<a href="'.$url.'" target="_blank" title="审核">审核</a>';
                }
            }
        }   
    }
    
    public $detail_basic = '基本信息';
    public function detail_basic($id)
    {
        $params['item_id'] = $id;
        $params['fields'] = "*,sku,item_store,item_status,item_count,item_desc,item_nature,spec_index";
        $pagedata = app::get('sysitem')->rpcCall('item.get',$params);
        $tmpParams = array(
            'shop_id' => $pagedata['shop_id'],
            'template_id' => $pagedata['dlytmpl_id'],
            'status' => 'on',
            'fields' => 'shop_id,name,template_id',
        );
        $catParams = array(
            'shop_id' => $pagedata['shop_id'],
            'cat_id' =>$pagedata['cat_id'],
            'fields' => 'cat_id,cat_name,is_leaf,parent_id,level');
        $pagedata['catInfo'] = app::get('sysitem')->rpcCall('category.cat.get.data',$catParams);
        $templateInfo = app::get('sysitem')->rpcCall('logistics.dlytmpl.get',$tmpParams);
        $pagedata['templateName'] = $templateInfo['name'];
        $pagedata['examine_setting'] = app::get('sysconf')->getConf('shop.goods.examine');
        $examineData= redis::scene('sysitem')->lrange('item_id_'.$id,0,-1);
        $pagedata['examineLog'] = array();
        foreach ($examineData as $key => $value) {
            $pagedata['examineLog'][$key] = unserialize($value);
        }

        //获取店铺分类
        $shop_cat_id = explode(',',$pagedata['shop_cat_id']);
        $shopCatParams = array(
            'shop_id' => $pagedata['shop_id'],
            'cat_id' => $shop_cat_id['1'],
            'fields' => 'cat_id,cat_name,is_leaf,parent_id,level'
        );

        $shopCatData = app::get('sysitem')->rpcCall('shop.cat.get',$shopCatParams);
        foreach ($shopCatData as $key => $value) {
            $pagedata['childCatName'] = $value['cat_name'];
            $pagedata['parent_id'] = $value['parent_id'];
            if($value['parent_id']){
                $pagedata['parentCatInfo'] = app::get('sysitem')->rpcCall('shop.cat.get',array('shop_id' => $pagedata['shop_id'],'cat_id'=>$value['parent_id'],'fields'=>'cat_name'));
            }
        }

        return view::make('sysitem/item/detail.html',$pagedata)->render();
    }

}
