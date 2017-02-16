<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class queue_push extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {

    }

    public function testPublish(){
        $params = array (
            'eventParams' =>
            array (
                0 =>
                array (
                    'user_id' => 4,
                    'user_name' => 'demo',
                    'trade' =>
                    array (
                        1 =>
                        array (
                            'tid' => '1606151610480004',
                            'shop_id' => 1,
                            'payment' => '317.00',
                            'order' =>
                            array (
                                0 =>
                                array (
                                    'shop_id' => 1,
                                    'tid' => '1606151610480004',
                                    'oid' => '1606151610490004',
                                    'user_id' => 4,
                                    'item_id' => 51,
                                    'sku_id' => 191,
                                    'num' => 1,
                                    'selected_promotion' => NULL,
                                    'activityDetail' => NULL,
                                ),
                            ),
                        ),
                    ),
                    'invoice' =>
                    array (
                        'need_invoice' => '0',
                        'invoice_type' => 'normal',
                        'invoice_content' => '',
                        'invoice_title' => 'individual',
                        'invoice_vat' =>
                        array (
                            'company_name' => '',
                            'registration_number' => '',
                            'company_address' => '',
                            'company_phone' => '',
                            'bankname' => '',
                            'bankaccount' => '',
                        ),
                    ),
                ),
                1 =>
                array (
                    'cartIds' =>
                    array (
                        0 => NULL,
                    ),
                    'mode' => 'fastbuy',
                    'cartPromotion' =>
                    array (
                        1 =>
                        array (
                            'basicPromotionListInfo' =>
                            array (
                            ),
                            'usedCartPromotion' =>
                            array (
                            ),
                        ),
                    ),
                ),
            ),
            'eventName' => 'trade.create',
            'listener' => 'systrade_events_listeners_createTradelog@addTradeLog',
        );

        //创建一个正常队列
        queue::push('system_tasks_events','system_tasks_events', $params);

        $runtimeStart = microtime(true);
        $runtime = round(($runtimeStop - $runtimeStart) , 4);
            $id = 1606151610480004;

        while( true )
        {
            $params['eventParams'][0][1]['tid'] = (string)$id;
            $params['eventParams'][0]['trade'][1]['order'][0]['oid']= (string)$id;
            $params['eventParams'][0]['trade'][1]['order'][0]['tid']= (string)$id;
            $params['eventParams'][0]['trade'][1]['tid']= (string)$id;

            $id = $id+1;

            $runtimeStop= microtime(true);
            $runtime = round(($runtimeStop - $runtimeStart) , 4);
            if( $runtime > 1000 ) break;

            //创建一个正常队列
            queue::push('system_tasks_events','system_tasks_events', $params);
        }

        //创建一个延时队列
        //queue::later('system_tasks_events','system_tasks_events', $params, 1);
    }
}



