<?php
namespace ju1ius\Css;

use ju1ius\Uri;
use ju1ius\Text\Source;

/**
 * Handles loading of stylesheets.
 *
 * Relies on the CURL extension to load network urls
 **/
class StyleSheetLoader
{
  private
    $options = array();

  public function __construct($options=array())
  {
    $this->setOptions($options);  
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

  /**
   * Gets an option value.
   *
   * @param  string $name    The option name
   * @param  mixed  $default The default value (null by default)
   *
   * @return mixed  The option value or the default value
   */
  public function getOption($name, $default=null)
  {
    return isset($this->options[$name]) ? $this->options[$name] : $default;
  }
  /**
   * Sets an option value.
   *
   * @param  string $name  The option name
   * @param  mixed  $value The default value
   *
   * @return ju1ius\Css\StyleSheetLoader The current Css\StyleSheetLoader instance
   */
  public function setOption($name, $value)
  {
    $this->options[$name] = $value;
    return $this;
  }

  /**
   * Returns the options of the current instance.
   *
   * @return array The current instance's options
   **/
  public function getOptions()
  {
    return $this->options;
  }

  /**
   * Merge given options with the current options
   *
   * @param array $aOptions The options to merge
   *
   * @return ju1ius\Css\StyleSheetLoader The current StyleSheetLoader instance
   **/
  public function setOptions(array $options) 
  {
    $this->options = array_merge($this->options, $options);
    return $this;
  }
}
