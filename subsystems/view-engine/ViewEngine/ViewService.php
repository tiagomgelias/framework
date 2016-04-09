<?php
namespace Selenia\ViewEngine;

use Selenia\Application;
use Selenia\Exceptions\Fatal\FileNotFoundException;
use Selenia\Exceptions\FatalException;
use Selenia\Interfaces\InjectorInterface;
use Selenia\Interfaces\Views\ViewServiceInterface;

class ViewService implements ViewServiceInterface
{
  /**
   * @var Application
   */
  private $app;
  /**
   * @var InjectorInterface
   */
  private $injector;
  /**
   * @var string[] A map of regular expression patterns to view engine class names.
   */
  private $patterns = [];

  function __construct (Application $app, InjectorInterface $injector)
  {
    $this->injector = $injector;
    $this->app      = $app;
  }

  function getEngine ($engineClass)
  {
    // The engine class may receive this insstance as a $view parameter on the constructor (optional).
    $engine = $this->injector->make ($engineClass, [':view' => $this]);
    return $engine;
  }

  function getEngineFromFileName ($fileName)
  {
    foreach ($this->patterns as $pattern => $class)
      if (preg_match ($pattern, $fileName))
        return $this->getEngine ($class);
    throw new FatalException ("None of the available view engines is capable of handling a file named <b>$fileName</b>.
<p>Make sure the file name has one of the supported file extensions or matches a known pattern.");
  }

  function loadFromFile ($path)
  {
    $engine = $this->getEngineFromFileName ($path);
    $src    = $this->loadViewTemplate ($path);
    return $this->loadFromString ($src, $engine);
  }

  function loadFromString ($src, $engineOrClass)
  {
    if (is_string ($engineOrClass))
      $engineOrClass = $this->getEngine ($engineOrClass);
    // The injector is not used here. This service only returns instances of View.
    $view = new View($engineOrClass);
    $view->setSource ($src);
    return $view;
  }

  public function loadViewTemplate ($path)
  {
    return loadFile ($this->resolveTemplatePath ($path));
  }

  function register ($engineClass, $filePattern)
  {
    $this->patterns[$filePattern] = $engineClass;
    return $this;
  }

  public function resolveTemplatePath ($path, &$base = null)
  {
    $dirs = $this->app->viewsDirectories;
    foreach ($dirs as $base) {
      $p = "$base/$path";
      if (fileExists ($p))
        return $p;
    }
    $paths = implode ('', map ($dirs, function ($path) {
      return "<li><path>$path</path>";
    }));
    throw new FileNotFoundException($path, "<p>Search paths:<ul>$paths</ul>");
  }

}
