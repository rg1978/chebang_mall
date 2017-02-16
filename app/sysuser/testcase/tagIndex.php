<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class tagIndex extends PHPUnit_Framework_TestCase
{
    public function setUp(){
        //$this->model = app::get('base')->model('members');
    }

    public function testSearchByTagId(){

        $tagId = 2;

        $page = 2;
        $pagesize = 10;

        $start = ($page - 1) * $pagesize;
        $stop = $start + $pagesize - 1;

        $searcher = kernel::single('sysuser_data_tag_index');

        $userIds = $searcher->searchByTagId($tagId, $start, $stop);

        var_dump($userIds);
    }

    public function testSearchByTagIds()
    {
        $tagIds = [2,3];

        $page = 2;
        $pagesize = 10;

        $start = ($page - 1) * $pagesize;
        $stop = $start + $pagesize - 1;

        $searcher = kernel::single('sysuser_data_tag_index');

        $userIds = $searcher->searchByTagIdsUserInter($tagIds, $start, $stop);

        var_dump($userIds);

    }

}
