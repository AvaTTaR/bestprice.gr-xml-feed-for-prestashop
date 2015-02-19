<?php
/**
 * Project: coremodule
 * File: Exception.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 17/11/2014
 * Time: 8:30 πμ
 * Since: 141110
 * Copyright: 2014 Panagiotis Vagenas
 */

namespace XDaRk_v141110;

if (!defined('_PS_VERSION_'))
	exit;

class Exception extends \Exception{
	public final function factory(){
		return new self(func_get_args());
	}
} 