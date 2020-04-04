<?php declare(strict_types=1);

namespace ju1ius\Css\StyleDeclaration;

use ju1ius\Css\Property;
use ju1ius\Css\PropertyValueList;
use ju1ius\Css\StyleDeclaration;
use ju1ius\Css\Util\Cloner;
use ju1ius\Css\Value;

/**
 * Expands shorthand properties to their longhand form in a StyleDeclaration
 *
 **/
class ExpandShorthands
{
    private $styleDeclaration;

    /**
     * Constructor
     *
     * @param ju1ius\Css\StyleDeclaration $styleDeclaration
     **/
    public function __construct(StyleDeclaration $styleDeclaration)
    {
        $this->styleDeclaration = $styleDeclaration;
    }

    /**
     * Split shorthand declarations (e.g. +margin+ or +font+) into their constituent parts.
     *
     **/
    public function expandShorthands()
    {
        // border must be expanded before dimensions
        $this->expandBorderShorthands();
        $this->expandDimensionsShorthands();
        $this->expandFontShorthands();
        $this->expandBackgroundShorthands();
        $this->expandListStyleShorthands();
    }


    public function expandBackgroundShorthands()
    {
        $aProperties = $this->styleDeclaration->getProperties('background');
        // don't expand if several shorthands are present,
        // as it is generally done on purpose, ie for vendor specific values.
        if (count($aProperties) !== 1) {
            return;
        }
        foreach ($aProperties as $iPos => $oProperty) {
            $this->_expandBackgroundShorthand($iPos, $oProperty);
        }
    }

    /**
     * Convert shorthand font declarations
     * (e.g. <tt>font: 300 italic 11px/14px verdana, helvetica, sans-serif;</tt>)
     * into their constituent parts.
     **/
    public function expandFontShorthands()
    {
        $aProperties = $this->styleDeclaration->getProperties('font');
        if (empty($aProperties)) {
            return;
        }
        foreach ($aProperties as $iPos => $oProperty) {
            // reset properties to 'normal' per http://www.w3.org/TR/CSS21/fonts.html#font-shorthand
            $aFontProperties = [
                'font-style' => 'normal',
                'font-variant' => 'normal',
                'font-weight' => 'normal',
                'font-size' => 'normal',
                'line-height' => 'normal',
            ];
            $aValues = $oProperty->getValueList()->getItems();
            foreach ($aValues as $mValue) {
                $mValue = Cloner::clone($mValue);
                if (in_array($mValue, ['normal', 'inherit'])) {
                    foreach (['font-style', 'font-weight', 'font-variant'] as $sProperty) {
                        if (!isset($aFontProperties[$sProperty])) {
                            $aFontProperties[$sProperty] = $mValue;
                        }
                    }
                } elseif (in_array($mValue, ['italic', 'oblique'])) {
                    $aFontProperties['font-style'] = $mValue;
                } elseif ($mValue == 'small-caps') {
                    $aFontProperties['font-variant'] = $mValue;
                } elseif (
                    in_array($mValue, ['bold', 'bolder', 'lighter'])
                    || ($mValue instanceof Value\Dimension
                        && in_array($mValue->getValue(), range(100, 900, 100)))
                ) {
                    $aFontProperties['font-weight'] = $mValue;
                } elseif ($mValue instanceof PropertyValueList && $mValue->getSeparator() === '/') {
                    list($oSize, $oHeight) = $mValue->getItems();
                    $aFontProperties['font-size'] = $oSize;
                    $aFontProperties['line-height'] = $oHeight;
                } elseif (
                    ($mValue instanceof Value\Dimension && $mValue->getUnit() !== null)
                    || in_array($mValue, ['xx-small', 'x-small', 'small', 'medium', 'large', 'x-large', 'xx-large', 'larger', 'smaller'])
                ) {
                    $aFontProperties['font-size'] = $mValue;
                } else {
                    $aFontProperties['font-family'] = $mValue;
                }
            }
            foreach ($aFontProperties as $sProperty => $mValue) {
                $this->_addPropertyExpansion($iPos, $oProperty, $sProperty, $mValue);
            }
            $this->styleDeclaration->remove($oProperty);
        }
    }

