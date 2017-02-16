<?php

class search_policy_mysql implements search_interface_policy {

    public $name = '开发测试环境';
    public $type = 'mysql';
    public  $description = '默认开发使用mysql搜索';

    /**
     * 查询需要count的字断定义
     */
    public $countColumns = null;


    public function index($class)
    {
        $this->index = $class;
        return $this;
    }

    public function checkColumnsReturn($columns)
    {
        return true;
    }

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
            $table = $this->index->colsByTable($column);
            $this->countColumns = 'count('.$table.'.`'.$column.'`)';
        }

        if( $as ) $this->countColumns .= 'as '.$as;

        return $this;
    }

    public function select($cols='*', $offset=0, $limit=-1, $orderBy=null, $groupBy='')
    {
        $cols = $this->countColumns ? ($cols.','.$this->countColumns) : $cols;
        $data['list'] = $this->index->getList($cols, $this->filter, $offset, $limit, $orderBy, $groupBy);
        if( !$this->countColumns )
        {
            $data['total_found'] = $this->count($filter);
        }
        return $data;
    }

    public function buildExcerpts($text, $opts)
    {
        if( !$this->word ) return $text;
        $textStr = is_array($text) ? implode('#highlight#', $text) : $text;

        if(empty($opts)){
            $opts=array(
                'before_match'=>'<span class="highlight">',
                'after_match'=>'</span>'
            );
        }

        $opts_str = $opts['before_match'].$this->word.$opts['after_match'];
        $highlightText = str_replace($this->word,$opts_str,$textStr);

        if( is_array($text) )
        {
            $highlightText = explode('#highlight#', $highlightText);
        }

        return $highlightText;
    }

    public function count()
    {
        return $this->index->count($this->filter);
    }

    public function queryFilter($filter)
    {
        $this->filter = $filter;
        $this->word = $filter['search_keywords'];
        return $this;
    }

    public function link()
    {
        return true;
    }//End Function

    public function insert($val=array())
    {
        return true;
    }

    public function update($val=array(),$where)
    {
        return true;
    }

    public function delete($val=array())
    {
        return true;
    }

    public function status()
    {
        return true;
    }
}//End Class

