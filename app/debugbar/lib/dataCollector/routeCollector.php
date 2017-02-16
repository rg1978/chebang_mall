<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://club.shopex.cn/ ShopEx License
 */

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use base_routing_router as Router;
//use Illuminate\Routing\Route;
//use Illuminate\Http\Request;
//use Illuminate\Routing\Route;
//use Illuminate\Routing\Router;
//use Illuminate\Support\Facades\Config;


class debugbar_dataCollector_routeCollector extends DataCollector implements Renderable
{
    /**
     * The router instance.
     *
     * @var \Illuminate\Routing\Router
     */
    protected $router;

    public function __construct()
    {
        $this->router = route::instance();
    }

    /**
     * {@inheritDoc}
     */
    public function collect()
    {
        //$route = $this->router->current();
        $route = request::route();
        return $this->getRouteInformation($route[1]);
    }

    /**
     * Get the route information for a given route.
     *
     * @param  \Illuminate\Routing\Route $route
     * @return array
     */
    protected function getRouteInformation($route)
    {
        
        //$uri = head($route->methods()) . ' ' . $route->uri();
        $uri = $route['uri'];
        //$action = $route->getAction();
        //$action = $route['action'];
        $action = $route;

        $result = ['uri' => $uri ?: '-'];
        $result = array_merge($result, $action);

        if (isset($action['uses']) && strpos($action['uses'], '@') !== false) {
			list($controller, $method) = explode('@', $action['uses']);
			if(class_exists($controller) && method_exists($controller, $method)) {
			    $reflector = new \ReflectionMethod($controller, $method);
			}
            unset($result['uses']);
		} else {
            $actionClosureKey = null;
            foreach ($result as $actionClosureKey => $value) {
                if ($value instanceof Closure) {
                    $closure = $value;
                    break;
                }
            }

            if (is_int($actionClosureKey) || is_string($actionClosureKey)) {
                unset($result[$actionClosureKey]);
            }

            $reflector = new \ReflectionFunction($closure);
            //            $result['uses'] = $this->formatVar($closure);
            //            echo '<pre>';
            //            var_dump($result['uses']);exit;
            
        }

        if (isset($reflector)) {
            $filename = ltrim(str_replace(ROOT_DIR, '', $reflector->getFileName()), '/');
            $result['file'] = $filename . ':' . $reflector->getStartLine() . '-' . $reflector->getEndLine();
        }

        
        if ($middleware = $this->getMiddleware($route)) { 
            $result['middleware'] = $middleware; 
        }
        
        return $result;
    }

    
    /**
     * Get middleware
     *
     * @param  \Illuminate\Routing\Route $route
     * @return string
     */
    protected function getMiddleware($route)
    {
        $middleware = array_values($route['middleware']);

        return implode(', ', $middleware);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'route';
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgets()
    {
        $widgets = array(
            "route" => array(
                "icon" => "share",
                "widget" => "PhpDebugBar.Widgets.VariableListWidget",
                "map" => "route",
                "default" => "{}"
            )
        );
        if (config::get('debugbar.options.route.label', true)) {
            $widgets['currentroute'] = array(
                "icon" => "share",
                "tooltip" => "route",
                "map" => "route.uri",
                "default" => ""
            );
            
        }
        return $widgets;
    }

    /**
     * Display the route information on the console.
     *
     * @param  array $routes
     * @return void
     */
    protected function displayRoutes(array $routes)
    {
        $this->table->setHeaders($this->headers)->setRows($routes);

        $this->table->render($this->getOutput());
    }
}
