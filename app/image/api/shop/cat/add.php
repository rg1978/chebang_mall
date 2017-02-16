<?php
/**
 * ShopEx licence
 * - image.shop.cat.add
 * - 创建店铺图片类型子分类
 *
 * @copyright Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license   http://ecos.shopex.cn/ ShopEx License
 * @link      http://www.shopex.cn/
 * @author    shopex 2016-05-17
 */
class image_api_shop_cat_add {

    public $apiDescription = "创建店铺图片类型子分类";

    public function getParams()
    {
        $return['params'] = [
            'shop_id' => ['type'=>'int', 'valid'=>'required|numeric', 'title'=>'店铺ID', 'example'=>'24','desc'=>'店铺ID'],
            'img_type' => ['type'=>'string', 'valid'=>'required', 'title'=>'店铺图片类型', 'example'=>'item','desc'=>'店铺图片类型，产品图片item;店铺图片shop'],
            'image_cat_name' => ['type'=>'string', 'valid'=>'required|max:10', 'title'=>'分类名称', 'example'=>'女装','desc'=>'图片类型的子分类名称'],
        ];
        return $return;
    }

    /**
     * @desc 创建店铺图片类型子分类
     *
     * @return int result 返回当前插入的image_cat_id
     */
    public function create($params)
    {

        if( !in_array($params['img_type'], config::get('image.image_type.shop')) )
        {
            throw new \LogicException(app::get('image')->_('该图片类型不能定义子分类'));
        }

        $objMdlImageCat = app::get('image')->model('image_cat');

        $filter = [
            'shop_id'=>$params['shop_id'],
            'img_type'=>$params['img_type']
        ];
        if( $objMdlImageCat->count($filter) >= 19 )
        {
            throw new \LogicException(app::get('image')->_('该图片类型的子分类已超过最大限制'));
        }

        $filter['image_cat_name'] = trim($params['image_cat_name']);
        if ($objMdlImageCat->count($filter) )
        {
            throw new \LogicException(app::get('image')->_('子分类已存在'));
        }

        $filter['last_modified'] = time();
        return $objMdlImageCat->insert($filter);
    }
}

