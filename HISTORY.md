* �ѹ�������

** 2.1.2-autoload

- Supports PHP5.3=<
- Supported autoload and composer.
- Changed the behavior of ActionForm to use $_REQUEST instead of $_POST or $_GET.
- removed ?> in the end of PHP file.


** 2.1.1

*** bug fixes

- ethna.bat�Υѥ�����

** 2.1.0

*** features

- ethna���ޥ�ɤ�ETHNA_HOME�򥤥󥹥ȡ�����˷��ꤹ��褦�˲���
- Ethna_ActionForm::validate() ��¿���������Ϥ��줿�Ȥ���notice�����
- Ethna_Backend::setActionForm(), Ethna_Backend::setActionClass()�᥽�åɤ��ɲ�
- Ethna_Filter�Υ�����ȥ��preActionFilter()/postActionFilter()���ɲ�
- Ethna_AppObject::_getPropDef()�˥���å���������ɲ�
- Ethna_CacheManager���饹���ɲ�(w/ localfile) - from GREE:)
- Ethna_DB::getDSN()�᥽�åɤ��ɲ�
- ini�ե�����Υ�����ȥ��dsn����ץ��ɲ�
- add-template���ޥ���ɲ�(by nnno)
- add-project���Υǥե���ȥƥ�ץ졼�ȥǥ�������ѹ�
- ethna���ޥ�ɤ�-v(--version)���ץ������ɲ�
- smarty_modifier_select(), smarty_function_select()��"selected"°����xhtml�б�(selected="true")
- {form_name}, {form_input}�ץ饰�����ɲ�(�㤷��experimental�Ȥ�����ongoing)
- Ethna_ViewClass��helper���������ե������б�
-- Ethna_ViewClass->helper_action_form = array('some_action_name' => null, ...)�Ȥ����{form_name}�Ȥ��ǻȤ��ޤ�
- [breaking B.C.] Ethna_ActionClass��preforward()���ݡ���(�फ�����Υ����ɤˤ���ޤ����ΤǤ�)���
- (�פ�)�ʥ��֥ͥ�å��ץ饰����{form}...{/form}�ɲ�
-- ethna_action�������ɲ�(�����hidden��������)
- Ethna_Controller��$smarty_block_plugin�ץ�ѥƥ����ɲ�
- ethna���ޥ�ɤ�add-action-cli���ɲ�
- [breaking B.C.] main_CLI�Υ������������ǥ��쥯�ȥ��action_cli���ѹ�
-- controller��directory�ץ�ѥƥ���'bin'���Ǥ��ɲ�
- ethna���ޥ�ɤ�add-app-manager���ɲ�(thanks butatic)
- Ethna_ActionForm ��ե�������� (by ������)
-- $this->form �ξ�ά�������� setFormVars() ���饳�󥹥ȥ饯���˰�ư
-- �ե������ͤΥ����顼/��������å��� setFormVars() �Ǥ���褦���ѹ�
--- vaildate() �������� setFormVars() �ǥ��顼 (handleError()) ��ȯ�����뤳�Ȥ�����ޤ�
-- �ե������ͤΥ����顼/��������å��ǥե�����������Ȱۤʤ���� null �ˤ���
-- �ե�����ǡ����κƹ������˹Ԥ��褦���ѹ�
-- �ե����������������� required, max/min �����꤬������ΥХ�����
-- _filter_alnum_zentohan() ���ɲ� (mb_convert_kana($value, "a"))
- XMLRPC�����ȥ�������faultCode���ݡ��Ȥ��ɲ�
-- action��Ethna_Error(���뤤��PEAR_Error)���֥������Ȥ��֤��ȥ��顼���֤��ޤ�
- XMLRPC�����ȥ��������ݡ����ɲ�(experimental)
-- ethna add-action-xmlrpc [action]��XMLRPC�᥽�åɤ��ɲò�ǽ
-- ����1�Ĥȥե��������1�Ĥ��������б����ޤ�
-- ToDo
--- ���ϥХåե������å�
--- method not found�ʤɥ��顼�����б�
- Ethna_ActionForm���饹�Υ��󥹥ȥ饯����setFormVars()��¹Ԥ��ʤ��褦���ѹ�
- ������ȥ�˴ޤޤ��'your name'��ޥ���({$author})���ѹ�(~/.ethna�б�)
- �ʤ���������ؿ�file_exists_ex(), is_absolute_path()���ɲ�
- SimpleTest�Ȥ�Ϣ�ȵ�ǽ���ɲ�(ethna���ޥ�ɤ�add-action-test,add-view-test���ɲäʤ�)
-- SimpleTest�Υ��󥹥ȡ�������å����ɲ�
- package.xml����������ץȲ���(ethna���ޥ�ɥ��󥹥ȡ����б��ʤ�)
- Haste_ADOdb, Haste_Creole�ޡ���(from Haste Project by halt����)
- Ethna_AppObject���饹�Υơ��֥�/�ץ�ѥƥ������ư�������ݡ����ɲ�(from generate_app_object originally by ��夵��+halt����)
- Ethna_Controller::getAppdir()�᥽�åɤ��ɲ�
- Ethna_Controller::getDBType()�ΰ�����null���ä���������������֤��褦���ѹ�
- ethna���ޥ�ɥ饤��ϥ�ɥ���ɲ�(+�ϥ�ɥ��pluggable��+add-view�ǥƥ�ץ졼���������ݡ���)��please cp bin/ethna to /usr/local/bin or somewhere
 generate_project_skelton.php -> ethna add-project
 generate_action_script.php   -> ethna add-action
 generate_view_script.php     -> ethna add-view
 generate_app_object.php      -> ethna add-app-object
