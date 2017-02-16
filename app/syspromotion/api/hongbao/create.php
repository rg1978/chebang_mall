<?php
/**
 * ShopEx licence
 * - hongbao.create
 * - 平台创建红包接口
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 */
final class syspromotion_api_hongbao_create {

    /**
     * 接口作用说明
     */
    public $apiDescription = '平台创建红包';

    /**
     * 接口参数
     */
    public function getParams()
    {
        $return['params'] = array(
            'hongbao_id'       => ['type'=>'string', 'valid'=>'',                 'title'=>'红包ID',            'desc'=>'红包ID，如果有红包ID则更新红包数据'],
            'name'             => ['type'=>'string', 'valid'=>'required|max:20',  'title'=>'红包名称',          'desc'=>'红包名称'],
            'used_platform'    => ['type'=>'string', 'valid'=>'required|in:all,pc,wap', 'title'=>'使用平台',    'desc'=>'使用平台(all,pc,wap)'],
            'total_money'      => ['type'=>'number', 'valid'=>'',                 'title'=>'红包总金额',        'desc'=>'可领取的红包总金额'],
            'user_total_money' => ['type'=>'number', 'valid'=>'required|numeric|min:0', 'title'=>'用户可领取总金额',  'desc'=>'用户可领取红包的总金额'],
            'user_total_num'   => ['type'=>'string', 'valid'=>'required|numeric|min:1', 'title'=>'用户可领取总数',    'desc'=>'用户可领取红包的总数量'],
            'get_start_time'   => ['type'=>'string', 'valid'=>'required',         'title'=>'领取红包起始时间',  'desc'=>'领取红包起始时间'],
            'get_end_time'     => ['type'=>'string', 'valid'=>'required',         'title'=>'领取红包截止时间',  'desc'=>'领取红包截止时间'],
            'use_start_time'   => ['type'=>'string', 'valid'=>'required',         'title'=>'使用红包起始时间',  'desc'=>'使用红包起始时间'],
            'use_end_time'     => ['type'=>'string', 'valid'=>'required',         'title'=>'使用红包截止时间',  'desc'=>'使用红包截止时间'],
            'hongbao_type'     => ['type'=>'string', 'valid'=>'required',         'title'=>'红包类型',          'desc'=>'红包类型(定额红包，随机红包等)，目前只支持定额红包'],
            'hongbao_list'     => ['type'=>'string', 'valid'=>'required',         'title'=>'生成红包详细信息',  'desc'=>'生成红包详细结构'],
            'status'           => ['type'=>'string', 'valid'=>'in:active', 'title'=>'红包是否开启领取', 'desc'=>'红包开启领取后，在红包开始领取时间可领取红包'],
        );
        return $return;
    }

    /**
     * 平台创建红包
     *
     * @desc 平台创建红包
     * @return int result 创建红包ID
     */
    public function create($params)
    {
        $params['hongbao_list'] = json_decode($params['hongbao_list'], true);

        if( count($params['hongbao_list']) > 20 )
        {
            throw new \LogicException('最多添加20个红包规则');
        }

        $totalMoney = 0;
        $totalNum = 0;
        foreach( $params['hongbao_list'] as $k=>$v)
        {
            if( $v['money'] <= 0 )
            {
                throw new \LogicException('红包金额必须大于0');
            }

            if( $v['num'] <= 0 )
            {
                throw new \LogicException('红包数量必须大于0');
            }


            if( $moneyArr && in_array($v['money'], $moneyArr) )
            {
                throw new \LogicException('红包规则金额不能重复');
            }
            else
            {
                $moneyArr[] = $v['money'];
            }

            //定额红包可以统计出总红包金额
            if( $params['hongbao_type'] == 'fixed' )
            {
                $totalMoney = ecmath::number_plus(array($totalMoney, ecmath::number_multiple(array($v['money'], $v['num']))));
            }

            $totalNum = ecmath::number_plus(array($totalNum, $v['num']));
        }

        if( $params['hongbao_type'] == 'fixed' )
        {
            $params['total_money'] = $totalMoney;
        }

        $params['total_num'] = intval($totalNum);

        return kernel::single('syspromotion_hongbao')->save($params);
    }
}

