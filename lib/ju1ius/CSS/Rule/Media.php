<?php
namespace ju1ius\CSS\Rule;

use ju1ius\CSS\Rule;
use ju1ius\CSS\RuleList;
use ju1ius\CSS\MediaList;

/**
 * Represents an @media rule
 * @package CSS
 * @subpackage Rule
 **/
class Media extends Rule
{
  private $mediaList;
  private $ruleList;
  private $parentStyleSheet;

  public function __construct(MediaList $mediaList, RuleList $ruleList=null)
  {
    if($ruleList === null)
    {
      $ruleList = new RuleList();
    }
    $this->mediaList = $mediaList;
    $this->ruleList = $ruleList;
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
		$indent = '';
		$nl = ' ';
		if(isset($options['indent_level']))
		{
			$indent = str_repeat($options['indent_char'], $options['indent_level']);
			$options['indent_level']++;
			$nl = "\n";
		}
		return $indent . '@media ' . $this->mediaList->getCssText()
			. '{' . $nl
			. parent::getCssText($options)
			. $nl . $indent . '}';
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
