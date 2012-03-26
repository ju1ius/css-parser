<?php
namespace ju1ius\Css;

use ju1ius\Uri;

/**
 * Handles loading of stylesheets
 *
 * Relies on the CURL extension to load network urls
 */
class StyleSheetLoader
{
  private
    $options = array();

  public function __construct($options=array())
  {
    $this->setOptions($options);  
  }

  public function load($url)
  {
    $uri = Uri::parse($url);
    if($uri->isAbsoluteUrl()) {
      return $this->loadUrl($uri);
    }
    return $this->loadFile($uri);
  }

  /**
   * Loads a CSS file into a StyleSheetInfo object.
   *
   * @param string|ju1ius\Uri The path to the file
   * @return StyleSheetInfo
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
    $info = $this->loadString($content);
    $info->setUrl($path);
    return $info;
  }

  /**
   * Loads a CSS file into a StyleSheetInfo object.
   *
   * Relies on the CURL extension to load network urls
   *
   * @param string|ju1ius\Uri $url The url of the file
   * @param boolean           $preferFileCharset If false, use the content-type header for charset detection
   * @return StyleSheetInfo
   **/
  public function loadUrl($url, $preferFileCharset=false)
  {
    $uri = Uri::parse($url);
    $response = self::_loadUrl($uri);
    $content = $response['body'];
    $charset = $response['charset'];
    $info = $this->loadString($content);
    $info->setUrl($url);
    if($charset && !$preferFileCharset) {
      $info->setCharset($charset);
    }
    return $info;
  }

  /**
   * Loads a CSS string into a StyleSheetInfo object.
   *
   * @param string The CSS string
   * @return StyleSheetInfo
   **/
  public function loadString($str)
  {
    // detect charset from BOM and/or @charset rule
    $charset = Util\Charset::detect($str);
    // Or defaults to utf-8
    if(!$charset) {
      $charset = 'utf-8';
    }
    $str = Util\Charset::removeBOM($str);
    return new StyleSheetInfo(null, $str, $charset);
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
