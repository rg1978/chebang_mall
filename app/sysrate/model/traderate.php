<?php
class sysrate_mdl_traderate extends dbeav_model{

    public $defaultOrder = array('modified_time','DESC');

    public function _filter($filter,$tableAlias=null,$baseWhere=null)
    {

        if( is_array($filter) && !$filter['disabled'] )//默认只取出有效的评价，删除评价是将此字段修改为1
        {
            $filter['disabled'] = 0;
        }

        $filter = parent::_filter($filter,$tableAlias,$baseWhere);
        return $filter;
    }

    public function doDelete($rows)
    {
        foreach($rows as $rateId)
        {
            $rateData = app::get('sysrate')->model('traderate')->getRow('rate_id,result,item_id',array('rate_id'=>$rateId));
            if( !$rateData ) continue;

            if( $rateData['result'] == 'good' )
            {
                $filter['rate_good_count'] = -1;
            }
            elseif( $rateData['result'] == 'bad' )
            {
                $filter['rate_bad_count'] = -1;
            }
            else
            {
                $filter['rate_neutral_count'] = -1;
            }

            $filter['item_id'] = $rateData['item_id'];
            try{
                if( !app::get('sysrate')->rpcCall('item.updateRateQuantity', $filter) )
                {
                    throw new \LogicException(app::get('sysrate')->_('删除失败'));
                }
            }catch( Exception $e ){
                throw new \LogicException(app::get('sysrate')->_('删除失败'));
            }
        }

        try{
            app::get('sysrate')->model('traderate')->update(['disabled'=>1],['rate_id'=>$rows]);
        }catch( Exception $e ){
            throw new \LogicException(app::get('sysrate')->_('删除失败'));
        }
        return true;
    }
}

