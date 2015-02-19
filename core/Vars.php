<?php
/**
 * Project: coremodule
 * File: Vars.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 19/11/2014
 * Time: 8:34 πμ
 * Since: 141110
 * Copyright: 2014 Panagiotis Vagenas
 */

namespace XDaRk_v141110;

if (!defined('_PS_VERSION_'))
	exit;


class Vars extends Core{
	/**
	 * Converts PHP variables into JavaScript.
	 *
	 * @note This follows JSON standards; except we use single quotes instead of double quotes.
	 *    Also, see {@link strings::esc_js_sq_deep()} for subtle differences when it comes to line breaks.
	 *    • Special handling for line breaks in strings: `\r\n` and `\r` are converted to `\n`.
	 *
	 * @param mixed   $var Any input variable (or an expression is fine also).
	 * @param boolean $___recursion Internal use only.
	 *
	 * @return string JavaScript value (w/ string representation).
	 *
	 * @throws exception If invalid types are passed through arguments list.
	 */
	public function to_js($var, $___recursion = FALSE)
	{
		if(!$___recursion) // Only for the initial caller.
			$this->check_arg_types('', 'boolean', func_get_args());

		switch(($type = gettype($var))) // Based on type.
		{
			case 'object': // Iterates each object property.
			case 'array': // Or, each array key (if this is an array).

				if($type === 'array' && (empty($var) || array_keys($var) === range(0, count($var) - 1)))
					$js_value = '['.implode(',', array_map(array($this, __FUNCTION__), $var, array_fill(0, count($var), TRUE))).']';

				else // It's an object; or an associative array (which is converted to an object).
				{
					$_nested_key_props = array(); // Initialize.

					foreach($var as $_nested_key_prop => &$_nested_value)
						$_nested_key_props[] = "'".$this->String->esc_js_sq((string)$_nested_key_prop)."':".$this->to_js($_nested_value, TRUE);
					$js_value = '{'.implode(',', $_nested_key_props).'}';

					unset($_nested_key_prop, $_nested_value, $_nested_key_props); // Housekeeping.
				}
				break; // Break switch.

			// Everything else is MUCH simpler to handle.

			case 'integer':
				$js_value = (string)$var;
				break; // Break switch.

			case 'real': // Alias for `float` type.
			case 'double': // Alias for `float` type.
			case 'float': // Standardized as `float` type.
				$js_value = (string)$var;
				break; // Break switch.

			case 'string':
				$js_value = "'".$this->String->esc_js_sq($var)."'";
				break; // Break switch.

			case 'boolean':
				$js_value = ($var) ? 'true' : 'false';
				break; // Break switch.

			case 'resource':
				$js_value = "'".$this->String->esc_js_sq((string)$var)."'";
				break; // Break switch.

			case 'NULL':
				$js_value = 'null';
				break; // Break switch.

			default: // Default case handler.
				$js_value = "'".$this->String->esc_js_sq((string)$var)."'";
				break; // Break switch.
		}
		return $js_value; // JavaScript value.
	}

	/**
	 * Returns a copy of all `$_SERVER` vars.
	 *
	 * @param string|integer $key Optional. Looking for a specific array key?
	 *
	 * @return array|mixed|null Copy of all `$_SERVER` vars by default, else an empty array.
	 *    If a specific `$key` is requested, the value of that `$key`; else NULL.
	 */
	public function _SERVER($key = NULL)
	{
		$this->check_arg_types(array('null', 'integer', 'string'), func_get_args());

		if(!empty($_SERVER))
		{
			if(isset($key)) // A specific key?
			{
				if(array_key_exists($key, (array)$_SERVER))
					return $_SERVER[$key];
				return NULL;
			}
			return (array)$_SERVER;
		}
		return isset($key) ? NULL : array();
	}

