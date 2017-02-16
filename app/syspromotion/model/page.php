<?php

/**
 * page.php 
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class syspromotion_mdl_page extends dbeav_model {

    public function modifier_page_tmpl(&$colList)
    {
        foreach ($colList as $k=>$row)
        {
            if(!$row)
            {
                $colList[$k] = app::get('syspromotion')->_('默认模板');
            }
        }
    }
}
 