<?php

/**
 * @brief 商家入驻申请及查看页面
 */
class topshop_ctl_enterapply extends topshop_controller{

    public $nomenu = true;
    const NO_MAINLAND = 2;
    /**
     * @brief 入驻申请 function
     *
     * @return html
     */
    public function apply()
    {
        $result = app::get('topshop')->rpcCall('shop.get.enterapply',array('seller_id'=>$this->sellerId,'fields'=>'seller_id'));
        if($result)
        {
            return redirect::action('topshop_ctl_enterapply@checkPlan');
        }
        else
        {
            $pagedata = $this->__pubdata();
            $this->set_tmpl('commonpage');
            if($_POST['license'] != "true")
            {
                //获取配置好的入驻协议
                $pagedata['content'] = app::get('sysshop')->getConf('setprotocol');
                $this->contentHeaderTitle = '您还未提交入驻申请';
                return $this->page('topshop/enterapply/premise.html', $pagedata);
            }
            else
            {
                $this->contentHeaderTitle = '填写入驻申请资料';
                return $this->page('topshop/enterapply/apply.html', $pagedata);
            }
        }
    }

    /**
     * @brief 入驻申请查看
     *
     * @return thml
     */
    public function checkPlan()
    {
        $datalist = app::get('topshop')->rpcCall('shop.get.enterapply',array('seller_id'=>$this->sellerId,'fields'=>'enterlog,status'));
        $pagedata['logdata'] = unserialize($datalist['enterlog']);
        $pagedata['data'] = $datalist;
        $this->set_tmpl('commonpage');
        $this->contentHeaderTitle = '查看入驻申请进度';
        return $this->page('topshop/enterapply/checkPlan.html', $pagedata);
    }

    /**
     * @brief 入驻申请更改
     *
     * @return thml
     */
    public function updateApply()
    {
        $pagedata = $this->__pubdata();
        $datalist = app::get('topshop')->rpcCall('shop.get.enterapply',array('seller_id'=>$this->sellerId,'fields'=>'*'));
        $pagedata['shop'] = unserialize($datalist['shop']);
        $pagedata['shop_info'] = unserialize($datalist['shop_info']);

        unset($datalist['shop_info'],$datalist['shop']);
        $pagedata['applydata'] = $datalist;

        $this->set_tmpl('commonpage');
        $this->contentHeaderTitle = '编辑入驻申请资料';
        return $this->page('topshop/enterapply/apply.html', $pagedata);
    }

    /**
     * @brief 保存入驻申请信息
     *
     * @return
     */
    public function saveApply()
    {
        try{
            $url = url::route('topshop.home');
            $post = input::get();
            $this->__checkpost($post);
            $result = app::get('topshop')->rpcCall('shop.create.enterapply',$post);
            $msg = app::get('topshop')->_('申请入驻成功');
            return $this->splash('success',$url,$msg,true);
        } catch (\LogicException $e) {
            return $this->splash('error',null,$e->getMessage(),true);
        }
    }

    /**
     * @brief ajax请求类目下的品牌
     *
     * @return
     */

     public function ajaxCatBrand()
     {
         $params['cat_id'] = input::get('cat_id');
         $brands = app::get('topshop')->rpcCall('category.get.cat.rel.brand',$params);
         if($brands)
         {
             sort($brands);
             return response::json($brands);exit;
         }
         return response::json(array());exit;
     }

