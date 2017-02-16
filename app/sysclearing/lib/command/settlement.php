<?php
/**
 * ShopEx licence

/**
 * 重新计算结算明细
 * 按照当前店铺类目设置的佣金比例重新计算结算明细和结算金额
 */
class sysclearing_command_settlement extends base_shell_prototype
{
	/**
	 * @var string 方法名称说明
	 */
    public $command_recount = '重新计算结算明细';

    public $command_recount_options = array(
        'timeStart'=>array('title'=>'重新结算开始时间戳'),
        'timeEnd'=>array('title'=>'重新结算结束时间戳，不填写则更新到当前时间'),
    );

    public function command_recount($timeStart, $timeEnd)
    {
        $objMdlSettlementDetail = app::get('sysclearing')->model('settlement_detail');

        //重新计算开始的时间戳
        $settlementStartTime = $timeStart;
        //重新计算结束的时间戳
        $settlementEndTime = $timeEnd ? $timeEnd : time();

        $filter['settlement_time|between'] = array($settlementStartTime, $settlementEndTime);

        $pagesize = 50;

        $count = $objMdlSettlementDetail->count($filter);
        logger::info(sprintf('总共 %d 条结算明细需要重新计算', $count));

        $objLibCatServiceRate = kernel::single('sysshop_data_cat');
        $objLibMath = kernel::single('ectools_math');

        for($i=0; $i<$count; $i+=$pagesize)
        {
            $rows = $objMdlSettlementDetail->getList('id,tid,shop_id,item_id,settlement_type,commission_fee,cat_service_rate,refund_fee,payment', $filter, $i, $pagesize);
            foreach( $rows as $row )
            {
                //获取cat_id
                $itemData = app::get('sysclearing')->rpcCall('item.get', ['item_id'=>$row['item_id'],'fields'=>'item_id,cat_id']);
                if( empty($itemData) ) continue;

                $catId = $itemData['cat_id'];

                $catServiceRate = $objLibCatServiceRate->getCatServiceRate(array('shop_id'=>$row['shop_id'], 'cat_id'=>$catId));
                $data['cat_service_rate'] = $catServiceRate;

                $settlementType = $row['settlement_type'];

                if( $settlementType == '3' )//如果子订单有部分售后退款的情况，需要改造此处
                {
                    //平台提取的佣金返还
                    $commissionFee = $objLibMath->number_multiple(array($row['refund_fee'],$catServiceRate));
                    $data['commission_fee'] = -$commissionFee;
                    //返还结算给商家的金额
                    $settlementFee = $objLibMath->number_minus(array($row['refund_fee'],$commissionFee));
                    $data['settlement_fee'] = -$settlementFee;
                }
                else
                {
                    //计算平台提取的佣金
                    $commissionFee = $objLibMath->number_multiple(array($row['payment'],$catServiceRate));
                    //计算结算给商家的金额
                    $data['settlement_fee'] = $objLibMath->number_minus(array($row['payment'],$commissionFee));
                }

                $objMdlSettlementDetail->update($data, ['id'=>$row['id']]);

                logger::info(sprintf('重新计算的订单ID：%s', $row['tid']));
            }
        }


    }//End Function

}//End Class

