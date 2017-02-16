<?php
/**
 *
 */
class syslogistics_data_area {

    /**
     * 返回地区ID和地区名
     */
    private function __preKeyValue($contents, $areaKvdata = array())
    {
        if( !$contents ) $contents = $this->__getAreaFileContents();
        foreach( $contents as $key=>$value )
        {
            if( !$value['disabled'] )
            {
                $this->areaIdPath[$value['parentId']][] = $value['id'];
            }

            $areaKvdata[$value['id']]['value'] = $value['value'];
            $areaKvdata[$value['id']]['parentId'] = $value['parentId'];
            $areaKvdata[$value['id']]['disabled'] = $value['disabled'];
            if( !empty($value['children']) )
            {
                $areaKvdata = $this->__preKeyValue($value['children'], $areaKvdata);
            }
        }
        return $areaKvdata;
    }

    private function __getAreaFileContents()
    {
        if( $this->areaFileContents ) return $this->areaFileContents;
        $this->areaFileContents = json_decode(redis::scene('syslogistics')->get('areaFileContents'), true);
        if ( !$this->areaFileContents )
        {
            $file = app::get('ectools')->res_dir.'/scripts/region.json';
            if( !is_file($file) )
            {
                $this->initFileContents();
                $this->resetFile();
            }
            else
            {
                $this->areaFileContents = json_decode(file_get_contents($file),true);
                $this->__setKvArea();
            }
        }

        return $this->areaFileContents;
    }

    public function initFileContents()
    {
        $staticsHostUrl = kernel::get_app_statics_host_url();
        $fileDir = $staticsHostUrl.'/ectools/statics/scripts/area.json';
        $this->areaFileContents = json_decode(file_get_contents($fileDir),true);
        $this->__setKvArea();
    }

    private function __setKvArea()
    {
        redis::scene('syslogistics')->set('areaFileContents', json_encode($this->areaFileContents));
        cache::store('misc')->forever('areaKvdata', array());
        cache::store('misc')->forever('areaIdPath', array());
        cache::store('misc')->forever('countAreaData', array());
        return true;
    }

    public function __construct()
    {
        if( !$this->__getCacheAreaData() )
        {
            $this->areaKvdata = $this->__preKeyValue();
            $this->__cacheAreaData();
        }
    }

    /**
     * 获取缓存中结构格式化后的数据
     */
    private function __getCacheAreaData()
    {
        $count = cache::store('misc')->get('countAreaData');
        if( $count )
        {
            $areaData = array();
            for( $i=0; $i < $count; $i++ )
            {
                $chunk = cache::store('misc')->get('areaKvdata'.$i);
                $areaData += $chunk;
            }

            $this->areaKvdata = $areaData;
        }
        else
        {
            $this->areaKvdata = cache::store('misc')->get('areaKvdata');
        }

        $this->areaIdPath = cache::store('misc')->get('areaIdPath');

        return  ($this->areaIdPath && $this->areaKvdata) ? true : false;
    }

    /**
     * 缓存结构格式化后的数据
     */
    private function __cacheAreaData()
    {
        if(config::get('cache.enabled', true))
        {
            //分块存储 默认一个缓存存储4500个地址
            $bulk = 4500;
            $total = count($this->areaKvdata);
            if( $total > $bulk )
            {
                $newAreaData = array_chunk($this->areaKvdata, $bulk, true);
                $i = 0;
                foreach( $newAreaData as $k=>$val )
                {
                    cache::store('misc')->forever('areaKvdata'.$i, $val);
                    $i++;
                }
                cache::store('misc')->forever('countAreaData', $i);
            }
            else
            {
                cache::store('misc')->forever('areaKvdata',$this->areaKvdata);
                cache::store('misc')->forever('countAreaData', 0);
            }

            cache::store('misc')->forever('areaIdPath',$this->areaIdPath);
        }

        return true;
    }

    public function resetFile()
    {
        $this->__getAreaFileContents();
        $this->__editAreaData($this->areaFileContents,'reset');
        sort($this->areaFileContents);
        $file = app::get('ectools')->res_dir.'/scripts/region.json';
        if( !file_put_contents($file, json_encode($this->areaFileContents)) )
        {
            throw new LogicException('文件写入失败：'.$file.'请检查是否有写权限');
        }
    }

    public function editArea($type, $areaId, $areaName)
    {
        $this->__getAreaFileContents();

        if( $type == 'add' && $areaId || $type != 'add' )
        {
            $this->__editAreaData($this->areaFileContents, $type, $areaId, $areaName);
        }
        else
        {
            $this->areaFileContents[] = ['id'=>$this->__genAreaId(),'value'=>$areaName,'parentId'=>$areaId];
        }

        if( $type != 'reset' )
        {
            $this->__setKvArea();
        }
    }

    //如果并发的时候可能会有问题，导致ID一致，但是在添加地区的时候，正常情况下添加地区不会并发
    private function __genAreaId($parentId)
    {
        if (!($areaIdIndex = redis::scene('syslogistics')->get('areaIdIndex')))
        {
            $areaIdIndex = $id = 900000;
        }
        else
        {
            if( is_array($areaIdIndex) )
            {
                $index =  max($areaIdIndex);
                unset($areaIdIndex);
                $areaIdIndex = $index;
            }

            $areaIdIndex = $id = $areaIdIndex + 1;
        }

        redis::scene('syslogistics')->set('areaIdIndex', $areaIdIndex);
        return $id.rand(999);
    }

