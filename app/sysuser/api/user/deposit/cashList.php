<?php
class sysuser_api_user_deposit_cashList
{
    public $apiDescription = "申请提现";

    public function getParams()
    {
        $return['params'] = array(
            'user_id' => ['type'=>'int',    'valid'=>'numeric|required', 'title'=>'用户id',     'desc'=>'用户id'],
            'fields'  => ['type'=>'string', 'valid'=>'',                 'title'=>'字段',     'desc'=>'需要的数据字段'],
            'page'    => ['type'=>'int',    'valid'=>'numeric|min:0',    'title'=>'页码',       'desc'=>'日志列表的页数','default'=>'1'],
            'row_num' => ['type'=>'int',    'valid'=>'numeric|min:0',    'title'=>'容量',       'desc'=>'日志列表每页的行数','default'=>'10'],
        );
        return $return;
    }

    /**
     * @return array list 提现单列表
     * @return int count 数据条数
     *
     */
    public function get($params)
    {
        $userId = $params['user_id'];
        $fields = $params['fields'] ? $params['fields'] : '*';
        $page = $params['page'] ? $params['page'] : 1;
        $rowNum = $params['row_num'] ? $params['row_num'] : 10;

        list($list, $count) = kernel::single('sysuser_data_deposit_cash')->getList($fields, $userId, $page, $rowNum);


        return ['list'=>$list, 'count'=>$count];
    }

}
