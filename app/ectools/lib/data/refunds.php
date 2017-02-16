<?php
class ectools_data_refunds{
    public function create($params)
    {
        if($params['money'])
        {
            $params['cur_money'] = $params['money'];
        }
        if($params['rufund_type'] == 'deposit')
        {
            $params['status'] = "ready";
        }
        elseif($params['rufund_type'] == 'offline')
        {
            $params['status'] = "succ";
            $params['finish_time'] =time();
        }
        $params['refund_id'] = time();
        $params['created_time'] =time();
        $params['confirm_time'] =time();
        $objMdlRefunds = app::get('ectools')->model('refunds');
        $result = $objMdlRefunds->save($params);
        if(!$result)
        {
            throw new \LogicException("创建退款单失败");
            return false;
        }
        return $params['refund_id'];
    }

    public function update($data,$filter)
    {
        $data['confirm_time'] = time();
        $data['finish_time'] = time();
        $objMdlRefunds = app::get('ectools')->model('refunds');
        $result = $objMdlRefunds->update($data,$filter);
        if(!$result)
        {
            throw new \LogicException("创建退款单失败");
            return false;
        }
        return true;
    }
}
