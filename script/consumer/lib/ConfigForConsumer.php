<?php

class ConfigForConsumer{

    static public function getQueueConf()
    {

        return config::get('queue.queues', []);
    }

    static public function getConsumeConf()
    {

        return config::get('queue.consume', []);
    }

}

