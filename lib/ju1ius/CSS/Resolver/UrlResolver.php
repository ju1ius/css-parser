<?php
namespace ju1ius\CSS\Resolver;

use ju1ius\CSS\StyleSheet;
use ju1ius\CSS\Iterator\ValueIterator;
use ju1ius\CSS\Util;
use ju1ius\CSS\Value;

/**
 * 
 */
class UrlResolver
{
  private
    $stylesheet;

  public function __construct(StyleSheet $stylesheet, $base_url=null)
  {
    $this->stylesheet = $stylesheet;
    $this->base_url = $base_url;
    if(!$this->base_url) {
      $href = $this->stylesheet->getHref();
      $this->base_url = Util\URL::dirname($this->stylesheet->getHref());
      if(!$this->base_url) {
        throw new \RuntimeException("You must provide a valid base url");
      }
    }
  }

  public function resolve()
  {
    $it = new ValueIterator($this->stylesheet, 'ju1ius\CSS\Value\URL', true);
    $bIsAbsBaseUrl = Util\URL::isAbsUrl($this->base_url) || Util\URL::isAbsPath($this->base_url);
    foreach($it as $value) {
      $url = $value->getUrl()->getString();
      $isAbsPath = Util\URL::isAbsPath($url);
      $isAbsUrl = Util\URL::isAbsUrl($url);
      // resolve only if:
      if(!$isAbsUrl && !$isAbsPath){
        // $url is not absolute url or absolute path
        $url = Util\URL::joinPaths($this->base_url, $url);
        $value->setUrl(new Value\String($url));
      } else if($isAbsPath && $bIsAbsBaseUrl) {
        // $url is absolute path and base url is absolute
        // get the base domain from url
        $base_url = preg_replace("#^(\w+://.*?)/.*#u", "$1", $this->base_url);
        $url = Util\URL::joinPaths($base_url, $url);
        $value->setUrl(new Value\String($url));
      }
    }
  }
}
