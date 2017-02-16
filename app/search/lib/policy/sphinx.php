<?php

use Foolz\SphinxQL\SphinxQL;
use Foolz\SphinxQL\Helper;
use Foolz\SphinxQL\Connection;
use Foolz\SphinxQL\Drivers\SimpleConnection;

class search_policy_sphinx implements search_interface_policy {

    public $name = 'sphinx搜索';
    public $type = 'sphinx';
    public $description = '基于sphinxql开发的搜索引擎';

    /**
     * 当前查询的索引名称
     */
    public $index = null;

    /**
     * 查询需要count的字断定义
     */
    public $countColumns = null;

    /**
     * 查询的关键字
     */
    private $word = null;

    /**
     * 连接
     */
    private $conn = null;

    /**
     * _config 获取sphinxql连接配置信息
     *
     * @access private
     * @return array
     */
    private function __config()
    {
        $host = config::get('search.sphinx.host');
        if (!empty($host))
        {
            $option ['host']= $host;
        }

        $params = ($option ['host'] ? $option ['host'] : '127.0.0.1:9306');
        list( $host, $port ) = explode(':', $params);
        $configParams['host'] = $host;
        $configParams['port'] = $port;

        return $configParams;
    }//End Function

    /**
     * connection
     *
     * @access protected
     * @return object
     */
    public function link()
    {
        if (is_resource( $this->conn ))
        {
            return $this->conn;
        }

        $this->conn = new Connection();
        $this->conn->setParams($this->__config());

        return $this;
    }//End Function

    public function index($index)
    {
        $this->index = $index;
        return $this;
    }

    /**
     *__construct 初始化类,连接sphinx服务
     */
    public function __construct()
    {
        $this->link();
    }//End Function

    /**
     * 设置需要count的字断和别名
     *
     * @param $column 需要count的字断
     * @param $as count后的别名
     */
    public function setCountColumns($column, $as)
    {
        if( empty($column) ) return $this;

        if( $column == '*')
        {
            $this->countColumns = 'count(*)';
        }
        else
        {
            $this->countColumns = 'count(DISTINCT '.$column.')';
        }

        if( $as ) $this->countColumns .= 'as '.$as;

        return $this;
    }

    /**
     * 执行搜索
     *
     * @param string $cols 需要查询的字段
     * @param int $offset
     * @param int limit
     * @param string|array 排序
     * @param string 分组
     */
    public function select($cols='*', $offset=0, $limit=-1, $orderBy=null, $groupBy='')
    {
        $cols = $this->countColumns ? ($cols.','.$this->countColumns) : $cols;

        $setting = $this->getIndexParams($this->index)['setting'];
        if( $offset<=0 ) $offset = 0;
        if( $setting['max_matches'] && $offset >= $setting['max_matches'] ) $offset -= 1;

        if( $limit <= 0 || !$limit ) $limit = $setting['max_limit'];

        $sq = SphinxQL::create($this->conn)
            ->select($cols)
            ->from($this->index)
            ->offset($offset)
            ->limit($limit);
        //match搜索
        foreach( (array)$this->match as $title=>$val )
        {
            if( $val )
            {
                $sq->match($title, $sq->expr($val));
            }
        }
        //where
        if($this->filter) $this->__filter($sq);

        //排序
        $this->__preOrderBy($orderBy, $setting, $sq);
        //分组
        empty($groupBy) ?: $sq->groupBy($groupBy);
        //查询参数
        $option = ['ranker'=>$setting['ranker'],'max_matches'=>$setting['max_matches']];
        foreach( $option as $key=>$val )
        {
            $sq->option($key, $val);
        }

        //打印执行sql
        //echo $sq->compile()->getCompiled();

        //返回查询值
        $list['list'] = $sq->execute();

        if( !$this->countColumns )
        {
            $total = Helper::create($this->conn)->showMeta()->execute();
            $list['total_found'] = $total[1]['Value'];
        }

        return $list;
    }

    /**
     * 设置排序
     *
     */
    private function __preOrderBy($orderBy=null, $setting, &$sq)
    {
        if( $orderBy )
        {
            $orderBy = is_array($orderBy) ? implode(' ', $orderBy) : $orderBy;
            array_map(function($o) use (&$sq){
                $permissionOrders = ['asc', 'desc', ''];
                @list($sort, $order) = explode(' ', trim($o));
                $sq->orderBy($sort, $order);
            }, explode(',', $orderBy));

            $string = ' ORDER BY '.(is_array($orderBy) ? implode(' ',$orderBy) : $orderBy);
        }
        else
        {
            $sq->orderBy($setting['order_value'], $setting['order_type']);
        }

        return $this;
    }

