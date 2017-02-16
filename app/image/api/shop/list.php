<?php
/**
 * ShopEx licence
 * - image.shop.list
 * - 获取当前店铺的图片列表
 *
 * @copyright Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license   http://ecos.shopex.cn/ ShopEx License
 * @link      http://www.shopex.cn/
 * @author    shopex 2016-05-17
 */
class image_api_shop_list {

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取当前店铺的图片列表';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        $return['params'] = array(
            'shop_id'       => ['type'=>'int',    'valid'=>'required|numeric', 'title'=>'店铺ID',           'example'=>'24',    'desc'=>'店铺ID'],
            'img_type'      => ['type'=>'string', 'valid'=>'required',         'title'=>'店铺图片类型',     'example'=>'item',  'desc'=>'店铺图片类型，产品图片item;店铺图片shop'],
            'image_cat_id'  => ['type'=>'int',    'valid'=>'numeric',          'title'=>'图片类型子分类ID', 'example'=>'11',    'desc'=>'图片类型子分类ID'],
            'image_name'    => ['type'=>'string', 'valid'=>'',                 'title'=>'图片名称',         'example'=>'',      'desc'=>'图片名称',],
			//分页参数
            'page_no'       => ['type'=>'int',    'valid'=>'numeric', 'title'=>'当前页码', 'example'=>'2',   'desc'=>'分页当前页数,默认为1'],
            'page_size'     => ['type'=>'int',    'valid'=>'numeric', 'title'=>'分页数',   'example'=>'20',  'desc'=>'每页数据条数,默认10条'],
            'orderBy'       => ['type'=>'string', 'valid'=>'',        'title'=>'排序',     'example'=>'',    'desc'=>'排序，默认last_modified desc',],
            'fields'        => ['type'=>'field_list', 'valid'=>'',  'title'=>'查询字段', 'example'=>'*', 'desc'=>'需要查询返回的字段,默认返回所有'],
        );
        return $return;
    }

    /*
     * 分页处理
     *
     *@param $total int 根据条件查询的总数
     *@param $pageNo int 当前分页的页数
     *@param $pageSIze int 当前分页的页码
     */
     private function __page($total, $pageNo, $pageSize)
	{
		$pageTotal = ceil($total/$pageSize);
		$pageNo =  $pageNo ? $pageNo : 1;
		$data['limit'] = $pageSize ? $pageSize : 10;
		$currentPage = $pageTotal < $pageNo ? $pageTotal : $pageNo;
		$data['offset'] = ($currentPage-1) * $data['limit'];

		return $data;
	}

    private function __filter($params)
    {
        $filter['disabled'] = 0;
        $filter['target_id'] = $params['shop_id'];
        $filter['target_type'] = 'shop';

        if( $params['img_type'] != 'all' )
        {
            $filter['img_type'] = $params['img_type'];
        }

        if( isset($params['image_cat_id']) &&  $params['image_cat_id'] !== "" )
        {
            $filter['image_cat_id'] = $params['image_cat_id'];
        }

        //根据图片名称搜索图片
        if( isset($params['image_name']) && $params['image_name'] !== false && !is_null($params['image_name']) )
        {
            $filter['image_name|has'] = $params['image_name'];
        }

        return $filter;
    }

    private function __preFields($params)
    {
        if( $params['fields'] && $params['fields'] != '*' )
        {
            $fields = explode(',',$params['fields']);
            if( in_array('size', $fields) )
            {
                $this->isReturnSize = true;
            }

            foreach( $fields as $key=>$val  )
            {
                if( $val == 'format_size' )
                {
                    $this->isReturnFormatSize = true;
                    if( !$this->isReturnSize )
                    {
                        $fields[$key] = 'size';
                    }
                    else
                    {
                        unset($fields[$key]);
                    }
                }
            }

            $fields = implode(',',$fields);
        }
        else
        {
            $fields = "id,storage,image_name,img_type,url,ident,width,height,size,image_cat_id,last_modified";
        }

        return $fields;
    }

    /**
     *
     * @desc 获取当前店铺的图片列表
     *
     * @return int id 图片ID，对应其他APIimage_id参数
     * @return string storage 存储引擎，filesystem本地存储，qiniu七牛存储
     * @return string img_type 图片类型，item产品图片，shop店铺图片等
     * @return string url 图片地址
     * @return string ident 唯一标识
     * @return int width 上传图片原始宽度
     * @return int height 上传图片原始高度
     * @return int size 上传图片大小
     * @return string format_size 上传图片大小格式化
     * @return int image_cat_id 图片类型子分类ID
     * @return string last_modified 图片最后修改时间
     */
    public function get($params)
    {
        //条件处理
        $filter = $this->__filter($params);

        //返回字段处理
        $fields = $this->__preFields($params);

		$total = app::get('image')->model('images')->count($filter);
        $result['total'] = $total;
        if( !$total ) $result['list'] = [];

        $page = $this->__page($total, $params['page_no'], $params['page_size']);
        $orderBy = $params['orderBy'] ? $params['orderBy'] : 'last_modified desc';

        $result['list'] = app::get('image')->model('images')->getList($fields, $filter, $page['offset'], $page['limit'], $orderBy);

        if( $params['fields'] == '*' || $this->isReturnFormatSize )
        foreach( $result['list']  as $k=>$v )
        {
            $result['list'][$k]['format_size'] = format_filesize($v['size']);
            if( !$this->isReturnSize )
            {
                unset($result['list'][$k]['size']);
            }
        }
		return $result;
	}
}
