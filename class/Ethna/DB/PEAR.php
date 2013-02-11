<?php
// vim: foldmethod=marker
/**
 *	Ethna_DB_PEAR.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */
include_once('DB.php');

// {{{ Ethna_DB_PEAR
/**
 *	Ethna_DB���饹�μ���(PEAR��)
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_DB_PEAR extends Ethna_DB
{
	/**#@+
	 *	@access	private
	 */

	/**	@var	object	DB				PEAR DB���֥������� */
	var $db;

	/**	@var	array	�ȥ�󥶥��������������å� */
	var	$transaction = array();


	/**	@var	object	Ethna_Logger	�����֥������� */
	var $logger;

	/**	@var	object	Ethna_AppSQL	SQL���֥������� */
	var $sql;

	/**	@var	string	DB������(mysql, pgsql...) */
	var $type;

	/**	@var	string	DSN */
	var $dsn;

	/**	@var	bool	��³��³�ե饰 */
	var $persistent;

	/**#@-*/


	// {{{ Ethna_DB���饹�μ���
	/**
	 *	Ethna_DB_PEAR���饹�Υ��󥹥ȥ饯��
	 *
	 *	@access	public
	 *	@param	object	Ethna_Controller	&$controller	����ȥ��饪�֥�������
	 *	@param	string	$dsn								DSN
	 *	@param	bool	$persistent							��³��³����
	 */
	function Ethna_DB_PEAR(&$controller, $dsn, $persistent)
	{
        parent::Ethna_DB($controller, $dsn, $persistent);

		$this->db = null;
		$this->logger =& $controller->getLogger();
		$this->sql =& $controller->getSQL();

		$dsninfo = DB::parseDSN($dsn);
		$this->type = $dsninfo['phptype'];
	}

	/**
	 *	DB����³����
	 *
	 *	@access	public
	 *	@return	mixed	0:���ｪλ Ethna_Error:���顼
	 */
	function connect()
	{
		$this->db =& DB::connect($this->dsn, $this->persistent);
		if (DB::isError($this->db)) {
			$error = Ethna::raiseError('DB��³���顼: %s', Ethna_Const::E_DB_CONNECT, $this->db->getUserInfo());
			$error->addUserInfo($this->db);
			$this->db = null;
			return $error;
		}

		return 0;
	}

	/**
	 *	DB��³�����Ǥ���
	 *
	 *	@access	public
	 */
	function disconnect()
	{
		if (is_null($this->db)) {
			return;
		}
		$this->db->disconnect();
	}

	/**
	 *	DB��³���֤��֤�
	 *
	 *	@access	public
	 *	@return	bool	true:���� false:���顼
	 */
	function isValid()
	{
		if (is_null($this->db)) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 *	DB�ȥ�󥶥������򳫻Ϥ���
	 *
	 *	@access	public
	 *	@return	mixed	0:���ｪλ Ethna_Error:���顼
	 */
	function begin()
	{
		if (count($this->transaction) > 0) {
			$this->transaction[] = true;
			return 0;
		}

		$r = $this->query('BEGIN;');
		if (Ethna::isError($r)) {
			return $r;
		}
		$this->transaction[] = true;

		return 0;
	}

	/**
	 *	DB�ȥ�󥶥����������Ǥ���
	 *
	 *	@access	public
	 *	@return	mixed	0:���ｪλ Ethna_Error:���顼
	 */
	function rollback()
	{
		if (count($this->transaction) == 0) {
			return 0;
		}

		// ����Хå����ϥ����å����˴ؤ�餺�ȥ�󥶥������򥯥ꥢ����
		$r = $this->query('ROLLBACK;');
		if (Ethna::isError($r)) {
			return $r;
		}
		$this->transaction = array();

		return 0;
	}

	/**
	 *	DB�ȥ�󥶥�������λ����
	 *
	 *	@access	public
	 *	@return	mixed	0:���ｪλ Ethna_Error:���顼
	 */
	function commit()
	{
		if (count($this->transaction) == 0) {
			return 0;
		} else if (count($this->transaction) > 1) {
			array_pop($this->transaction);
			return 0;
		}

		$r = $this->query('COMMIT;');
		if (Ethna::isError($r)) {
			return $r;
		}
		array_pop($this->transaction);

		return 0;
	}

    /**
     *  �ơ��֥����������������
     *
     *  @access public
     *  @param  string  $table  �ơ��֥�̾
     *  @return mixed   array: PEAR::DB�˽स���᥿�ǡ��� Ethna_Error::���顼
     */
    function getMetaData($table)
    {
        return $this->db->tableInfo($table);
    }
	// }}}

	// {{{ Ethna_AppObjectϢ�ȤΤ���μ���
	/**
	 *	DB�����פ��֤�
	 *
	 *	@access	public
	 *	@return	string	DB������
	 */
	function getType()
	{
		return $this->type;
	}

	/**
	 *	�������ȯ�Ԥ���
	 *
	 *	@access	public
	 *	@param	string	$query	SQLʸ
	 *	@return	mixed	DB_Result:��̥��֥������� Ethna_Error:���顼
	 */
	function &query($query)
	{
		return $this->_query($query);
	}

	/**
	 *	ľ���INSERT�ˤ��ID���������
	 *
	 *	��³���DB��mysql�ʤ�mysql_insert_id()���ͤ��֤�
	 *
	 *	@access	public
	 *	@return	mixed	int:ľ���INSERT�ˤ���������줿ID null:̤���ݡ���
	 */
	function getInsertId()
	{
		if ($this->isValid() == false) {
			return null;
		} else if ($this->type == 'mysql') {
			return mysql_insert_id($this->db->connection);
		}

		return null;
	}

	/**
	 *	ľ��Υ�����ˤ�빹���Կ����������
	 *
	 *	@access	public
	 *	@return	int		�����Կ�
	 */
	function affectedRows()
	{
		return $this->db->affectedRows();
	}
	// }}}

	// {{{ Ethna_DB_PEAR�ȼ��μ���
	/**
	 *	SQLʸ���ꥯ�����ȯ�Ԥ���
	 *
	 *	@access	public
	 *	@param	string	$sqlid		SQL-ID(+����)
	 *	@return	mixed	DB_Result:��̥��֥������� Ethna_Error:���顼
	 */
	function &sqlquery($sqlid)
	{
		$args = func_get_args();
		array_shift($args);
		$query = $this->sql->get($sqlid, $args);

		return $this->_query($query);
	}

	/**
	 *	SQLʸ���������
	 *	
	 *	@access	public
	 *	@param	string	$sqlid		SQL-ID
	 *	@return	string	SQLʸ
	 */
	function sql($sqlid)
	{
		$args = func_get_args();
		array_shift($args);
		$query = $this->sql->get($sqlid, $args);

		return $query;
	}

	/**
	 *	�ơ��֥���å�����
	 *
	 *	@access	public
	 *	@param	mixed	��å��оݥơ��֥�̾
	 *	@return	mixed	DB_Result:��̥��֥������� Ethna_Error:���顼
	 */
	function lock($tables)
	{
		$this->message = null;

		$sql = "";
		foreach (to_array($tables) as $table) {
			if ($sql != "") {
				$sql .= ", ";
			}
			$sql .= "$table WRITE";
		}

		return $this->query("LOCK TABLES $sql");
	}

	/**
	 *	�ơ��֥�Υ�å����������
	 *
	 *	@access	public
	 *	@return	mixed	DB_Result:��̥��֥������� Ethna_Error:���顼
	 */
	function unlock()
	{
		$this->message = null;
		return $this->query("UNLOCK TABLES");
	}

	/**
	 *	�������ȯ�Ԥ���
	 *
	 *	@access	private
	 *	@param	string	$query	SQLʸ
	 *	@return	mixed	DB_Result:��̥��֥������� Ethna_Error:���顼
	 */
	function &_query($query)
	{
		$this->logger->log(LOG_DEBUG, "$query");
		$r =& $this->db->query($query);
		if (DB::isError($r)) {
			if ($r->getCode() == DB_ERROR_ALREADY_EXISTS) {
				$error = Ethna::raiseNotice('��ˡ������󥨥顼 SQL[%s]', Ethna_Const::E_DB_DUPENT, $query, $this->db->errorNative(), $r->getUserInfo());
			} else {
				$error = Ethna::raiseError('�����ꥨ�顼 SQL[%s] CODE[%d] MESSAGE[%s]', Ethna_Const::E_DB_QUERY, $query, $this->db->errorNative(), $r->getUserInfo());
			}
			return $error;
		}
		return $r;
	}
	// }}}
}
// }}}
?>
