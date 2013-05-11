<?php
// vim: foldmethod=marker
/**
 *	Ethna_ClassFactory.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

// {{{ Ethna_ClassFactory
/**
 *	Ethna�ե졼�����Υ��֥����������������ȥ�����
 *
 *	DI����ƥʤ����Ȥ������Ȥ�ͤ��ޤ�����Ethna�ǤϤ������٤�ñ��ʤ�Τ�
 *	α��Ƥ����ޤ������ץꥱ��������٥�DI���������ϥե��륿���������
 *	�ȤäƼ¸����뤳�Ȥ����ޤ���
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_ClassFactory
{
	/**#@+
	 *	@access	private
	 */

	/**	@var	object	Ethna_Controller	controller���֥������� */
	var	$controller;

	/**	@var	object	Ethna_Controller	controller���֥�������(��ά��) */
	var	$ctl;
	
	/**	@var	array	���饹��� */
	var	$class = array();

	/**	@var	array	�����Ѥߥ��֥������ȥ���å��� */
	var	$object = array();

	/**#@-*/


	/**
	 *	Ethna_ClassFactory���饹�Υ��󥹥ȥ饯��
	 *
	 *	@access	public
	 *	@param	object	Ethna_Controller	&$controller	controller���֥�������
	 *	@param	array						$class			���饹���
	 */
	function Ethna_ClassFactory(&$controller, $class)
	{
		$this->controller = $controller;
		$this->ctl = $controller;
		$this->class = $class;
	}

	/**
	 *	���饹�������б����륪�֥������Ȥ��֤�
	 *
	 *	@access	public
	 *	@param	string	$key	���饹����
	 *	@param	bool	$weak	���֥������Ȥ�̤�����ξ��ζ��������ե饰(default: false)
	 *	@return	object	�������줿���֥�������(���顼�ʤ�null)
	 */
	function &getObject($key, $weak = false)
	{
		if (isset($this->class[$key]) == false) {
			return null;
		}
		$class_name = $this->class[$key];
		if (isset($this->object[$key]) && is_object($this->object[$key])) {
			return $this->object[$key];
		}

		$method = sprintf('_getObject_%s', ucfirst($key));
		if (method_exists($this, $method)) {
			$obj = $this->$method($class_name);
		} else {
			$obj = new $class_name();
		}
		$this->object[$key] = $obj;

		return $obj;
	}

	/**
	 *	���饹�������б����륯�饹̾���֤�
	 *
	 *	@access	public
	 *	@param	string	$key	���饹����
	 *	@return	string	���饹̾
	 */
	function getObjectName($key)
	{
		if (isset($this->class[$key]) == false) {
			return null;
		}

		return $this->class[$key];
	}

	/**
	 *	���֥������������᥽�å�(backend)
	 *
	 *	@access	protected
	 *	@param	string	$class_name		���饹̾
	 *	@return	object	�������줿���֥�������(���顼�ʤ�null)
	 */
	function &_getObject_Backend($class_name)
	{
		$_ret_object = new $class_name($this->ctl);
		return $_ret_object;
	}

	/**
	 *	���֥������������᥽�å�(config)
	 *
	 *	@access	protected
	 *	@param	string	$class_name		���饹̾
	 *	@return	object	�������줿���֥�������(���顼�ʤ�null)
	 */
	function &_getObject_Config($class_name)
	{
		$_ret_object = new $class_name($this->ctl);
		return $_ret_object;
	}

	/**
	 *	���֥������������᥽�å�(i18n)
	 *
	 *	@access	protected
	 *	@param	string	$class_name		���饹̾
	 *	@return	object	�������줿���֥�������(���顼�ʤ�null)
	 */
	function &_getObject_I18n($class_name)
	{
		$_ret_object = new $class_name($this->ctl->getDirectory('locale'), $this->ctl->getAppId());
		return $_ret_object;
	}

	/**
	 *	���֥������������᥽�å�(session)
	 *
	 *	@access	protected
	 *	@param	string	$class_name		���饹̾
	 *	@return	object	�������줿���֥�������(���顼�ʤ�null)
	 */
	function &_getObject_Session($class_name)
	{
		$_ret_object = new $class_name($this->ctl->getAppId(), $this->ctl->getDirectory('tmp'));
		return $_ret_object;
	}

	/**
	 *	���֥������������᥽�å�(sql)
	 *
	 *	@access	protected
	 *	@param	string	$class_name		���饹̾
	 *	@return	object	�������줿���֥�������(���顼�ʤ�null)
	 */
	function &_getObject_Sql($class_name)
	{
		$_ret_object = new $class_name($this->ctl);
		return $_ret_object;
	}
}
// }}}