    /**
     * Split shorthand border declarations (e.g. <tt>border: 1px red;</tt>)
     * Additional splitting happens in expandDimensionsShorthand
     * Multiple borders are not yet supported as of CSS3
     **/
    public function expandBorderShorthands()
    {
        $aBorderProperties = [
            'border', 'border-left', 'border-right', 'border-top', 'border-bottom',
        ];
        $aBorderSizes = [
            'thin', 'medium', 'thick',
        ];
        foreach ($aBorderProperties as $sBorderProperty) {
            $aProperties = $this->styleDeclaration->getProperties($sBorderProperty);
            if (empty($aProperties)) {
                continue;
            }
            foreach ($aProperties as $iPos => $oProperty) {
                $aValues = $oProperty->getValueList()->getItems();
                foreach ($aValues as $mValue) {
                    $mValue = Cloner::clone($mValue);
                    if ($mValue instanceof Value\Dimension) {
                        $sNewPropertyName = $sBorderProperty . "-width";
                    } elseif ($mValue instanceof Value\Color) {
                        $sNewPropertyName = $sBorderProperty . "-color";
                    } else {
                        if (in_array($mValue, $aBorderSizes)) {
                            $sNewPropertyName = $sBorderProperty . "-width";
                        } else /* if (in_array($mValue, $aBorderStyles))*/ {
                            $sNewPropertyName = $sBorderProperty . "-style";
                        }
                    }
                    $this->_addPropertyExpansion($iPos, $oProperty, $sNewPropertyName, $mValue);
                }
                $this->styleDeclaration->remove($oProperty);
            } // end foreach $oPropertys
        } // end foreach $aBorderProperties
    }

    /**
     * Split shorthand dimensional declarations (e.g. <tt>margin: 0px auto;</tt>)
     * into their constituent parts.
     * Handles margin, padding, border-color, border-style and border-width.
     **/
    public function expandDimensionsShorthands()
    {
        $aExpansions = [
            'margin' => 'margin-%s',
            'padding' => 'padding-%s',
            'border-color' => 'border-%s-color',
            'border-style' => 'border-%s-style',
            'border-width' => 'border-%s-width',
        ];
        foreach ($aExpansions as $sProperty => $sExpanded) {
            $aProperties = $this->styleDeclaration->getProperties($sProperty);
            //$aProperties = $this->styleDeclaration->getAppliedProperty($sProperty, true);
            if (empty($aProperties)) {
                continue;
            }
            foreach ($aProperties as $iPos => $oProperty) {
                $aValues = $oProperty->getValueList()->getItems();
                $top = $right = $bottom = $left = null;
                switch (count($aValues)) {
                    case 1:
                        $result = [
                            'top' => Cloner::clone($aValues[0]),
                            'right' => Cloner::clone($aValues[0]),
                            'bottom' => Cloner::clone($aValues[0]),
                            'left' => Cloner::clone($aValues[0]),
                        ];
                        break;
                    case 2:
                        $result = [
                            'top' => Cloner::clone($aValues[0]),
                            'right' => Cloner::clone($aValues[1]),
                            'bottom' => Cloner::clone($aValues[0]),
                            'left' => Cloner::clone($aValues[1]),
                        ];
                        break;
                    case 3:
                        $result = [
                            'top' => Cloner::clone($aValues[0]),
                            'right' => Cloner::clone($aValues[1]),
                            'bottom' => Cloner::clone($aValues[2]),
                            'left' => Cloner::clone($aValues[1]),
                        ];
                        break;
                    case 4:
                        $result = [
                            'top' => Cloner::clone($aValues[0]),
                            'right' => Cloner::clone($aValues[1]),
                            'bottom' => Cloner::clone($aValues[2]),
                            'left' => Cloner::clone($aValues[3]),
                        ];
                        break;
                }
                foreach ($result as $sPosition => $mValue) {
                    $sNewPropertyName = sprintf($sExpanded, $sPosition);
                    $this->_addPropertyExpansion($iPos, $oProperty, $sNewPropertyName, $mValue);
                }
                $this->styleDeclaration->remove($oProperty);
            }
        }
    }

