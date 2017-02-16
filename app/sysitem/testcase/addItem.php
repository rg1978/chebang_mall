<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class addItem extends PHPUnit_Framework_TestCase
{
    public function setUp(){
        //$this->model = app::get('base')->model('members');
    }

    public function testRequest(){
        $item = array (
            'item' =>
            array (
                'item_id' => '',
                'sku' => '{"f475876af4":{"sku_id":"new","spec_desc":{"spec_private_value_id":{"1":"","2":""},"spec_value":{"1":"白色","2":"s"},"spec_value_id":{"1":"1","2":"19"}},"price":"1","mkt_price":"11111","cost_price":"11111","store":"10000"}}',
                'spec' => '{"1":{"spec_name":"颜色","spec_id":"1","show_type":"image","option":{"1":{"private_spec_value_id":"","spec_value":"白色","spec_value_id":"1","spec_image":"http://images.bbc.shopex123.com/images/b4/88/e5/1633d8c4f5b349e862f2def11a5cf29ade116e0b.jpg","spec_image_url":"http://images.bbc.shopex123.com/images/b4/88/e5/1633d8c4f5b349e862f2def11a5cf29ade116e0b.jpg_t.jpg"}}},"2":{"spec_name":"尺码","spec_id":"2","show_type":"text","option":{"19":{"private_spec_value_id":"","spec_value":"s","spec_value_id":"19"}}}}',
                'shop_cids' =>
                array (
                    0 => '60',
                ),
                'title' => 'fewafewa',
                'sub_title' => '',
                'brand_id' => '3',
                'bn' => '',
                'barcode' => '',
                'use_platform' => '0',
                'price' => '11111',
                'store' => '10000',
                'sub_stock' => '0',
                'mkt_price' => 0,
                'cost_price' => 0,
                'weight' => '1',
                'dlytmpl_id' => '7',
                'nature_props' =>
                array (
                    3 => '25',
                    6 => '38',
                ),
                'desc' => '3123123raewfa',
                'wap_desc' => '',
                'shop_id' => 3,
                'cat_id' => '33',
                'approve_status' => 'instock',
                'shop_cat_id' => ',60,',
                'is_selfshop' => 1,
                'order_sort' => 1,
            ),
            'return_to_url' => '',
            'cat_id' => '33',
            'search_brand' => '',
            'spec_value' =>
            array (
                '1_1' => '白色',
                '1_2' => '橙色',
                '1_3' => '粉红色',
                '1_4' => '黑色',
                '1_5' => '红色',
                '1_6' => '黄色',
                '1_7' => '灰色',
                '1_8' => '金色',
                '1_9' => '酒红色',
                '1_10' => '咖啡色',
                '1_11' => '卡其色',
                '1_12' => '蓝色',
                '1_13' => '绿色',
                '1_14' => '米黄色',
                '1_15' => '浅蓝色',
                '1_16' => '深蓝色',
                '1_17' => '银色',
                '1_18' => '紫色',
                '2_19' => 's',
                '2_20' => 'm',
                '2_21' => 'l',
                '2_22' => 'xl',
                '2_23' => 'xxl',
                '2_24' => 'xxxl',
            ),
            'images' =>
            array (
                '1_1' => 'http://images.bbc.shopex123.com/images/b4/88/e5/1633d8c4f5b349e862f2def11a5cf29ade116e0b.jpg',
            ),
            'f475876af4' =>
            array (
                'sku_id' => 'new',
                'price' => '1',
                'mkt_price' => '11111',
                'cost_price' => '11111',
                'store' => '10000',
                'bn' => '',
                'barcode' => '',
            ),
        );

        $itemname = 'itemname';
        for($i=0; $i<10000; $i++)
        {
            $item['item']['title'] = $itemname . $i;
            $res = kernel::single('sysitem_data_item')->add($item);

            echo "添加{$item['item']['title']}，成功！\n";
        }
    }
}
