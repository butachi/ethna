<?php
// vim: foldmethod=marker tabstop=4 shiftwidth=4 autoindent
/**
 *	Ethna_CacheManager.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *	@version    $Id$
 */

/**
 *	����å���ޥ͡����㥯�饹
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_CacheManager
{
	/**#@+	@access	private	*/

	/**	@var	string	���ߤΥ͡��ॹ�ڡ��� */
	var $namespace = '';

	/**	@var	object	Ethna_Backend		backend���֥������� */
	var $backend;

	/**	@var	object	Ethna_Config		���ꥪ�֥�������	*/
	var $config;

	/**#@-*/

	/**
	 *	Ethna_CacheMaanger���饹�Υ��󥹥��󥹤��������
	 *
	 *	@access	public
	 *	@param	string	$type	����å��奿����('localfile', 'memcache'...)
	 *	@return	object Ethna_CacheMaanger	Ethna_CacheManager���֥�������
	 */
	function &getInstance($type)
	{
		static $instance = array();

		if (array_key_exists($type, $instance)) {
			return $instance[$type];
		}

        // TODO: use plugin
		$class_name = sprintf("Ethna_CacheManager_%s", ucfirst(strtolower($type)));
		$class_file = sprintf("%s/CacheManager/%s.php", dirname(__FILE__), $class_name);
		if (file_exists($class_file) == false) {
			$retval = Ethna::raiseError("invalid cache type: no such file ($class_file)", Ethna_Const::E_CACHE_INVALID_TYPE);
			return $retval;
		}
		include_once($class_file);
		if (class_exists($class_name) == false) {
			$retval = Ethna::raiseError("invalid cache type: class is not defined ($class_name)", Ethna_Const::E_CACHE_INVALID_TYPE);
			return $retval;
		}

		$instance[$type] =& new $class_name();

		return $instance[$type];
	}
    
    /**
     *  Ethna_CacheManager���饹�Υ��󥹥ȥ饯��
     *
     *  @access public
     */
    function Ethna_CacheManager()
    {
        $this->controller =& Ethna_Controller::getInstance();
        $this->backend =& $this->controller->getBackend();
        $this->config =& $this->controller->getConfig();
    }

	/**
	 *	����å���͡��ॹ�ڡ������������
	 *
	 *	@access	public
	 *	@return	string	���ߤΥ���å���͡��ॹ�ڡ���
	 */
	function getNamespace($namespace)
	{
		return $this->namespace;
	}

	/**
	 *	����å���͡��ॹ�ڡ��������ꤹ��
	 *
	 *	@access	public
	 *	@param	string	$namespace	�͡��ॹ�ڡ���
	 */
	function setNamespace($namespace)
	{
		$this->namespace = $namespace;
	}

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
	 *	@return	mixed	����å�����
	 */
	function get($key, $lifetime = null, $namespace = null)
	{
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
	}

	/**
	 *	�ͤ�����å��夵��Ƥ��뤫�ɤ������������
	 *
	 *	@access	public
	 *	@param	string	$key		����å��奭��
	 *	@param	int		$lifetime	����å���ͭ������
	 *	@param	string	$namespace	����å���͡��ॹ�ڡ���
	 */
	function isCached($key, $timestamp = null, $namespace = null)
	{
	}

	/**
	 *	����å��夫���ͤ�������
	 *
	 *	@access	public
	 *	@param	string	$key		����å��奭��
	 *	@param	string	$namespace	����å���͡��ॹ�ڡ���
	 */
	function clear($key, $namespace = null)
	{
	}

	/**
	 *	����å���ǡ������å�����
	 *
	 *	@access	public
	 *	@param	string	$key		����å��奭��
	 *	@param	int		$timeout	��å������ॢ����
	 *	@param	string	$namespace	����å���͡��ॹ�ڡ���
	 *	@return	bool	true:���� false:����
	 */
	function lock($key, $timeout = 5, $namespace = null)
	{
		return false;
	}

	/**
	 *	����å���ǡ����Υ�å���������
	 *
	 *	@access	public
	 *	@param	string	$key		����å��奭��
	 *	@param	string	$namespace	����å���͡��ॹ�ڡ���
	 *	@return	bool	true:���� false:����
	 */
	function unlock($key, $namespace = null)
	{
		return false;
	}

	/**
	 * ���̥ե饰��Ω�Ƥ�
	 *
	 * MySQL�ʤɤ����Ĥ��λҥ��饹��ͭ��
	 * 
	 * @access public
	 * @param bool $flag �ե饰
	 */
	function setCompress($flag) {
		return false;
	}
}

