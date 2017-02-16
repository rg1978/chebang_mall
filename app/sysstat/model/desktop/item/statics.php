<?php
class sysstat_mdl_desktop_item_statics extends dbeav_model
{
    public function getStatGoodsList($cols='*',$filter,$offset=0, $limit=-1, $orderBy=null)
    { 
        $db = app::get('sysstat')->database();
        $qb = $db->createQueryBuilder();
        
        if($filter['cat_id']=='all')
        {
          $qb->select('I.shop_name as shop_name,sum(I.amountprice) as amountprice,sum(I.amountnum) as amountnum,I.title as title,I.pic_path as pic_path,I.item_id as item_id,I.cat_id as cat_id,I.cat_name as cat_name')
           ->from('sysstat_desktop_item_statics', 'I')
           ->setFirstResult($offset)
           ->setMaxResults($limit)
           ->where($qb->expr()->andX(
               $qb->expr()->gte('I.createtime', intval($filter['timeStart'])),
               $qb->expr()->lte('I.createtime', intval($filter['timeEnd']))
           ))
           ->groupBy('I.item_id');
        }
        else
        {
          $qb->select('I.shop_name as shop_name,I.itemurl as itemurl,sum(I.amountprice) as amountprice,sum(I.amountnum) as amountnum,I.title as title,I.pic_path as pic_path,I.item_id as item_id,I.cat_id as cat_id,I.cat_name as cat_name')
           ->from('sysstat_desktop_item_statics', 'I')
           ->setFirstResult($offset)
           ->setMaxResults($limit)
           ->where($qb->expr()->andX(
               $qb->expr()->gte('I.createtime', intval($filter['timeStart'])),
               $qb->expr()->lte('I.createtime', intval($filter['timeEnd'])),
               $qb->expr()->eq('I.cat_id', intval($filter['cat_id']))
           ))
           ->groupBy('I.item_id');
        }
        
        $qb->addOrderBy($orderBy, 'desc');

        $rows = $qb->execute()->fetchAll();
        
        return $rows;

    }

}
