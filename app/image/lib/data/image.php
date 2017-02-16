<?php

use Symfony\Component\HttpFoundation\File\UploadedFile;

class image_data_image {

    public $imageTypeAll = array(
        1=> 'GIF',
        2=> 'JPEG',
        3=> 'PNG',
        4=> 'SWF',
        5=> 'PSD',
        6=> 'BMP',
        9=> 'JPC',
        10=>'JP2',
        11=>'JPX',
        12=>'JB2',
        13=>'SWC',
        14=>'IFF',
        15=>'WBMP',
        16=>'XBM',
    );

    /**
     * 上传图片商家ID
     */
    public $sellerId = null;

    /**
     * 上传图片的店铺ID
     */
    public $shopId = null;

    /**
     * 上传图片的分类ID
     */
    public $imageCatId = null;


    public function __construct()
    {
        $this->objMdlImage = app::get('image')->model('images');
    }

    //上传图片支持的后缀
    public function getImageSupportFiletype()
    {
        return config::get('image.image_support_filetype');
    }

    private function __getImageParams( $file )
    {
        $imageParams = getimagesize($file);
        $data['width'] = $imageParams[0];
        $data['height'] = $imageParams[1];
        $data['type'] = $imageParams[2];
        return $data;
    }

    private function __checkImage( $fileObject )
    {
        $extension = $fileObject->getClientOriginalExtension();
        if( !in_array(strtolower($extension), $this->getImageSupportFiletype()) )
        {
            throw new \LogicException(app::get('image')->_('不支持该图片格式'));
        }

        //检查上传图片大小限制
        $this->__checkUploadedFileMaxSize($fileObject);

        $imageName = $fileObject->getClientOriginalName();
        if( strlen($imageName) > 200 )
        {
            throw new \LogicException(app::get('image')->_('图片文件名称过长，名称不能超过200个字符'));
        }

        $file = $fileObject->getRealPath();
        $imageParams = $this->__getImageParams($file);
        $imageType = $imageParams['type'];
        if( !in_array($imageType,[IMAGETYPE_JPEG,IMAGETYPE_GIF,IMAGETYPE_PNG]) )
        {
            throw new \LogicException(app::get('image')->_("不支持{$this->imageTypeAll[$imageType]}图片格式"));
        }

        return true;
    }

    private function __checkUploadedFileMaxSize($fileObject)
    {
        $phpIniMax = $fileObject->getMaxFilesize();
        $maxFilesize = config::get('image.uploadedFileMaxSize');
        $maxFilesize = $maxFilesize > $phpIniMax ? $phpIniMax : $maxFilesize;

        if( $fileObject->getError() == UPLOAD_ERR_INI_SIZE  ||//上传文件大小限制
            $fileObject->getError() == UPLOAD_ERR_FORM_SIZE ||//POST上传大小表单限制
            $maxFilesize < $fileObject->getClientSize()
        )
        {
            throw new \LogicException(app::get('image')->_('超出限制，上传图片不能超过'.format_filesize($maxFilesize)));
        }
    }

    private function __preFileObject( $fileObject )
    {
        if(substr($fileObject,0,4) == 'http' )
        {
            $fileObject = $this->__getNetworkImage($fileObject);
        }
        elseif( !is_object($fileObject) )
        {
            if( file_exists($fileObject) )
            {
                $imageParams = pathinfo($fileObject);

                $file = tempnam(TMP_DIR,'tmpImage');
                kernel::single('base_filesystem')->copy($fileObject, $file);
                $size = filesize($file);

                $imageName = substr(strrchr($fileObject,'/'),1);
                $fileObject = new UploadedFile($file, $imageName, $imageParams['extension'], $size, 0, true);
            }
            else
            {
                $fileObject = new UploadedFile($fileObject['tmp_name'], $fileObject['name'], $fileObject['type'], $fileObject['size'], $fileObject['error']);
            }
        }

        return $fileObject;
    }

    /**
     * 商家：检查上传图片指定的图片类型和分类是否正确
     */
    public function setImageCatId($imageCatId, $imageType)
    {
        pamAccount::setAuthType('sysshop');
        $this->sellerId = pamAccount::getAccountId();
        if( $this->sellerId )
        {
            $this->shopId = app::get('image')->rpcCall('shop.get.loginId',array('seller_id'=>$this->sellerId));
        }
        else
        {
            throw new \LogicException(app::get('image')->_('登录已过期，请重新登录后操作'));
        }
        if( !$this->shopId  )
        {
            throw new \LogicException(app::get('image')->_('上传失败，店铺不存在'));
        }

        $filter = [
            'image_cat_id'=>$imageCatId,
            'img_type' => $imageType,
            'shop_id'=>$this->shopId,
        ];

        $objMdlImageCat = app::get('image')->model('image_cat');
        if(  !$objMdlImageCat->count($filter) )
        {
            throw new \LogicException(app::get('image')->_('上传指定的图片类型子分类不存在'));
        }

        $this->imageCatId = $imageCatId;
        return $this;
    }

