<?php
/**
 * ShopEx licence
 * - image.shop.cat.delete
 * - 删除店铺图片类型子分类
 *
 * @copyright Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license   http://ecos.shopex.cn/ ShopEx License
 * @link      http://www.shopex.cn/
 * @author    shopex 2016-05-17
 */
class image_api_shop_cat_delete {

    public $apiDescription = "删除店铺图片类型子分类";

    public function getParams()
    {
        $return['params'] = [
            'image_cat_id' => ['type'=>'int', 'valid'=>'required|numeric', 'title'=>'图片类型子分类ID', 'example'=>'24','desc'=>'图片类型子分类ID'],
            'shop_id' => ['type'=>'int', 'valid'=>'required|numeric', 'title'=>'店铺ID', 'example'=>'24','desc'=>'店铺ID'],
        ];
        return $return;
    }

    /**
     * @desc 删除店铺图片类型子分类
     *
     * @return bool result 执行删除操作的状态，成功或者失败
     */
    public function delete($params)
    {
        $data = app::get('image')->model('images')->count(['image_cat_id'=>$params['image_cat_id'],'disabled'=>0]);
        if( $data )
        {
            throw new \LogicException(app::get('image')->_('该文件夹下存在图片，不能删除'));
        }
        $objMdlImageCat = app::get('image')->model('image_cat');
        return $objMdlImageCat->delete($params);
    }
}