    public function expandListStyleShorthands()
    {
        $aListStyleTypes = [
            'none', 'disc', 'circle', 'square', 'decimal-leading-zero', 'decimal',
            'lower-roman', 'upper-roman', 'lower-greek', 'lower-alpha', 'lower-latin',
            'upper-alpha', 'upper-latin', 'hebrew', 'armenian', 'georgian', 'cjk-ideographic',
            'hiragana', 'hira-gana-iroha', 'katakana-iroha', 'katakana',
        ];
        $aListStylePositions = [
            'inside', 'outside',
        ];
        $aProperties = $this->styleDeclaration->getProperties('list-style');
        if (empty($aProperties)) {
            return;
        }
        foreach ($aProperties as $iPos => $oProperty) {
            $aListProperties = [
                'list-style-type' => 'disc',
                'list-style-position' => 'outside',
                'list-style-image' => 'none',
            ];
            $aValues = $oProperty->getValueList()->getItems();
            if (count($aValues) === 1 && $aValues[0] === 'inherit') {
                foreach ($aListProperties as $sProperty => $mValue) {
                    $this->_addPropertyExpansion($iPos, $oProperty, $sProperty, 'inherit');
                    if ($cleanup) {
                        $this->_cleanupProperty($sNewPropertyName);
                    }
                }
                $this->styleDeclaration->remove($iPos);
                return;
            }
            foreach ($aValues as $mValue) {
                $mValue = Cloner::clone($mValue);
                if ($mValue instanceof Value\Url) {
                    $aListProperties['list-style-image'] = $mValue;
                } elseif (in_array($mValue, $aListStyleTypes)) {
                    $aListProperties['list-style-type'] = $mValue;
                } elseif (in_array($mValue, $aListStylePositions)) {
                    $aListProperties['list-style-position'] = $mValue;
                }
            }
            foreach ($aListProperties as $sProperty => $mValue) {
                $this->_addPropertyExpansion($iPos, $oProperty, $sProperty, $mValue);
            }
            $this->styleDeclaration->remove($oProperty);
        }
    }

    private function _addPropertyExpansion($iShorthandPosition, $oShorthandProperty, $sNewPropertyName, $mValue)
    {
        if (!$this->_canAddShorthandExpansion($oShorthandProperty, $sNewPropertyName/*, $iShorthandPosition*/)) {
            return;
        }
        $separator = is_array($mValue) ? ' ' : ',';
        $oNewProperty = new Property(
            $sNewPropertyName,
            new PropertyValueList($mValue, $separator)
        );
        $oNewProperty->setIsImportant($oShorthandProperty->getIsImportant());
        $this->styleDeclaration->insertAfter($oNewProperty, $oShorthandProperty);
    }

    /**
     * Checks if we can add an expansion
     * We don't expand if a property exists with the same name after the new one,
     * unless the importance of the new one is superior
     **/
    private function _canAddShorthandExpansion($oShorthandProperty, $sNewPropertyName, $iShorthandPosition = null)
    {
        if ($iShorthandPosition === null) {
            $iShorthandPosition = $this->styleDeclaration->getPropertyIndex($oShorthandProperty);
        }
        $bShorthandIsImportant = $oShorthandProperty->getIsImportant();
        $aExistingProperties = $this->styleDeclaration->getProperties($sNewPropertyName);
        if (!empty($aExistingProperties)) {
            foreach ($aExistingProperties as $iPos => $oProperty) {
                $bPropertyIsImportant = $oProperty->getIsImportant();
                if ($iPos > $iShorthandPosition
                    && ($bShorthandIsImportant == $bPropertyIsImportant
                        || ($bPropertyIsImportant && !$bShorthandIsImportant))
                ) {
                    return false;
                }
            }
        }
        return true;
    }

