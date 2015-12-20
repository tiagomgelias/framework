<?php
namespace Selenia\Matisse\Attributes\Base;

use Selenia\Matisse\Attributes\DSL\is;
use Selenia\Matisse\Attributes\DSL\type;
use Selenia\Matisse\Components\Base\Component;
use Selenia\Matisse\Components\Internal\Parameter;
use Selenia\Matisse\Components\Internal\Text;
use Selenia\Matisse\Exceptions\ComponentException;

class ComponentAttributes
{
  protected static $NEVER_DIRTY = [];
  /**
   * Default values for each attribute.
   * <p>Map of property name => mixed
   * @var array
   */
  static protected $_defaults = [];
  /**
   * Enumerations for each attributes.
   * <p>Map of property name => array
   * @var array[]
   */
  static protected $_enums = [];
  /**
   * Mandatory attributes.
   * <p>Map of property name => true
   * @var array
   */
  static protected $_required = [];
  /**
   * The types of each attribute.
   * <p>Map of property name => type::XXX
   * @var string[]
   */
  static protected $_types         = [];

  /**
   * Set to `true` when one or more attributes have been changed from their default values.
   * @var bool
   */
  public $_modified = false;

  /**
   * The component that owns these attributes.
   * @var Component
   */
  protected $component;

  public function __construct ($component)
  {
    $this->component = $component;
    if (!static::$_types)
      $this->initMetadata ();
    foreach (static::$_defaults as $prop => $val)
      $this->$prop = $val;
  }

  public static function validateScalar ($type, $v)
  {
    if (!type::validate($type, $v))
      throw new \InvalidArgumentException(sprintf(
        "A value of PHP type <b>%s</b> is not valid for an attribute/parameter of type <b>%s</b>.",
        typeOf($v), type::getNameOf($type)
      ));

    if (isset($v) && $v !== '') {
      switch ($type) {

        case type::bool:
          return type::toBoolean ($v);

        case type::id:
          if (preg_match ('#^\w+$#', $v) === false)
            throw new \InvalidArgumentException(
              "<b>$v</b> (PHP type " . gettype ($v) . ") is not a valid <b>identifier</b>.");
          return $v;

        case type::number:
          if (is_numeric ($v)) return intval ($v);
          throw new \InvalidArgumentException(
            "<b>$v</b> (PHP type " . gettype ($v) . ") is not a valid <b>number</b>.");

        case type::text:
          if (!is_scalar ($v))
            throw new \InvalidArgumentException(
              "A value of PHP type <b>" . gettype ($v) . "</b> is not valid for a <b>text</b> attribute/parameter.");
          if (!is_string ($v))
            return $v; //for mixed value attributes
          $v = preg_replace ('#<br ?/?>$|<p>&nbsp;</p>#', '', $v);
          $v = preg_replace ('#&nbsp;</p>#', '</p>', $v);
          return $v;

        case type::data:
          if ($v instanceof \Iterator)
            return $v;
          if ($v instanceof \IteratorAggregate)
            return $v->getIterator ();
          if (is_string ($v) && strpos ($v, '{') !== false)
            return $v;
          if (is_array ($v) || is_object ($v))
            return $v;
          throw new \InvalidArgumentException(
            (is_scalar ($v) ? "The value <b>$v</b>" : 'A value')
            . " of PHP type <b>" . gettype ($v) . "</b> is not valid for a <b>data</b> attribute/parameter.");
      }
      if (isset(self::$TYPE_NAMES[$type]))
        throw new \InvalidArgumentException("Invalid attempt to validate an attribute/parameter value of type <b>" .
                                            self::$TYPE_NAMES[$type] . "<b> with code $type.");
    }
    return null;
  }

  public function __get ($name)
  {
    throw new ComponentException($this->component, "Can't read non existing attribute <b>$name</b>.");
  }

  public function __set ($name, $value)
  {
    throw new ComponentException($this->component, "Can't set non existing attribute <b>$name</b>.");
  }

  public function apply (array $attrs)
  {
    foreach ($attrs as $k => $v)
      $this->set ($k, $v);
  }

  /**
   * Checks if the component supports the given attribute.
   *
   * @param string $name
   * @param bool   $asSubtag When true, the attribute MUST be able to be specified in subtag form.
   *                         When false, the attribute can be either a tag attribute or a subtag.
   * @return bool
   */
  public function defines ($name, $asSubtag = false)
  {
    if ($asSubtag) return $this->isSubtag ($name);
    return isset (static::$_types[$name]);
  }

  public function get ($name, $default = null)
  {
    return property ($this, $name, $default);
  }

  public function getAll ()
  {
    $p = $this->getAttributeNames ();
    $r = [];
    foreach ($p as $prop)
      $r[$prop] = $this->{$prop};
    return $r;
  }

  public function getAttributeNames ()
  {
    return array_keys (static::$_types);
  }

  public function getAttributesOfType ($type)
  {
    $result = [];
    $names  = $this->getAttributeNames ();
    if (isset($names))
      foreach ($names as $name)
        if ($this->getTypeOf ($name) == $type)
          $result[$name] = $this->get ($name);
    return $result;
  }

  public function getEnumOf ($name)
  {
    return get (static::$_enums, $name, false);
  }

  public function getScalar ($name)
  {
    return static::validateScalar ($this->getTypeOf ($name), $this->get ($name));
  }

