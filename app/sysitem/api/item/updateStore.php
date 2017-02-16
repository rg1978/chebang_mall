<?php
class sysitem_api_item_updateStore {

    public $apiDescription = "回写库存";

    public function getParams()
    {
        $return['params'] = array(
            'list_quantity' => ['type'=>'string','valid'=>'required','description'=>'库存列表的json格式[{"bn"=>,"quantity"=>}](最多50条),bn为sku_bn，不是商品bn','example'=>'[{"bn":"S558FBDE4EE0E901","quantity":100},{"bn":"S558FBDE4EE0E902","quantity":100}]','default'=>''],
            'shop_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'18','description'=>'店铺ID'],
        );
        return $return;
    }

    public function updateStore($params)
    {
        $shopId = $params['shop_id'];
        $listQuantity = json_decode($params['list_quantity'], 1);
        $this->__checkListQuantity($listQuantity);

        foreach($listQuantity as $quantity)
        {
            try
            {
                $this->__checkQuantity($quantity);
                $skuBn = $quantity['bn'];
                $store = $quantity['quantity'];
                kernel::single('sysitem_item_store')->updateStoreByBn($skuBn, $shopId, $store);
            }
            catch(Exception $e)
            {
                $errordata[] = $quantity['bn'];
            }
        }

        if( $errordata )
        {
            $errordata = json_encode($errordata);
            throw new LogicException($errordata);
        }

        return true;
    }

    private function __checkQuantity($quantity)
    {
        $quantityValidate = [
            'bn' => 'required|max:30',
            'quantity' => 'required|numeric',
            ];
        $validator = validator::make($quantity, $quantityValidate);
        if( $validator->fails() )
        {
            $errors = json_decode( $validator->messages(), 1 );
            foreach( $errors as $error )
            {
                throw new LogicException( $error[0] );
            }
        }
    }

    private function __checkListQuantity($listQuantity)
    {
        $countListQuantity = count($listQuantity);
        if($countListQuantity == 0)
        {
            throw new LogicException('list_quantity长度不能为0');
        }
        elseif($countListQuantity > 50 )
        {
            throw new LogicException('批量更新库存的货品数量不能多于50个');
        }
    }
}
