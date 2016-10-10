<?php namespace core\route;

use Closure;
use Exception;
use ReflectionMethod;

class Route
{
    private $app = '';
    /**
     * @var array The route patterns and their handling functions
     */
    private $routes = [];
    /**
     * @var array The before filter route patterns and their handling functions
     */
    private $beforeFilter = [];
    /**
     * @var array The after filter route patterns and their handling functions
     */
    private $afterFilter = [];
    /**
     * @var string Current baseRoute, used for (sub)route grouping
     */
    private $baseRoute = '';
    /**
     * @var string The Request Method that needs to be handled
     */
    private $method = '';
    /**
     * @var object|callable The function to be executed when no route has been matched
     */
    protected $notFound;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Store a before filter route and a handling function to be executed when accessed using one of the specified methods
     *
     * @param string $methods Allowed methods, | delimited
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $filter The handling function to be executed
     */
    public function before($methods, $pattern, $filter)
    {
        $pattern = $this->baseRoute . '/' . trim($pattern, '/');
        $pattern = $this->baseRoute ? rtrim($pattern, '/') : $pattern;
        foreach (explode('|', $methods) as $method) {
            $this->beforeFilter[$method][] = array(
                'pattern' => $pattern,
                'filter' => $filter
            );
        }
    }

    /**
     * Store a after filter route and a handling function to be executed when accessed using one of the specified methods
     *
     * @param string $methods Allowed methods, | delimited
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $filter The handling function to be executed
     */
    public function after($methods, $pattern, $filter)
    {
        $pattern = $this->baseRoute . '/' . trim($pattern, '/');
        $pattern = $this->baseRoute ? rtrim($pattern, '/') : $pattern;
        foreach (explode('|', $methods) as $method) {
            $this->afterFilter[$method][] = array(
                'pattern' => $pattern,
                'filter' => $filter
            );
        }
    }

    /**
     * Store a route and a handling function to be executed when accessed using one of the specified methods
     *
     * @param string $methods Allowed methods, | delimited
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $filter The handling function to be executed
     */
    public function match($methods, $pattern, $filter)
    {
        $pattern = $this->baseRoute . '/' . trim($pattern, '/');
        $pattern = $this->baseRoute ? rtrim($pattern, '/') : $pattern;
        foreach (explode('|', $methods) as $method) {
            $this->routes[$method][] = array(
                'pattern' => $pattern,
                'filter' => $filter
            );
        }
        //缓存路由
        if (config('http.routes_cache')) {
            cache('routes', $this->routes);
        }
    }

    /*
     * Shorthand for a route accessed using all method
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $filter The handling function to be executed
     */
    public function all($pattern, $filter)
    {
        $this->match('GET|POST|PUT|DELETE|OPTIONS|PATCH|HEAD', $pattern, $filter);
    }

    /*
     * Shorthand for a route accessed using any method
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $filter The handling function to be executed
     */
    public function any($pattern, $filter)
    {
        $this->match('GET|POST', $pattern, $filter);
    }

    /**
     * Shorthand for a route accessed using GET
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $filter The handling function to be executed
     */
    public function get($pattern, $filter)
    {
        $this->match('GET', $pattern, $filter);
    }

    /**
     * Shorthand for a route accessed using POST
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $filter The handling function to be executed
     */
    public function post($pattern, $filter)
    {
        $this->match('POST', $pattern, $filter);
    }

    /**
     * Shorthand for a route accessed using PATCH
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $filter The handling function to be executed
     */
    public function patch($pattern, $filter)
    {
        $this->match('PATCH', $pattern, $filter);
    }

    /**
     * Shorthand for a route accessed using DELETE
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $filter The handling function to be executed
     */
    public function delete($pattern, $filter)
    {
        $this->match('DELETE', $pattern, $filter);
    }

    /**
     * Shorthand for a route accessed using PUT
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $filter The handling function to be executed
     */
    public function put($pattern, $filter)
    {
        $this->match('PUT', $pattern, $filter);
    }

    /**
     * Shorthand for a route accessed using OPTIONS
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $filter The handling function to be executed
     */
    public function options($pattern, $filter)
    {
        $this->match('OPTIONS', $pattern, $filter);
    }

    /**
     * groups a collection of callables onto a base route
     *
     * @param string $baseRoute The route subpattern to group the callables on
     * @param callable $filter The callabled to be called
     */
    public function group($baseRoute, $callback)
    {
        // Track current baseRoute
        $currentBaseRoute = $this->baseRoute;
        // Build new baseRoute string
        $this->baseRoute .= $baseRoute;
        // Call the callable
        call_user_func($callback);
        // Restore original baseRoute
        $this->baseRoute = $currentBaseRoute;
    }

    /**
     * Execute the router: Loop all defined before filters and routes, and execute the handling function if a match was found
     *
     * @param object|callable $callback Function to be executed after a matching route was handled (= after router filter)
     * @return bool
     */
    public function dispatch($callback = null)
    {
        // Define which method we need to handle
        $this->method = $this->getRequestMethod();
        // Handle all before filters
        if (isset($this->beforeFilter[$this->method])) {
            $this->handle($this->beforeFilter[$this->method]);
        }
        //设置路由缓存
        if (config('http.routes_cache') && ($routes = cache('routes'))) {
            $this->routes = $routes;
        }
        // Handle all routes
        $numHandled = 0;
        if (isset($this->routes[$this->method])) {
            $numHandled = $this->handle($this->routes[$this->method], false);
        }
        // If no route was handled, trigger the 404 (if any)
        if ($numHandled === 0) {
            if ($this->notFound && is_callable($this->notFound)) {
                call_user_func($this->notFound);
            } else {
                header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            }
        } else { // If a route was handled, perform the finish callback (if any)
            if (isset($this->afterFilter[$this->method])) { // Handle all after filters
                $this->handle($this->afterFilter[$this->method]);
            }
            if ($callback) {
                $callback();
            }
        }
        // If it originally was a HEAD request, clean up after ourselves by emptying the output buffer
        if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
            ob_end_clean();
        }

