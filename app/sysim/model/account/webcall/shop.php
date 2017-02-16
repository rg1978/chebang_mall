<?php
class sysim_mdl_account_webcall_shop extends dbeav_model
{
    public function modifier_shop_id(&$colList)
    {
        $list = $colList;
        $shopIdList = [];
        foreach($list as $key=>$shop_id)
        {
            if($shop_id != 'platform')
                $shopIdList[] = $shop_id;

        }

        $fmtShops = [];
        if($shopIdList)
        {
            $shopIds = implode(',', $shopIdList);
            $shops = app::get('sysim')->rpcCall('shop.get.list', ['shop_id'=>$shopIds, 'fields'=>'shop_name,shop_id']);
            
            foreach($shops as $shop)
            {
                $shopId = $shop['shop_id'];
                $fmtShops[$shopId] = $shop;
            }
        }
        

        foreach($colList as $key=>$row)
        {
            if($row == 'platform')
            {
                $colList[$key] = app::get('sysim')->_('平台');
                continue;
            }
            $colList[$key] = $fmtShops[$row]['shop_name'];
        }

      //foreach($colList as $key=>$shop_id);
      //{
      //    if($shop_id == 'platform')
      //        continue;
      //    $list[] = $shop_id;
      //}

    }

}

