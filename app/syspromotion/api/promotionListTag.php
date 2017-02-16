<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 根据多条促销ID,获取促销标签
 * promotion.promotion.list.tag
 */
final class syspromotion_api_promotionListTag {

    public $apiDescription = '根据多条促销ID,获取促销标签';

    public function getParams()
    {
        $return['params'] = array(
            'promotion_id'  => ['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'促销表id'],
            'platform'      => ['type'=>'string', 'valid'=>'', 'default'=>'pc', 'example'=>'', 'description'=>'促销规则应用平台'],
        );
        return $return;
    }

    /**
     * @desc 根据多条促销ID,获取促销标签
     *
     * @return int promotion_id 促销ID
     * @return string promotion_tag 促销标签
     */
    public function getlist($params)
    {
        $filter['promotion_id'] = explode(',',$params['promotion_id']);
        // 平台未选择则默认全选
        if( $params['platform'] == 'pc' )
        {
            $filter['used_platform'] = array('0', '1');
        }
        elseif( $params['platform'] == 'wap' )
        {
            $filter['used_platform'] = array('0', '2');
        }
        else
        {
            $filter['used_platform'] = array('0','1','2');
        }

        $promotionData = app::get('syspromotion')->model('promotions')->getList('promotion_id,start_time,end_time,check_status,promotion_tag', $filter);
        if( !$promotionData ) return [];

        $data = [];
        $now = time();
        foreach( $promotionData as $key=>$row )
        {
            if( $now > $row['start_time'] &&  $now < $row['end_time'] && $row['check_status'] == 'agree')
            {
                $data[$row['promotion_id']]['promotion_id'] = $row['promotion_id'];
                $data[$row['promotion_id']]['tag'] = $row['promotion_tag'];
            }
        }

        return $data;
    }
}

