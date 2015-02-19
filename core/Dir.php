<?php
/**
 * Project: coremodule
 * File: Dir.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 11/11/2014
 * Time: 8:46 μμ
 * Since: 141110
 * Copyright: 2014 Panagiotis Vagenas
 */

namespace XDaRk_v141110;

if (!defined('_PS_VERSION_'))
	exit;

class Dir extends Core {

	/**
	 * Normalizes directory/file separators.
	 *
	 * @return array {@inheritdoc}
	 *
	 * @see \xd_v141226_dev::n_dir_seps()
	 * @inheritdoc \xd_v141226_dev::n_dir_seps()
	 */
	public function n_seps() // Arguments are NOT listed here.
	{
		return call_user_func_array(array('\\XDaRk_v141110\Stub', 'n_dir_seps'), func_get_args());
	}
}