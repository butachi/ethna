<?php
/**
 *	mail.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

/**
 *	�᡼��ƥ�ץ졼�ȥ�����: ľ������
 */
define('MAILSENDER_TYPE_DIRECT', 0);

/**
 *	�᡼���������饹
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_MailSender
{
	/**#@+
	 *	@access	private
	 */

	/**
	 *	@var	array	�᡼��ƥ�ץ졼�����
	 */
	var	$def = array(
	);

	/**
	 *	@var	int		�����᡼�륿����
	 */
	var	$type;

	/**
	 *	@var	object	Ethna_Backend	backend���֥�������
	 */
	var $backend;

	/**
	 *	@var	object	Ethna_Config	���ꥪ�֥�������
	 */
	var $config;

	/**#@-*/

	/**
	 *	Ethna_MailSender���饹�Υ��󥹥ȥ饯��
	 *
	 *	@access	public
	 *	@param	object	Ethna_Backend	&$backend		backend���֥�������
	 */
	function Ethna_MailSender(&$backend)
	{
		$this->backend =& $backend;
		$this->config =& $this->backend->getConfig();
	}

	/**
	 *	�᡼�����������
	 *
	 *	@access	public
	 *	@param	string	$to		�᡼�������襢�ɥ쥹
	 *	@param	string	$type	�᡼��ƥ�ץ졼�ȥ�����
	 *	@param	array	$macro	�ƥ�ץ졼�ȥޥ���($type��MAILSENDER_TYPE_DIRECT�ξ��ϥ᡼����������)
	 *	@param	array	$attach	ź�եե�����(array('content-type' => ..., 'content' => ...))
	 *	@return	string	$to��null�ξ��ƥ�ץ졼�ȥޥ���Ŭ�Ѹ�Υ᡼������
	 */
	function send($to, $type, $macro, $attach = null)
	{
		// ����ƥ�ĺ���
		$c =& $this->backend->getController();

		if ($type != MAILSENDER_TYPE_DIRECT) {
			$smarty =& $c->getTemplateEngine();

			// ���ܾ�������
			$smarty->assign("env_datetime", strftime('%Yǯ%m��%d�� %H��%Mʬ%S��'));
			$smarty->assign("env_useragent", $_SERVER["HTTP_USER_AGENT"]);
			$smarty->assign("env_remoteaddr", $_SERVER["REMOTE_ADDR"]);

			// �ǥե���ȥޥ�������
			$macro = $this->_setDefaultMacro($macro);

			// �桼�������������
			if (is_array($macro)) {
				foreach ($macro as $key => $value) {
					$smarty->assign($key, $value);
				}
			}

			$template = $this->def[$type];
			ob_start();
			$smarty->display("mail/$template");
			$mail = ob_get_contents();
			ob_end_clean();
		} else {
			$mail = $macro;
		}

		if (is_null($to)) {
			return $mail;
		}

		// ����
		foreach (to_array($to) as $rcpt) {
			list($header, $body) = $this->_parse($mail);

			// multipart�б�
			if ($attach != null) {
				$boundary = Etuna_Util::getRandom(); 

				$body = "This is a multi-part message in MIME format.\n\n" .
					"--$boundary\n" .
					"Content-Type: text/plain; charset=ISO-2022-JP\n\n" .
					"$body\n" .
					"--$boundary\n" .
					"Content-Type: " . $attach['content-type'] . "; name=\"" . $attach['name'] . "\"\n" .
					"Content-Transfer-Encoding: base64\n" . 
					"Content-Disposition: attachment; filename=\"" . $attach['name'] . "\"\n\n";
				$body .= chunk_split(base64_encode($attach['content']));
				$body .= "--$boundary--";
			}

			$body = str_replace("\r\n", "\n", $body);

			// �����ɬ�פʥإå����ɲ�
			if (array_key_exists('mime-version', $header) == false) {
				$header['mime-version'] = array('Mime-Version', '1.0');
			}
			if (array_key_exists('subject', $header) == false) {
				$header['subject'] = array('Subject', 'no subject in original');
			}
			if (array_key_exists('content-type', $header) == false) {
				if ($attach == null) {
					$header['content-type'] = array('Content-Type', 'text/plain; charset=ISO-2022-JP');
				} else {
					$header['content-type'] = array('Content-Type', "multipart/mixed; boundary=\"$boundary\"");
				}
			}

			$header_line = "";
			foreach ($header as $key => $value) {
				if ($key == 'subject') {
					// should be added by mail()
					continue;
				}
				if ($header_line != "") {
					$header_line = "$header_line\n";
				}
				$header_line .= $value[0] . ": " . $value[1];
			}

			mail($rcpt, $header['subject'][1], $body, $header_line);
		}
	}

	/**
	 *	���ץꥱ��������ͭ�Υޥ��������ꤹ��
	 *
	 *	@access	protected
	 *	@param	array	$macro	�桼������ޥ���
	 *	@return	array	���ץꥱ��������ͭ�����Ѥߥޥ���
	 */
	function _setDefaultMacro($macro)
	{
		return $macro;
	}

	/**
	 *	�ƥ�ץ졼�ȥ᡼��Υإå�������������
	 *
	 *	@access	private
	 *	@param	string	$mail	�᡼��ƥ�ץ졼��
	 *	@return	array	�إå�, ��ʸ
	 */
	function _parse($mail)
	{
		list($header_line, $body) = preg_split('/\r?\n\r?\n/', $mail, 2);
		$header_line .= "\n";

		$header_lines = explode("\n", $header_line);
		$header = array();
		foreach ($header_lines as $h) {
			if (strstr($h, ':') == false) {
				continue;
			}
			list($key, $value) = preg_split('/\s*:\s*/', $h, 2);
			$i = strtolower($key);
			$header[$i] = array();
			$header[$i][] = $key;
			$header[$i][] = preg_replace('/([^\x00-\x7f]+)/e', "mb_encode_mimeheader('$1')", $value);
		}

		$body = mb_convert_encoding($body, "ISO-2022-JP", "EUC-JP");

		return array($header, $body);
	}
}
?>