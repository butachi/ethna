<?php
/**
 *    Ethna_ActionClass.php
 *
 *    @author        Masaki Fujimoto <fujimoto@php.net>
 *    @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *    @package    Ethna
 *    @version    $Id$
 */
/**
 *    action�¹ԥ��饹
 *
 *    @author        Masaki Fujimoto <fujimoto@php.net>
 *    @access        public
 *    @package    Ethna
 */
class Ethna_ActionClass
{
    /**#@+
     *    @access    private
     */

    /**    @var    object    Ethna_Backend        backend���֥������� */
    var $backend;

    /**    @var    object    Ethna_Config        ���ꥪ�֥�������    */
    var $config;

    /**    @var    object    Ethna_ActionError    ��������󥨥顼���֥������� */
    var $action_error;

    /**    @var    object    Ethna_ActionError    ��������󥨥顼���֥�������(��ά��) */
    var $ae;

    /**    @var    object    Ethna_ActionForm    ���������ե����४�֥������� */
    var $action_form;

    /**    @var    object    Ethna_ActionForm    ���������ե����४�֥�������(��ά��) */
    var $af;

    /**    @var    object    Ethna_Session        ���å���󥪥֥������� */
    var $session;

    /**#@-*/

    /**
     *    Ethna_ActionClass�Υ��󥹥ȥ饯��
     *
     *    @access    public
     *    @param    object    Ethna_Backend    $backend    backend���֥�������
     */
    function Ethna_ActionClass(&$backend)
    {
        $c = $backend->getController();
        $this->backend = $backend;
        $this->config = $this->backend->getConfig();

        $this->action_error = $this->backend->getActionError();
        $this->ae = $this->action_error;

        $this->action_form = $this->backend->getActionForm();
        $this->af = $this->action_form;

        $this->session = $this->backend->getSession();

        // Ethna_AppManager���֥������Ȥ�����
        $manager_list = $c->getManagerList();
        foreach ($manager_list as $k => $v) {
            $this->$k = $backend->getManager($v);
        }
    }

    /**
     *    ���������¹�����ǧ�ڽ�����Ԥ�
     *
     *    @access    public
     *    @return    string    ����̾(null�ʤ����ｪλ, false�ʤ������λ)
     */
    function authenticate()
    {
        return null;
    }

    /**
     *    ���������¹����ν���(�ե������ͥ����å���)��Ԥ�
     *
     *    @access    public
     *    @return    string    ����̾(null�ʤ����ｪλ, false�ʤ������λ)
     */
    function prepare()
    {
        return null;
    }

    /**
     *    ���������¹�
     *
     *    @access    public
     *    @return    string    ����̾(null�ʤ����ܤϹԤ�ʤ�)
     */
    function perform()
    {
        return null;
    }
}

