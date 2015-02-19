<?php
/**
 * Project: coremodule
 * File: Options.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 11/11/2014
 * Time: 8:36 μμ
 * Since: 141110
 * Copyright: 2014 Panagiotis Vagenas
 */

namespace XDaRk_v141110;


if (!defined('_PS_VERSION_'))
	exit;

class Options extends Core
{
	/**
	 * @var string
	 */
	protected $optionsArrayName;

	/**
	 * @var array
	 */
	protected $defaults = array(
		'hooks' => array()
	);

	protected $validators = array(
		'hooks' => array('array')
	);
	/**
	 * @var array
	 */
	protected $stored = array();

	/**
	 *
	 */
	public function __construct($module)
	{
		parent::__construct($module);
		$this->optionsArrayName = Core::$instanceNamespace.'-Options';
		$this->init();
	}

	/**
	 * @return $this
	 *
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since ${VERSION}
	 */
	protected function init()
	{
		$this->setUp($this->defaults, $this->validators);

		$this->stored = unserialize(\Configuration::get($this->optionsArrayName));

		if (!$this->stored || empty($this->stored)) {
			$this->stored = $this->defaults;
			$this->saveOptions($this->stored);
		} else {
			$this->stored = array_merge($this->defaults, $this->stored);
		}

		return $this;
	}

	/**
	 * @extend
	 *
	 * @param $defaults
	 * @param $validators
	 *
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 141110
	 */
	protected function setUp($defaults, $validators)
	{
		$this->_setUp($defaults, $validators);
	}

	/**
	 * @doNotExtend
	 *
	 * @param $defaults
	 * @param $validators
	 *
	 * @throws \Exception
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 141110
	 */
	protected function _setUp($defaults, $validators)
	{
		if (!is_array($defaults) || !is_array($validators) || count($defaults) !== count($validators)) {
			throw new \Exception('Options array size not match with validators array size');
		}
		$this->defaults   = $defaults;
		$this->validators = $validators;
	}

	/**
	 * @param $optionName
	 * @param bool $default
	 *
	 * @return mixed
	 * @throws \Exception
	 *
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since ${VERSION}
	 */
	public function getValue($optionName, $default = false)
	{
		if (!isset($this->defaults[ $optionName ])) {
			throw new \Exception('No matching option');
		}
		if ($default) {
			return $this->defaults[ $optionName ];
		}

		return isset($this->stored[ $optionName ]) ? $this->stored[ $optionName ] : $this->defaults[ $optionName ];
	}

	/**
	 * @param $newOptions
	 *
	 * @return bool
	 *
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since ${VERSION}
	 */
	public function saveOptions($newOptions)
	{
		$this->stored = array_merge((array)$this->stored, $this->validateOptions($newOptions));

		return \Configuration::updateValue($this->optionsArrayName, serialize($this->stored));
	}

