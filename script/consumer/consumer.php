#!/usr/bin/env php
<?php
//-----------------------------------------------------------------------------------------------
//init ec-os framework start;
require_once('../lib/runtime.php');

require_once('lib/ConfigForConsumer.php');
require_once('lib/Server.php');
require_once('lib/Job.php');

//init ec-os framework end;

//-----------------------------------------------------------------------------------------------
set_error_handler('error_handler');
function error_handler($code,$msg,$file,$line)
{
    if($code == ($code & (E_ERROR ^ E_USER_ERROR ^ E_USER_WARNING)))
    {
        logger::error(sprintf('CONSUMER ERROR:%d @ %s @ file:%s @ line:%d', $code, $msg, $file, $line));
        if($code == ($code & (E_ERROR ^ E_USER_ERROR)))
        {
            exit;
        }
    }
    return true;
}
//-----------------------------------------------------------------------------------------------

$queueConfig = ConfigForConsumer::getQueueConf();
$consumeConfig = ConfigForConsumer::getConsumeConf();

$server = new Server($queueConfig, $consumeConfig);
$server->run();


