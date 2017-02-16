<?php
class sysshop_ctl_admin_applycat extends desktop_controller{

    public function index()
    {
        return $this->finder('sysshop_mdl_shop_apply_cat');
    }

    public function goExamine()
    {
        $postdata = input::get('apply_id');
        $objMdlApplyCat = app::get('sysshop')->model('shop_apply_cat');
        $applyCat = $objMdlApplyCat->getRow('*',array('apply_id'=>$postdata));
        $relCat = app::get('sysshop')->model('shop_rel_lv1cat')->getList('cat_id',['shop_id'=>$applyCat['shop_id']]);
        $catIds = $applyCat['cat_id'].','.implode(',',array_column($relCat,'cat_id'));
        //新申请的类目
        $catList = app::get('sysshop')->rpcCall('category.cat.get.info',['cat_id'=>$catIds]);
        foreach($catList as $key=>$val)
        {
            if($val['cat_id'] == $applyCat['cat_id'])
            {
                $pagedata['newcat'] = $val;
            }
            if(in_array($val['cat_id'],array_column($relCat,'cat_id')))
            {
                $pagedata['oldcat'][] = $val;
            }
        }
        //已申请通过的类目
        $pagedata['shop'] = app::get('sysshop')->model('shop')->getRow('shop_type,shop_name',['shop_id'=>$applyCat['shop_id']]);
        $pagedata['apply_cat'] = $applyCat;
        return view::make('sysshop/admin/shop/examine_cat.html', $pagedata);
    }

    public function doExamine()
    {
        $postdata = input::get('apply');
        $this->begin();
        try{
            $this->adminlog("审核商家申请类目权限[{$postdata['check_status']}]", 1);

            if($postdata['check_status'] == "adopt")
            {
                unset($postdata['check_fail_reason']);
            }
            elseif($postdata['check_status'] == "reject")
            {
                if(!$postdata['check_fail_reason'])
                {
                    throw new \LogicException('请填写您拒绝该申请的原因');
                }
            }

            $objApplyCat = kernel::single('sysshop_data_applycat');
            $result = $objApplyCat->doExamine($postdata);
        }
        catch(\LogicException $e)
        {
            $this->adminlog("审核商家申请类目权限[{$postdata['check_status']}]", 0);
            $msg = $e->getMessage();
            $this->end(false,$msg);
        }
        $this->end(true,$msg);
    }

    /**
     * @brief 列表tab
     *
     * @return
     */
    public function _views()
    {

        $subMenu = array(
            0=>array(
                'label'=>app::get('sysshop')->_('待审核'),
                'optional'=>false,
                'filter'=>array(
                    'check_status'=>'pending',
                ),
            ),
            1=>array(
                'label'=>app::get('sysshop')->_('审核通过'),
                'optional'=>false,
                'filter'=>array(
                    'check_status'=>'adopt',
                ),

            ),
            2=>array(
                'label'=>app::get('sysshop')->_('审核驳回'),
                'optional'=>false,
                'filter'=>array(
                    'check_status'=>'reject',
                ),
            ),
        );
        return $subMenu;
    }
}
