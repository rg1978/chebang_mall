<?php

class sysrate_ctl_traderate extends desktop_controller {

    public $workground = 'sysuser.wrokground.user';

    public function index()
    {
        return $this->finder(
            'sysrate_mdl_traderate',
            array(
                'title'=>app::get('sysrate')->_('评论列表'),
                'use_buildin_delete' => true,
            )
        );
    }

    public function _views(){
        $subMenu = array(
            0=>array(
                'label'=>app::get('sysrate')->_('全部'),
                'optional'=>false,
            ),
            1=>array(
                'label'=>app::get('sysrate')->_('好评'),
                'optional'=>false,
                'filter'=>array(
                    'result'=>'good',
                ),

            ),
            2=>array(
                'label'=>app::get('sysrate')->_('中评'),
                'optional'=>false,
                'filter'=>array(
                    'result'=>'neutral',
                ),
            ),
            3=>array(
                'label'=>app::get('sysrate')->_('差评'),
                'optional'=>false,
                'filter'=>array(
                    'result'=>'bad',
                ),
            ),
        );
        return $subMenu;
    }

    public function showRateView($rateId)
    {
        $params = ['rate_id'=>$rateId,'fields'=>'*,append'];
        $pagedata['rateData'] = app::get('sysrate')->rpcCall('rate.get', $params);
        if( $pagedata['rateData']['rate_pic'] )
        {
            $pagedata['rateData']['rate_pic'] = explode(',',$pagedata['rateData']['rate_pic']);
        }

        if( $pagedata['rateData']['append']['append_rate_pic'] )
        {
            $pagedata['rateData']['append']['append_rate_pic'] = explode(',',$pagedata['rateData']['append']['append_rate_pic']);
        }

        return $this->page('sysrate/traderate/detail.html', $pagedata);
    }

    public function doDelAppendRate()
    {
        $this->begin("javascript:finderGroup["."'".$_GET["finder_id"]."'"."].refresh()");
        $meg = "删除追加评论成功";
        try{
            $rateId = input::get('rate_id');
            $result = app::get('sysrate')->model('append')->delete(['rate_id'=>$rateId]);
            $this->adminlog("删除追加评论,[{$rateId}]", 1);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
        }
        $this->end($result,$msg);
    }
}

