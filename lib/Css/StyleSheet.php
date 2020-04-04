<?php declare(strict_types=1);

namespace ju1ius\Css;

/**
 * Represents a Css StyleSheet
 **/
class StyleSheet implements Serializable
{
    private $href;
    private $media_list;
    private $rule_list;
    private $charset;

    public function __construct(RuleList $rule_list = null, $charset = "utf-8")
    {
        if ($rule_list === null) {
            $this->rule_list = new RuleList();
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

    public function getCssText($options = [])
    {
        return $this->rule_list->getCssText($options);
    }

    public function __toString()
    {
        return $this->getCssText();
    }

    public function getFirstRule()
    {
        return $this->rule_list->getFirst();
    }

    public function getLastRule()
    {
        return $this->rule_list->getLast();
    }

    /*
    public function __call($method, $args)
    {
      if (method_exists($this->rule_list, $method))
      {
        return call_user_func_array(array($this->rule_list, $method), $args);
      }
    }
     */

    public function __clone()
    {
        $this->media_list = clone $this->media_list;
        $this->rule_list = clone $this->rule_list;
    }
}
