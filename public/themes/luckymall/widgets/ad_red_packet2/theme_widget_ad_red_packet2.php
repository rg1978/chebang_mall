<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_ad_red_packet2(&$setting){
    if( $setting['hongbao_select'] )
    {
        $hongbaoId = implode(',', $setting['hongbao_select']);
        $filter = array(
            'hongbao_id'=>$hongbaoId,
            'page_no'=>1,
            'page_size'=>100,
            'fields'=>'hongbao_id,name,hongbao_list'
        );
        $hongbaoData = app::get('topc')->rpcCall('promotion.hongbao.list.get', $filter)['list'];

        $i = 0;
        foreach( $hongbaoData as $hongbao )
        {
            foreach($hongbao['hongbao_list'] as $row)
            {
                if( $i >= 10 ) break;

                $data[$i]['name'] = $hongbao['name'];
                $data[$i]['hongbao_id'] = $hongbao['hongbao_id'];
                $data[$i]['money'] = $row['money'];
                $i++;
            }
        }

        $setting['hongbao'] = $data;
    }
    return $setting;
}
?>
