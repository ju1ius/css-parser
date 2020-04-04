<?php declare(strict_types=1);

namespace ju1ius\Css\Resolver;

use ju1ius\Css\Iterator\ValueIterator;
use ju1ius\Css\StyleSheet;
use ju1ius\Css\Value;
use ju1ius\Uri;
use RuntimeException;

/**
 * Resolves relative urls in a stylesheet,
 * using a base url or the stylesheet's href
 **/
class UrlResolver
{
    private $stylesheet;

    public function __construct(StyleSheet $stylesheet, $base_url = null)
    {
        $this->stylesheet = $stylesheet;
        if (!$base_url && !$this->stylesheet->getHref()) {
            throw new RuntimeException(
                "The provided stylesheet has no href, you must provide a base url"
            );
        } elseif (!$base_url) {
            $href = new Uri($this->stylesheet->getHref());
            $this->base_url = $href->dirname();
            if (!$this->base_url) {
                throw new RuntimeException("You must provide a valid base url");
            }
        } elseif ($base_url instanceof Uri) {
            $this->base_url = $base_url;
        } else {
            $this->base_url = new Uri($base_url);
        }
    }

    public function resolve()
    {
        $it = new ValueIterator($this->stylesheet, 'ju1ius\Css\Value\Url', true);
        $bIsAbsBaseUrl = $this->base_url->isAbsoluteUrl() || $this->base_url->isAbsolutePath();
        foreach ($it as $value) {
            $url = new Uri($value->getUrl()->getString());
            $isAbsPath = $url->isAbsolutePath();
            $isAbsUrl = $url->isAbsoluteUrl();
            // resolve only if:
            if (!$isAbsUrl && !$isAbsPath) {
                // $url is not absolute url or absolute path
                $url = $this->base_url->join($url);
                $value->setUrl(new Value\CssString((string)$url));
            } elseif ($isAbsPath && $bIsAbsBaseUrl) {
                // $url is absolute path and base url is absolute
                // get the base domain from url
                $base_url = $this->base_url->getRootUrl();
                $url = $base_url->join($url);
                $value->setUrl(new Value\CssString((string)$url));
            }
        }
    }
}
