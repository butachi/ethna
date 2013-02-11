<?php
// vim: foldmethod=marker
/**
 *	Ethna_LogWriter_File.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

// {{{ Ethna_LogWriter_File
/**
 *	�����ϥ��饹(File)
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_LogWriter_File extends Ethna_LogWriter
{
	/**#@+
	 *	@access	private
	 */

	/**	@var	int		���ե�����ϥ�ɥ� */
	var	$fp;

	/**#@-*/

	/**
	 *	Ethna_LogWriter_File���饹�Υ��󥹥ȥ饯��
	 *
	 *	@access	public
	 *	@param	string	$log_ident		�������ǥ�ƥ��ƥ�ʸ����(�ץ���̾��)
	 *	@param	int		$log_facility	���ե�����ƥ�
	 *	@param	string	$log_file		��������ե�����̾(LOG_FILE���ץ���󤬻��ꤵ��Ƥ�����Τ�)
	 *	@param	int		$log_option		�����ץ����(LOG_FILE,LOG_FUNCTION...)
	 */
	function Ethna_LogWriter_File($log_ident, $log_facility, $log_file, $log_option)
	{
		parent::Ethna_LogWriter($log_ident, $log_facility, $log_file, $log_option);
		$this->fp = null;
	}

	/**
	 *	�����Ϥ򳫻Ϥ���
	 *
	 *	@access	public
	 */
	function begin()
	{
		$this->fp = fopen($this->file, 'a');
	}

	/**
	 *	������Ϥ���
	 *
	 *	@access	public
	 *	@param	int		$level		����٥�(LOG_DEBUG, LOG_NOTICE...)
	 *	@param	string	$message	����å�����(+����)
	 */
	function log($level, $message)
	{
		if ($this->fp == null) {
			return;
		}

		$prefix = strftime('%Y/%m/%d %H:%M:%S ') . $this->ident;
		if ($this->option & LOG_PID) {
			$prefix .= sprintf('[%d]', getmypid());
		}
		$prefix .= sprintf('(%s): ', $this->_getLogLevelName($level));
		if ($this->option & (LOG_FUNCTION | LOG_POS)) {
			$tmp = "";
			$bt = $this->_getBacktrace();
			if ($bt && ($this->option & LOG_FUNCTION) && $bt['function']) {
				$tmp .= $bt['function'];
			}
			if ($bt && ($this->option & LOG_POS) && $bt['pos']) {
				$tmp .= $tmp ? sprintf('(%s)', $bt['pos']) : $bt['pos'];
			}
			if ($tmp) {
				$prefix .= $tmp . ": ";
			}
		}
		fwrite($this->fp, $prefix . $message . "\n");

		return $prefix . $message;
	}

	/**
	 *	�����Ϥ�λ����
	 *
	 *	@access	public
	 */
	function end()
	{
		if ($this->fp) {
			fclose($this->fp);
			$this->fp = null;
		}
	}
}
// }}}
?>