        // Return true if a route was handled, false otherwise
        if ($numHandled === 0) {
            return false;
        }
        return true;
    }

    /**
     * Handle a a set of routes: if a match is found, execute the relating handling function
     * @param array $routes Collection of route patterns and their handling functions
     * @param boolean $quitAfterRun Does the handle function need to quit after one route was matched?
     * @return int The number of routes handled
     */
    private function handle($routes, $quitAfterRun = false)
    {
        // Counter to keep track of the number of routes we've handled
        $numHandled = 0;
        // The current page URL
        $uri = $this->getCurrentUri();
        // Loop all routes
        foreach ($routes as $route) {
            // we have a match!
            if (preg_match_all('#^' . $route['pattern'] . '$#', $uri, $matches, PREG_OFFSET_CAPTURE)) {
                // Rework matches to only contain the matches, not the orig string
                $matches = array_slice($matches, 1);
                // Extract the matched URL parameters (and only the parameters)
                $params = array_map(function ($match, $index) use ($matches) {
                    // We have a following parameter: take the substring from the current param position until the next one's position (thank you PREG_OFFSET_CAPTURE)
                    if (isset($matches[$index + 1]) && isset($matches[$index + 1][0]) && is_array($matches[$index + 1][0])) {
                        return trim(substr($match[0][0], 0, $matches[$index + 1][0][1] - $match[0][1]), '/');
                    } else { // We have no following parameters: return the whole lot
                        return (isset($match[0][0]) ? trim($match[0][0], '/') : null);
                    }
                }, $matches, array_keys($matches));
                // 处理过滤器
                if (is_array($route['filter'])) {
                    foreach ($route['filter'] as $filter) {
                        $filter = Config::get('filter.' . $filter);
                        if (class_exists($filter)) {
                            call_user_func_array([(new $filter($this->app)), 'handle'], $params);
                        }
                    }
                } elseif ($route['filter'] instanceof Closure) {
                    call_user_func_array($route['filter'], $params);
                } else {
                    //设置控制器与方法
                    $router = explode('/', $route['filter']);
                    //模块
                    define('MODULE', array_shift($router));
                    //动作
                    define('CONTROLLER', str_replace(' ', '', ucwords(str_replace('-', ' ', array_shift($router)))));
                    //方法
                    define('ACTION', array_shift($router));
                    //基本路由
                    define('GROUP', MODULE);

                    $class = MODULE . '\\' . CONTROLLER;

                    //控制器不存在
                    if (!class_exists($class)) {
                        throw new Exception("{$class} 不存在");
                    }
                    $controller = new $class($this->app);
                    //执行动作
                    try {
                        $action = new ReflectionMethod($controller, ACTION);
                        if ($action->isPublic()) {
                            //控制器前置钩子
                            $this->app['Hook']->listen('begin');
                            //执行动作
                            call_user_func_array([$controller, ACTION], $params);
                        } else {
                            throw new \ReflectionException('方法不存在');
                        }
                    } catch (\ReflectionException $e) {
                        $action = new ReflectionMethod($controller, '__call');
                        $action->invokeArgs($controller, [ACTION, '']);
                    }
                }
                $numHandled++;
                // If we need to quit, then quit
                if ($quitAfterRun) {
                    break;
                }
            }
        }
        // Return the number of routes handled
        return $numHandled;
    }

    /**
     * Set the 404 handling function
     * @param object|callable $callback The function to be executed
     */
    public function set404($callback)
    {
        $this->notFound = $callback;
    }

    /**
     * Get all request headers
     * @return array The request headers
     */
    public function getRequestHeaders()
    {
        // getallheaders available, use that
        if (function_exists('getallheaders')) {
            return getallheaders();
        }
        // getallheaders not available: manually extract 'm
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if ((substr($name, 0, 5) == 'HTTP_') || ($name == 'CONTENT_TYPE') || ($name == 'CONTENT_LENGTH')) {
                $headers[str_replace(array(' ', 'Http'), array('-', 'HTTP'), ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    /**
     * Get the request method used, taking overrides into account
     * @return string The Request method to handle
     */
    public function getRequestMethod()
    {
        // Take the method as found in $_SERVER
        $method = $_SERVER['REQUEST_METHOD'];
        // If it's a HEAD request override it to being GET and prevent any output, as per HTTP Specification
        // @url http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4
        if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
            ob_start();
            $method = 'GET';
        } elseif ($_SERVER['REQUEST_METHOD'] == 'POST') { // If it's a POST request, check for a method override header
            $headers = $this->getRequestHeaders();
            if (isset($headers['X-HTTP-Method-Override']) && in_array($headers['X-HTTP-Method-Override'], array('PUT', 'DELETE', 'PATCH'))) {
                $method = $headers['X-HTTP-Method-Override'];
            }
        }
        return $method;
    }

    /**
     * Define the current relative URI
     * @return string
     */
    public function getCurrentUri()
    {
        // Get the current Request URI and remove rewrite basePath from it (= allows one to dispatch the router in a subfolder)
        $basePath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)) . '/';
        $uri = substr($_SERVER['REQUEST_URI'], strlen($basePath));
        // Don't take query params into account on the URL
        if (strstr($uri, '?')) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }
        // Remove trailing slash + enforce a slash at the start
        $uri = '/' . trim($uri, '/');
        return $uri;
    }
}