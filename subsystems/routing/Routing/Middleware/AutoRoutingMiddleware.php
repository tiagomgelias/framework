<?php
namespace Selenia\Routing\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Selenia\Exceptions\Fatal\FileNotFoundException;
use Selenia\Http\Components\PageComponent;
use Selenia\Interfaces\Http\RequestHandlerInterface;
use Selenia\Interfaces\InjectorInterface;

/**
 * It allows a designer to rapidly prototype the application by automatically providing routing for URLs starting with
 * a specific prefix, which will be routed to a generic controller that will load the corresponding view from the
 * registered view directories, from a relative file path derived from the URL.
 *
 * <p>**This is NOT recommended for production!**
 *
 * <p>You should register this middleware right before the router, but only if `debugMode = false`.
 */
class AutoRoutingMiddleware implements RequestHandlerInterface
{
  /**
   * @var InjectorInterface
   */
  private $injector;

  public function __construct (InjectorInterface $injector)
  {
    $this->injector = $injector;
  }

  function __invoke (ServerRequestInterface $request, ResponseInterface $response, callable $next)
  {
    $URL = $request->getAttribute ('virtualUri');
    inspect ($URL);
    if ($URL == '') $URL = '/';
    if (substr ($URL, -1) == '/') $URL = $URL . 'index';

    /** @var PageComponent $page */
    $page              = $this->injector->make (PageComponent::class);
    $page->templateUrl = "$URL.html";

    try {
      return $page ($request, $response, $next);
    }
    catch (FileNotFoundException $e) {
      return $next ();
    }
  }

}