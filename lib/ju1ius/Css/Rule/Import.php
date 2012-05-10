<?php
namespace ju1ius\Css\Rule;

use ju1ius\Css\Rule;
use ju1ius\Css\Value\Url;
use ju1ius\Css\MediaQueryList;
use ju1ius\Css\StyleSheet;

/**
 * Represents an @import rule
 **/
class Import extends Rule
{
  private $href;
  private $media_list;
	private $styleSheet;

  public function __construct(Url $href, MediaQueryList $media_list=null)
  {
    $this->href = $href;
    $this->media_list = $media_list;
  }

  public function getHref()
  {
    return $this->href;
  }
  public function setHref(Url $url)
  {
    $this->href = $url;
  }

  public function getMediaQueryList()
  {
    return $this->media_list;
  }
  public function setMediaQueryList(MediaQueryList $media_list)
  {
    $this->media_list = $media_list;
  }

  public function getCssText($options=array())
  {
    $mediaText = $this->media_list->getCssText();
    return "@import " . $this->href->getCssText()
      . ($mediaText ? ' '.$mediaText : '')
      .';';
	}

	public function getStyleSheet()
	{
		if($this->styleSheet === null)
		{
			$this->styleSheet = $this->loadStyleSheet();
		}
		return $this->styleSheet;
	}

  public function getType()
  {
    return self::IMPORT_RULE;
	}

	private function loadStyleSheet()
	{
		return new StyleSheet();
	}

  public function __clone()
  {
    $this->href = clone $this->href;
    $this->media_list = clone $this->media_list;
  }
}
