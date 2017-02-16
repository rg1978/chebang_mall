<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class site_view_compiler
{
    static private $_cache = array();
    static private $_wgbar = array();

    /**
     * compiler: 模板主区域
     * 
     * @param array $attrs 标签属性
     * @return string
     */
    public function compile_main($attrs, &$compiler)
    {
        // todo: 目前不再支持主区域劫持.
        $view = $attrs['view'] ? trim($attrs['view'], '\'"') : null;
        if ($view) {
            return "echo theme::partial('${view}', theme::getContentArguments());";
        } else {
            return "echo theme::content();";
            
        }
    }

    /**
     * compiler: 模板require
     * 
     * @param array $attrs 标签属性
     * @return string
     */    
    public function compile_require($attrs, &$compiler)
    {
        // 判断是否有必要属性:file
        if (empty($attrs['file'])) throw new \InvalidArgumentException("missing 'file' attribute in include tag in " . __FILE__ . ' on line ' . __LINE__, E_USER_ERROR);
        //todo: 目前临时做法, 后续会通过更改模板习惯
        //$include_file = str_replace('block/', '', $attrs['file']);
        $include_file = $attrs['file'];
        return "echo theme::partial({$include_file});";
    }


    public function compile_widgets($attrs, &$compiler)
    {
        $id = $attrs['id'] ? trim($attrs['id'], '\'"') : null;
        $isCache = true;
        if (isset($attrs['isCache'])) {
            $isCache = (trim(array_get($attrs, 'isCache', true), '\'"') == 'false') ? false : true;
        }

        if ($isCache) {
            if (isset($attrs['ttl'])) {
                $ttl = (int)trim($attrs['ttl'], '\'"');
            }
            $ttl = $ttl > 0 ? $ttl : array_get(cache::getStoreConfig($cacheStore), 'params.ttl', 1);
        }
            
        $current_file = theme::getCurrentLayoutOrPartial();
        $current_file = substr($current_file, strpos($current_file, ':') + 1);

        
        $slot = intval(static::$_wgbar[$current_file]++);

        $widgets_group = $this->getInstanceWidgets($current_file, $id, $slot);

        $id = $id ?: $slot;
        
        /*--------------------- 获取全部widgets ------------------------------*/
        if(is_array($widgets_group)) {

            $return .= '$func = function() {';            
            $return .= sprintf('$__THEME_URL = \'%s\';', kernel::get_themes_host_url().'/'.theme::getThemeName());
            
            $return .= '$body= \'\';';
            
            foreach($widgets_group as $widget) {
                $return .= $this->__site_parse_widget_instance($widget, $compiler);
            }

            $return .= 'return str_replace(\'%THEME%\',$__THEME_URL, $body);';

            $return .= '};';

            $cacheStore = 'theme-widgets';
            if ($isCache) {
                $return .= sprintf('echo cache::store(\'%s\')->remember(\'%s\', %d , $func);',
                               $cacheStore,
                               $current_file.'-'.$id,
                               $ttl);
            } else {
                $return .= 'echo $func();';
            }
                
            return $return;
        }else{
            return '';
        }
    }

    private function loadPageWidgetInstances($coreFile)
    {
        kernel::single('base_session')->start();
        if (!static::$_cache[$coreFile]) {
            if (theme::isPreview() && $_SESSION['WIDGET_TMP_DATA'][$coreFile]&&is_array($_SESSION['WIDGET_TMP_DATA'][$coreFile]))
            {
                $widgets = (array)$_SESSION['WIDGET_TMP_DATA'][$coreFile];
            }
            else
            {
                $qb = app::get('site')->database()->createQueryBuilder();
                $qb->select('*')->from('site_widgets_instance')->where('core_file='.$qb->createPositionalParameter($coreFile))->orderBy('widgets_order', 'asc');
                $widgets = app::get('site')->model('widgets_instance')->tidy_data($qb->execute()->fetchAll());
            }

            foreach($widgets as $key=>$widget){
                if($widget['core_id'])
                {
                     $allWidgetsGroup['id'][$widget['core_id']][] = $widgets[$key];
                }
                else
                {
                    $allWidgetsGroup['slot'][$widget['core_slot']][] = $widgets[$key];
                }
            }
            
            static::$_cache[$coreFile] = $allWidgetsGroup;
        }
    }

    private function getInstanceWidgets($coreFile, $id, $slot)
    {
        $this->loadPageWidgetInstances($coreFile);
        if ($id) {
            $widgets_group = static::$_cache[$coreFile]['id'][$id];
        } else {
            $widgets_group = $allWidgetsGroup[$coreFile]['slot'][$slot];
        }
        return $widgets_group;
    }
    
    

    private function __site_parse_widget_instance($widget, &$wg_compiler, $type)
    {
        $widgets_config = kernel::single('site_theme_widget')->widgets_config($widget['widgets_type'], $widget['app'], $widget['theme']);

        // widget 目录路径
        $widget_dir = $widgets_config['dir'];
        // widget 标识唯一来源
        $widget_flag = $widgets_config['flag'];
        // widget 函数名
        $widget_run = $widgets_config['run'];
        // widget url地址
        $widgets_url = $widgets_config['url'];
        // widget 处理函数所在文件完整路径
        $func_file = $widgets_config['func'];
        // widget 模板文件完整路径
        $tpl =  $widget_dir . '/' .$widget['tpl'];

        //如果不存在模板, 则返回空
        if(!file_exists($tpl)) return '';

        $params = (is_array($widget['params'])) ? $widget['params'] : array();
        $output = '$__widgets_setting = '.var_export($params,1).';';

        if(file_exists($func_file))
        {
            //todo:最简单的方式取一下数据，否则缓存控制器无法得知widgets_instance会影响到缓存
            $output .= 'if(!function_exists("'.$widget_run.'")) require("'.$func_file.'");$__widgets_data = '.$widget_run.'($__widgets_setting);';
        }
        $output .= sprintf('$__widgets_id = \'%s\';', $widget['widgets_id']);

        $pattern_from = array(
            '/(\'|\")(images\/)/is',
            '/((?:background|src|href)\s*=\s*["|\'])(?:\.\/|\.\.\/)?(images\/.*?["|\'])/is',
            '/((?:background|background-image):\s*?url\()(?:\.\/|\.\.\/)?(images\/)/is',
        );

        $pattern_to = array(
            "\$1" . $widgets_url .'/' . "\$2",
            "\$1" . $widgets_url .'/' . "\$2",
            "\$1" . $widgets_url .'/' . "\$2",
        );

        $content=preg_replace($pattern_from, $pattern_to, $content);

        theme::uses($widgets['theme']);
        $path = theme::getThemeNamespace('widgets/'.$widget['widgets_type'].'/'.$widget['tpl']);
        $output .= sprintf('$body .= view::make(\'%s\', array(\'data\' => $__widgets_data, \'setting\' => $__widgets_setting, \'widgets_id\' => $__widgets_id))->render();', $path);
        
        return $output;
    }//End Function
}
