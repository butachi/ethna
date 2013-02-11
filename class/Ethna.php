<?php
// vim: foldmethod=marker
/**
 *	Ethna.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

/** Ethna depends on PEAR */
include_once('PEAR.php');

/** Ethna (*currently*) depends on Smarty */
include_once('Smarty/Smarty.class.php');

/** Ethna�١����ǥ��쥯�ȥ���� */
define('ETHNA_BASE',  dirname(__FILE__));


/** Ethna�����Х��ѿ�: ���顼������Хå��ؿ� */
$GLOBALS['_Ethna_error_callback_list'] = array();

/** Ethna�����Х��ѿ�: ���顼��å����� */
$GLOBALS['_Ethna_error_message_list'] = array();


// {{{ Ethna
/**
 *	Ethna�ե졼�������饹
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna extends PEAR
{
	/**#@+
	 *	@access	private
	 */

	/**#@-*/

	/**
	 *	Ethna_Error���֥������Ȥ���������(���顼��٥�:E_USER_ERROR)
	 *
	 *	@access	public
	 *	@param	string	$message			���顼��å�����
	 *	@param	int		$code				���顼������
	 *	@static
	 */
	function &raiseError($message, $code = Ethna_Const::E_GENERAL)
	{
		$userinfo = null;
		if (func_num_args() > 2) {
			$userinfo = array_slice(func_get_args(), 2);
			if (count($userinfo) == 1 && is_array($userinfo[0])) {
				$userinfo = $userinfo[0];
			}
		}
		return PEAR::raiseError($message, $code, PEAR_ERROR_RETURN, E_USER_ERROR, $userinfo, 'Ethna_Error');
	}

	/**
	 *	Ethna_Error���֥������Ȥ���������(���顼��٥�:E_USER_WARNING)
	 *
	 *	@access	public
	 *	@param	string	$message			���顼��å�����
	 *	@param	int		$code				���顼������
	 *	@static
	 */
	function &raiseWarning($message, $code = Ethna_Const::E_GENERAL)
	{
		$userinfo = null;
		if (func_num_args() > 2) {
			$userinfo = array_slice(func_get_args(), 2);
			if (count($userinfo) == 1 && is_array($userinfo[0])) {
				$userinfo = $userinfo[0];
			}
		}
		return PEAR::raiseError($message, $code, PEAR_ERROR_RETURN, E_USER_WARNING, $userinfo, 'Ethna_Error');
	}

	/**
	 *	Ethna_Error���֥������Ȥ���������(���顼��٥�:E_USER_NOTICE)
	 *
	 *	@access	public
	 *	@param	string	$message			���顼��å�����
	 *	@param	int		$code				���顼������
	 *	@static
	 */
	function &raiseNotice($message, $code = Ethna_Const::E_GENERAL)
	{
		$userinfo = null;
		if (func_num_args() > 2) {
			$userinfo = array_slice(func_get_args(), 2);
			if (count($userinfo) == 1 && is_array($userinfo[0])) {
				$userinfo = $userinfo[0];
			}
		}
		return PEAR::raiseError($message, $code, PEAR_ERROR_RETURN, E_USER_NOTICE, $userinfo, 'Ethna_Error');
	}

	/**
	 *	���顼ȯ������(�ե졼�����Ȥ��Ƥ�)������Хå��ؿ������ꤹ��
	 *
	 *	@access	public
	 *	@param	mixed	string:������Хå��ؿ�̾ array:������Хå����饹(̾|���֥�������)+�᥽�å�̾
	 *	@static
	 */
	function setErrorCallback($callback)
	{
		$GLOBALS['_Ethna_error_callback_list'][] = $callback;
	}

	/**
	 *	���顼ȯ�����ν�����Ԥ�(������Хå��ؿ�/�᥽�åɤ�ƤӽФ�)
	 *	
	 *	@access	public
	 *	@param	object	Ethna_Error		Ethna_Error���֥�������
	 *	@static
	 */
	function handleError(&$error)
	{
		for ($i = 0; $i < count($GLOBALS['_Ethna_error_callback_list']); $i++) {
			$callback =& $GLOBALS['_Ethna_error_callback_list'][$i];
			if (is_array($callback) == false) {
				call_user_func($callback, $error);
			} else if (is_object($callback[0])) {
				$object =& $callback[0];
				$method = $callback[1];

				// perform some more checks?
				$object->$method($error);
			} else {
				call_user_func($callback, $error);
			}
		}
	}
}
// }}}
