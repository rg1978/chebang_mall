<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class sysuser_finder_user_deposit_cash
{
    public $column_edit = "操作";
    public $column_edit_order = 1;
    public function column_edit(&$colList, $list)
    {
        foreach($list as $k=>$row)
        {
            if($row['status'] == 'TO_VERIFY')
            {
              //$url = '?app=sysuser&ctl=admin_user&act=cashVerifyPage&finder_id='.$_GET['_finder']['finder_id'].'&cash_id='.$row['cash_id'];
              //$target = 'dialog::  {title:\''.app::get('sysuser')->_('审核提现单').'\', width:500, height:400}';
              //$title = app::get('sysuser')->_('审核提现单');
              //$button = '<a href="' . $url . '" target="' . $target . '">' . $title . '</a>';
                $url = '?app=sysuser&ctl=admin_user&act=cashVerify&finder_id='.$_GET['_finder']['finder_id'].'&cash_id='.$row['cash_id'] . '&allow=1';
                $title = app::get('sysuser')->_('审核通过');
                $button = '<a href="' . $url . '" >' . $title . '</a>';

                $button .= '&nbsp;&nbsp;&nbsp;&nbsp;';

                $url = '?app=sysuser&ctl=admin_user&act=cashVerify&finder_id='.$_GET['_finder']['finder_id'].'&cash_id='.$row['cash_id'] . '&allow=0';
                $title = app::get('sysuser')->_('驳回');
                $button .= '<a href="' . $url . '">' . $title . '</a>';

            }elseif($row['status'] == 'VERIFIED' ){
                $url = '?app=sysuser&ctl=admin_user&act=cashCompeletePage&finder_id='.$_GET['_finder']['finder_id'].'&cash_id='.$row['cash_id'];
                $target = 'dialog::  {title:\''.app::get('sysuser')->_('完成提现单').'\', width:300, height:150}';
                $title = app::get('sysuser')->_('完成提现单');
                $button = '<a href="' . $url . '" target="' . $target . '">' . $title . '</a>';
            }else{
                $button = null;
            }
            $colList[$k] = $button;
        }
    }

    public $column_amount = "金额";
    public function column_amount(&$colList, $list)
    {
        foreach($list as $k=>$row)
        {
            $colList[$k] = '￥ ' . $row['amount'];
        }
    }
}

