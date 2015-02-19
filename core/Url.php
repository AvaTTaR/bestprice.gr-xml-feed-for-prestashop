<?php
/**
 * Project: skroutzxmlfeed
 * File: Url.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 13/2/2015
 * Time: 9:01 μμ
 * Since: TODO ${VERSION}
 * Copyright: 2015 Panagiotis Vagenas
 */

namespace XDaRk_v141110;


class Url extends Core{
	/**
	 * @var string Regex matches a `scheme://`.
	 */
	public $regex_frag_scheme = '(?:[a-zA-Z0-9]+\:)?\/\/';

	/**
	 * @var string Regex matches a `host` name (TLD optional).
	 */
	public $regex_frag_host = '[a-zA-Z0-9]+(?:\-*[a-zA-Z0-9]+)*(?:\.[a-zA-Z0-9]+(?:\-*[a-zA-Z0-9]+)*)*(?:\.[a-zA-Z][a-zA-Z0-9]+)?';

	/**
	 * @var string Regex matches a `host:port` (`:port`, TLD are optional).
	 */
	public $regex_frag_host_port = '[a-zA-Z0-9]+(?:\-*[a-zA-Z0-9]+)*(?:\.[a-zA-Z0-9]+(?:\-*[a-zA-Z0-9]+)*)*(?:\.[a-zA-Z][a-zA-Z0-9]+)?(?:\:[0-9]+)?';

	/**
	 * @var string Regex matches a `user:pass@host:port` (`user:pass@`, `:port`, TLD are optional).
	 */
	public $regex_frag_user_host_port = '(?:[a-zA-Z0-9\-_.~+%]+(?:\:[a-zA-Z0-9\-_.~+%]+)?@)?[a-zA-Z0-9]+(?:\-*[a-zA-Z0-9]+)*(?:\.[a-zA-Z0-9]+(?:\-*[a-zA-Z0-9]+)*)*(?:\.[a-zA-Z][a-zA-Z0-9]+)?(?:\:[0-9]+)?';

	/**
	 * @var string Regex matches a valid `scheme://user:pass@host:port/path/?query#fragment` URL (`scheme:`, `user:pass@`, `:port`, `TLD`, `path`, `query` and `fragment` are optional).
	 */
	public $regex_valid_url = '/^(?:[a-zA-Z0-9]+\:)?\/\/(?:[a-zA-Z0-9\-_.~+%]+(?:\:[a-zA-Z0-9\-_.~+%]+)?@)?[a-zA-Z0-9]+(?:\-*[a-zA-Z0-9]+)*(?:\.[a-zA-Z0-9]+(?:\-*[a-zA-Z0-9]+)*)*(?:\.[a-zA-Z][a-zA-Z0-9]+)?(?:\:[0-9]+)?(?:\/(?!\/)[a-zA-Z0-9\-_.~+%]*)*(?:\?(?:[a-zA-Z0-9\-_.~+%]+(?:\=[a-zA-Z0-9\-_.~+%&]*)?)*)?(?:#[^\s]*)?$/';

	/**
	 * Gets the current URL (via environment variables).
	 *
	 * @param string $scheme Optional. A scheme to force. (i.e. `https`, `http`).
	 *    Use `//` to force a cross-protocol compatible scheme.
	 *
	 * @note If `$scheme` is NOT passed in (or is empty), we detect the current scheme, and use that by default.
	 *    For instance, if this `is_ssl()`, an SSL scheme will be used; else `http`.
	 *
	 * @return string The current URL, else an exception is thrown on failure.
	 *
	 * @throws exception If invalid types are passed through arguments list.
	 * @throws exception If unable to determine the current URL.
	 */
	public function current($scheme = '')
	{
		$this->check_arg_types('string', func_get_args());

		if(isset($this->static[__FUNCTION__][$scheme]))
			return $this->static[__FUNCTION__][$scheme];

		$this->static[__FUNCTION__][$scheme] = $this->current_scheme().'://'.$this->current_host().$this->current_uri();
		if($scheme) $this->static[__FUNCTION__][$scheme] = $this->set_scheme($this->static[__FUNCTION__][$scheme], $scheme);

		return $this->static[__FUNCTION__][$scheme];
	}

