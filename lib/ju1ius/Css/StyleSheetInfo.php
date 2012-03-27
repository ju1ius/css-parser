<?php
namespace ju1ius\Css;

use ju1ius\Text\Source\File;

/**
 * 
 */
class StyleSheetInfo extends File
{
  public function getCharset()
  {
    return $this->encoding;
  }
}
