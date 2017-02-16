#!/usr/bin/env php
<?php

$system = strtoupper(substr(PHP_OS,0,3));

if($system != "WIN")
{
    echo 'The script only to support in windows'."\n";
}

$root_dir = realpath(dirname(__FILE__).'/../../');
$script_dir = $root_dir.'/script';
require_once($script_dir."/lib/runtime.php");


$executable = dirname(ini_get('extension_dir')).'/php';
$executable = file_exists($executable) ? $executable : 'php';
$executable = str_replace('\\','/',$executable);

$queues = config::get('queue.queues', array());

$list = array_keys($queues);
foreach( $list as $queueName )
{
    for( $i=0;$i<100;$i++ )
    {
        if(system_queue::instance()->is_end($queueName))
        {
            break;
        }
        $execStr =  $executable .' '.$script_dir.'/queue\queuescript.php '. $queueName .' 3600';
        exec($execStr);
        echo $execStr."\n";
    }
}
?>
