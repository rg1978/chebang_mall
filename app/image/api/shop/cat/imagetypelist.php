<?php
/**
 * ShopEx licence
 * - image.shop.cat.imagetype.list
 * - 获取店铺图片子分类列表
 *
 * @copyright Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license   http://ecos.shopex.cn/ ShopEx License
 * @link      http://www.shopex.cn/
 * @author    shopex 2016-05-17
 */
class image_api_shop_cat_imagetypelist {

    public $apiDescription = "获取店铺图片类型子分类列表(根据图片类型)";

    public function getParams()
    {
        $return['params'] = [
            'shop_id' => ['type'=>'int', 'valid'=>'required|numeric', 'title'=>'店铺ID', 'example'=>'24','desc'=>'店铺ID'],
            'img_type'=> ['type'=>'string', 'valid'=>'', 'title'=>'店铺图片类型', 'example'=>'item','desc'=>'店铺图片类型，产品图片item;店铺图片shop'],
            'fields'  => ['type'=>'field_list', 'valid'=>'required', 'title'=>'查询字段', 'example'=>'*', 'desc'=>'需要查询返回的字段'],
        ];
        return $return;
    }

    /**
     * @desc 获取店铺图片子分类列表
     *
     * @return int image_cat_id 图片类型子分类ID
     * @return int shop_id      店铺ID
     * @return string img_type  图片类型
     * @return string image_cat_name 图片类型子分类名称
     * @return time last_modified  最后修改时间
     */
    public function get($params)
    {
        if( $params['img_type'] && !in_array($params['img_type'], config::get('image.image_type.shop')) )
        {
            throw new \LogicException(app::get('image')->_('该图片类型不支持子分类'));
        }

        $objMdlImageCat = app::get('image')->model('image_cat');
        $filter['shop_id'] = $params['shop_id'];
        if( $params['img_type'] )
        {
            $filter['img_type'] = $params['img_type'];
        }
        return $objMdlImageCat->getlist($params['fields'], $filter);
    }
}

