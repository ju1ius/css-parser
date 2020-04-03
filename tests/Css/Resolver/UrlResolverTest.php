<?php

namespace ju1ius\Tests\Css\Resolver;

use ju1ius\Css\Resolver\UrlResolver;
use ju1ius\Tests\CssParserTestCase;

class UrlResolverTest extends CssParserTestCase
{
    /**
     * @dataProvider resolveUrlsProvider
     **/
    public function testResolveUrls($input, $base_url, $expected)
    {
        $styleSheet = $this->parseStyleSheet($input);
        $resolver = new UrlResolver($styleSheet, $base_url);
        $resolver->resolve();
        $this->assertEquals($expected, $styleSheet->getCssText());
    }

    public function resolveUrlsProvider()
    {
        return [
            // relative paths && absolute base_url
            [
                '@import "foo/bar.css"; p{ background: url(../img/foobar.png) }',
                'http://foo.com/css',
                '@import url("http://foo.com/css/foo/bar.css");p{ background: url("http://foo.com/css/../img/foobar.png"); }',
            ],
            // relative paths && base_url is absolute path
            [
                '@import "foo/bar.css"; p{ background: url(../img/foobar.png) }',
                '/foodir/css',
                '@import url("/foodir/css/foo/bar.css");p{ background: url("/foodir/css/../img/foobar.png"); }',
            ],
            // relative paths && base_url is relative
            [
                '@import "foo/bar.css"; p{ background: url(../img/foobar.png) }',
                'foodir/css',
                '@import url("foodir/css/foo/bar.css");p{ background: url("foodir/css/../img/foobar.png"); }',
            ],
            // absolute paths && absolute base_url
            [
                '@import "/css/bar.css"; p{ background: url(/img/foobar.png) }',
                'http://foo.com/bar',
                '@import url("http://foo.com/css/bar.css");p{ background: url("http://foo.com/img/foobar.png"); }',
            ],
            // absolute paths && base_url is absolute path
            [
                '@import "/css/bar.css"; p{ background: url(http://foo.com/img/foobar.png) }',
                '/srv/www/',
                '@import url("/css/bar.css");p{ background: url("http://foo.com/img/foobar.png"); }',
            ],
        ];
    }
}
