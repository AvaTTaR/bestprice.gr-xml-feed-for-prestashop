<?php
/**
 * Project: coremodule
 * File: Module.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 12/11/2014
 * Time: 9:55 μμ
 * Since: 141110
 * Copyright: 2014 Panagiotis Vagenas
 */

namespace XDaRk_v141110;

if (!defined('_PS_VERSION_'))
	exit;

if (!class_exists('XDaRk_v141110\Module')) {

	/**
	 * Class Module
	 * @package XDaRk
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 141110
	 *
	 * @property \XDaRk_v141110\Dir             Dir
	 * @property \XDaRk_v141110\File            File
	 * @property \XDaRk_v141110\Form            Form
	 * @property \XDaRk_v141110\Hooks           Hooks
	 * @property \XDaRk_v141110\Installer       Installer
	 * @property \XDaRk_v141110\Options         Options
	 * @property \XDaRk_v141110\XML             XML
	 * @property \XDaRk_v141110\Exception       Exception
	 */
	abstract class Module extends \Module
	{
		/**
		 * @var string Name of this plugin
		 */
		public $name = 'coremodule';
		/**
		 * @var string Description
		 */
		public $description = 'Core Module For PrestaShop';
		/**
		 * @var string
		 */
		public $tab = 'others';
		/**
		 * @var string
		 */
		public $version = '141110';
		/**
		 * @var string
		 */
		public $author = 'Panagiotis Vagenas <pan.vagenas@gmail.com>';
		/**
		 * @var int
		 */
		public $need_instance = 0;
		/**
		 * @var array
		 */
		public $ps_versions_compliancy = array('min' => '1.5');
		/**
		 * @var array
		 */
		public $dependencies = array();
		/**
		 * @var string
		 */
		public $displayName = 'XDaRk_v141110 Core Module';
		/**
		 * @var bool
		 */
		public $bootstrap = true;
		/**
		 * @var AutoLoader
		 */
		protected $loader;
		/**
		 * @var Core
		 */
		public $core;

		public function __call($name, $args)
		{
			// hook functions to Hook class
			if (Hooks::isHookFunction($name)) {
				$name = 'hook'.ucfirst(ltrim($name, 'hook'));
				if (!method_exists($this->core->moduleInstance->Hooks, $name)) {
					throw new \Exception('Hook '.$name.' Not Found');
				}

				return $this->core->moduleInstance->Hooks->{$name}($args);
			} else {
				return $this->core->{$name}($args);
			}
		}

		public function __isset($name)
		{
			// hook functions to Hook class
			if (Hooks::isHookFunction($name)) {
				return true;
			}

			return false;
		}

		public function __get($name)
		{
			return $this->core->{$name};
		}

		/**
		 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
		 * @since 141110
		 */
		public final function _initialize()
		{
			$this->loader = new AutoLoader();
			$this->loader->register();

			// Register core namespace
			$this->loader->addNamespace('\XDaRk_v141110', dirname(__FILE__));

			$this->core              = Core::getInstance($this);
			Core::$instanceNamespace = $GLOBALS[ $this->name ]['root_ns'];
			Core::$instanceBaseDir   = $GLOBALS[ $this->name ]['dir'];
			Core::$instanceRootNSDir = $GLOBALS[ $this->name ]['dir'].DIRECTORY_SEPARATOR.Core::$instanceNamespace;

			// Register instance namespace, this is a necessary step
			$this->loader->addNamespace('\\'.Core::$instanceNamespace, Core::$instanceRootNSDir);

			Core::$instanceClasses = File::phpClassesInDir(Core::$instanceRootNSDir);
			Core::$classes         = File::phpClassesInDir(dirname(__FILE__));

			// Extenders
			$this->xdRegisterNameSpaces();

			Hooks::registerHooks($this, $this->Hooks);
		}

		/**
		 * @extend Always call parent construct before implementing custom logic
		 */
		public function __construct()
		{
			parent::__construct();

			$this->displayName      = $this->l($this->displayName);
			$this->description      = $this->l($this->description);
			$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
			$this->_initialize();
		}

		protected function xdRegisterNameSpaces()
		{
			return true;
		}

		/**
		 * Module options page
		 *
		 * @doNotExtend
		 *
		 * @return string
		 *
		 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
		 * @since ${VERSION}
		 */
		public final function getContent()
		{
			$output = '';

			if (\Tools::isSubmit('submit'.$this->name)) {
				$newOptions = $_POST;
				if ($this->Options->saveOptions($newOptions)) {
					$output .= $this->displayConfirmation($this->l('Settings updated'));
				} else {
					$output .= $this->displayError($this->l('There was an error saving options'));
				}
			}

			return $output.$this->xdGetContent();
		}

		/**
		 * @extenders This should be used by extenders to display form fields
		 * @return string
		 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
		 * @since 141110
		 */
		protected function xdGetContent()
		{
			return '';
		}

		/**
		 * @doNotExtend
		 *
		 * @return bool
		 *
		 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
		 * @since ${VERSION}
		 */
		public final function install()
		{
			return parent::install() && $this->Installer->install();
		}

		/**
		 * @doNotExtend
		 *
		 * @return bool
		 *
		 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
		 * @since ${VERSION}
		 */
		public final function uninstall()
		{
			return parent::uninstall() && $this->Installer->uninstall();
		}

		/**
		 * We need this because is protected in parent
		 * @return \Context
		 *
		 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
		 * @since ${VERSION}
		 */
		public function getContext(){
			return $this->context;
		}
	}
}