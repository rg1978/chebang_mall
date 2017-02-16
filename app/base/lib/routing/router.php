<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

use Closure;
use Exception;
use Throwable;
use FastRoute\Dispatcher;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

use base_pipeline_pipeline as Pipeline;
use base_support_collection as Collection;
use base_http_HttpResponseException as HttpResponseException;

class base_routing_router
{
    const INDEX_SEPERATE_FLAG = '*%#$#^@%@';

	/**
	 * The route collection instance.
	 *
	 * @var \Illuminate\Routing\RouteCollection
	 */
	protected $routes = [];

    protected $domains = [];

    /**
     * All of the named routes and URI pairs.
     *
     * @var array
     */
    public $namedRoutes = [];

    public $actionRoutes = [];

    
	/**
	 * All of the short-hand keys for middlewares.
	 *
	 * @var array
	 */
	protected $middleware = [];

    /**
     * All of the route specific middleware short-hands.
     *
     * @var array
     */
    protected $routeMiddleware = [];

	/**
	 * The route group attribute stack.
	 *
	 * @var array
	 */
	protected $groupStack = [];

    /**
     * The current route being dispatched.
     *
     * @var array
     */
    protected $currentRoute;
    
    /**
     * The FastRoute dispatcher.
     *
     * @var \FastRoute\Dispatcher
     */
    protected $dispatcher;

    public function namedRoutes($name)
    {
        if ($name === null) {
            return null;
        } elseif(isset($this->namedRoutes[$name])) {
            return $this->getIndexRoute($this->namedRoutes[$name]);
        }

        return null;
    }

    public function actionRoutes($name = null)
    {
        if ($name === null) {
            return $this->actionRoutes;
        } elseif(isset($this->actionRoutes[$name])) {
            return $this->getIndexRoute($this->actionRoutes[$name]);
        }

        return null;
    }

    public function getIndexRoute($index) {
        return array_get($this->routes, $index, null, self::INDEX_SEPERATE_FLAG);
    }

    /**
     * 鑾峰彇璺����敱鐩稿叧鏁版嵁宸插仛璺����敱
     *
     * @return array
     */
    public function getData() 
    {
        return [
            'namedRoutes' => $this->namedRoutes,
            'actionRoutes' => $this->actionRoutes,
            'routes' => $this->routes
        ];
    }

    /**
     * 璺����敱缂撳瓨鎭㈠����
     *
     * @param  array     $data
     * @return array
     */
    public function setData($data)
    {
        $this->namedRoutes = $data['namedRoutes'];
        $this->actionRoutes = $data['actionRoutes'];
        $this->routes = $data['routes'];
        
    }
    
	/**
	 * Create a route group with shared attributes.
	 *
	 * @param  array     $attributes
	 * @param  \Closure  $callback
	 * @return void
	 */
	public function group(array $attributes, Closure $callback)
	{
		$this->updateGroupStack($attributes);

		// Once we have updated the group stack, we will execute the user Closure and
		// merge in the groups attributes when the route is created. After we have
		// run the callback, we will pop the attributes off of this group stack.
		call_user_func($callback, $this);

		array_pop($this->groupStack);
	}

	/**
	 * Update the group stack with the given attributes.
	 *
	 * @param  array  $attributes
	 * @return void
	 */
	protected function updateGroupStack(array $attributes)
	{
        if (isset($attributes['middleware']) && is_string($attributes['middleware'])) {
            $attributes['middleware'] = explode('|', $attributes['middleware']);
        }

		if ( ! empty($this->groupStack))
		{
			$attributes = $this->mergeGroup($attributes, last($this->groupStack));
		}

		$this->groupStack[] = $attributes;
	}

