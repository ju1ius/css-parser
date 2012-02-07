<?php
require_once __DIR__.'/../../CSSParser_TestCase.php';

use ju1ius\CSS\Resolver\UrlResolver;
use ju1ius\CSS\Value;

class UrlResolverTest extends CSSParser_TestCase
{
  /**
   * @dataProvider testResolveUrlsProvider
   **/
  public function testResolveUrls($input, $base_url, $expected)
  {
    $parser = $this->createParser();
    $styleSheet = $parser->parseStyleSheet($input);
    $resolver = new UrlResolver($styleSheet, $base_url);
    $resolver->resolve();
    $this->assertEquals($expected, $styleSheet->getCssText());
  }
  public function testResolveUrlsProvider()
  {
    return array(
      // relative paths && absolute base_url
      array(
        '@import "foo/bar.css"; p{ background: url(../img/foobar.png) }',
        'http://foo.com/css',
        '@import url("http://foo.com/css/foo/bar.css");p{ background: url("http://foo.com/css/../img/foobar.png"); }'
      ),
      // relative paths && base_url is absolute path
      array(
        '@import "foo/bar.css"; p{ background: url(../img/foobar.png) }',
        '/foodir/css',
        '@import url("/foodir/css/foo/bar.css");p{ background: url("/foodir/css/../img/foobar.png"); }'
      ),
      // relative paths && base_url is relative
      array(
        '@import "foo/bar.css"; p{ background: url(../img/foobar.png) }',
        'foodir/css',
        '@import url("foodir/css/foo/bar.css");p{ background: url("foodir/css/../img/foobar.png"); }'
      ),
      // absolute paths && absolute base_url
      array(
        '@import "/css/bar.css"; p{ background: url(/img/foobar.png) }',
        'http://foo.com/bar',
        '@import url("http://foo.com/css/bar.css");p{ background: url("http://foo.com/img/foobar.png"); }'
      ),
      // absolute paths && base_url is absolute path
      array(
        '@import "/css/bar.css"; p{ background: url(http://foo.com/img/foobar.png) }',
        '/srv/www/',
        '@import url("/srv/www/css/bar.css");p{ background: url("http://foo.com/img/foobar.png"); }'
      ),
    );
  }
}
