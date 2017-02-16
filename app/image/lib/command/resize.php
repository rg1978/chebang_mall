<?php
/**
 * ShopEx licence
 *
 * @category ecos
 * @package image.lib
 * @author shopex ecstore dev dev@shopex.cn
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * @version 0.1
 */

/**
 * 实现相应的缩略图，改变图片的大小
 * @category ecos
 * @package image.lib.command
 * @author shopex ecstore dev dev@shopex.cn
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class image_command_resize extends base_shell_prototype
{
	/**
	 * @var string 方法名称说明
	 */
    var $command_do = '图片重新生成';

    /**
     * 根据提交的图片全局配置大小，生成相应的缩略图
     * filesystem图片重新生成
     * @param null - 通过function_get_args方法来获取
     * @return null
     */
    public function command_do()
    {
        $objLibImage = kernel::single('image_data_image');
        $pagesize = 50;
        $imgObj = kernel::single('image_clip');
        $imgMdl = app::get('image')->model('images');
        $filter['disabled'] = 0;
        $count = $imgMdl->count($filter);
        logger::info(sprintf('Total %d records', $count));
        for($i=0; $i<$count; $i+=$pagesize)
        {
            $rows = $imgMdl->getList('ident,img_type', $filter, $i, $pagesize);
            foreach($rows AS $row)
            {
                $imgType = $row['img_type'] ? $row['img_type'] : 'cmd';
                logger::info(sprintf('file %s image type %s', $row['ident'], $imgType));
                $objLibImage->rebuild($row['ident'], $imgType);
            }
            logger::info(sprintf('%d records Completed!', $i+count($rows)));
        }

    }//End Function

	/**
	 * @var 定义名称
	 */
    var $command_refreshmodify = '强制刷新图片最新更新时间';
    /**
     * 强制刷新图片最新更新时间
     * @param null
     * @return null
     */
    public function command_refreshmodify()
    {
        app::get('image')->database()->executeUpdate('update image_images SET last_modified = last_modified + 1');
        logger::info('Refresh last_modified OK!');
    }//End Function

}//End Class
