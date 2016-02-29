<?php

namespace Mlntn\Console\Commands;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class RouteList extends Command
{
  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'route:list';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'List all registered routes';

  /**
   * The router instance.
   *
   * @var \Illuminate\Routing\Router
   */
  protected $router;

  /**
   * An array of all the registered routes.
   *
   * @var \Illuminate\Routing\RouteCollection
   */
  protected $routes;

  /**
   * The table headers for the command.
   *
   * @var array
   */
  protected $headers = ['Method', 'URI', 'Name', 'Action', 'Middleware'];

  /**
   * Create a new route command instance.
   */
  public function __construct()
  {
    parent::__construct();

    $this->routes = app()->getRoutes();
  }

  /**
   * Execute the console command.
   *
   * @return void
   */
  public function fire()
  {
    if (count($this->routes) == 0) {
      return $this->error("Your application doesn't have any routes.");
    }

    $this->displayRoutes($this->getRoutes());
  }

  /**
   * Compile the routes into a displayable format.
   *
   * @return array
   */
  protected function getRoutes()
  {
    $results = [];

    foreach ($this->routes as $route) {
      $results[] = $this->getRouteInformation(collect($route));
    }

    if ($sort = $this->option('sort')) {
      $results = Arr::sort($results, function ($value) use ($sort) {
        return $value[$sort];
      });
    }

    if ($this->option('reverse')) {
      $results = array_reverse($results);
    }

    return array_filter($results);
  }

  /**
   * Get the route information for a given route.
   *
   * @param  Collection $route
   * @return array
   */
  protected function getRouteInformation($route)
  {
    $action = collect($route->get('action'));
    return $this->filterRoute([
      'method' => $route->get('method'),
      'uri'    => $route->get('uri'),
      'name'   => $action->get('as', ''),
      'action' => $action->get('uses'),
      'middleware' => $this->getMiddleware($route),
    ]);
  }

  /**
   * Display the route information on the console.
   *
   * @param  array  $routes
   * @return void
   */
  protected function displayRoutes(array $routes)
  {
    $this->table($this->headers, $routes);
  }

  /**
   * Get before filters.
   *
   * @param  array  $route
   * @return string
   */
  protected function getMiddleware($route)
  {
    return implode(',', $route['action']['middleware']);
  }

  /**
   * Get the middlewares for the given controller instance and method.
   *
   * @param  \Illuminate\Routing\Controller  $controller
   * @param  string  $method
   * @return array
   */
  protected function getControllerMiddlewareFromInstance($controller, $method)
  {
    $middleware = $this->router->getMiddleware();

    $results = [];

    foreach ($controller->getMiddleware() as $name => $options) {
      if (! $this->methodExcludedByOptions($method, $options)) {
        $results[] = Arr::get($middleware, $name, $name);
      }
    }

    return $results;
  }

  /**
   * Determine if the given options exclude a particular method.
   *
   * @param  string  $method
   * @param  array  $options
   * @return bool
   */
  protected function methodExcludedByOptions($method, array $options)
  {
    return (! empty($options['only']) && ! in_array($method, (array) $options['only'])) ||
    (! empty($options['except']) && in_array($method, (array) $options['except']));
  }

  /**
   * Filter the route by URI and / or name.
   *
   * @param  array  $route
   * @return array|null
   */
  protected function filterRoute(array $route)
  {
    if (($this->option('name') && ! Str::contains($route['name'], $this->option('name'))) ||
      $this->option('path') && ! Str::contains($route['uri'], $this->option('path')) ||
      $this->option('method') && ! Str::contains($route['method'], strtoupper($this->option('method')))) {
      return;
    }

    return $route;
  }

  /**
   * Get the console command options.
   *
   * @return array
   */
  protected function getOptions()
  {
    return [
      ['method', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by method.'],

      ['name', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by name.'],

      ['path', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by path.'],

      ['reverse', 'r', InputOption::VALUE_NONE, 'Reverse the ordering of the routes.'],

      ['sort', null, InputOption::VALUE_OPTIONAL, 'The column (method, uri, name, action, middleware) to sort by.', 'uri'],
    ];
  }
}