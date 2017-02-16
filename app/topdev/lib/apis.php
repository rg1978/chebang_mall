<?php

class topdev_apis {

    public function getApiGroupList()
    {
        $apis = $this->getApiList('apis');

        $redis = redis::scene('topdev');
        $redisKey = 'apis'. md5(json_encode($apis));

        $list = $redis->get($redisKey);
        if( !$list )
        {
            foreach ($apis as $key => $value)
            {
                list($class, $method) = explode('@', $value['uses']);
                $handlar = new $class;
                $value['apidesc'] = $handlar->apiDescription;
                $apigroup = $this->getGroupName($class);
                $list[$apigroup]['list'][$key] = $value;
                $list[$apigroup]['name'] = $apigroup;
                $list[$apigroup]['count'] += 1;
            }

            $redis->set($redisKey, json_encode($list));
        }
        else
        {
            $list = json_decode($list,true);
        }

        return $list;
    }

    public function getGroupName($handle)
    {
        $args = explode('_', $handle);
        $appName = $args[0];
        $appTitles = config::get('prism.prismApiName', array());
        $appTitle = $appTitles[$appName] ? $appTitles[$appName] : $appName;
        return $appTitle;
    }

    public function getTopApiGroupList()
    {
        $apis = $this->getApiList('topapi');

        $redis = redis::scene('topdev');
        $redisKey = 'topapi'. md5(json_encode($apis));
        $list = $redis->get($redisKey);
        if( !$list )
        {
            foreach ($apis as $key => $value)
            {
                list($class, $method) = explode('@', $value['uses']);
                $handlar = new $class;
                $value['apidesc'] = $handlar->apiDescription;
                $apigroup = explode('.',$key)[0];
                $list[$apigroup]['list'][$key] = $value;
                $list[$apigroup]['name'] = $apigroup;
                $list[$apigroup]['count'] += 1;
            }
            $redis->set($redisKey, json_encode($list));
        }
        else
        {
            $list = json_decode($list,true);
        }

        return $list;
    }


    public function getSystemParams($isAuth, $type)
    {
        $return = [
            [
                'field' => 'format',
                'type' => 'string',
                'validate' => 'required',
                'example' => 'json',
                'desc' => '返回数据是json格式的，目前只支持json',
            ],
            [
                'field' => 'v',
                'type' => 'string',
                'validate' => 'required',
                'example' => 'v1',
                'desc' => '标识该接口的版本',
            ],
        ];

        if( $isAuth && $type=='topapi')
        {
            $return[] = [
                'field' => 'accessToken',
                'type' => 'string',
                'validate' => 'required',
                'example' => '',
                'desc' => '登录颁发的token',
            ];
        }

        return $return;
    }

    public function getParams($handlar, $type)
    {
        if( $type  == 'apis' )
        {
            $return = $handlar->getParams();
            $params = $return['params'];
        }
        else
        {
            $params = $handlar->setParams();
        }

        $ret = array();
        foreach($params as $key=>$value)
        {
            $field = array();
            $valid = explode('|', $value['valid']);
            foreach( $valid as $val )
            {
                if( $val == 'required' )
                {
                    $field['required'] = true;
                }

                $selectVal = explode(":",$val);
                if( $selectVal[0] == 'in' )
                {
                    $field['select_option'] = explode(',', $selectVal[1]);
                    $field['input_type'] = 'select';
                }
            }

            $field['field'] = $key;
            $field['title'] = $value['title']?:$value['description'];
            $field['type'] = $value['type'];
            $field['validate'] = $value['valid'];
            $field['example'] = $value['example'];
            $field['desc'] = $value['desc'];
            $field['desc'] = $value['desc']?:$value['description'];
            $ret[] = $field;
        }
        return $ret;
    }

    /**
     * 获取API列表
     *
     * @param string $type API类型:apis系统API，topapi聚合API
     */
    public function getApiList($type)
    {
        if( $type == 'topapi' )
        {
            $apis = config::get('topapi.routes.v1');
        }
        else
        {
            $apis = config::get('apis.routes');
        }

        return $apis;
    }

    public function formatSize($bytes)
    {
        switch($bytes)
        {
        case $bytes< 1024:
            $result = $bytes.'B';
            break;
        case ($bytes < pow(1024, 2) ):
            $result =  strval(round($bytes/1024, 2)).'KB';
            break;
        default:
            $result = $bytes/pow(1024, 2);
            $result = strval(round($result, 2)).'MB';
            break;
        }

        return $result;
    }

    public function getResponse($class, $method)
    {
        $rMethod = new ReflectionMethod($class, $method);

        $docComment = $rMethod->getDocComment();
        $docCommentArr = explode("\n", $docComment);

        foreach ($docCommentArr as $comment)
        {
            $comment = trim($comment);

            //标题描述
            if (empty($description) && strpos($comment, '@') === false && strpos($comment, '/') === false)
            {
                $description = substr($comment, strpos($comment, '*') + 1);
                continue;
            }

            //@desc注释
            $pos = stripos($comment, '@desc');
            if ($pos !== false)
            {
                $descComment = substr($comment, $pos + 5);
                continue;
            }

            //@return注释
            $pos = stripos($comment, '@return');
            if ($pos === false)
            {
                continue;
            }

            $returnCommentArr = explode(' ', substr($comment, $pos + 8));
            if (count($returnCommentArr) < 2)
            {
                continue;
            }
            if (!isset($returnCommentArr[2]))
            {
                $returnCommentArr[2] = '';  //可选的字段说明
            }
            else
            {
                //兼容处理有空格的注释
                $returnCommentArr[2] = implode(' ', array_slice($returnCommentArr, 2));
            }

            $returns[] = $returnCommentArr;
        }
        return $returns;
    }
}

