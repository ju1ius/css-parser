<?php

namespace ju1ius\Css\Rule;

use ju1ius\Css\PageSelector;
use ju1ius\Css\Rule;
use ju1ius\Css\RuleList;
use ju1ius\Css\StyleDeclaration;

/**
 * Represents an @page rule
 **/
class Page extends Rule
{
    private
        $selector,
        $margin_rules,
        $style_declaration;

    public function __construct(
        PageSelector $selector = null,
        RuleList $margin_rules = null,
        StyleDeclaration $style_declaration = null
    )
    {
        $this->selector = $selector;
        $this->margin_rules = $margin_rules;
        if ($style_declaration) {
            $style_declaration->setParentRule($this);
            if ($parentStyleSheet = $this->getParentStyleSheet()) {
                $style_declaration->setParentStyleSheet($parentStyleSheet);
            }
        }
        $this->style_declaration = $style_declaration;
    }

    public function getSelector()
    {
        return $this->selector;
    }

    public function setSelector(PageSelector $selector)
    {
        $this->selector = $selector;
    }

    public function getRuleList()
    {
        return $this->margin_rules;
    }

    public function setRuleList(RuleList $margin_rules)
    {
        $this->margin_rules = $margin_rules;
    }

    public function getStyleDeclaration()
    {
        return $this->style_declaration;
    }

    public function setStyleDeclaration(StyleDeclaration $style_declaration)
    {
        $style_declaration->setParentRule($this);
        if ($parentStyleSheet = $this->getParentStyleSheet()) {
            $style_declaration->setParentStyleSheet($parentStyleSheet);
        }
        $this->style_declaration = $style_declaration;
    }

    public function getCssText($options = [])
    {
        $indent = $nl = '';
        if (isset($options['indent_level'])) {
            $indent = str_repeat($options['indent_char'], $options['indent_level']);
            $options['indent_level']++;
            $nl = "\n";
        }

        $rules = $this->margin_rules ? $this->margin_rules->getCssText($options) : '';
        $declarations = $this->style_declaration ? $this->style_declaration->getCssText($options) : '';

        return $indent . '@page ' . $this->getSelectorText()
            . '{' . $nl
            . $rules
            . $declarations
            . $nl . $indent . '}';
    }

    public function getSelectorText()
    {
        return $this->selector ? $this->selector->getCssText() : '';
    }

    public function __clone()
    {
        $this->style_declaration = clone $this->style_declaration;
        $this->margin_rules = clone $this->margin_rules;
        $this->selector = clone $this->selector;
    }
}
