<?php
class syspromotion_remind{

    /**
     * @brief 提交提醒
     *
     * @param $params
     *
     * @return
     */
    public function doAdd($params)
    {
        $objMdlRemind = app::get('syspromotion')->model('remind');
        $result = $objMdlRemind->save($params);
        return $result;
    }

    /**
     * @brief 删除提醒
     *
     * @param $params
     *
     * @return
     */
    public function doDelete($params)
    {
        $objMdlRemind = app::get('syspromotion')->model('remind');
        $result = $objMdlRemind->delete($params);
        return $result;
    }

    /**
     * @brief 获取提醒数据
     *
     * @param $params
     *
     * @return
     */
    public function getRemindList($params)
    {
        $objMdlRemind = app::get('syspromotion')->model('remind');
        $result = $objMdlRemind->getList('*',$params);
        return $result;
    }
}
