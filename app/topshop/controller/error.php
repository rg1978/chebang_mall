<?php

/**
 * error.php 
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topshop_ctl_error extends topshop_controller {

    public function index()
    {

        return $this->page('topshop/error/error.html', []);
    }

}
 