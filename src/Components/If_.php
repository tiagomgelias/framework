<?php
namespace Selenia\Matisse\Components;
use Selenia\Matisse\AttributeType;
use Selenia\Matisse\Component;
use Selenia\Matisse\Attributes\ComponentAttributes;
use Selenia\Matisse\IAttributes;

class IfAttributes extends ComponentAttributes
{
  public $the;
  public $is;
  public $isSet  = false;
  public $isTrue = false;
  public $not    = false;
  public $matches;
  public $case;          //note: doesn't work with databinding
  public $else;

  protected function typeof_the () { return AttributeType::TEXT; }

  protected function typeof_is () { return AttributeType::TEXT; }

  protected function typeof_isSet () { return AttributeType::BOOL; }

  protected function typeof_isTrue () { return AttributeType::BOOL; }

  protected function typeof_not () { return AttributeType::BOOL; }

  protected function typeof_matches () { return AttributeType::TEXT; }

  protected function typeof_case () { return AttributeType::PARAMS; }

  protected function typeof_else () { return AttributeType::SRC; }
}

/**
 * Rendes content blocks conditionally.
 *
 * ##### Syntax:
 * ```
 * <If the="value1" is="value2">
 *   content if true
 *   <Else> content if false </Else>
 * </If>
 *
 * <If is="value"> content if value is truthy </If>
 *
 * <If not is="value"> content if value is falsy </If>
 *
 * <If the="value" isTrue> content if value is truthy </If>
 *
 * <If the="value" not isTrue> content if value is falsy </If>
 *
 * <If the="value" isSet> content if value is different from null and the empty string </If>
 *
 * <If the="value" not isSet> content if value is equal to null or an empty string </If>
 *
 * <If the="value" matches="regexp"> content if value matches the regular expression </If>
 *
 * <If the="value" not matches="regexp"> content if value doesn't matche the regular expression </If>
 *
 * <If the="value">
 *   <p:case is="value1"> content if value == value1 </p:case>
 *   ...
 *   <p:case is="valueN"> content if value == valueN </p:case>
 *   <Else> content if no match </Else>
 * </If>
 * ```
 */
class If_ extends Component implements IAttributes
{
  public $allowsChildren = true;

  /**
   * Returns the component's attributes.
   * @return IfAttributes
   */
  public function attrs ()
  {
    return $this->attrsObj;
  }

  /**
   * Creates an instance of the component's attributes.
   * @return IfAttributes
   */
  public function newAttributes ()
  {
    return new IfAttributes($this);
  }

  protected function render ()
  {
    $attr = $this->attrs ();

    $v   = $attr->get ('the');
    $is  = $attr->get ('is');
    $not = $attr->not;

    if (isset($is)) {
      if (!isset($v)) {
        $is = strToBool ($is);
        $v  = true;
      }
      if ($v === $is xor $not)
        $this->renderChildren ();
      else $this->renderParameter ('else');
      return;
    }

    if ($attr->isSet) {
      if ((isset($v) && $v != '') xor $not)
        $this->renderChildren ();
      else $this->renderParameter ('else');
      return;
    }

    if ($attr->isTrue) {
      if (strToBool ($v) xor $not)
        $this->renderChildren ();
      else $this->renderParameter ('else');
      return;
    }

    if (isset($attr->matches)) {
      if (preg_match ("%$attr->matches%", $v) xor $not)
        $this->renderChildren ();
      else $this->renderParameter ('else');
      return;
    }

    if (isset($attr->case)) {
      foreach ($attr->case as $param) {
//        $param->databind ();
        if ($v == $param->attrs ()->is) {
//          $children = self::cloneComponents ($param->children);
//          $this->setChildren ($children);
          $this->setChildren ($param->children);
          return;
        }
      }
      $this->renderParameter ('else');
      return;
    }

    if ($v)
      $this->renderChildren ();
    else $this->renderParameter ('else');
  }

}
