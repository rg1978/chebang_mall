<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class base_command_cache extends base_shell_prototype
{

    /**
     * clean command title
     *
     * @var string
     */
    public $command_clean = '清除缓存';

    /**
     * clean command options
     *
     * @var array
     */
    public $command_clean_options = [
        'all' => ['title' => '清空所有缓存资源', 'short' => 'a'],
        'resource' => ['title' => '清空指定缓存资源', 'short' => 'r', 'need_value' => 1],
        'store' => ['title' => '清空Store对应缓存资源', 'short' => 's', 'need_value' => 1],
        
    ];
    
    /**
     * command: cache clean
     *
     * @return null
     */
    public function command_clean()
    {
        if ($this->get_option('all'))
        {
            collect(cache::getStoreResourcesConfig())->each(function($item, $resource) {

                cache::enable();
                cache::resource($resource)->flush();
            });
        }
        elseif($resource = $this->get_option('resource'))
        {
            cache::enable();
            cache::resource($resource)->flush();
        }
        elseif($store = $this->get_option('store'))
        {
            $resource = cache::getStoreConfig($store)['resource'] ?: cache::getStoreConfig(cache::getDefaultDriver())['resource'];
            cache::enable();
            cache::resource($resource)->flush();
        }
        else
        {
            $this->outputCleanHelp();
        }
    }

    /**
     * command: Output clean help.
     *
     * @return null
     */
    protected function outputCleanHelp()
    {
        $output = 'Usage:'.PHP_EOL;
        $output .= 'base:cache clean [options] [--store <store>]'.PHP_EOL;
        $output .= $this->outputOptions('clean');

        echo $output;
    }
    
    /**
     * command: Output clean options.
     *
     * @return null
     */
    protected function outputOptions($command)
    {
        $options = $this->get_options_define('command_'.$command);

        $output = '';
        
        foreach($options as $option=>$define){
            $option_name = '--'.$option;
            if($define['short']){
                $option_name .= ' / -'.$define['short'];
            }
            if($define['need_value']){
                $option_name .= ' ['.$define['need_value'].']';
            }
            $output .= str_pad($option_name,30).$define['title']."\n";
        }
        return $output;
    }

    /**
     * list command title
     *
     * @var string
     */
    public $command_list = '清单';

    /**
     * clean command options
     *
     * @var array
     */
    public $command_list_options = [
        'resource' => ['title' => '资源列表', 'short' => 'a'],
        'store' => ['title' => 'Store列表', 'short' => 'r'],
    ];

    /**
     * command: cache clean
     *
     * @return null
     */
    public function command_list()
    {
        if ($this->get_option('resource'))
        {
            $resources = collect(cache::getResourceConfig())->map(function ($resource, $key) {
                $resource = collect($resource)->map(function ($item, $key) {
                    return is_array($item) ? json_encode($item) : $item;
                })->all();
                $resource['name'] = $key;
                return $resource;
            });
            $this->output_table($resources);
        }
        elseif($this->get_option('store'))
        {
            $stores = cache::getStoreConfig();
            collect($stores)->each(function($config, $storeName) use (&$results) {
                $results[] = [$storeName, $config['resource']?:cache::getStoreConfig(cache::getDefaultDriver())['resource']];
            });
            $this->output_table($results);
        }
        else
        {
            cache::getStoreResourcesConfig();
        }
    }
}
