<?php


use Illuminate\Contracts\Routing\UrlRoutable;

class base_routing_urlgenerator
{

	/**
	 * The request instance.
	 *
	 * @var \Illuminate\Http\Request
	 */
    protected $request;

    /**
     * The cached URL scheme for generating URLs.
     *
     * @var string|null
     */
    protected $cachedScheme;

    /**
     * The cached URL root.
     *
     * @var string|null
     */
    protected $cachedRoot;

    /**
     * The URL schema to be forced on all generated URLs.
     *
     * @var string|null
     */
    protected $forceSchema;

    /**
     * Create a new URL redirector instance.
     *
     * @param  Application  $application
     * @return void
     */
    public function __construct(base_http_request $request)
    {
        //$this->request = $request;
    }

    public function getRequest()
    {
        return request::instance();
    }

    /**
     * Get the full URL for the current request.
     *
     * @return string
     */
    public function full()
    {
        return $this->request->fullUrl();
    }

    /**
     * Get the current URL for the request.
     *
     * @return string
     */
    public function current()
    {
        return $this->to($this->request->getPathInfo());
    }

    /**
	 * Get the URL for the previous request.
	 *
	 * @return string
	 */
	public function previous()
	{
		return $this->to($this->request->headers->get('referer'));
	}    

    /**
     * Generate a url for the application.
     *
     * @param  string  $path
     * @param  array  $extra
     * @param  bool  $secure
     * @return string
     */
    public function to($path, $extra = [], $secure = null)
    {
        // First we will check if the URL is already a valid URL. If it is we will not
        // try to generate a new one but will simply return the URL as is, which is
        // convenient since developers do not always have to check if it's valid.
        if ($this->isValidUrl($path)) {
            return $path;
        }

        $scheme = $this->getSchemeForUrl($secure);

        $tail = implode('/', array_map(
            'rawurlencode', (array) $extra)
        );

        // Once we have the scheme we will compile the "tail" by collapsing the values
        // into a single string delimited by slashes. This just makes it convenient
        // for passing the array of parameters to this URL as a list of segments.
        $root = $this->getRootUrl($scheme);

        if (($queryPosition = strpos($path, '?')) !== false) {
            $query = mb_substr($path, $queryPosition);
            $path = mb_substr($path, 0, $queryPosition);
        } else {
            $query = '';
        }
        return $this->trimUrl($root, $path, $tail).$query;
    }

    /**
     * Generate a secure, absolute URL to the given path.
     *
     * @param  string  $path
     * @param  array   $parameters
     * @return string
     */
    public function secure($path, $parameters = [])
    {
        return $this->to($path, $parameters, true);
    }

    /**
     * Generate a URL to an application asset.
     *
     * @param  string  $path
     * @param  bool|null  $secure
     * @return string
     */
    public function asset($path, $secure = null)
    {
        if ($this->isValidUrl($path)) {
            return $path;
        }

        // Once we get the root URL, we will check to see if it contains an index.php
        // file in the paths. If it does, we will remove it since it is not needed
        // for asset paths, but only for routes to endpoints in the application.
        $root = $this->getRootUrl($this->getScheme($secure));

        return $this->removeIndex($root).'/'.trim($path, '/');
    }

    /**
     * Generate a URL to an application asset from a root domain such as CDN etc.
     *
     * @param  string  $root
     * @param  string  $path
     * @param  bool|null  $secure
     * @return string
     */
    public function assetFrom($root, $path, $secure = null)
    {
        // Once we get the root URL, we will check to see if it contains an index.php
        // file in the paths. If it does, we will remove it since it is not needed
        // for asset paths, but only for routes to endpoints in the application.
        $root = $this->getRootUrl($this->getScheme($secure), $root);

        return $this->removeIndex($root).'/'.trim($path, '/');
    }

    /**
     * Remove the index.php file from a path.
     *
     * @param  string  $root
     * @return string
     */
    protected function removeIndex($root)
    {
        $i = 'index.php';

        return str_contains($root, $i) ? str_replace('/'.$i, '', $root) : $root;
    }

    /**
     * Generate a URL to a secure asset.
     *
     * @param  string  $path
     * @return string
     */
    public function secureAsset($path)
    {
        return $this->asset($path, true);
    }

