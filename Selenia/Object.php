<?php
namespace Selenia;

use Selenia\Exceptions\FlashMessageException;
use Selenia\Exceptions\FlashType;

abstract class Object
{
  public function __construct (array &$initializers = null)
  {
    if (is_null ($initializers)) return;
    $types = $this->getTypes ();
    if (isset($types)) {
      foreach ($initializers as $k => &$v)
        $this->set ($k, $v, $types);
    }
    else foreach ($initializers as $k => $v)
      $this->{$k} = $v;
  }

  public function set ($k, &$v, &$types = null)
  {
    if (is_null ($types))
      $types = $this->getTypes ();
    if (isset($types[$k])) {
      if ($types[$k] !== '' && gettype ($v) != $types[$k])
        throw new FlashMessageException("Error setting property <b>$k</b> on a <b>" . get_class ($this) .
                                "</b> instance: expected <b>" . $types[$k] . "</b> but got <b>" . gettype ($v) . "</b>",
          FlashType::FATAL);
      $m = "set_$k";
      if (method_exists($this, $m))
        $this->$m ($v);
      else $this->{$k} = $v;
    }
    else throw new FlashMessageException("Can't set non existing property <b>$k</b> on a <b>" . get_class ($this) .
                                 "</b> instance.", FlashType::FATAL);
  }

  /**
   * Returns an array with keys for each class property name and values that define the property data type.
   * @return array
   */
  public abstract function getTypes ();

}
