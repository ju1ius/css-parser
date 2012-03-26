<?php
namespace ju1ius\Css\Iterator;

/**
 * 
 */
class SelectorIterator implements \Iterator
{
  private
    $selectors = array();
  
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

  public static function getSelectorsForObject($object, $type_filter=null)
  {
    $selectors = array();

    if(method_exists($obj, 'getSelectorList')) {
      foreach($obj->getSelectorList()->getItems() as $selector) {
        $selectors[] = $selector;
      }
    } else if(method_exists($obj, 'getRuleList')) {
      foreach($obj->getRuleList()->getRules() as $rule) {
        $selectors = array_merge(
          $selectors,
          self::getSelectorsForObject($rule, $type)
        );
      }
    }

    if($type) {
      return array_filter($selectors, function($item) use($type)
      {
        return $item instanceof $type;
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
