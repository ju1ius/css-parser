<?php
namespace ju1ius\Css\Rule;

use ju1ius\Css\Rule;
use ju1ius\Css\RuleList;
use ju1ius\Css\MediaQueryList;

/**
 * Represents an @media rule
 **/
class Media extends Rule
{
    private $media_list;
    private $rule_list;
    private $parentStyleSheet;

    public function __construct(MediaQueryList $media_list, RuleList $rule_list=null)
    {
        if ($rule_list === null) {
            $rule_list = new RuleList();
        }
        $this->media_list = $media_list;
        $this->rule_list = $rule_list;
    }

    public function getMediaQueryList()
    {
        return $this->media_list;
    }

    public function setMediaQueryList(MediaQueryList $media_list)
    {
        $this->media_list = $media_list;
    }

    public function getRuleList()
    {
        return $this->rule_list;
    }

    public function setRuleList(RuleList $rule_list)
    {
        $this->rule_list = $rule_list;
    }

    public function getCssText($options=array())
    {
        $indent = '';
        $nl = ' ';
        if (isset($options['indent_level'])) {
            $indent = str_repeat($options['indent_char'], $options['indent_level']);
            $options['indent_level']++;
            $nl = "\n";
        }
        return $indent . '@media ' . $this->media_list->getCssText()
            . '{' . $nl
            . $this->rule_list->getCssText($options)
            . $nl . $indent . '}'
        ;
    }

    /*
    public function __call($method, $args)
    {
        if (method_exists($this->rule_list, $method)) {
            return call_user_func_array(array($this->rule_list, $method), $args);
        }
    }
    */

    public function __clone() {
        $this->media_list = clone $this->media_list;
        $this->rule_list = clone $this->rule_list;
    }

}
