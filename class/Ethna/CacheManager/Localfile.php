<?php
// vim: foldmethod=marker tabstop=4 shiftwidth=4 autoindent
/**
 *	Ethna_CacheManager_Localfile.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *	@version    $Id$
 */

/**
 *	����å���ޥ͡����㥯�饹(������ե����륭��å�����)
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_CacheManager_Localfile extends Ethna_CacheManager
{
	/**#@+	@access	private	*/

	/**#@-*/

	/**
	 *	����å�������ꤵ�줿�ͤ��������
	 *
	 *	����å�����ͤ����ꤵ��Ƥ�����ϥ���å�����
	 *	������ͤȤʤ롣����å�����ͤ�̵������lifetime
	 *	��᤮�Ƥ����硢���顼��ȯ����������PEAR_Error
	 *	���֥������Ȥ�����ͤȤʤ롣
	 *
	 *	@access	public
	 *	@param	string	$key		����å��奭��
	 *	@param	int		$lifetime	����å���ͭ������
	 *	@param	string	$namespace	����å���͡��ॹ�ڡ���
	 *	@return	array	����å�����
	 */
	function get($key, $lifetime = null, $namespace = null)
	{
		$namespace = is_null($namespace) ? $this->namespace : $namespace;
		$cache_file = $this->_getCacheFile($namespace, $key);

		// �饤�ե���������å�
		clearstatcache();
		$st = @stat($cache_file);
		if ($st == false) {
			return PEAR::raiseError('fopen failed', Ethna_Const::E_CACHE_NO_VALUE);
		}
		if (is_null($lifetime) == false) {
			if (($st[9]+$lifetime) < time()) {
				return PEAR::raiseError('fopen failed', Ethna_Const::E_CACHE_EXPIRED);
			}
		}

		$fp = fopen($cache_file, "r");
		if ($fp == false) {
			return PEAR::raiseError('fopen failed', Ethna_Const::E_CACHE_NO_VALUE);
		}
		// ��å�
		$timeout = 3;
		while ($timeout > 0) {
			$r = flock($fp, LOCK_EX|LOCK_NB);
			if ($r) {
				break;
			}
			$timeout--;
			sleep(1);
		}
		if ($timeout <= 0) {
			fclose($fp);
			return PEAR::raiseError('fopen failed', E_CACHEthna_Const::E_GENERAL);
		}

		$n = 0;
		while ($st[7] == 0) {
			clearstatcache();
			$st = @stat($cache_file);
			usleep(1000*1);
			$n++;
			if ($n > 5) {
				break;
			}
		}

		if ($st == false || $n > 5) {
			fclose($fp);
			return PEAR::raiseError('stat failed', Ethna_Const::E_CACHE_NO_VALUE);
		}
		$value = fread($fp, $st[7]);
		fclose($fp);

		return unserialize($value);
	}

	/**
	 *	����å���κǽ������������������
	 *
	 *	@access	public
	 *	@param	string	$key		����å��奭��
	 *	@param	string	$namespace	����å���͡��ॹ�ڡ���
	 *	@return	int		�ǽ���������(unixtime)
	 */
	function getLastModified($key, $namespace = null)
	{
		$namespace = is_null($namespace) ? $this->namespace : $namespace;
		$cache_file = $this->_getCacheFile($namespace, $key);

		clearstatcache();
		$st = @stat($cache_file);
		if ($st == false) {
			return PEAR::raiseError('fopen failed', Ethna_Const::E_CACHE_NO_VALUE);
		}
		return $st[9];
	}

	/**
	 *	�ͤ�����å��夵��Ƥ��뤫�ɤ������������
	 *
	 *	@access	public
	 *	@param	string	$key		����å��奭��
	 *	@param	int		$lifetime	����å���ͭ������
	 *	@param	string	$namespace	����å���͡��ॹ�ڡ���
	 */
	function isCached($key, $lifetime = null, $namespace = null)
	{
		$namespace = is_null($namespace) ? $this->namespace : $namespace;
		$cache_file = $this->_getCacheFile($namespace, $key);

		// �饤�ե���������å�
		clearstatcache();
		$st = @stat($cache_file);
		if ($st == false) {
			return false;
		}
		if (is_null($lifetime) == false) {
			if (($st[9]+$lifetime) < time()) {
				return false;
			}
		}

		return true;
	}

	/**
	 *	����å�����ͤ����ꤹ��
	 *
	 *	@access	public
	 *	@param	string	$key		����å��奭��
	 *	@param	mixed	$value		����å�����
	 *	@param	int		$timestamp	����å���ǽ���������(unixtime)
	 *	@param	string	$namespace	����å���͡��ॹ�ڡ���
	 */
	function set($key, $value, $timestamp = null, $namespace = null)
	{
		$namespace = is_null($namespace) ? $this->namespace : $namespace;
		$dir = $this->_getCacheDir($namespace, $key);

		// ����å���ǥ��쥯�ȥ�����å�
		$dir_list = array();
		$tmp = $dir;
		while (is_dir($tmp) == false) {
			array_unshift($dir_list, $tmp);
			$tmp = dirname($tmp);
		}
		foreach ($dir_list as $tmp) {
			$r = @mkdir($tmp);
			if ($r == false && is_dir($tmp) == false) {
				$message = sprintf('mkdir(%s) failed', $tmp);
				trigger_error($message, E_USER_WARNING);
			}
			$this->_chmod($tmp, 0777);
		}

		$cache_file = $this->_getCacheFile($namespace, $key);
		$fp = fopen($cache_file, "a+");
		if ($fp == false) {
			return PEAR::raiseError('fopen failed', E_CACHEthna_Const::E_GENERAL);
		}

		// ��å�
		$timeout = 3;
		while ($timeout > 0) {
			$r = flock($fp, LOCK_EX|LOCK_NB);
			if ($r) {
				break;
			}
			$timeout--;
			sleep(1);
		}
		if ($timeout <= 0) {
			fclose($fp);
			return PEAR::raiseError('fopen failed', E_CACHEthna_Const::E_GENERAL);
		}
		rewind($fp);
		ftruncate($fp, 0);
		fwrite($fp, serialize($value));
		fclose($fp);
		$this->_chmod($cache_file, 0666);

		if (is_null($timestamp)) {
			// this could suppress warning
			touch($cache_file);
		} else {
			touch($cache_file, $timestamp);
		}

		return 0;
	}

	/**
	 *	����å����ͤ�������
	 *
	 *	@access	public
	 *	@param	string	$key		����å��奭��
	 *	@param	string	$namespace	����å���͡��ॹ�ڡ���
	 */
	function clear($key, $namespace = null)
	{
		$namespace = is_null($namespace) ? $this->namespace : $namespace;
		$cache_file = $this->_getCacheFile($namespace, $key);

		if (file_exists($cache_file)) {
			unlink($cache_file);
		}
	}

	/**
	 *	����å����оݥǥ��쥯�ȥ���������
	 *
	 *	@access	private
	 */
	function _getCacheDir($namespace, $key)
	{
		$len = strlen($key);
		// intentionally avoid using -2 or -4
		$dir1 = substr($key, $len-4, 2);
		if ($len-4 < 0 || strlen($dir1) < 2) {
			$dir1 = "__dir1";
		}
		$dir2 = substr($key, $len-2, 2);
		if ($len-2 < 0 || strlen($dir2) < 2) {
			$dir2 = "__dir2";
		}

        $map = $this->config->get('cachemanager_localfile');
		$tmp_key = $namespace . "::" . $key;
		// PHP��¸:)
		$dir = "default";

        if (is_array($map)) {
            foreach ($map as $key => $value) {
                if (strncmp($key, $tmp_key, strlen($key)) == 0) {
                    $dir = $value;
                    break;
                }
            }
        }
		
        return sprintf("%s/cache/%s/cache_%s/%s/%s", $this->backend->getTmpdir(), $dir, $this->_escape($namespace), $this->_escape($dir1), $this->_escape($dir2));
	}

	/**
	 *	����å���ե�������������
	 *
	 *	@access	private
	 */
	function _getCacheFile($namespace, $key)
	{
		return sprintf("%s/%s", $this->_getCacheDir($namespace, $key), $this->_escape($key));
	}

	/**
	 *	������ե����륷���ƥ��Ѥ˥��������פ���
	 *
	 *	@access	private
	 */
	function _escape($string)
	{
		return preg_replace('/([^0-9A-Za-z_])/e', "sprintf('%%%02X', ord('\$1'))", $string);
	}

	/**
	 *	�ե�����Υѡ��ߥå������ѹ�����
	 *
	 *	@access	private
	 */
	function _chmod($file, $mode)
	{
		$st = stat($file);
		if (($st[2] & 0777) == $mode) {
			return true;
		}
		return chmod($file, $mode);
	}
}

