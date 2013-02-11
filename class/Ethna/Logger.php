<?php
// vim: foldmethod=marker
/**
 *	Ethna_Logger.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

/**
 *	��ĥ���ץ�ѥƥ�:	�ե��������
 */
define('LOG_FILE', 1 << 16);

/**
 *	��ĥ���ץ�ѥƥ�:	ɸ�����
 */
define('LOG_ECHO', 1 << 17);

/**
 *	��ĥ���ץ�ѥƥ�:	�ؿ�̾ɽ��
 */
define('LOG_FUNCTION', 1 << 18);

/**
 *	��ĥ���ץ�ѥƥ�:	�ե�����̾+���ֹ�ɽ��
 */
define('LOG_POS', 1 << 19);


// {{{ ethna_error_handler
/**
 *	���顼������Хå��ؿ�
 *
 *	@param	int		$errno		���顼��٥�
 *	@param	string	$errstr		���顼��å�����
 *	@param	string	$errfile	���顼ȯ���ս�Υե�����̾
 *	@param	string	$errline	���顼ȯ���ս�ι��ֹ�
 */
function ethna_error_handler($errno, $errstr, $errfile, $errline)
{
	if ($errno == E_STRICT) {
		// E_STRICT��ɽ�����ʤ�
		return E_STRICT;
	}

	list($level, $name) = Ethna_Logger::errorLevelToLogLevel($errno);
	switch ($errno) {
	case E_ERROR:
	case E_CORE_ERROR:
	case E_COMPILE_ERROR:
	case E_USER_ERROR:
		$php_errno = 'Fatal error'; break;
	case E_WARNING:
	case E_CORE_WARNING:
	case E_COMPILE_WARNING:
	case E_USER_WARNING:
		$php_errno = 'Warning'; break;
	case E_PARSE:
		$php_errno = 'Parse error'; break;
	case E_NOTICE:
	case E_USER_NOTICE:
		$php_errno = 'Notice'; break;
	default:
		$php_errno = 'Unknown error'; break;
	}

	$php_errstr = sprintf('PHP %s: %s in %s on line %d', $php_errno, $errstr, $errfile, $errline);
	if (ini_get('log_errors') && (error_reporting() & $errno)) {
		$locale = setlocale(LC_TIME, 0);
		setlocale(LC_TIME, 'C');
		error_log($php_errstr, 0);
		setlocale(LC_TIME, $locale);
	}

	$c =& Ethna_Controller::getInstance();
	$logger =& $c->getLogger();
	$logger->log($level, sprintf("[PHP] %s: %s in %s on line %d", $name, $errstr, $errfile, $errline));

	$facility = $logger->getLogFacility();
	if (($facility != LOG_ECHO) && ini_get('display_errors') && (error_reporting() & $errno)) {
		if ($c->getCLI()) {
			$format = "%s: %s in %s on line %d\n";
		} else {
			$format = "<b>%s</b>: %s in <b>%s</b> on line <b>%d</b><br />\n";
		}
		printf($format, $php_errno, $errstr, $errfile, $errline);
	}
}
// }}}

