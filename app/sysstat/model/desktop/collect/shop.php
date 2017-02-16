<?php
class sysstat_mdl_desktop_collect_shop extends dbeav_model
{
    public function getCollectShopList($cols='*',$filter,$offset=0, $limit=-1, $orderBy=null)
    { 
        $db = app::get('sysstat')->database();
        $qb = $db->createQueryBuilder();

        $qb->select('S.shopname as shopname,sum(S.collectnum) as collectnum,S.shop_id as shop_id,S.shopurl as shopurl')
         ->from('sysstat_desktop_collect_shop', 'S')
         ->setFirstResult($offset)
         ->setMaxResults($limit)
         ->where($qb->expr()->andX(
             $qb->expr()->gte('S.createtime', intval($filter['timeStart'])),
             $qb->expr()->lte('S.createtime', intval($filter['timeEnd']))
         ))
        ->groupBy('S.shop_id');

        $qb->addOrderBy($orderBy, 'desc');

        $rows = $qb->execute()->fetchAll();
        //echo '<pre>';print_r($rows);exit();
        return $rows;

    }

}
