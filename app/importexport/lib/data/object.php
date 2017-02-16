<?php
/*
 * 提供导出导出数据处理
 * */
class importexport_data_object{

    /**
     * 实例对应的model
     *
     * @params string $class_model  例如:b2c_mdl_members
     */
    var $orderBy = '';
    public function __construct($class_model){
        //实例化要导出或导入的model
        $model = substr(stristr($class_model,'mdl_'),4);
        $app_id = substr($class_model,0,strpos($class_model,'_mdl'));
        $this->model = app::get($app_id)->model($model);

        //导出导入数据组织扩展
        $object =  kernel::service('importexport.'.$class_model);
        if( is_object($object) ){
            $this->extends = $object;
        }

        $this->set_group();
    }

    public function getObjectModel()
    {
        return $this->model;
    }
    
    /**
     * 设置导出字段标题
     * 
     * @param mixed $fields array or string
     * 示例：array('name','pwd') or 'name,pwd'
     * */
    public function set_title($fields)
    {
        $title = $this->get_title();
        $tmpTitle = array();
        if($fields)
        {
            if(is_string($fields))
            {
                $fields = explode(',', $fields);
            }
            
            foreach ($fields as $val)
            {
                if(array_key_exists($val, $title))
                {
                    $tmpTitle[$val] = $title[$val];
                }
            }
        }
        
        if(!$tmpTitle)
        {
            $tmpTitle = $title;
        }
        
        $this->title = $tmpTitle;
    }

    /**
     * 设置每次getList分页条数
     */
    public function set_limit($limit){
        return $this->limit = $limit;

    }

    public function get_limit(){
        return $this->limit ? $this->limit : 100;
    }

    /**
     * 设置导出排序
     */
    public function set_orderBy($orderBy){
        if($orderBy)
        {
            return $this->orderBy = $orderBy.' desc';
        }
    }

    public function get_orderBy(){
        return $this->orderBy;
    }


    /**
     * 导出数据设置分组
     *
     * @params $col string 导出数据分组字段
     */
    public function set_group($col=null)
    {
        if( empty($col) ) return ;

        if( $this->extends && method_exists($this->extends, 'set_group') )
        {
            $col = $this->extends->set_group($col);
        }

        return $this->group_col = $col;
    }

    public function get_template($group_val=0){
        $title = $this->get_title();
        if( $group_val ){
            return $title[$group_val];
        }else{
            return $title;
        }
    }

    /*
     * 获取导出数据
     * */
    public function fgetlist( &$outputData,$filter,$offset){
        $limit = $this->get_limit();
        if(!$this->title){
            $this->title = $this->get_title();
        }

        $contents = $this->get_content($filter,$offset,$limit);
        if(!$contents) return false;

        $outputData = array();
        foreach($contents as $group_val=>$content){
            if( $this->group_col ){
                $outputGroupData = array_merge(array($this->title[$group_val]),$contents[$group_val]);
                $outputData = array_merge($outputGroupData,$outputData);
            }else{
                if($offset === 0){//第一次需要头部数据
                    $outputData = array_merge(array($this->title),$content);
                }else{
                    $outputData = $content;
                }
            }
        }
        return true;
    }//end function

    /*
     * 获取导出字段
     */
    public function get_title()
    {
        $title = $this->_title();

        if( $this->extends && method_exists($this->extends, 'get_title') )
        {
            $title = $this->extends->get_title($title);
        }
        return $title;
    }

    private function _title()
    {
        $cols = $this->model->_columns();
        $title = array();
        foreach( $cols as $col => $val )
        {
            if( !$val['deny_export'] ){//不进行导出导入字段
                if(!$val['label'] && !$val['comment'])
                {
                    $title[$col] = $col;
                }
                else
                {
                    $title[$col] = $val['label'] ? $val['label'] : $val['comment'];
                }
            }
        }
        return $title;
    }

    /**
     * 获取导出的数据
     */
    public function get_content($filter,$offset,$limit)
    {
        //$title = $this->_title();//需要导出字段
         if(!$this->title){
            $this->title = $this->get_title();
        }
        $title = $this->title;
        // 获取要导出的数据库存在的字段
        $cols = $this->model->_columns();
        $titleKey = array_keys($title);
        $tmpCols = [];
        foreach ($titleKey as $tkey)
        {
            if(array_key_exists($tkey, $cols))
            {
                $tmpCols[] = $tkey;
            }
        }
        
        // 获取数据
        if($this->group_col){
            if( !$list = $this->model->getList(implode(',',$tmpCols), $filter,$offset*$limit,$limit,$this->group_col) ) return false;
        }else{
            if( !$list = $this->model->getList(implode(',',$tmpCols),$filter,$offset*$limit,$limit,$this->get_orderBy() )) return false;
        }

        $contents = array();
        foreach( (array)$list as $line => $row )
        {
            $tmpRow = array();
            $row = $this->_pre_content($row);
            if( $this->extends && method_exists($this->extends, 'get_content_row') )
            {
                $tmpRow[$line] = $this->extends->get_content_row($row);
            }else{
                $tmpRow[$line] = $row;
            }

            $group_val = isset($row[$this->group_col]) ? $row[$this->group_col] : 0;
            foreach($tmpRow as $key=>$tmpRowVal ){
                $contents[$group_val][] = $tmpRowVal;
            }
        }
        return $contents;
    }