	/**
	 * Merge the given group attributes.
	 *
	 * @param  array  $new
	 * @param  array  $old
	 * @return array
	 */
	public static function mergeGroup($new, $old)
	{
		$new['prefix'] = static::formatGroupPrefix($new, $old);

        if (isset($old['domain'])) {
            if (!isset($new['domain'])) {
                $new['domain'] = $old['domain'];
            }
        }

        if (isset($old['as'])) {
            $new['as'] = $old['as'].(isset($new['as']) ? $new['as'] : '');
        }


		return array_merge_recursive(array_except($old, ['domain', 'prefix', 'as']), $new);
	}

    /**
     * Format the prefix for the new group attributes.
     *
     * @param  array  $new
     * @param  array  $old
     * @return string|null
     */
    protected static function formatGroupPrefix($new, $old)
    {
        $oldPrefix = isset($old['prefix']) ? $old['prefix'] : null;

        if (isset($new['prefix'])) {
            return trim($oldPrefix, '/').'/'.trim($new['prefix'], '/');
        }

        return $oldPrefix;
    }
    
    /**
     * Register a route with the application.
     *
     * @param  string  $uri
     * @param  mixed  $action
     * @return $this
     */
    public function get($uri, $action)
    {
        $this->addRoute('GET', $uri, $action);

        return $this;
    }

    /**
     * Register a route with the application.
     *
     * @param  string  $uri
     * @param  mixed  $action
     * @return $this
     */
    public function post($uri, $action)
    {
        $this->addRoute('POST', $uri, $action);

        return $this;
    }

    /**
     * Register a route with the application.
     *
     * @param  string  $uri
     * @param  mixed  $action
     * @return $this
     */
    public function put($uri, $action)
    {
        $this->addRoute('PUT', $uri, $action);

        return $this;
    }

    /**
     * Register a route with the application.
     *
     * @param  string  $uri
     * @param  mixed  $action
     * @return $this
     */
    public function patch($uri, $action)
    {
        $this->addRoute('PATCH', $uri, $action);

        return $this;
    }

    /**
     * Register a route with the application.
     *
     * @param  string  $uri
     * @param  mixed  $action
     * @return $this
     */
    public function delete($uri, $action)
    {
        $this->addRoute('DELETE', $uri, $action);

        return $this;
    }

    /**
     * Register a route with the application.
     *
     * @param  string  $uri
     * @param  mixed  $action
     * @return $this
     */
    public function options($uri, $action)
    {
        $this->addRoute('OPTIONS', $uri, $action);

        return $this;
    }

    public function match($method, $uri, $action)
    {
        return $this->addRoute($method, $uri, $action);
    }

    /**
     * Add a route to the collection.
     *
     * @param  array|string  $method
     * @param  string  $uri
     * @param  mixed  $action
     * @return void
     */
    public function addRoute($method, $uri, $action)
    {
        $action = $this->parseAction($action);

        if ($this->hasGroupStack()) {
            $groupAttributes = last($this->groupStack);
            if (isset($groupAttributes['prefix'])) {
                $uri = trim($groupAttributes['prefix'], '/').'/'.trim($uri, '/');
            }

            if (isset($groupAttributes['middleware'])) {
                if (isset($action['middleware'])) {
                    $action['middleware'] = array_merge($groupAttributes['middleware'], $action['middleware']);
                } else {
                    $action['middleware'] = $groupAttributes['middleware'];
                }
            }

            if (isset($groupAttributes['scheme'])) {
                if (isset($action['scheme'])) {
                    $action['scheme'] = array_merge((array)$groupAttributes['scheme'], (array)$action['middleware']);
                } else {
                    $action['scheme'] = $groupAttributes['scheme'];
                }
            }
            
            if (!isset($action['domain'])) {
                if (isset($groupAttributes['domain'])) {
                    $action['domain'] = $groupAttributes['domain'];
                }
            }
        }
        $uri = '/'.trim($uri, '/');

        $method = (array)$method;
        
        foreach ($method as $verb) {
            if (isset($action['domain'])) {
                $this->routes['domain'][$action['domain']][$verb.$uri] = ['method' => $verb, 'uri' => $uri, 'action' => $action];

                $this->addRoutesIndex('domain'.self::INDEX_SEPERATE_FLAG.$action['domain'].self::INDEX_SEPERATE_FLAG.$verb.$uri, $action);
            } else {
                $this->routes['nodomain'][$verb.$uri] = ['method' => $verb, 'uri' => $uri, 'action' => $action];
                $this->addRoutesIndex('nodomain'.self::INDEX_SEPERATE_FLAG.$verb.$uri, $action);
            }
        }
    }

