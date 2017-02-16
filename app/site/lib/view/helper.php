<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class site_view_helper
{

    public function function_header($params, $template)
    {
        $defaultSeo = seo::get_default_seo();

        $title = theme::getTitle() ? theme::getTitle() : app::get('site')->getConf('site.name');
        $title = $title ? $title : $defaultSeo['seo_title'];
        $keywords = theme::getKeywords() ? theme::getKeywords() : $defaultSeo['seo_keywords'];
        $description = theme::getDescription() ? theme::getDescription() : $defaultSeo['seo_content'];
        $noindex = theme::getNoindex()? true : false;
        $nofollow = theme::getNofollow()? true : false;
        $icon = theme::getIcon() ?: app::get('site')->res_url.'/favicon.ico';

        $html = theme::getHeaders() ?: '';

        $pagedata['seo'] = compact('title', 'keywords', 'description', 'noindex', 'nofollow', 'icon');
        // 通过seo类处理后生成对应的header
        //$html .= view::make('site/common/header.html', $pagedata)->render();

        $service = kernel::service("site_view_helper.".$params['app']);
        if(method_exists($service, 'function_header'))
        {
            $html .= $service->function_header($params, $template, $pagedata);
        }

        $html = ecos_cactus('site','check_demosite',$html);
        return $html;
    }//End Function

    public function function_footer($params, $template)
    {
        $service = kernel::servicelist("site_view_helper.".$params['app']);
        if(method_exists($service, 'function_footer'))
        {
            $html = $service->function_footer($params);
        }

        $obj = kernel::service('site_footer_copyright');
        if(is_object($obj) && method_exists($obj, 'get')){
            $html .= $obj->get();
        }else{
            if(!defined('WITHOUT_POWERED') || !constant('WITHOUT_POWERED')){
                $html .= ecos_cactus('site','copyr',$html);
            }
        }
        if (isset($_COOKIE['site']['preview'])&&$_COOKIE['site']['preview']=='true'){
            $base_dir = kernel::base_url();
            $remove_cookie= "$.cookie.raw = true; $.removeCookie('site[preview]',{path:'".$base_dir."/'});";
            $set_window = '$(document.body).addClass("set-margin-body");
            moveTo(0,0);
            resizeTo(screen.availWidth,screen.availHeight);';
            $html .='<style>
                .set-margin-body{margin-top:36px;}#_theme_preview_tip_{width:100%;position:absolute;left:0;top:0;background:#FCE2BC;height:25px;line-height: 25px;padding:5px 0;border-bottom:1px solid #FF9900;box-shadow:0 2px 5px #CCCCCC;}#_theme_preview_tip_ span.msg{float:left;_display:inline;zoom:1;line-height:25px;margin-left:10px;color:#333;}#_theme_preview_tip_ button.btn{vertical-align:middle;color:#333;display:block;float:right;margin:0 10px;}
        </style>
            <div id="_theme_preview_tip_">
            <span class="msg">'.app::get('site')->_('目前正在预览模式').'</span>
            <button class="btn" onclick="'.$remove_cookie.'this.disabled=true;location.reload();"><span><span>'.app::get('site')->_('退出预览').'</span></span></button>
            </div>
            <script>'.$set_window.'$(window).on("unload",function(){'.$remove_cookie.'});</script>';
        }

        $icp = app::get('site')->getConf('system.site_icp');
        if( $icp )
            $html .= '<div style="text-align: center;">'.$icp.'</div>';

        return $html;
    }//End Function

    public function function_template_filter($params, $template)
    {

        if($params['type']){
            $theme = kernel::single('site_theme_base')->get_default($params['platform']);
            $obj = kernel::single('site_theme_tmpl');
            $theme_list = $obj->get_edit_list($theme);
            $pagedata['list'] = $theme_list[$params['type']];
            unset($params['type']);
            $pagedata['selected'] = $params['selected'];
            unset($params['selected']);
            if(is_array($params)){
                foreach($params AS $k=>$v){
                    $ext .= sprintf(' %s="%s"', $k, $v);
                }
            }
            $pagedata['ext'] = $ext;
            return view::make('site/admin/theme/tmpl/template_filter.html', $pagedata)->render();
        }else{
            return '';
        }
    }//End Function

    function function_vcode($params, $template){
        $label = $params['label'] ? $params['label'] : app::get('site')->_('看不清楚?换个图片');
        $key = $params['key'] ? $params['key'] : 'test';
        $params['maxlength'] = $params['maxlength'] ? $params['maxlength'] : 4;
        $url = 'index-gen_vcode-'.$key.'-'.$params['maxlength'].'.html';
        $handle = '<a href="'.$url.'" class="auto-change-verify-handle">'.$label.'</a>';
        $img = '<img src="'.$url.'" alt="'.app::get('site')->_('验证码').'" title="'.app::get('site')->_('点击更换验证码').'" class="auto-change-verify-handle">';
        if($params['is_input'] == 'true'){
            $name = $params['input_name'];
            $str = '<input type="text" name="'.$name.'"  maxlength="'.$params['maxlength'].'" class="x-input verify-input" placeholder="'.app::get('site')->_('验证码').'" vtype="required&&number">';
        }
        $html = $str.$img.$handle;
        echo $html;
    }


}//End Class
