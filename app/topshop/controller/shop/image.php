<?php

class topshop_ctl_shop_image extends topshop_controller {

    public $limit = 30;

    public function index()
    {
		$result = $this->__getListData();

		$pagedata['activeImgType'] = input::get('img_type','item');

        //不同类型的图片，支持不同图片的规格
        $pagedata['imageTypeSize'] = kernel::single('image_data_image')->getImageTypeSize('item');

        $pagedata['imageShopCatList'] = $result['imageShopCatList'];
        $pagedata['activeImageCatId'] = input::get('image_cat_id',0);
        $pagedata['imagedata'] = $result['data']['list'];
        $pagedata['count'] = $result['data']['total'];
        $pagedata['pagers'] = $result['pagers'];
        return $this->page('topshop/shop/image/index.html', $pagedata);
    }

    private function __pager($filter, $count, $isImageModal)
    {
        $params['img_type'] = $filter['img_type'];
        $params['orderBy'] = $filter['orderBy'];
        $params['image_cat_id'] = $filter['image_cat_id'];
        if( $filter['image_name'] )
		{
			$params['image_name'] = $filter['image_name'];
		}

        if( $isImageModal )
        {
            $params['imageModal'] = true;
        }

        $params['pages'] = time();
        $total = ceil($count/$this->limit);
        $pagers = array(
            'link'=>url::action('topshop_ctl_shop_image@search',$params),
            'current'=>$filter['page_no'],
            'use_app' => 'topshop',
            'total'=>$total,
            'token'=>time(),
        );
        return $pagers;
    }

    /**
     * @param bool $isImageModal 是否为弹出框
     */
	private function __getListData($isImageModal)
	{
        if( $isImageModal )
        {
            $this->limit = 12;
        }

        $params['shop_id'] = $this->shopId;
        $params['page_no'] = intval(input::get('pages',1));
        $params['page_size'] = intval($this->limit);
		$params['img_type'] = input::get('img_type','item');
		$params['image_cat_id'] = input::get('image_cat_id',0);
		$params['orderBy'] = input::get('orderBy','last_modified desc');
		$params['fields'] = '*';
		if( input::get('image_name',false) !== false )
		{
			$params['image_name'] = input::get('image_name');
		}
        $result['data'] = app::get('topshop')->rpcCall('image.shop.list', $params);
        $result['pagers'] = $this->__pager($params, $result['data']['total'], $isImageModal);

        $catListApiParams = [
            'shop_id' => $this->shopId,
            'img_type' => $params['img_type'],
            'fields' => 'image_cat_id,image_cat_name',
        ];

        $result['imageShopCatList'] = app::get('topshop')->rpcCall('image.shop.cat.imagetype.list',$catListApiParams);
		return $result;
	}

	public function search()
	{
        //是否为弹出框的列表
        if( input::get('imageModal',false) )
        {
            $isImageModal = true;
            $pagedata['imageModal'] = true;
        }
		$result = $this->__getListData($isImageModal);
        $pagedata['activeImageCatId'] = input::get('image_cat_id',0);
        $pagedata['imageShopCatList'] = $result['imageShopCatList'];
        $pagedata['imagedata'] = $result['data']['list'];
        $pagedata['pagers'] = $result['pagers'];
        $pagedata['image_name'] = input::get('image_name',false);

        $pagedata['imageTypeSize'] = kernel::single('image_data_image')->getImageTypeSize(input::get('img_type','item'));

        return view::make('topshop/shop/image/list.html', $pagedata);
	}

	public function delImgLink()
	{
        $params['image_id'] = implode(',', input::get('img_id') );
		try
		{
        	app::get('topshop')->rpcCall('image.delete.imageLink', $params, 'seller');
            $status = 'success';
            $msg = '删除成功';
		}
		catch( Exception $e)
		{
			$msg = $e->getMessage();
            $status = 'error';
		}
        $this->sellerlog('删除图片。图片ID是'.$params['image_id']);
        return $this->splash($status,null,$msg,true);
	}