    public function hasDefinedDomain() {
        return isset($this->routes['domain']) ? true : false;
    }

    protected function addRoutesIndex($index, $action)
    {
        if (isset($action['as'])) {
            $this->namedRoutes[$action['as']] = $index;
        }

        if (isset($action['uses'])) {
            $this->actionRoutes[$action['uses']] = $index;
        }
    }


    /**
     * Parse the action into an array format.
     *
     * @param  mixed  $action
     * @return array
     */
    protected function parseAction($action)
    {
        if (is_string($action)) {
            return ['uses' => $action];
        } elseif (! is_array($action)) {
            return [$action];
        }

        if (isset($action['middleware']) && is_string($action['middleware'])) {
            $action['middleware'] = explode('|', $action['middleware']);
        }

        return $action;
    }

    /**
     * Determine if the router currently has a group stack.
     *
     * @return bool
     */
    public function hasGroupStack()
    {
        return ! empty($this->groupStack);
    }

    /**
     * Add new middleware to the application.
     *
     * @param  Closure|array  $middleware
     * @return $this
     */
    public function middleware($middleware)
    {
        if (! is_array($middleware)) {
            $middleware = [$middleware];
        }

        $this->middleware = array_unique(array_merge($this->middleware, $middleware));

        return $this;
    }
    
    /**
     * Define the route middleware for the application.
     *
     * @param  array  $middleware
     * @return $this
     */
    public function routeMiddleware(array $middleware)
    {
        $this->routeMiddleware = array_merge($this->routeMiddleware, $middleware);

        return $this;
    }

    /**
     * Run the application and send the response.
     *
     * @param  SymfonyRequest|null  $request
     * @return void
     */
    public function run($request = null)
    {
        $response = $this->dispatch($request);

        if ($response instanceof SymfonyResponse) {
            $response->send();
        } else {
            echo (string) $response;
        }

        if (count($this->middleware) > 0) {
            $this->callTerminableMiddleware($response);
        }
    }

    /**
     * Call the terminable middleware.
     *
     * @param  mixed  $response
     * @return void
     */
    protected function callTerminableMiddleware($response)
    {
        $response = $this->prepareResponse($response);

        foreach ($this->middleware as $middleware) {
            if (! is_string($middleware)) {
                continue;
            }

            $instance = kernel::single($middleware);

            if (method_exists($instance, 'terminate')) {
                $instance->terminate(request::instance(), $response);
            }
        }
    }

    public function dispatch($request)
    {
        $domain = $request->getHttpHost();
        if (isset($this->routes['domain'])) {
            if (isset($this->routes['domain'][$domain])) {
                //todo
                return $this->dispatchRoute($request, $this->routes['domain'][$domain]);
            } else {
                $dispatcher = \FastRoute\simpleDispatcher(function ($r) {
                    foreach(array_keys($this->routes['domain']) as $domain){
                        $r->addRoute('aaaa', $domain, $this->routes['domain'][$domain]);
                    }
                });

                $routeInfo = $dispatcher->dispatch('aaaa', $domain);

                switch ($routeInfo[0]) {
                    case Dispatcher::NOT_FOUND:
                        return $this->dispatchRoute($request, $this->routes['nodomain']);
                    case Dispatcher::METHOD_NOT_ALLOWED:
                        throw new MethodNotAllowedHttpException($routeInfo[1]);
                    case Dispatcher::FOUND:
                        return $this->dispatchRoute($request, $routeInfo[1], (array)$routeInfo[2]);
                }
            }
        } else {
            //todo
            return $this->dispatchRoute($request, $this->routes['nodomain']);
        }
    }

