<?php
/**
 * Project: coremodule
 * File: Hooks.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 11/11/2014
 * Time: 8:59 μμ
 * Since: 141110
 * Copyright: 2014 Panagiotis Vagenas
 */

namespace XDaRk_v141110;

if (!defined('_PS_VERSION_'))
	exit;


class Hooks extends Core {
	/**
	 * Hooks are registered dynamically so no need to do this in install time. TODO is this efficient?
	 *
	 * @param \Module $module
	 * @param Hooks $class
	 *
	 * @return bool
	 * @throws \PrestaShopException
	 * @static * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 141110
	 */
	public static function registerHooks(\Module &$module, Hooks &$class) {
		$hooks = (array) get_class_methods(get_class($class));

		foreach ($module->Options->getValue('hooks') as $hook) {
			if (!in_array($hook, $hooks)) {
				$module->unregisterHook($hook);
			}
		}

		$result = true;
		foreach ($hooks as $key => $hook) {
			$hookName = lcfirst(ltrim($hook, 'hook'));
			if (!self::isHookFunction($hook)) {
				unset ($hooks[ $key ]);
				continue;
			}
			if ($module->isRegisteredInHook($hookName)) {
				continue;
			}

			$result &= (bool) $module->registerHook($hookName);
		}

		$module->Options->saveOptions(array('hooks' => $hooks));

		return $result;
	}

	public static function isHookFunction($name) {
		return preg_match(Core::$__REGEX_HOOK_FUNCTION, $name);
	}

	public static function removeAllHooksFromModule(\Module &$module) {
		foreach (\Hook::getHooks() as $k => $hook) {
			$module->unregisterHook((int) $hook['id_hook']);
		}
	}
} 