    private function _expandBackgroundShorthand($iPos, $oProperty)
    {
        $oValueList = $oProperty->getValueList();
        // Get a normalized array
        if ($oValueList->getSeparator() === ',' && count($oValueList) > 1) {
            // we have multiple layers
            $aValueList = $oValueList->getItems();
        } else {
            // we have only one value or a space separated list of values
            $aValueList = [$oValueList->getItems()];
        }
        $iNumLayers = count($aValueList);
        $aUnfoldedResults = [];
        // background-color only allowed on final layer;
        $color = null;

        foreach ($aValueList as $iLayerIndex => $aValues) {

            // if we have multiple layers, get the values for this layer
            if ($aValues instanceof PropertyValueList) {
                $aValues = $aValues->getItems();
            } elseif (!is_array($aValues)) {
                $aValues = [$aValues];
            }

            $aBgProperties = [];
            $iNumBgPos = 0;
            $iNumBoxValues = 0;
            foreach ($aValues as $mValue) {
                $mValue = Cloner::clone($mValue);
                if ($mValue instanceof Value\Url || $mValue instanceof Value\CssFunction || $mValue == "none") {
                    $aBgProperties['background-image'] = $mValue;
                } elseif ($mValue instanceof PropertyValueList) {
                    // bg-pos bg-pos? / bg-size bg-size?
                    $oBgPosValues = $mValue->getFirst();
                    if ($oBgPosValues instanceof PropertyValueList) {
                        $aBgPosValues = $oBgPosValues->getItems();
                    } else {
                        $aBgPosValues = [$oBgPosValues];
                    }
                    $bgpos_valuelist = new PropertyValueList(
                        [$aBgPosValues[0], 'center'],
                        ' '
                    );
                    if (count($aBgPosValues) > 1) {
                        $bgpos_valuelist->replace(1, $aBgPosValues[1]);
                    }
                    $aBgProperties['background-position'] = $bgpos_valuelist;
                    //
                    $oBgSizeValues = $mValue->getLast();
                    if ($oBgSizeValues instanceof PropertyValueList) {
                        $aBgSizeValues = $oBgSizeValues->getItems();
                    } else {
                        $aBgSizeValues = [$oBgSizeValues];
                    }
                    $bgsize_valuelist = new PropertyValueList(
                        [$aBgSizeValues[0], $aBgSizeValues[0]],
                        ' '
                    );
                    if (count($aBgSizeValues) > 1) {
                        $bgsize_valuelist->replace(1, $aBgSizeValues[1]);
                    }
                    $aBgProperties['background-size'] = $bgsize_valuelist;
                } elseif (in_array($mValue, ['left', 'center', 'right', 'top', 'bottom'])
                    || $mValue instanceof Value\Dimension
                ) {
                    //if ($mValue instanceof Value\Dimension) $mValue = clone $mValue;
                    if ($iNumBgPos === 0) {
                        $aBgProperties['background-position'] = new PropertyValueList(
                            [$mValue, 'center'],
                            ' '
                        );
                    } else {
                        $aBgProperties['background-position']->replace(1, $mValue);
                    }
                    $iNumBgPos++;
                } elseif (in_array($mValue, ['repeat', 'no-repeat', 'repeat-x', 'repeat-y', 'space', 'round'])) {
                    $aBgProperties['background-repeat'] = $mValue;
                } elseif (in_array($mValue, ['scroll', 'fixed', 'local'])) {
                    $aBgProperties['background-attachment'] = $mValue;
                } elseif (in_array($mValue, ['border-box', 'padding-box', 'content-box'])) {
                    if ($iNumBoxValues === 0) {
                        $aBgProperties['background-origin'] = $mValue;
                        $aBgProperties['background-clip'] = $mValue;
                    } else {
                        $aBgProperties['background-clip'] = $mValue;
                    }
                    $iNumBoxValues++;
                } elseif ($mValue instanceof Value\Color) {
                    if ($iLayerIndex == $iNumLayers - 1) {
                        $color = $mValue;
                    } else {
                        if (empty($aBgProperties)) {
                            $aBgProperties['background-image'] = "none";
                        }
                    }
                }
            }
            $aUnfoldedResults[] = $aBgProperties;
        }
        if ($color) {
            $aUnfoldedResults[$iNumLayers - 1]['background-color'] = $color;
        }
        $aFoldedResults = [];
        foreach ($aUnfoldedResults as $i => $result) {
            foreach ($result as $propname => $propval) {
                $aFoldedResults[$propname][$i] = $propval;
            }
        }
        foreach ($aFoldedResults as $propname => $aValues) {
            if ($this->_canAddShorthandExpansion($oProperty, $propname)) {
                $separator = count($aValues) === 0 ? ' ' : ',';
                $oValueList = new PropertyValueList($aValues, $separator);
                $oNewProp = new Property($propname, $oValueList);
                $oNewProp->setIsImportant($oProperty->getIsImportant());
                $this->styleDeclaration->insertAfter($oNewProp, $oProperty);
            }
        }
        $this->styleDeclaration->remove($oProperty);
    }
}
