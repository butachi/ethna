<?php
// vim: foldmethod=marker
// {{{ Ethna_Action_Info
/**
 *	__ethna_info__���������μ���
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_Action_Info extends Ethna_ActionClass
{
	/**
	 *	__ethna_info__����������������
	 *
	 *	@access	public
	 *	@return	string		Forward��(���ｪλ�ʤ�null)
	 */
	function prepare()
	{
		return null;
	}

	/**
	 *	__ethna_info__���������μ���
	 *
	 *	@access	public
	 *	@return	string	����̾
	 */
	function perform()
	{
		return '__ethna_info__';
	}
}
// }}}