    /**
     * @brief  检测输入项
     *
     * @param $postdata
     *
     * @return array
     */
    private function __checkpost(&$postdata)
    {

        if(!$postdata['seller_id']) $postdata['seller_id'] = $this->sellerId;
        if(!$postdata['status']) $postdata['status'] = 'active';
        $postdata['enterlog'] = array(array(
            'plan'=>$postdata['enterapply_id'] ? '重新提交入驻申请' : '提交入驻申请',
            'times'=>time(),
            'hint'=>$postdata['enterapply_id'] ? '您修改了入驻申请并重新提交' : '您提交了入驻申请',
        ));

        $postdata['shop_info']['establish_date'] = strtotime($postdata['shop_info']['establish_date']);
        $postdata['shop_info']['license_indate'] = strtotime($postdata['shop_info']['license_indate']);

        if($postdata['shop_info']['license_indate'] > 9999999999)
        {
            $msg = app::get('topshop')->_("营业期限不能大于2286-01-01");
            throw new \LogicException($msg);
        }

        $postdata['add_time'] = time();

        if(!$postdata['shop_type'])
        {
            $msg = app::get('topshop')->_("店铺类型不能为空");
            throw new \LogicException($msg);
        }

        if(!array_filter($postdata['shop']['shop_cat']))
        {
            $msg = app::get('topshop')->_("类目不能为空");
            throw new \LogicException($msg);
        }

        if($postdata['shop_name'])
        {
            $params = array(
                'shop_name'=>$postdata['shop_name'],
                'enterapply_id' => $postdata['enterapply_id'],
            );
            try{
                app::get('topshop')->rpcCall('shop.name.check',$params);
            }
            catch(LogicException $e)
            {
                throw new \LogicException($e->getMessage());
            }
        }
        if(mb_strlen(trim($postdata['shop']['shop_descript']),'utf8') > 200)
        {
            $msg = app::get('topshop')->_("店铺描述不能高于200字符");
            throw new \LogicException($msg);
        }

        if(!in_array($postdata['shop_type'],['cat','store']) && !$postdata['new_brand'] && !$postdata['shop']['shop_brand'])
        {
            $msg = app::get('topshop')->_("请选择店铺品牌 或 新增店品牌");
            throw new \LogicException($msg);
        }

        //护照判断
        if($postdata['shop_info']['is_mainland'] == self::NO_MAINLAND)
        {
            if(!$postdata['shop_info']['passport_number'])
            {
                $msg = app::get('topshop')->_("请填写护照号码");
                throw new \LogicException($msg);
            }
        }

        if($postdata['new_brand'])
        {
            $postdata['shop']['postdata_brand'] = "";
        }

        app::get('topshop')->rpcCall('shop.check.brand.sign',$postdata);
        if($postdata['shop_type'] == "cat")
        {
            $postdata['shop']['postdata_brand'] = "";
            $postdata['new_brand'] = "";
        }

        // 判断资料上传
        $this->__checkPostImg($postdata['shop_info'],$postdata['shop_type']);
    }

    /**
     * @brief 申请编辑时的公有数据
     *
     * @return array
     */
    private function __pubdata(){
        $lv1Catlists = app::get('topshop')->rpcCall('category.cat.get.info',array('fields'=>'cat_id,cat_name','parent_id'=>0,'level'=>1));
        foreach($lv1Catlists as $val)
        {
            $catlists[$val['cat_id']] = $val['cat_name'];
        }
        $pagedata['catlist'] = $catlists;

        $shopTypelist = app::get('topshop')->rpcCall('shop.type.get');
        unset($shopTypelist['self']);//去掉自营店铺类型
        $pagedata['shoptypelist'] = $shopTypelist;

        return $pagedata;
    }

    // 验证商家入住申请时必上传的图片，只做是否上传判断
    private function __checkPostImg($shopInfo,$shopType)
    {
        if(!$shopInfo)
        {
            return false;
        }
        // 设置必上传的图片数组
        $imgInfo = [
                'corporate_identity_img_z' => app::get('topshop')->_("请上传法人身份证正面复印件"),
                'corporate_identity_img_f' => app::get('topshop')->_("请上传法人身份证反面复印件"),
                'license_img' => app::get('topshop')->_("请上传营业执照副本复印件"),
                'tissue_code_img' => app::get('topshop')->_("请上传组织机构代码证复印件"),
                'tax_code_img' => app::get('topshop')->_("请上传税务登记证复印件"),
                'brand_warranty' => app::get('topshop')->_("请上传品牌授权书电子版"),
                'shopuser_identity_img_z' => app::get('topshop')->_("请上传店主身份证电子版正面"),
                'shopuser_identity_img_f' => app::get('topshop')->_("请上传店主身份证电子版反面")
        ];

        if($shopType == "cat" || $shopType == "store")
        {
            unset($imgInfo['brand_warranty']);
        }

        foreach ($shopInfo as $key => $val)
        {
            if(array_key_exists($key, $imgInfo) && !$val)
            {
                throw new \LogicException($imgInfo[$key]);
            }
        }

        return true;
    }
}

