<?php
namespace ju1ius\Css\Value;

use ju1ius\Css\Util;

/**
 * @package Css
 * @subpackage Value
 **/
class Color extends PrimitiveValue
{
  private $channels = array(
    'r' => 0, 'g' => 0, 'b' => 0 
  );
  private $mode;

  public function __construct($color=null)
  {
    if(is_array($color))
    {
      if(isset($color['r'], $color['g'], $color['b']))
      {
        $this->fromRGB($color);
      }
      else if(isset($color['h'], $color['s'], $color['l']))
      {
        $this->fromHSL($color);
      }
    }
    else if(is_string($color))
    {
      if($rgb = Util\Color::namedColor2rgb($color))
      {
        $this->fromRGB($rgb);
      }
      else if($rgb = Util\Color::hex2rgb($color))
      {
        $this->fromRGB($rgb);
      }
    }
  }

  public function getMode()
  {
    return $this->mode;
  }

  public function fromRGB(Array $rgb)
  {
    $mode = 'rgb';
    foreach(array('r', 'g', 'b', 'a') as $channel)
    {
      if($channel === 'a')
      {
				if(!isset($rgb['a'])) continue;
				$value = Util\Color::constrainValue((string)$rgb['a'], 0, 1);
				if($value === 1) continue;
				$mode .= 'a';
			}
      else
      {
				$value = Util\Color::normalizeRGBValue((string)$rgb[$channel]);
			}
      $this->channels[$channel] = new Dimension($value);
    }
		$this->mode = $mode;
    return $this;
  }

  public function fromHSL(Array $hsl)
  {
    $rgb = Util\Color::hsl2rgb(
			(string)$hsl['h'],
			(string)$hsl['s'],
			(string)$hsl['l'],
      isset($hsl['a']) ? (string)$hsl['a'] : 1
    );
    return $this->fromRGB($rgb);
  }

  public function fromHex($value)
  {
    $rgb = Util\Color::hex2rgb($value);
    return $this->fromRGB($rgb);
  }

  public function fromNamedColor($value)
  {
    $rgb = Util\Color::namedColor2rgb($value);
    return $this->fromRGB($rgb);
  }

  public function toRGB()
  {
    $mode = $this->mode;
    $channels = $this->channels;
    
    if(!$mode || $mode === 'rgb') return;
    if($mode === 'rgba')
    {
      // If we don't need alpha channel, drop it
      if($channels['a']->getValue() >= 1) {
        unset($this->channels['a']);
        $this->mode = 'rgb';
      }
      return;
    }
    $rgb = Util\Color::hsl2rgb(
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

  public function toHSL()
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

  public function getNamedColor()
  {
    $this->toRGB();
		$channels = $this->channels;
		var_dump($channels);
    return Util\Color::rgb2NamedColor(
      $channels['r']->getValue(),
      $channels['g']->getValue(),
      $channels['b']->getValue()
    );
  }

  public function getHexValue()
  {
    $channels = $this->channels;
    if(isset($channels['a']) && $channels['a']->getValue() !== 1) return null;
    if($this->mode === 'rgb')
    {
      return Util\Color::rgb2hex(
        $channels['r']->getValue(),
        $channels['g']->getValue(),
        $channels['b']->getValue()
      );
    }
    else if($this->mode === 'hsl')
    {
      return Util\Color::hsl2hex(
        $channels['h']->getValue(),
        $channels['s']->getValue(),
        $channels['l']->getValue()
      );
    }
  }

  public function getCssText($options=array())
  {
		if(isset($options['color_mode']))
		{
			switch($options['color_mode'])
			{
				case 'hex':
					if($value = $this->getHexValue()) return $value;
					break;
				case 'X11':
					if($value = $this->getNamedColor()) return $value;
					break;
				case 'rgb':
				case 'rgba':
					$this->toRGB();
					break;
				case 'hsl':
				case 'hsla':
					$this->toHSL();
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
