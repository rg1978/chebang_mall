<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
use DebugBar\DataCollector\ExceptionsCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\RequestDataCollector;
use DebugBar\DataCollector\TimeDataCollector;

use DebugBar\Bridge\DoctrineCollector;
use Doctrine\DBAL\Logging\DebugStack;

use DebugBar\Bridge\MonologCollector;

class debugbar_middleware_debugbar
{

    public function __construct()
    {
        
    }

    public function handle($request, Closure $next)
    {
        try {
            /** @var \Illuminate\Http\Response $response */
            $response = $next($request);
        } catch (Exception $e) {
            throw $e;
            //$response = $this->handleException($request, $e);
        }
 
        // Modify the response to add the Debugbar
        debugbar::modifyResponse($request, $response);

        return $response;
    }
}
