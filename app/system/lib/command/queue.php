<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class system_command_queue extends base_shell_prototype {

    public $command_failed_exec = '执行指定的失败队列';

    public function command_failed_exec()
    {
        $args = func_get_args();
        if( $args[0] == 'sync' )
        {
            $id = $args[1];
            $model = app::get('system')->model('queue_failed');
            $data = $model->getRow('id,queue_name,data', array('id'=>$id));
            if( $data )
            {
                $queueMessage = new system_queue_message_failed($model, $data['data'], $data['queue_name'], $data['id']);
                $queueMessage->fire();
            }
        }
        else
        {
            $id = intval($args[0]);
            $this->procThread('system:queue failed_exec sync ', $id);
            logger::info('异步执行失败队列 id '.$id);
        }
    }

    private function procThread($command, $params)
    {
        if ($phpExec){
            $executable = $phpExec;
        }elseif(strtoupper(substr(PHP_OS,0,3))=="WIN"){
            $executable = dirname(ini_get('extension_dir')).'/php';
            $executable = file_exists($executable) ? $executable : 'php';
        }else{
            $executable = PHP_BINDIR.'/php';
            $executable = file_exists($executable) ? $executable : 'php';
        }

        $cmd = $executable.' '.APP_DIR.'/base/cmd '.$command.' '.$params;

        $descriptorspec = array(
            0 => array("pipe", "r"),  // 标准输入，子进程从此管道中读取数据
            1 => array('pipe', 'w'),
            2 => array('file', '/dev/null', 'a')
        );
        $resource = proc_open($cmd, $descriptorspec, $pipes, null, $_ENV);
        //等待代码执行完成
        usleep(5000);

        $status = proc_get_status($resource);
        if( !$status['running'] )
        {
            foreach( $pipes as $pipe )
            {
                fclose($pipe);
            }
            $return = proc_close($resource);
        }
        return $return;
    }
}
