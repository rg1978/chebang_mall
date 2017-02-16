<?php

/**
 * @brief 商品列表
 */
class sysitem_ctl_admin_item extends desktop_controller{

    public $workground = 'sysitem.workground.item';

    /**
     * @brief 列表
     *
     * @return
     */
    public function index()
    {
        $actions = array(
            array(
                'label'=>app::get('sysitem')->_('下架商品'),
                'icon' => 'download.gif',
                'submit' => '?app=sysitem&ctl=admin_item&act=disable',
                'confirm' => app::get('sysitem')->_('确定要下架选中商品？'),
            ),
            array(
                'label'=>app::get('sysitem')->_('删除'),
                'icon' => 'download.gif',
                'submit' => '?app=sysitem&ctl=admin_item&act=doDelete',
                'confirm' => app::get('sysitem')->_('您在进行商品删除操作，平台方应承担此操作的风险后果，确定要删除选中商品？'),
            ),
        );
        return $this->finder('sysitem_mdl_item',array(
            'use_buildin_set_tag' => false,
            'use_buildin_tagedit' => true,
            'use_buildin_filter'=> true,
            'use_buildin_refresh' => true,
            'use_buildin_delete' => false,
            //'allow_detail_popup' => true,
            'title' => app::get('sysitem')->_('商品列表'),
            'actions' => $actions,
        ));

    }
    public function doDelete()
    {
        $this->begin('?app=sysitem&ctl=admin_item&cat=index');
        $postdata = $_POST;
        $ojbMdlItem = app::get('sysitem')->model('item');
        $result = $ojbMdlItem->delete($postdata);
        $this->adminlog("删除商品", $result ? 1 : 0);
        // 删除增量索引
        if($result)
        {
            event::fire('del.item', array($postdata['item_id']));
        }
        
        $this->end($result,$msg);
    }


    /**
        * @brief 下架违规商品
        *
        * @return
     */
    public function disable()
    {
        $this->begin('?app=sysitem&ctl=admin_item&cat=index');
        $postdata = $_POST;
        $ojbItem = kernel::single('sysitem_data_item');
        $result = $ojbItem->batchCloseItem($postdata,'instock',$msg);
        $this->adminlog("下架商品", $result ? 1 : 0);
        $this->end($result,$msg);
    }

    public function approve()
    {
        $data = input::get();
        $params['item_id'] = intval($data['item_id']);
        $params['shop_id'] = intval($data['shop_id']);
        $params['approve_status'] = $data['approve_status'];
        
        if ($params['approve_status'] == 'refuse') {
            $params['reason'] = $data['reason'];
        }
        if( !trim($data['reason']) && $data['approve_status'] == 'refuse' )
        {
            return $this->splash('error',null,'请填写驳回原因',true);
        }

        $logInfo=array(
            'time' => time(),
            'approve_status' => $data['approve_status'],
            'reason' => $data['reason']
        );

        try{
            $result = app::get('sysitem')->rpcCall('item.sale.status',$params);
            if ($result) {
                $this->adminlog("商品审核[{$data['approve_status']}]，商品ID：{$data['item_id']}", 1);
                redis::scene('sysitem')->rpush('item_id_'.$data['item_id'],serialize($logInfo));
                return $this->splash('success',null,'操作成功',true);
            }else{
                $this->adminlog("商品审核[{$data['approve_status']}]，商品ID：{$data['item_id']}", 0);
                return $this->splash('error',null,'操作失败',true);
            }
        } catch(\LogicException $e){
            return $this->splash('success',null,$e->getMessage(),true);
        }
    }

    public function refuse()
    {
        $pagedata = input::get();
        return view::make('sysitem/item/refuse.html', $pagedata);
    }


    public function _views()
    {
        if(app::get('sysconf')->getConf('shop.goods.examine')){
            $subMenu = array(
                0=>array(
                    'label'=>app::get('sysitem')->_('全部'),
                    'optional'=>false,
                ),
                1=>array(
                    'label'=>app::get('sysitem')->_('已上架'),
                    'optional'=>false,
                    'filter'=>array(
                        'status'=>'onsale',
                    ),
                ),
                2=>array(
                    'label'=>app::get('sysitem')->_('已下架'),
                    'optional'=>false,
                    'filter'=>array(
                        'status'=>'instock',
                    ),
                ),
                3=>array(
                    'label'=>app::get('sysitem')->_('自营商品'),
                    'optional'=>false,
                    'filter'=>array(
                        'is_selfshop'=>1,
                    ),
                ),
                4=>array(
                    'label'=>app::get('sysitem')->_('未审核'),
                    'optional'=>false,
                    'filter'=>array(
                        'status'=>'pending',
                    ),
                ),
            );
        }else{
            $subMenu = array(
                0=>array(
                    'label'=>app::get('sysitem')->_('全部'),
                    'optional'=>false,
                ),
                1=>array(
                    'label'=>app::get('sysitem')->_('已上架'),
                    'optional'=>false,
                    'filter'=>array(
                        'status'=>'onsale',
                    ),
                ),
                2=>array(
                    'label'=>app::get('sysitem')->_('已下架'),
                    'optional'=>false,
                    'filter'=>array(
                        'status'=>'instock',
                    ),
                ),
                3=>array(
                    'label'=>app::get('sysitem')->_('自营商品'),
                    'optional'=>false,
                    'filter'=>array(
                        'is_selfshop'=>1,
                    ),
                ),
            );
        }
        
        return $subMenu;
    }
}


