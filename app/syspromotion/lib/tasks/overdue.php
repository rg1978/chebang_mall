<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class syspromotion_tasks_overdue extends base_task_abstract implements base_interface_task{

    // 每个队列执行100条订单信息
    var $limit = 100;
    public function exec($params=null)
    {

        $filter = array(
            'end_time'=>time(),
            'check_status'=>'overdue',
        );

        $offset = 0;
        while( $listFlag = $this->__promotionList($data, $filter, $offset) )
        {
            $offset++;
             // 把分页得到的promotion Id加入相关队列
            try
            {
                $this->__implement($data);
            }
            catch(Exception $e)
            {
                throw new \LogicException($e->getMessage());
            }
        }
    }
    private function __promotionList(&$data , $filter, $offset)
    {
        $params['end_time'] = $filter['end_time'];
        $params['check_status'] = $filter['check_status'];
        $params['page_no'] = $offset;
        $params['page_size'] = $this->limit;
        $params['fields'] = 'promotion_id';
        $overDuData = app::get('syspromotion')->rpcCall('promotion.overdue.get',$params);

        if(!$overDuData)
        {
            return false;
        }
        else
        {
            $data = $overDuData;
            return true;
        }
    }
    //执行
    private function __implement($data)
    {
        $itemPromotionMdl = app::get('sysitem')->model('item_promotion');
        $promotionMdl = app::get('syspromotion')->model('promotions');
        $promotionIds = array_column($data,'promotion_id');
        //echo '<pre>';print_r($data);exit();
        $itemPromotionMdl->delete(array('promotion_id'=>$promotionIds));
        $promotionMdl->update(array('check_status'=>'overdue'),array('promotion_id'=>$promotionIds));
        // foreach ($data as $key => $value)
        // {
        //     $itemPromotionMdl->delete(array('promotion_id'=>$value['promotion_id']));
        //     $promotionMdl->update(array('check_status'=>'overdue'),array('promotion_id'=>$value['promotion_id']));
        // }
        //return true;
    }
}
