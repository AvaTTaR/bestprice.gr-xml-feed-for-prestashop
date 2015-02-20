<?php
/**
 * Project: coremodule
 * File: Core.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 11/11/2014
 * Time: 8:19 μμ
 * Since: 141110
 * Copyright: 2014 Panagiotis Vagenas
 */

namespace XDaRk_v141110;

if (!defined('_PS_VERSION_'))
	exit;

if(!class_exists('XDaRk_v141110\Core')) {
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Stub.php';
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Module.php';
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'CarrierModule.php';
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'PaymentModule.php';
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'AutoLoader.php';
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Constants.php';

	/**
	 * Class Core
	 * @package XDaRk
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 141110
	 *
	 * @property \XDaRk_v141110\Module          Module
	 * @property \XDaRk_v141110\Dir             Dir
	 * @property \XDaRk_v141110\File            File
	 * @property \XDaRk_v141110\Form            Form
	 * @property \XDaRk_v141110\Hooks           Hooks
	 * @property \XDaRk_v141110\Installer       Installer
	 * @property \XDaRk_v141110\Options         Options
	 * @property \XDaRk_v141110\XML             XML
	 * @property \XDaRk_v141110\Exception       Exception
	 * @property \XDaRk_v141110\Method          Method
	 * @property \XDaRk_v141110\String          String
	 * @property \XDaRk_v141110\Vars            Vars
	 * @property \XDaRk_v141110\Url             Url
	 * @property \XDaRk_v141110\Integer         Integer
	 */
	class Core implements Constants{
		public static $singletonClasses = array();

		public static $classes = array();

		public static $instanceClasses = array();

		public static $instanceNamespace;
		public static $instanceBaseDir;
		public static $instanceRootNSDir;


		public static $__REGEX_MATCH_ALL__ = '//';
		public static $__REGEX_MATCH_PHP_FILES = '/^.+\.php$/i';
		public static $__REGEX_HOOK_FUNCTION = '/^(hook)+.+$/';

		public $moduleInstance;
		public $instanceNamespaceClass;

		/**
		 * An instance-based reference to the global/static cache for the current blog ID & class extender.
		 *
		 * @var array An instance-based reference to the global/static cache for the current blog ID & class extender.
		 *
		 * @final Should NOT be overridden by class extenders.
		 *    Would be `final` if PHP allowed such a thing.
		 *
		 * @protected Accessible only to self & extenders.
		 */
		protected $static = array();

		public function __get( $name ) {
			if ( property_exists( $this, $name ) ) {
				return $this->{$name};
			}

			$nsName = ( in_array( $name, Core::$instanceClasses ) ? Core::$instanceNamespace : __NAMESPACE__ ) . '\\' . $name;

			if ( in_array( $name, Core::$instanceClasses ) ) {
				return $this->{$name} = new $nsName( $this->moduleInstance, $this );
			} elseif ( in_array( $name, Core::$classes ) ) {
				return $this->{$name} = new $nsName( $this->moduleInstance );
			} elseif ( in_array( $name, Core::$singletonClasses ) ) {
				return $this->{$name} = $nsName::getInstance();
			}


			return null;
		}

		public function __call( $name, $args ) {
			if ( method_exists( $this, $name ) ) {
				return call_user_func_array(array($this, $name), $args);
			} else if (method_exists($this->moduleInstance, $name)){
				return call_user_func_array(array($this->moduleInstance, $name), $args);
			}

			$nsName = ( in_array( $name, Core::$instanceClasses ) ? Core::$instanceNamespace : __NAMESPACE__ ) . '\\' . $name;

			if ( in_array( $name, Core::$instanceClasses ) ) {
				return new $nsName( $args );
			} elseif ( in_array( $name, Core::$classes ) ) {
				return new $nsName( $args );
			} elseif ( in_array( $name, Core::$singletonClasses ) ) {
				return $nsName::getInstance( $args );
			}


			return null;
		}


		public static function __callStatic($name, $arguments)
		{
			return Stub::$name($arguments);
		}

		public function __isset( $name ) {
			// hook functions to Hook class
			if ( Hooks::isHookFunction( $name )
			     || in_array( $name, Core::$instanceClasses )
			     || in_array( $name, Core::$classes )
			     || in_array( $name, Core::$singletonClasses )
			) {
				return true;
			}


			return false;
		}

		/**
		 * Returns the *Singleton* instance of this class.
		 *
		 * @staticvar Singleton $instance The *Singleton* instances of this class.
		 *
		 * @return $this The *Singleton* instance.
		 */
		public static function getInstance( \Module &$moduleInstance ) {
			static $instance = array();
			if ( ! isset( $instance[ $moduleInstance->name ] ) || null === $instance[ $moduleInstance->name ] ) {
				$instance[ $moduleInstance->name ] = new static( $moduleInstance );
			}

			return $instance[ $moduleInstance->name ];
		}

		/**
		 * Protected constructor to prevent creating a new instance of the
		 * *Singleton* via the `new` operator from outside of this class.
		 *
		 * @param \Module $moduleInstance
		 */
		protected function __construct( \Module &$moduleInstance ) {
			$this->instanceNamespaceClass = get_class( $this );
			$this->moduleInstance         = $moduleInstance;
		}

		/**
		 * PHP's `is_...()` type checks.
		 *
		 * @var array PHP `is_...()` type checks.
		 *    Keys correspond with type hints accepted by `check_arg_types()`.
		 *    Values are `is_...()` functions needed to test for each type.
		 *
		 * @final Should NOT be overridden by class extenders.
		 *    Would be `final` if PHP allowed such a thing.
		 *
		 * @protected Accessible only to self & extenders.
		 */
		protected static $___is_type_checks = array(
			'string'          => 'is_string',
			'!string'         => 'is_string',
			'string:!empty'   => 'is_string',
			'boolean'         => 'is_bool',
			'!boolean'        => 'is_bool',
			'boolean:!empty'  => 'is_bool',
			'bool'            => 'is_bool',
			'!bool'           => 'is_bool',
			'bool:!empty'     => 'is_bool',
			'integer'         => 'is_integer',
			'!integer'        => 'is_integer',
			'integer:!empty'  => 'is_integer',
			'int'             => 'is_integer',
			'!int'            => 'is_integer',
			'int:!empty'      => 'is_integer',
			'float'           => 'is_float',
			'!float'          => 'is_float',
			'float:!empty'    => 'is_float',
			'real'            => 'is_float',
			'!real'           => 'is_float',
			'real:!empty'     => 'is_float',
			'double'          => 'is_float',
			'!double'         => 'is_float',
			'double:!empty'   => 'is_float',
			'numeric'         => 'is_numeric',
			'!numeric'        => 'is_numeric',
			'numeric:!empty'  => 'is_numeric',
			'scalar'          => 'is_scalar',
			'!scalar'         => 'is_scalar',
			'scalar:!empty'   => 'is_scalar',
			'array'           => 'is_array',
			'!array'          => 'is_array',
			'array:!empty'    => 'is_array',
			'object'          => 'is_object',
			'!object'         => 'is_object',
			'object:!empty'   => 'is_object',
			'resource'        => 'is_resource',
			'!resource'       => 'is_resource',
			'resource:!empty' => 'is_resource',
			'null'            => 'is_null',
			'!null'           => 'is_null',
			'null:!empty'     => 'is_null'
		);

		public function l($string){
			$nameAr = explode('\\', get_class($this));
			$className = strtolower(end($nameAr));
			return $this->moduleInstance->l($string, $className);
		}

		/**
		 * Checks function/method arguments against a list of type hints.
		 *
		 * @note Very important for this method to remain HIGHLY optimized at all times.
		 *    This method is called MANY times throughout the entire WebSharks™ Core framework.
		 *
		 * @note This is about 6.5 times slower than `is_...()` checks alone (tested in PHP v5.3.13).
		 *    We've ALREADY put quite a bit of work into optimizing this as-is, so we might need an entirely new approach in the future.
		 *    Benchmarking this against straight `is..()` checks alone, is not really fair either; since this routine "enforces" type hints.
		 *    In the mean time, the benefits of using this method, far outweigh the cost in performance — in most cases.
		 *    ~ Hopefully we'll have better support for type hinting upon the release of PHP 6.0.
		 *
		 * @note Try NOT to `check_arg_types()` recursively (i.e. in recursive functions). It's really a waste of resources.
		 *    If a function is going to be called recursively, please design your function (while in recursion), to bypass `check_arg_types()`.
		 *
		 * @params-variable-length This method accepts any number of parameters (i.e. type hints, as seen below).
		 *
		 *    Arguments to this method should first include a variable-length list of type hints.
		 *
		 *    Format as follows: `check_arg_types('[type]', '[type]' ..., func_get_args())`.
		 *    Where type hint arguments MUST be ordered exactly the same as each argument requested by the function/method we're checking.
		 *    However, it's fine to exclude certain arguments from the end (i.e. any we don't need to check), or via exclusion w/ an empty string.
		 *
		 *    Where `[type]` can be any string (or array combination) of: `:!empty|int|integer|real|double|float|string|bool|boolean|array|object|resource|scalar|numeric|null|[instanceof]`.
		 *    Where `[instanceof]` can be used in cases where we need to check for a specific type of object instance. Anything not matching a standardized type, is assumed to be an `[instanceof]` check.
		 *    For performance reasons, `[type]` is caSe sensitive. Therefore, `INTeger` will NOT match `integer` (that would be invalid; resulting in a check requiring an instance of `INTeger`).
		 *
		 *    Negating certain types.
		 *    Example: `check_arg_types('!object', func_get_args())`.
		 *    Allows anything, except an object type.
		 *
		 *    Require values that are NOT `empty()`.
		 *    Example: `check_arg_types('string:!empty', func_get_args())`.
		 *    Requires a string that is NOT considered `empty()` by the PHP interpreter.
		 *
		 *    Require anything that is NOT `empty()`.
		 *    Example: `check_arg_types(':!empty', func_get_args())`.
		 *    Anything that is NOT considered `empty()` by the PHP interpreter.
		 *
		 *    Using an array of multiple type hints.
		 *    Example: `check_arg_types(array('string', 'object'), func_get_args())`.
		 *    Example: `check_arg_types(array('string:!empty', 'object'), func_get_args())`.
		 *    Allows either a string `OR` an object to be passed in as the first argument value.
		 *    In the second example, we allow a string (NOT empty) `OR` an object instance.
		 *
		 *    Array w/ an empty type hint value (NOT recommended).
		 *    Example: `check_arg_types(array('string', ''), func_get_args())`.
		 *    Allows a string, or anything else (so actually, anything is allowed here).
		 *    It would be VERY odd to do this. Just documenting this behavior for the sake of clarity.
		 *
		 *    Using an `[instanceof]` check.
		 *    Example: `check_arg_types('\\wsc_v000000_dev\\users', func_get_args())`.
		 *    Example: `check_arg_types(array('WP_User', '\\wsc_v000000_dev\\users'), func_get_args())`.
		 *    For practicality & performance reasons, we do NOT check `!` or `:!empty` in the case of `[instanceof]`.
		 *    It's VERY rare that one would need to require something that's NOT a specific object instance.
		 *    And, objects are NEVER empty anyway, according to PHPs `empty()` function.
		 *
		 * @note Ordinarily, the last argument to this method is a numerically indexed array of all arguments that were passed into the function/method we're checking.
		 *    Use `func_get_args()` as the last argument to this method. Example: `check_arg_types('[type]', '[type]' ..., func_get_args())`.
		 *
		 * @note For performance reasons, array keys in the last argument, MUST be indexed numerically.
		 *    Please make sure that `func_get_args()` is used as the last argument. Or, any array that uses numeric indexes, is also fine.
		 *    Associative arrays will cause PHP notices, due to undefined indexes. We're expecting a numerically indexed array of arguments here.
		 *
		 * @note If the last argument to this method is an integer, instead of an array; we treat the last argument as the number of required arguments.
		 *    Example: `check_arg_types('[type]', '[type]' ..., func_get_args(), 2)`. This requires a minimum of two argument values.
		 *    This is NOT needed in most cases. PHP should have already triggered a warning about missing arguments.
		 *
		 * @return boolean TRUE if all argument values can be validated against the list of type hints; else an exception is thrown.
		 *
		 * @throws exception If the last parameter is an integer indicating a number of required arguments,
		 *    and the number of arguments passed in, is less than this number.
		 * @throws exception If even ONE argument is passed incorrectly.
		 *
		 * @final May NOT be overridden by extenders.
		 * @public Available for public usage.
		 */
		final public function check_arg_types() {
			$_arg_type_hints__args__required_args = func_get_args();
			$_last_arg_value                      = array_pop( $_arg_type_hints__args__required_args );
			$required_args                        = 0; // Default number of required arguments.

			if ( is_integer( $_last_arg_value ) ) // Required arguments?
			{
				$required_args = $_last_arg_value; // Number of required arguments.
				$args          = (array) array_pop( $_arg_type_hints__args__required_args );
			} else {
				$args = (array) $_last_arg_value;
			} // Use `$_last_arg_value` as `$args`.

			$arg_type_hints      = $_arg_type_hints__args__required_args; // Type hints (remaining arguments).
			$total_args          = count( $args ); // Total arguments passed into the function/method we're checking.
			$total_arg_positions = $total_args - 1; // Based on total number of arguments.

			// Commenting for performance. NOT absolutely necessary.
			# unset($_arg_type_hints__args__required_args, $_last_arg_value); // Housekeeping.

			if ( $total_args < $required_args ) // Enforcing minimum args?
			{
				throw $this->Exception->factory( // Need to be VERY descriptive here.
					$this->method( __FUNCTION__ ) . '#args_missing', get_defined_vars(),
					sprintf( $this->moduleInstance->l( 'Missing required argument(s); `%1$s` requires `%2$s`, `%3$s` given.' ),
						$this->Method->get_backtrace_callers( debug_backtrace(), 'last' ), $required_args, $total_args ) .
					' ' . sprintf( $this->moduleInstance->l( 'Got: `%1$s`.' ), $this->©var->dump( $args ) ) );
			}

			if ( $total_args === 0 ) {
				return true;
			} // Stop here (no arguments to check).

			foreach ( $arg_type_hints as $_arg_position => $_arg_type_hints ) // Type hints.
			{
				if ( $_arg_position > $total_arg_positions ) // Argument not even passed in?
				{
					continue;
				} // Argument was not even passed in (we don't need to check this value).

				unset( $_last_arg_type_key ); // Unset before iterating (define below if necessary).

				foreach ( ( $_arg_types = (array) $_arg_type_hints ) as $_arg_type_key => $_arg_type ) {
					switch ( ( $_arg_type = (string) $_arg_type ) ) // Checks type requirements.
					{
						case '': // Anything goes (there are NO requirements).
							break 2; // We have a valid type/value here.

						/****************************************************************************/

						case ':!empty': // Anything goes. But check if it's empty.
							if ( empty( $args[ $_arg_position ] ) ) // Is empty?
							{
								if ( ! isset( $_last_arg_type_key ) ) {
									$_last_arg_type_key = count( $_arg_types ) - 1;
								}

								if ( $_arg_type_key === $_last_arg_type_key ) // Exhausted list of possible types.
								{
									$problem = array(
										'types'    => $_arg_types,
										'position' => $_arg_position,
										'value'    => $args[ $_arg_position ],
										'empty'    => empty( $args[ $_arg_position ] )
									);
									break 3; // We DO have a problem here.
								}
							} else {
								break 2;
							} // We have a valid type/value here.

							break 1; // Default break 1; and continue type checking.

						/****************************************************************************/

						case 'string': // All of these fall under `!is_...()` checks.
						case 'boolean':
						case 'bool':
						case 'integer':
						case 'int':
						case 'float':
						case 'real':
						case 'double':
						case 'numeric':
						case 'scalar':
						case 'array':
						case 'object':
						case 'resource':
						case 'null':

							$is_ = static::$___is_type_checks[ $_arg_type ];

							if ( ! $is_( $args[ $_arg_position ] ) ) // Not this type?
							{
								if ( ! isset( $_last_arg_type_key ) ) {
									$_last_arg_type_key = count( $_arg_types ) - 1;
								}

								if ( $_arg_type_key === $_last_arg_type_key ) // Exhausted list of possible types.
								{
									$problem = array(
										'types'    => $_arg_types,
										'position' => $_arg_position,
										'value'    => $args[ $_arg_position ],
										'empty'    => empty( $args[ $_arg_position ] )
									);
									break 3; // We DO have a problem here.
								}
							} else {
								break 2;
							} // We have a valid type/value here.

							break 1; // Default break 1; and continue type checking.

						/****************************************************************************/

						case '!string': // All of these fall under `is_...()` checks.
						case '!boolean':
						case '!bool':
						case '!integer':
						case '!int':
						case '!float':
						case '!real':
						case '!double':
						case '!numeric':
						case '!scalar':
						case '!array':
						case '!object':
						case '!resource':
						case '!null':

							$is_ = static::$___is_type_checks[ $_arg_type ];

							if ( $is_( $args[ $_arg_position ] ) ) // Is this type?
							{
								if ( ! isset( $_last_arg_type_key ) ) {
									$_last_arg_type_key = count( $_arg_types ) - 1;
								}

								if ( $_arg_type_key === $_last_arg_type_key ) // Exhausted list of possible types.
								{
									$problem = array(
										'types'    => $_arg_types,
										'position' => $_arg_position,
										'value'    => $args[ $_arg_position ],
										'empty'    => empty( $args[ $_arg_position ] )
									);
									break 3; // We DO have a problem here.
								}
							} else {
								break 2;
							} // We have a valid type/value here.

							break 1; // Default break 1; and continue type checking.

						/****************************************************************************/

						case 'string:!empty': // These are `!is_...()` || `empty()` checks.
						case 'boolean:!empty':
						case 'bool:!empty':
						case 'integer:!empty':
						case 'int:!empty':
						case 'float:!empty':
						case 'real:!empty':
						case 'double:!empty':
						case 'numeric:!empty':
						case 'scalar:!empty':
						case 'array:!empty':
						case 'object:!empty':
						case 'resource:!empty':
						case 'null:!empty':

							$is_ = static::$___is_type_checks[ $_arg_type ];

							if ( ! $is_( $args[ $_arg_position ] ) || empty( $args[ $_arg_position ] ) ) // Now, have we exhausted the list of possible types?
							{
								if ( ! isset( $_last_arg_type_key ) ) {
									$_last_arg_type_key = count( $_arg_types ) - 1;
								}

								if ( $_arg_type_key === $_last_arg_type_key ) // Exhausted list of possible types.
								{
									$problem = array(
										'types'    => $_arg_types,
										'position' => $_arg_position,
										'value'    => $args[ $_arg_position ],
										'empty'    => empty( $args[ $_arg_position ] )
									);
									break 3; // We DO have a problem here.
								}
							} else {
								break 2;
							} // We have a valid type/value here.

							break 1; // Default break 1; and continue type checking.

						/****************************************************************************/

						default: // Assume object `instanceof` in this default case handler.
							// For practicality & performance reasons, we do NOT check `!` or `:!empty` here.
							// It's VERY rare that one would need to require something that's NOT a specific object instance.
							// Objects are NEVER empty anyway, according to PHPs `empty()` function.

							if ( ! ( $args[ $_arg_position ] instanceof $_arg_type ) ) {
								if ( ! isset( $_last_arg_type_key ) ) {
									$_last_arg_type_key = count( $_arg_types ) - 1;
								}

								if ( $_arg_type_key === $_last_arg_type_key ) // Exhausted list of possible types.
								{
									$problem = array(
										'types'    => $_arg_types,
										'position' => $_arg_position,
										'value'    => $args[ $_arg_position ],
										'empty'    => empty( $args[ $_arg_position ] )
									);
									break 3; // We DO have a problem here.
								}
							} else {
								break 2;
							} // We have a valid type for this arg.

							break 1; // Default break 1; and continue type checking.
					}
				}
			}
			// Commenting for performance. NOT absolutely necessary.
			# unset($_arg_position, $_arg_type_hints, $_arg_types, $_arg_type_key, $_last_arg_type_key, $_arg_type, $is_);

			if ( ! empty( $problem ) ) // We have a problem!
			{
				$position   = $problem['position'] + 1;
				$types      = implode( '|', $problem['types'] );
				$empty      = ( $problem['empty'] ) ? $this->moduleInstance->l( 'empty' ) . ' ' : '';
				$type_given = ( is_object( $problem['value'] ) ) ? get_class( $problem['value'] ) : gettype( $problem['value'] );

				throw $this->Exception->factory( // Need to be VERY descriptive here.
					$this->method( __FUNCTION__ ) . '#invalid_args', get_defined_vars(),
					sprintf( $this->moduleInstance->l( 'Argument #%1$s passed to `%2$s` requires `%3$s`, %4$s`%5$s` given.' ),
						$position, $this->Method->get_backtrace_callers( debug_backtrace(), 'last' ), $types, $empty, $type_given ) .
					' ' . sprintf( $this->moduleInstance->l( 'Got: `%1$s`.' ), $this->©var->dump( $args ) ) );
			}

			return true; // Default return value (no problem).
		}

		/**
		 * Gets `__METHOD__` for current class.
		 *
		 * @param string $function Pass `__FUNCTION__` from any class member.
		 *    Current class is prepended to this (very much like `__METHOD__`).
		 *
		 * @return string `__METHOD__` for current class; i.e. `get_class($this)`.
		 *
		 * @final May NOT be overridden by extenders.
		 * @public Available for public usage.
		 */
		final public function method( $function ) {
			$function = (string) $function; // Force string.

			return $this->instanceNamespaceClass . '::' . $function;
		}

		/**
		 * Private clone method to prevent cloning of the instance of the
		 * *Singleton* instance.
		 *
		 * @return void
		 */
		private function __clone() {
		}

		/**
		 * Private unserialize method to prevent unserializing of the *Singleton*
		 * instance.
		 *
		 * @return void
		 */
		private function __wakeup() {
		}
	}
}