- [breaking B.C.] client_type���ѻ� -> gateway�ɲ�
-- CLIENT_TYPE����ѻ�
-- Ethna_Controller::getClientType(), Ethna_Controller::setClientType()�ѻ�
-- Ethna_Controller::setCLI()/Ethna_Controller::getCLI() -> obsolete
-- GATEWAY����ɲ�(GATEWAY_WWW, GATEWAY_CLI, GATEWAY_XMLRPC, GATEWAY_SOAP)
-- Ethna_Controller::setGateway()/Ethna_Controller::getGateway()�ɲ�
-- ��꤫����AMF�����ȥ��������ݡ��Ȥ�(��ö)�ѻ�
- Ethna_SkeltonGenerator::_checkAppId()��Ethna_Controller::checkAppId()�˰�ư
- generate_app_object���ɲ�
- ���饹�Υ᥽�åɤ�SmartyFunction�Ȥ�����Ͽ�Ǥ���褦�˽���

*** bug fixes

- [[#8435>http://sourceforge.jp/tracker/index.php?func=detail&aid=8435&group_id=1343&atid=5092]](Ethna_AppObject prop_def[]['seq']��̤����)
- [[#8079>http://sourceforge.jp/tracker/index.php?func=detail&aid=8079&group_id=1343&atid=5092]](Filter��Backend��Ƥ֤�ActionForm���ͤ����ˤʤ�)
- [[#8200>http://sourceforge.jp/tracker/index.php?func=detail&aid=8200&group_id=1343&atid=5092]](PHP5.1.0�ʹߤ�af��validate()�����ե����å��������ʤ�)
- [[#8179>http://sourceforge.jp/tracker/index.php?func=detail&aid=8179&group_id=1343&atid=5092]](getManager������ͤ������Ϥ��ˤʤäƤ��ʤ�)
- [[#8400>http://sourceforge.jp/tracker/index.php?func=detail&aid=8400&group_id=1343&atid=5092]](AppObject prop_def[]['form_name']��NULL)
- [[#7751>http://sourceforge.jp/tracker/index.php?func=detail&aid=7751&group_id=1343&atid=5092]](SAFE_MODE��mail�ؿ����裵�����������Waning)����
- [[#8496>http://sourceforge.jp/tracker/index.php?func=detail&aid=8496&group_id=1343&atid=5092]](Ethna_AppObject.php���typo)����
- [[#8387>http://sourceforge.jp/tracker/index.php?func=detail&aid=8387&group_id=1343&atid=5092]](checkMailaddress��checkURL��Notice)����
- [[#8130>http://sourceforge.jp/tracker/index.php?func=detail&aid=8130&group_id=1343&atid=5092]](Notice�Ĥ֤�)����
- typo fixed (aleady -> already)
- [[#7717>http://sourceforge.jp/tracker/index.php?func=detail&aid=7717&group_id=1343&atid=5092]](Ethna_AppObject::add()��Notice)����
- [[#7664>http://sourceforge.jp/tracker/index.php?func=detail&aid=7664&group_id=1343&atid=5092]](Ethna_AppObject�ΥХ�)����
- [[#7729>http://sourceforge.jp/tracker/index.php?func=detail&aid=7729&group_id=1343&atid=5092]](ethna_info��Firefox���Ȥ����)����

- (within beta) ethna_handle.php��̵�Ѥ�ob_end_clean()�����������
- (within beta) ethna add-view�ǥץ������ȥǥ��쥯�ȥ����ꤷ�������������ե����뤬��������ʤ��������
- (within beta) Windows�Ǥ�ethna���ޥ�ɤ��ѥå��������饤�󥹥ȡ��뤷�����¹ԤǤ��ʤ��������
- (within beta) ActionForm������Υե������ͤ��˲�������������(by sfio����)


** [2006/01/29] 0.2.0

*** features

- ʸ�����min/max���顼�Υǥե���ȥ��顼��å���������
- �ե�����������˥������२�顼��å�����������Ǥ���褦���ѹ�
- Ethna_Controller::main_CLI()�᥽�åɤ˥ե��륿��̵���������륪�ץ������ɲ�
- Ethna_ActionForm���饹�Υե����������������ʥߥå����ѹ������褦�˽���
- Ethna_ActionForm���饹�Υե�����������˥ƥ�ץ졼�ȵ�ǽ���ɲ�
- Ethna_Backend::getActionClasss()�᥽�åɤ��ɲ�(�¹���Υ�������󥯥饹�����)
- ~/.ethna�ե�����ˤ��桼�����������ȥ�ޥ�����ɲ�
- smarty_function_select��$empty�������ɲ�
- mb_*���Ѵ������󥳡��ǥ��󥰤�EUC-JP���꤫���������󥳡��ǥ��󥰤��ѹ�
- Ethna_Backend::begin()��Ethna_Backend::commit()��Ethna_Backend::rollback()���ѻ�
- Ethna_Controller::getDB()��Ethna_Controller::getDBType()���ѹ�
- Ethna_DB���饹����ݥ��饹(����)�Ȥ��ƿ�����Ethna_DB���饹���������Ethna_DB_PEAR���饹���ɲ�
- Ethna_LogWriter���饹����ݥ��饹(����)�Ȥ��ƿ�����Ethna_LogWriter���饹���������Ethna_LogWriter_Echo��Ethna_LogWriter_File��Ethna_LogWriter_Syslog���饹���ɲ�
- log_facility��null�ξ��Υ����ϥ��饹��Ethna_LogWriter_Echo����Ethna_LogWriter���ѹ�(�����Ϥʤ�)
- log_facility�˥��饹̾��񤤤����Ϥ��Υ��饹������ϥ��饹�Ȥ������Ѥ���褦���ѹ�
- Ethna_Filter::preFilter()��Ethna_Filter::postFilter()��Ethna_Error���֥������Ȥ��֤������ϼ¹Ԥ���ߤ���褦���ѹ�
- Ethna_InfoManager������ɽ�����ܤ��ɲ�
- Ethna_ActionForm::isForceValidatePlus()��Ethna_ActionForm::setForceValidatePlus()�᥽�åɤȡ�$force_validate_plus���Ф��ɲ�($force_validate_plus��true�����ꤹ��ȡ��̾︡�ڤǥ��顼��ȯ���������Ǥ�_validatePlus()�᥽�åɤ��¹Ԥ����ݥǥե����:false)
- �ե������������custom°���˥���޶��ڤ�Ǥ�ʣ���᥽�åɥ��ݡ��Ȥ��ɲ�

*** bug fixes

- htmlspecialchars��ENT_QUOTES���ץ������ɲ�
- Ethna_AppSQL���饹�Υ��󥹥ȥ饯���᥽�å�̾����
- [[#7659>http://sourceforge.jp/tracker/index.php?func=detail&aid=7659&group_id=1343&atid=5092]](Ethna_Config.php��Notice���顼)����
- Ethna_SOAP_ActionForm.php��typo����
- [[#6616>http://sourceforge.jp/tracker/index.php?func=detail&aid=6616&group_id=1343&atid=5092]](���å�����Object���Ǽ�Ǥ��ʤ�)����
- [[#7640>https://sourceforge.jp/tracker/index.php?func=detail&aid=7640&group_id=1343&atid=5092]](�����¸ʸ���Υ����å��ǥ��顼��å�������ɽ������ʤ���)����
- [[#6566>https://sourceforge.jp/tracker/index.php?func=detail&aid=6566&group_id=1343&atid=5092]](skel.action.php�Υ���ץ��typo)����
- [[#7451>https://sourceforge.jp/tracker/index.php?func=detail&aid=7451&group_id=1343&atid=5092]](PHP 5.0.5�б�)����
- .museum�б�
- Ethna_Backend���饹�Υ��饹����¿���������
- BASE����αƶ��ǥ���ȥ���ηѾ���������������
- Windows�Ķ����������Ƥ��ʤ�LOG_LOCAL�����ɾ�����Ƥ��ޤ��������
- [[#6423>http://sourceforge.jp/tracker/index.php?func=detail&aid=6423&group_id=1343&atid=5092]](php-4.4.0�����̤Υ��顼�θ塢Segv(11))����(patch by ramsy����)
- [[#6074>http://sourceforge.jp/tracker/index.php?func=detail&aid=6074&group_id=1343&atid=5092]](generate_project_skelton.php��ư��۾�)����
- safe_mode=on�ξ���uid/gid warning��ȯ������(��ǽ���Τ���)�������
- ���פʻ����Ϥ�����
- ����¾�٤��ʽ���(elseif -> else if��)
- PATH_SEPARATOR/DIRECTORY_SEPARATOR��̤����ξ��(PHP 4.1.x��)���������
- smarty_modifier_wordwrap_i18n()�β����б�
- �桼������ե����ม�ڥ᥽�åɤ��ƤӽФ���ʤ�(���Ȥ�����)�������
- �ޥ�������ץ饤�ޥꥭ�����ѻ��˥��֥������Ȥ���������������Ƚ�̤Ǥ��ʤ��������
- Ethna_AppObject��JOIN������SQL���顼�ˤʤ�ʤ��Ȥ�������������
- ���å��������������륿���ߥ󥰤��ٱ�(̵�¥롼�פ����������)
- Ethna_MalSender����mail()�ؿ��˥��ץ������Ϥ���褦�˽���
- Ethna_View_List::_fixNameObject���оݥ��֥������Ȥ��Ϥ��褦�˽���


** [2005/03/02] 0.1.5

*** features

- Ethna_Controller::getCLI()(CLI�Ǽ¹��椫�ɤ������֤��᥽�å�)���ɲ�
- ethna_error_handler��php.ini������˱�����PHP������Ϥ���褦���ѹ�
- Smarty�ץ饰����(truncate_i18n)���ɲ�
- Ethna_AppObject/Ethna_AppManager�˥���å��嵡�����ɲ�(experimental)
- �᡼��ƥ�ץ졼�ȥ��󥸥�Υեå��᥽�åɤ��ɲ�
- MIME���󥳡����ѥ桼�ƥ���ƥ��᥽�åɤ��ɲ�
- include_path�Υ��ѥ졼����win32�б�

*** bug fixes

- ethna_error_handler��typo����
- Ethna_Session���饹�ǥ������������Ϥ���ʤ��������


** [2005/01/14] 0.1.4

*** features

- Ethna_AppObject��JOIN�������ˡ�(��ǽ�ʤ�)�ץ饤�ޥꥭ����GROUP BY����褦���ѹ�

*** bug fixes

- __ethna_info__������ư��ʤ��������:(


** [2005/01/13] 0.1.3

*** features

- Ethna_AppSearchObject��ʣ�����б�
- Ethna_ClassFactory���饹���ɲ�
- Ethna_Controller��backend, i18n, session, action_error���Ф��ѻ�
- Ethna_Controller::getClass()�᥽�åɤ��ѻ�
- Ethna_ActionClass��authenticate�᥽�åɤ��ɲ�
- preActionFilter/postActionFilter���ɲ�(experimental)
- Ethna_View_List(�ꥹ��ɽ���ѥӥ塼���쥯�饹)�Υ������б�
- �Ȥ߹���Smarty�ؿ�is_error()���ɲ�
- Ethna_ActionForm::handleError����2�������ѻ�
- Ethna_ActionForm::_handleError��public�᥽�åɤ��ѹ�(Ethna_ActionForm::handleError��̾���ѹ�)
- Ethna_ActionForm::getDef�᥽�åɤ˰������ɲ�(��ά��)

*** bug fixes

- �ե�����������������ꤷ�Ƥ������Υ�����������å��᥽�åɤθƤӽФ����������Ԥ��ʤ��������
- �ե�����������������ꤷ�Ƥ�������ɬ�ܥ����å����������Ԥ��ʤ��������
- __ethna_info__�����֥ǥ��쥯�ȥ��������줿���������������������Ǥ��ʤ��������
- VAR_TYPE_FILE�ξ���regexp°����̵���ˤʤ�褦�˽���


** [2004/12/23] 0.1.2

*** features

- __ethna_info__�����������ɲ�
- class_path, form_path, view_path°���Υե�ѥ����ꥵ�ݡ��Ȥ��ɲ�
- ������ץȤ�1�ե�����ˤޤȤ��ġ���(bin/unify_script.php)���ɲ�

*** bug fixes

- �ץ������ȥ�����ȥ��������˥��ץꥱ�������ID��ʸ����/ͽ��������å�����褦�˽���
- 'form_name'����ꤹ���̵�Ѥ˷ٹ�ȯ�������������
- ���Хѥ�Ƚ��Υץ�åȥե������¸����(Windows�б�����)
- VAR_TYPE_INT��VAR_TYPE_FLOAT������ͤ���ʣ���Ƥ����������
- SOAP/Mobile(AU)�ǥ�������󥹥���ץȤΥѥ��������������Ǥ��ʤ��������
- Ethna_Util::getRandom()��mt_srand()���Ĥ�rand()��Ƥ�Ǥ����ս��mt_rand()��ƤӽФ��褦�˽���
- CHANGES�Υ��󥳡��ǥ��󥰽���(ISO-2022-JP -> EUC-JP)
- �ե졼������ȯ�Ԥ���SQLʸ�˰����ĤäƤ������ߥ�������
- ����ȥ�ݥ����(index.php)�˵��Ҥ��줿�ǥե���ȥ��������̾��1�����ܤ˥������ꥹ�������Ѥ���Ƥ���ȡ�������ư��ʤ�(���⤷��ʤ�)�������~
��(����ʾ��):
 <?php
 include_once('../../app/Sample_Controller.php');
 Sample_Controller::Main('Sample_Controller', array(
  'login*',
 ));
 ?>


** [2004/12/10] 0.1.1

*** bug fixes

- �ӥ塼���֥������Ȥ�preforward()���ƤФ�ʤ����Ȥ������������
- ���������/�ӥ塼�Υ�����ȥ��������˥ե�������񤭤��ʤ��褦�˽���
- �ӥ塼�Υ�����ȥ�ǥ��饹̾���������ִ�����ʤ��������

** [2004/12/09] 0.1.0

- �����꡼��

