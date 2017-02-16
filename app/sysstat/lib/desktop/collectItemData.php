<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysstat_desktop_collectItemData
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
        //获取商品收藏排行数据
        $collectItem = $this->_getStoreListData($dataType,$timeStart,$timeEnd,$limit,$catId);
        //获取商品类目排行
        $catNameListData = $this->_getCatName();
       //echo '<pre>';print_r($tradeData);exit();
        $pagedata['collectItemData'] = $collectItem;
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
        $mdlDesktopCollectItem = app::get('sysstat')->model('desktop_collect_item');
        $fileds = 'cat_id,cat_name';
        $catListData = $mdlDesktopCollectItem->getList($fileds);
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
        $mdlDesktopCollectItem = app::get('sysstat')->model('desktop_collect_item');
        $orderBy = 'collectnum';

        $filter = array(
            'timeStart'=>$timeStart,
            'timeEnd'=>$timeEnd,
            'cat_id'=>$catId
        );
        if(!$limit)
        {
            $limit = -1;
        }
        $fileds = 'shop_id,item_id,title,pic_path,shop_name,collectnum,cat_id,cat_name,itemurl,createtime';

        $collectItemData = $mdlDesktopCollectItem->getCollectItemList($fileds,$filter,0,$limit,$orderBy);
        //echo '<pre>';print_r($collectItemData);exit();
        return $collectItemData;
    }

}
