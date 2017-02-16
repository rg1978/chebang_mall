<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://club.shopex.cn/ ShopEx License
 */

class debugbar_command_debugbar extends base_shell_prototype
{
    /**
     * clean the debugbar data
     *
     * @var string
     */
    public $command_clear = "Flush the debugbar data";

    public function command_clear() {
        debugbar::selectStorage(debugbar::instance());
        debugbar::getStorage()->clear();
        logger::info('Debugbar data cleared.');
    }
}
