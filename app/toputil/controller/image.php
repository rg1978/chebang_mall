<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class toputil_ctl_image {

    public function uploadImages()
    {
        $objLibImage = kernel::single('image_data_image');

        $file = $this->__getFile(input::file());

        $imageType = input::get('type',false);
        $imageFrom = input::get('from',false);
        if( input::get('image_cat_id',false) && $imageFrom == 'shop' )
        {
            $imageCatId = intval(input::get('image_cat_id'));
            $objLibImage->setImageCatId($imageCatId, $imageType);
        }

        foreach( (array)$file as $key=>$fileObject  )
        {
            try
            {
                $imageData = $objLibImage->store($fileObject,$imageFrom,$imageType);
                $objLibImage->rebuild($imageData['ident'], $imageType);
            }
            catch(Exception $e)
            {
                $msg = $e->getMessage();
                $result = array('error'=>true, 'message'=>$msg);
                return response::json($result);
            }
            $imageSrc[$key]['url'] = base_storager::modifier($imageData['url']);
            $imageSrc[$key]['image_id'] = $imageData['url'];
            $imageSrc[$key]['image_name'] = $imageData['image_name'];

	    $imageSrc[$key]['t_url'] = base_storager::modifier($imageData['url'],'T');
        }

        $result = array('success'=>true, 'data'=>$imageSrc);
        return response::json($result);
    }

    private function __getFile($file)
    {
        $objFile = current($file);
        if( $objFile && !is_object($objFile) && is_array($objFile) )
        {
            $file = $this->__getFile($objFile);
        }
        return $file;
    }

    /**
     * 根据itemId获取图片
     */
    public function getItemPic()
    {
        $itemId = input::get('itemIds');
        $picData = kernel::single('sysitem_item_info')->getItemDefaultPic($itemId);
        if( $picData[$itemId]['image_default_id'] )
        {
            $result['url'] = $picData[$itemId]['image_default_id'];
        }
        return response::json($result);
    }

}