	/**
	 * Generates an array from a string of query vars.
	 *
	 * @param string      $string An input string of query vars.
	 *
	 * @param boolean     $convert_dots_spaces Optional. This defaults to a TRUE value (just like PHP's `parse_str()` function).
	 *    Setting this to a FALSE value, makes it possible to preserve variables that actually SHOULD contain dots and/or spaces.
	 *
	 * @param null|string $dec_type Optional. Defaults to {@link fw_constants::rfc1738}, indicating `urldecode()`.
	 *    Or, this can also be set to {@link fw_constants::rfc3986}, indicating `rawurldecode()`.
	 *    Or, if this is set to a NULL value, no URL-decoding will occur whatsoever.
	 *    Should be specified with one of these constants:
	 *       • {@link fw_constants::rfc1738}
	 *       • {@link fw_constants::rfc3986}
	 *
	 * @param null|array  $___parent_array Internal use only; for recursion.
	 *
	 * @return array An array of data, based on the input `$string` value.
	 *
	 * @see The `build_query()` method in this class, for the opposite.
	 *
	 * @throws exception If invalid types are passed through arguments list.
	 * @throws exception If `$dec_type` is passed as a string, and it's empty.
	 */
	public function parse_query($string, $convert_dots_spaces = TRUE, $dec_type = self::rfc1738, &$___parent_array = NULL)
	{
		if(!isset($___parent_array)) // Only check arg types initially (i.e. NOT in recursive calls).
			$this->check_arg_types('string', 'boolean', array('null', 'string:!empty'), array('null', 'array'), func_get_args());

		if(isset($___parent_array))
			$array = & $___parent_array;
		else $array = array(); // Initialize array.

		foreach(explode('&', $string) as $_name_value)
		{
			if(strlen($_name_value) && $_name_value !== '=')
			{
				$_name_value = explode('=', $_name_value, 2);
				$_name       = $_name_value[0]; // Always has length.
				$_value      = (isset($_name_value[1])) ? $_name_value[1] : '';

				if($dec_type === $this::rfc1738)
					$_name = urldecode($_name);
				else if($dec_type === $this::rfc3986)
					$_name = rawurldecode($_name);

				if($convert_dots_spaces)
					$_name = str_replace(array('.', ' '), '_', $_name);

				if(strlen($_name) // Handles recursion into multiple dimensions of arrays.
				   && preg_match('/^(?P<name>[^\[]+)\[(?P<nested_name>[^\]]*)\](?P<deep>.*)$/', $_name, $_m)
				) // Here we use regex, and parent arrays by &reference; to parse all dimensions.
				{
					if(!isset($array[$_m['name']]))
						$array[$_m['name']] = array();

					if(!strlen($_m['nested_name']))
						$_m['nested_name'] = count($array[$_m['name']]);

					if($dec_type === $this::rfc1738)
						$_value = urlencode($_m['nested_name'].$_m['deep']).'='.$_value;
					else if($dec_type === $this::rfc3986)
						$_value = rawurlencode($_m['nested_name'].$_m['deep']).'='.$_value;
					else $_value = $_m['nested_name'].$_m['deep'].'='.$_value;

					$array[$_m['name']] = $this->parse_query($_value, $convert_dots_spaces, $dec_type, $array[$_m['name']]);
				}
				else // NOT an array.
				{
					if($dec_type === $this::rfc1738)
						$_value = urldecode($_value);
					else if($dec_type === $this::rfc3986)
						$_value = rawurldecode($_value);

					$array[$_name] = $_value;
				}
				unset($_m); // Housekeeping.
			}
		}
		unset($_name_value, $_name, $_value);

		return $array; // Final array.
	}

	/**
	 * Generates an array from a string of query vars.
	 *
	 * @note This method is an alias for `parse_query()` with `$enc_type` set to: {@link fw_constants::rfc3986}.
	 *    Please check the `parse_query()` method for further details.
	 *
	 * @param string  $string An input string of query vars.
	 *
	 * @param boolean $convert_dots_spaces Optional. This defaults to a TRUE value.
	 *
	 * @return array An array of data, based on the input `$string` value.
	 */
	public function parse_raw_query($string, $convert_dots_spaces = TRUE)
	{
		return $this->parse_query($string, $convert_dots_spaces, $this::rfc3986);
	}

	/**
	 * Get request's query vars as an assoc array
	 * @return array
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since TODO ${VERSION}
	 */
	public function getQueryVars(){
		return $this->Vars->parse_raw_query($this->Vars->_SERVER('QUERY_STRING'));
	}
} 