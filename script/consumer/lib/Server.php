<?php

class Server
{

    private $queueConfig = [];

    private $consumeConfig = [];

    private $process = [];

    public function __construct($queueConfig, $consumeConfig)
    {
        $this->queueConfig = $queueConfig;
        $this->consumeConfig = $consumeConfig;
    }

    public function run()
    {
        $server = $this;
        swoole_timer_tick(10000, [$this, 'start']);
    //  $this->start();
        swoole_process::signal(SIGCHLD, function($sig, $server) {
            //必须为false，非阻塞模式
            while($ret =  swoole_process::wait(false)) {
                $this->stop($ret['pid']);
                logger::debug('Exit the process which pid = '.$ret['pid']);
            }
        });



          //foreach($this->process as $topic => $processGroup)
          //{
          //    foreach($this->process[$topic] as $pid => $process)
          //    {
          //        if($this->process[$topic][$pid] == null)
          //            unset($this->process[$topic][$pid]);
          //    }
          //}

         //while($ret = swoole_process::wait(false))
          //{
          //    var_dump($ret);

          //}
    }

    public function start()
    {
        echo "run start flag!!!!!!!!!\n";
        foreach($this->queueConfig as $topic=>$qc)
        {

            $threadNum = $qc['thread'];
            while(count($this->pids[$topic]) < $threadNum)
            {
                $job = new Job($topic, $this->consumeConfig, $this);
                $process = new swoole_process([$job, 'run']);
                $process->name('php server.php ' . $topic . ' queue worker');
                $pid = $process->start();

//              $this->process[$topic][$pid] = $process;
                $this->pids[$topic][$pid] = $pid;
//              $this->pids[$topic][$pid] = $pid;
//              var_dump($this->process[$topic][$pid]);
            }
        }
    }

    public function stop($pid)
    {
        logger::debug("Server want to find the process by pid($pid) to close!");
        foreach($this->queueConfig as $topic=>$qc)
        {
            if(isset($this->pids[$topic][$pid]))
            {
                logger::debug("Server find the process by pid($pid) with topic($topic)!");
                $this->close($topic, $pid);
            }
        }
    }

    public function close($topic, $pid)
    {
      //$this->process[$topic][$pid]->close();
     // var_dump($this->pids[$topic][$pid]);
        unset($this->pids[$topic][$pid]);
        logger::debug("Server close the process by pid($pid) with topic($topic)!\n");

    }

}