	/**
	 * @param array $newOptions
	 *
	 * @return array
	 * @throws \Exception
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 141110
	 */
	protected function validateOptions(Array $newOptions)
	{
		foreach ($newOptions as $_key => &$_value) {
			if (!isset($this->defaults[ $_key ]))
				unset($newOptions[ $_key ]);
			else if (gettype($_value) !== gettype($this->defaults[ $_key ]))
				$_value = $this->defaults[ $_key ];
			else if (!empty($this->validators[ $_key ])) {
				foreach ($this->validators[ $_key ] as $_validation_key => $_data) {
					// Can be a combination of numeric/associative keys.

					if (is_numeric($_validation_key)) // A numeric key?
					{
						$_validation_type = $_data; // As type.
						$_data            = null; // Nullify data.
					} else // Associative key with possible `$_data`.
					{
						/** @var mixed $_data */
						$_validation_type = $_validation_key;
					}
					switch ($_validation_type) // By validation type.
					{
						case 'string': // Validation only.

							if (!is_string($_value)) {
								$_value = $this->defaults[ $_key ];
								break 2; // Done validating here.
							}
							break; // Do next validation.

						case 'string:!empty': // Validation only.

							if (!is_string($_value) || empty($_value)) {
								$_value = $this->defaults[ $_key ];
								break 2; // Done validating here.
							}
							break; // Do next validation.

						case 'string:strlen': // Validation only.
							if (!is_string($_value) || !strlen($_value)) {
								$_value = $this->defaults[ $_key ];
								break 2; // Done validating here.
							}
							break; // Do next validation.

						case 'string:strlen <=': // Validation only.

							if (!is_string($_value) || (is_numeric($_data) && strlen($_value) > $_data)) {
								$_value = $this->defaults[ $_key ];
								break 2; // Done validating here.
							}
							break; // Do next validation.

						case 'string:strlen >=': // Validation only.

							if (!is_string($_value) || (is_numeric($_data) && strlen($_value) < $_data)) {
								$_value = $this->defaults[ $_key ];
								break 2; // Done validating here.
							}
							break; // Do next validation.

						case 'string:numeric': // Validation only.

							if (!is_string($_value) || !is_numeric($_value)) {
								$_value = $this->defaults[ $_key ];
								break 2; // Done validating here.
							}
							break; // Do next validation.

						case 'string:numeric <=': // Validation only.

							if (!is_string($_value) || !is_numeric($_value) || (is_numeric($_data) && $_value > $_data)) {
								$_value = $this->defaults[ $_key ];
								break 2; // Done validating here.
							}
							break; // Do next validation.

						case 'string:numeric >=': // Validation only.

							if (!is_string($_value) || !is_numeric($_value) || (is_numeric($_data) && $_value < $_data)) {
								$_value = $this->defaults[ $_key ];
								break 2; // Done validating here.
							}
							break; // Do next validation.

						case 'string:preg_match': // Validation only.

							if (!is_string($_value) || (is_string($_data) && !preg_match($_data, $_value))) {
								$_value = $this->defaults[ $_key ];
								break 2; // Done validating here.
							}
							break; // Do next validation.

						case 'string:in_array': // Validation only.

							if (!is_string($_value) || (is_array($_data) && !in_array($_value, $_data))) {
								$_value = $this->defaults[ $_key ];
								break 2; // Done validating here.
							}
							break; // Do next validation.

						case 'string:strtolower': // Validation w/procedure.

							if (!is_string($_value)) {
								$_value = $this->defaults[ $_key ];
								break 2; // Done validating here.
							} else // Just force lowercase.
							{
								$_value = strtolower($_value);
								break; // Do next validation.
							}

						case 'string:preg_replace': // Validation w/procedure.

							if (!is_string($_value)) {
								$_value = $this->defaults[ $_key ];
								break 2; // Done validating here.
							} else if (is_array($_data) && isset($_data['replace']) && isset($_data['with']))
								$_value = preg_replace($_data['replace'], $_data['with'], $_value);

							break; // Do next validation.

						case 'string:date': // Validation w/procedure.
							$time =  strtotime($_value);
							if (!is_string($_value) && !is_numeric($time)) {
								$_value = $this->defaults[ $_key ];
								break 2; // Done validating here.
							} else
								$_value = date('Y-m-d', $time);

							break; // Do next validation.

						case 'array': // Validation only.

							if (!is_array($_value)) {
								$_value = $this->defaults[ $_key ];
								break 2; // Done validating here.
							}
							break; // Do next validation.

						case 'array:!empty': // Validation only.

							if (!is_array($_value) || empty($_value)) {
								$_value = $this->defaults[ $_key ];
								break 2; // Done validating here.
							}
							break; // Do next validation.

						case 'array:count <=': // Validation only.

							if (!is_array($_value) || (is_numeric($_data) && count($_value) > $_data)) {
								$_value = $this->defaults[ $_key ];
								break 2; // Done validating here.
							}
							break; // Do next validation.

						case 'array:count >=': // Validation only.

							if (!is_array($_value) || (is_numeric($_data) && count($_value) < $_data)) {
								$_value = $this->defaults[ $_key ];
								break 2; // Done validating here.
							}
							break; // Do next validation.

						case 'array:containing': // Validation only.

							if (!is_array($_value) || !in_array($_data, $_value, true)) {
								$_value = $this->defaults[ $_key ];
								break 2; // Done validating here.
							}
							break; // Do next validation.

						case 'array:containing-any-of': // Validation only.

							if (!is_array($_value)) {
								$_value = $this->defaults[ $_key ];
								break 2; // Done validating here.
							} else if (is_array($_data)) {
								foreach ($_data as $_data_value) {
									if (in_array($_data_value, $_value, true))
										break;
								} // Do next validation.

								unset($_data_value); // Housekeeping.
								$_value = $this->defaults[ $_key ];
								break 2; // Done validating here.
							}
							break; // Do next validation.

						case 'array:containing-all-of': // Validation only.

							if (!is_array($_value)) {
								$_value = $this->defaults[ $_key ];
								break 2; // Done validating here.
							} else if (is_array($_data)) {
								foreach ($_data as $_data_value) {
									if (!in_array($_data_value, $_value, true)) {
										$_value = $this->defaults[ $_key ];
										break 2; // Done validating here.
									}
								}
								unset($_data_value); // Housekeeping.
							}
							break; // Do next validation.
						case 'bool': // Validation only.
							$_value = (bool)$_value;
							break; // Do next validation.

						default: // Exception.
							throw new \Exception(sprintf('Unknown validation type: `%1$s`.', $_validation_type));
					}
				}
				unset($_validation_key, $_validation_type, $_data);
			}
		}
		unset($_key, $_value); // A little housekeeping.

		return $newOptions; // Returns all options (fully validated).
	}

	/**
	 * Returns all booleans defined in validators array
	 * @return array assoc $key => $value
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 141110
	 */
	public function getAllBooleans(){
		$ret = array();
		foreach ($this->defaults as $k => $v) {
			if($this->validators[$k] == 'bool'){
				$ret[$k] = $this->defaults[$k];
			}
		}
		return $ret;
	}

	/**
	 * @param bool $defaults
	 *
	 * @return array
	 *
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since ${VERSION}
	 */
	public function getOptionsArray($defaults = false)
	{
		return $defaults ? $this->defaults : $this->stored;
	}

	public function deleteAllOptions(){
		return \Configuration::deleteByName($this->optionsArrayName);
	}
}