	/**
	 * Gets the current scheme (via environment variables).
	 *
	 * @return string The current scheme, else an exception is thrown on failure.
	 *
	 * @throws exception If unable to determine the current scheme.
	 */
	public function current_scheme()
	{
		if(isset($this->static[__FUNCTION__]))
			return $this->static[__FUNCTION__];

		$scheme = $this->Vars->_SERVER('REQUEST_SCHEME');

		if($this->String->is_not_empty($scheme))
			$this->static[__FUNCTION__] = $this->n_scheme($scheme);
		else $this->static[__FUNCTION__] = ($this->is_ssl()) ? 'https' : 'http';

		return $this->static[__FUNCTION__];
	}

	/**
	 * Gets the current URI (via environment variables).
	 *
	 * @return string The current URI, else an exception is thrown on failure.
	 *
	 * @throws exception If unable to determine the current URI.
	 */
	public function current_uri()
	{
		if(isset($this->static[__FUNCTION__]))
			return $this->static[__FUNCTION__];

		if(is_string($uri = $this->Vars->_SERVER('REQUEST_URI')))
			$uri = $this->parse_uri($uri);

		if(!$this->String->is_not_empty($uri))
			throw $this->Exception->factory('Missing required `$_SERVER[\'REQUEST_URI\']`.');

		return ($this->static[__FUNCTION__] = $uri);
	}

