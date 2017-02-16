<?php
/**
 * topapi
 *
 * -- user.vcode
 * -- 获取图片验证码
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_user_vcode implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取图片验证码';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'vcode_type' => ['type'=>'string', 'valid'=>'required', 'example'=>'topapi-signin', 'desc'=>'图片验证类型', 'msg'=>'图片验证类型必填'],
        ];
    }

    public function handle($params)
    {
        $vcode = kernel::single('base_vcode');
        $vcode->setPicSize(35, 120);
        $vcode->length(4);
        $vcode->verify_key($params['vcode_type']);
        $image = $vcode->base64Image();
        return $image;
    }
}

