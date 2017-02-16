<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2014 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class syslogistics_data_dlytmpl {

    public function __construct($app)
    {
        $this->app = $app;
        $this->objMdldlytmpl = app::get('syslogistics')->model('dlytmpl');
    }

    /**
     * @brief 存储店铺快递运费模板数据
     *
     * @param array $data 店铺快递运模板费数据
     *
     * @return bool
     */
    public function addDlyTmpl($data,$shopId)
    {
        $this->__check($data, $shopId);
        $saveData = $this->__preData($data,$shopId);
        if( !$this->objMdldlytmpl->insert($saveData) )
        {
            $msg = app::get('syslogistics')->_('保存失败');
            throw new \LogicException($msg);
        }
        return true;
    }

    /**
     * @brief 更新快递运费模板数据
     *
     * @param array $data
     *
     * @return bool
     */
    public function updateDlyTmpl($data,$shopId)
    {
        $this->__check($data, $shopId);
        $saveData = $this->__preData($data,$shopId);
        $filter['template_id'] = $saveData['template_id'];
        $filter['shop_id'] = $shopId;
        if( !$this->objMdldlytmpl->update($saveData,$filter) )
        {
            $msg = app::get('syslogistics')->_('保存失败');
            throw new \LogicException($msg);
        }
        return true;
    }

    /**
     * @brief 判断快递模板名称是否存在
     *
     * @param string $tmplname 模板名
     * @param string $shopId 店铺id
     *
     * @return bool|int  存在返回template_id | false 不存在
     */
    public function isExistsName($tmplname,$shopId)
    {
        $data = $this->objMdldlytmpl->getRow('template_id',array('name'=>$tmplname,'shop_id'=>$shopId));
        return $data ? $data['template_id'] : false;
    }

    private function __preData($data,$shopId)
    {
        if( $data['template_id'] )
        {
            $return['template_id'] = intval($data['template_id']);
        }
        $return['shop_id'] = $shopId;
        $return['name'] = trim($data['name']);
        $return['is_free'] = $data['is_free'] ? 1 : 0;
        $return['valuation'] = $data['valuation'];

        if( $data['protect'] )
        {
            $return['protect'] = $data['protect'] ;
            $return['protect_rate'] = $data['protect_rate'];
            $return['minprice'] = $data['minprice'];
        }
        else
        {
            $return['protect'] = 0;
            $return['protect_rate'] = 0;
            $return['minprice'] = 0;
        }

        $return['status'] = $data['status'] == 'off' ? 'off' : 'on';
        $return['fee_conf']  = $return['is_free'] ? '' : serialize($data['fee_conf']);
        $return['free_conf'] = $return['is_free']||!$data['free_conf'] ? '' : serialize($data['free_conf']);
        if( !$data['template_id'] )
        {
            $return['create_time'] = time();
        }
        $return['modifie_time'] = time();

        return $return;
    }

    private function __check($data, $shopId)
    {
        if( empty($data['name']) || mb_strlen(trim($data['name']),'utf8') > 20 )
        {
            $msg = app::get('syslogistics')->_('运费模板名称不能为空，且不可以超过20个字');
            throw new \LogicException($msg);
        }

        //修改的该模板ID是否存在
        $template_id = $this->isExistsName($data['name'], $shopId);
        if( $template_id && (!$data['template_id'] || $data['template_id'] != $template_id) )
        {
            $msg = app::get('syslogistics')->_('该运费模板名称已存在');
            throw new \LogicException($msg);
        }

        if( !in_array($data['valuation'], array(1,2,3,4)) )
        {
            $msg = app::get('syslogistics')->_('请选择正确的计价方式');
            throw new \LogicException($msg);
        }

        $areaArr = array();
        foreach( $data['fee_conf'] as $key=>$row )
        {
            if( !$row['area'] ) continue;
            $area = explode(',', $row['area']);
            foreach( $area as $areaId )
            {
                $areaName = area::getAreaNameById($areaId);
                if( !$areaName )
                {
                    $msg = app::get('syslogistics')->_("参数错误，选择的地区不存在");
                    throw new \LogicException($msg);
                }

                if( in_array($areaId, $areaArr) )
                {
                    $msg = app::get('syslogistics')->_("地区({$areaName})配置重复");
                    throw new \LogicException($msg);
                }
                else
                {
                    $areaArr[] = $areaId;
                }
            }
        }
        // 按金额计费要验证条件是否正确
        if($data['valuation']=='3')
        {
            foreach($data['fee_conf'] as $v)
            {
                if(!$v['rules'])throw new \LogicException(app::get('syslogistics')->_("没有填写运费规则"));
                $rule = $v['rules'];
                $first = key($rule); //数组第一个键名
                $ruleLength = count($rule)+$first; //数组的键的长度加上起始值
                if($rule[$first]['up'] != 0 )
                {
                    throw new \LogicException('第一行起始金额必须为0！');
                }
                if( count($rule)==1 && !is_numeric($rule[$first]['basefee']) && $data['is_free'] != '1')
                {
                    throw new \LogicException('运费必须大于等于0！');
                }
                for($i=$first; $i<$ruleLength; $i++)
                {
                    if( !is_numeric($rule[$i]['up']) )
                    {
                        throw new \LogicException('每一行的起始金额必须为大于等于0的数字！');
                    }
                    if( $i<($ruleLength-1) && !is_numeric($rule[$i]['down']) )
                    {
                        throw new \LogicException('除了最后一行，每一行的末尾金额必须为大于等于0的数字！');
                    }
                    if( !is_numeric($rule[$i]['basefee']) && $data['is_free'] != '1' )
                    {
                        throw new \LogicException('运费必须为大于等于0的数字！');
                    }
                    if( $i<($ruleLength-1) && $rule[$i]['up'] >= $rule[$i]['down'] )
                    {
                        throw new \LogicException('每行金额上下限必须左小右大！');
                    }
                    if( $i<($ruleLength-1) && $rule[$i]['up'] >= $rule[$i+1]['up'] )
                    {
                        throw new \LogicException('每一行的起始金额必须大于上一行的起始金额！');
                    }
                    if( $i<($ruleLength-1) && $rule[$i]['down'] != $rule[$i+1]['up'] )
                    {
                        throw new \LogicException('每一行的起始金额必须等于上一行的末尾金额！');
                    }
                    if( $i<($ruleLength-1) && $rule[$i]['basefee'] <= $rule[$i+1]['basefee'] )
                    {
                        throw new \LogicException('阶梯运费每行之间必须依次递减！');
                    }
                }
            }
        }

        $areaArr_freerule = array();
        foreach( $data['free_conf'] as $key=>$row )
        {
            if( !$row['area'] ) continue;
            $area = explode(',', $row['area']);
            foreach( $area as $areaId )
            {
                $areaName = area::getAreaNameById($areaId);
                if( !$areaName )
                {
                    $msg = app::get('syslogistics')->_("参数错误，包邮规则中选择的地区不存在");
                    throw new \LogicException($msg);
                }

                if( in_array($areaId, $areaArr_freerule) )
                {
                    $msg = app::get('syslogistics')->_("包邮规则中地区({$areaName})配置重复");
                    throw new \LogicException($msg);
                }
                else
                {
                    $areaArr_freerule[] = $areaId;
                }
            }
        }

        return true;
    }

    /**
     * @brief 获取运费模板数据
     *
     * @param string $fields
     * @param array $filter
     *
     * @return array
     */
    public function fetchDlyTmpl($fields='*', $filter,$pageNo='1',$pageSize='-1',$orderBy)
    {
        $total = $this->objMdldlytmpl->count($filter);
        if($total > 0)
        {
            $pageTotal = ceil($total/$pageSize);
            $page =  $pageNo ? $pageNo : 1;
            $limit = $pageSize ? $pageSize : 40;
            $currentPage = ($pageTotal < $page) ? $pageTotal : $page; //防止传入错误页面，返回最后一页信息
            $offset = ($currentPage-1) * $limit;

            $tmpl = $this->objMdldlytmpl->getList($fields, $filter,$offset,$limit,$orderBy);

            if( isset($tmpl[0]['fee_conf']))
            {
                foreach($tmpl as $key=>$val)
                {
                    $tmpl[$key]['fee_conf'] = unserialize($val['fee_conf']);
                }
            }
            $data['data'] = $tmpl;
            $data['total_found'] = $total;
            return $data;
        }
        else
        {
            return false;
        }

    }

    /**
     * @brief 删除对应ID的快递运费模板
     *
     * @param int|array  $templateId
     *
     * @return boole
     */
    public function remove($filter)
    {
        return $this->objMdldlytmpl->delete($filter);
    }

    public function getRow($row,$filter)
    {
        $objMdlDlyTmpl = app::get('syslogistics')->model('dlytmpl');
        $data = $objMdlDlyTmpl->getRow($row,$filter);
        if($data['fee_conf'])
        {
            $data['fee_conf'] = unserialize($data['fee_conf']);
        }
        if($data['free_conf'])
        {
            $data['free_conf'] = unserialize($data['free_conf']);
        }
        return $data;
    }

    /**
     * 根据运费模板ID 和传入的重量，地区参数计算运费
     *
     * @param int $templateId 运费模板ID
     * @param int $weight 重量
     * @param string $areaIds 地区ID
     *
     * @return int 返回运费值
     */
    public function countFee($templateId, $areaIds, $total_price, $total_quantity, $total_weight)
    {
        if( !area::checkArea($areaIds) ) return false;

        $filter = array(
            'template_id' => $templateId,
            'status' => 'on',
        );
        $template = $this->objMdldlytmpl->getrow("*", $filter);
        if( empty($template) )
        {
            $msg = app::get('syslogistics')->_("找不到运费模板，请联系商家！");
            throw new \LogicException($msg);
        }
        // 卖家免运费则直接返回运费为0；
        if($template['is_free'])
        {
            $fee = 0;
        }
        else
        {
            $paramsCartData['total_weight'] = $total_weight;
            $paramsCartData['total_price'] = $total_price;
            $paramsCartData['total_quantity'] = $total_quantity;
            // 计算运费
            $fee = $this->__count($template, $areaIds, $paramsCartData);
            // 判断是否符合包邮规则
            $isFree = $this->__isFree($template, $areaIds, $paramsCartData);
            if($isFree)
            {
                $fee = 0;
            }
        }

        return $fee;
    }

    /**
     * 根据传参计算出运费
     *
     * @param array $template 运费模板信息
     * @param string $areaIds 收货地区
     * @param int   $paramsCartData 原始计算参数。对应运费模板的各商品总重量，总价钱，总购买数量
     *
     * @return int
     */
    private function __count($template, $areaIds, $paramsCartData)
    {
        $fee_conf = unserialize($template['fee_conf']);
        $areaIdsArr = explode(',',$areaIds);
        foreach( $fee_conf as $data )
        {
            if( empty($data['area']) )
            {
                $defaultConf = $data;
            }
            else
            {
                // 只要传入的地区中和配置的指定地区有一个匹配了，则表示地区运费按照本次循环的指定地区进行计算(因为，运费模板指定地区是不可以重复的，只要省、市、区与指定地区配置中的省、市、区有一个匹配了则匹配成功)
                $areaSetting = explode(',',$data['area']);
                $intersect = array_intersect($areaSetting,$areaIdsArr);//求交集，只要有一个符合则表示匹配成功，跳出循环
                if( $intersect )
                {
                    $feeConf = $data;
                    break;
                }
            }
        }
        $config = $feeConf ? $feeConf : $defaultConf;

        $fee = 0;
        if($template['valuation']=='1')
        {
            if( $paramsCartData['total_weight'] <= $config['start_standard'] )
            {
                $fee = $config['start_fee'];
            }
            elseif( $config['add_standard'] > 0 )
            {
                $addWeight = ceil(bcsub($paramsCartData['total_weight'], $config['start_standard'], 2));
                $nums = bcdiv($addWeight, $config['add_standard'], 2);
                $fee = bcadd($config['start_fee'], bcmul($nums,$config['add_fee'],2) , 2);
            }

        }
        if($template['valuation']=='2')
        {
            if( $paramsCartData['total_quantity'] <= $config['start_standard'] )
            {
                $fee = $config['start_fee'];
            }
            elseif( $config['add_standard'] > 0 )
            {
                $addNum = bcsub($paramsCartData['total_quantity'], $config['start_standard']);
                $beishu = ceil( bcdiv($addNum, $config['add_standard'], 2) ) ;
                $fee = bcadd($config['start_fee'], bcmul($config['add_fee'], $beishu, 2) , 2);
            }
        }
        if($template['valuation']=='3')
        {
            foreach($config['rules'] as $v)
            {
                if( $paramsCartData['total_price']>=$v['up'] && $paramsCartData['total_price']<$v['down'])
                {
                    $fee = $v['basefee'];
                }
            }
            if(!$fee)
            {
                $maxrule = end($config['rules']);
                if($paramsCartData['total_price']>=$maxrule['up'])
                {
                    $fee = $maxrule['basefee'];
                }
            }

        }
        return $fee;
    }

    /**
     * 根据配置参数判断是否符合免运费规则
     *
     * @param int $valuation 计价方式
     * @param array $freeConfig 运费模板运费配置
     * @param int   $total_price 总价
     * @param int   $total_quantity 总件数
     * @param int   $total_weight 重量kg
     *
     * @return int
     */
    private function __isFree($template, $areaIds, $paramsCartData)
    {
        // 判断是否符合包邮规则
        $valuation = $template['valuation'];
        $areaIdsArr = explode(',', $areaIds);
        $total_weight = $paramsCartData['total_weight'];
        $total_price = $paramsCartData['total_price'];
        $total_quantity = $paramsCartData['total_quantity'];
        $free_conf = unserialize($template['free_conf']);
        foreach( $free_conf as $v )
        {
            if( empty($v['area']) )
            {
                $defaultFreeConf = $v;
            }
            else
            {
                // 只要传入的地区中和配置的指定地区有一个匹配了，则表示地区按照本次循环的指定地区进行判断(因为，包邮规则指定地区是不可以重复的，只要省、市、区与指定地区配置中的省、市、区有一个匹配了则匹配成功)
                $freeAreaSetting = explode(',',$v['area']);
                $intersect = array_intersect($freeAreaSetting,$areaIdsArr);//求交集，只要有一个符合则表示匹配成功，跳出循环
                if( $intersect )
                {
                    $freeConf = $v;
                    break;
                }
            }
        }
        $freeConfig = $freeConf ? $freeConf : $defaultFreeConf;

        if($valuation=='1')
        {
            // 重量
            if($freeConfig['freetype']=='1')
            {
                return ( $freeConfig['inweight'] && $total_weight <= $freeConfig['inweight']) ? true : false;
            }
            // 金额
            if($freeConfig['freetype']=='2')
            {
                return ( $freeConfig['upmoney'] && $total_price >= $freeConfig['upmoney']) ? true : false;
            }
            // 重量+金额
            if($freeConfig['freetype']=='3')
            {
                return (  $freeConfig['inweight'] && $freeConfig['upmoney'] && ($total_weight <= $freeConfig['inweight']) && ($total_price >= $freeConfig['upmoney']) ) ? true : false;
            }
        }
        elseif($valuation=='2')
        {
            // 件数
            if($freeConfig['freetype']=='1')
            {
                return ( $freeConfig['upquantity'] && ($total_quantity >= $freeConfig['upquantity']) ) ? true : false;
            }
            // 金额
            if($freeConfig['freetype']=='2')
            {
                return ( $freeConfig['upmoney'] && ($total_price >= $freeConfig['upmoney']) ) ? true : false;
            }
            // 件数+金额
            if($freeConfig['freetype']=='3')
            {
                return ( $freeConfig['upquantity'] && $freeConfig['upmoney'] && ($total_quantity >= $freeConfig['upquantity']) && ($total_price >= $freeConfig['upmoney']) ) ? true : false;
            }
        }
        else
        {
            return false;
        }
    }

}

