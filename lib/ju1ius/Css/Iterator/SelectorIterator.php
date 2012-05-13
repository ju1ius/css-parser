<?php
namespace ju1ius\Css\Iterator;

use ju1ius\Css;

/**
 * Iterates over all selectors of a Css object (stylesheet, media rule...)
 */
class SelectorIterator implements \Iterator
{
  private
    $selectors = array();
  
  /**
   * @param Serializable $object A css serializable object
   * @param string|null  $type_filter A fully qualified classname to filter the selectors
   **/
  public function __construct($object, $type_filter=null)
  {
    $this->object = $object;
    $this->selectors = self::getSelectorsForObject($object, $type_filter);
  }

  public function getSelectors()
  {
    return $this->selectors;
  }
  public function getObject()
  {
    return $this->object;
  }

  private static function getSelectorsForObject($object, $type_filter=null)
  {
    $selectors = array();

    if(method_exists($object, 'getSelectorList')) {
      foreach($object->getSelectorList()->getItems() as $selector) {
        $selectors[] = $selector;
      }
    } else if(method_exists($object, 'getRuleList')) {
      foreach($object->getRuleList()->getRules() as $rule) {
        $selectors = array_merge(
          $selectors,
          self::getSelectorsForObject($rule, $type)
        );
      }
    }

    if($type_filter) {
      return array_filter($selectors, function($item) use($type_filter)
      {
        return $item instanceof $type_filter;
      });
    }
    return $selectors;
  }

  /* ----- Iterator Interface implementation ----- */

  public function rewind()
  {
    reset($this->selectors);
  }
  public function current()
  {
    return current($this->selectors);
  }
  public function key()
  {
    return key($this->selectors);
  }
  public function next()
  {
    return next($this->selectors);
  }
  public function valid()
  {
    $key = key($this->selectors);
    return $key !== null && $key !== false;
  }

}
