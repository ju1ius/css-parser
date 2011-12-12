<?php
namespace CSS\StyleDeclaration;

use CSS\Serializable;
use CSS\Util\Object;
use CSS\StyleDeclaration;
use CSS\PropertyValueList;
use CSS\Property;
use CSS\Value;


/**
 * Utility class to create shorthand properties from a StyleDeclaration
 *
 * @package CSS
 * @subpackage StyleDeclaration
 **/
class CreateShorthands
{
  private $styleDeclaration;

  /**
   * Constructor
   *
   * @param CSS\StyleDeclaration $styleDeclaration
   **/
  public function __construct(StyleDeclaration $styleDeclaration)
  {
    $this->styleDeclaration = $styleDeclaration;
  }

  /**
   * Create shorthand properties (e.g. `margin` or `font`) whenever possible.
   **/
  public function createShorthands()
  {
    $this->createBackgroundShorthand();
    $this->createDimensionsShorthands();
    // border must be shortened after dimensions 
    $this->createBorderShorthand();
    $this->createFontShorthand();
		$this->createListStyleShorthand();
  }

  /**
   *
   * @todo Handle background-color
   *
   **/
  public function createBackgroundShorthand()
  {
    $aProperties = array(
      'background-image', 'background-position',
      // <bg-position> [ / <bg-size> ]? syntax not yet supported as of Firefox 7
      /* 'background-size', */
      'background-repeat', 'background-attachment',
      'background-origin', 'background-clip'
    );
    $oBgImageProperty = $this->styleDeclaration->getAppliedProperty('background-image');
    $oBgColorProperty = $this->styleDeclaration->getAppliedProperty('background-color');
    // we need at least a background-image or a background-color
    if(!$oBgImageProperty && !$oBgColorProperty) return;
    // get the number of layers from background-image property
    $iNumLayers = 1;
    if($oBgImageProperty) {
      $oBgImageValueList = $oBgImageProperty->getValueList();
      if($oBgImageValueList->getSeparator() === ',') {
        $iNumLayers = $oBgImageValueList->getLength();
      }
    }
    if($iNumLayers === 1) {
      $aProperties[] = 'background-color';
      $this->_createShorthandProperties($aProperties, 'background', true);
      return;
    }
    $bCanProceed = $this->_safeCleanup($aProperties, 'background');
    if(!$bCanProceed) return;
    // Now we collapse the rules
    $aNewValues = array('normal' => array(), 'important' => array());
    $aOldProperties = array('normal' => array(), 'important' => array());
    foreach($aProperties as $sProperty) {
      $oProperty = $this->styleDeclaration->getAppliedProperty($sProperty);
      if(!$oProperty) continue;
      $sDest = $oProperty->getIsImportant() ? 'important' : 'normal';
      $oValueList = $oProperty->getValueList();
      if($oValueList->getSeparator() === ',') {
        $aPropertyLayers = $oValueList->getItems();
      } else {
        $aPropertyLayers = array($oValueList->getItems());
      }
      $aOldProperties[$sDest][] = $oProperty;
      // compute missing layers
      while(count($aPropertyLayers) < $iNumLayers) {
        $aPropertyLayers = array_merge($aPropertyLayers, Object::getClone($aPropertyLayers));
      }
      // drop extra layers
      $aPropertyLayers = array_slice($aPropertyLayers, 0, $iNumLayers);
      //
      foreach($aPropertyLayers as $i => $mValue) {
        $aNewValues[$sDest][$i][$sProperty] = Object::getClone($mValue);
      }
    }
    if($oBgColorProperty) {
      $sDest = $oBgColorProperty->getIsImportant() ? 'important' : 'normal';
      $aOldProperties[$sDest][] = $oBgColorProperty;
      $aNewValues[$sDest][$iNumLayers-1]['background-color'] = Object::getClone(
        $oBgColorProperty->getValueList()->getFirst()
      );
      
    }
    $iImportantCount = count($aNewValues['important']);
    $iNormalCount = count($aNewValues['normal']);
    // Merge important values only if no normal values are present
    if($iNormalCount) {
      $this->_mergeLayers('background', $aNewValues['normal'], $aOldProperties['normal'], false);
    } else if($iImportantCount) {
      $this->_mergeLayers('background', $aNewValues['important'], $aOldProperties['important'], true);
    }
	}

