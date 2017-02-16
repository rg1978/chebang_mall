<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysstat_desktop_goodsListData
{
     /**
     * 获取公共数据
     * data  页面传过来的数据
     * @return array
     */
    public function getCommonData($data)
    {
        if(strtotime($data['time_start'])>strtotime($data['time_end']))
        {
            throw new \LogicException(app::get('sysstat')->_("开始时间必须小于结束时间"));
        }
        if($data['timeType'])
        {
            $timeRange = kernel::single('sysstat_desktop_commonData')->getTimeRangeByType($data['timeType']);
            $timeStart = strtotime($timeRange['time_start']);
            $timeEnd = strtotime($timeRange['time_end']);
        }
        else
        {
            $timeStart = strtotime($data['time_start']);
            $timeEnd = strtotime($data['time_end']);
        }
        $catId = $data['catId'];

        $dataType = $data['dataType']?$data['dataType']:'num';
        $limit = $data['storeLimit']?$data['storeLimit']:5;
        //获取商品排行数据
        $goodsListInfo = $this->_getStoreListData($dataType,$timeStart,$timeEnd,$limit,$catId);
        //获取商品类目排行
        $catNameListData = $this->_getCatName();
       //echo '<pre>';print_r($tradeData);exit();
        $pagedata['goodsListData'] = $goodsListInfo;
        $pagedata['catListData'] = $catNameListData;
        $pagedata['time_start'] = date('Y/m/d',$timeStart);
        $pagedata['time_end'] = date('Y/m/d',$timeEnd);
        //echo '<pre>';print_r($pagedata);exit();
        return $pagedata;
    }

    /**
     * @brief  获取所有类目
     * 
     * @return array
     */
    private function _getCatName()
    {
        $mdlDesktopItemStat = app::get('sysstat')->model('desktop_item_statics');
        $fileds = 'cat_id,cat_name';
        $catListData = $mdlDesktopItemStat->getList($fileds);
        $catNameList = $this->array_unique_fb($catListData);
        //echo '<pre>';print_r($catNameList);exit();
        return $catNameList;
    }

    //数组去掉重复值
    public function array_unique_fb($array2D)
    {

        foreach ($array2D as $v)
        {
            $v=join(',',$v);  //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
            $temp[]=$v;
        }
        $temp=array_unique($temp);    //去掉重复的字符串,也就是重复的一维数组

        foreach ($temp as $k => $v)
        {
            $array=explode(',',$v); //再将拆开的数组重新组装
            
            $cat[$k]['cat_id'] =$array[0];
            $cat[$k]['cat_name'] =$array[1];

        }
        return $cat;
    }


    /**
     * @brief  获取交易数据
     * $dataType 数据类型  是件数num,还是钱money,string
     * $timeStart 查询的开始时间 2015-03-01
     * $timeEnd 查询的结束时间2015-03-03
     * 
     * @return array
     */
    private function _getStoreListData($dataType,$timeStart,$timeEnd,$limit,$catId)
    {
        $mdlDesktopItemStat = app::get('sysstat')->model('desktop_item_statics');
        if($dataType=='num')
        {
            $orderBy = 'amountnum';
        }
        if($dataType=='money')
        {
            $orderBy = 'amountprice';
        }
        $filter = array(
            'timeStart'=>$timeStart,
            'timeEnd'=>$timeEnd,
            'cat_id'=>$catId
        );
        if(!$limit)
        {
            $limit = -1;
        }
        $fileds = 'shop_id,item_id,title,pic_path,shop_name,amountnum,amountprice,cat_id,cat_name,createtime';
        //echo '<pre>';print_r($orderBy);exit();
        $goodsListData = $mdlDesktopItemStat->getStatGoodsList($fileds,$filter,0,$limit,$orderBy);
        foreach ($goodsListData as $key => $value)
        {
            $goodsListData[$key]['itemUrl'] = url::action("topc_ctl_item@index",array('item_id'=>$value['item_id']));
        }
        //echo '<pre>';print_r($goodsListData);exit();
        return $goodsListData;
    }

}
