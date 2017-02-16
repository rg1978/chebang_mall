<?php
class sysshop_data_applycat{

    public function doExamine($params)
    {
        $ojbMdlApplyCat = app::get('sysshop')->model('shop_apply_cat');
        $ojbMdlRelCat = app::get('sysshop')->model('shop_rel_lv1cat');
        $db = app::get('sysshop')->database();
        $db->beginTransaction();
        try{
            $params['check_time'] = time();
            $result = $ojbMdlApplyCat->save($params);
            if(!$result)
            {
                throw new \LogicException('审核信息保存失败');
            }

            if($params['check_status'] == 'adopt')
            {
                $data = $ojbMdlApplyCat->getRow('cat_id,shop_id',['apply_id'=>$params['apply_id']]);
                $result = $ojbMdlRelCat->save($data);
                if(!$result)
                {
                    throw new \LogicException('同意该申请，关联到该店铺失败');
                }
            }
            $db->commit();
        }
        catch(\LogicException $e)
        {
            $db->rollback();
            throw new \LogicException($e->getMessage());
            return false;

        }
        return true;
    }
}
