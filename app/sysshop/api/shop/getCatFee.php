<?php
class sysshop_api_shop_getCatFee{
    public $apiDescription = "获取店铺关联的类目费率";
    public function getParams()
    {
        $return['params'] = array(
            'shop_id' => ['type'=>'int','valid'=>'required','description'=>'店铺id','default'=>'','example'=>'1'],
        );
        return $return;
    }
    public function getCatFee($params)
    {
        $filter['shop_id'] = $params['shop_id'];
        $catRows = "cat_id,fee_confg";
        $objMdlRelCat = app::get('sysshop')->model('shop_rel_lv1cat');
        $cats = $objMdlRelCat->getList($catRows,$filter);
        if(!$cats)  return array();

        foreach($cats as $value)
        {
            $catId[] = $value['cat_id'];
            $feeConf[] = unserialize($value['fee_confg']);
        }
        $catParams = array(
            'cat_id' => implode(',',$catId),
            'fields' => 'level,cat_name,guarantee_money,platform_fee,cat_service_rates,cat_id',
        );
        $catList = app::get('sysshop')->rpcCall('category.cat.get',$catParams,'seller');

        $data = array();
        foreach($catList as $k=>$cat)
        {
            $data[$k][$k]['cat_id'] = $cat['cat_id'];
            $data[$k][$k]['cat_name'] = $cat['cat_name'];
            $data[$k][$k]['cat_fee'] = $cat['platform_fee'];
            foreach($cat['lv2'] as $k2=>$lv2cat)
            {
                $data[$k][$k2][$k2]['cat_id'] = $lv2cat['cat_id'];
                $data[$k][$k2][$k2]['cat_name'] = $lv2cat['cat_name'];
                $data[$k][$k2][$k2]['cat_fee'] = $lv2cat['guarantee_money'];

                foreach($lv2cat['lv3'] as $k3=>$lv3cat)
                {
                    $data[$k][$k2][$k3]['cat_id'] = $lv3cat['cat_id'];
                    $data[$k][$k2][$k3]['cat_name'] = $lv3cat['cat_name'];
                    $data[$k][$k2][$k3]['cat_fee'] = $lv3cat['cat_service_rates'];
                }
            }
        }

        foreach($feeConf as $k=>$fee)
        {
            if($fee){
                unset($data[$k]);
            }

            foreach($fee as $key=>$value)
            {
                $data[$key][$key]['cat_id'] = $key;
                $data[$key][$key]['cat_name'] = $catList[$key]['cat_name'];
                $data[$key][$key]['cat_fee'] = $value['lvfee'];
                unset($data[$key]['lvfee']);
                foreach ($value as $ck2 => $va)
                {
                    $data[$key][$ck2][$ck2]['cat_id'] = $ck2;
                    $data[$key][$ck2][$ck2]['cat_name'] = $catList[$key]['lv2'][$ck2]['cat_name'];
                    $data[$key][$ck2][$ck2]['cat_fee'] = $va['lv2fee'];
                    unset($data[$key]['lvfee']);
                    foreach ($va as $ck3 => $v)
                    {
                        $data[$key][$ck2][$ck3]['cat_id'] = $ck3;
                        $data[$key][$ck2][$ck3]['cat_name'] = $catList[$key]['lv2'][$ck2]['lv3'][$ck3]['cat_name'];
                        $data[$key][$ck2][$ck3]['cat_fee'] = $v;
                        unset($data[$key][$ck2]['lv2fee']);
                    }
                }
            }
        }
        return $data;
    }
}
