<?php

/**
 * getPageInfo.php 
 * -- promotion.get.page.info
 * -- 获取促销页面信息
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class syspromotion_api_getPageInfo {

    public $apiDescription = '获取促销页面信息';
    
    public function getParams()
    {
        $return['params'] = array(
                'page_id'       => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'title'=>'促销专题ID', 'description'=>'促销专题ID'],
                'fields'    => ['type'=>'field_list', 'valid'=>'', 'default'=>'', 'example'=>'', 'title'=>'需要的字段', 'description'=>'需要的字段'],
        );
    
        return $return;
    }
    
    public function getInfo($params)
    {
        $row = empty($params['fields']) ? '*' : $params['fields'];
        return kernel::single('syspromotion_page')->getInfo(intval($params['page_id']), $row);
    }
}
 