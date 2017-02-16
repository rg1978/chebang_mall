<?php
/**
 * ShopEx licence
 *
 * @category ecos
 * @package image.controller
 * @author shopex ecstore dev dev@shopex.cn
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * @version 0.1
 */

/**
 * 后台图片管理类
 * @category ecos
 * @package image.controller.admin
 * @author shopex ecstore dev dev@shopex.cn
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

use Symfony\Component\HttpFoundation\File\UploadedFile;

class image_ctl_admin_manage extends desktop_controller
{
    /**
     * @var 定义控制器属于哪个菜单区域
     */
    var $workground = 'image_ctl_admin_manage';

    /**
     * act==index页面入口
     * @param null
     * @return string html内容
     */
    function index(){

        if( input::get('view') == 5 )
        {
            $action = array(
                array(
                    'label' => app::get('image')->_('清除'),
                    'submit' => '?app=image&ctl=admin_manage&act=doDelete',
                    'confirm' => app::get('image')->_('确定删除图片源文件？'),
                ),
                array(
                    'label' => app::get('image')->_('放回原处'),
                    'submit' => '?app=image&ctl=admin_manage&act=doRecovery',
                    'confirm' => app::get('image')->_('确定将图片放回原处？'),
                ),
            );
        }
        else
        {
            $action = array(
                array('label'=>app::get('image')->_('上传新图片'),'href'=>'?app=image&ctl=admin_manage&act=image_swf_uploader'
                ,'target'=>'dialog::{title:\''.app::get('image')->_('上传图片').'\',width:500,height:350}'),
                    array('label'=>app::get('image')->_('添加网络图片'),'href'=>'?app=image&ctl=admin_manage&act=image_www_uploader'
                    ,'target'=>'dialog::{title:\''.app::get('image')->_('添加网络图片').'\',width:550,height:200}'),
                        array( 'label' => app::get('image')->_('放入回收站'),
                        'submit' => '?app=image&ctl=admin_manage&act=doRecycling',
                        'confirm' => app::get('image')->_('确定放入回收站？'),
                    ),
                );
        }
        return $this->finder('image_mdl_images',array(
            'title'=>app::get('image')->_('图片管理'),
            'actions'=>$action,
            //'use_buildin_set_tag'=>true,
            'use_buildin_filter'=>true,
            //'use_buildin_tagedit'=>true
            'base_filter'=>array('disabled'=>0),
            'use_buildin_delete'=>false,
        ));
    }

    public function _views()
    {
        $objMdlImages = app::get('image')->model('images');
        $sub_menu = array(
            0=>array('label'=>app::get('image')->_('全部'),'optional'=>false,'filter'=>array('disabled'=>0)),
            1=>array('label'=>app::get('image')->_('平台图片'),'optional'=>false,'filter'=>array('target_type'=>'admin','disabled'=>0)),
            2=>array('label'=>app::get('image')->_('店铺图片'),'optional'=>false,'filter'=>array('target_type'=>['shop'],'disabled'=>0)),
            3=>array('label'=>app::get('image')->_('商家入住图片'),'optional'=>false,'filter'=>array('target_type'=>['seller'],'disabled'=>0)),
            4=>array('label'=>app::get('image')->_('用户图片'),'optional'=>false,'filter'=>array('target_type'=>'user','disabled'=>0)),
            5=>array('label'=>app::get('image')->_('回收站'),'optional'=>false,'filter'=>array('disabled'=>1)),
        );
        return $sub_menu;
    }

    public function doRecycling()
    {
        $this->begin("?app=image&ctl=admin_manage&act=index");
        $id = input::get('id');
        if( $id )
        {
            app::get('image')->model('images')->update(['disabled'=>1], ['id'=>$id]);
        }

        if( input::get('isSelectedAll') == '_ALL_' )
        {
            $msg = '不能删除全部图片';
            return $this->end(false,$msg);
        }

        $msg = '删除成功';
        $this->adminlog("图片放入回收站,不删除真实图片文件[id:{$id}]", 1);
        return $this->end(true,$msg);
    }

    public function doDelete()
    {
        $this->begin("?app=image&ctl=admin_manage&act=index&view=5");

        if( input::get('isSelectedAll') == '_ALL_' )
        {
            $msg = '不能删除全部图片';
            return $this->end(false,$msg);
        }

        $id = input::get('id');
        if( $id )
        {
            try {
                kernel::single('image_data_image')->removeImageFile($id);
            }
            catch( Exception $e)
            {
                return $this->end(false,$msg = $e->getMessage());
            }
        }
        else
        {
            return $this->end(false,'删除失败，参数错误');
        }

        return $this->end(true,'已删除指定的图片源文件');
    }

    public function doRecovery()
    {
        $this->begin("?app=image&ctl=admin_manage&act=index&view=4");

        if( input::get('isSelectedAll') == '_ALL_' )
        {
            $msg = '不能一次恢复所有，请勾选恢复';
            return $this->end(false,$msg);
        }

        $id = input::get('id');
        if( $id )
        {
            app::get('image')->model('images')->update(['disabled'=>0], ['id'=>$id]);
        }

        $this->adminlog("恢复图片[id:{$id}]", 1);
        return $this->end(true,'恢复成功');
    }

    /**
     * 显示上传swf的入口
     * @param null
     * @return string html
     */
    function image_swf_uploader(){
        $pagedata['ssid'] =  kernel::single('base_session')->sess_id();
        $pagedata['IMAGE_MAX_SIZE'] = config::get('image.uploadedFileMaxSize');
        $pagedata['currentcount'] = app::get('image')->model('images')->count(['disabled=>0']);
        return view::make('image/image_swf_uploader.html', $pagedata);
    }

    /**
     * 图片上传的接口
     * @param null
     * @return string 上传的消息
     */
    public function image_upload()
    {
        //echo '<pre>';print_r($_FILES);exit();
       $objLibImage = kernel::single('image_data_image');
       if(!$_FILES['upload_item']['type'] or !$_FILES['upload_item']['name'])
       {
            header('Content-Type:text/html; charset=utf-8');
            echo "{error:'".app::get('image')->_('图片不能为空！')."',splash:'true'}";
            exit;
       }
       try {
           $imageData = $objLibImage->store($_FILES['upload_item'],null,'admin');
       }catch( Exception $e) {
           header('Content-Type:text/html; charset=utf-8');
           echo "{error:'".app::get('image')->_($e->getMessage())."',splash:'true'}";
           exit;
       }
       if(!$imageData['url'])
       {
            header('Content-Type:text/html; charset=utf-8');
            echo "{error:'".app::get('image')->_('图片上传失败')."',splash:'true'}";
            exit;
       }

       $objLibImage->rebuild($imageData['ident'], 'admin');

       $image_id = $imageData['url'];
       $image_src = base_storager::modifier($imageData['url']);

       $this->_set_tag($imageData);
       if($callback = $_REQUEST['callbackfunc'])
       {
           //$_return = "<script>try{parent.$callback('$image_id','$image_src')}catch(e){}</script>";
	   $_return = "<script>if(!parent.window.modedialogInstance){parent.window.opener.image={id:'$image_id',src:'$image_src'};parent.window.close();}else{try{parent.$callback('$image_id','$image_src')}catch(e){}}</script>";

       }

       $_return.="<script>parent.MessageBox.success('".app::get('image')->_('图片上传成功')."');</script>";
       $this->adminlog("上传图片[image_src:{$image_src}]", 1);
       echo $_return;
    }

    /**
     * 设置图片的tag-本类私有方法
     * @param null
     * @return null
     */
    public function _set_tag($imageData)
    {
       $tagctl   = app::get('desktop')->model('tag');
       $tag_rel   = app::get('desktop')->model('tag_rel');
       $data['rel_id'] = $image_id;
       $tags = explode(' ',$_POST['tag']['name']);
       $data['tag_type'] = 'image';
       $data['app_id'] = 'image';
       foreach($tags as $key=>$tag)
       {
           if(!$tag) continue;
            $data['tag_name'] = $tag;
            $tagctl->save($data);
            if($data['tag_id'])
            {
                $data2['tag']['tag_id'] = $data['tag_id'];
                $data2['rel_id'] = $imageData['id'];
                $data2['tag_type'] = 'image';
                $data2['app_id'] = 'image';
                $tag_rel->save($data2);
                unset($data['tag_id']);
            }
       }
    }

    /**
     * 上传网络图片地址-本类私有方法
     * @param null
     * @return string html内容
     */
    function image_www_uploader()
    {
        if($_POST['upload_item'])
        {
            $objLibImage = kernel::single('image_data_image');
            try{
                $image = $objLibImage->store($_POST['upload_item'],null,'admin');
            }catch(Exception $e){
                $html ='<div class="tableform notice">'.$e->getMessage().'</div>';
                $html .='<div class="division"><h5>'.app::get('image')->_('网络图片地址：').'</h5>';
                $html .= view::ui()->form_start(array('method'=>'post'));
                $html .= view::ui()->input(array(

                    'type'=>'url',
                    'name'=>'upload_item',
                    'value'=>'http://',

                    'style'=>'width:70%'
                ));
                $html .='</div>';
                $html .= view::ui()->form_end();
                echo $html."";
                exit;
            }

            $objLibImage->rebuild($image['ident'],'admin');
            $image_src = base_storager::modifier($image['url']);
            $image_id = $image['url'];
            $this->_set_tag($image);
            if($callback = $_REQUEST['callbackfunc']){

                 $_return = "<script>try{parent.$callback('$image_id','$image_src')}catch(e){}</script>";

            }

            $_return.="<script>parent.MessageBox.success('".app::get('image')->_('图片上传成功')."');</script>";

            echo $_return;
            echo <<<EOF
<div id="upload_remote_image"></div>
<script>
try{
    if($('upload_remote_image').getParent('.dialog'))
    $('upload_remote_image').getParent('.dialog').retrieve('instance').close();
}catch(e){}
</script>
EOF;
        }else{
            $html  ='<div class="division"><h5>'.app::get('image')->_('网络图片地址：').'</h5>';
            $html .= view::ui()->form_start(array('method'=>'post'));
            $html .= view::ui()->input(array(

                'type'=>'url',
                'name'=>'upload_item',
                'value'=>'http://',

                'style'=>'width:70%'
                ));
            $html .='</div>';
            $html .= view::ui()->form_end();
            echo $html."";

        }
    }

    /**
     * 远程swf的页面显示
     * @param null
     * @return string html内容
     */
    function image_swf_remote()
    {
        $objLibImage = kernel::single('image_data_image');
        try {
            $imageData = $objLibImage->store($_FILES['Filedata'],null,'admin');
            $objLibImage->rebuild($imageData['ident'],'admin');
            $pagedata['image_id'] = $imageData['id'];
            $pagedata['image_scr'] = base_storager::modifier($imageData['url']);
        }catch( Exception $e ) {
            $pagedata['error_msg'] = $e->getMessage();
        }
        return view::make('image/image_swf_uploader_reponse.html', $pagedata);
    }

    /**
     * 动态的swf页面显示
     * @param null
     * @return string html内容 //目前未发现有使用
     */
    function gimage_swf_remote(){

        $objLibImage = kernel::single('image_data_image');
        $imageData = $objLibImage->store($_FILES['Filedata'],null,'admin');

        $objLibImage->rebuild($imageData['ident'],'admin');

        $pagedata['gimage']['image_id'] = $imageData['url'];

        header('Content-Type:text/html; charset=utf-8');
        return view::make('image/gimage.html', $pagedata);
    }

    /**
     * 图片浏览器
     * @param int 第几页的页面
     * @return string html内容
     */
    function image_broswer($page=1){

        $pagelimit = 10;

        $otag = app::get('desktop')->model('tag');
        $oimage = $this->app->model('images');
        $tags = $otag->getList('*',array('tag_type'=>'image'));

        $pagedata['type'] = $_GET['type'];
        $pagedata['tags'] = $tags;
        return view::make('image/image_broswer.html', $pagedata);

    }

    /**
     * 图片管理列表内容显示
     * @param string 图片的tag
     * @param int 第几页的页面
     * @return string html内容
     */
    function image_lib($tag='',$page=1){
        $pagelimit = 50;

        //$otag = $this->app->model('tag');
        $oimage = $this->app->model('images');

        //$tags = $otag->getList('*',array('tag_type'=>'image'));
        $filter = array();
        if( $_GET['imageType'] == 'admin' )
        {
            $filter['img_type'] = 'admin';
        }

        $images = $oimage->getList('*',$filter,$pagelimit*($page-1),$pagelimit);
        $count = $oimage->count($filter);

        $limitwidth = 100;

        foreach($images as $key=>$row){
            $maxsize = max($row['width'],$row['height']);
            if($maxsize>$limitwidth){
                $size ='width=';
                $size.=$row['width']-$row['width']*(($maxsize-$limitwidth)/$maxsize);
                $size.=' height=';
                $size.=$row['height']-$row['height']*(($maxsize-$limitwidth)/$maxsize);
            }else{
                $size ='width='.$row['width'].' height='.$row['height'];
            }
            $row['size'] = $size;
            $images[$key] = $row;
        }

        $pagedata['images'] = $images;
        $pagedata['pagers'] = view::ui()->pager(array(
            'current'=>$page,
            'total'=>ceil($count/$pagelimit),
            'link'=>'?app=image&ctl=admin_manage&act=image_lib&p[0]='.$tag.'&p[1]=%d&imageType='.$_GET['imageType'],
            ));
        return view::make('image/image_lib.html', $pagedata);

     }

    /**
     * 删除图片
     * @param nulll
     * @return string 图片删除信息json
     */
    function image_del()
    {
        $image_id = $_GET['image_id'];
        if( $image_id && app::get('image')->model('images')->update(['disabled'=>1], ['id'=>$image_id]))
        {
            header('Content-Type:application/json; charset=utf-8');
            echo '{success:"'.app::get('image')->_('删除成功').'"}';
        }
   }

    /**
     * 图片大小配置
     * @param nulll
     * @return string 显示配置页面内容
     */
    public function imageset()
    {
        header("cache-control: no-store, no-cache, must-revalidate");
        $image = app::get('image')->model('images');
        $objLibImage = kernel::single('image_data_image');

       $allsize = array();
        if( input::get('pic') )
        {
            $imageSet = input::get('pic');
            $objLibImage->setImageSetting('item', $imageSet);
            $def_image_set = $imageSet;
        }

        if(!$def_image_set && !$def_image_set = $objLibImage->getImageSetting('item') )
        {
            $def_image_set = config::get('image.image_default_set');
            $curImageSet = $objLibImage->getImageSetting('sysitem');
        }
        else
        {
            $curImageSet = $def_image_set;
        }

		$minsize_set = false;
        foreach($def_image_set as $k=>$v)
        {
            if(!$minsize_set||$v['height']<$minsize_set['height'])
            {
				$minsize_set = $v;
			}
		}

        $pagedata['allsize'] = $def_image_set;
		$pagedata['minsize'] = $minsize_set;
        $pagedata['image_set'] = $curImageSet;
        $pagedata['this_url'] = $this->url;
        return $this->page('image/imageset.html', $pagedata);
    }

    /**
     * 查看图片
     * @param nulll
     * @return string html页面内容
     */
    function view_gimage($image_id){
        $pagedata['image_id'] = $image_id;
        return $this->page('image/images.html', $pagedata);
    }
}//End Class