	public function upImgName()
	{
        $params['url'] = input::get('url');
        $params['image_name'] = input::get('image_name');
        try
        {
            $status = 'success';
            app::get('topshop')->rpcCall('image.shop.upImageName', $params, 'seller');
        }
        catch( LogicExpection $e )
        {
            $mag = $e->getMessage();
            $status = 'error';
        }
        $this->sellerlog('更新图片。图片名称是：'.$params['image_name']);
        $msg = '更新成功';
        return $this->splash($status,null,$msg,true);
	}

    public function loadImageModal()
    {
        $isImageModal = true;
        $result = $this->__getListData($isImageModal);
        $pagedata['imageShopCatList'] = $result['imageShopCatList'];
        $pagedata['imagedata'] = $result['data']['list'];
        $pagedata['pagers'] = $result['pagers'];
        $pagedata['load_id'] = rand(0,999);
        $pagedata['imageModal'] = true;
        if( input::get('isOnlyShow') )
        {
            $pagedata['isOnlyShow'] = input::get('isOnlyShow');
        }
        return view::make('topshop/shop/image/upload.html', $pagedata);
    }

    /**
     * 图片移动文件夹弹出框
     */
    public function loadImageMoveCatModal()
    {
        $pagedata['imageId'] = input::get('image_id');

        $imgType = input::get('image_type','item');
        $catListApiParams = [
            'shop_id' => $this->shopId,
            'fields' => 'image_cat_id,image_cat_name,img_type',
        ];
        $data = app::get('topshop')->rpcCall('image.shop.cat.imagetype.list',$catListApiParams);

        $imageShopCatList['item'] = [];
        $imageShopCatList['shop'] = [];
        foreach( (array)$data as $row)
        {
            $imageShopCatList[$row['img_type']][] = $row;
        }
        $pagedata['imageShopCatList'] = $imageShopCatList;
        $pagedata['activeImageType'] = $imgType;
        return view::make('topshop/shop/image/modal/moveCat.html', $pagedata);
    }

    /**
     * 加载文件夹管理弹出框
     */
    public function loadImgCatModal()
    {
        $imgType = input::get('image_type','item');
        $imageCatId = input::get('image_cat_id',0);
        $catListApiParams = [
            'shop_id' => $this->shopId,
            'img_type' => $imgType,
            'fields' => 'image_cat_id,image_cat_name,img_type',
        ];
        $data = app::get('topshop')->rpcCall('image.shop.cat.imagetype.list',$catListApiParams);
        $pagedata['imageShopCatList'] = $data;
        $pagedata['activeImageType'] = $imgType;
        $pagedata['activeImageCatId'] = $imageCatId;

        if( input::get('folderlist') )
        {
            return view::make('topshop/shop/image/folderlist.html', $pagedata);
        }
        else
        {
            return view::make('topshop/shop/image/modal/folderMange.html', $pagedata);
        }
    }

    public function addImgCat()
    {
        $imgType = input::get('image_type');
        $imageCatName = app::get('topshop')->_('未命名文件夹');
        $catListApiParams = [
            'shop_id' => $this->shopId,
            'img_type' => $imgType,
            'fields' => 'image_cat_id,image_cat_name',
        ];
        $data = app::get('topshop')->rpcCall('image.shop.cat.imagetype.list',$catListApiParams);
        if( $data )
        {
            $arrImageCatName = array_column($data, 'image_cat_name');
            for( $i=0; $i<20; $i++)
            {
                $tmpImageCatName = $i ? $imageCatName.$i : $imageCatName;
                if( !in_array($tmpImageCatName, $arrImageCatName) )
                {
                    $imageCatName = $tmpImageCatName;
                    break;
                }
            }
        }

        try
        {
            $imageCatId = app::get('topshop')->rpcCall('image.shop.cat.add',['image_cat_name'=>$imageCatName, 'img_type'=>$imgType,'shop_id'=>$this->shopId]);
            $status = $imageCatId ? 'success' : 'error';
            $msg = $imageCatId ? ['image_cat_id'=>$imageCatId, 'image_cat_name'=>$imageCatName] : app::get('topshop')->_('创建文件夹失败');
        }
        catch( \LogicException $e )
        {
            $msg = $e->getMessage();
            $status = 'error';
        }
        catch( Exception $e)
        {
            $msg = app::get('topshop')->_('创建文件夹失败');
            $status = 'error';
        }

        if( $status == 'success' )
        {
            $this->sellerlog('创建图片文件夹，image_cat_name：['.$imgType.']:'.$imageCatName);
        }
        return $this->splash($status,null,$msg,true);
    }

