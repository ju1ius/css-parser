<?php
namespace ju1ius\Css\Resolver;

use ju1ius\Uri;
use ju1ius\Text\Source;

use ju1ius\Css\StyleSheet;
use ju1ius\Css\StyleSheetLoader;
use ju1ius\Css\Parser;
use ju1ius\Css\Rule;
use ju1ius\Css\Util;

/**
 * Recursively resolve @import rules
 * and merge their associated stylesheets into the main StyleSheet
 *
 * @package Css
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
    $main_charset = $this->stylesheet->getCharset();
    $main_charset = mb_strtolower($main_charset, $main_charset);
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
        $source = $loader->load($url);
        // if imported file is in another charset,
        // we need to convert it
        if(mb_strtolower($source->getEncoding(), $main_charset) !== $main_charset) {
          $converted = Util\Charset::convert(
            $source->getContents(),
            $source->getEncoding(),
            $main_charset
          );
          $source = new Source\File($url, $converted, $main_charset);
        }
        // Do the parsing
        $stylesheet = $parser->parse($source);

        // Remove charset rules
        $rule_list = $stylesheet->getRuleList();
        foreach($rule_list->getItems() as $sub_rule) {
          if($sub_rule instanceof Rule\Charset) {
            $stylesheet->getRuleList()->remove($sub_rule);
            // Only one charset is allowed, so we can
            break;
          }
        }
        $rule_list->resetKeys();
        
        // Do the recurse
        $resolver = new ImportResolver($stylesheet);
        $resolver->resolve($imported_files);

        // Wrap into media query if needed
        if($rule->getMediaQueryList() !== null && !$rule->getMediaQueryList()->isEmpty()) {
          $media_query = new Rule\Media(
            $rule->getMediaQueryList(),
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
