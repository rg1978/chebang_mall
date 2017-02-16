<?php
class sysstat_mdl_desktop_stat_shop extends dbeav_model
{
    public function getStoreList($cols='*',$filter,$offset=0, $limit=-1, $orderBy=null)
    { 
        $db = app::get('sysstat')->database();
        $qb = $db->createQueryBuilder();

        $qb->select('S.shopname as shopname,sum(S.shopaccountfee) as shopaccountfee,sum(S.shopaccountnum) as shopaccountnum')
           ->from('sysstat_desktop_stat_shop', 'S')
           ->setFirstResult($offset)
           ->setMaxResults($limit)
           ->where($qb->expr()->andX(
               $qb->expr()->gte('S.createtime', intval($filter['timeStart'])),
               $qb->expr()->lte('S.createtime', intval($filter['timeEnd']))
           ))
           ->groupBy('S.shop_id');
        $qb->addOrderBy($orderBy, 'desc');

        $rows = $qb->execute()->fetchAll();
        return $rows;

    }

}
