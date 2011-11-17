<?php
namespace CSS;

/**
 * Represents a CSS StyleSheet
 * @package CSS
 **/
class StyleSheet implements Serializable
{
  private $href;
  private $mediaList;
  private $ruleList;

  public function __construct(RuleList $ruleList=null)
  {
    if($ruleList == null)
    {
      $this->ruleList = new RuleList(); 
    }
  }

  public function getHref()
  {
    return $this->href;
  }
  public function setHref($href)
  {
    $this->href = $href;
  }

  public function getMediaList()
  {
    return $this->mediaList;
  }
  public function setMediaList(MediaList $mediaList)
  {
    $this->mediaList = $mediaList;
  }

  public function getRuleList()
  {
    return $this->ruleList;
  }
  public function setRuleList(RuleList $ruleList)
  {
    $this->ruleList = $ruleList;
  }

  public function getCssText($options=array())
  {
    return $this->ruleList->getCssText($options);
  }

  public function getFirstRule()
  {
    return $this->ruleList->getFirst();
  }

  public function getLastRule()
  {
    return $this->ruleList->getLast();
  }

  public function __call($method, $args)
  {
    if(method_exists($this->ruleList, $method))
    {
      return call_user_func_array(array($this->ruleList, $method), $args);
    }
  }

  public function __clone()
  {
    $this->mediaList = clone $this->mediaList;
    $this->ruleList = clone $this->ruleList;
  }

}
