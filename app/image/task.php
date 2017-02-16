<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class image_task
{
    function post_install()
    {
        logger::info('Initial images');

        $objImage = kernel::single('image_data_image');
        $conf = config::get('image.image_default_set');
        $imageType = 'sysitem';
        $data = $objImage->store(APP_DIR.'/image/initial/default_images/default.gif', 'sysitem', $imageType, true);
        foreach( $conf as $k=>$row )
        {
            $conf[$k]['sysitem'] = $data['url'];
        }
        $objImage->setImageSetting('item', $conf);
        $objImage->setImageDefault($data['url']);

        app::get('image')->model('images')->update(array('img_type'=>'sysitem'),array('img_type'=>'size'));
        app::get('image')->model('images')->update(array('img_type'=>'shop_apply'),array('img_type'=>'','target_type'=>'seller'));
    }//End Function

    public function post_update($dbver)
    {
        if($dbver['dbver']<1.1)
        {
            $conf = app::get('image')->getConf('image.default.set');
            if( !$conf )
            {
                kernel::single('base_initial', 'image')->init();
                $objImage = kernel::single('image_data_image');
                $app_dir = app::get('image')->app_dir;
                $conf = app::get('image')->getConf('image.default.set');
                foreach($conf as $k=>$item)
                {
                    $data = $objImage->store($app_dir.'/initial/default_images/'.$item['default_image'].'.gif', 'admin', 'size', true);
                    $objImage->rebuild($data['ident']);
                    $conf[$k]['default_image'] = $data['url'];
                }
                app::get('image')->setConf('image.default.set',$conf);
                app::get('image')->setConf('image.set',$conf);
            }
        }
        elseif( $dbver['dbver'] < 2.0 )
        {
            $objImage = kernel::single('image_data_image');

            $conf = config::get('image.image_default_set');
            $imageType = 'sysitem';
            $data = $objImage->store(APP_DIR.'/image/initial/default_images/default.gif', 'sysitem', $imageType, true);
            foreach( $conf as $k=>$row )
            {
                $conf[$k]['sysitem'] = $data['url'];
            }
            $objImage->setImageSetting($imageType, $conf);
            $objImage->setImageDefault($data['url']);
            app::get('image')->model('images')->update(array('img_type'=>'sysitem'),array('img_type'=>'size'));
            app::get('image')->model('images')->update(array('img_type'=>'shop_apply'),array('img_type'=>'','target_type'=>'seller'));
            app::get('image')->model('images')->update(array('img_type'=>'shop'),array('img_type'=>'','target_type'=>'shop'));

            //重新生成图片规格
            $objLibImage = kernel::single('image_data_image');
            $pagesize = 50;
            $imgObj = kernel::single('image_clip');
            $imgMdl = app::get('image')->model('images');
            $count = $imgMdl->count($filter);
            logger::info(sprintf('Images Total %d records', $count));
            for($i=0; $i<$count; $i+=$pagesize)
            {
                $rows = $imgMdl->getList('ident,img_type', $filter, $i, $pagesize);
                foreach($rows AS $row)
                {
                    $imgType = $row['img_type'] ? $row['img_type'] : 'cmd';
                    $objLibImage->rebuild($row['ident'], $imgType);
                }
                logger::info(sprintf('%d records Completed!', $i+count($rows)));
            }
        }

        if( $dbver['dbver'] ==  2.0 )
        {
            app::get('image')->model('images')->update(array('image_cat_id'=>'0'),array('image_cat_id'=>null));
        }
    }
}//End Class

