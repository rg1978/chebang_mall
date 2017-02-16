<?php
class sysshop_data_shopnotice{
    //保存商家通知保
    public function saveShopNotice($postdata)
    {
        $shopNoticeMdl = app::get('sysshop')->model('shop_notice');
        if($postdata['shop_id']=='')
        {
            $postdata['shop_id'] = '0';
        }
        if($postdata['notice_id']!='')
        {
            $postdata['modified_time'] = time();
        }
        else
        {
            $postdata['createtime'] = time();
        }
        $adminId = pamAccount::getAccountId();
        $postdata['admin_id'] = $adminId;
        $result = $shopNoticeMdl->save($postdata);
        if(!$result)
        {
            throw new \LogicException("商家通知保存失败!");
        }
        return true;
    }

    public function getNoticeInfo($params)
    {
        $shopNoticeMdl = app::get('sysshop')->model('shop_notice');
        if($params['notice_id']=='')
        {
            throw new \LogicException("商家通知id不能为空!");
        }

        $noticeInfo = $shopNoticeMdl->getRow($params['fields'],array('notice_id'=>$params['notice_id']));

        return $noticeInfo;
    }

    public function getNoticeList($params)
    {
        $shopNoticeMdl = app::get('sysshop')->model('shop_notice');
        if($params['fields']=='')
        {
            $params['fields'] = '*';
        }
        $filter = array('shop_id'=>$params['shop_id'],'notice_type'=>$params['notice_type']);
        $orderBy    = $params['orderBy'] ? $params['orderBy'] : 'createtime DESC';

        $noticeCount = $shopNoticeMdl->count($filter);
        $pageTotal = ceil($noticeCount/$params['page_size']);
        $page =  $params['page_no'] ? $params['page_no'] : 1;
        $limit = $params['page_size'] ? $params['page_size'] : -1;
        $currentPage = ($pageTotal < $page) ? $pageTotal : $page; //防止传入错误页面，返回最后一页信息
        $offset = ($currentPage-1) * $limit;

        $aData = $shopNoticeMdl->getList($params['fields'], $filter,$offset,$limit, $orderBy);
        $noticeData = array(
                'noticeList'  => $aData,
                'noticecount' => $noticeCount,
            );
        //echo '<pre>';print_r($noticeData);exit();
        return $noticeData;
    }

}