    /**
     * Dispatch the incoming request.
     *
     * @param  SymfonyRequest|null  $request
     * @return Response
     */
    public function dispatchRoute($request = null, $routes, $parameters = [])
    {
        list($method, $pathInfo) = $this->parseIncomingRequest($request);
        $pathInfo = rtrim($pathInfo, '/');
        $pathInfo = empty($pathInfo) ? '/' : $pathInfo;
        $cacheStrategys = config::get('page_cache.pages');

        try {
            return $this->sendThroughPipeline($this->middleware, function () use ($method, $pathInfo, $routes, $parameters) {

                if (isset($routes[$method.$pathInfo])) {
                    return $this->handleFoundRouteCache([true, $routes[$method.$pathInfo]['action'], $parameters]);
                }

                $routeInfo = $this->createDispatcher($routes)->dispatch($method, $pathInfo);

                $routeInfo[2] = array_merge($parameters, (array)$routeInfo[2]);
                
                return $this->handleDispatcherResponse($routeInfo);
            });
        } catch (Exception $e) {
            return $this->sendExceptionToHandler($e);
        } catch (Throwable $e) {
            return $this->sendExceptionToHandler($e);
        }
    }

    public function sendExceptionToHandler($e)
    {
        return kernel::exceptionBootstrap()->handleException($e);
    }

    /**
     * Parse the incoming request and return the method and path info.
     *
     * @param  \Illuminate\Http\Request|null  $request
     * @return array
     */
    protected function parseIncomingRequest($request)
    {
        return [$request->getMethod(), $request->getPathInfo()];
    }
    
    /**
     * Create a FastRoute dispatcher instance for the application.
     *
     * @return Dispatcher
     */
    protected function createDispatcher($routes)
    {
        return $this->dispatcher ?: \FastRoute\simpleDispatcher(function ($r) use ($routes) {
            foreach ($routes as $route) {
                $r->addRoute($route['method'], $route['uri'], $route['action']);
            }
        });
    }

    /**
     * Set the FastRoute dispatcher instance.
     *
     * @param  \FastRoute\Dispatcher  $dispatcher
     * @return void
     */
    public function setDispatcher(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
    
    /**
     * Handle the response from the FastRoute dispatcher.
     *
     * @param  array  $routeInfo
     * @return mixed
     */
    protected function handleDispatcherResponse($routeInfo)
    {
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                throw new NotFoundHttpException;

            case Dispatcher::METHOD_NOT_ALLOWED:
                throw new MethodNotAllowedHttpException($routeInfo[1]);

            case Dispatcher::FOUND:
                return $this->handleFoundRouteCache($routeInfo);
        }
    }

    public function handleFoundRouteCache($routeInfo)
    {
        $this->currentRoute = $routeInfo;

        request::setRouteResolver(function () {
            return $this->currentRoute;
        });

        $cacheStrategys = config::get('page_cache.pages');
		if (request::getMethod() == 'GET' && $cacheStrategys != null) {
            if ($this->currentRouteName() !== null && ($cacheStrategy = $cacheStrategys[$this->currentRouteName()])) {
                $cacheKey = md5(request::fullUrl());
                $timeout = (int)$cacheStrategy['timeout'] !==0 ? $cacheStrategy['timeout'] : 1;
                return unserialize(cache::store('controller-cache')->remember($cacheKey, $timeout, function() use ($routeInfo) {
                    return serialize($this->handleFoundRoute($routeInfo));
                }));
            }
        }
        return $this->handleFoundRoute($routeInfo);
    }
    /**
     * Handle a route found by the dispatcher.
     *
     * @param  array  $routeInfo
     * @return mixed
     */
    protected function handleFoundRoute($routeInfo)
    {
        $action = $routeInfo[1];

        // Pipe through route middleware...
        if (isset($action['middleware'])) {
            $middleware = $this->gatherMiddlewareClassNames($action['middleware']);
            return $this->prepareResponse($this->sendThroughPipeline($middleware, function () {
                //todo:娌℃湁route
                return $this->callActionOnArrayBasedRoute(request::route());
            }));
        }

        return $this->prepareResponse(
            $this->callActionOnArrayBasedRoute($routeInfo)
        );
    }

