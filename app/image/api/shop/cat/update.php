<?php
/**
 * ShopEx licence
 * - image.shop.cat.update
 * - 更新店铺图片类型子分类
 *
 * @copyright Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license   http://ecos.shopex.cn/ ShopEx License
 * @link      http://www.shopex.cn/
 * @author    shopex 2016-05-17
 */
class image_api_shop_cat_update {

    public $apiDescription = "更新店铺图片类型子分类";

    public function getParams()
    {
        $return['params'] = [
            'image_cat_id' => ['type'=>'int', 'valid'=>'required|numeric', 'title'=>'图片类型子分类ID', 'example'=>'24','desc'=>'被修改的图片类型子分类ID'],
            'shop_id' => ['type'=>'int', 'valid'=>'required|numeric', 'title'=>'店铺ID', 'example'=>'24','desc'=>'店铺ID'],
            'image_cat_name' => ['type'=>'string', 'valid'=>'required|max:10', 'title'=>'分类名称', 'example'=>'女装','desc'=>'图片类型的子分类名称，修改后的名称'],
        ];
        return $return;
    }

    /**
     * @desc 更新店铺图片类型子分类
     *
     * @return bool result 执行更新操作的状态，成功或者失败
     */
    public function edit($params)
    {
        $objMdlImageCat = app::get('image')->model('image_cat');

        //验证更新的条件是否可用
        $filter = [
            'image_cat_id'=>$params['image_cat_id'],
            'shop_id'=>$params['shop_id'],
        ];
        $data = $objMdlImageCat->getRow('image_cat_id,shop_id,img_type,image_cat_name',$filter);
        if(  empty($data) )
        {
            throw new \LogicException(app::get('image')->_('修改的图片类型子分类不存在'));
        }

        if( $data['image_cat_name'] == $params['image_cat_name'] )
        {
            return true;
        }

        //验证更新的名称是否有重复
        $countFilter = [
            'shop_id'=>$params['shop_id'],
            'image_cat_name'=>trim($params['image_cat_name']),
            'img_type'=>$data['img_type'],
        ];
        if( $objMdlImageCat->count($countFilter)  )
        {
            throw new \LogicException(app::get('image')->_('修改的图片类型子分类已存在'));
        }

        return $objMdlImageCat->update(['image_cat_name'=>trim($params['image_cat_name'])],['image_cat_id'=>$params['image_cat_id']]);
    }
}

