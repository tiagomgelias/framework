<?php
namespace Selenia\Matisse\Parser;

/**
 * Represents a parsed tag.
 * > The class is used internally by the parser.
 */
class Tag
{
  /**
   * Buffers the attribute name, so that if no value is specified, that name is not written.
   */
  public $attrName = '';
  /**
   * Attribute separator.
   */
  public $attrSep;
  /**
   * Indicates if the tag's content is being defined.
   */
  public $isContentSet = false;
  /**
   * Signals if the attribute has yet no values defined for it.
   */
  public $isFirstValue = false;
  /**
   * The tag name.
   * @var string
   */
  public $name;
}