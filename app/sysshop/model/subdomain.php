<?php

class sysshop_mdl_subdomain extends dbeav_model {

    // 重写save方法
    public function save(&$data,$mustUpdate = null,$mustInsert = false){
        if(!preg_match('/^[\pL\pM\pN][\pL\pM\pN-]{2,30}[\pL\pM\pN]$/u', $data['subdomain']))
        {
            throw new \LogicException('二级域名长度必须为4-32个"字母、数字、-" 组成的字符串，且中杠不能在开头和结尾!');
        }
        return parent::save($data,$mustUpdate);
    }
}

