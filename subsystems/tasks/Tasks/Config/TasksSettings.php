<?php
namespace Electro\Tasks\Config;

use Electro\Interfaces\AssignableInterface;
use Electro\Traits\ConfigurationTrait;

/**
 * Configuration settings for the Tasks subsystem.
 *
 * @method $this|string scaffoldsPath  (string $v = null) The path of scaffolds's dir, relative to the project's dir.
 */
class TasksSettings implements AssignableInterface
{
  use ConfigurationTrait;

  private $scaffoldsPath;

  public function __construct ()
  {
    $this->scaffoldsPath = updir (__DIR__, 2) . '/scaffolds';
  }

}
