<?php
/**
 *	soap_gateway.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

/**
 *	SOAP�����ȥ������δ��쥯�饹
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_SoapGateway
{
	/**#@+
	 *	@access	private
	 */

	/**
	 *	@var	object	Ethna_Controller	controller���֥�������
	 */
	var	$controller;

	/**#@-*/

	/**
	 *	Ethna_SoapGateway���饹�Υ��󥹥ȥ饯��
	 *
	 *	@access	public
	 */
	function Ethna_SoapGateway()
	{
		$this->controller =& $GLOBALS['controller'];
	}

	/**
	 *	SOAP����������¹Ԥ���
	 *
	 *	@access	public
	 */
	function dispatch()
	{
		$this->controller->trigger();
	}

	/**
	 *	���ץꥱ������������Ͱ������������
	 *
	 *	@access	public
	 *	@return	array	���ץꥱ������������Ͱ���
	 */
	function &getApp()
	{
		$action_form =& $this->controller->getActionForm();
		return $action_form->app_vars;
	}

	/**
	 *	���顼�����ɤ��������
	 *
	 *	@access	public
	 *	@return	int		���顼������(null�ʤ饨�顼̵��)
	 */
	function getErrorCode()
	{
		$action_error =& $this->controller->getActionError();
		if ($action_error->count() == 0) {
			return null;
		}
		
		// �ǽ��1�Ĥ��֤�
		$error_list = $action_error->getErrorList();
		$error =& $error_list[0];

		return $error->getCode();
	}

	/**
	 *	���顼��å��������������
	 *
	 *	@access	public
	 *	@return	string	���顼��å�����(null�ʤ饨�顼̵��)
	 */
	function getErrorMessage()
	{
		$action_error =& $this->controller->getActionError();
		if ($action_error->count() == 0) {
			return null;
		}

		// �ǽ��1�Ĥ��֤�
		$message_list = $action_error->getMessageList();
		$message = $message_list[0];

		return $message;
	}
}

/**
 *	���ꤵ�줿����ȥ�������б����륲���ȥ��������饹�����ɤ��������륯�饹
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna	
 */
class Ethna_SoapGatewayGenerator
{
	/**#@+
	 *	@access	private
	 */

	/**
	 *	@var	object	Ethna_Controller	controller���֥�������
	 */
	var	$controller;

	/**
	 *	@var	object	Ethna_Config		���ꥪ�֥�������
	 */
	var	$config;

	/**
	 *	@var	object	Ethna_ActionError	action error���֥�������
	 */
	var	$action_error;

	/**
	 *	@var	object	Ethna_ActionError	action error���֥�������(��ά��)
	 */
	var	$ae;

	/**
	 *	@var	string		�����ȥ��������饹������
	 */
	var	$gateway;

	/**
	 *	@var	string		�����ȥ��������饹����̾
	 */
	var	$name;

	/**
	 *	@var	string		�����ȥ��������饹�͡��ॹ�ڡ���
	 */
	var	$namespace;

	/**#@-*/

	/**
	 *	Ethna_SoapGatewayGenerator���饹�Υ��󥹥ȥ饯��
	 *
	 *	@access	public
	 */
	function Ethna_SoapGatewayGenerator()
	{
		$this->controller =& $GLOBALS['controller'];
		$this->config =& $this->controller->getConfig();
		$this->action_error = null;
		$this->ae =& $this->action_error;
		$this->gateway = "";
		$this->name = $this->controller->getAppId();
		$this->namespace = $this->_getNameSpace();
	}

	/**
	 *	�����ȥ��������饹�����ɤ���������
	 *
	 *	@access	public
	 *	@return	string	�����ȥ������饹������
	 */
	function generate()
	{
		$prev_type = $this->controller->getClientType();
		$this->controller->setClientType(CLIENT_TYPE_SOAP);

		$this->gateway .= $this->_getHeader();
		$this->gateway .= $this->_getEntry();
		$this->gateway .= $this->_getFooter();

		$this->controller->setClientType($prev_type);

		return $this->gateway;
	}

	/**
	 *	�����ȥ��������饹�Υ��饹̾���������
	 *
	 *	@access	public
	 *	@return	string	�����ȥ��������饹�Υ��饹̾
	 */
	function getClassName()
	{
		return sprintf("Ethna_%sSoapGateway", $this->name);
	}

	/**
	 *	�����ȥ��������饹������(�إå���ʬ)���������
	 *
	 *	@access	private
	 *	@return	string	�����ȥ��������饹������(�إå���ʬ)
	 */
	function _getHeader()
	{
		$header = sprintf("class Ethna_%sSoapGateway extends Ethna_SoapGateway {\n", $this->name);

		return $header;
	}

	/**
	 *	�����ȥ��������饹������(�᥽�åɥ���ȥ���ʬ)���������
	 *
	 *	@access	private
	 *	@return	string	�����ȥ��������饹������(�᥽�åɥ���ȥ���ʬ)
	 */
	function _getEntry()
	{
		$entry = "";
		foreach ($this->controller->soap_action as $k => $v) {
			$action_form_name = $this->controller->getActionFormName($k);
			$form =& new $action_form_name($this->controller);
			$arg_list = array_keys($form->form);

			$entry .= "  function $k(";
			for ($i = 0; $i < count($arg_list); $i++) {
				if ($i > 0) {
					$entry .= ", ";
				}
				$entry .= "\$" . $arg_list[$i];
			}
			$entry .= ") {\n";

			$entry .= "    \$_SERVER['REQUEST_METHOD'] = 'post';\n";
			$entry .= "    \$_POST['action_$k'] = 'dummy';\n";
			foreach ($arg_list as $arg) {
				$entry .= "    \$_POST['$arg'] = \$$arg;\n";
			}
			
			$entry .= "    \$this->dispatch();\n";

			$entry .= "    \$app =& \$this->getApp();\n";
			$entry .= "    \$errorcode = \$this->getErrorCode();\n";
			$entry .= "    \$errormessage = \$this->getErrorMessage();\n";
			$entry .= "    \$retval = array();\n";
			foreach ($form->retval as $k => $v) {
				$entry .= "    \$retval['$k'] = \$app['$k'];\n";
			}
			$entry .= "    \$retval['errorcode'] = \$errorcode;\n";
			$entry .= "    \$retval['errormessage'] = \$errormessage;\n";

			$entry .= "    return \$retval;\n";
			$entry .= "  }\n";
		}
		return $entry;
	}

	/**
	 *	�����ȥ��������饹������(�եå���ʬ)���������
	 *
	 *	@access	private
	 *	@return	string	�����ȥ��������饹������(�եå���ʬ)
	 */
	function _getFooter()
	{
		$footer = "}\n";

		return $footer;
	}

	/**
	 *	�͡��ॹ�ڡ������������
	 *
	 *	@access	private
	 *	@return	string	�͡��ॹ�ڡ���
	 */
	function _getNameSpace()
	{
		return sprintf("%s/%s", $this->config->get('url'), $this->name);
	}
}
?>