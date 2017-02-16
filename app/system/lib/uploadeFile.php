<?php

use Symfony\Component\HttpFoundation\File\UploadedFile;

class system_uploadeFile {

    public function __construct()
    {
        $this->objMdlFile = app::get('system')->model('file');
    }

    public function uploade( $fileObject )
    {
        $fileObject = $this->__preFileObject($fileObject);

        $filePath = $fileObject->getRealPath();
        $params['file_path'] = $filePath;
        $params['size'] = $fileObject->getClientSize();
        $params['file_name'] = $fileObject->getClientOriginalName();
        $params['last_modified'] = time();

        $storager = kernel::single('base_storager');
        $result = $storager->upload($fileObject);
        list($url,$ident,$storage) = explode('|', $result);
        $params['url'] = $url;
        $params['ident'] = $ident;
        $params['storage'] = $storage;

        $this->objMdlFile->insert($params);
        return $params;
    }

    public function getFileUrl( $ident )
    {
        $data = $this->objMdlFile->getRow('url',['ident'=>$ident]);
        return kernel::get_resource_host_url().$data['url'];
    }

    public function removeFile($ident)
    {
        try{
            $storager = kernel::single('base_storager');
            $storager->remove($ident);
        }
        catch( Exception $e)
        {
            throw new Exception('删除失败');
        }

        $this->objMdlFile->delete(['ident'=>$ident]);

        return true;
    }

    private function __preFileObject( $fileObject )
    {
        if( $fileObject instanceof UploadedFile ) return $fileObject;

        if( file_exists($fileObject) )
        {
            $fileParams = pathinfo($fileObject);

            $file = tempnam(TMP_DIR,'tmpFile');
            kernel::single('base_filesystem')->copy($fileObject, $file);
            $size = filesize($file);

            $fileName = substr(strrchr($fileObject,'/'),1);
            $fileObject = new UploadedFile($file, $fileName, $fileParams['extension'], $size, 0, true);
        }
        else
        {
            throw new \Exception(app::get('system')->_('存储的文件不存在'));
        }

        return $fileObject;
    }
}

