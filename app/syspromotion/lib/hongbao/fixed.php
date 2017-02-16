<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 */
//定额红包

class syspromotion_hongbao_fixed {

    public function __construct($object)
    {
        $this->baseHongbaoType = $object;
        $this->redis = redis::scene('hongbao');
    }

    /**
     * 将生成红包list结构保存到Redis
     *
     * @param int $hongbaoId
     * @param array $data
     */
    public function hongbaolistSetToRedis($hongbaoId, $data)
    {
        foreach( $data['hongbao_list'] as $key=>$value )
        {
            $money = $value['money'];
            $num = $value['num'];
            $this->redis->set($this->createPayload('hongbaolist',$hongbaoId, $money), intval($num));
        }

        //保存红包总金额
        $this->redis->set($this->createPayload('totalMoney',$hongbaoId), $data['total_money']);
        $this->redis->set($this->createPayload('totalNum',$hongbaoId), intval($data['total_num']));
        return true;
    }

    public function getHongbaoInfo($hongbaoId, $data)
    {
        $data['getTotalMoney'] = ecmath::number_minus([$data['total_money'], $this->redis->get($this->createPayload('totalMoney',$hongbaoId))]);
        $data['getTotalNum'] =  (int)ecmath::number_minus([$data['total_num'], $this->redis->get($this->createPayload('totalNum',$hongbaoId))]);
        $data['useTotalMoney'] = $this->redis->get($this->createPayload('useTotalMoney',$hongbaoId));
        //$data['useTotalNum'] = (int)$this->redis->get($this->createPayload('useTotalNum',$hongbaoId));
        $data['refundMoney'] = $this->redis->get($this->createPayload('RefundMoney', $hongbaoId));
        $data['useRefundMoney'] = $this->redis->get($this->createPayload('useRefundMoney', $hongbaoId));

        foreach( $data['hongbao_list'] as $key=>&$value )
        {
            $payloadId = $this->createPayload('hongbaolist', $hongbaoId, $value['money']);
            $value['getNum'] = intval(ecmath::number_minus([$value['num'], $this->redis->get($payloadId)]));
        }

        return $data;
    }

    /**
     * 退还红包给用户
     *
     * @param int $hongbaoId 退还红包的红包ID
     * @param int $userId 退还用户ID
     * @param float $money 退还红包金额
     */
    public function refundUserHongbao($hongbaoId, $userId, $money)
    {
        //增加用户退还红包总金额
        $data = $this->baseHongbaoType->execRedisCommad('incrbyfloat', $this->createPayload('userRefundMoney', $hongbaoId, $userId), $money);

        //增加红包ID对应的退还总金额
        $this->baseHongbaoType->execRedisCommad('incrbyfloat', $this->createPayload('RefundMoney', $hongbaoId), $money);

        return $money;
    }

    /**
     * 给用户发放红包
     */
    public function getUserHongbao($userId, $money, $hongbaoData)
    {
        //用户红包金额  判断用户是否有领取资格
        $userGetHongbaoMoney = $this->baseHongbaoType->execRedisCommad('incrbyfloat', $this->createPayload('userGetMoney', $hongbaoData['hongbao_id'], $userId), $money);
        if( $userGetHongbaoMoney > $hongbaoData['user_total_money'] )
        {
            throw new \LogicException(app::get('syspromotion')->_('你已达到红包领取金额最大值'));
        }

        //用户红包数量
        $userGetHongbaoNum = $this->baseHongbaoType->execRedisCommad('incr', $this->createPayload('userGetNum', $hongbaoData['hongbao_id'], $userId) );
        if( $userGetHongbaoNum > $hongbaoData['user_total_num'] )
        {
            throw new \LogicException(app::get('syspromotion')->_('你已达到红包领取数量最大值'));
        }

        //领取红包
        $payloadId = $this->createPayload('hongbaolist', $hongbaoData['hongbao_id'], $money);
        $hongbaoTypeNum = $this->baseHongbaoType->execRedisCommad('decr', $payloadId);
        if( $hongbaoTypeNum < 0 )
        {
            throw new \LogicException(app::get('syspromotion')->_('红包已被领完') );
        }

        $hongbaoId = $hongbaoData['hongbao_id'];
        //红包总量剩余金额
        $totalMoney = $this->baseHongbaoType->execRedisCommad('decrbyfloat', $this->createPayload('totalMoney',$hongbaoId), $money);
        //红包总量剩余数量
        $totalNum = $this->baseHongbaoType->execRedisCommad('decr', $this->createPayload('totalNum',$hongbaoId));
        if( $totalMoney < 0 || $totalNum < 0 )
        {
            throw new \LogicException(app::get('syspromotion')->_('红包已被领完'), 200);
        }

        return $money;
    }

    /**
     * 使用退还的红包
     *
     * @param int $userId 使用红包用户ID
     * @param int $hongbaoId 使用红包的ID
     * @param float $money 红包金额
     */
    public function useRefundHongbao($userId, $hongbaoId, $money)
    {
        //使用用户退还红包总金额
        $userRefundMoney = $this->baseHongbaoType->execRedisCommad('decrByFloat', $this->createPayload('userRefundMoney', $hongbaoId, $userId), $money);
        if( $userRefundMoney < 0 )
        {
            throw new \LogicException(app::get('syspromotion')->_('你红包金额不足'));
        }

        //增加红包ID对应的使用退还总金额
        $this->baseHongbaoType->execRedisCommad('incrbyfloat', $this->createPayload('useRefundMoney', $hongbaoId), $money);

        return true;
    }

    /**
     * 使用红包
     *
     * @param int $userId 使用红包用户ID
     * @param int $hongbaoId 使用红包的ID
     * @param float $money 红包金额
     */
    public function useHongbao($userId, $hongbaoId, $money)
    {
        //用户已使用红包金额
        $userUseHongbaoMoney = $this->baseHongbaoType->execRedisCommad('incrbyfloat', $this->createPayload('userUseMoney', $hongbaoId, $userId), $money);
        //用户领取的红包金额
        $userTotalHongbaoMoney = $this->redis->get($this->createPayload('userGetMoney', $hongbaoId, $userId));
        if( $userUseHongbaoMoney > $userTotalHongbaoMoney )
        {
            throw new \LogicException(app::get('syspromotion')->_('你红包金额不足'));
        }

        //用户使用对应红包总金额
        $this->baseHongbaoType->execRedisCommad('incrbyfloat', $this->createPayload('useTotalMoney', $hongbaoId), $money);
        //用户使用对应红包总数量
        //$this->baseHongbaoType->execRedisCommad('incr', $this->createPayload('useTotalNum',$hongbaoId));

        return true;
    }

    public function createPayload(...$data)
    {
        return implode('_', $data);
    }
}