    /**
     * Get the scheme for a raw URL.
     *
     * @param  bool|null  $secure
     * @return string
     */
    protected function getScheme($secure)
    {
        if (is_null($secure)) {
            return $this->forceSchema ?: request::getScheme().'://';
        }

        return $secure ? 'https://' : 'http://';
    }

    /**
     * Force the schema for URLs.
     *
     * @param  string  $schema
     * @return void
     */
    public function forceSchema($schema)
    {
        $this->forceSchema = $schema.'://';
    }

    /**
     * Get the URL to a named route.
     *
     * @param  string  $name
     * @param  mixed   $parameters
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function route($name, $parameters = [])
    {
        $routeInfo = route::namedRoutes($name, $parameters);
        
        if (! $routeInfo) {
            throw new \InvalidArgumentException("Route [{$name}] not defined.");
        }

        return $this->routeInfoTo($routeInfo, $parameters);
    }

    public function action($name, $parameters = [])
    {
        $routeInfo = route::actionRoutes($name);
        
        if (! $routeInfo) {
            throw new \InvalidArgumentException("Route [{$name}] not defined.");
        }

        return $this->routeInfoTo($routeInfo, $parameters);
    }
    

    public function routeInfoTo($routeInfo, $parameters = [])
    {
        //$domain = array_get($routeInfo['action'], 'domain', null);
        $pathInfo = array_get($routeInfo, 'uri', null);
        if (!route::hasDefinedDomain()) {
            $url = $this->trimUrl($this->getRootUrl($this->getSchemeForUrl(null)), $pathInfo);
        } elseif (isset($routeInfo['action']['domain'])) {
            //todo 看路由是否需要决定scheme
            $url = $this->trimUrl($this->getSchemeForUrl(null).$routeInfo['action']['domain'], $pathInfo);
        } else {
            $url = $this->trimUrl(config::get('app.url', 'http:/localhost'), $pathInfo);
        }
        
        $parameters = is_array($parameters) ? $parameters : [$parameters];

        
        $url = $this->replaceRouteParameters($url, $parameters);

        /*
        if ($domain) {
            $uri = $this->getSchemeForUrl(null).$uri;
        } else {
            $uri = $this->to($uri, []);
        }
        */

        if (! empty($parameters)) {
            $fullUrl = $url.'?'.http_build_query($parameters);
        } else {
            $fullUrl = $url;
        }
        

        return $fullUrl;        
    }

    public function replaceRouteParameters($uri, array &$parameters)
    {
        $uri = preg_replace_callback('/\{(.*?)(:.*?)?(\{[0-9,]+\})?\}/', function ($m) use (&$parameters) {
            return isset($parameters[$m[1]]) ? array_pull($parameters, $m[1]) : $m[0];
        }, $uri);

        return $uri;
        
    }


    /**
     * Determine if the given path is a valid URL.
     *
     * @param  string  $path
     * @return bool
     */
    protected function isValidUrl($path)
    {
        if (starts_with($path, ['#', '//', 'mailto:', 'tel:', 'http://', 'https://'])) {
            return true;
        }

        return filter_var($path, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Get the scheme for a raw URL.
     *
     * @param  bool|null  $secure
     * @return string
     */
    protected function getSchemeForUrl($secure)
    {
        if (is_null($secure)) {
            if (is_null($this->cachedScheme)) {
                $this->cachedScheme = request::getScheme().'://';
            }

            return $this->cachedScheme;
        }

        return $secure ? 'https://' : 'http://';
    }

    /**
     * Get the base URL for the request.
     *
     * @param  string  $scheme
     * @param  string  $root
     * @return string
     */
    protected function getRootUrl($scheme, $root = null)
    {
        if (is_null($root)) {
            if (is_null($this->cachedRoot)) {
                $this->cachedRoot = request::root();
            }
            
            $root = $this->cachedRoot;
        }

        $start = starts_with($root, 'http://') ? 'http://' : 'https://';

        return preg_replace('~'.$start.'~', $scheme, $root, 1);
    }

    /**
     * Format the given URL segments into a single URL.
     *
     * @param  string  $root
     * @param  string  $path
     * @param  string  $tail
     * @return string
     */
    protected function trimUrl($root, $path, $tail = '')
    {
        return trim($root.'/'.trim($path.'/'.$tail, '/'), '/');
    }
}