    /**
     * Gather the full class names for the middleware short-cut string.
     *
     * @param  string  $middleware
     * @return array
     */
    protected function gatherMiddlewareClassNames($middleware)
    {
        $middleware = is_string($middleware) ? explode('|', $middleware) : (array) $middleware;

        return array_map(function ($name) {
            list($name, $parameters) = array_pad(explode(':', $name, 2), 2, null);

            return array_get($this->routeMiddleware, $name, $name).($parameters ? ':'.$parameters : '');
        }, $middleware);
    }

    /**
     * Send the request through the pipeline with the given callback.
     *
     * @param  array  $middleware
     * @param  \Closure  $then
     * @return mixed
     */
    protected function sendThroughPipeline(array $middleware, Closure $then)
    {
        //$shouldSkipMiddleware = $this->bound('middleware.disable') &&
        //$this->make('middleware.disable') === true;

        //if (count($middleware) > 0 && ! $shouldSkipMiddleware) {
        if (count($middleware) > 0) {
            return (new Pipeline($this))
                ->send(request::instance())
                ->through($middleware)
                ->then($then);
        }
        return $then();
    }
    

    /**
     * Call the Closure on the array based route.
     *
     * @param  array  $routeInfo
     * @return mixed
     */
    protected function callActionOnArrayBasedRoute($routeInfo)
    {
        $action = $routeInfo[1];

        if (isset($action['uses'])) {
            return $this->prepareResponse($this->callControllerAction($routeInfo));
        }

        foreach ($action as $value) {
            if ($value instanceof Closure) {
                $closure = $value;
                break;
            }
        }

        try {
            return $this->prepareResponse(call_user_func_array($closure, $routeInfo[2]));
        } catch (HttpResponseException $e) {
            return $e->getResponse();
        }
    }

    /**
     * Call a controller based route.
     *
     * @param  array  $routeInfo
     * @return mixed
     */
    protected function callControllerAction($routeInfo)
    {
        list($controller, $method) = explode('@', $routeInfo[1]['uses']);

        $appId = substr($controller,0, strpos($controller, '_'));
        
        if (! method_exists($instance = new $controller(app::get($appId)), $method)) {
            throw new NotFoundHttpException;
        }

        return $this->callController($instance, $method, $routeInfo);
    }

    public function callController($instance, $method, $routeInfo)
    {
        $callable = [$instance, $method];
        $parameters = $routeInfo[2];
        try {
            return $this->prepareResponse(
                call_user_func_array($callable, $parameters)
            );
        } catch (HttpResponseException $e) {
            return $e->getResponse();
        }
        
    }

    
    /**
     * Prepare the response for sending.
     *
     * @param  mixed  $response
     * @return Response
     */
    public function prepareResponse($response)
    {
		if ( ! $response instanceof SymfonyResponse)
		{
			$response = new base_http_response($response);
		}

        return $response->prepare(request::instance());
        return $response;
    }

    /**
     * Get the raw routes for the application.
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Get the action name of the current route.
     *
     * @return string
     */
    public function currentActionName()
    {
        return isset($this->currentRoute[1]['uses']) ? $this->currentRoute['1']['uses'] : null;
    }

    /**
     * Get the route name of the current route.
     *
     * @return string
     */
    public function currentRouteName()
    {
        return isset($this->currentRoute[1]['as']) ? $this->currentRoute['1']['as'] : null;
    }

    /**
     * Get the route parameters of the current route.
     *
     * @return string
     */
    public function currentParameters()
    {
        return isset($this->currentRoute[2]) ? $this->currentRoute['2'] : null;
    }
}
