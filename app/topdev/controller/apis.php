<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topdev_ctl_apis extends topdev_controller {

    public function search()
    {
        //面包屑
        $this->runtimePath = array(
            ['url'=> url::action('topdev_ctl_index@index'),'title' => app::get('topdev')->_('桌面')],
        );

        $search = input::get('q');
        if( !$search )
        {
            redirect::action('topdev_ctl_index@index')->send();
        }
        $this->runtimePath[] = ['title' => app::get('topdev')->_($search.' 相关')];

        $topapisList = kernel::single('topdev_apis')->getTopApiGroupList();
        foreach( $topapisList as $value )
        {
            foreach( $value['list'] as $apiName => $val )
            {
                if( stristr($apiName,$search) || stristr($val['apidesc'], $search) )
                {
                    $searchData[$apiName] = $val;
                }
            }
        }
        $list['topapi'] = $searchData;

        $apisList = kernel::single('topdev_apis')->getApiGroupList();
        foreach( $apisList as $row )
        {
            foreach( $row['list'] as $apiName => $val )
            {
                if( stristr($apiName,$search) || stristr($val['apidesc'], $search) )
                {
                    $searchApisData[$apiName] = $val;
                }
            }
        }
        $list['apis'] = $searchApisData;

        $pagedata['activeGroupList'] = $list;
        return $this->page('topdev/apis/search.html', $pagedata);
    }

    public function group()
    {
        //面包屑
        $this->runtimePath = array(
            ['url'=> url::action('topdev_ctl_index@index'),'title' => app::get('topdev')->_('桌面')],
        );

        $apiType = input::get('apitype');
        if( $apiType == 'topapi' )
        {
            $list = kernel::single('topdev_apis')->getTopApiGroupList();
            $this->runtimePath[] = ['title' => app::get('topdev')->_('APP聚合API列表')];
            $this->activeMenu = 'APP聚合API';
        }
        else
        {
            $list = kernel::single('topdev_apis')->getApiGroupList();
            $this->runtimePath[] = ['title' => app::get('topdev')->_('系统API列表')];
            $this->activeMenu = '系统API';
        }
        $this->runtimePath[] = ['title' => input::get('group').'相关API'];

        $pagedata['activeGroupList'] = $list[input::get('group')];
        $pagedata['apitype'] = input::get('apitype');

        return $this->page('topdev/apis/list.html', $pagedata);
    }

    public function info()
    {
        $apiType = input::get('apitype');

        //面包屑
        $this->runtimePath = array(
            ['url'=> url::action('topdev_ctl_index@index'),'title' => app::get('topdev')->_('桌面')],
        );

        $objApis = kernel::single('topdev_apis');
        $apis = $objApis->getApiList($apiType);
        $method = input::get('method');
        if( $method && $apis[$method] )
        {
            $apiConf = $apis[$method];
            $handle = $apiConf['uses'];
            list($class, $fun) = explode('@', $handle);
            $fun = $fun ? : 'handle';
            $handlar = new $class;
            $pagedata['method'] = $method;
            $pagedata['apidesc'] = $handlar->apiDescription;
            $pagedata['system_params'] = $objApis->getSystemParams($apiConf['auth'], $apiType);
            $pagedata['params'] = $objApis->getParams($handlar, $apiType);
            $pagedata['response'] = $objApis->getResponse($class, $fun);
            if( method_exists($handlar, 'returnJson') )
            {
                $pagedata['returnJson'] = $handlar->returnJson();
            }

            $pagedata['apitype'] = input::get('apitype');

            if( $apiType == 'topapi' )
            {
                $this->runtimePath[] = ['title' => app::get('topdev')->_('APP聚合API列表')];
                $groupName = explode('.',$method)[0];
                $url = url::action('topdev_ctl_apis@group', ['apitype'=>$apiType,'group'=>$groupName]);
                $this->activeMenu = 'APP聚合API';
            }
            else
            {
                $this->runtimePath[] = ['title' => app::get('topdev')->_('系统API列表')];
                $groupName = kernel::single('topdev_apis')->getGroupName($class);
                $url = url::action('topdev_ctl_apis@group', ['apitype'=>$apiType,'group'=>$groupName]);
                $this->activeMenu = '系统API';
            }
            $this->runtimePath[] = ['url'=>$url, 'title' => $groupName];
            $this->runtimePath[] = ['title' => $method.'  '.$pagedata['apidesc']];

            return $this->page('topdev/apis/info.html', $pagedata);
        }
    }

    public function testView()
    {
        //面包屑
        $this->runtimePath = array(
            ['url'=> url::action('topdev_ctl_index@index'),'title' => app::get('topdev')->_('桌面')],
        );

        $objApis = kernel::single('topdev_apis');
        $apiType = input::get('apitype');
        $apis = $objApis->getApiList($apiType);

        $method = input::get('method');
        if( $method && $apis[$method] )
        {
            $apiConf = $apis[$method];
            $handle = $apiConf['uses'];
            list($class, $fun) = explode('@', $handle);
            $fun = $fun ? : 'handle';
            $handlar = new $class;
            $pagedata['groupKey'] = ($apiType == 'apis') ? 'api/'. explode('_',$class)[0] : explode('_',$class)[0];
            $pagedata['method'] = $method;
            $pagedata['apidesc'] = $handlar->apiDescription;
            $pagedata['system_params'] = $objApis->getSystemParams($apiConf['auth'], $apiType);
            $pagedata['params'] = $objApis->getParams($handlar, $apiType);

            $pagedata['apitype'] = input::get('apitype');

            if( $apiType == 'topapi' )
            {
                $this->runtimePath[] = ['title' => app::get('topdev')->_('APP聚合API列表')];
                $groupName = explode('.',$method)[0];
                $url = url::action('topdev_ctl_apis@group', ['apitype'=>$apiType,'group'=>$groupName]);
                $this->activeMenu = 'APP聚合API';
            }
            else
            {
                $this->runtimePath[] = ['title' => app::get('topdev')->_('系统API列表')];
                $groupName = kernel::single('topdev_apis')->getGroupName($class);
                $url = url::action('topdev_ctl_apis@group', ['apitype'=>$apiType,'group'=>$groupName]);
                $this->activeMenu = '系统API';
            }
            $this->runtimePath[] = ['url'=>$url, 'title' => $groupName];
            $this->runtimePath[] = ['url'=>url::action('topdev_ctl_apis@info', ['apitype'=>$apiType,'method'=>$method]), 'title' => $method.'  '.$pagedata['apidesc']];

            return $this->page('topdev/apis/test.html', $pagedata);
        }
    }

    public function testApi()
    {
        //面包屑
        $this->runtimePath = array(
            ['url'=> url::action('topdev_ctl_index@index'),'title' => app::get('topdev')->_('桌面')],
        );

        $apiType = input::get('apitype');
        $method = trim(input::get('method'));

        $objApis = kernel::single('topdev_apis');
        $apis = $objApis->getApiList($apiType);

        $apiParams = input::get('params');
        if( $apiType == 'apis' )
        {
            $url = kernel::base_url(1).kernel::url_prefix().'/api';
            $pagedata['apiParams'] = $apiParams;
            $apiParams['method'] = $method;
            $apiParams['timestamp'] = time();
            $apiParams['sign_type'] = 'MD5';
            $apiParams['sign'] = base_rpc_validate::sign($apiParams,base_certificate::token());
        }
        else
        {
            $url = kernel::base_url(1).kernel::url_prefix().'/topapi';
            $pagedata['apiParams'] = $apiParams;
            $apiParams['method'] = $method;
        }

        $runtimestart = microtime(true);
        $result = client::post($url, ['body' => $apiParams])->getBody();
        $runtimestop= microtime(true);
        $runtime = round(($runtimestop - $runtimestart) , 4);

        $pagedata['getUrl'] = $getUrl = $url.'?'.http_build_query($apiParams);
        $pagedata['apiRunTime'] = $runtime;
        $pagedata['filesize'] = kernel::single('topdev_apis')->formatSize(strlen($result));
        $pagedata['result'] = $result;
        $html = view::make('topdev/apis/result.html', $pagedata);
        return response::json(['html'=>strval($html)]);
    }
}
