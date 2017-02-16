<?php
/**
 * topapi
 *
 * -- member.deposit.detail
 * -- 会员预存款明细
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_deposit_detail implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '会员预存款明细';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'page_no'   => ['type'=>'int','valid'=>'', 'example'=>'', 'desc'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int','valid'=>'', 'example'=>'', 'desc'=>'每页数据条数,默认10条'],
        ];
    }

    /**
     * @return float deposit 会员预存款总金额
     * @return int pagers.total 明细总数量
     * @return string list.type add充值 expense 消费
     * @return string list.operator 执行操作用户
     * @return string list.fee 消费或充值金额
     * @return string list.message 变更记录
     * @return time list.logtime 日志记录时间
     * @return string cur_symbol.sign 货币符号
     * @return string cur_symbol.decimals 计算精度，保留小数点位数
     */
    public function handle($params)
    {
        $userId = $params['user_id'];
        $page = $params['page_no'] ? $params['page_no'] : 1;
        $pageSize = $params['page_size'] ? $params['page_size'] : 10;
        $deposit = app::get('topapi')->rpcCall('user.deposit.getInfo', ['user_id'=>$userId, 'with_log'=>'true', 'page'=>$page, 'row_num'=>$pageSize]);
        if( !$deposit['list'] ) return array();

        $deposit['pagers']['total'] = $deposit['count'];
        unset($deposit['count']);

        $cur_symbol = app::get('topapi')->rpcCall('currency.get.symbol',array());
        $deposit['cur_symbol'] = $cur_symbol;

        return $deposit;
    }
}

