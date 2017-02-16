<?php

class sysaftersales_finder_refunds {

    public $column_edit = '操作';
    public $column_edit_order = 1;
    public $column_edit_width = 60;
    public function column_edit(&$colList, $list){
        foreach($list as $k=>$row)
        {

            //3 商家审核通过，5 商家强制关单 6 平台强制关单
            if( in_array($row['status'],['3','5','6']) )
            {
                $url = '?app=sysaftersales&ctl=refunds&act=refundsPay&finder_id='.$_GET['_finder']['finder_id'].'&p[refunds_id]='.$row['refunds_id'];
                $target = 'dialog::{title:\''.app::get('sysaftersales')->_('处理退款').'\', width:800, height:400}';
                $title = app::get('sysaftersales')->_('退款');
                $colList[$k] = '<a href="' . $url . '" target="' . $target . '">' . $title . '</a>';
            }
            elseif( $row['status'] == '0' )
            {
                $colList[$k] = '等待商家审核';
            }

            #$url = '?app=sysaftersales&ctl=refunds&act=rejectView&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['refunds_id'];
            #$target = 'dialog::{title:\''.app::get('sysaftersales')->_('拒绝退款').'\', width:300, height:300}';
            #$title = app::get('sysaftersales')->_('拒绝退款');
            #$colList[$k] .= ' | <a href="' . $url . '" target="' . $target . '">' . $title . '</a>';
        }
    }

    /** 退款详情 */
    public $detail_basic = '退款详情';
    public function detail_basic($Id)
    {
        $objRefunds = app::get('sysaftersales')->model('refunds');
        $refundRow = $objRefunds->getRow('*', array('refunds_id' => $Id));

        $refundRow['refundFee'] = ecmath::number_minus(array($refundRow['refund_fee'], $refundRow['hongbao_fee']));

        if(! $refundRow)
        {
            return false;
        }
        if($refundRow['status'] == '1')
        {
            $objEctoolsRefunds = app::get('ectools')->model('refunds');
            $filter = array('tid'=>$refundRow['tid'], 'refunds_type'=> $refundRow['refunds_type']);
            $refundRow['refunds_view'] = $objEctoolsRefunds->getRow('refund_bank,refund_account,refund_people,receive_bank,receive_account,beneficiary,rufund_type',$filter);

            // 和之前的老数据兼容，因为之前refund_people和beneficiary存储的是字符串数字，现在存储的是用户填写的真实姓名
            if(is_numeric($refundRow['refunds_view']['refund_people']))
            {
                $refundRow['refunds_view']['refund_people'] = kernel::single('desktop_user')->get_login_name();
            }

            if(is_numeric($refundRow['refunds_view']['beneficiary']))
            {
                $user = app::get('sysaftersales')->rpcCall('user.get.account.name',array('user_id'=>$refundRow['user_id']),'buyer');
                $refundRow['refunds_view']['beneficiary'] = $user[$refundRow['user_id']];
            }
        }

        return view::make('sysaftersales/refunds_view.html',$refundRow)->render();
    }
}
