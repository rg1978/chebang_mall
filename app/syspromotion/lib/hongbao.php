<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 */
class syspromotion_hongbao {

    public function __construct()
    {
        $this->objMdlHongbao = app::get('syspromotion')->model('hongbao');
        $this->baseHongbaoType = kernel::single('syspromotion_abstract_hongbaoType');
    }

    private function checkHongbaoParams($data)
    {
        $updateHongbaoData = $this->objMdlHongbao->getRow('hongbao_id,status', ['hongbao_id'=>$data['hongbao_id']]);
        if( $data['hongbao_id'] && !$updateHongbaoData )
        {
            throw new \LogicException(app::get('syspromotion')->_('不存在需要更新的红包'));
        }

        if( $data['hongbao_id'] && $updateHongbaoData['status'] != 'pending' )
        {
            throw new \LogicException(app::get('syspromotion')->_('已开始的红包不可编辑'));
        }

        $hongbao = $this->objMdlHongbao->getRow('hongbao_id', ['name'=>$data['name']]);
        if( (!$data['hongbao_id'] && $hongbao) || ($data['hongbao_id'] &&  $data['hongbao_id'] != $hongbao['hongbao_id']) )
        {
            throw new \LogicException(app::get('syspromotion')->_('红包名称已存在，请换一个重试'));
        }

        if( $data['get_start_time'] >=  $data['get_end_time'] )
        {
            throw new \LogicException(app::get('syspromotion')->_('红包领取开始时间需大于红包领取结束时间'));
        }

        if( $data['use_start_time'] >=  $data['use_end_time'] )
        {
            throw new \LogicException(app::get('syspromotion')->_('红包使用开始时间需大于红包使用结束时间'));
        }

        if( $data['get_start_time'] >=  $data['use_end_time'] )
        {
            throw new \LogicException(app::get('syspromotion')->_('红包使用结束时间需大于红包领取时间'));
        }

        return true;
    }

    /**
     *  保存红包规则
     */
    public function save($data)
    {
        $this->checkHongbaoParams($data);

        if( $data['hongbao_id'] && $data['status'] )
        {
            $data['status'] = $data['status'];
        }

        $data['created_time'] = time();
        $flag = $this->objMdlHongbao->save($data);
        if( $flag  )
        {
            $hongbaoId = $data['hongbao_id'];
            $hongbaoTypeClass = $this->getHongTypeClass($data['hongbao_type']);

            $result = kernel::single($hongbaoTypeClass)->hongbaolistSetToRedis($hongbaoId, $data);
        }

        return $hongbaoId;
    }

    /**
     * 获取单个红包详情
     */
    public function getHongbaoInfo($hongbaoId, $fields)
    {
        $data = $this->objMdlHongbao->getRow($fields, ['hongbao_id'=>$hongbaoId]);

        $hongbaoTypeClass = $this->getHongTypeClass($data['hongbao_type']);
        $objHongbaoType = kernel::single($hongbaoTypeClass, $this->baseHongbaoType);

        $data['hongbao_list'] = unserialize($data['hongbao_list']);

        if( $data['status'] != 'pending' )
        {
            $data = $objHongbaoType->getHongbaoInfo($hongbaoId, $data);
        }

        return $data;
    }

    /**
     * 批量发放红包
     *
     * @param int $userId 用户ID
     * @param array $hongbaoList 批量发送红包的列表
     * @param string $hongbaoObtainType 发送红包的方式
     */
    public function batchGetHongbao($userId, $hongbaoList, $hongbaoObtainType)
    {
        $this->baseHongbaoType->beginTransaction();

        foreach( $hongbaoList as $key=>$hongbaoRow)
        {
            $hongbaoId = $hongbaoRow['hongbao_id'];
            $money = $hongbaoRow['money'];

            $hongbaoData = $this->objMdlHongbao->getRow('hongbao_id,name,status,hongbao_type,used_platform,user_total_money,user_total_num,get_start_time,get_end_time,use_start_time,use_end_time,hongbao_list', ['hongbao_id'=>$hongbaoId]);
            try
            {
                $result = $this->__getHongbao($userId, $hongbaoId, $money, $hongbaoObtainType, $hongbaoData);
                $return[$key] = $result;
            }
            catch( \LogicException $e )
            {
                $this->baseHongbaoType->rollback();
                if( $e->getCode() == 200 )
                {
                    $this->objMdlHongbao->update(['status'=>'success'],['hongbao_id'=>$hongbaoData['hongbao_id']]);
                }

                throw $e;
            }
            catch( \Exception $e )
            {
                $this->baseHongbaoType->rollback();
                throw $e;
            }
        }

        return $return;
    }

    //发放红包
    public function getHongbao($userId, $hongbaoId, $money, $hongbaoObtainType)
    {
        $hongbaoData = $this->objMdlHongbao->getRow('hongbao_id,name,status,hongbao_type,used_platform,user_total_money,user_total_num,get_start_time,get_end_time,use_start_time,use_end_time,hongbao_list', ['hongbao_id'=>$hongbaoId]);
        if( ! $hongbaoData )
        {
            throw new \LogicException('领取红包失败');
        }

        $this->baseHongbaoType->beginTransaction();
        try
        {
            $return = $this->__getHongbao($userId, $hongbaoId, $money, $hongbaoObtainType, $hongbaoData);
        }
        catch( \LogicException $e )
        {
            $this->baseHongbaoType->rollback();
            if( $e->getCode() == 200 )
            {
                $this->objMdlHongbao->update(['status'=>'success'],['hongbao_id'=>$hongbaoData['hongbao_id']]);
            }

            throw $e;
        }
        catch( \Exception $e )
        {
            $this->baseHongbaoType->rollback();
            throw $e;
        }

        return $return;
    }

