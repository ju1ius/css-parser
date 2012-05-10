<?php
namespace ju1ius\Css;

use ju1ius\Uri;
use ju1ius\Text\Source;

use ju1ius\Css\Exception\StyleSheetNotFoundException;

/**
 * Handles loading of stylesheets.
 * If the source is not ASCII or UTF-8 encoded, it will be converted to UTF-8.
 *
 * Relies on the CURL extension to load network urls
 **/
class StyleSheetLoader
{

  /**
   * Loads a Css file or url.
   *
   * @param string|ju1ius\Uri $url
   *
   * @throws StyleSheetNotFoundException if file doesn't exist or is not readable
   *
   * @return Source\File
   **/
  public static function load($url)
  {
    $uri = Uri::parse($url);
    if($uri->isAbsoluteUrl()) {
      return self::loadUrl($uri);
    }
    return self::loadFile($uri);
  }

  /**
   * Loads a CSS file into a Source\File object.
   *
   * @param string|ju1ius\Uri $url The path to the file
   *
   * @throws StyleSheetNotFoundException if file doesn't exist or is not readable
   *
   * @return Source\File
   **/
  public static function loadFile($url)
  {
    $uri = Uri::parse($url);
    $path = realpath($uri);
    if(false === $path || !is_file($path) || !is_readable($path)) {
      throw new StyleSheetNotFoundException($path);
    }
    $content = file_get_contents($path);
    if(false === $content) {
      throw new StyleSheetNotFoundException($path);
    }
    $infos = self::_loadString($content);
    return new Source\File($path, $infos['contents'], $infos['charset']);
  }

  /**
   * Loads a CSS file into a Source\File object.
   *
   * Relies on the CURL extension to load network urls
   *
   * @param string|ju1ius\Uri $url The url of the file
   *
   * @throws StyleSheetNotFoundException if file doesn't exist or is not readable
   *
   * @return Source\File
   **/
  public static function loadUrl($url)
  {
    $uri = Uri::parse($url);
    $response = self::_loadUrl($uri);
    $content = $response['body'];
    $charset = $response['charset'];

    $infos = self::_loadString($response['body']);
    // FIXME: Http header sometimes return wrong results
    /*
    if($response['charset'] && !$preferFileCharset) {
      $infos['charset'] = $response['charset'];
    }
     */
    return new Source\File($url, $infos['contents'], $infos['charset']);
  }

  /**
   * Loads a CSS string into a Source\String object.
   *
   * @param string $str  The CSS string
   *
   * @return Source\String
   **/
  public static function loadString($str, $encoding=null)
  {
    $infos = self::_loadString($str);
    return new Source\String($infos['contents'], $infos['charset']);
  }

  private static function _loadString($str)
  {
    // detect charset from BOM and/or @charset rule
    $charset = Util\Charset::detect($str);
    // Or defaults to utf-8
    if(!$charset) $charset = 'utf-8';
    $str = Util\Charset::removeBOM($str);

    if (!Util\Charset::isSameEncoding($charset, 'ascii')
        || !Util\Charset::isSameEncoding($charset, 'utf-8')
    ) {
      $str = Util\Charset::convert($str, 'utf-8', $charset);
      $charset = 'utf-8';
    }

    return array(
      'contents' => $str,
      'charset' => $charset
    );
  }

  private static function _loadUrl(Uri $url)
  {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    //curl_setopt($curl, CURLOPT_HEADER, true);
    curl_setopt($curl, CURLOPT_ENCODING, 'deflate,gzip');
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_USERAGENT, 'ju1ius/CssParser v0.1');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($curl);
    if(false === $response) {
      throw new StyleSheetNotFoundException($url, curl_error($curl));
    };
    $infos = curl_getinfo($curl);

    curl_close($curl);

    $results = array(
      'charset' => null,
      'body' => $response  
    );
    if($infos['content_type']) {
      if(preg_match('/charset=([a-zA-Z0-9-]*)/', $infos['content_type'], $matches)) {
        $results['charset'] = $matches[0];
      }
    }
    return $results;
  }

  private static function maybeConvertEncoding(array &$infos, $to_encoding=null)
  {
    if (Util\Charset::isSameEncoding($infos['charset'], 'ascii')) return;
    if (!Util\Charset::isSameEncoding($infos['charset'], 'utf-8')) {
      $infos['contents'] = Util\Charset::convert($infos['contents'], 'utf-8', $infos['charset']);
      $infos['charset'] = 'utf-8';
    }
  }
}
