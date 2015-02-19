<?php
/**
 * Project: coremodule
 * File: Form.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 11/11/2014
 * Time: 8:35 μμ
 * Since: 141110
 * Copyright: 2014 Panagiotis Vagenas
 */

/**
 * acswebservices
 * ${FILE_NAME}
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 11/11/2014
 * Time: 10:29 Ï€Î¼
 * Copyright: 2014 Panagiotis Vagenas
 */

namespace XDaRk_v141110;

use XDaRk_v141110\Panels\Panel;

if (!defined('_PS_VERSION_'))
	exit;

class Form extends \HelperFormCore
{
	protected $default_lang;
	protected $initialized = false;
	protected $tab = 0;
	protected $panels = array();
	public $fields_value = array();

	/**
	 * @param array $fieldValues
	 *
	 * @return $this
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since ${VERSION}
	 */
	public function setFieldsValues(Array $fieldValues)
	{
		/* @var Panel $panel */
		foreach ((array)$this->panels as $panel) {
			$this->fields_value = array_merge($this->fields_value, $panel->parseFieldsValues($fieldValues));
		}

		return $this;
	}

	/**
	 * @param Panel $panel
	 *
	 * @return $this
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 141110
	 */
	public function registerPanel(Panel $panel){
		array_push($this->panels, $panel);
		return $this;
	}


	/**
	 * @param array $fields_value
	 *
	 * @return string
	 *
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since ${VERSION}
	 */
	public function generateForm($fields_value = array())
	{
		if(empty($this->fields_value) && !empty($fields_value)){
			$this->setFieldsValues($fields_value);
		} elseif(!empty($this->panels)) {
			return '';
		}

		if ( !empty($this->panels) && $this->initialized)
		{
			$main = '';
			$sidebar = '';
			/* @var $panel Panel */
			foreach ($this->panels as $k => $panel) {
				if($panel->isInSidebar()){
					$sidebar .= parent::generateForm(array($panel->__toArray()));
				} else {
					$main .= parent::generateForm(array($panel->__toArray()));
				}
			}

			if(empty($sidebar)){
				return $main;
			}
			return '<div class="row"><div class="col-lg-9">'.$main.'</div><div class="col-lg-3">'.$sidebar.'</div></div>';
		}
		return '';
	}

	/**
	 * @param array $fieldsForm
	 *
	 * @return $this
	 *
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since ${VERSION}
	 */
	public function setFieldsForm(Array $fieldsForm)
	{
		$this->fields_form = $fieldsForm;

		return $this;
	}

	/**
	 * @param $module
	 * @param bool $bootstrap
	 * @param bool $title
	 * @param bool $showToolbar
	 * @param bool $toolBarScroll
	 * @param array $toolbarBtn
	 *
	 * @return $this
	 *
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since ${VERSION}
	 */
	public function initialize($module, $bootstrap = true, $title = false, $showToolbar = true, $toolBarScroll = true, $toolbarBtn = array())
	{
		$this->default_lang = (int) \Configuration::get('PS_LANG_DEFAULT');

		$this->module          = $module;
		$this->name_controller = $module->name;
		$this->token           = \Tools::getAdminTokenLite('AdminModules');
		$this->currentIndex    = \AdminController::$currentIndex.'&configure='.$module->name;
		$this->bootstrap       = $bootstrap;
//		$this->name_controller = 'col-lg-3';

		// Language
		$this->default_form_language    = $this->default_lang;
		$this->allow_employee_form_lang = $this->default_lang;

		// Title and toolbar
		$this->title          = $title === false ? $module->displayName : $title;
		$this->show_toolbar   = $showToolbar;        // false -> remove toolbar
		$this->toolbar_scroll = $toolBarScroll;      // yes - > Toolbar is always visible on the top of the screen.
		$this->submit_action  = 'submit'.$module->name;
		$this->toolbar_btn    =
			(empty($toolbarBtn) || !is_array($toolbarBtn))
				? array(
				'save' => array(
					'desc' => $module->l('Save'),
					'href' => \AdminController::$currentIndex.'&configure='.$module->name.'&save'.$module->name.
					          '&token='.\Tools::getAdminTokenLite('AdminModules'),
				),
				'back' => array(
					'href' => \AdminController::$currentIndex.'&token='.\Tools::getAdminTokenLite('AdminModules'),
					'desc' => $module->l('Back to list')
				)
			)
				: $toolbarBtn;

		$this->initialized = true;

		return $this;
	}
}