    /**
     * 存储图片接口
     *
     * @param object $fileObject 继承SplFileInfo封装的类
     * @param string $from  上传图片用户类型
     */
    public function store( $fileObject, $from, $imageType, $test=false)
    {
        $fileObject = $this->__preFileObject( $fileObject );

        $this->__checkImage($fileObject);

        $file = $fileObject->getRealPath();
        $imageParams = $this->__getImageParams($file);
        $params['width'] = $imageParams['width'];
        $params['height'] = $imageParams['height'];
        $params['size'] = $fileObject->getClientSize();

        $params['image_name'] = htmlspecialchars(specialutils::filterInput($fileObject->getClientOriginalName()),ENT_QUOTES);
        if( empty($imageType) )
        {
            throw new \LogicException(app::get('image')->_('上传图片失败，上传图片类型不能为空'));
        }

        $params['image_cat_id'] = $this->imageCatId ? $this->imageCatId : 0;
        $params['img_type'] = $imageType;
        $params['last_modified'] = time();

        $storager = kernel::single('base_storager');
        $result = $storager->upload($fileObject);
        list($url,$ident,$storage) = explode('|', $result);

        $params['url'] = kernel::get_host_mirror_img().$url;
        $params['ident'] = $ident;
        $params['storage'] = $storage;

        $accountData = $this->__imageAttach($from, $test);
        $params['target_id'] = $accountData['target_id'];
        $params['target_type'] = $accountData['target_type'];
        $params['disabled'] = 0;

        if( !in_array($imageType, config::get('image.image_type.'.$params['target_type'])) )
        {
            throw new \LogicException(app::get('image')->_('上传图片失败，上传图片类型错误'));
        }

        if( $row = $this->objMdlImage->getRow('id',['url'=>$params['url'],'target_id'=>$params['target_id'],['target_type'=>$params['target_type']]]) )
        {
            $this->objMdlImage->update($params, ['id'=>$row['id']]);
        }
        else
        {
            $this->objMdlImage->insert($params);
        }
        unlink($file);

        return $params;
    }

    /**
     * 图片ID，关联上用户类型ID
     *
     * @param string $from 上传图片用户类型
     */
    private function __imageAttach($from=false, $test=false)
    {
        if( $from == 'shop' )
        {
            if( $this->sellerId )
            {
                $data['target_id'] = $this->sellerId;
            }
            else
            {
                pamAccount::setAuthType('sysshop');
                $data['target_id'] = pamAccount::getAccountId();
            }

            if( $this->shopId )
            {
                $shopId = $this->shopId;
            }
            else
            {
                $shopId = app::get('image')->rpcCall('shop.get.loginId',array('seller_id'=>$data['target_id']),'seller');
            }

            if($shopId )
            {
                $data['target_id'] = $shopId;
                $data['target_type'] = 'shop';
            }
            else
            {
                $data['target_type'] = 'seller';
            }
        }
        elseif( $from == 'user' )
        {
            pamAccount::setAuthType('sysuser');
            $data['target_id'] = pamAccount::getAccountId();
            $data['target_type'] = 'user';
        }
        else
        {
            pamAccount::setAuthType('desktop');
            $data['target_id'] = pamAccount::getAccountId();
            $data['target_type'] = 'admin';
        }

        if( !$data['target_id'] && !$test )
        {
            throw new \LogicException(app::get('image')->_('无上传图片权限'));
        }

        return $data;
    }

    public function getImageTypeSize( $imageType )
    {
        if( empty($imageType) ) return false;

        $imageSetting = config::get('image.image_setting');

        $size = $imageSetting['normal']['size'];
        foreach( $imageSetting as $k=>$row )
        {
            if( $row['image_type'] && in_array($imageType, $row['image_type']) )
            {
                $size = $row['size'];
            }
        }

        if( !$size ) return false;
        $setImageSize = config::get('image.image_setting_set');
        if( in_array( $imageType, $setImageSize) )
        {
            $setSize = $this->getImageSetting($imageType);
        }

        if( !$setSize )
        {
            $setSize = config::get('image.image_default_set');
        }

        foreach( $size as $s )
        {
            $res[$s]['width'] = $setSize[$s]['width'];
            $res[$s]['height'] = $setSize[$s]['height'];
            $res[$s]['title'] = $setSize[$s]['title'];
        }

        return $res;
    }

