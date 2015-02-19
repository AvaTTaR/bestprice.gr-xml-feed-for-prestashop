<?php
/**
 * Project: coremodule
 * File: Installer.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 12/11/2014
 * Time: 8:21 πμ
 * Since: 141110
 * Copyright: 2014 Panagiotis Vagenas
 */

namespace XDaRk_v141110;

if (!defined('_PS_VERSION_'))
	exit;

class Installer extends Core {
	public function install() {
		$result = true;
		$result &= $this->xdInstall();

		return $result;
	}

	public function xdInstall() {
		return true;
	}

	public function uninstall() {
		$result = true;
		$result &= $this->xdUninstall();
		$result &= $this->Options->deleteAllOptions();
		return $result;
	}

	public function xdUninstall() {
		return true;
	}
} 