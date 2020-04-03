<?php

namespace ju1ius\Css;

class PageSelector implements Serializable
{
    private
        $page_name,
        $pseudo_class;

    public function __construct($name = null, $pseudo = null)
    {
        $this->page_name = $name;
        $this->pseudo_class = $pseudo;
    }

    public function getPageName()
    {
        return $this->page_name;
    }

    public function setPageName($page_name)
    {
        $this->page_name = $page_name;
    }

    public function getPseudoClass()
    {
        return $this->pseudo_class;
    }

    public function setPseudoClass($pseudo_class)
    {
        $this->pseudo_class = $pseudo_class;
    }

    public function getSpecificity()
    {
        $specificity = 0;
        if ($this->page_name) $specificity += 100;
        switch ($this->pseudo_class) {
            case 'first':
                $specificity += 10;
                break;
            case 'left':
            case 'right':
                $specificity += 1;
                break;
            default:
                break;
        }
        return $specificity;
    }

    public function getCssText($options = [])
    {
        $name = $this->page_name ?: '';
        $pseudo = $this->pseudo_class ? ':' . $this->pseudo_class : '';
        return $name . $pseudo;
    }

    public function __toString()
    {
        return $this->getCssText();
    }
}
