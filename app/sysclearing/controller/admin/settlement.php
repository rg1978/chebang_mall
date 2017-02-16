<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2014 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class sysclearing_ctl_admin_settlement extends desktop_controller {
    public $workground = 'sysclearing_ctl_admin_settlement';
    protected $limit = 20;

    /**
     * 商家结算单管理列表
     */
    public function index()
    {
        // 准备数据
        $getData = input::get ();
        $searchParams = array ();

        // 表单搜索
        $actUrl = '?app=sysclearing&ctl=admin_settlement&act=index';
        $this->pagedata ['form_url'] = $actUrl;
        // 商家列表
        $objLibSett = kernel::single ('sysclearing_data_get');
        $shopList = $objLibSett->getShopList ();
        $shopList [- 1] = '全部';
        ksort ($shopList);

        // 设置页面变量
        $this->pagedata ['options'] = $shopList;
        $this->pagedata ['settlement_type'] = $objLibSett->settlement_status;
        $this->pagedata ['time_start'] = strtotime (date ('Y-m-d 00:00:00', strtotime ('-1 month')));
        $this->pagedata ['time_end'] = strtotime (date ('Y-m-d 23:59:59'));

        if (isset ($getData ['issearch']) && $getData ['issearch'] == 1)
        {
            // 搜索变量
            $searchParams['time_start'] = $this->pagedata ['time_start'] = isset ($getData ['time_start']) ? $getData ['time_start'] : $this->pagedata ['time_start'];
            $searchParams['time_end'] = $this->pagedata ['time_end'] = isset ($getData ['time_end']) ? $getData ['time_end'] : $this->pagedata ['time_end'];
            $searchParams['shop_id'] = $this->pagedata ['shop_id'] = isset ($getData ['shop_id']) ? $getData ['shop_id'] : - 1;
            $searchParams['settlement_status'] = $this->pagedata ['settlement_status'] = isset ($getData ['settlement_status']) ? $getData ['settlement_status'] : - 1;
        }

        // 导出条件
        $this->pagedata['export_filter'] = json_encode($searchParams);
        if($this->has_permission('export')){
            $top_extra_view = array('sysclearing'=>'sysclearing/admin/index_header.html');
        }

        return $this->finder('sysclearing_mdl_settlement',array(
                'title'=>app::get('sysclearing')->_('结算汇总'),
                'use_buildin_filter' => false,
                'use_buildin_delete' => false,
                'use_buildin_refresh' => false,
                'use_buildin_setcol' => false,
                'use_buildin_selectrow' =>false,
                'base_filter' =>$searchParams,
                'top_extra_view'=>$top_extra_view,
        ));

    }

    /**
     * 商家结算单明细列表
     */
    public function detail()
    {
        // 准备数据
        $getData = input::get ();
        $searchParams = array ();
        $actUrl = '?app=sysclearing&ctl=admin_settlement&act=detail';
        $this->pagedata ['form_url'] = $actUrl;
        $this->pagedata ['time_start'] = strtotime (date ('Y-m-d 00:00:00', strtotime ('-1 month')));
        $this->pagedata ['time_end'] = strtotime (date ('Y-m-d 23:59:59'));
        $objLibSett = kernel::single ('sysclearing_data_get');
        // 商家列表
        $shopList = $objLibSett->getShopList ();
        $shopList [- 1] = '全部';
        ksort ($shopList);
        $this->pagedata ['options'] = $shopList;
        $settlement_type = $objLibSett->settlement_type;
        foreach ($settlement_type as $key => $value) {
            if ( $key == "2")
            {
                unset($settlement_type[$key]);
            }
        }
        $this->pagedata ['search_settlement_type'] = $settlement_type;

        if (isset ($getData ['issearch']) && $getData ['issearch'] == 1)
        {
            // 搜索变量
            $searchParams['time_start'] = $this->pagedata ['time_start'] = isset ($getData ['time_start']) ? $getData ['time_start'] : $this->pagedata ['time_start'];
            $searchParams['time_end'] = $this->pagedata ['time_end'] = isset ($getData ['time_end']) ? $getData ['time_end'] : $this->pagedata ['time_end'];
            $searchParams['shop_id'] = $this->pagedata ['shop_id'] = isset ($getData ['shop_id']) ? $getData ['shop_id'] : - 1;
            $searchParams['settlement_type'] = $this->pagedata ['settlement_type'] = isset ($getData ['settlement_type']) ? $getData ['settlement_type'] : - 1;

        }

        // 导出参数
        $this->pagedata['export_filter'] = json_encode($searchParams);
        if($this->has_permission('export')){
            $top_extra_view = array('sysclearing'=>'sysclearing/admin/detail_header.html');
        }

        return $this->finder('sysclearing_mdl_settlement_detail',array(
                'title'=>app::get('sysclearing')->_('结算明细'),
                'use_buildin_filter' => false,
                'use_buildin_delete' => false,
                'use_buildin_refresh' => false,
                'use_buildin_setcol' => false,
                'use_buildin_selectrow' =>false,
                'base_filter' =>$searchParams,
                'top_extra_view'=>$top_extra_view,
        ));

    }

    public function confirm($settlementNo)
    {

        $pagedata ['settlement_no'] = $settlementNo;
        return $this->page ('sysclearing/admin/confirm.html', $pagedata);
    }

    public function doConfirm()
    {

        $this->begin ("?app=sysclearing&ctl=admin_settlement&act=index");
        $settlementNo = input::get ('settlement_no');
        $status = input::get ('settlement_status');
        try
        {
            kernel::single ('sysclearing_settlement')->doConfirm ($settlementNo, $status);
            $this->adminlog ("确认结算单[分类ID:{$settlementNo}]", 1);
        } catch ( Exception $e )
        {
            $this->adminlog ("确认结算单[分类ID:{$settlementNo}]", 0);
            $msg = $e->getMessage ();
            $this->end (false, $msg);
        }
        $this->end (true);
    }

}
