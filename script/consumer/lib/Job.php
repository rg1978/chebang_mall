<?php
class Job{

    private $maxRunTimes = 0;
    private $runTimes = 0;
    private $queueServer = null;
    private $topic = '';

    public function __construct($topic, $consumeConfig, &$server)
    {
        $this->topic = $topic;
        $this->queueServer = system_queue::instance();
        $this->maxRunTimes = $consumeConfig['maxRunTimes'];
        $this->server = $server;
        logger::debug("init job with: topic-$topic");
    }

    public function run($process)
    {
        while($this->runTimes < $this->maxRunTimes)
        {
            $topic = $this->topic;
            logger::debug( "run worker $topic, times : $this->runTimes.");
            $qs = $this->queueServer;
            try{
                if ($queueMessgage = $qs->get($topic)) {
                    $qs->consumer($queueMessgage);
                }
                else
                {
                    sleep(1);
                }
            }catch(Exception $e){
                $msg = $e->getMessage();
                logger::error(json_encode(['msg'=>$msg]));
                logger::debug(json_encode(['msg'=>$msg, 'exception'=>$e->__toString()]));
            }
            $this->runTimes++ ;
        }
        $pid = $process->pid;
        logger::debug("one process is over : $process->pid .");
        $process->close();
        $process->exit(0);
    }

}

