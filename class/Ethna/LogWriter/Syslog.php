<?php
// vim: foldmethod=marker
/**
 *	Ethna_LogWriter_Syslog.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

// {{{ Ethna_LogWriter_Syslog
/**
 *	�����ϥ��饹(Syslog)
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_LogWriter_Syslog extends Ethna_LogWriter
{
	/**
	 *	�����Ϥ򳫻Ϥ���
	 *
	 *	@access	public
	 */
	function begin()
	{
		// syslog�ѥ��ץ����Τߤ����
		$option = $this->option & (LOG_PID);

		openlog($this->ident, $option, $this->facility);
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
		$prefix = sprintf('%s: ', $this->_getLogLevelName($level));
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
		syslog($level, $prefix . $message);

		return $prefix . $message;
	}

	/**
	 *	�����Ϥ�λ����
	 *
	 *	@access	public
	 */
	function end()
	{
		closelog();
	}
}
// }}}
?>
