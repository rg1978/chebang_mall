<?php
/**
 * ShopEx licence
 * - promotion.hongbao.updateStatus
 * - 更新红包领取状态
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 */
final class syspromotion_api_hongbao_updateStatus {

    /**
     * 接口作用说明
     */
    public $apiDescription = '更新红包领取状态';

    /**
     * 接口参数
     */
    public function getParams()
    {
        $return['params'] = array(
            'hongbao_id' => ['type'=>'string', 'valid'=>'required',  'title'=>'红包ID',       'desc'=>'红包ID'],
            'status'     => ['type'=>'string', 'valid'=>'required|in:active,stop',  'title'=>'红包发放状态', 'desc'=>'红包发放状态'],
        );
        return $return;
    }

    /**
     * 使用红包接口
     *
     * @desc 使用红包接口
     * @return bool result 使用是否成功
     */
    public function update($params)
    {
        $this->objMdlHongbao = app::get('syspromotion')->model('hongbao');
        $data = $this->objMdlHongbao->getRow('hongbao_id,status,get_end_time', ['hongbao_id'=>$params['hongbao_id']]);

        if( !$data )
        {
            throw new \LogicException('更新的红包不存在');
        }

        if( $params['status'] == 'stop' )
        {
            if( $data['status'] == 'active' )
            {
                $this->objMdlHongbao->update(['status'=>'stop'], ['hongbao_id'=>$data['hongbao_id']]);
            }
            else
            {
                throw new \LogicException('红包发放已终止，不需要再操作');
            }
        }
        else
        {
            if( $data['status'] == 'stop' && $data['get_end_time'] > time() )
            {
                $this->objMdlHongbao->update(['status'=>'active'], ['hongbao_id'=>$data['hongbao_id']]);
            }
            else
            {
                throw new \LogicException('红包发放已结束不能重启');
            }
        }

        return true;
    }
}