// {{{ Ethna_Logger
/**
 *	���������饹
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_Logger extends Ethna_AppManager
{
	/**#@+
	 *	@access	private
	 */

	/**	@var	array	���ե�����ƥ����� */
	var $log_facility_list = array(
		'auth' => array('name' => 'LOG_AUTH'),
		'authpriv' => array('name' => 'LOG_AUTHPRIV'),
		'cron' => array('name' => 'LOG_CRON'),
		'daemon' => array('name' => 'LOG_DAEMON'),
		'kern' => array('name' => 'LOG_KERN'),
		'lpr' => array('name' => 'LOG_LPR'),
		'mail' => array('name' => 'LOG_MAIL'),
		'news' => array('name' => 'LOG_NEWS'),
		'syslog' => array('name' => 'LOG_SYSLOG'),
		'user' => array('name' => 'LOG_USER'),
		'uucp' => array('name' => 'LOG_UUCP'),
		'file' => array('name' => 'LOG_FILE'),
		'echo' => array('name' => 'LOG_ECHO'),
	);

	/**	@var	array	����٥���� */
	var $log_level_list = array(
		'emerg' => array('name' => 'LOG_EMERG'),
		'alert' => array('name' => 'LOG_ALERT'),
		'crit' => array('name' => 'LOG_CRIT'),
		'err' => array('name' => 'LOG_ERR'),
		'warning' => array('name' => 'LOG_WARNING'),
		'notice' => array('name' => 'LOG_NOTICE'),
		'info' => array('name' => 'LOG_INFO'),
		'debug' => array('name' => 'LOG_DEBUG'),
	);

	/**	@var	array	�����ץ������� */
	var $log_option_list = array(
		'pid' => array('name' => 'PIDɽ��', 'value' => LOG_PID),
		'function' => array('name' => '�ؿ�̾ɽ��', 'value' => LOG_FUNCTION),
		'pos' => array('name' => '�ե�����̾ɽ��', 'value' => LOG_POS),
	);

	/**	@var	array	����٥�ơ��֥� */
	var $level_table = array(
		LOG_EMERG	=> 7,
		LOG_ALERT	=> 6,
		LOG_CRIT	=> 5,
		LOG_ERR		=> 4,
		LOG_WARNING	=> 3,
		LOG_NOTICE	=> 2,
		LOG_INFO	=> 1,
		LOG_DEBUG	=> 0,
	);

	/**	@var	object	Ethna_Controller	controller���֥������� */
	var	$controller;

	/**	@var	int		����٥� */
	var $level;

	/**	@var	int		���ե�����ƥ� */
	var $facility;

	/**	@var	int		���顼�ȥ�٥� */
	var $alert_level;

	/**	@var	string	���顼�ȥ᡼�륢�ɥ쥹 */
	var $alert_mailaddress;

	/**	@var	string	��å������ե��륿(����) */
	var $message_filter_do;

	/**	@var	string	��å������ե��륿(̵��) */
	var $message_filter_ignore;

	/**	@var	object	Ethna_LogWriter	�����ϥ��֥������� */
	var	$writer;

	/**#@-*/
	
	/**
	 *	Ethna_Logger���饹�Υ��󥹥ȥ饯��
	 *
	 *	@access	public
	 *	@param	object	Ethna_Controller	$controller	controller���֥�������
	 */
	function Ethna_Logger(&$controller)
	{
		$this->controller =& $controller;
		$config =& $controller->getConfig();
		
		// ������μ���
		$this->level = $this->_parseLogLevel($config->get('log_level'));
		if (is_null($this->level)) {
			// ̤����ʤ�LOG_WARNING
			$this->level = LOG_WARNING;
		}
		$this->facility = $this->_parseLogFacility($config->get('log_facility'));
		$option = $this->_parseLogOption($config->get('log_option'));
		$this->alert_level = $this->_parseLogLevel($config->get('log_alert_level'));
		$this->alert_mailaddress = preg_split('/\s*,\s*/', $config->get('log_alert_mailaddress'));
		$this->message_filter_do = $config->get('log_filter_do');
		$this->message_filter_ignore = $config->get('log_filter_ignore');

		// LogWriter���饹������
		$file = $this->_getLogFile();
		$this->writer =& $this->_getLogWriter($file, $option);

		for ($i = 0; $i < 8; $i++) {
			if (defined("LOG_LOCAL$i")) {
				$this->log_facility_list["local$i"] = array('name' => "LOG_LOCAL$i");
			}
		}

		set_error_handler("ethna_error_handler");
	}

	/**
	 *	���ե�����ƥ����������
	 *
	 *	@access	public
	 *	@return	int		���ե�����ƥ�
	 */
	function getLogFacility()
	{
		return $this->facility;
	}

	/**
	 *	PHP���顼��٥�����٥���Ѵ�����
	 *
	 *	@access	public
	 *	@param	int		$errno	PHP���顼��٥�
	 *	@return	array	����٥�(LOG_NOTICE,...), ���顼��٥�ɽ��̾("E_NOTICE"...)
	 *	@static
	 */
	function errorLevelToLogLevel($errno)
	{
		switch ($errno) {
		case E_ERROR:			$code = "E_ERROR"; $level = LOG_ERR; break;
		case E_WARNING:			$code = "E_WARNING"; $level = LOG_WARNING; break;
		case E_PARSE:			$code = "E_PARSE"; $level = LOG_CRIT; break;
		case E_NOTICE:			$code = "E_NOTICE"; $level = LOG_NOTICE; break;
		case E_USER_ERROR:		$code = "E_USER_ERROR"; $level = LOG_ERR; break;
		case E_USER_WARNING:	$code = "E_USER_WARNING"; $level = LOG_WARNING; break;
		case E_USER_NOTICE:		$code = "E_USER_NOTICE"; $level = LOG_NOTICE; break;
		case E_STRICT:			$code = "E_STRING"; $level = LOG_NOTICE; return;
		default:				$code = "E_UNKNOWN"; $level = LOG_DEBUG; break;
		}
		return array($level, $code);
	}

	/**
	 *	�����Ϥ򳫻Ϥ���
	 *
	 *	@access	public
	 */
	function begin()
	{
		$this->writer->begin();
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
		// ����å������ե��륿(��٥�ե��륿��ͥ�褹��)
		$r = $this->_evalMessageMask($message);
		if ($r === false) {
			return;
		}

		// ����٥�ե��륿
		if ($r !== true && $this->_evalLevelMask($this->level, $level)) {
			return;
		}

		// ������
		$args = func_get_args();
		if (count($args) > 2) {
			array_splice($args, 0, 2);
			$message = vsprintf($message, $args);
		}
		$output = $this->writer->log($level, $message);

		// ���顼�Ƚ���
		if ($this->_evalLevelMask($this->alert_level, $level) == false) {
			if (count($this->alert_mailaddress) > 0) {
				$this->_alert($output);
			}
		}
	}

	/**
	 *	�����Ϥ�λ����
	 *
	 *	@access	public
	 */
	function end()
	{
		$this->writer->end();
	}

	/**
	 *	���ե�����ν񤭽Ф�����������(���ե�����ƥ���
	 *	LOG_FILE�����ꤵ��Ƥ�����Τ�ͭ��)
	 *
	 *	���ե�����ν񤭽Ф�����ѹ����������Ϥ��Υ᥽�åɤ�
	 *	�����С��饤�ɤ��ޤ�
	 *
	 *	@access	protected
	 *	@return	string	���ե�����ν񤭽Ф���
	 */
	function _getLogFile()
	{
		return sprintf('%s/%s.log',
			$this->controller->getDirectory('log'),
			strtolower($this->controller->getAppid())
		);
	}

	/**
	 *	LogWriter���֥������Ȥ��������
	 *
	 *	���ץꥱ��������ͭ��LogWriter�����Ѥ��������Ϥ��Υ᥽�åɤ�
	 *	�����С��饤�ɤ��ޤ�
	 *
	 *	@access	protected
	 *	@param	string	$file		���ե�����
	 *	@param	array	$option		�����ץ����
	 *	@return	object	LogWriter	LogWriter���֥�������
	 */
	function &_getLogWriter($file, $option)
	{
		if (is_null($this->facility)) {
			$writer_class = "Ethna_LogWriter";
		} else if (is_integer($this->facility)) {
			if ($this->facility == LOG_FILE) {
				$writer_class = "Ethna_LogWriter_File";
			} else if ($this->facility == LOG_ECHO) {
                if ($this->controller->getGateway() == GATEWAY_WWW ||
                    $this->controller->getGateway() == GATEWAY_CLI) {
                    $writer_class = "Ethna_LogWriter_Echo";
                } else {
                    $writer_class = "Ethna_LogWriter";
                }
			} else {
				$writer_class = "Ethna_LogWriter_Syslog";
			}
		} else if (is_string($this->facility)) {
			$writer_class = $this->facility;
			if (class_exists($writer_class) == false) {
				// falling back to default
				$writer_class = "Ethna_LogWriter";
			}
		}
		$_ret_object = new $writer_class($this->controller->getAppId(), $this->facility, $file, $option);
		return $_ret_object;
	}

	/**
	 *	�����ץ����(����ե�������)����Ϥ���
	 *
	 *	@access	private
	 *	@param	string	$string	�����ץ����(����ե�������)
	 *	@return	array	���Ϥ��줿����ե�������(���顼�����Υ᡼�륢�ɥ쥹, ���顼���оݥ���٥�, �����ץ����)
	 */
	function _parseLogOption($string)
	{
		$option = null;
		$elts = explode(',', $string);
		foreach ($elts as $elt) {
			if ($elt == 'pid') {
				$option |= LOG_PID;
			} else if ($elt == 'function') {
				$option |= LOG_FUNCTION;
			} else if ($elt == 'pos') {
				$option |= LOG_POS;
			}
		}

		return $option;
	}

	/**
	 *	���顼�ȥ᡼�����������
	 *
	 *	@access	protected
	 *	@param	string	$message	����å�����
	 *	@return	int		0:���ｪλ
	 */
	function _alert($message)
	{
		restore_error_handler();

		// �إå�
		$header = "Mime-Version: 1.0\n";
		$header .= "Content-Type: text/plain; charset=ISO-2022-JP\n";
		$header .= "X-Alert: " . $this->writer->getIdent();
		$subject = sprintf("[%s] alert (%s%s)\n", $this->writer->getIdent(), substr($message, 0, 12), strlen($message) > 12 ? "..." : "");
		
		// ��ʸ
		$mail = sprintf("--- [log message] ---\n%s\n\n", $message);
		if (function_exists("debug_backtrace")) {
			$bt = debug_backtrace();
			$mail .= sprintf("--- [backtrace] ---\n%s\n", Ethna_Util::FormatBacktrace($bt));
		}

		foreach ($this->alert_mailaddress as $mailaddress) {
			mail($mailaddress, $subject, mb_convert_encoding($mail, "ISO-2022-JP"), $header);
		}

		set_error_handler("ethna_error_handler");

		return 0;
	}

	/**
	 *	����å������Υޥ��������å���Ԥ�
	 *
	 *	@access	private
	 *	@param	string	$message	����å�����
	 *	@return	mixed	true:�������� false:����̵�� null:�����å�
	 */
	function _evalMessageMask($message)
	{
		$regexp_do = sprintf("/%s/", $this->message_filter_do);
		$regexp_ignore = sprintf("/%s/", $this->message_filter_ignore);

		if ($this->message_filter_do && preg_match($regexp_do, $message)) {
			return true;
		}
		if ($this->message_filter_ignore && preg_match($regexp_ignore, $message)) {
			return false;
		}
		return null;
	}

	/**
	 *	����٥�Υޥ��������å���Ԥ�
	 *
	 *	@access	private
	 *	@param	int		$src	����٥�ޥ���
	 *	@param	int		$dst	����٥�
	 *	@return	bool	true:���Ͱʲ� false:���Ͱʾ�
	 */
	function _evalLevelMask($src, $dst)
	{
		// �Τ�ʤ���٥�ʤ���Ϥ��ʤ�
		if (isset($this->level_table[$src]) == false || isset($this->level_table[$dst]) == false) {
			return true;
		}

		if ($this->level_table[$dst] >= $this->level_table[$src]) {
			return false;
		}

		return true;
	}

	/**
	 *	���ե�����ƥ�(����ե�������)����Ϥ���
	 *
	 *	@access	private
	 *	@param	string	$facility	���ե�����ƥ�(����ե�������)
	 *	@return	int		���ե�����ƥ�(LOG_LOCAL0, LOG_FILE...)
	 */
	function _parseLogFacility($facility)
	{
		$facility_map_table = array(
			'auth'		=> LOG_AUTH,
			'authpriv'	=> LOG_AUTHPRIV,
			'cron'		=> LOG_CRON,
			'daemon'	=> LOG_DAEMON,
			'kern'		=> LOG_KERN,
			'lpr'		=> LOG_LPR,
			'mail'		=> LOG_MAIL,
			'news'		=> LOG_NEWS,
			'syslog'	=> LOG_SYSLOG,
			'user'		=> LOG_USER,
			'uucp'		=> LOG_UUCP,
			'file'		=> LOG_FILE,
			'echo'		=> LOG_ECHO,
		);

		for ($i = 0; $i < 8; $i++) {
			if (defined("LOG_LOCAL$i")) {
				$facility_map_table["local$i"] = constant("LOG_LOCAL$i");
			}
		}

		if (isset($facility_map_table[strtolower($facility)]) == false) {
			return $facility;
		}
		return $facility_map_table[strtolower($facility)];
	}

	/**
	 *	����٥�(����ե�������)����Ϥ���
	 *
	 *	@access	private
	 *	@param	string	$level	����٥�(����ե�������)
	 *	@return	int		����٥�(LOG_DEBUG, LOG_NOTICE...)
	 */
	function _parseLogLevel($level)
	{
		$level_map_table = array(
			'emerg'		=> LOG_EMERG,
			'alert'		=> LOG_ALERT,
			'crit'		=> LOG_CRIT,
			'err'		=> LOG_ERR,
			'warning'	=> LOG_WARNING,
			'notice'	=> LOG_NOTICE,
			'info'		=> LOG_INFO,
			'debug'		=> LOG_DEBUG,
		);
		if (isset($level_map_table[strtolower($level)]) == false) {
			return null;
		}
		return $level_map_table[strtolower($level)];
	}
}
// }}}
?>
