<?php
namespace ju1ius\CSS\Resolver;

use ju1ius\CSS\StyleSheet;
use ju1ius\CSS\StyleSheetLoader;
use ju1ius\CSS\Parser;
use ju1ius\CSS\Rule;
use ju1ius\CSS\Util;

/**
 * Recursively resolve @import rules
 * and merge their associated stylesheets into the main StyleSheet
 *
 * @package CSS
 * @subpackage Resolver
 **/
class ImportResolver
{
  private
    $stylesheet;

  public function __construct(StyleSheet $stylesheet, $base_url=null)
  {
    $this->stylesheet = $stylesheet;
    $this->base_url = $base_url;
  }

  public function resolve($imported_files=array())
  {
    if(empty($imported_files)) {
      $imported_files[] = $this->stylesheet->getHref();
    }
    $main_charset = mb_strtolower(
      $this->stylesheet->getCharset(),
      $this->stylesheet->getCharset()
    );
    $loader = new StyleSheetLoader();
    $parser = new Parser();

    $url_resolver = new UrlResolver($this->stylesheet, $this->base_url);
    $url_resolver->resolve();

    foreach($this->stylesheet->getRuleList()->getRules() as $rule) {
      if($rule instanceof Rule\Import) {
        $url = $rule->getHref()->getUrl()->getString();
        // Take care of circular imports !
        if(in_array($url, $imported_files)) {
          continue;
        } else {
          $imported_files[] = $url;
        }
        if(Util\URL::isAbsUrl($url)) {
          $info = $loader->loadUrl($url);
        } else {
          $info = $loader->loadFile($url);
        }
        // if imported file is in another charset,
        // we need to convert it
        if(mb_strtolower($info->getCharset(), $main_charset) !== $main_charset) {
          $converted = Util\Charset::convert(
            $info->getContent(),
            $info->getCharset(),
            $main_charset
          );
          $info->setContent($converted);
          $info->setCharset($main_charset);
        }
        // Do the parsing
        $stylesheet = $parser->parse($info);

        // Remove charset rules
        $rule_list = $stylesheet->getRuleList();
        foreach($rule_list->getItems() as $sub_rule) {
          if($sub_rule instanceof Rule\Charset) {
            $stylesheet->getRuleList()->remove($sub_rule);
            break; //TODO: is this needed ?
          }
        }
        $rule_list->resetKeys();
        
        // Do the recurse
        $resolver = new ImportResolver($stylesheet);
        $resolver->resolve($imported_files);

        // Wrap into media query if needed
        if($rule->getMediaList() !== null && !$rule->getMediaList()->isEmpty()) {
          $media_query = new Rule\Media(
            $rule->getMediaList(),
            $rule_list
          );
          $this->stylesheet->getRuleList()->replace($rule, $media_query);
        } else {
          $this->stylesheet->getRuleList()->replace(
            $rule,
            $rule_list->getItems()
          );
        }
      } 
    }
  }
}
