<?php
namespace ju1ius\CSS\Util;

define('OS_WIN32', defined('OS_WINDOWS') ? OS_WINDOWS : !strncasecmp(PHP_OS, 'win', 3));

/**
 * @package CSS
 * @subpackage Util
 **/
class URL
{

  /**
   * Requests the contents of an URL
   *
   * @param   string $url the URL to fetch
   * @return  array        an array in the form:
   *   'charset'  => the charset of the response as specified by the
   *                 HTTP Content-Type header, if specified
   *   'response' => the response body
   **/
  static public function loadURL($url)
  {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    //curl_setopt($curl, CURLOPT_HEADER, true);
    curl_setopt($curl, CURLOPT_ENCODING, 'deflate,gzip');
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_USERAGENT, 'cssparser v0.1');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    $infos = curl_getinfo($curl);
    curl_close($curl);
    if(false === $response) return false;
    $results = array(
      'charset' => null,
      'response' => $response  
    );
    if($infos['content_type'])
    {
      if(preg_match('/charset=([a-zA-Z0-9-]*)/', $infos['content_type'], $matches))
      {
        $results['charset'] = $matches[0];
      }
    }
    return $results;
  }

  /**
   * ju1ius\CSS\Util\URL::joinPaths( string $head, string $tail [, string $...] )
   *
   * @param   string $head the head component of the path
   * @param   string $tail at least one path component
   *
   * @return string       the resulting path
   **/
  static public function joinPaths()
  {
    $num_args = func_num_args();
    if($num_args < 1) return '';
    $args = func_get_args();
    if($num_args == 1) return rtrim($args[0], DIRECTORY_SEPARATOR);

    $head = array_shift($args);
    $head = rtrim($head, DIRECTORY_SEPARATOR);
    $output = array($head);
    foreach ($args as $arg)
    {
      $output[] = trim($arg, DIRECTORY_SEPARATOR);
    }
    return implode(DIRECTORY_SEPARATOR, $output);
  }

  /**
   * Returns boolean based on whether given path is absolute or not.
   *
   * @param   string  $path Given path
   * @return  boolean       True if the path is absolute, false if it is not
   */
  static public function isAbsPath($path)
  {
    if (preg_match('#(?:/|\\\)\.\.(?=/|$)#u', $path))
    {
      return false;
    }
    if (OS_WIN32)
    {
      return (($path[0] === '/') ||  preg_match('#^[a-zA-Z]:(\\\|/)#u', $path));
    }
    return ($path[0] === '/') || ($path[0] === '~');
  }

  /**
   * Tests if an URL is absolute
   *
   * @param  string  $url
   * @return boolean
   **/
  static public function isAbsURL($url)
  {
    return preg_match('#^(http|https|ftp)://#u', $url);
  }

  /**
   * Returns the parent path of an URL or path
   * 
   * @param   string $url an URL
   * @returns string       an URL
   **/
  static public function dirname($url)
  {
    $aURL = parse_url($url);
    if(isset($aURL['path']))
    {
      $path = dirname($aURL['path']);
      if($path === '/')
      {
        unset($aURL['path']);
      }
      else
      {
        $aURL['path'] = $path;
      }
    }
    return self::buildURL($aURL);
  }
  
  /**
   * Builds an URL from an array of URL parts
   *
   * @param  array  $aURL   URL parts in the format returned by parse_url
   * @return string         the builded URL
   * @see http://php.net/manual/function.parse-url.php 
   **/
  static public function buildURL(array $aURL)
  {
    $url = '';
    if(isset($aURL['scheme']))
    {
      $url .= $aURL['scheme'] . '://';
    }
    if(isset($aURL['user']))
    {
      $url .= $aURL['user'];
      if(isset($aURL['pass']))
      {
        $url .= ':' . $aURL['pass'];
      }
      $url .= '@';
    }
    if(isset($aURL['host']))
    {
      $url .= $aURL['host'];
    }
    if(isset($aURL['port']))
    {
      $url .= ':' . $aURL['port'];
    }
    if(isset($aURL['path']))
    {
      if(strpos($aURL['path'], '/') !== 0) $url .= '/';
      $url .= $aURL['path'];
    }
    if(isset($aURL['query']))
    {
      $url .= '?' . $aURL['query'];
    }
    if(isset($aURL['fragment']))
    {
      $url .= '#' . $aURL['fragment'];
    }
    return $url;
  }
}
