<?php

namespace ju1ius\CSS;

/**
 * 
 */
class StyleSheetList extends CSSList
{
  private
    $dom,
    $xpath;

  public function __construct(\DOMDocument $dom, $stylesheets=array())
  {
    $this->attach($dom);
    $this->items = $stylesheets;
  }

  /**
   * Attaches this StyleSheetList to a DOMDocument instance
   *
   * @param \DOMDocument $dom
   **/
  public function attach(\DOMDocument $dom)
  {
    $this->dom = $dom;
    $this->xpath = new \DOMXPath($dom);
  }

  /**
   * Loads stylesheets from the DOMDocument
   *
   * @todo Find a way to restrict loading to matching MediaQuery
   **/
  public function load()
  {
    $loader = new StyleSheetLoader();
    $parser = new Parser();
    $xpath = new \DOMXPath($this->dom);
    $elements = $xpath->query('//link[@rel="stylesheet"]|//style');

    foreach($elements as $element) {
      if($element->tagName === "link") {

        $href = $element->getAttribute('href');
        $stylesheet = $parser->parse($loader->load($href));

        $media = $element->getAttribute('media');
        if($media) {
          $media_list = $parser->parseMediaQuery($media);
          $stylesheet->setMediaQueryList($media_list);
        }
        $this->stylesheets[] = $stylesheet;

      } else if($element->tagName === "style") {
        $this->stylesheets[] = $parser->parse(
          $loader->loadString($element->textContent)
        );
      }
    }
  }

  /**
   * Merges all stylesheets and returns the resulting stylesheet
   *
   * @todo handle charsets
   * @return StyleSheet
   **/
  public function merge()
  {
    $result = new StyleSheet();
    foreach($this->stylesheets as $stylesheet) {
      $rule_list = $stylesheet->getRuleList();
      $media_list = $stylesheet->getMediaQueryList();
      if($media_list && count($media_list)) {
        $media_rule = new Rule\Media(
          clone $media_list,
          clone $rule_list
        );
        $result->getRuleList()->append($media_rule);
      } else {
        $result->getRuleList()->extend($rule_list);
      }
    }
    return $result;
  }
}
