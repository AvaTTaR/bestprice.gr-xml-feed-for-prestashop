<?php
/**
 * Project: coremodule
 * File: Constants.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 11/11/2014
 * Time: 8:43 μμ
 * Since: 141110
 * Copyright: 2014 Panagiotis Vagenas
 */

namespace XDaRk_v141110;

if (!defined('_PS_VERSION_'))
	exit;


/**
 * Class Constants. XDaRk Core constants
 * @package XDaRk
 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
 * @since 141110
 */
interface Constants
{
	const  __CORE_NS__ = 'XDaRk_v141110';
	const __CORE_VERSION__ = 141110;

	# -----------------------------------------------------------------------------------------------------------------------------
	# URL parts/components bitmask for XDaRk Core.
	# -----------------------------------------------------------------------------------------------------------------------------

	/**
	 * @var integer Indicates scheme component in a URL.
	 */
	const url_scheme = 1;

	/**
	 * @var integer Indicates user component in a URL.
	 */
	const url_user = 2;

	/**
	 * @var integer Indicates pass component in a URL.
	 */
	const url_pass = 4;

	/**
	 * @var integer Indicates host component in a URL.
	 */
	const url_host = 8;

	/**
	 * @var integer Indicates port component in a URL.
	 */
	const url_port = 16;

	/**
	 * @var integer Indicates path component in a URL.
	 */
	const url_path = 32;

	/**
	 * @var integer Indicates query component in a URL.
	 */
	const url_query = 64;

	/**
	 * @var integer Indicates fragment component in a URL.
	 */
	const url_fragment = 128;

	# -----------------------------------------------------------------------------------------------------------------------------
	# RFC types (standards).
	# -----------------------------------------------------------------------------------------------------------------------------

	/**
	 * @var string Represents conformity with rfc1738.
	 */
	const rfc1738 = '___rfc1738___';

	/**
	 * @var string Represents conformity with rfc3986.
	 */
	const rfc3986 = '___rfc3986___';
}