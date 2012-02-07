<?php
namespace ju1ius\CSS;

/**
 * 
 */
class StyleSheetLoader
{
  private
    $options = array();

  public function __construct($options=array())
  {
    $this->setOptions($options);  
  }

  public function loadFile($path)
  {
    $path = realpath($path);
    $content = file_get_contents($path);
    if(false === $content) {
      throw new \RuntimeException("Could not load file: $path");
    }
    $info = $this->loadString($content);
    $info->setUrl($path);
    return $info;
  }

  public function loadUrl($url, $preferFileCharset=false)
  {
    $response = Util\URL::loadURL($url);
    $content = $response['body'];
    $charset = $response['charset'];
    $info = $this->loadString($content);
    $info->setUrl($url);
    if($charset && !$preferFileCharset) {
      $info->setCharset($charset);
    }
    return $info;
  }

  public function loadString($str)
  {
    // detect charset from BOM and/or @charset rule
    $charset = Util\Charset::detectCharset($str);
    // Or defaults to utf-8
    if(!$charset) {
      $charset = 'utf-8';
    }
    $str = Util\Charset::removeBOM($str);
    return new StyleSheetInfo(null, $str, $charset);
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
   * @return ju1ius\CSS\StyleSheetLoader The current CSS\StyleSheetLoader instance
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
   * @return ju1ius\CSS\StyleSheetLoader The current StyleSheetLoader instance
   **/
  public function setOptions(array $options) 
  {
    $this->options = array_merge($this->options, $options);
    return $this;
  }
}
