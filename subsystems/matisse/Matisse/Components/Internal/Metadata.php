<?php
namespace Selenia\Matisse\Components\Internal;

use Selenia\Matisse\Components\Base\Component;
use Selenia\Matisse\Parser\Context;
use Selenia\Matisse\Properties\Base\MetadataProperties;
use Selenia\Matisse\Properties\Types\type;

/**
 * A complex property that is expressed as a subtag.
 *
 * > <p>**Note:** rendering a metadata component **does not** automatically render its children.
 * > <p>Otherwise problems would occur when rendering metadata children, as some of those components may also be
 * metadata.
 * > <p>The content of metadata components, if it is rendered at all, it **must always** be rendered manually on the
 * owner component's `render()`.
 */
class Metadata extends Component
{
  protected static $propertiesClass = MetadataProperties::class;

  public $allowsChildren = true;
  /**
   * The data type of the property for which this component holds the value.
   * @var number
   */
  public $type;
  /**
   * The parameter's scalar value.
   * Note that data sources are also considered scalar values.
   * @var mixed
   */
  public $value;

  public function __construct (Context $context, $tagName, $type, array $attributes = null)
  {
    parent::__construct ($context, $attributes);
    $this->type = $type;
    $this->setTagName ($tagName);
  }

  public function getValue ()
  {
    if ($this->type == type::content)
      return $this->getChildren ();
    return $this->value;
  }

  public function isScalar ()
  {
    //Note that parameters are never of type TYPE_PARAMS.
    return $this->type != type::content;
  }

  public function parsed ()
  {
    $this->databind ();
  }

  /**
   * @return MetadataProperties
   */
  function props ()
  {
    return $this->props;
  }

  public function setScalar ($v)
  {
    $this->value = $this->props->validateScalar ($this->type, $v);
  }

}