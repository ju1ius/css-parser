<?php
namespace ju1ius\Css\Rule;

use ju1ius\Css\Rule;
use ju1ius\Css\StyleDeclaration;
use ju1ius\Css\Value\Percentage;

/**
 * Represents an @keyframe rule
 **/
class Keyframe extends Rule
{
    private $selectors;
    private $styleDeclaration;

    public function __construct($selectors, StyleDeclaration $styleDeclaration=null)
    {
        $this->selectors = $selectors;
        $this->styleDeclaration = $styleDeclaration;
    }

    public function getSelectors()
    {
        return $this->selectors;
    }

    public function setSelectors(Array $selectors)
    {
        $this->selectors = $selectors;
    }

    public function getStyleDeclaration()
    {
        return $this->styleDeclaration;
    }

    public function setStyleDeclaration(StyleDeclaration $styleDeclaration)
    {
        $styleDeclaration->setParentRule($this);
        if ($parentStyleSheet = $this->getParentStyleSheet()) {
            $styleDeclaration->setParentStyleSheet($parentStyleSheet);
        }
        $this->styleDeclaration = $styleDeclaration;
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
        $selectorsText = implode(',', array_map(function($selector) {
            return $selector->getCssText();
        }, $this->selectors));
        return $selectorsText . '{' . $nl
            . $this->styleDeclaration->getCssText($options)
            . $nl . $indent . '}'
        ;
    }

    public function __clone()
    {
        $this->styleDeclaration = clone $this->styleDeclaration;
    }
}
