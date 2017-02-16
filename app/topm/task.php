<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topm_task{

    public function post_update($dbver)
    {
        if($dbver['dbver'] < 0.2)
        {
            $shell = kernel::single('base_shell_webproxy');
            $shell->exec_command('install topwap');
        }

    }

}

