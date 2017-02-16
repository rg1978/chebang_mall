<?php
/**
 * 直接导出，不通过队列
 */
class importexport_export {

    public function fileDownload($filetype, $model, $filename, $filter,$orderBy=null)
    {
        $filetypeObj = kernel::single('importexport_type_'.$filetype);

        $filetypeObj->set_queue_header($filename.'.'.$filetype);

        if( method_exists($filetypeObj, 'setBom') )
        {
            $bom = $filetypeObj->setBom();
            echo $bom;
        }

        $this->export($filetype, $model, $filter,$orderBy);
    }

    private function export($filetype, $model, $filter,$orderBy)
    {
        //实例化导出数据类
        $dataObj = kernel::single('importexport_data_object',$model);
        $dataObj->set_orderBy($orderBy);
        //设置导出字段
        $object =  kernel::service('importexport.'.$model);
        if( is_object($object) ){
            if($fields = $object->shop_export_fields)
            {
                $dataObj->set_title($fields);
            }
        }

        //实例化导出文件类型类
        $filetypeObj = kernel::single('importexport_type_'.$filetype);

        //加入文件头部数据
        $fileHeader = $filetypeObj->fileHeader();
        if( $fileHeader )
        {
            echo $fileHeader;
        }

        //导出数据写到本地文件
        $offset = 0;
        while( $listFlag = $dataObj->fgetlist($data,$filter,$offset) )
        {
            $offset++;
            $rs = $filetypeObj->arrToExportType($data);
            echo $rs;
        }

        if( !$rs )
        {
            echo  '数据为空';
        }

        //加入文件尾部数据
        $fileFoot = $filetypeObj->fileFoot();
        if( $fileFoot )
        {
            echo $fileFoot;
        }

        exit;
    }
}
