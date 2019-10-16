<?php
/**
 * @package     Gerens/CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author      João Vinezof
 * 
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 
 * Classe Service
 * 
 * @package     Gerens/CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author      João Vinezof
 * 
 */
class CI_Service {

	/**
	 * Class constructor
	 *
	 * @link	https://github.com/bcit-ci/CodeIgniter/issues/5332
	 * @return	void
	 */
	public function __construct() {}

	/**
	 * __get magic
	 *
	 * Allows models to access CI's loaded classes using the same
	 * syntax as controllers.
	 *
	 * @param	string	$key
	 */
	public function __get($key)
	{
		// Debugging note:
		//	If you're here because you're getting an error message
		//	saying 'Undefined Property: system/core/Model.php', it's
		//	most likely a typo in your model code.
		return get_instance()->$key;
	}

}