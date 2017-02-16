<?php
/**
 * 商品运费修改
 * item.update.dlytmpl
 */
class sysitem_api_item_updateDlytmpl{

    public $apiDescription = "商品运费模板修改";

    public function getParams($params)
    {
        $return['params'] = array(
            'item_id' => ['type'=>'int','valid'=>'required|int','description'=>'商品id，多个id用，隔开','example'=>'1','default'=>''],
            'shop_id' => ['type'=>'int','valid'=>'required|int','description'=>'店铺id','example'=>'1','default'=>''],
            'dlytmpl_id' => ['type'=>'int','valid'=>'required','description'=>'商品运费模板','example'=>'1','default'=>''],
        );
        return $return;
    }

    public function updateDlytmpl($params)
    {
        $itemId = $params['item_id'];
        $dlytmpl_id = $params['dlytmpl_id'];
        try
        {
              $ojbMdlItem = app::get('sysitem')->model('item');
              $result = $ojbMdlItem->update(array('dlytmpl_id'=>$dlytmpl_id),array('item_id'=>$itemId,'shop_id'=>$params['shop_id']));
              if(!$result)
              {
                  throw new Exception('运费模板更新失败');
              }
        }
        catch(Exception $e)
        {
            throw new Exception($e->getMessage());
        }
        return true;
    }

}
