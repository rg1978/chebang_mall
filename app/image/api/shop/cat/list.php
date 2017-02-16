<?php
/**
 * ShopEx licence
 * - image.shop.cat.list
 * - 获取图片类型子分类列表
 *
 * @copyright Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license   http://ecos.shopex.cn/ ShopEx License
 * @link      http://www.shopex.cn/
 * @author    shopex 2016-05-17
 */
class image_api_shop_cat_list {

    public $apiDescription = "获取店铺图片类型子分类列表(根据分类ID)";

    public function getParams()
    {
        $return['params'] = [
            'shop_id'       => ['type'=>'int',        'valid'=>'required|numeric',  'title'=>'店铺ID',           'example'=>'24','desc'=>'店铺ID'],
            'image_cat_id'  => ['type'=>'string',     'valid'=>'required',          'title'=>'图片类型子分类ID', 'example'=>'item','desc'=>'图片类型子分类ID,多个用逗号(,)隔开，一次最多20个'],
            'fields'        => ['type'=>'field_list', 'valid'=>'required',          'title'=>'查询字段',         'example'=>'*', 'desc'=>'需要查询返回的字段'],
        ];
        return $return;
    }

    /**
     * @desc 获取图片类型子分类列表
     *
     * @return int image_cat_id 图片类型子分类ID
     * @return int shop_id      店铺ID
     * @return string img_type  图片类型
     * @return string image_cat_name 图片类型子分类名称
     * @return time last_modified  最后修改时间
     */
    public function get($params)
    {
        $imageCatIds = explode(',',$params['image_cat_id']);
        if( count($imageCatIds) > 20 )
        {
            throw new \LogicException(app::get('image')->_('图片类型子分类一次最多查询20条数据'));
        }

        foreach( (array)$imageCatIds as $key=>$imageCatId )
        {
            if( !is_numeric($imageCatId) )
            {
                unset($imageCatIds[$key]);
            }
        }

        if( empty($imageCatIds) )
        {
            return array();
        }

        $objMdlImageCat = app::get('image')->model('image_cat');
        $filter = [
            'shop_id' => $params['shop_id'],
            'image_cat_id' => $imageCatIds,
        ];

        $data = $objMdlImageCat->getlist($params['fields'], $filter);
        return $data;
    }
}

