<?php declare(strict_types=1);

namespace ju1ius;

use InvalidArgumentException;

define('OS_WIN32', defined('OS_WINDOWS') ? OS_WINDOWS : !strncasecmp(PHP_OS, 'win', 3));

final class Uri
{
    private const COMPONENTS = [
        'scheme', 'host', 'port',
        'user', 'pass',
        'path', 'query', 'fragment',
    ];

    private ?string $scheme = null;
    private ?string $host = null;
    private ?string $port = null;
    private ?string $user = null;
    private ?string $pass = null;
    private ?string $path = null;
    private ?string $query = null;
    private ?string $fragment = null;

    public function __construct(string $uri)
    {
        $parts = parse_url($uri);
        foreach (self::COMPONENTS as $key) {
            if (isset($parts[$key])) {
                $this->{$key} = $parts[$key];
            }
        }
    }

    public static function parse($uri): self
    {
        if ($uri instanceof self) {
            return $uri;
        } elseif (is_string($uri)) {
            return new self($uri);
        }
        throw new InvalidArgumentException(
            "Argument passed to ju1ius\Uri::parse must be a string or ju1ius\Uri instance"
        );
    }

    public function getScheme(): ?string
    {
        return $this->scheme;
    }

    public function setScheme(?string $scheme)
    {
        $this->scheme = $scheme;
        return $this;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost(?string $host)
    {
        $this->host = $host;
        return $this;
    }

    public function getPort(): ?string
    {
        return $this->port;
    }

    public function setPort(?string $port)
    {
        $this->port = $port;
        return $this;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function setUser(?string $user)
    {
        $this->user = $user;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->pass;
    }

    public function setPassword(?string $pass)
    {
        $this->pass = $pass;
        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path)
    {
        $this->path = $path;
        return $this;
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }

    public function setQuery(?string $query)
    {
        if (is_array($query)) {
            $this->query = http_build_query($query);
        } else {
            $this->query = $query;
        }
        return $this;
    }

    public function getFragment(): ?string
    {
        return $this->fragment;
    }

    public function setFragment(?string $fragment)
    {
        $this->fragment = $fragment;
        return $this;
    }

    public function getUri(): string
    {
        $url = '';
        if ($this->isWindowsDrive()) {
            $url .= $this->scheme . ':\\';
        } elseif ($this->scheme) {
            $url .= $this->scheme . '://';
        }
        if ($this->user) {
            $url .= $this->user;
            if ($this->pass) {
                $url .= ':' . $this->pass;
            }
            $url .= '@';
        }
        if ($this->host) {
            $url .= $this->host;
        }
        if ($this->port) {
            $url .= ':' . $this->port;
        }
        if ($this->path) {
            if (!$this->isWindowsDrive()
                && $this->scheme
                && strpos($this->path, '/') !== 0
            ) {
                $url .= '/';
            }
            $url .= $this->path;
        }
        if ($this->query) {
            $url .= '?' . $this->query;
        }
        if ($this->fragment) {
            $url .= '#' . $this->fragment;
        }
        return $url;
    }

    public function __toString()
    {
        return $this->getUri();
    }

    public function getRootUrl(): self
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
     * @param string $separator
     * @return $this
     */
    public function setQueryVariables(array $array, string $separator = "&")
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
     * @return array|string|null
     **/
    public function getQueryVariable(string $path, string $separator = '.')
    {
        if (!$path) {
            return null;
        }

        $segs = explode($separator, $path);
        $target = $this->getQueryVariables();

        for ($i = 0; $i < count($segs) - 1; $i++) {
            if (isset($target[$segs[$i]]) && is_array($target[$segs[$i]])) {
                $target = $target[$segs[$i]];
            } else {
                return null;
            }
        }
        if (isset($target[$segs[count($segs) - 1]])) {
            return $target[$segs[count($segs) - 1]];
        }
        return null;
    }

    /**
     * Sets the specified variable(s) in the query string.
     *
     * @param array|string $path the object path to the variable, or an array of paths
     * @param array|string $value variable value
     * @param string $separator The object path separator (default = '.')
     *
     * @return $this
     */
    public function setQueryVariable($path, $value, string $separator = '.')
    {
        if (is_array($path)) {
            foreach ($path as $p => $v) {
                $this->setQueryVariable($p, $v);
            }
        } else {
            $segs = explode($separator, $path);

            $vars = $this->getQueryVariables();
            $target =& $vars;
            for ($i = 0; $i < count($segs) - 1; $i++) {
                if (!isset($target[$segs[$i]])) {
                    $target[$segs[$i]] = [];
                }
                $target =& $target[$segs[$i]];
            }
            if ($segs[count($segs) - 1] === '*') {
                foreach ($target as $key => $value) {
                    $target[$key];
                }
            } elseif ($value === null && isset($target[$segs[count($segs) - 1]])) {
                unset($target[$segs[count($segs) - 1]]);
            } else {
                $target[$segs[count($segs) - 1]] = $value;
            }
            $this->setQueryVariables($vars);
        }
        return $this;
    }

    public function isAbsoluteUrl(): bool
    {
        return $this->scheme
            && $this->scheme !== 'file'
            && !$this->isWindowsDrive();
    }

    public function isAbsolutePath(): bool
    {
        if (!$this->path) {
            return false;
        }
        // TODO: is this right ?
        if ($this->scheme === 'file') {
            return true;
        }
        if ($this->isWindowsDrive()) {
            return true;
        }
        if ($this->isAbsoluteUrl()) {
            return false;
        }
        return $this->path[0] === '/' || $this->path[0] === '~';
    }

    public function isWindowsDrive(): bool
    {
        if (!$this->scheme || !$this->path) {
            return false;
        }
        if ($this->scheme === 'file' && preg_match('#^[a-zA-Z]:/#', $this->path)) {
            return true;
        }
        return preg_match('/^[a-zA-Z]$/', $this->scheme)
            && ($this->path[0] === '/' || $this->path[0] === '\\');
    }

    public function dirname(): self
    {
        $uri = clone $this;
        if ($this->path) {
            $path = dirname($this->path);
            if ($path === '/') {
                $path = null;
            }
            $uri->setPath($path);
        }
        return $uri;
    }

    public function join(...$paths): self
    {
        $uri = clone $this;
        $components = [rtrim($this->path ?? '', '/')];

        foreach ($paths as $path) {
            $component = $path;
            if ($path instanceof Uri) {
                $component = $path->getPath();
            }
            $components[] = trim((string)$component, '/');
        }
        $uri->setPath(implode('/', $components));
        return $uri;
    }

    /**
     * Returns the normalized Uri as stated in RFC 3886, section 6
     *
     * @return ju1ius\Uri
     **/
    public function normalize(): self
    {
        $uri = clone $this;
        if ($this->isAbsoluteUrl()) {
            // Schemes are case-insensitive
            if ($this->scheme) {
                $uri->setScheme(strtolower($this->scheme));
            }
            // Hostnames are case-insensitive
            if ($this->host) {
                $uri->setHost(strtolower($this->host));
            }
            // Remove default port number for known schemes (RFC 3986, section 6.2.3)
            if ($this->port
                && $this->scheme
                && $this->port === getservbyname($this->scheme, 'tcp')
            ) {
                $uri->setPort(null);
            }
            // Normalize case of %XX percentage-encodings (RFC 3986, section 6.2.2.1)
            foreach (['user', 'pass', 'host', 'path'] as $part) {
                if ($this->$part) {
                    $value = preg_replace_callback('/%[0-9a-f]{2}/iS', function ($matches) {
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
    public static function removeDotSegments(string $path): string
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
                $path = '/' . substr($path, 4);
                $i = strrpos($output, '/');
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
