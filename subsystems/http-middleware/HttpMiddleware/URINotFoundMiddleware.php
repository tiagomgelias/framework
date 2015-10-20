<?php
namespace Selenia\HttpMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Selenia\Application;
use Selenia\Exceptions\HttpException;
use Selenia\Interfaces\MiddlewareInterface;

/**
 *
 */
class URINotFoundMiddleware implements MiddlewareInterface
{
  private $app;

  function __construct (Application $app)
  {
    $this->app = $app;
  }

  function __invoke (ServerRequestInterface $request, ResponseInterface $response, callable $next)
  {
    throw new HttpException (404, 'Page not available',
      "The requested web address <kbd>{$request->getUri()->getPath()}</kbd> is not valid");
  }
}
