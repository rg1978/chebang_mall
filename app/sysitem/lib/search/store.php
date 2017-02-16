<?php

/**
 * store.php 
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class sysitem_search_store {

    public function getItemListByStore($params, $start, $limit)
    {
        $store = app::get ('sysitem')->database ()->createQueryBuilder ();
        $db = $store->getConnection ();
        $store->select ('I.item_id,I.modified_time,I.title,I.image_default_id,I.price,S.store,ST.approve_status,ST.list_time')
        ->from ('sysitem_item_store', 'S')
        ->leftJoin ('S', 'sysitem_item', 'I', 'S.item_id=I.item_id')
        ->leftJoin ('S', 'sysitem_item_status', 'ST', 'S.item_id=ST.item_id')
        ->where ('S.store<=' . $store->createPositionalParameter ($params ['store']))
        ->andWhere ('I.shop_id=' . $store->createPositionalParameter ($params ['shop_id']))
        ->setFirstResult ($start)
        ->setMaxResults ($limit);
        
        if ($params ['search_keywords'])
        {
            $store->andWhere ($store->expr ()->like ('I.title', $db->quote ('%' . $params ['search_keywords'] . '%', \PDO::PARAM_STR)));
        }
        if ($params ['min_price'] || $params ['max_price'])
        {
            if ($params ['min_price'])
            {
                $store->andWhere ($store->expr ()->gte ('I.price', $db->quote ($params ['min_price'], \PDO::PARAM_INT)));
            }
            if ($params ['max_price'])
            {
                $store->andWhere ($store->expr ()->lt ('I.price', $db->quote ($params ['max_price'], \PDO::PARAM_INT)));
            }
        }
        if (isset ($params ['use_platform']) && $params ['use_platform'] != null)
        {
            
            $store->andWhere ($store->expr ()->eq ('I.use_platform', $db->quote ($params ['use_platform'], \PDO::PARAM_INT)));
        }
        
        if ($params ['bn'])
        {
            $store->andWhere ($store->expr ()->like ('I.bn', $db->quote ('%' . $params ['bn'] . '%', \PDO::PARAM_STR)));
        }
        
        if (isset ($params ['shop_cat_id']) && is_array ($params ['shop_cat_id']))
        {
            $whereSql = '';
            foreach ($params ['shop_cat_id'] as $key => $value)
            {
                $shopCatWhere [] = " (I.shop_cat_id like '%" . $value . "%')";
            }
            
            $whereSql = implode ($shopCatWhere, ' or ');
            $store->andWhere ($whereSql);
        }
        
        $storeList = $store->execute ()->fetchAll ();
        
        return $storeList;
    }

    public function getItemCountByStore($params)
    {

        $store = app::get ('sysitem')->database ()->createQueryBuilder ();
        $db = $store->getConnection ();
        $store->select ('count(I.item_id) as itemNum')->from ('sysitem_item_store', 'S')->leftJoin ('S', 'sysitem_item', 'I', 'S.item_id=I.item_id')->leftJoin ('S', 'sysitem_item_status', 'ST', 'S.item_id=ST.item_id')->where ('S.store<=' . $store->createPositionalParameter ($params ['store']))->andWhere ('I.shop_id=' . $store->createPositionalParameter ($params ['shop_id']));
        
        if ($params ['search_keywords'])
        {
            $store->andWhere ($store->expr ()->like ('I.title', $db->quote ('%' . $params ['search_keywords'] . '%', \PDO::PARAM_STR)));
        }
        if ($params ['min_price'] || $params ['max_price'])
        {
            if ($params ['min_price'])
            {
                $store->andWhere ($store->expr ()->gte ('I.price', $db->quote ($params ['min_price'], \PDO::PARAM_INT)));
            }
            if ($params ['max_price'])
            {
                $store->andWhere ($store->expr ()->lt ('I.price', $db->quote ($params ['max_price'], \PDO::PARAM_INT)));
            }
        }
        if (isset ($params ['use_platform']) && $params ['use_platform'] != null)
        {
            
            $store->andWhere ($store->expr ()->eq ('I.use_platform', $db->quote ($params ['use_platform'], \PDO::PARAM_INT)));
        }
        
        if ($params ['bn'])
        {
            $store->andWhere ($store->expr ()->like ('I.bn', $db->quote ('%' . $params ['bn'] . '%', \PDO::PARAM_STR)));
        }
        
        if (isset ($params ['shop_cat_id']) && is_array ($params ['shop_cat_id']))
        {
            $whereSql = '';
            foreach ($params ['shop_cat_id'] as $key => $value)
            {
                $shopCatWhere [] = " (I.shop_cat_id like '%" . $value . "%')";
            }
            $whereSql = implode ($shopCatWhere, ' or ');
            $store->andWhere ($whereSql);
        }
        
        $storeCount = $store->execute ()->fetchAll ();
        
        return $storeCount [0] ['itemNum'];
    }

}