<?php
/**
 * promotion.activity.item.info
 */
class syspromotion_api_activity_itemInfo{
    public $apiDescription = "获取参与活动的商品详情";
    public function getParams()
    {
        $data['params'] = array(
            'activity_id' => ['type'=>'int', 'valid'=>'sometimes|required|integer', 'default'=>'', 'example'=>'', 'description'=>'活动id'],
            'item_id' => ['type'=>'int', 'valid'=>'required|integer', 'default'=>'', 'example'=>'', 'description'=>'参加活动的商品id'],
            'valid' => ['type'=>'bool', 'valid'=>'sometimes|required|boolean', 'default'=>'', 'example'=>'', 'description'=>'活动状态'],
        );
        return $data;
    }

    public function getInfo($params)
    {
        $data = array();
        $objItemActivity = kernel::single('syspromotion_activity');
        if($params['valid'])
        {
            $itemFilter['item_id'] = $params['item_id'];
            $itemFilter['start_time|lthan'] = time();
            $itemFilter['end_time|than'] = time();
            $itemFilter['verify_status'] = 'agree';
            $data = $this->__getItemInfo($itemFilter);
            if($data['activity_id'])
            {
                $data['activity_info'] = $objItemActivity->getInfo('*', array('activity_id'=>$data['activity_id']));
            }
        }
        else
        {
            $itemFilter['item_id'] = $params['item_id'];
            $itemFilter['activity_id'] = $params['activity_id'];
            if($itemFilter['activity_id'])
            {
                $data = $this->__getItemInfo($itemFilter);
                $data['activity_info'] = $objItemActivity->getInfo('*', array('activity_id'=>$params['activity_id']));
            }
        }
        return $data;
    }

    // 同一个商品有效期内只能参加一个活动
    private function __getItemInfo($filter)
    {
        $objMdlItemActivity = app::get('syspromotion')->model('activity_item');
        $activityItem = $objMdlItemActivity->getRow('*', $filter);
        if($activityItem['verify_status']=='agree' && time()>$activityItem['start_time'] && time()<$activityItem['end_time'])
        {
            $activityItem['status'] = 1;
        }

        return $activityItem;
    }

}