    public function setImageSetting($imageType, $value)
    {
        return app::get('image')->setConf('image.set.'.$imageType, $value);
    }

    public function getImageSetting($imageType)
    {
        return app::get('image')->getConf('image.set.'.$imageType);
    }

    public function setImageDefault($url)
    {
        return app::get('image')->setConf('image.set.defaultPic', $url);
    }

    public function getImageDefault()
    {
        return app::get('image')->getConf('image.set.defaultPic');
    }

    /**
     * 商品图片相册图片生成
     *
     * @param $ident 需要生成相册图片唯一值
     * @param $sizes   生成图片大小
     *
     * @return bool
     */
    public function rebuild($ident, $imageType, $sizes )
    {
        if( !$sizes )
        {
            $sizes = $this->getImageTypeSize($imageType);
        }

        $storager = kernel::single('base_storager');
        $orgFile = $storager->getFile($ident);

        if( !file_exists($orgFile) || !$sizes ) return true;

        foreach($sizes as $s=>$value)
        {
            $tmpTarget = tempnam(TMP_DIR,'img');
            $w = $value['width'];
            $h = $value['height'];

            $orgFileSize = getimagesize($orgFile);
            if( $orgFileSize['0'] < $w )
            {
                $w = $orgFileSize['0'];
            }
            if( $orgFileSize['1'] < $h )
            {
                $h = $orgFileSize['1'];
            }

            image_clip::image_resize($orgFile, $tmpTarget, $w, $h);

            $imageParams = getimagesize($tmpTarget);
            $size = filesize($tmpTarget);
            $fileObject = new UploadedFile($tmpTarget, $images['image_name'], $imageParams['mime'], $size, 0, true);

            $storager->rebuild($fileObject, strtolower($s), $ident);
            unlink($tmpTarget);
        }

        return true;
    }

    /**
     * 存储网络图片
     *
     * @param string $imageUrl 图片URL地址
     */
    public function storeNetworkImage( $imageUrl, $from, $imageType, $test=false )
    {
        $fileObject = $this->__getNetworkImage($imageUrl);
        $imageId = $this->store($fileObject, $from, $imageType, $test);
        $file = $fileObject->getRealPath();
        unlink($file);
        return $imageId;
    }

    private function __getNetworkImage($imageUrl)
    {
        $imageContent = client::get($imageUrl)->getBody();
        $tmpTarget = tempnam(TMP_DIR, 'imageurl');
        file_put_contents($tmpTarget, $imageContent);

        $imageParams = getimagesize($tmpTarget);
        $size = filesize($tmpTarget);

        $imageName = substr(strrchr($imageUrl,'/'),1);

        if( $num = strpos($imageName,'?') )
        {
            $imageName = substr($imageName,0,$num);
        }

        $fileObject = new UploadedFile($tmpTarget, $imageName, $imageParams['mime'], $size, 0, true);
        return $fileObject;
    }

    /**
     * 存储服务器中移除图片源文件
     * @param array $ids 多个图片ID
     */
    public function removeImageFile($ids)
    {
        try{
            $data = $this->objMdlImage->getList('id,storage,ident,img_type',['id'=>$ids]);
            foreach( $data as $row )
            {
                $storager = kernel::single('base_storager',$row['storage']);

                $num = $this->objMdlImage->count(['ident'=>$row['ident']]);
                //七牛存储服务 同一个图片只生成一个唯一标示
                //如果不同店铺上传同一个图片，或导致一个唯一标示对应多条记录
                if( $num == 1 )
                {
                    $storager->remove($row['ident']);

                    $allsize = $this->getImageTypeSize($row['img_type']);
                    foreach($allsize as $s=>$value)
                    {
                        $storager->removeSizeFile($row['ident'],$s);
                    }
                }

                $delImageIds[] = $row['id'];
            }
        }
        catch( Exception $e)
        {
            if( $delImageIds )
            {
                $this->objMdlImage->delete(['id'=>$delImageIds]);
                throw new Exception('部分删除失败');
            }

            throw new Exception('删除失败');
        }

        $this->objMdlImage->delete(['id'=>$delImageIds]);

        return true;
    }
}

