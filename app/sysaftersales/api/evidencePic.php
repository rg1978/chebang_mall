<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 根据售后单下载售后凭
 */
class sysaftersales_api_evidencePic {

    /**
     *      * 接口作用说明
     *           */
    public $apiDescription = '根据售后单下载售后凭证';

    /**
     *      * 根据售后单下载售后凭
     *           */
    public function getParams()
    {
        $return['params'] = array(
            'aftersales_bn' => ['type'=>'int','valid'=>'required|numeric', 'description'=>'申请售后编号'],
        );

        return $return;
    }

    /**
     *      * 根据售后单下载售后凭
     *           */
    public function download($params)
    {
        $aftersalesEvidencePicDir = DATA_DIR.'/afsEvidencePic';

        is_dir($aftersalesEvidencePicDir) or mkdir($aftersalesEvidencePicDir, 0755, true);

        $objMdlAftersales = app::get('sysaftersales')->model('aftersales');
        $aftersalesInfo = $objMdlAftersales->getRow('evidence_pic', ['aftersales_bn'=>$params['aftersales_bn']]);
        if( empty($aftersalesInfo['evidence_pic']) ) return true;

        $dirname = $params['aftersales_bn'];
        is_dir($aftersalesEvidencePicDir .'/' . $dirname) or mkdir($aftersalesEvidencePicDir .'/' . $dirname, 0755, true);

        $tar = kernel::single('base_tar');
        $tar->addDirectory($aftersalesEvidencePicDir .'/' . $dirname, $dirname);

        foreach( explode(',',$aftersalesInfo['evidence_pic']) as $url)
        {
            $url = base_storager::modifier($url);
            ob_start();

            readfile($url);
            $img=ob_get_contents();

            ob_end_clean();

            $file = $aftersalesEvidencePicDir .'/' . $dirname .'/'. pathinfo($url)['basename'];
            file_put_contents($file,$img);

            $file_information = stat($file);
            $tar->addFile($file,$img,$file_information, $dirname .'/'. pathinfo($url)['basename']);
        }

        $tar->filename = $aftersalesEvidencePicDir .'/' .$dirname.'.zip';
        $tar->saveTar();

        $filename = $dirname.'.zip';
        $filepath = $aftersalesEvidencePicDir;

        header("Content-Type: application/force-download");
        header("Content-Transfer-Encoding: binary");
        header('Content-Type: application/zip');
        header("Connection: close");
        header("Content-Disposition: attachment; filename=\"".$filename."\"");
        header("Content-Length: ".filesize($aftersalesEvidencePicDir .'/' .$dirname.'.zip'));
        readfile($aftersalesEvidencePicDir .'/' .$dirname.'.zip');
        exit;
    }
}

