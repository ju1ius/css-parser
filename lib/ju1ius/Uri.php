<?php

namespace ju1ius;

define('OS_WIN32', defined('OS_WINDOWS') ? OS_WINDOWS : !strncasecmp(PHP_OS, 'win', 3));

/**
 * 
 */
class Uri
{
	private static $URI_COMPONENTS = array(
		'scheme','host','port','user','pass','path','query','fragment'
	);

	private
		$scheme,
		$host,
		$port,
		$user,
		$pass,
		$path,
		$query,
		$fragment;

	public function __construct($uri)
	{
		$parts = parse_url($uri);
		foreach(self::$URI_COMPONENTS as $key) {
			if(isset($parts[$key])) {
				$this->$key = $parts[$key];
			}
		}
	}

  static public function parse($uri)
  {
    if($uri instanceof self) {
      return $uri;
    } else if(is_string($uri)) {
      return new self($uri);
    }
    throw new \InvalidArgumentException(
      "Argument passed to ju1ius\Uri::parse must be a string or ju1ius\Uri instance"
    );
  }

  public function getScheme()
  {
    return $this->scheme;
  }
  public function setScheme($scheme)
  {
    $this->scheme = $scheme;
    return $this;
  }

  public function getHost()
  {
    return $this->host;
  }
  public function setHost($host)
  {
    $this->host = $host;
    return $this;
  }

  public function getPort()
  {
    return $this->port;
  }
  public function setPort($port)
  {
    $this->port = $port;
    return $this;
  }

  public function getUser()
  {
    return $this->user;
  }
  public function setUser($user)
  {
    $this->user = $user;
    return $this;
  }

  public function getPassword()
  {
    return $this->pass;
  }
  public function setPassword($pass)
  {
    $this->pass = $pass;
    return $this;
  }

  public function getPath()
  {
    return $this->path;
  }
  public function setPath($path)
  {
    $this->path = $path;
    return $this;
  }

  public function getQuery()
  {
    return $this->query;
  }
  public function setQuery($query)
  {
    if(is_array($query)) {
      $this->query = http_build_query($query); 
    } else {
      $this->query = $query;
    }
    return $this;
  }

  public function getFragment()
  {
    return $this->fragment;
  }
  public function setFragment($fragment)
  {
    $this->fragment = $fragment;
    return $this;
  }

  public function getUri()
  {
    $url = '';
    if($this->isWindowsDrive()) {
      $url .= $this->scheme . ':\\';
    } else if($this->scheme) {
      $url .= $this->scheme . '://';
    }
    if($this->user) {
      $url .= $this->user;
      if($this->pass) {
        $url .= ':' . $this->pass;
      }
      $url .= '@';
    }
    if($this->host) {
      $url .= $this->host;
    }
    if($this->port) {
      $url .= ':' . $this->port;
    }
    if($this->path) {
      if(!$this->isWindowsDrive()
        && $this->scheme
        && strpos($this->path, '/') !== 0
      ) {
        $url .= '/';
      }
      $url .= $this->path;
    }
    if($this->query) {
      $url .= '?' . $this->query;
    }
    if($this->fragment) {
      $url .= '#' . $this->fragment;
    }
    return $url;
  }

  public function __toString()
  {
    return $this->getUri();
  }

  public function getRootUrl()
  {
    $uri = clone $this;
    $uri->setPath(null)
        ->setQuery(null)
        ->setFragment(null);
    return $uri;
  }

  /**
   * Returns the query variables as an array
   *
   * @return array
   */
  public function getQueryVariables()
  {
    parse_str($this->query, $result);
    return $result;
  }

  /**
   * Sets the query string to the specified variable in the query string.
   *
   * @param array $array (name => value) array
   *
   * @return $this
   */
  public function setQueryVariables(array $array, $separator="&")
  {
    if (empty($array)) {
      $this->query = null;
    } else {
      $this->query = http_build_query($array, $separator);
    }
    return $this;
  }

  /**
   * Returns the specified variable.
   * Nested vars can be accessed by object path notation,
   * eg: "myvar.foo.bar"
   *
   * @param string $path The object path to the variable
   * @param string $separator The object path separator (default = '.')
   *
   * @return array|string
   **/
  public function getQueryVariable($path, $separator='.')
  {
    if(!$path) return null;

    $segs = explode($separator, $path);

    $target = $this->getQueryVariables(); 
    for($i = 0; $i < count($segs)-1; $i++) { 
      if(isset($target[$segs[$i]]) && is_array($target[$segs[$i]])) { 
        $target = $target[$segs[$i]]; 
      } else {
        return null; 
      } 
    }
    if(isset($target[$segs[count($segs)-1]])) { 
      return $target[$segs[count($segs)-1]]; 
    }
    return null;
  }

