<?php
/**
 * itemRt.php 更新商品增量索引
 *
 * @author     Xiaodc
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysitem_events_listeners_itemDelta {

    protected $search_server_policy = 'search_policy_sphinx';
    protected $indexName = 'sysitem_item';
    protected $status;

    public function __construct()
    {
        $policy = app::get('search')->getConf('search_server_policy');
        if($policy != $this->search_server_policy)
        {
            return false;
        }

        $obj = kernel::single($policy);
        $this->status = $obj->status();
    }

    public function handle($itemIds)
    {
        if(!$this->status)
        {
            return false;
        }

        if(!$itemIds)
        {
            return false;
        }

        // 将参数变为整型
        $itemIds = $this->rintval($itemIds, true);

        if(is_numeric($itemIds))
        {
            return $this->replaceOne($itemIds);
        }

        if(is_array($itemIds))
        {
            return $this->replaceMulti($itemIds);
        }

        return true;
    }

    public function delDelta($itemIds)
    {
        if(!$this->status)
        {
            return false;
        }

        if(!$itemIds)
        {
            return false;
        }

        // 将参数变为整型
        $itemIds = $this->rintval($itemIds, true);

        $filter = array('id'=>$itemIds);
        $filter['index_name'] = $this->indexName;
        $objMdl = app::get('search')->model('delta');

        return $objMdl->delete($filter);
    }

    /**
     *  批量更新
     *  @param array $itemIds
     *  @return bool
     *
     * */
    private function replaceMulti($itemIds)
    {
        $objMdl = app::get('search')->model('delta');
        $filter = array('id'=>$itemIds);
        $filter['index_name'] = $this->indexName;
        $deltaInfo = $objMdl->getList('id', $filter);

        if($deltaInfo)
        {
            $deltaInfo = array_column($deltaInfo, 'id');
            $filter['id'] = array_intersect($deltaInfo, $itemIds);
            $replaceData['last_modify'] = time();
            $objMdl->update($replaceData, $filter);
            // 取差集
            $itemIds = array_diff($itemIds, $deltaInfo);
            if(!$itemIds)
            {
                return true;
            }
        }

        $replaceData = array();
        foreach ($itemIds as $v)
        {
            $replaceData[] = $this->combinData($v);
        }

        return $this->insertMulti($replaceData);
    }

    /**
     *  更新一条数据
     *  @param int $itemId
     *  @return bool
     *
     * */
    private function replaceOne($itemId)
    {
        $objMdl = app::get('search')->model('delta');
        $filter = array('id'=>$itemId);
        $filter['index_name'] = $this->indexName;
        $result = $objMdl->getRow('id', $filter);
        $replaceData = array();
        if($result)
        {
            $replaceData['last_modify'] = time();
            $result = $objMdl->update($replaceData, $filter);
        }else
        {
            $replaceData = $this->combinData($itemId);
            $result = $objMdl->insert($replaceData);
        }

        return $result;
    }

    /**
     * 组合数据
     * @param int $itemId
     * @return array
     *
     * */
    private function combinData($itemId)
    {
        $data = array();
        if(is_numeric($itemId))
        {
            $data['id'] = $itemId;
            $data['index_name'] = $this->indexName;
            $data['last_modify'] = time();
        }

        return $data;
    }

    /**
     * 添加多条数据
     * 一般不建议循环添加数据
     * @param array $data
     * @return bool
     *
     * */
    private function insertMulti($data)
    {
        if(!is_array($data))
        {
            return false;
        }

        $objMdl = app::get('search')->model('delta');
        $level = $this->getArrLevel($data);
        if($level == 1)
        {
            $rs = $objMdl->insert($data);
        }

        foreach ($data as $v)
        {
            $this->insertMulti($v);
        }

        return $rs;
    }

    /**
     * 获取数组等级
     * @param array $arr
     * @return int
     *
     * */
    private function getArrLevel($arr)
    {
        $al = array();
        $this->__getArrLevel($arr, $al);

        return max($al);

    }

    private function __getArrLevel($arr, &$al, $level=0)
    {
        if(is_array($arr))
        {
            $level++;
            $al[] = $level;
            foreach ($arr as $v)
            {
                $this->__getArrLevel($v, $al, $level);
            }
        }
    }

    /**
     * 把数据转成数字
     * @param * $int 传入的数据
     * @param bool $allowarray 是否允许数组
     */

    private function rintval($int, $allowarray = false)
    {

        $ret = is_scalar ($int) ? intval ($int) : 0;
        if ($int == $ret || (! $allowarray && is_array ($int)))
        {return $ret;}

        if ($allowarray && is_array ($int))
        {
            foreach ($int as &$v)
            {
                $v = $this->rintval ($v, true);
            }

            return $int;
        }
        elseif ($int <= 0xffffffff)
        {
            $l = strlen ($int);
            $m = substr ($int, 0, 1) == '-' ? 1 : 0;
            if (($l - $m) === strspn ($int, '0987654321', $m))
            {return $int;}
        }

        return $ret;
    }
}

