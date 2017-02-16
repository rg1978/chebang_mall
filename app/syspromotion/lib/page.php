<?php

/**
 * page.php 
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class syspromotion_page {

    public function saveData($data)
    {

        $objMdl = app::get('syspromotion')->model('page');
        $data = $this->__checkData($data);
        $db = app::get('syspromotion')->database();
        $db->beginTransaction();
        try
        {
            $objMdl->save($data);
            $db->commit();
        } catch ( LogicException $e )
        {
            $db->rollback();
            throw $e;
        }
        
        return true;
    }
    
    public function getInfo($pageId, $row='*')
    {
        if(!$pageId)
        {
            return false;
        }
        
        $objMdl = app::get('syspromotion')->model('page');
        return $objMdl->getRow($row, ['page_id'=>$pageId]);
    }

    private function __checkData($data)
    {
        $validator = validator::make(
                ['title' => $data['title'], 'platform'=>$data['platform'], 'tmpl'=>$data['page_tmpl']],
                ['title' => 'required|min:2|max:20','platform'=>'required', 'tmpl'=>'required'],
                ['title' => '专题名称不能为空|专题名称至少两个字|专题名称最多二十个字', 'platform'=>'请指定客户端', 'tmpl'=>'请选择专题模板']
        );
        $validator->newFails();
        if(mb_strlen($data['desc'], 'utf-8') >200)
        {
            throw new LogicException(app::get('syspromotion')->_('专题描述过长'));
        }
        
        // 只在发布状态下判断发布时间
        if(!$data['page_id'])
        {
            // 允许一分钟的误差
            if(strtotime($data['display_time'].':59')<time())
            {
                throw new LogicException(app::get('syspromotion')->_('专题发布时间不能小于当前时间'));
            }
        }
        
        $tmp = [];
        $tmp['page_name'] = $data['title'];
        $tmp['page_tmpl'] = $data['page_tmpl'];
        $tmp['page_desc'] = $data['desc'];
        $tmp['used_platform'] = $data['platform'];
        $tmp['display_time'] = empty($data['display_time']) ? time() : strtotime($data['display_time']);
        $tmp['is_display'] = $data['is_display'];
        $tmp['used_platform'] = $data['platform'];
        if($data['page_id'])
        {
            $tmp['page_id'] = $data['page_id'];
            $tmp['updated_time'] = time();
        }
        else
        {
            $tmp['created_time'] =  $tmp['updated_time'] = time();
        }
        
        return $tmp;
    }

}