  public function getTypeNameOf ($name)
  {
    $t = $this->getTypeOf ($name);
    if (!is_null ($t))
      return static::$TYPE_NAMES[$t];
    return static::$TYPE_NAMES[type::text];
  }

  public function getTypeOf ($name)
  {
    return static::$_types[$name];
  }

  public function isEnum ($name)
  {
    return isset(static::$_enums[$name]);
  }

  public function isScalar ($name)
  {
    $type = $this->getTypeOf ($name);
    return $type == type::bool || $type == type::id || $type == type::number ||
           $type == type::text;
  }

  public function isSubtag ($name)
  {
    $type = $this->getTypeOf ($name);
    switch ($type) {
      case type::parameter:
      case type::multipleParams:
      case type::metadata:
        return true;
    }
    return false;
  }

  public function set ($name, $value)
  {
    if (!$this->defines ($name))
      throw new ComponentException($this->component, "Invalid attribute <b>$name</b> specified.");
    if ($this->isScalar ($name))
      $this->setScalar ($name, $value);
    else switch ($type = $this->getTypeOf ($name)) {
      case type::parameter:
        $ctx  = $this->component->context;
        $text = Text::from ($ctx, $value);
        if (isset($this->$name))
          $this->$name->addChild ($text);
        else {
          $param = new Parameter ($ctx, $name, $type);
          $param->attachTo ($this->component);
          $param->addChild ($text);
          $this->$name = $param;
        }
        $this->_modified = true;
        break;
      default:
        $this->$name     = $value;
        $this->_modified = true;
    }
  }

  public function setComponent (Component $owner)
  {
    $this->component = $owner;
    $attrs           = $this->getAttributesOfType (type::parameter);
    foreach ($attrs as $name => $value)
      if (!is_null ($value)) {
        /** @var Component $c */
        $c = clone $value;
        $c->attachTo ($owner);
        $this->$name = $c;
      }
    $attrs = $this->getAttributesOfType (type::multipleParams);
    foreach ($attrs as $name => $values)
      if (!empty($values))
        $this->$name = Component::cloneComponents ($values, $owner);
  }

  public function setScalar ($name, $v)
  {
    if ($this->isEnum ($name)) {
      $enum = $this->getEnumOf ($name);
      if (array_search ($v, $enum) === false) {
        $list = implode ('</b>, <b>', $enum);
        throw new ComponentException($this->component,
          "Invalid value for attribute/parameter <b>$name</b>.\nExpected: <b>$list</b>.");
      }
    }
    $newV = static::validateScalar ($this->getTypeOf ($name), $v);
    if ($this->$name !== $newV) {
      $this->$name = $newV;
      if (!isset(static::$NEVER_DIRTY[$name]))
        $this->_modified = true;
    }
  }

  private function initMetadata ()
  {
    $class = new \ReflectionClass($this);
    foreach ($class->getProperties (\ReflectionProperty::IS_PUBLIC) as $property) {
      $name  = $property->name;
      $value = $this->$name;
      if (!is_array ($value))
        $value = [$value];
      $it = new \ArrayIterator($value);
      while ($it->valid ()) {
        $v = $it->current ();
        if (is_string ($v)) {
          if ($v !== '' && $v[0] == '§') // It's metadata.
            switch ($v) {

              case is::enum:
                $it->next ();
                if ($it->valid ()) {
                  $e = $it->current ();
                  if (is_array ($e))
                    static::$_enums[$name] = $e;
                  else throw new ComponentException($this, "Invalid enumeration for the <kbd>$name</kbd> attribute");
                }
                else throw new ComponentException($this,
                  "Missing argument for the <kbd>$name</kbd> attribute's enumeration");
                break;

              case is::required:
                static::$_required[$name] = true;
                break;

              case type::binding:
                static::$_types[$name]    = $v;
                static::$_defaults[$name] = '';
                break;

              case type::bool:
                static::$_types[$name]    = $v;
                static::$_defaults[$name] = false;
                break;

              case type::data:
                static::$_types[$name]    = $v;
                static::$_defaults[$name] = null;
                break;

              case type::id:
                static::$_types[$name]    = $v;
                static::$_defaults[$name] = '';
                break;

              case type::metadata:
                static::$_types[$name]    = $v;
                static::$_defaults[$name] = null;
                break;

              case type::multipleParams:
                static::$_types[$name]    = $v;
                static::$_defaults[$name] = null;
                break;

              case type::number:
                static::$_types[$name]    = $v;
                static::$_defaults[$name] = 0;
                break;

              case type::parameter:
                static::$_types[$name]    = $v;
                static::$_defaults[$name] = null;
                break;

              case type::text:
                static::$_types[$name]    = $v;
                static::$_defaults[$name] = '';
                break;

              default:
                throw new ComponentException($this, "Invalid type declaration for the <kbd>$name</kbd> attribute");
            }
          else {
            static::$_types[$name]    = type::text;
            static::$_defaults[$name] = $v;
          }
        }
        else {
          // The type is defined implicitly via the default value.

          static::$_defaults[$name] = $v;
          switch (gettype ($v)) {
            case 'boolean':
              static::$_types[$name] = type::bool;
              break;
            case 'integer':
            case 'double':
              static::$_types[$name] = type::number;
              break;
            case 'NULL':
              break;
          }
        }
        $it->next ();
      }
      if (!isset(static::$_types[$name]))
        throw new ComponentException($this, "Missing type declaration for the <kbd>$name</kbd> attribute");
    }
  }

}
