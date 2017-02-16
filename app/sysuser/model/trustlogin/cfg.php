<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysuser_mdl_trustlogin_cfg extends dbeav_model{

    function __construct()
    {
        $this->columns = array(
                        'name' => array('label'=>app::get('sysuser')->_('名称'),'width'=>200),
                        'version' => array('label'=>app::get('sysuser')->_('版本'),'width'=>200),
                        'status' =>  array('type' => 'bool' ,'label'=>app::get('sysuser')->_('状态'),'width'=>200),
                        'platform'=>array(
                                'type' =>array (
                                    'web' => app::get('sysuser')->_('标准版'),
                                    'wap' => app::get('sysuser')->_('触屏版'),
                                ),
                                'default' => 'web',
                                'label'=>app::get('ectools')->_('支持平台'),
                                'width'=>120,
                            ),
                   );

        $this->schema = array(
                'default_in_list'=>['name','version','status'],
                'in_list'=>['name','version','status'],
                'idColumn'=>'app_name',
                'columns'=>$this->columns
            );
    }

     /**
     * suffix of model
     * @params null
     * @return string table name
     */
    public function table_name()
    {
        return 'trustlogin_cfg';
    }

    function get_schema()
    {
        return $this->schema;
    }

    //返回接口的数量
    function count($filter='')
    {
        return count($this->getList('*',array('platform'=>'web')));
    }
    /**
     * 取到服务列表 - 1条或者多条
     * @params string - 特殊的列名
     * @params array - 限制条件
     * @params 偏移量起始值
     * @params 偏移位移值
     * @params 排序条件
     */
    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderby=null)
    {
        $trustInfos = [];
        $trustCollection = collect(kernel::single('sysuser_passport_trust_manager')->getTrusts());
        $trustCollection->each(function($trust) use (&$trustInfos) {
            $trustInfos[] = $trust->getInfo();
        });
        //in_array($filter['platform'],$trust->getInfo()['platform'])
        //添加判断pc和wap端的
        foreach ($trustInfos as $key => $value)
        {
            if(!in_array($filter['platform'],$value['platform']))
            {
                unset($trustInfos[$key]);
            }
        }

        return $trustInfos;
    }
}