    public function delImgCat()
    {
        $imageCatId = input::get('image_cat_id');
        try
        {
            $res = app::get('topshop')->rpcCall('image.shop.cat.delete',['image_cat_id'=>$imageCatId, 'shop_id'=>$this->shopId]);
            $status = $res ? 'success' : 'error';
            $msg = $res ?  app::get('topshop')->_('成功删除') : app::get('topshop')->_('删除失败');
        }
        catch( \LogicException $e )
        {
            $msg = $e->getMessage();
            $status = 'error';
        }
        catch( Exception $e)
        {
            $msg = app::get('topshop')->_('删除失败');
            $status = 'error';
        }

        if( $status == 'success' )
        {
            $this->sellerlog('删除图片文，image_cat_id：'.$imageCatId);
        }
        return $this->splash($status,null,$msg,true);
    }

    public function editImgCat()
    {
        $imageCatId = input::get('image_cat_id');
        $imageCatName = trim(input::get('image_cat_name'));
        $validator = validator::make(
            array('image_cat_name' => $imageCatName),
            array('image_cat_name' => 'required|max:10'),
            array('image_cat_name' => '文件夹名称必填|文件名称不能超过10个字')
        );
        if( $validator->fails() )
        {
            $msg = $validator->messages()->first('image_cat_name');
            return $this->splash('error',null,$msg,true);
        }

        try
        {
            $res = app::get('topshop')->rpcCall('image.shop.cat.update',['image_cat_id'=>$imageCatId, 'image_cat_name'=>$imageCatName,'shop_id'=>$this->shopId]);
            $status = $res ? 'success' : 'error';
            $msg = $res ?  app::get('topshop')->_('创建文件夹成功') : app::get('topshop')->_('创建文件夹失败');
        }
        catch( \LogicException $e )
        {
            $msg = $e->getMessage();
            $status = 'error';
        }
        catch( Exception $e)
        {
            $msg = app::get('topshop')->_('创建文件夹失败');
            $status = 'error';
        }

        if( $status == 'success' )
        {
            $this->sellerlog('更新图片文件夹名称，image_cat_name：['.$imgType.']:'.$imageCatName);
        }
        return $this->splash($status,null,$msg,true);
    }

    /**
     * 移动图片
     */
    public function moveImageCat()
    {
        $imageId = input::get('image_id');
        $imageCatId = input::get('image_cat_id');
        $moveApiParams = [
            'shop_id' => $this->shopId,
            'image_id' => $imageId,
            'image_cat_id' => $imageCatId,
            'img_type' => input::get('img_type'),
        ];

        $msg = '移动完成';
        try
        {
            $result = app::get('topshop')->rpcCall('image.shop.move.cat',$moveApiParams);
            $status = 'success';
        }
        catch( \LogicException $e )
        {
            $msg = $e->getMessage();
            $status = 'error';
        }
        catch( \Exception $e)
        {
            $msg = app::get('topshop')->_('移动失败');
            $status = 'error';
        }

        $params =  array(
            'img_type'=>input::get('img_type','item'),
            'image_cat_id'=>$imageCatId
        );
        $url = url::action('topshop_ctl_shop_image@index', $params);

        if( $status == 'success' )
        {
            $this->sellerlog('移动图片，图片ID：'.$imageId);
        }
        return $this->splash($status,$url,$msg,true);
    }
}
