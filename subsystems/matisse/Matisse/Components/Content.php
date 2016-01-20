<?php
namespace Selenia\Matisse\Components;

use Selenia\Matisse\Components\Base\Component;
use Selenia\Matisse\Exceptions\ComponentException;
use Selenia\Matisse\Properties\Base\ComponentProperties;
use Selenia\Matisse\Properties\TypeSystem\type;

class ContentProperties extends ComponentProperties
{
  /**
   * The block name. If you set it via this property, the new content will be appended to the saved content (if any).
   *
   * @var string
   */
  public $appendTo = type::id;
  /**
   * Modifies the saved content only if none is set yet.
   *
   * @var bool
   */
  public $byDefault = false;
  /**
   * The block name. If you set it via this property, the new content will overwrite the saved content (if any).
   *
   * @var string
   */
  public $of = type::id;
  /**
   * The block name. If you set it via this property, the new content will be prepended to the saved content (if any).
   *
   * @var string
   */
  public $prependTo = type::id;
  /**
   * Alternative to setting the content via the tag's content; useful for short strings.
   * If set, the tag's content is ignored.
   *
   * @var string
   */
  public $value = type::string;
}

/**
 * The Content component allows you to save HTML on named memory containers, and yield it later at specific
 * locations.
 *
 * <p>Ex:
 * <p>
 * ```HTML
 *   <Content of="header">
 *     <h1>A Header</h1>
 *   </Content>
 *
 *   {!! #header !!}
 * ```
 * <p>You can also use the `{{ #name }}` syntax, but note that it escapes its output, which is, usually, not what
 * you intend, as the content being output is (or should be) already safe HTML.
 */
class Content extends Component
{
  protected static $propertiesClass = ContentProperties::class;

  public $allowsChildren = true;
  /** @var ContentProperties */
  public $props;

  /**
   * Adds (or replaces) the content of the `value` property (or the component's content) to a named block on the page.
   */
  protected function render ()
  {
    $prop    = $this->props;
    $content = exists ($prop->value) ? $prop->value : $this->getChildren();

    if (exists ($name = $prop->of)) {
      if ($prop->byDefault && $this->context->hasBlock ($name))
        return;
      $this->context->setBlock ($name, $content);
    }
    elseif (exists ($name = $prop->appendTo)) {
      if ($prop->byDefault && $this->context->hasBlock ($name))
        return;
      $this->context->appendToBlock ($name, $content);
    }
    elseif (exists ($name = $prop->prependTo)) {
      if ($prop->byDefault && $this->context->hasBlock ($name))
        return;
      $this->context->prependToBlock ($name, $content);
    }
    else throw new ComponentException($this,
      "One of these properties must be set:<p><kbd>of | appendTo | prependTo</kbd>");
  }

}