  /**
   * Sets the specified variable(s) in the query string.
   *
   * @param array|string $path the object path to the variable, or an array of paths
   * @param array|string $value variable value
   * @param string       $separator The object path separator (default = '.')
   *
   * @return $this
   */
  public function setQueryVariable($path, $value, $separator='.')
  {
    if(is_array($path)) { 
      foreach($path as $p => $v) { 
        $this->setQueryVariable($p, $v); 
      } 
    } else {
      $segs = explode($separator, $path); 

      $vars = $this->getQueryVariables();
      $target =& $vars;
      for($i = 0; $i < count($segs)-1; $i++) {
        if(!isset($target[$segs[$i]])) {
          $target[$segs[$i]] = array(); 
        } 
        $target =& $target[$segs[$i]]; 
      } 
      if($segs[count($segs)-1] == '*') {
        foreach($target as $key => $value) {
          $target[$key]; 
        } 
      } else if($value === null && isset($target[$segs[count($segs)-1]])) {
        unset($target[$segs[count($segs)-1]]); 
      } else {
        $target[$segs[count($segs)-1]] = $value; 
      }
      $this->setQueryVariables($vars);
    }
    return $this;
  }

  public function isAbsoluteUrl()
  {
    return $this->scheme
      && $this->scheme !== 'file'
      && !$this->isWindowsDrive();
  }

  public function isAbsolutePath()
  {
    if(!$this->path) return false;
    // TODO: is this right ?
    if($this->scheme === 'file') return true;
    if($this->isWindowsDrive()) return true;
    if($this->isAbsoluteUrl()) return false;
    return $this->path[0] === '/' || $this->path[0] === '~';
  }

  public function isWindowsDrive()
  {
    if(!$this->scheme || !$this->path) {
      return false;
    }
    if($this->scheme === 'file' && preg_match('#^[a-zA-Z]:/#', $this->path)) {
      return true;
    }
    return preg_match('/^[a-zA-Z]$/', $this->scheme)
      && ($this->path[0] === '/' || $this->path[0] === '\\');
  }

  public function dirname()
  {
    $uri = clone $this;
    if($this->path) {
      $path = dirname($this->path);
      if($path === '/') {
        $path = null;
      }
      $uri->setPath($path);
    }
    return $uri;
  }

  public function join()
  {
    $uri = clone $this;
    $components = array(
      rtrim($this->path, '/')
    );
    foreach (func_get_args() as $arg) {
      $component = $arg;
      if($arg instanceof Uri) {
        $component = $arg->getPath();
      }
      $components[] = trim($component, '/');
    }
    $uri->setPath(implode('/', $components));
    return $uri;
  }

  /**
   * Returns the normalized Uri as stated in RFC 3886, section 6
   *
   * @return ju1ius\Uri
   **/
  public function normalize()
  {
    $uri = clone $this;
    if($this->isAbsoluteUrl()) {
      // Schemes are case-insensitive
      if ($this->scheme) {
        $uri->setScheme(strtolower($this->scheme));
      }
      // Hostnames are case-insensitive
      if ($this->host) {
        $uri->setHost(strtolower($this->host));
      }
      // Remove default port number for known schemes (RFC 3986, section 6.2.3)
      if ($this->port && $this->scheme
          && $this->port == getservbyname($this->scheme, 'tcp')
      ) {
        $uri->setPort(null);
      }
      // Normalize case of %XX percentage-encodings (RFC 3986, section 6.2.2.1)
      foreach (array('user','pass','host','path') as $part) {
        if ($this->$part) {
          $value = preg_replace_callback('/%[0-9a-f]{2}/iS', function($matches)
          {
            return strtoupper($matches[0]);
          }, $this->$part);
          $method = 'set' . ucfirst($part);
          $uri->$method($value);
        }
      }
    }
    // Path segment normalization (RFC 3986, section 6.2.2.3)
    $uri->setPath(self::removeDotSegments($this->path));
    // Scheme based normalization (RFC 3986, section 6.2.3)
    if ($this->host && !$uri->getPath()) {
      $uri->setPath('/');
    }
  }

  /**
   * Removes dots as described in RFC 3986, section 5.2.4, e.g.
   * "/foo/../bar/baz" => "/bar/baz"
   *
   * @param string $path a path
   *
   * @return string a path
   */
  public static function removeDotSegments($path)
  {
    $output = '';
    // Make sure not to be trapped in an infinite loop
    // in case of bug in this method
    $j = 0;
    while ($path && $j++ < 100) {
      if (substr($path, 0, 2) == './') {
        // Step 2.A
        $path = substr($path, 2);
      } elseif (substr($path, 0, 3) == '../') {
        // Step 2.A
        $path = substr($path, 3);
      } elseif (substr($path, 0, 3) == '/./' || $path == '/.') {
        // Step 2.B
        $path = '/' . substr($path, 3);
      } elseif (substr($path, 0, 4) == '/../' || $path == '/..') {
        // Step 2.C
        $path   = '/' . substr($path, 4);
        $i      = strrpos($output, '/');
        $output = $i === false ? '' : substr($output, 0, $i);
      } elseif ($path == '.' || $path == '..') {
        // Step 2.D
        $path = '';
      } else {
        // Step 2.E
        $i = strpos($path, '/');
        if ($i === 0) {
          $i = strpos($path, '/', 1);
        }
        if ($i === false) {
          $i = strlen($path);
        }
        $output .= substr($path, 0, $i);
        $path = substr($path, $i);
      }
    }
    return $output;
  }
}
