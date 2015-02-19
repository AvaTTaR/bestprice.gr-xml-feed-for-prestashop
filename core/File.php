<?php
/**
 * Project: coremodule
 * File: File.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 11/11/2014
 * Time: 8:46 μμ
 * Since: 141110
 * Copyright: 2014 Panagiotis Vagenas
 */

namespace XDaRk_v141110;

if (!defined('_PS_VERSION_'))
	exit;

class File extends Core {
	/**
	 * @param $dir
	 *
	 * @return array
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 141110
	 */
	public static function filesInDir($dir) {
		return self::filesInDirRegex($dir, Core::$__REGEX_MATCH_ALL__);
	}

	/**
	 * @param $dir
	 * @param $regex
	 *
	 * @return array
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 141110
	 */
	public static function filesInDirRegex($dir, $regex) {
		$ar = array();
		if (is_dir($dir)) {
			$directory = new \DirectoryIterator($dir);
			foreach ($directory as $fileInfo) {
				if (!$fileInfo->isDot() && preg_match($regex, $fileInfo->getFilename())) {
					$ar[ $fileInfo->getRealPath() ] = $fileInfo->getFilename();
				}
			}
		}

		return $ar;
	}

	/**
	 * @param $dir
	 *
	 * @return array
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 141110
	 */
	public static function phpFilesInDir($dir) {
		return self::filesInDirRegex($dir, Core::$__REGEX_MATCH_PHP_FILES);
	}

	/**
	 * @param $dir
	 *
	 * @return array
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 141110
	 */
	public static function phpClassesInDir($dir) {
		$files = self::phpFilesInDir($dir);
		foreach ($files as $path => $filename) {
			$files[ $path ] = str_replace('.php', '', $filename);
		}

		return $files;
	}
} 