  public function createListStyleShorthand()
  {
		$aProperties = array(
			'list-style-type', 'list-style-position', 'list-style-image'
		);
		$this->_createShorthandProperties($aProperties, 'list-style');
	}

  /**
   * Combine border-color, border-style and border-width into border
   * Should be run after create_dimensions_shorthand!
   **/
  public function createBorderShorthand()
  {
    $aProperties = array(
      'border-width', 'border-style', 'border-color' 
    );
		$this->_createShorthandProperties($aProperties, 'border');
  }

  /**
   * Looks for long format CSS dimensional properties
   * (margin, padding, border-color, border-style and border-width) 
   * and converts them into shorthand CSS properties.
   **/
  public function createDimensionsShorthands()
  {
    $aPositions = array('top', 'right', 'bottom', 'left');
    $aExpansions = array(
      'margin'       => 'margin-%s',
      'padding'      => 'padding-%s',
      'border-color' => 'border-%s-color', 
      'border-style' => 'border-%s-style', 
      'border-width' => 'border-%s-width'
    );
    $aProperties = $this->styleDeclaration->getProperties();
    $iImportantCount = 0;
    foreach($aExpansions as $sProperty => $sExpanded) {
      $aFoldable = array();
			foreach($aPositions as $sPosition) {
				$oProperty = $this->styleDeclaration->getAppliedProperty(sprintf($sExpanded, $sPosition));
        if(!$oProperty) continue;
        if($oProperty->getIsImportant()) $iImportantCount++;
				$aFoldable[$oProperty->getName()] = $oProperty; 
			}
      // All four dimensions must be present
      if(count($aFoldable) !== 4) return;
      // All four dimensions must have same importance
      if($iImportantCount && $iImportantCount !== 4) return;

      $aValues = array();
      foreach($aPositions as $sPosition) {
        $oProperty = $aFoldable[sprintf($sExpanded, $sPosition)];
        $aPropertyValues = $oProperty->getValueList()->getItems();
        $aValues[$sPosition] = Object::getClone($aPropertyValues[0]);
      }
      $oNewValueList = new PropertyValueList(array(), ' ');
      if($aValues['left']->getCssText() === $aValues['right']->getCssText()) {
        if($aValues['top']->getCssText() === $aValues['bottom']->getCssText()) {
          if($aValues['top']->getCssText() === $aValues['left']->getCssText()) {
            // All 4 sides are equal
            $oNewValueList->append($aValues['top']);
          } else {
            // Top and bottom are equal, left and right are equal
            $oNewValueList->append($aValues['top']);
            $oNewValueList->append($aValues['left']);
          }
        } else {
          // Only left and right are equal
          $oNewValueList->append($aValues['top']);
          $oNewValueList->append($aValues['left']);
          $oNewValueList->append($aValues['bottom']);
        }
      } else {
        // No sides are equal 
        $oNewValueList->append($aValues['top']);
        $oNewValueList->append($aValues['right']);
        $oNewValueList->append($aValues['bottom']);
        $oNewValueList->append($aValues['left']);
      }
      $oNewProperty = new Property($sProperty, $oNewValueList);
      $oNewProperty->setIsImportant(!!$iImportantCount);
      $this->styleDeclaration->append($oNewProperty);
      foreach ($aPositions as $sPosition)
      {
        $this->styleDeclaration->remove(sprintf($sExpanded, $sPosition));
      }
    }
  }

