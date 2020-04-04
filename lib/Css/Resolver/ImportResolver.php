<?php declare(strict_types=1);

namespace ju1ius\Css\Resolver;

use ju1ius\Css\Exception\StyleSheetNotFoundException;
use ju1ius\Css\Lexer;
use ju1ius\Css\Loader;
use ju1ius\Css\Parser;
use ju1ius\Css\Rule;
use ju1ius\Css\StyleSheet;

/**
 * Recursively resolve @import rules
 * and merge their associated stylesheets into the main StyleSheet
 *
 **/
class ImportResolver
{
    private $stylesheet;

    public function __construct(StyleSheet $stylesheet, $base_url = null)
    {
        $this->stylesheet = $stylesheet;
        $this->base_url = $base_url;
    }

    public function resolve($imported_files = [])
    {
        if (empty($imported_files)) {
            $imported_files[] = $this->stylesheet->getHref();
        }
        $encoding = $this->stylesheet->getCharset();
        $lexer = new Lexer();
        $parser = new Parser($lexer);
        $url_resolver = new UrlResolver($this->stylesheet, $this->base_url);
        $url_resolver->resolve();

        foreach ($this->stylesheet->getRuleList()->getRules() as $rule) {
            if ($rule instanceof Rule\Import) {
                $url = $rule->getHref()->getUrl()->getString();
                // Take care of circular imports !
                if (in_array($url, $imported_files)) {
                    continue;
                } else {
                    $imported_files[] = $url;
                }
                try {
                    $source = Loader::load($url, $encoding);
                } catch (StyleSheetNotFoundException $e) {
                    // FIXME: should we remove the rule ?
                    continue;
                }
                // Do the parsing
                $lexer->setSource($source);
                $stylesheet = $parser->parseStyleSheet();

                // Remove charset rules
                $rule_list = $stylesheet->getRuleList();
                foreach ($rule_list->getItems() as $sub_rule) {
                    if ($sub_rule instanceof Rule\Charset) {
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
                if ($rule->getMediaQueryList() !== null && !$rule->getMediaQueryList()->isEmpty()) {
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