    public function count()
    {
        $sq = SphinxQL::create($this->conn)
            ->select('id')
            ->from($this->index);

        //match搜索
        foreach( (array)$this->match as $title=>$val )
        {
            $sq->match($title, $sq->escapeMatch($val));
        }
        //where
        if($this->filter) $this->__filter($sq);
        $sq->execute();

        $total = Helper::create($this->conn)->showMeta()->execute();
        $totalFound = $total[1]['Value'];
        return $totalFound;
    }

    public function insert($val=[])
    {
    }

    public function update($val=array(),$where)
    {
    }

    public function delete($val=array())
    {
    }

    /**
     *  判断连接状态
     */
    public function status()
    {
        try{
            $result = Helper::create($this->conn)->showStatus()->execute();
        }catch( Exception $e){
            return false;
        }

        if($result[1]['Variable_name'] == 'connections' || $result[1]['Counter'] == 'connections')
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 检查查询条件是否需要使用sphinx进行搜索
     *
     * @param array $filter 查询条件
     *
     * @return string|bool 如果需要查询则返回处理过的filter
     */
    public function queryFilter($filter)
    {
        //查询到索引的字段
        $columns = $this->getDescribe();
        $describeInt = $columns['int'];
        $describeField = $columns['field'];

        foreach($filter as $key=>$val )
        {
            if( $val === null || $val === "" ) continue;

            $cols = explode('|',$key);
            $col = $cols[0];

            if( $col == 'search_keywords' )
            {
                $this->word = implode(' ', $this->splitWords($val));
                $searchKeyword['*'] =  $this->word;
            }

            if( in_array($col,$describeInt ) )
            {
                $intFilter[$key] = $val;
                continue;
            }

            if( in_array($col, $describeField) )
            {
                if( !is_array(current($val)) )
                {
                    $searchKeyword[$col] =  implode(' | ' , (array)$val);
                }
                else
                {
                    foreach( $val as $row )
                    {
                        $searchKeyword[$col] =  implode(' | ' , (array)$row);
                    }
                }
            }
        }

        $this->match = $searchKeyword;
        $this->filter = $intFilter;
        return  $this;
    }

    /**
     * 索引管理，model调用
     */
    public function getIndex()
    {
        $result = Helper::create($this->conn)->showTables()->execute();
        foreach($result as $key=>$row)
        {
            $data[$key]['index_name'] = $row['Index'];
            $data[$key]['index_type'] = $row['Type'];
        }
        return $data;
    }

    /**
     * 检查索引是否可以直接返回数据，如果只是查询int 那么在sphinx是可以直接返回数据
     * 则不用再到db去再次查询
     */
    public function checkColumnsReturn($columns)
    {
        if( $columns == '*' ) return false;

        $colsArr = explode(',', $columns);
        //查询到索引的字段
        $columns = $this->getDescribe();
        $describeField = $columns['field'];
        $intCols = $columns['int'];
        foreach( $colsArr as $cols )
        {
            if( in_array(trim($cols), $describeField) || !in_array(trim($cols), $intCols))
            {
                return false;
            }
        }
        return true;
    }

    /**
     * 获取索引的具体字段
     *
     * @param $index 索引名称
     */
    public function getDescribe($index, $new=false)
    {
        if( $new ) return Helper::create($this->conn)->describe($index)->execute();

        if(!$index) $index = $this->index;
        if( $this->columns[$index] ) return $this->columns[$index];

        $this->columns[$index] = app::get('search')->getConf('describe_'.$index);
        if(!$this->columns[$index])
        {
            $this->columns[$index] = $this->__setDescribe($index);
        }

        return $this->columns[$index];
    }

    /**
     * set_describe 设置对应索引中可搜索字段
     * @param string $index 索引名称
     * @access private
     * @return array $columns 可以索引字段
     */
    private function __setDescribe($index)
    {
        if(!$index) $index = $this->index;

        $indexDesc = Helper::create($this->conn)->describe($index)->execute();
        foreach( (array)$indexDesc as $row )
        {
            if( $row['Agent'] )
            {
                $data = Helper::create($this->conn)->describe($row['Agent'])->execute();
            }
            else
            {
                $data = $indexDesc;
            }

            break;
        }

        $columns = array();
        foreach($data as $key=>$val)
        {
            if($val['Type'] != 'field')
            {//可返回字段
                $columns['int'][] = $val['Field'];
            }
            else
            {//可检索字段
                $columns['field'][] = $val['Field'];
            }
            $columns['all'][] = $val['Field'];
        }
        app::get('search')->setConf('describe_'.$index,$columns);
        return $columns;
    }

    /**
     * 设置索引所需参数信息
     *
     * @param string $indexName 索引名称
     * @param array $params  索引参数
     *
     * @return bool
     */
    public function setIndexParams($indexName, $params)
    {
        $key = 'search_index_setting_'.$indexName;
        return app::get('search')->setConf($key,$params);
    }

    /**
     * @brief 根据索引名称获取索引的配置参数信息
     *
     * @param $indexName
     *
     * @return array
     */
    public function getIndexParams($indexName)
    {
        $tablesInfo = $this->getDescribe($indexName);
        $column = array_combine($tablesInfo['int'],$tablesInfo['int']);
        $setting = app::get('search')->getConf('search_index_setting_'.$indexName);

        $data['setting']['ranker'] = empty($setting['ranker']) ? 'proximity_bm25' : $setting['ranker'];
        $data['setting']['order_value'] = empty($setting['order_value']) ? current($column) : $setting['order_value'];
        $data['setting']['order_type'] =  empty($setting['order_type']) ? 'desc' : $setting['order_type']; //默认降序
        $data['setting']['max_limit'] = empty($setting['max_limit']) ? '1000' : $setting['max_limit']; ;
        $data['setting']['max_matches'] = empty($setting['max_matches']) ? '1000000' : $setting['max_matches'];//默认分页数目

        $data['search_ranker'] =
            array(
                'proximity_bm25'=>'proximity_bm25',
                'bm25'=>'bm25',
                'none'=>'none',
                'wordcount'=>'wordcount',
                'proximity'=>'proximity',
                'matchany'=>'matchany',
                'fieldmask'=>'fieldmask'
            );
        $data['column'] = $column;
        return $data;
    }

    /**
     * buildExcerpts 高亮显示
     *
     * @param string $text  待高亮的字符串
     * @param array  $opts  sphinx BuildExcerpts的opt参数
     * @param string $index 索引名称
     * @access public
     * @return string        添加过标签的字符串
     */
    public function buildExcerpts($text, $options=array(), $index=null)
    {
        if( !$this->word ) return $text;

        $textStr = is_array($text) ? implode('#highlight#', $text) : $text;

        if(!$index) $index = $this->index;

        $this->indexDescribe = $this->indexDescribe ?: $this->getDescribe($index,true);
        foreach( (array)$this->indexDescribe as $row )
        {
            if( $row['Agent'] )
            {
                $index = $row['Agent'];
            }
        }

        if(empty($options))
        {
            $options=array(
                'before_match'=>'<span class=highlight>',
                'after_match'=>'</span>',
                'limit'=>strlen($textStr),
            );
        }

        $result = Helper::create($this->conn)->callSnippets($textStr, $index, $this->word, $options)->execute();

        return $result[0]['snippet'] ? explode("#highlight#", $result[0]['snippet']) : $text;
    }//End Function

    private function __filter(&$sq)
    {
        if( !is_array($this->filter) ) return null;
        //索引字段
        $intCols = $this->getDescribe()['int'];
        // 过滤无用的filter条件
        $filter = array_where($this->filter, function($filterKey, $filterValue) use ($intCols) {
            return !is_null($filterValue) &&
                (isset($intCols[$filterKey]) || strpos($filterKey, '|'));
        });

        foreach($this->filter as $filterKey => $filterValue)
        {
            if (strpos($filterKey, '|'))
            {
                list($columnName, $type) = explode('|', $filterKey);
                $sType = $this->__processTypeSql($type);
            }
            else
            {
                $columnName = $filterKey;
                $sType = is_array($filterValue) ? 'IN' : '=';
            }

            if( is_array($filterValue) )
            {
                foreach( $filterValue as $key=>$val )
                {
                    $filterValue[$key] = intval($val);
                }
            }
            else
            {
                $filterValue = intval($filterValue);
            }

            $sq->where($columnName, $sType, $filterValue);
        }
        return $this;
    }

    private function __processTypeSql($type)
    {
        $FilterArray=[
            'than'    => '>',
            'nequal'  => '=',
            'noequal' => '<>',
            'tequal'  => '=',
            'sthan'   => '<=',
            'bthan'   => '>=',
            'between' => 'BETWEEN',
            'in'      => 'IN',
            'notin'   => 'NOT IN',
        ];
        return $FilterArray[$type];
    }

    /**
     * 分词
     * @param $word 搜索的关键字
     */
    public function splitWords($word)
    {
        $segmentServer = config::get('search.segment');
        $segmentClass = $segmentServer[config::get('search.segment_default')];
        if( !$segmentClass ) return $word;

        $objSegment = kernel::single($segmentClass);

        return $objSegment->split_words($word);
    }

}//End Class

