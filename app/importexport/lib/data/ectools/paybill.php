<?php

/**
 * paybill.php 
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class importexport_data_ectools_paybill {
    
    public function __construct()
    {
        $this->model = app::get('ectools')->model('trade_paybill');
    }
    
    // 设置标题
    public function get_title()
    {
        $title = $this->_title();
        $title['pay_type'] = app::get('importexport')->_('支付方式');
        
        return $title;
    }
    
    // 设置单行数据
    public function get_content_row($row)
    {
        $objMdlPayments = app::get('ectools')->model('payments');
        $filter['payment_id'] = $row['payment_id'];
        $filter['status'] = 'succ';
        $result = $objMdlPayments->getRow('pay_name', $filter);
        $row['pay_type'] = '--';
        if($result)
        {
            $row['pay_type'] = $result['pay_name'];
        }
        
        return $row;
    }
    
    private function _title()
    {
        $cols = $this->model->_columns();
        $title = array();
        foreach( $cols as $col => $val )
        {
            if(isset($val['deny_export']) && $val['deny_export'] == false)
            {
                continue;
            }
            
            $title[$col] = $val['label'] ? $val['label'] : $val['comment'];
        }
        return $title;
    }

}
 