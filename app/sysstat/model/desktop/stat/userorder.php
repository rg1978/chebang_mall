<?php
class sysstat_mdl_desktop_stat_userorder extends dbeav_model
{
    public function getUserList($cols='*',$filter,$offset=0, $limit=-1, $orderBy=null)
    { 
        $db = app::get('sysstat')->database();
        $qb = $db->createQueryBuilder();

        $qb->select('U.user_id as user_id,U.user_name as user_name,sum(U.userfee) as userfee,sum(U.userordernum) as userordernum')
           ->from('sysstat_desktop_stat_userorder', 'U')
           ->setFirstResult($offset)
           ->setMaxResults($limit)
           ->where($qb->expr()->andX(
               $qb->expr()->gte('U.createtime', intval($filter['timeStart'])),
               $qb->expr()->lte('U.createtime', intval($filter['timeEnd']))
           ))
           ->groupBy('U.user_id');
        $qb->addOrderBy($orderBy, 'desc');

        $rows = $qb->execute()->fetchAll();
        
        return $rows;

    }

}
