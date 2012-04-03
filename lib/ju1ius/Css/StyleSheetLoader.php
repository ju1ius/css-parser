<?php
namespace ju1ius\Css;

use ju1ius\Uri;
use ju1ius\Collections\ParameterBag;
use ju1ius\Text\Source;

/**
 * Handles loading of stylesheets.
 *
 * Relies on the CURL extension to load network urls
 **/
class StyleSheetLoader
{
  private
    $options;

  public function __construct($options=array())
  {
    $this->options = new ParameterBag(array(
      'encoding' => null
    ));
    $this->options->merge($options);  
  }

  /**
   * Returns the options of the current instance.
   *
   * @return ParameterBag The current instance's ParameterBag
   **/
  public function getOptions()
  {
    return $this->options;
  }

  /**
   * Loads a Css file or string.
   *
   * If passed an absolute url or a filesystem path, returns a Source\File object.
   * If passed a Css string, returns a Source\String object.
   *
   * @param string|ju1ius\Uri $url_or_string
   *
   * @return Source\String|Source\File
   **/
  public function load($url_or_string)
  {
    $uri = Uri::parse($url_or_string);
    if($uri->isAbsoluteUrl()) {
      return $this->loadUrl($uri);
    }
    $path = realpath($uri);
    if(is_file($path)) {
      return $this->loadFile($uri);
    }
    return $this->loadString($url_or_string);
  }

  /**
   * Loads a CSS file into a Source\File object.
   *
   * @param string|ju1ius\Uri $url The path to the file
   * @return Source\File
   **/
  public function loadFile($url)
  {
    $uri = Uri::parse($url);
    $path = realpath($uri);
    $content = file_get_contents($path);
    if(false === $content) {
      throw new \RuntimeException(
        sprintf('Could not load file: "%s"', $path)
      );
    }
    $infos = self::_loadString($content);
    // Convert encoding if encoding option has been set
    self::maybeConvertEncoding($infos, $this->options->get('encoding'));
    return new Source\File($path, $infos['contents'], $infos['charset']);
  }

  /**
   * Loads a CSS file into a Source\File object.
   *
   * Relies on the CURL extension to load network urls
   *
   * @param string|ju1ius\Uri $url The url of the file
   * @param boolean           $preferFileCharset If false, use the content-type header for charset detection
   * @return Source\File
   **/
  public function loadUrl($url, $preferFileCharset=false)
  {
    $uri = Uri::parse($url);
    $response = self::_loadUrl($uri);
    $content = $response['body'];
    $charset = $response['charset'];

    $infos = self::_loadString($response['body']);
    if($response['charset'] && !$preferFileCharset) {
      $infos['charset'] = $response['charset'];
    }
    // Convert encoding if encoding option has been set
    self::maybeConvertEncoding($infos, $this->options->get('encoding'));
    return new Source\File($url, $infos['contents'], $infos['charset']);
  }

  /**
   * Loads a CSS string into a Source\String object.
   *
   * @param string $str  The CSS string
   * @return StyleSheetInfo
   **/
  public function loadString($str)
  {
    $infos = self::_loadString($str);
    // Convert encoding if encoding option has been set
    self::maybeConvertEncoding($infos, $this->options->get('encoding'));
    return new Source\String($infos['contents'], $infos['charset']);
  }

  static private function _loadString($str)
  {
    // detect charset from BOM and/or @charset rule
    $charset = Util\Charset::detect($str);
    // Or defaults to utf-8
    if(!$charset) {
      $charset = 'utf-8';
    }
    $str = Util\Charset::removeBOM($str);

    return array(
      'contents' => $str,
      'charset' => $charset
    );
  }

  static private function _loadUrl(Uri $url)
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
      throw new \RuntimeException(curl_error($curl));
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

  static private function maybeConvertEncoding(array &$infos, $to_encoding=null)
  {
    if($to_encoding && !Util\Charset::isSameEncoding($infos['charset'], $to_encoding)) {
      $infos['contents'] = Util\Charset::convert($infos['contents'], $to_encoding, $infos['charset']);
      $infos['charset'] = $to_encoding;
    }
  }
}
