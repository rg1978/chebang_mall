<?php
/**
 * trade.php
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class importexport_data_trade_trade{

    public function __construct()
    {
        $this->model = app::get('systrade')->model('trade');
    }

    public function get_title()
    {
        $title = $this->_title();
        $title['vatInvoice_company_name'] = app::get('systrade')->_('增值税-公司名称');
        $title['vatInvoice_registration_number'] = app::get('systrade')->_('增值税-纳税人登记号');
        $title['vatInvoice_company_address'] = app::get('systrade')->_('增值税-公司地址');
        $title['vatInvoice_company_phone'] = app::get('systrade')->_('增值税-电话');
        $title['vatInvoice_bankname'] = app::get('systrade')->_('增值税-开户银行');
        $title['vatInvoice_bankaccount'] = app::get('systrade')->_('增值税-银行账号');

        return $title;
    }

    public function get_content_row($row)
    {
        $row['vatInvoice_company_name'] = '--';
        $row['vatInvoice_registration_number'] = '--';
        $row['vatInvoice_company_address'] = '--';
        $row['vatInvoice_company_phone'] = '--';
        $row['vatInvoice_bankname'] = '--';
        $row['vatInvoice_bankaccount'] = '--';
        if($row['invoice_vat_main']){
            $row['vatInvoice_company_name'] = $row['invoice_vat_main']['company_name'];
            $row['vatInvoice_registration_number'] = $row['invoice_vat_main']['registration_number'];
            $row['vatInvoice_company_address'] = $row['invoice_vat_main']['company_address'];
            $row['vatInvoice_company_phone'] = $row['invoice_vat_main']['company_phone'];
            $row['vatInvoice_bankname'] = $row['invoice_vat_main']['bankname'];
            $row['vatInvoice_bankaccount'] = $row['invoice_vat_main']['bankaccount'];
        }

        return $row;
    }

    private function _title()
    {
        $cols = $this->model->_columns();
        $title = array();
        foreach( $cols as $col => $val )
        {
            if( !$val['deny_export'] ){//不进行导出导入字段
                if(!$val['label'] && !$val['comment'])
                {
                    $title[$col] = $col;
                }
                else
                {
                    $title[$col] = $val['label'] ? $val['label'] : $val['comment'];
                }
            }
        }
        return $title;
    }
}
