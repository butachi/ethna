<?php
/**
 *    {$project_id}_Controller.php
 *
 *    @author        {$author}
 *    @package    {$project_id}
 *    @version    $Id$
 */

/** ���ץꥱ�������١����ǥ��쥯�ȥ� */
define('BASE', dirname(dirname(__FILE__)));

// include_path������(���ץꥱ�������ǥ��쥯�ȥ���ɲ�)
$app = BASE . "/app";
$lib = BASE . "/lib";
ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . implode(PATH_SEPARATOR, array($app, $lib)));


/** ���ץꥱ�������饤�֥��Υ��󥯥롼�� */
include_once('Ethna/Ethna.php');
include_once('{$project_id}_Error.php');

/**
 *    {$project_id}���ץꥱ�������Υ���ȥ������
 *
 *    @author        {$author}
 *    @access        public
 *    @package    {$project_id}
 */
class {$project_id}_Controller extends Ethna_Controller
{
    /**#@+
     *    @access    private
     */

    /**
     *    @var    string    ���ץꥱ�������ID
     */
    protected    $appid = '{$application_id}';

    /**
     *    @var    array    forward���
     */
    protected $forward = array(
        /*
         *    TODO: ������forward��򵭽Ҥ��Ƥ�������
         *
         *    �����㡧
         *
         *    'index'            => array(
         *        'view_name'    => '{$project_id}_View_Index',
         *    ),
         */
    );

    /**
     *    @var    array    action���
     */
    protected $action = array(
        /*
         *    TODO: ������action����򵭽Ҥ��Ƥ�������
         *
         *    �����㡧
         *
         *    'index'        => array(),
         */
    );

    /**
     *    @var    array    soap action���
     */
    protected $soap_action = array(
        /*
         *    TODO: ������SOAP���ץꥱ��������Ѥ�action�����
         *    ���Ҥ��Ƥ�������
         *    �����㡧
         *
         *    'sample'            => array(),
         */
    );

    /**
     *    @var    array        ���ץꥱ�������ǥ��쥯�ȥ�
     */
    protected $directory = array(
        'action'        => 'app/action',
        'action_cli'    => 'app/action_cli',
        'action_xmlrpc' => 'app/action_xmlrpc',
        'app'           => 'app',
        'bin'           => 'bin',
        'etc'            => 'etc',
        'filter'        => 'app/filter',
        'locale'        => 'locale',
        'log'            => 'log',
        'plugins'        => array(),
        'template'        => 'template',
        'template_c'    => 'tmp',
        'tmp'            => 'tmp',
        'view'            => 'app/view',
    );

    /**
     *    @var    array        DB�����������
     */
    protected    $db = array(
        ''                => DB_TYPE_RW,
    );

    /**
     *    @var    array        ��ĥ������
     */
    protected $ext = array(
        'php'            => 'php',
        'tpl'            => 'tpl',
    );

    /**
     *    @var    array    ���饹���
     */
    protected $class = array(
        /*
         *    TODO: ���ꥯ�饹�������饹��SQL���饹�򥪡��С��饤��
         *    �������ϲ����Υ��饹̾��˺�줺���ѹ����Ƥ�������
         */
        'class'            => 'Ethna_ClassFactory',
        'backend'        => 'Ethna_Backend',
        'config'        => 'Ethna_Config',
        'db'            => 'Ethna_DB_PEAR',
        'error'            => 'Ethna_ActionError',
        'form'            => 'Ethna_ActionForm',
        'i18n'            => 'Ethna_I18N',
        'logger'        => 'Ethna_Logger',
        'session'        => 'Ethna_Session',
        'sql'            => 'Ethna_AppSQL',
        'view'            => 'Ethna_ViewClass',
    );

    /**
     *    @var    array        �ե��륿����
     */
    protected $filter = array(
        /*
         *    TODO: �ե��륿�����Ѥ�����Ϥ����ˤ��Υ��饹̾��
         *    ���Ҥ��Ƥ�������
         *
         *    �����㡧
         *
         *    '{$project_id}_Filter_ExecutionTime',
         */
    );

    /**
     *    @var    array    �ޥ͡��������
     */
    protected $manager = array(
        /*
         *    TODO: �����˥��ץꥱ�������Υޥ͡����㥪�֥������Ȱ�����
         *    ���Ҥ��Ƥ�������
         *
         *    �����㡧
         *
         *    'um'    => 'User',
         */
    );

    /**
     *    @var    array    smarty modifier���
     */
    protected $smarty_modifier_plugin = array(
        /*
         *    TODO: �����˥桼�������smarty modifier�����򵭽Ҥ��Ƥ�������
         *
         *    �����㡧
         *
         *    'smarty_modifier_foo_bar',
         */
    );

    /**
     *    @var    array    smarty function���
     */
    protected $smarty_function_plugin = array(
        /*
         *    TODO: �����˥桼�������smarty function�����򵭽Ҥ��Ƥ�������
         *
         *    �����㡧
         *
         *    'smarty_function_foo_bar',
         */
    );

    /**
     *    @var    array    smarty prefilter���
     */
    protected $smarty_prefilter_plugin = array(
        /*
         *    TODO: �����˥桼�������smarty prefilter�����򵭽Ҥ��Ƥ�������
         *
         *    �����㡧
         *
         *    'smarty_prefilter_foo_bar',
         */
    );

    /**
     *    @var    array    smarty postfilter���
     */
    protected $smarty_postfilter_plugin = array(
        /*
         *    TODO: �����˥桼�������smarty postfilter�����򵭽Ҥ��Ƥ�������
         *
         *    �����㡧
         *
         *    'smarty_postfilter_foo_bar',
         */
    );

    /**
     *    @var    array    smarty outputfilter���
     */
    protected $smarty_outputfilter_plugin = array(
        /*
         *    TODO: �����˥桼�������smarty outputfilter�����򵭽Ҥ��Ƥ�������
         *
         *    �����㡧
         *
         *    'smarty_outputfilter_foo_bar',
         */
    );

    /**#@-*/

    /**
     *    ���ܻ��Υǥե���ȥޥ�������ꤹ��
     *
     *    @access    protected
     *    @param    object    Smarty    $smarty    �ƥ�ץ졼�ȥ��󥸥󥪥֥�������
     */
    function _setDefaultTemplateEngine(&$smarty)
    {
        /*
         *    TODO: �����ǥƥ�ץ졼�ȥ��󥸥�ν�������
         *  ���ƤΥӥ塼�˶��̤ʥƥ�ץ졼���ѿ������ꤷ�ޤ�
         *
         *    �����㡧
         * $smarty->assign_by_ref('session_name', session_name());
         * $smarty->assign_by_ref('session_id', session_id());
         *
         * // ������ե饰(true/false)
         * $session = $this->getClassFactory('session');
         * if ($session && $this->session->isStart()) {
         *     $smarty->assign_by_ref('login', $session->isStart());
         * }
         */
    }
}

