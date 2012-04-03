<?php
namespace ju1ius\Css\Value;

use ju1ius\Css\Util;

/**
 * @package Css
 * @subpackage Value
 **/
class Color extends PrimitiveValue
{
  private
    $channels = array(
      'r' => 0, 'g' => 0, 'b' => 0 
    ),
    $mode;

  /**
   * @param mixed $color Can be an hex color string, an x11 color name,
   *                     or an array of rgb(a) or hsl(a) channels;
   *
   * @example $c = new Color(array('h' => 120, 's' => '50%', 'l' => '50%', 'a' => 0.8));
   **/
  public function __construct($color=null)
  {
    if(is_array($color)) {
      if(isset($color['r'], $color['g'], $color['b'])) {
        $this->fromRgb($color);
      } else if(isset($color['h'], $color['s'], $color['l'])) {
        $this->fromHsl($color);
      }
    } else if(is_string($color)) {
      if($rgb = Util\Color::x11ToRgb($color)) {
        $this->fromRgb($rgb);
      } else if($rgb = Util\Color::hexToRgb($color)) {
        $this->fromRgb($rgb);
      }
    }
  }

  public function getMode()
  {
    return $this->mode;
  }

  public function fromRgb(Array $rgb)
  {
    $mode = 'rgb';
    foreach($rgb as $channel => $value) {
      if($channel === 'a') {
				$value = Util\Color::constrainValue((string)$value, 0, 1);
				if($value === 1) continue;
				$mode .= 'a';
			} else {
				$value = Util\Color::normalizeRgbValue((string)$value);
			}
      $this->channels[$channel] = new Dimension($value);
    }
		$this->mode = $mode;
    return $this;
  }

  public function fromHsl(Array $hsl)
  {
    $rgb = Util\Color::hslToRgb(
			(string)$hsl['h'],
			(string)$hsl['s'],
			(string)$hsl['l'],
      isset($hsl['a']) ? (string)$hsl['a'] : 1
    );
    return $this->fromRgb($rgb);
  }

  public function fromHex($value)
  {
    $rgb = Util\Color::hexToRgb($value);
    return $this->fromRgb($rgb);
  }

  public function fromX11($value)
  {
    $rgb = Util\Color::x11ToRgb($value);
    return $this->fromRgb($rgb);
  }

  public function toRgb()
  {
    $mode = $this->mode;
    $channels = $this->channels;
    
    if(!$mode || $mode === 'rgb') return;
    if($mode === 'rgba') {
      // If we don't need alpha channel, drop it
      if($channels['a']->getValue() >= 1) {
        unset($this->channels['a']);
        $this->mode = 'rgb';
      }
      return;
    }
    $rgb = Util\Color::hslToRgb(
      $channels['h']->getValue(),
      $channels['s']->getValue(),
      $channels['l']->getValue(),
      isset($channels['a']) ? $channels['a']->getValue() : 1
    );
		
    $this->channels = array();
    foreach($rgb as $key => $val) {
      $this->channels[$key] = new Dimension($val);
    }
    $this->mode = isset($rgb['a']) ? 'rgba' : 'rgb';
    return $this;
  }

  public function toHsl()
  {
    $mode = $this->mode;
    $channels = $this->channels;

    if(!$mode || $mode == 'hsl') return;
    if($mode == 'hsla') {
      // If we don't need alpha channel, drop it
      if($channels['a']->getValue() >= 1) {
        unset($this->channels['a']);
        $this->mode = 'hsl';
      }
      return;
    }
    $hsl = Util\Color::rgb2hsl(
      $channels['r']->getValue(),
      $channels['g']->getValue(),
      $channels['b']->getValue(),
      isset($channels['a']) ? $channels['a']->getValue() : 1
    );
    $this->channels = array();
    $this->channels['h'] = new Dimension($hsl['h']);
    $this->channels['s'] = new Percentage($hsl['s']);
    $this->channels['l'] = new Percentage($hsl['l']);
		$this->mode = 'hsl';
    if(isset($hsl['a'])) {
      $this->channels['a'] = new Dimension($hsl['a']);
      $this->mode = 'hsla';
		}
    return $this;
  }

  public function getX11Color()
  {
    $channels = $this->channels;
    if(isset($channels['a']) && $channels['a']->getValue() !== 1) return null;

    if($this->mode == 'rgb') {
      return Util\Color\rgbToX11(
        $channels['r']->getValue(),
        $channels['g']->getValue(),
        $channels['b']->getValue()
      );
    } else if ($this->mode == 'hsl') {
      return Util\Color::hslToX11(
        $channels['h']->getValue(),
        $channels['s']->getValue(),
        $channels['l']->getValue()
      );
    }
  }

  public function getHexValue()
  {
    $channels = $this->channels;
    if(isset($channels['a']) && $channels['a']->getValue() !== 1) return null;

    if($this->mode === 'rgb') {
      return Util\Color::rgbToHex(
        $channels['r']->getValue(),
        $channels['g']->getValue(),
        $channels['b']->getValue()
      );
    } else if($this->mode === 'hsl') {
      return Util\Color::hslToX11(
        $channels['h']->getValue(),
        $channels['s']->getValue(),
        $channels['l']->getValue()
      );
    }
  }

  public function getCssText($options=array())
  {
		if(isset($options['color_mode'])) {
			switch($options['color_mode']) {
				case 'hex':
					if($value = $this->getHexValue()) return $value;
					break;
				case 'X11':
					if($value = $this->getX11Color()) return $value;
					break;
				case 'rgb':
				case 'rgba':
					$this->toRgb();
					break;
				case 'hsl':
				case 'hsla':
					$this->toHsl();
					break;
			}
		}
    return $this->mode.'('.implode(',', $this->channels).')';
  }

  public function __clone()
  {
    foreach($this->channels as $key => $value)
    {
      $this->channels[$key] = clone $value;
    }
  }
}
