<?php
class im extends PHPUnit_Framework_TestCase
{
    public function setUp(){
        //$this->model = app::get('base')->model('members');
    }

    public function testInstance()
    {
        $shop_id = 3;
        $type = "itemInfo";
        $user_id = 1;
        $params['note'] = '订单号:111111';
        $params['loc'] = 'http://elrond.onex.software';
        $params['content'] = '客服';
        $content = "客服";
        $html = kernel::single('sysim_im')->getRow($shop_id, $type, $content, $user_id, $params);

        var_dump($html);
    }

    public function testApiGetRow()
    {

        $html = app::get('sysim')->rpcCall('im.get.row', ['shop_id'=>3, 'type'=>'index', 'content'=>'联系客服', 'params'=>['loc'=>'http://elrond.onex.software', 'note'=>'首页联系客服']]);

        var_dump($html);
    }


}


?>