    private function __editAreaData(&$areaFileContents, $type='reset', $areaId, $areaName)
    {
        foreach( $areaFileContents as $key=>$value )
        {
            switch($type)
            {
                case 'remove':
                    if( $value['id'] == $areaId )
                    {
                        $areaFileContents[$key]['disabled'] = true;
                    }
                    break;
                case 'update':
                    if( $value['id'] == $areaId )
                    {
                        $areaFileContents[$key]['value'] = $areaName;
                        break;
                    }
                case 'add':
                    if( $value['id'] == $areaId )
                    {
                        $id = $this->__genAreaId();
                        $areaFileContents[$key]['children'][] = ['value'=>$areaName,'id'=>$id,'parentId'=>$areaId];
                        break;
                    }
                default :
                    if(isset($value['disabled']) && $value['disabled'] == 1)
                    {
                        unset($areaFileContents[$key]);
                        unset($value);
                    }
            }

            if(isset($value['children']) && is_array($value['children']))
            {
                $this->__editAreaData($areaFileContents[$key]['children'], $type, $areaId, $areaName);
            }
        }

        return true;
    }

    /**
     * 返回所有地区内容
     *
     *array(
     *  18 =>
     *    array (
     *      'id' => '430000',
     *      'value' => '湖南省',
     *      'parentId' => '1',
     *      'children' =>
     *      array (
     *        0 =>
     *        array (
     *          'id' => '430100',
     *          'value' => '长沙市',
     *          'parentId' => '430000',
     *          'children' =>
     *          array (
     *            0 =>
     *            array (
     *              'id' => '430102',
     *              'value' => '芙蓉区',
     *              'parentId' => '430100',
     *            ),
     *          ),
     *        ),
     *      ),
     *    ),
     *),
     */
    public function getMap()
    {
        $this->__getAreaFileContents();
        return $this->areaFileContents;
    }

    /**
     * 获取地区的子节点
     *
     * @param Int $areaId 地区ID
     * @return 如果指定地区ID，则返回指定地区ID的所有子节点
     *         如果没有指定,则返回所有
     *
     * array (
     *   110000 =>
     *   array (
     *     0 => '110100',
     *   ),
     *   110100 =>
     *   array (
     *     0 => '110101',
     *     1 => '110102',
     *     2 => '110103',
     *     3 => '110104',
     *     4 => '110105',
     *   ),
     */
    public function getAreaIdPath($areaId)
    {
        if( $areaId )
        {
            return $this->areaIdPath[$areaId] ? $this->areaIdPath[$areaId] : false;
        }

        return $this->areaIdPath;
    }

    /**
     * 获取地区ID对应地区值
     *
     * @param Int $areaId 地区ID
     * @return 如果指定地区ID，则返回指定地区ID的名称和父节点ID
     *         如果没有指定,则返回所有
     * array (
     *  110100 =>
     *    array (
     *        'value' => '北京市',
     *        'parentId' => '110000',
     *    ),
     *  110101 =>
     *     array (
     *       'value' => '东城区',
     *       'parentId' => '110100',
     *    ),
     * )
     */
    public function areaKvdata($areaId)
    {
        if( $areaId )
        {
            return $this->areaKvdata[$areaId] ? $this->areaKvdata[$areaId] : false;
        }

        return $this->areaKvdata;
    }

    public function getAreaDataLv1()
    {
        $this->__getAreaFileContents();
        foreach( $this->areaFileContents as $row )
        {
            if( $row['disabled'] ) continue;
            $data[] = $row['id'];
        }
        return $data;
    }

    /**
     * 根据地区ID返回地区名称
     *
     * @param $areaId 地区ID
     */
    public function getAreaNameById($areaId)
    {
        return $this->areaKvdata[$areaId]['value'];
    }

    /**
     * 三级联动选择，根据选择的地区ID返回地区名称
     *
     * @param $areaId 地区ID  以逗号隔开的地区ID
     * @param $type   地区分隔符
     *
     * return array | bool
     */
    public function getSelectArea($areaIds, $type='/')
    {
        foreach( explode(',',$areaIds) as $id )
        {
            if( $area = $this->getAreaNameById($id) )
            {
                $name[] = $area;
            }
        }

        $areaName = implode($type,$name);
        return $areaName;
    }

    /**
     * 检查联动的ID是否是合法的
     *
     * @param $areaId 地区ID  以逗号隔开的地区ID
     *
     * return bool
     */
    public function checkArea($areaIds)
    {
        $ids = explode(',',$areaIds);
        foreach( $ids as $id )
        {
            if( $this->areaKvdata[$id]['disabled'] ) return false;

            if( $parentId )
            {
                if( !in_array($id,$this->areaIdPath[$parentId]) ) return false;
            }

            $parentId = $id;
        }

        if( $this->areaIdPath[end($ids)] ) return false;

        return true;
    }
}

