<?php

namespace ju1ius\Css;

use ju1ius\Text\Source;
use ju1ius\Css\Util\Charset;

class ParserFactory
{
  /**
   * Factory method that instatiate either an AsciiParser or Parser,
   * depending on the source encoding
   *
   * @param Source\String $source
   * @param array $options
   *
   * @return Parser
   **/
  static public function createParser(Source\String $source, array $options=array())
  {
    $encoding = $source->getEncoding();
    if(Charset::isSameEncoding($encoding, 'ascii')) {
      return new AsciiParser($options);
    } else {
      return new Parser($options);
    }
  }
}
