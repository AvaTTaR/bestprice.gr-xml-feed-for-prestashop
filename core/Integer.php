<?php
/**
 * Project: skroutzxmlfeed
 * File: Integer.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 13/2/2015
 * Time: 9:53 μμ
 * Since: TODO ${VERSION}
 * Copyright: 2015 Panagiotis Vagenas
 */

namespace XDaRk_v141110;


class Integer extends Core{
	/**
	 * Short version of `(!empty() && is_integer())`.
	 *
	 * @note Unlike PHP's `is_...` functions, this will NOT throw a NOTICE.
	 *
	 * @param mixed $var A variable by reference (no NOTICE).
	 *    If `$var` is NOT already set, it will be set to NULL by PHP, as a result of passing it by reference.
	 *
	 * @return boolean TRUE if the variable is `(!empty() && is_integer())`.
	 */
	public function is_not_empty(&$var)
	{
		return !empty($var) && is_integer($var);
	}
}