    /**
     * 导出数据库中的数据格式进行转换
     */
    private function _pre_content($row){
        $cols = $this->model->_columns();
        $rowVal = array();
        foreach( (array)$row as $col => $val ){
            //如果改字段的类型为time 则转换
            if( in_array( $cols[$col]['type'],array('time','last_modify') ) && $val )
            {
                $val = date('Y-m-d H:i',$val);
            }

            //如果是longtext,则将导出的数据去掉换行符
            if ($cols[$col]['type'] == 'longtext')
            {
                if (strpos($val, "\n") !== false){
                    $val = str_replace("\n", " ", $val);
                }
            }

            //关联表字段显示对应的is_title字段
            if( strpos( (string)$cols[$col]['type'], 'table:')===0 && $col != $this->group_col)
            {
                #type=>table@b2c:member_lv  $subobj = array(0=>b2c,1=>member_lv)
                $subobj = explode( '@',substr($cols[$col]['type'],6) );
                if( !$subobj[1] ){//为指定app则表示关联表和当前表属相同app
                    $subobj[1] = $this->model->app->app_id;
                }
                $hasModel = app::get($subobj[1])->model( $subobj[0] );
                $textColumn = $hasModel->getList( $hasModel->schema['textColumn'], array( $hasModel->schema['idColumn'] => $val ) );
                $val = $textColumn[0][$hasModel->schema['textColumn']] ? $textColumn[0][$hasModel->schema['textColumn']] : $val;
            }

            #'type' => array(
            #     'pc' =>app::get('importexport')->_('标准平台'),
            #     'wap' => app::get('importexport')->_('手机触屏')
            # )
            # 如果type为数组则 $val='pc' 转换为$val = '标准平台' 否则 直接导出数据库存储的值
            $rowVal[$col] = is_array($cols[$col]['type']) ? $cols[$col]['type'][$val] : $val;
        }
        return $rowVal;
    }

    /*-----------------------以下为导入函数-----------------------*/

    //处理读取出的数据，格式化数据,将数据中的值对应到字段中
    public function pre_import_data($contents,$line) {

        $oneline = current($contents);

        $title = $this->get_title();

        if( ! is_array( current($title) ) ) $title = [$title];

        foreach( $title as $group_val=>$title_row )
        {
            //如果第一条记录为标题
            if( current($title_row) == current($oneline) )
            {
                //将导入的标题对应到定义的字段中
                $title_row_flip = array_flip($title_row);
                $this->current_title = array();
                foreach( $oneline as $key=>$label )
                {
                    $col = $title_row_flip[$label];
                    $this->current_title[] = $col;
                }
                //end
                array_shift($contents);
            }
        }

        if( !$this->current_title ){
            return false;
        }

        foreach((array)$contents[$line] as $key=>$value){
            $col = $this->current_title[$key];
            if( $col )
            {
                $contents[$line][$col] =$value;
            }
            unset($contents[$line][$key]);
        }
        return $contents;
    }//end function


    /**
     * 检查是否需要继续读取文件
     * 一般情况下每次读取一行则进行处理一行数据
     * 但是多规格商品的时候，需要一次读取多个行，才是一个完整的商品数据
     * 因此需要判断如果一个商品数据未完成则时候则需要继续读取数据
     */
    public function check_continue(&$contents,&$line) {

        if( empty($contents) ) return true;

        if($line == 0 ){
            $line++;
            return true; //如果是第一行则继续
        }

        $result = false; //默认不需要继续

        if( $this->extends && method_exists($this->extends, 'check_continue') )
        {
            //check_continue 需要将$contents 传引用出来
            $result = $this->extends->check_continue($contents,$line);
        }

        if( $result ) $line++;

        return $result;
    }

    /**
     * 将导入数据由数组转换为sdf格式
     * @param array $contents 导入的数据
     * @param string 如果错误返回错误信息
     */
    public function dataToSdf($contents,&$msg) {

        if( $this->extends && method_exists($this->extends, 'dataToSdf') )
        {
            $rs = $this->extends->dataToSdf($contents,$msg);
        }
        else
        {
            $rs = $contents;
        }

        return $rs;
    }
}