  /**
   * Looks for long format CSS font properties (e.g. <tt>font-weight</tt>) and 
   * tries to convert them into a shorthand CSS <tt>font</tt> property. 
   * At least font-size AND font-family must be present in order to create a shorthand declaration.
   **/
  public function createFontShorthand()
  {
    $aFontProperties = array(
      'font-style', 'font-variant', 'font-weight', 'font-size', 'line-height', 'font-family'
    );
    $oFSProperty = $this->styleDeclaration->getAppliedProperty('font-size');
    $oFFProperty = $this->styleDeclaration->getAppliedProperty('font-family');
    if(!$oFSProperty || !$oFFProperty) return;
    $oNewValueList = new PropertyValueList(array(), ' ');
    foreach(array('font-style', 'font-variant', 'font-weight') as $sProperty) {
			$oProperty = $this->styleDeclaration->getAppliedProperty($sProperty);
			if(!$oProperty) continue;
			$aValues = $oProperty->getValueList()->getItems();
			if($aValues[0] !== 'normal') {
				$oNewValueList->append(Object::getClone($aValues[0]));
			}
    }
    // Get the font-size value
    $aFSValues = $oFSProperty->getValueList()->getItems();
    // But wait to know if we have line-height to add it
		$oLHProperty = $this->styleDeclaration->getAppliedProperty('line-height');
    if($oLHProperty) {
      $aLHValues = $oLHProperty->getValueList()->getItems();
      if('normal' !== $aLHValues[0]) {
        $val = new PropertyValueList(
          array(
            Object::getClone($aFSValues[0]),
            Object::getClone($aLHValues[0])
          ),
          '/'
        );
        $oNewValueList->append($val);
      } else {
        $oNewValueList->append(Object::getClone($aFSValues[0]));
      }
    } else {
      $oNewValueList->append(Object::getClone($aFSValues[0]));
    }
		// Font-Family
    //$aFFValues = $oFFProperty->getValueList()->getItems();
		//$oFFValue = new PropertyValueList($aFFValues, ',');
    //$oNewValueList->append($oFFValue);
    $oNewValueList->append(Object::getClone($oFFProperty->getValueList()));

    $oNewProperty = new Property('font', $oNewValueList);
    $this->styleDeclaration->append($oNewProperty);
    $this->styleDeclaration->remove($aFontProperties);
	}

  private function _createShorthandProperties(array $aProperties, $sShorthand, $bSafe=false)
  {
    if($bSafe) {
      $bCanProceed = $this->_safeCleanup($aProperties, $sShorthand);
    } else {
      $bCanProceed = $this->_destructiveCleanup($aProperties, $sShorthand);
    }
    if(!$bCanProceed) return;
    // Now we collapse the rules
    $aNewValues = array('normal' => array(), 'important' => array());
    $aOldProperties = array('normal' => array(), 'important' => array());
    foreach($aProperties as $sProperty) {
      $aProperties = $this->styleDeclaration->getProperties($sProperty);
      foreach($aProperties as $iPos => $oProperty) {
        $aValues = $oProperty->getValueList()->getItems();
        $sDest = $oProperty->getIsImportant() ? 'important' : 'normal';
        $aOldProperties[$sDest][] = $iPos;
        foreach($aValues as $mValue) {
          $aNewValues[$sDest][] = Object::getClone($mValue);
        }
      }
    }
    $iImportantCount = count($aNewValues['important']);
    $iNormalCount = count($aNewValues['normal']);
    // Merge important values only if no normal values are present
    if($iNormalCount) {
      $this->_mergeValues($sShorthand, $aNewValues['normal'], $aOldProperties['normal'], false);
    } else if($iImportantCount) {
      $this->_mergeValues($sShorthand, $aNewValues['important'], $aOldProperties['important'], true);
    }
  }

  private function _mergeLayers($sShorthand, $aLayers, $aOldProperties, $bImportant)
  {
    $this->styleDeclaration->remove($aOldProperties);
    $oNewValueList = new PropertyValueList(array(), ',');
    foreach($aLayers as $aValues) {
      $oLayerValueList = new PropertyValueList($aValues, ' ');
      $oNewValueList->append($oLayerValueList);
    }
    $oNewProperty = new Property($sShorthand, $oNewValueList);
    $oNewProperty->setIsImportant($bImportant);
    $this->styleDeclaration->append($oNewProperty);
  }

  private function _mergeValues($sShorthand, $aValues, $aOldProperties, $bImportant)
  {
    $this->styleDeclaration->remove($aOldProperties);
    $oNewValueList = new PropertyValueList($aValues, ' ');
    $oNewProperty = new Property($sShorthand, $oNewValueList);
    $oNewProperty->setIsImportant($bImportant);
    $this->styleDeclaration->append($oNewProperty);
  }
  
