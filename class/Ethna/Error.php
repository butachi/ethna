<?php
// vim: foldmethod=marker
/**
 *	Ethna_Error.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

// {{{ Ethna_Error
/**
 *	���顼���饹
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_Error extends PEAR_Error
{
	/**#@+
	 *	@access	private
	 */

	/**	@var	object	Ethna_I18N	i18n���֥������� */
	var $i18n;

	/**	@var	object	Ethna_Logger	logger���֥������� */
	var $logger;

	/**#@-*/

	/**
	 *	Ethna_Error���饹�Υ��󥹥ȥ饯��
	 *
	 *	@access	public
	 *	@param	int		$level				���顼��٥�
	 *	@param	string	$message			���顼��å�����
	 *	@param	int		$code				���顼������
	 *	@param	array	$userinfo			���顼�ɲþ���(���顼�����ɰʹߤ����Ƥΰ���)
	 */
	function Ethna_Error($message = null, $code = null, $mode = null, $options = null)
	{
		$controller =& Ethna_Controller::getInstance();
        if ($controller !== null) {
            $this->i18n =& $controller->getI18N();
        }

		// $options�ʹߤΰ���->$userinfo
		if (func_num_args() > 4) {
			$userinfo = array_slice(func_get_args(), 4);
			if (count($userinfo) == 1) {
				if (is_array($userinfo[0])) {
					$userinfo = $userinfo[0];
				} else if (is_null($userinfo[0])) {
					$userinfo = array();
				}
			}
		} else {
			$userinfo = array();
		}

		// ��å�������������
		if (is_null($message)) {
			// $code�����å��������������
			$message = $controller->getErrorMessage($code);
			if (is_null($message)) {
				$message = 'unkown error';
			}
		}

		parent::PEAR_Error($message, $code, $mode, $options, $userinfo);

		// Ethna�ե졼�����Υ��顼�ϥ�ɥ�(PEAR_Error�Υ�����Хå��Ȥϰۤʤ�)
		Ethna::handleError($this);
	}

	/**
	 *	level�ؤΥ�������(R)
	 *
	 *	@access	public
	 *	@return	int		���顼��٥�
	 */
	function getLevel()
	{
		return $this->level;
	}

	/**
	 *	message�ؤΥ�������(R)
	 *
	 *	PEAR_Error::getMessage()�򥪡��С��饤�ɤ��ưʲ��ν�����Ԥ�
	 *	- ���顼��å�������i18n����
	 *	- $userinfo�Ȥ����Ϥ��줿�ǡ����ˤ��vsprintf()����
	 *
	 *	@access	public
	 *	@return	string	���顼��å�����
	 */
	function getMessage()
	{
        $tmp_message = $this->i18n ? $this->i18n->get($this->message) : $this->message;
		$tmp_userinfo = to_array($this->userinfo);
		$tmp_message_arg_list = array();
		for ($i = 0; $i < count($tmp_userinfo); $i++) {
            $tmp_message_arg_list[] = $this->i18n ? $this->i18n->get($tmp_userinfo[$i]) : $tmp_userinfo[$i];
		}
		return vsprintf($tmp_message, $tmp_message_arg_list);
	}

	/**
	 *	���顼�ɲþ���ؤΥ�������(R)
	 *
	 *	PEAR_Error::getUserInfo()�򥪡��С��饤�ɤ��ơ�����θġ���
	 *	����ȥ�ؤΥ��������򥵥ݡ���
	 *
	 *	@access	public
	 *	@param	int		$n		���顼�ɲþ���Υ���ǥå���(��ά��)
	 *	@return	mixed	message����
	 */
	function getUserInfo($n = null)
	{
		if (is_null($n)) {
			return $this->userinfo;
		}

		if (isset($this->userinfo[$n])) {
			return $this->userinfo[$n];
		} else {
			return null;
		}
	}

	/**
	 *	���顼�ɲþ���ؤΥ�������(W)
	 *
	 *	PEAR_Error::addUserInfo()�򥪡��С��饤��
	 *
	 *	@access	public
	 *	@param	string	$info	�ɲä��륨�顼����
	 */
	function addUserInfo($info)
	{
		$this->userinfo[] = $info;
	}
}
// }}}

