<?php
namespace CSS\Rule;

use CSS\Rule;
use CSS\Value\URL;
use CSS\MediaList;
use CSS\StyleSheet;

/**
 * Represents an @import rule
 * @package CSS
 * @subpackage Rule
 **/
class Import extends Rule
{
  private $href;
  private $mediaList;
	private $styleSheet;

  public function __construct(URL $href, MediaList $mediaList=null)
  {
    $this->href = $href;
    $this->mediaList = $mediaList;
  }

  public function getHref()
  {
    return $this->href;
  }
  public function setHref(URL $url)
  {
    $this->href = $url;
  }

  public function getMediaList()
  {
    return $this->mediaList;
  }
  public function setMediaList(MediaList $mediaList)
  {
    $this->mediaList = $mediaList;
  }

  public function getCssText($options=array())
  {
    $mediaText = $this->mediaList->getCssText();
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
    $this->mediaList = clone $this->mediaList;
  }
}