  /**
   * Destructively cleans up rules before creating shorthand properties.
   * Keeps only significant properties according to their
   * respective order and importance.
   * This is the method we want to use in most cases.
   *
   **/
  private function _destructiveCleanup(Array $aProperties, $sShorthand) {
    // first we check if a shorthand already exists, and keep only the relevant one.
    $aLastExistingShorthand = $this->styleDeclaration->getAppliedProperty($sShorthand, true);
    foreach($this->styleDeclaration->getProperties($sShorthand) as $iPos => $oProperty) {
      if($iPos !== $aLastExistingShorthand['position']) $this->styleDeclaration->remove($iPos);
    }
    // next we try to get rid of unused rules
    foreach($aProperties as $sProperty) {
      $aRule = $this->styleDeclaration->getAppliedProperty($sProperty, true);
      if(!$aRule) continue;
			foreach($this->styleDeclaration->getProperties($sProperty) as $iPos => $oProperty) {
        if($iPos !== $aRule['position']) $this->styleDeclaration->remove($iPos);
      }
      if($aLastExistingShorthand) {
        $bRuleIsImportant = $aRule['property']->getIsImportant();
        $bShorthandIsImportant = $aLastExistingShorthand['property']->getIsImportant();
        $iRulePosition = $aRule['position'];
        $iShorthandPosition = $aLastExistingShorthand['position'];
        // IF property comes before shorthand AND they have the same importance,
        // OR IF shorthand is important AND property is not,
        // we can get rid of the property.
        if(($iRulePosition < $iShorthandPosition && $bRuleIsImportant === $bShorthandIsImportant)
           || (!$bRuleIsImportant && $bShorthandIsImportant)) {
          $this->styleDeclaration->remove($iRulePosition);
        }
      }
    }
    if($aLastExistingShorthand) {
      // Now that we made sure that there is no duplicate shorthand
      // we can expand the corresponding rule as expanding doesn't create duplicates.
      $sExpandMethod = 'expand'.str_replace(' ', '', ucwords(str_replace('-', ' ', $sShorthand))).'Shorthands';
      $this->styleDeclaration->$sExpandMethod();
    }
    return true;
  }

  /**
   * Safely cleans up properties before creating shorthand properties.
   * Avoids creating a shorthand if:
   * <ul>
   *   <li>
   *     More than one shorthand is already present,
   *     as it is generally done on purpose, ie for vendor specific values.
   *     <code>
   *       background: -webkit-linear-gradient(#000, #fff);
   *       background: -moz-linear-gradient(#000, #fff);
   *     </code>
   *   </li>
   *   <li>
   *     More than one identical properties are found,
   *     for the very same reasons
   *   </li>
   * </ul>
   *
   * @return bool True if collapsing can continue after cleanup, false otherwise
   **/
  private function _safeCleanup(Array $aProperties, $sShorthand) {
    $aExistingShorthands = $this->styleDeclaration->getProperties($sShorthand);
    $iNumShorthands = count($aExistingShorthands);
    // Don't create shorthands if more than one are already present,
    if($iNumShorthands > 1) return false;
    if($iNumShorthands === 1) {
      $iExistingShorthandPosition = key($aExistingShorthands);
    }
    foreach($aProperties as $sProperty) {
      $aProperties = $this->styleDeclaration->getProperties($sProperty);
      // Don't merge anything if several identical rules are present.
      if(count($aProperties) > 1) return false;
      // Can't merge property if no value
      if(count($aProperties) === 0) continue;
			foreach($aProperties as $iPos => $oProperty) {
        if($iNumShorthands && !$oProperty->getIsImportant() && $iPos < $iExistingShorthandPosition) {
          // If rule is not important and comes before a shorthand, we can safely remove it.
          $this->styleDeclaration->remove($iPos);
          continue;
				}
      }
    }
    if($iNumShorthands) {
      // Now that we made sure that there is no duplicate shorthand
      // we can expand the corresponding property as expanding doesn't create duplicates.
      $sExpandMethod = 'expand'.str_replace(' ', '', ucwords(str_replace('-', ' ', $sShorthand))).'Shorthands';
      $this->styleDeclaration->$sExpandMethod();
    }
    return true;
  }
}
