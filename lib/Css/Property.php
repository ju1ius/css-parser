<?php declare(strict_types=1);

namespace ju1ius\Css;

/**
 * Represents a Css property
 **/
class Property implements Serializable
{
    private $name;
    private $valueList;
    private $isImportant;

    public function __construct($name, PropertyValueList $valueList = null)
    {
        $this->name = $name;
        if (null === $valueList) {
            $this->valueList = new PropertyValueList();
        }
        $this->valueList = $valueList;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getValueList()
    {
        return $this->valueList;
    }

    public function setValueList(PropertyValueList $valueList)
    {
        $this->valueList = $valueList;
    }

    public function getIsImportant()
    {
        return $this->isImportant;
    }

    public function setIsImportant($isImportant)
    {
        $this->isImportant = $isImportant;
    }

    /**
     * Adds a value to the existing value.
     * Value will be appended if a PropertyValueList exists of the given type.
     * Otherwise, the existing value will be wrapped by one.
     */
    public function addValue($value, $type = ' ')
    {
        //$this->value->append($value);
        if (!is_array($value)) {
            $value = [$value];
        }
        if (!$this->valueList instanceof PropertyValueList || $this->valueList->getSeparator() !== $type) {
            $currentValue = $this->valueList;
            $this->valueList = new PropertyValueList($type);
            if ($currentValue) {
                $this->valueList->append($currentValue);
            }
        }
        foreach ($value as $valueItem) {
            $this->valueList->append($valueItem);
        }
    }

    public function getCssText($options = [])
    {
        return $this->name . ': '
            . $this->valueList->getCssText($options)
            . ($this->isImportant ? ' !important' : '')
            . ';';
    }

    public function __toString()
    {
        return $this->getCssText();
    }

    public function __clone()
    {
        $this->valueList = clone $this->valueList;
    }
}