    /**
     * 发放红包
     *
     * @param int $userId 用户ID
     * @param int $hongbaoId 红包ID
     * @param float $money 发放给用户的金额，或则金额规则
     * @param string $hongbaoObtainType 红包领取方式
     */
    private function __getHongbao($userId, $hongbaoId, $money, $hongbaoObtainType, $hongbaoData)
    {
        $hongbaoTypeClass = $this->getHongTypeClass($hongbaoData['hongbao_type']);
        $objHongbaoType = kernel::single($hongbaoTypeClass, $this->baseHongbaoType);

        //只用用户主动领取需要判断领取时间，如果是退还则不需要判断时间
        if( $hongbaoObtainType == 'userGet' )
        {
            if( in_array($hongbaoData['status'],['pending','stop','success']) )
            {
                throw new LogicException(app::get('syspromotion')->_('红包发放还未开始或已结束'));
            }

            if( $hongbaoData['get_start_time'] > time() )
            {
                throw new LogicException(app::get('syspromotion')->_('红包发放还未开始'));
            }

            if( $hongbaoData['get_end_time'] < time() )
            {
                throw new LogicException(app::get('syspromotion')->_('红包发放已结束'));
            }

            $hongbaoList = unserialize($hongbaoData['hongbao_list']);
            $flag = false;
            foreach( $hongbaoList as $value )
            {
                if( $money == $value['money'] )
                {
                    $money = $value['money'];
                    $flag = true;
                }
            }

            if( ! $flag )
            {
                throw new LogicException(app::get('syspromotion')->_('红包已被领完'));
            }
        }

        if( $hongbaoObtainType == 'aftersales' || $hongbaoObtainType == 'cancelTrade' )
        {
            $getMoney = $objHongbaoType->refundUserHongbao($hongbaoData['hongbao_id'], $userId, $money);
        }
        else
        {
            $getMoney = $objHongbaoType->getUserHongbao($userId, $money, $hongbaoData);
        }

        $return['hongbao_id'] = $hongbaoData['hongbao_id'];
        $return['hongbao_type'] = $hongbaoData['hongbao_type'];
        $return['use_start_time'] = $hongbaoData['use_start_time'];
        $return['use_end_time'] = $hongbaoData['use_end_time'];
        $return['used_platform'] = $hongbaoData['used_platform'];
        $return['money'] = $getMoney;
        $return['name'] = $hongbaoData['name'];
        return $return;
    }

    /**
     * 使用红包
     *
     * @param int $userId 用户ID
     */
    public function useHongbao($userId, $useHongbaoList)
    {
        if( $useHongbaoList['refund'] )
        {
            $refundHongbaoIds = array_keys($useHongbaoList['refund']);
        }

        if( $useHongbaoList['get'] )
        {
            if( isset($refundHongbaoIds) )
            {
                $getHongbaoIds = array_keys($useHongbaoList['get']);
                $hongbaoIds = array_merge($refundHongbaoIds, $getHongbaoIds);
            }
            else
            {
                $hongbaoIds = array_keys($useHongbaoList['get']);
            }
        }

        $hongbaoListData = $this->objMdlHongbao->getList('hongbao_id,hongbao_type,used_platform,use_start_time,use_end_time', ['hongbao_id'=>$hongbaoIds]);
        $hongbaoListData = array_bind_key($hongbaoListData, 'hongbao_id');

        if( !$hongbaoListData )
        {
            throw new LogicException(app::get('syspromotion')->_('找不到使用的红包'));
        }

        $this->baseHongbaoType->beginTransaction();
        try{

            if( $useHongbaoList['refund'] )
            {
                foreach( $useHongbaoList['refund'] as $hongbaoId=>$money )
                {
                    $hongbaoTypeClass = $this->getHongTypeClass($hongbaoListData[$hongbaoId]['hongbao_type']);
                    $objHongbaoType = kernel::single($hongbaoTypeClass, $this->baseHongbaoType);

                    $objHongbaoType->useRefundHongbao($userId, $hongbaoId, $money);
                }
            }
            elseif( $useHongbaoList['get'] )
            {
                foreach( $useHongbaoList['get'] as $hongbaoId=>$money  )
                {

                    $hongbaoTypeClass = $this->getHongTypeClass($hongbaoListData[$hongbaoId]['hongbao_type']);
                    $objHongbaoType = kernel::single($hongbaoTypeClass, $this->baseHongbaoType);

                    if( $hongbaoListData[$hongbaoId]['use_start_time'] > time() )
                    {
                        throw new LogicException(app::get('syspromotion')->_('红包还未到使用时间'));
                    }

                    //该处不判断红包是否过期，红包过期由会员中心的具体红包过期时间为准。
                    //可能在售后的时候会为单个红包进行续期操作
                    //考虑到扩展，则不在该处进行重复判断

                    $objHongbaoType->useHongbao($userId, $hongbaoId, $money);
                }
            }
        }
        catch(\Exception $e)
        {
            $this->baseHongbaoType->rollback();
            throw $e;
        }

        return true;
    }

    public function getHongTypeClass($hongbaoType)
    {
        return 'syspromotion_hongbao_'.$hongbaoType;
    }
}

