<?php
/**
 * ShopEx licence
 * - image.shop.move.cat
 * - 将图片从图片类型的一个子分类移动到(同类型或不同类型)另一个子分类
 *
 * @copyright Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license   http://ecos.shopex.cn/ ShopEx License
 * @link      http://www.shopex.cn/
 * @author    shopex 2016-05-17
 */
class image_api_shop_moveImageCat {

    public $apiDescription = "将图片从图片类型的一个子分类移动到(同类型或不同类型)另一个子分类";

    public function getParams()
    {
        $return['params'] = [
            'shop_id' => ['type'=>'int', 'valid'=>'required|numeric', 'title'=>'店铺ID', 'example'=>'24','desc'=>'店铺ID'],
            'image_id' => ['type'=>'string', 'valid'=>'required', 'title'=>'图片ID', 'example'=>'24,34','desc'=>'图片ID,如果为多个图片ID，则用逗号(,)隔开'],
            'image_cat_id' => ['type'=>'int', 'valid'=>'required|numeric', 'title'=>'图片类型子分类ID', 'example'=>'24','desc'=>'被移动到的图片类型子分类ID'],
            'img_type' => ['type'=>'string', 'valid'=>'', 'title'=>'店铺图片类型', 'example'=>'item','desc'=>'店铺图片类型，如果image_cat_id为0，则必填'],
        ];
        return $return;
    }

    /**
     * @desc 将图片从图片类型的一个子分类移动到(同类型或不同类型)另一个子分类
     *
     * @return bool result 执行更新操作的状态，成功或者失败
     */
    public function move($params)
    {
        $objMdlImageCat = app::get('image')->model('image_cat');

        $imageCatData = $objMdlImageCat->getRow('img_type,image_cat_id',['shop_id'=>$params['shop_id'],'image_cat_id'=>$params['image_cat_id']]);
        if( $params['image_cat_id'] && empty($imageCatData) )
        {
            throw new \LogicException(app::get('image')->_('被移动到的图片类型分类不存在'));
        }

        if( !$params['image_cat_id'] && !$params['img_type'])
        {
            throw new \LogicException(app::get('image')->_('店铺图片类型必填'));
        }

        $moveImgType = $imageCatData['img_type'] ? $imageCatData['img_type'] : $params['img_type'];

        $imageIds = explode(',',$params['image_id']);
        foreach( $imageIds as $k=>$v )
        {
            if( !is_numeric($v) )
            {
                unset($imageIds[$k]);
            }
        }

        if( empty($imageIds) )
        {
            throw new \LogicException(app::get('image')->_('被移动图片不存在'));
        }

        $objMdlImages = app::get('image')->model('images');
        $filter = [
            'id'=>$imageIds,
            'target_id'=>$params['shop_id'],
            'target_type'=>'shop',
        ];

        $imageData = $objMdlImages->getList('id,ident,img_type',$filter);
        if( !$imageData )
        {
            throw new \LogicException(app::get('image')->_('被移动图片不存在'));
        }

        $objLibImage = kernel::single('image_data_image');
        //被移动到的类型需要生成的规格
        $sizes = $objLibImage->getImageTypeSize($moveImgType);
        foreach( $imageData as $row )
        {
            //被移动的图片中，已有的规格
            $newSizes = $objLibImage->getImageTypeSize($row['img_type']);
            foreach( $sizes as $key=>$val )
            {
                //如果已有的规格，不在生成的规格中，则需要重新生成，或者生成的不一致
                if( !isset($newSizes[$key]) || $newSizes[$key] != $val )
                {
                    $rebuildSizes[$key] = $val;
                }
            }

            if( $rebuildSizes )
            {
                $objLibImage->rebuild($row['ident'], $row['img_type'], $rebuildSizes);
            }
        }

        return $objMdlImages->update(['image_cat_id'=>$params['image_cat_id'],'img_type'=>$moveImgType], ['id'=>$imageIds]);
    }
}

