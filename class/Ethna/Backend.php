<?php
// vim: foldmethod=marker
/**
 *	Ethna_Backend.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

/**
 *	�Хå�����ɽ������饹
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_Backend
{
	/**#@+
	 *	@access		private
	 */

	/**	@var	object	Ethna_Controller	controller���֥������� */
	var	$controller;

	/**	@var	object	Ethna_Controller	controller���֥�������($controller�ξ�ά��) */
	var	$ctl;

	/**	@var	object	Ethna_Config		���ꥪ�֥������� */
	var	$config;

	/**	@var	object	Ethna_ActionError	��������󥨥顼���֥������� */
	var $action_error;

	/**	@var	object	Ethna_ActionError	��������󥨥顼���֥�������($action_error�ξ�ά��) */
	var $ae;

	/**	@var	object	Ethna_ActionForm	���������ե����४�֥������� */
	var $action_form;

	/**	@var	object	Ethna_ActionForm	���������ե����४�֥�������($action_form�ξ�ά��) */
	var $af;

	/**	@var	object	Ethna_ActionClass	��������󥯥饹���֥������� */
	var $action_class;

	/**	@var	object	Ethna_ActionClass	��������󥯥饹���֥�������($action_class�ξ�ά��) */
	var $ac;

	/**	@var	object	Ethna_Session		���å���󥪥֥������� */
	var $session;

	/**	@var	array	�ޥ͡����㥪�֥������ȥ���å��� */
	var $manager = array();

	/**#@-*/


	/**
	 *	Ethna_Backend���饹�Υ��󥹥ȥ饯��
	 *
	 *	@access	public
	 *	@param	object	Ethna_Controller	&$controller	����ȥ��饪�֥�������
	 */
	function Ethna_Backend($controller)
	{
		// ���֥������Ȥ�����
		$this->controller = $controller;
		$this->ctl = $this->controller;

		$this->config = $controller->getConfig();

		$this->action_error = $controller->getActionError();
		$this->ae = $this->action_error;
		$this->action_form = $controller->getActionForm();
		$this->af = $this->action_form;
		$this->action_class = null;
		$this->ac = $this->action_class;

		$this->session = $this->controller->getSession();

		// �ޥ͡����㥪�֥������Ȥ�����(TODO: create on demand)
		$manager_list = $controller->getManagerList();
		foreach ($manager_list as $key => $value) {
			$class_name = $this->controller->getManagerClassName($value);
			$this->manager[$value] = new $class_name($this);
		}

		foreach ($manager_list as $key => $value) {
			foreach ($manager_list as $k => $v) {
				if ($v == $value) {
					/* skip myself */
					continue;
				}
				$this->manager[$value]->$k = $this->manager[$v];
			}
		}
	}

	/**
	 *	controller���֥������ȤؤΥ�������(R)
	 *
	 *	@access	public
	 *	@return	object	Ethna_Controller	controller���֥�������
	 */
	function getController()
	{
		return $this->controller;
	}

	/**
	 *	���ꥪ�֥������ȤؤΥ�������(R)
	 *
	 *	@access	public
	 *	@return	object	Ethna_Config		���ꥪ�֥�������
	 */
	function getConfig()
	{
		return $this->config;
	}

	/**
	 *	���ץꥱ�������ID���֤�
	 *
	 *	@access	public
	 *	@return	string	���ץꥱ�������ID
	 */
	function getAppId()
	{
		return $this->controller->getAppId();
	}

	/**
	 *	��������󥨥顼���֥������ȤΥ�������(R)
	 *
	 *	@access	public
	 *	@return	object	Ethna_ActionError	��������󥨥顼���֥�������
	 */
	function getActionError()
	{
		return $this->action_error;
	}

	/**
	 *	���������ե����४�֥������ȤΥ�������(R)
	 *
	 *	@access	public
	 *	@return	object	Ethna_ActionForm	���������ե����४�֥�������
	 */
	function getActionForm()
	{
		return $this->action_form;
	}

	/**
	 *	���������ե����४�֥������ȤΥ�������(W)
	 *
	 *	@access	public
	 */
	function setActionForm(&$action_form)
	{
		$this->action_form = $action_form;
        $this->af = $action_form;
	}

	/**
	 *	�¹���Υ�������󥯥饹���֥������ȤΥ�������(R)
	 *
	 *	@access	public
	 *	@return	mixed	Ethna_ActionClass:��������󥯥饹 null:��������󥯥饹̤��
	 */
	function getActionClass()
	{
		return $this->action_class;
	}

	/**
	 *	�¹���Υ�������󥯥饹���֥������ȤΥ�������(W)
	 *
	 *	@access	public
	 */
	function setActionClass(&$action_class)
	{
        $this->action_class = $action_class;
        $this->ac = $action_class;
	}

	/**
	 *	���å���󥪥֥������ȤΥ�������(R)
	 *
	 *	@access	public
	 *	@return	object	Ethna_Session	���å���󥪥֥�������
	 */
	function getSession()
	{
		return $this->session;
	}

	/**
	 *	�ޥ͡����㥪�֥������ȤؤΥ�������(R)
	 *
	 *	@access	public
	 *	@return	object	Ethna_AppManager	�ޥ͡����㥪�֥�������
	 */
	function getManager($type)
	{
		if (isset($this->manager[$type])) {
			return $this->manager[$type];
		}
		return null;
	}

	/**
	 *	���ץꥱ�������Υ١����ǥ��쥯�ȥ���������
	 *
	 *	@access	public
	 *	@return	string	�١����ǥ��쥯�ȥ�Υѥ�̾
	 */
	function getBasedir()
	{
		return $this->controller->getBasedir();
	}

	/**
	 *	���ץꥱ�������Υƥ�ץ졼�ȥǥ��쥯�ȥ���������
	 *
	 *	@access	public
	 *	@return	string	�ƥ�ץ졼�ȥǥ��쥯�ȥ�Υѥ�̾
	 */
	function getTemplatedir()
	{
		return $this->controller->getTemplatedir();
	}

	/**
	 *	���ץꥱ������������ǥ��쥯�ȥ���������
	 *
	 *	@access	public
	 *	@return	string	����ǥ��쥯�ȥ�Υѥ�̾
	 */
	function getEtcdir()
	{
		return $this->controller->getDirectory('etc');
	}

	/**
	 *	���ץꥱ�������Υƥ�ݥ��ǥ��쥯�ȥ���������
	 *
	 *	@access	public
	 *	@return	string	�ƥ�ݥ��ǥ��쥯�ȥ�Υѥ�̾
	 */
	function getTmpdir()
	{
		return $this->controller->getDirectory('tmp');
	}

	/**
	 *	���ץꥱ�������Υƥ�ץ졼�ȥե������ĥ�Ҥ��������
	 *
	 *	@access	public
	 *	@return	string	�ƥ�ץ졼�ȥե�����γ�ĥ��
	 */
	function getTemplateext()
	{
		return $this->controller->getExt('tpl');
	}

	/**
	 *	�Хå�����ɽ�����¹Ԥ���
	 *
	 *	@access	public
	 *	@param	string	$action_name	�¹Ԥ��륢��������̾��
	 *	@return	mixed	(string):Forward̾(null�ʤ�forward���ʤ�) Ethna_Error:���顼
	 */
	function perform($action_name)
	{
		$forward_name = null;

		$action_class_name = $this->controller->getActionClassName($action_name);
		$this->action_class = new $action_class_name($this);
		$this->ac = $this->action_class;

		// ���������μ¹�
		$forward_name = $this->ac->authenticate();
		if ($forward_name === false) {
			return null;
		} else if ($forward_name !== null) {
			return $forward_name;
		}

		$forward_name = $this->ac->prepare();
		if ($forward_name === false) {
			return null;
		} else if ($forward_name !== null) {
			return $forward_name;
		}

		$forward_name = $this->ac->perform();

		return $forward_name;
	}
}

