<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://club.shopex.cn/ ShopEx License
 */

use DebugBar\DebugBar;

use debugbar_javascriptRenderer as JavascriptRenderer;

use DebugBar\DataCollector\ExceptionsCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\RequestDataCollector;
use DebugBar\DataCollector\TimeDataCollector;

use debugbar_dataCollector_filesCollector as FilesCollector;
use debugbar_dataCollector_viewCollector as ViewCollector;

use debugbar_dataCollector_symfonyRequestCollector as SymfonyRequestCollector;

use DebugBar\Bridge\DoctrineCollector;
use Doctrine\DBAL\Logging\LoggerChain;

use DebugBar\Bridge\MonologCollector;
use DebugBar\Storage\RedisStorage;

class debugbar_debugbar extends Debugbar
{
    /**
     * True when booted.
     *
     * @var bool
     */
    protected $booted = false;

    /**
     * True when enabled, false disabled an null for still unknown
     *
     * @var bool
     */
    protected $enabled = null;

    /**
     * Enable the Debugbar and boot, if not already booted.
     */
    public function enable()
    {
        $this->enabled = true;

        if (!$this->booted) {
            $this->boot();
        }
    }

    /**
     * Boot the debugbar (add collectors, renderer and listener)
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }

        $debugbar = $this;
        $this->selectStorage($debugbar);
        if ($this->shouldCollect('phpinfo', true)) {
            $debugbar->addCollector(new PhpInfoCollector());
        }

        if ($this->shouldCollect('messages', true)) {
            $debugbar->addCollector(new MessagesCollector());
        }
            
        if ($this->shouldCollect('time', true)) {
            $debugbar->addCollector(new TimeDataCollector());
        }

        $debugbar->startMeasure('application', 'Application');

        if ($this->shouldCollect('memory', true)) {
            $debugbar->addCollector(new MemoryCollector());
        }

        if ($this->shouldCollect('exceptions', true)) {
            try {
                $exceptionCollector = new ExceptionsCollector();
                $exceptionCollector->setChainExceptions(
                    config::get('debugbar.options.exceptions.chain', true)
                );
                $this->addCollector($exceptionCollector);
            } catch (\Exception $e) {
            }
        }

        if ($this->shouldCollect('views', true)) {
            try {
                $collectData = config::get('debugbar.options.views.data', true);
                $this->addCollector(new ViewCollector($collectData));
                event::listen(
                    'composing',
                    function ($view) use ($debugbar) {
                        $debugbar['views']->addView($view);
                    }
                );
            } catch (\Exception $e) {
                $this->addException(
                    new Exception(
                        'Cannot add ViewCollector to Laravel Debugbar: ' . $e->getMessage(), $e->getCode(), $e
                    )
                );
            }
        }        

        if ($this->shouldCollect('db', true)) {
            $db = db::instance();
            if ($debugbar->hasCollector('time') && config::get('debugbar.options.db.timeline', false)) {
                $timeCollector = $debugbar->getCollector('time');
            } else {
                $timeCollector = null;
            }

            $debugStack = new debugbar_support_doctrine_debugStack();
            $historyLogger = clone $db->getConfiguration()->getSQLLogger();
            $loggerChain = new LoggerChain();
            $loggerChain->addLogger($debugStack);
            $loggerChain->addLogger($historyLogger);
            
            $db->getConfiguration()->setSQLLogger($loggerChain);
            
            $queryCollector = new debugbar_dataCollector_queryCollector($debugStack, $timeCollector);
            //$queryCollector = new DoctrineCollector($debugStack, $timeCollector);

            if (config::get('debugbar.options.db.explain.enabled', true)) {
                $types = config::get('debugbar.options.db.explain.types');
                $queryCollector->setExplainSource(true, $types);
            }

            if (config::get('debugbar.options.db.hints', true)) {
                $queryCollector->setShowHints(true);
            }
            
            $debugbar->addCollector($queryCollector);
            
        }

        if ($this->shouldCollect('log', true)) {
            $debugbar->addCollector(new MonologCollector(logger::getMonolog()));
        }

        if ($this->shouldCollect('files', false)) {
            $this->addCollector(new FilesCollector($app));
        }        

        if ($this->shouldCollect('route', true)) {
            try {
                $this->addCollector(new debugbar_dataCollector_routeCollector());
            } catch (\Exception $e) {
                $this->addException(
                    new Exception(
                        'Cannot add RouteCollector to Debugbar: ' . $e->getMessage(),
                        $e->getCode(),
                        $e
                    )
                );
            }
        }

        

        $renderer = $this->getJavascriptRenderer();

        $renderer->setBindAjaxHandlerToXHR(true);

        $this->booted = true;
    }

    public function shouldCollect($name, $default = false)
    {
        return config::get('debugbar.collectors.' . $name, $default);
    }

    /**
     * Starts a measure
     *
     * @param string $name Internal name, used to stop the measure
     * @param string $label Public name
     */
    public function startMeasure($name, $label = null)
    {
        if ($this->hasCollector('time')) {
            /** @var \DebugBar\DataCollector\TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            $collector->startMeasure($name, $label);
        }
    }

    /**
     * Stops a measure
     *
     * @param string $name
     */
    public function stopMeasure($name)
    {
        if ($this->hasCollector('time')) {
            /** @var \DebugBar\DataCollector\TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            try {
                $collector->stopMeasure($name);
            } catch (\Exception $e) {
                //  $this->addException($e);
            }
        }
    }

    public function getJavascriptRenderer()
    {
        //return parent::getJavascriptRenderer();
        if ($this->jsRenderer === null) {
            $this->jsRenderer = new JavascriptRenderer($this, $baseUrl, $basePath);
        }
        return $this->jsRenderer;
    }


    public function modifyResponse($request, $response)
    {
        if (kernel::runningInConsole() || !$this->isEnabled() || $this->isDebugbarRequest()) {
            return $response;
        }

        if ($this->shouldCollect('request', true) && !$this->hasCollector('request')) {
            try {
                $this->addCollector(new SymfonyRequestCollector($request, $response, $_SESSION));
            } catch (\Exception $e) {
                $this->addException(
                    new Exception(
                        'Cannot add SymfonyRequestCollector to luckymall Debugbar: ' . $e->getMessage(),
                        $e->getCode(),
                        $e
                    )
                );
            }
        }
        

        if($response->isRedirection()) {
            // 临时兼容
            if (!isset($_SESSION)) $_SESSION = [];
            $this->stackData();
        } elseif ($this->isJsonRequest($request)) {
            //} elseif (request::ajax($request)) {
            $this->sendDataInHeaders(true);
            //return;
        } elseif ($response->headers->has('Content-Type') && strpos($response->headers->get('Content-Type'), 'html') === false) {
            $this->collect();
        } elseif (true) {
            $this->injectDebugbar($response);
        }

        return $response;

    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    protected function isJsonRequest($request)
    {
        // If XmlHttpRequest, return true
        if ($request->isXmlHttpRequest()) {
            return true;
        }

        // Check if the request wants Json
        $acceptable = $request->getAcceptableContentTypes();
        return (isset($acceptable[0]) && $acceptable[0] == 'application/json');
    }

    /**
     * Collects the data from the collectors
     *
     * @return array
     */
    public function collect()
    {
        /** @var Request $request */
        $request = request::instance();

        $this->data = array(
            '__meta' => array(
                'id' => $this->getCurrentRequestId(),
                'datetime' => date('Y-m-d H:i:s'),
                'utime' => microtime(true),
                'method' => $request->getMethod(),
                'uri' => $request->getRequestUri(),
                'ip' => $request->getClientIp()
            )
        );

        foreach ($this->collectors as $name => $collector) {
            $this->data[$name] = $collector->collect();
        }

        // Remove all invalid (non UTF-8) characters
        array_walk_recursive(
            $this->data,
            function (&$item) {
                if (is_string($item) && !mb_check_encoding($item, 'UTF-8')) {
                    $item = mb_convert_encoding($item, 'UTF-8', 'UTF-8');
                }
            }
        );

        if ($this->storage !== null) {
            $this->storage->save($this->getCurrentRequestId(), $this->data);
        }

        return $this->data;
    }
    

    public function selectStorage($debugbar)
    {
        $storage = new RedisStorage(redis::scene('system')->getClient());
        $debugbar->setStorage($storage);
    }

    public function injectDebugbar($response) {
        $debugbar = $this;
        $content = $response->getContent();

        $renderer = $debugbar->getJavascriptRenderer();

        if ($this->getStorage()) {
            $openHandlerUrl = route('debugbar.openhandler');
            $renderer->setOpenHandlerUrl($openHandlerUrl);
        }        

        $renderedContent = $renderer->renderHead() . $renderer->render();

        $pos = strripos($content, '</body>');
        if (false !== $pos) {
            $content = substr($content, 0, $pos) . $renderedContent . substr($content, $pos);
        } else {
            $content = $content . $renderedContent;
        }
        $response->setContent($content);
        return $response;
    }

    /**
     * Magic calls for adding messages
     *
     * @param string $method
     * @param array $args
     * @return mixed|void
     */
    public function __call($method, $args)
    {
        $messageLevels = array('emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug', 'log');
        if (in_array($method, $messageLevels)) {
            foreach($args as $arg) {
                $this->addMessage($arg, $method);
            }
        }
    }

    /**
     * Adds a message to the MessagesCollector
     *
     * A message can be anything from an object to a string
     *
     * @param mixed $message
     * @param string $label
     */
    public function addMessage($message, $label = 'info')
    {
        if ($this->hasCollector('messages')) {
            /** @var \DebugBar\DataCollector\MessagesCollector $collector */
            $collector = $this->getCollector('messages');
            $collector->addMessage($message, $label);
        }
    }    

    /**
     * Check if the Debugbar is enabled
     * @return boolean
     */
    public function isEnabled()
    {
        if ($this->enabled === null) {
            $enabled = config::get('debugbar.enabled');
            if (is_null($enabled)) {
                $enabled = config::get('app.debug', false);
            } else {
                $eabled = (bool)$enabled;
            }
        }

        return $this->enabled = $enabled;
    }

    /**
     * Check if this is a request to the Debugbar OpenHandler
     *
     * @return bool
     */
    protected function isDebugbarRequest()
    {
        return request::segment(1) == config::get('debugbar.route_prefix');
    }


}