	/**
	 * Determine if SSL is used.
	 *
	 * @since 2.6.0
	 *
	 * @return bool True if SSL, false if not used.
	 */
	public function is_ssl() {
		if ( isset($_SERVER['HTTPS']) ) {
			if ( 'on' == strtolower($_SERVER['HTTPS']) )
				return true;
			if ( '1' == $_SERVER['HTTPS'] )
				return true;
		} elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Gets the current host name (via environment variables).
	 *
	 * @return string The current host name, else an exception is thrown on failure.
	 *
	 * @throws exception If unable to determine the current host name.
	 */
	public function current_host()
	{
		if(isset($this->static[__FUNCTION__]))
			return $this->static[__FUNCTION__];

		$host = $this->Vars->_SERVER('HTTP_HOST');

		if(!$this->String->is_not_empty($host))
			throw $this->Exception->factory('Missing required `$_SERVER[\'HTTP_HOST\']`.');

		return ($this->static[__FUNCTION__] = $host);
	}

	/**
	 * Normalizes a URL scheme.
	 *
	 * @param string $scheme An input URL scheme.
	 *
	 * @return string A normalized URL scheme (always lowercase).
	 *
	 * @throws exception If invalid types are passed through arguments list.
	 */
	public function n_scheme($scheme)
	{
		$this->check_arg_types('string', func_get_args());

		if(strpos($scheme, ':') !== FALSE)
			$scheme = strstr($scheme, ':', TRUE);

		return strtolower($scheme); // Normalized scheme.
	}

	/**
	 * Normalizes a URL host name.
	 *
	 * @param string $host An input URL host name.
	 *
	 * @return string A normalized URL host name (always lowercase).
	 *
	 * @throws exception If invalid types are passed through arguments list.
	 */
	public function n_host($host)
	{
		$this->check_arg_types('string', func_get_args());

		return strtolower($host); // Normalized host name.
	}

	/**
	 * Sets a particular scheme.
	 *
	 * @param string $url A full URL.
	 *
	 * @param string $scheme Optional. The scheme to use (i.e. `//`, `https`, `http`).
	 *    Use `//` to use a cross-protocol compatible scheme.
	 *    Defaults to the current scheme.
	 *
	 * @return string The full URL w/ `$scheme`.
	 *
	 * @throws exception If invalid types are passed through arguments list.
	 */
	public function set_scheme($url, $scheme = '')
	{
		$this->check_arg_types('string', 'string', func_get_args());

		if(!$scheme) // Current scheme?
			$scheme = $this->current_scheme();

		if($scheme !== '//') $scheme = $this->n_scheme($scheme).'://';

		return preg_replace('/^'.$this->regex_frag_scheme.'/', $this->String->esc_refs($scheme), $url);
	}

	/**
	 * Parses a URL (or a URI/query/fragment only) into an array.
	 *
	 * @param string       $url_uri_query_fragment A full URL; or a partial URI;
	 *    or only a query string, or only a fragment. Any of these can be parsed here.
	 *
	 * @note A query string or fragment MUST be prefixed with the appropriate delimiters.
	 *    This is bad `name=value` (interpreted as path). This is good `?name=value` (query string).
	 *    This is bad `anchor` (interpreted as path). This is good `#fragment` (fragment).
	 *
	 * @param null|integer $component Same as PHP's `parse_url()` component.
	 *    Defaults to NULL; which defaults to an internal value of `-1` before we pass to PHP's `parse_url()`.
	 *
	 * @param null|integer $normalize A bitmask. Defaults to NULL (indicating a default bitmask).
	 *    Defaults include: {@link fw_constants::url_scheme}, {@link fw_constants::url_host}, {@link fw_constants::url_path}.
	 *    However, we DO allow a trailing slash (even if path is being normalized by this parameter).
	 *
	 * @return array|string|integer|null If a component is requested, returns a string component (or an integer in the case of `PHP_URL_PORT`).
	 *    If a specific component is NOT requested, this returns a full array, of all component values.
	 *    Else, this returns NULL on any type of failure (even if a component was requested).
	 *
	 * @note Arrays returned by this method, will include a value for each component (a bit different from PHP's `parse_url()` function).
	 *    We start with an array of defaults (i.e. all empty strings, and `0` for the port number).
	 *    Components found in the URL are then merged into these default values.
	 *    The array is also sorted by key (e.g. alphabetized).
	 *
	 * @throws exception If invalid types are passed through arguments list.
	 */
	public function parse($url_uri_query_fragment, $component = NULL, $normalize = NULL)
	{
		$this->check_arg_types('string', array('null', 'integer'), array('null', 'integer'), func_get_args());

		if(!isset($normalize)) // Use defaults?
			$normalize = $this::url_scheme | $this::url_host | $this::url_path;

		if(strpos($url_uri_query_fragment, '//') === 0 && preg_match($this->regex_valid_url, $url_uri_query_fragment))
		{
			$url_uri_query_fragment = $this->current_scheme().':'.$url_uri_query_fragment; // So URL is parsed properly.
			// Works around a bug in `parse_url()` prior to PHP v5.4.7. See: <http://php.net/manual/en/function.parse-url.php>.
			$x_protocol_scheme = TRUE; // Flag this, so we can remove scheme below.
		}
		else $x_protocol_scheme = FALSE; // No scheme; or scheme is NOT cross-protocol compatible.

		$parsed = @parse_url($url_uri_query_fragment, ((!isset($component)) ? -1 : $component));

		if($x_protocol_scheme) // Cross-protocol scheme?
		{
			if(!isset($component) && is_array($parsed))
				$parsed['scheme'] = ''; // No scheme.

			else if($component === PHP_URL_SCHEME)
				$parsed = ''; // No scheme.
		}
		if($normalize & $this::url_scheme) // Normalize scheme?
		{
			if(!isset($component) && is_array($parsed))
			{
				if(!$this->String->is($parsed['scheme']))
					$parsed['scheme'] = ''; // No scheme.
				$parsed['scheme'] = $this->n_scheme($parsed['scheme']);
			}
			else if($component === PHP_URL_SCHEME)
			{
				if(!is_string($parsed))
					$parsed = ''; // No scheme.
				$parsed = $this->n_scheme($parsed);
			}
		}
		if($normalize & $this::url_host) // Normalize host?
		{
			if(!isset($component) && is_array($parsed))
			{
				if(!$this->String->is($parsed['host']))
					$parsed['host'] = ''; // No host.
				$parsed['host'] = $this->n_host($parsed['host']);
			}
			else if($component === PHP_URL_HOST)
			{
				if(!is_string($parsed))
					$parsed = ''; // No scheme.
				$parsed = $this->n_host($parsed);
			}
		}
		if($normalize & $this::url_path) // Normalize path?
		{
			if(!isset($component) && is_array($parsed))
			{
				if(!$this->String->is($parsed['path']))
					$parsed['path'] = '/'; // Home directory.
				$parsed['path'] = $this->n_path_seps($parsed['path'], TRUE);
				if(strpos($parsed['path'], '/') !== 0) $parsed['path'] = '/'.$parsed['path'];
			}
			else if($component === PHP_URL_PATH)
			{
				if(!is_string($parsed))
					$parsed = '/'; // Home directory.
				$parsed = $this->n_path_seps($parsed, TRUE);
				if(strpos($parsed, '/') !== 0) $parsed = '/'.$parsed;
			}
		}
		if(in_array(gettype($parsed), array('array', 'string', 'integer'), TRUE))
		{
			if(is_array($parsed)) // An array?
			{
				// Standardize.
				$defaults       = array(
					'fragment' => '',
					'host'     => '',
					'pass'     => '',
					'path'     => '',
					'port'     => 0,
					'query'    => '',
					'scheme'   => '',
					'user'     => ''
				);
				$parsed         = array_merge($defaults, $parsed);
				$parsed['port'] = (integer)$parsed['port'];
				ksort($parsed); // Sort by key.
			}
			return $parsed; // A `string|integer|array`.
		}
		return NULL; // Default return value.
	}

	/**
	 * Unparses a URL (putting it all back together again).
	 *
	 * @param array        $parsed An array with at least one URL component.
	 *
	 * @param null|integer $normalize A bitmask. Defaults to NULL (indicating a default bitmask).
	 *    Defaults include: {@link fw_constants::url_scheme}, {@link fw_constants::url_host}, {@link fw_constants::url_path}.
	 *    However, we DO allow a trailing slash (even if path is being normalized by this parameter).
	 *
	 * @return string A full or partial URL, based on components provided in the `$parsed` array.
	 *    It IS possible to receive an empty string, when/if `$parsed` does NOT contain any portion of a URL.
	 *
	 * @throws exception If invalid types are passed through arguments list.
	 */
	public function unparse($parsed, $normalize = NULL)
	{
		$this->check_arg_types('array', array('null', 'integer'), func_get_args());

		$unparsed = ''; // Initialize string value.

		if(!isset($normalize)) // Use defaults?
			$normalize = $this::url_scheme | $this::url_host | $this::url_path;

		if($normalize & $this::url_scheme) // Normalize scheme?
		{
			if(!$this->String->is($parsed['scheme']))
				$parsed['scheme'] = ''; // No scheme.
			$parsed['scheme'] = $this->n_scheme($parsed['scheme']);
		}
		if($this->String->is_not_empty($parsed['scheme']))
			$unparsed .= $parsed['scheme'].'://';
		else if($this->String->is($parsed['scheme']) && $this->String->is_not_empty($parsed['host']))
			$unparsed .= '//'; // Cross-protocol compatible (ONLY if there is a host name also).

		if($this->String->is_not_empty($parsed['user']))
		{
			$unparsed .= $parsed['user'];
			if($this->String->is_not_empty($parsed['pass']))
				$unparsed .= ':'.$parsed['pass'];
			$unparsed .= '@';
		}
		if($normalize & $this::url_host) // Normalize host?
		{
			if(!$this->String->is($parsed['host']))
				$parsed['host'] = ''; // No host.
			$parsed['host'] = $this->n_host($parsed['host']);
		}
		if($this->String->is_not_empty($parsed['host']))
			$unparsed .= $parsed['host'];

		if($this->Integer->is_not_empty($parsed['port']))
			$unparsed .= ':'.(string)$parsed['port']; // A `0` value is excluded here.
		else if($this->String->is_not_empty($parsed['port']) && (integer)$parsed['port'])
			$unparsed .= ':'.(string)(integer)$parsed['port']; // We also accept string port numbers.

		if($normalize & $this::url_path) // Normalize path?
		{
			if(!$this->String->is($parsed['path']))
				$parsed['path'] = '/'; // Home directory.
			$parsed['path'] = $this->n_path_seps($parsed['path'], TRUE);
			if(strpos($parsed['path'], '/') !== 0) $parsed['path'] = '/'.$parsed['path'];
		}
		if($this->String->is($parsed['path']))
			$unparsed .= $parsed['path'];

		if($this->String->is_not_empty($parsed['query']))
			$unparsed .= '?'.$parsed['query'];

		if($this->String->is_not_empty($parsed['fragment']))
			$unparsed .= '#'.$parsed['fragment'];

		return $unparsed; // Possible empty string.
	}

	/**
	 * Normalizes a URL path from a URL (or a URI/query/fragment only).
	 *
	 * @param string  $url_uri_query_fragment A full URL; or a partial URI;
	 *    or only a query string, or only a fragment. Any of these can be normalized here.
	 *
	 * @param boolean $allow_trailing_slash Defaults to a FALSE value.
	 *    If TRUE, and `$url_uri_query_fragment` contains a trailing slash; we'll leave it there.
	 *
	 * @return string Normalized URL (or a URI/query/fragment only).
	 *
	 * @throws exception If invalid types are passed through arguments list.
	 */
	public function n_path_seps($url_uri_query_fragment, $allow_trailing_slash = FALSE)
	{
		$this->check_arg_types('string', 'boolean', func_get_args());

		if(!strlen($url_uri_query_fragment)) return '';

		if(!($parts = $this->parse($url_uri_query_fragment, NULL, 0)))
			$parts['path'] = $url_uri_query_fragment;

		if(strlen($parts['path'])) // Normalize directory separators.
			$parts['path'] = $this->Dir->n_seps($parts['path'], $allow_trailing_slash);

		return $this->unparse($parts, 0); // Back together again.
	}

	/**
	 * Parses a URI from a URL (or a URI/query/fragment only).
	 *
	 * @param string       $url_uri_query_fragment A full URL; or a partial URI;
	 *    or only a query string, or only a fragment. Any of these can be parsed here.
	 *
	 * @param null|integer $normalize A bitmask. Defaults to NULL (indicating a default bitmask).
	 *    Defaults include: {@link fw_constants::url_scheme}, {@link fw_constants::url_host}, {@link fw_constants::url_path}.
	 *    However, we DO allow a trailing slash (even if path is being normalized by this parameter).
	 *
	 * @param boolean      $include_fragment Defaults to TRUE. Include a possible fragment?
	 *
	 * @return string|null A URI (i.e. a URL path), else NULL on any type of failure.
	 *
	 * @throws exception If invalid types are passed through arguments list.
	 */
	public function parse_uri($url_uri_query_fragment, $normalize = NULL, $include_fragment = TRUE)
	{
		$this->check_arg_types('string', array('null', 'integer'), 'boolean', func_get_args());

		if(($parts = $this->parse_uri_parts($url_uri_query_fragment, $normalize)))
		{
			if(!$include_fragment) // Ditch fragment?
				unset($parts['fragment']);

			return $this->unparse($parts, $normalize);
		}
		return NULL; // Default return value.
	}

	/**
	 * Parses URI parts from a URL (or a URI/query/fragment only).
	 *
	 * @param string       $url_uri_query_fragment A full URL; or a partial URI;
	 *    or only a query string, or only a fragment. Any of these can be parsed here.
	 *
	 * @param null|integer $normalize A bitmask. Defaults to NULL (indicating a default bitmask).
	 *    Defaults include: {@link fw_constants::url_scheme}, {@link fw_constants::url_host}, {@link fw_constants::url_path}.
	 *    However, we DO allow a trailing slash (even if path is being normalized by this parameter).
	 *
	 * @return array|null An array with the following components, else NULL on any type of failure.
	 *
	 *    • `path`(string) Possible URI path.
	 *    • `query`(string) A possible query string.
	 *    • `fragment`(string) A possible fragment.
	 *
	 * @throws exception If invalid types are passed through arguments list.
	 */
	public function parse_uri_parts($url_uri_query_fragment, $normalize = NULL)
	{
		$this->check_arg_types('string', array('null', 'integer'), func_get_args());

		if(($parts = $this->parse($url_uri_query_fragment, NULL, $normalize)))
			return array('path' => $parts['path'], 'query' => $parts['query'], 'fragment' => $parts['fragment']);

		return NULL; // Default return value.
	}
}