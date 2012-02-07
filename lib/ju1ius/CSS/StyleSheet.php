<?php
namespace ju1ius\CSS;

/**
 * Represents a CSS StyleSheet
 * @package CSS
 **/
class StyleSheet implements Serializable
{
  private
    $href,
    $mediaList,
    $ruleList,
    $charset;

  public function __construct(RuleList $ruleList=null, $charset="utf-8")
  {
    if($ruleList === null)
    {
      $this->ruleList = new RuleList(); 
    }
    $this->charset = $charset;
  }

  public function getHref()
  {
    return $this->href;
  }
  public function setHref($href)
  {
    $this->href = $href;
  }

  public function getCharset()
  {
    return $this->charset;
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
