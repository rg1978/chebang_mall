<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class sysshop_api_account_sellerAuthUpdate {
    
    public $apiDescription = "修改认证状态";
    
    public function getParams()
    {
        $return['params'] = array(
                'seller_id' => ['type'=>'int','valid'=>'required','description'=>'角色id','default'=>'','example'=>'1'],
                'shop_id' => ['type'=>'int','valid'=>'required','description'=>'店铺id','default'=>'','example'=>'1'],
                'auth_type' => ['type'=>'string','valid'=>'','description'=>'角色认证状态','default'=>'','example'=>'1'],
                'mobile' => ['type'=>'string','valid'=>'','description'=>'手机号','default'=>'','example'=>'13918765432'],
                'email' => ['type'=>'string','valid'=>'','description'=>'邮箱','default'=>'','example'=>'example@shopex.cn'],
                
        );
    
        return $return;
    }
    
    public function update($params)
    {
        $objMdlSeller = app::get('sysshop')->model('seller');
        $filter = [
                'seller_id'=>$params['seller_id'],
                'shop_id'=>$params['shop_id']
        ];
        $data = [
                'auth_type'=>$params['auth_type'],
                'modified_time'=>time(),
        ];
        
        if($params['mobile'])
        {
            $data['mobile'] = $params['mobile'];
        }
        
        if($params['email'])
        {
            $data['email'] = $params['email'];
        }
        
        $objMdlSeller->update($data, $filter);
        
        return true;
    }
}