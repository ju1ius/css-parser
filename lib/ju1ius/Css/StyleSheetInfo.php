<?php
namespace ju1ius\Css;

/**
 * 
 */
class StyleSheetInfo
{
  private
    $url,
    $charset,
    $content;

  public function __construct($url, $content, $charset="utf-8")
  {
    $this->url = $url;
    $this->content = $content;
    $this->charset = $charset;
  }
  
  public function setUrl($url)
  {
    $this->url = $url;
  }
  public function getUrl()
  {
    return $this->url;
  }

  public function setCharset($charset)
  {
    $this->charset = $charset;
  }
  public function getCharset()
  {
    return $this->charset;
  }

  public function setContent($content)
  {
    $this->content = $content;
  }
  public function getContent()
  {
    return $this->content;
  }
}
