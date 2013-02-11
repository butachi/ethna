<?php
// vim: foldmethod=marker
/**
 *  Ethna_ActionForm.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

/** �귿�ե��륿: Ⱦ������ */
define('FILTER_HW', 'numeric_zentohan,alphabet_zentohan,ltrim,rtrim,ntrim');

/** �귿�ե��륿: �������� */
define('FILTER_FW', 'kana_hantozen,ntrim');


// {{{ Ethna_ActionForm
/**
 *  ���������ե����९�饹
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_ActionForm
{
    /**#@+
     *  @access private
     */

    /** @var    array   �ե����������(�ǥե����) */
    var $form_template = array();

    /** @var    array   �ե���������� */
    var $form = array();

    /** @var    array   �ե������� */
    var $form_vars = array();

    /** @var    array   ���ץꥱ������������� */
    var $app_vars = array();

    /** @var    array   ���ץꥱ�������������(��ư���������פʤ�) */
    var $app_ne_vars = array();

    /** @var    object  Ethna_ActionError   ��������󥨥顼���֥������� */
    var $action_error;

    /** @var    object  Ethna_ActionError   ��������󥨥顼���֥�������(��ά��) */
    var $ae;

    /** @var    object  Ethna_I18N  i18n���֥������� */
    var $i18n;

    /** @var    object  Ethna_Logger    �������֥������� */
    var $logger;

    /** @var    array   �ե������������ */
    var $def = array('name', 'required', 'max', 'min', 'regexp', 'custom', 'filter', 'form_type', 'type');

    /** @var    bool    �ɲø��ڶ����ե饰 */
    var $force_validate_plus = false;

    /**#@-*/

    /**
     *  Ethna_ActionForm���饹�Υ��󥹥ȥ饯��
     *
     *  @access public
     *  @param  object  Ethna_Controller    &$controller    controller���֥�������
     */
    function Ethna_ActionForm(&$controller)
    {
        $this->action_error =& $controller->getActionError();
        $this->ae =& $this->action_error;
        $this->i18n =& $controller->getI18N();
        $this->logger =& $controller->getLogger();

        if (isset($_SERVER['REQUEST_METHOD']) == false) {
            return;
        }

        // �ե������ͥƥ�ץ졼�Ȥι���
        $this->form_template = $this->_setFormTemplate($this->form_template);

        // �ե����������������
        $this->_setFormDef();

        // ��ά������
        foreach ($this->form as $name => $value) {
            foreach ($this->def as $k) {
                if (isset($value[$k]) == false) {
                    $this->form[$name][$k] = null;
                }
            }
        }
    }

    /**
     *  �ե������ͤΥ�������(R)
     *
     *  @access public
     *  @param  string  $name   �ե������ͤ�̾��
     *  @return mixed   �ե�������
     */
    function get($name)
    {
        if (isset($this->form_vars[$name])) {
            return $this->form_vars[$name];
        }
        return null;
    }

    /**
     *  �ե�������������������
     *
     *  @access public
     *  @param  string  $name   ��������ե�����̾(��ά��:null�ʤ����Ƥ�������������)
     *  @return array   �ե����������
     */
    function getDef($name = null)
    {
        if (is_null($name)) {
            return $this->form;
        }

        if (array_key_exists($name, $this->form) == false) {
            return null;
        } else {
            return $this->form[$name];
        }
    }

    /**
     *  �ե��������ɽ��̾���������
     *
     *  @access public
     *  @param  string  $name   �ե������ͤ�̾��
     *  @return mixed   �ե������ͤ�ɽ��̾
     */
    function getName($name)
    {
        if (isset($this->form[$name]) == false) {
            return null;
        }
        if (isset($this->form[$name]['name']) && $this->form[$name]['name'] != null) {
            return $this->form[$name]['name'];
        }

        // try message catalog
        return $this->i18n->get($name);
    }

    /**
     *  �桼�������������줿�ե������ͤ�ե�����������˽��äƥ���ݡ��Ȥ���
     *
     *  @access public
     */
    function setFormVars()
    {
        if (strcasecmp($_SERVER['REQUEST_METHOD'], 'post') == 0) {
            $http_vars =& $_POST;
        } else {
            $http_vars =& $_GET;
        }

        foreach ($this->form as $name => $def) {
            $type = is_array($def['type']) ? $def['type'][0] : $def['type'];
            if ($type == Ethna_Const::VAR_TYPE_FILE) {
                // �ե�����ξ��

                // �ͤ�̵ͭ�θ���
                if (isset($_FILES[$name]) == false || is_null($_FILES[$name])) {
                    $this->form_vars[$name] = null;
                    continue;
                }

                // ����¤�θ���
                if (is_array($def['type'])) {
                    if (is_array($_FILES[$name]['tmp_name']) == false) {
                        $this->handleError($name, Ethna_Const::E_FORM_WRONGTYPE_ARRAY);
                        $this->form_vars[$name] = null;
                        continue;
                    }
                } else {
                    if (is_array($_FILES[$name]['tmp_name'])) {
                        $this->handleError($name, Ethna_Const::E_FORM_WRONGTYPE_SCALAR);
                        $this->form_vars[$name] = null;
                        continue;
                    }
                }

                $files = null;
                if (is_array($def['type'])) {
                    $files = array();
                    // �ե�����ǡ�����ƹ���
                    foreach (array_keys($_FILES[$name]['name']) as $key) {
                        $files[$key] = array();
                        $files[$key]['name'] = $_FILES[$name]['name'][$key];
                        $files[$key]['type'] = $_FILES[$name]['type'][$key];
                        $files[$key]['size'] = $_FILES[$name]['size'][$key];
                        $files[$key]['tmp_name'] = $_FILES[$name]['tmp_name'][$key];
                        $files[$key]['error'] = $_FILES[$name]['error'][$key];
                    }
                } else {
                    $files = $_FILES[$name];
                }

                // �ͤΥ���ݡ���
                $this->form_vars[$name] = $files;

            } else {
                // �ե�����ʳ��ξ��

                // �ͤ�̵ͭ�θ���
                if (isset($http_vars[$name]) == false || is_null($http_vars[$name])) {
                    $this->form_vars[$name] = null;
                    if (isset($http_vars["{$name}_x"]) && isset($http_vars["{$name}_y"])) {
                        // �����λ��ͤ˹�碌��
                        $this->form_vars[$name] = $http_vars["{$name}_x"];
                    }
                    continue;
                }

                // ����¤�θ���
                if (is_array($def['type'])) {
                    if (is_array($http_vars[$name]) == false) {
                        // ��̩�ˤϡ���������γ����Ǥϥ����顼�Ǥ���٤�
                        // TODO: ¿����������ˤ��б�����
                        $this->handleError($name, Ethna_Const::E_FORM_WRONGTYPE_ARRAY);
                        $this->form_vars[$name] = null;
                        continue;
                    }
                } else {
                    if (is_array($http_vars[$name])) {
                        $this->handleError($name, Ethna_Const::E_FORM_WRONGTYPE_SCALAR);
                        $this->form_vars[$name] = null;
                        continue;
                    }
                }

                // �ͤΥ���ݡ���
                $this->form_vars[$name] = $http_vars[$name];
            }
        }
    }

    /**
     *  �ե������ͤؤΥ�������(W)
     *
     *  @access public
     *  @param  string  $name   �ե������ͤ�̾��
     *  @param  string  $value  ���ꤹ����
     */
    function set($name, $value)
    {
        $this->form_vars[$name] = $value;
    }

    /**
     *  �ե���������������ꤹ��
     *
     *  @access public
     *  @param  string  $name   ���ꤹ��ե�����̾(��ά��:null�ʤ����Ƥ���������ꤹ��)
     *  @param  array   $value  ���ꤹ��ե����������
     *  @return array   �ե����������
     */
    function setDef($name, $value)
    {
        if (is_null($name)) {
            $this->form = $value;
        }

        $this->form[$name] = $value;
    }

    /**
     *  �ե������ͤ�����ˤ����֤�
     *
     *  @access public
     *  @param  bool    $escape HTML���������ץե饰(true:���������פ���)
     *  @return array   �ե������ͤ��Ǽ��������
     */
    function &getArray($escape = true)
    {
        $retval = array();

        $this->_getArray($this->form_vars, $retval, $escape);

        return $retval;
    }

    /**
     *  ���ץꥱ������������ͤΥ�������(R)
     *
     *  @access public
     *  @param  string  $name   ����
     *  @return mixed   ���ץꥱ�������������
     */
    function getApp($name)
    {
        if (isset($this->app_vars[$name]) == false) {
            return null;
        }
        return $this->app_vars[$name];
    }

    /**
     *  ���ץꥱ������������ͤΥ�������(W)
     *
     *  @access public
     *  @param  string  $name   ����
     *  @param  mixed   $value  ��
     */
    function setApp($name, $value)
    {
        $this->app_vars[$name] = $value;
    }

    /**
     *  ���ץꥱ������������ͤ�����ˤ����֤�
     *
     *  @access public
     *  @param  boolean $escape HTML���������ץե饰(true:���������פ���)
     *  @return array   �ե������ͤ��Ǽ��������
     */
    function &getAppArray($escape = true)
    {
        $retval = array();

        $this->_getArray($this->app_vars, $retval, $escape);

        return $retval;
    }

    /**
     *  ���ץꥱ�������������(��ư���������פʤ�)�Υ�������(R)
     *
     *  @access public
     *  @param  string  $name   ����
     *  @return mixed   ���ץꥱ�������������
     */
    function getAppNE($name)
    {
        if (isset($this->app_ne_vars[$name]) == false) {
            return null;
        }
        return $this->app_ne_vars[$name];
    }

    /**
     *  ���ץꥱ�������������(��ư���������פʤ�)�Υ�������(W)
     *
     *  @access public
     *  @param  string  $name   ����
     *  @param  mixed   $value  ��
     */
    function setAppNE($name, $value)
    {
        $this->app_ne_vars[$name] = $value;
    }

    /**
     *  ���ץꥱ�������������(��ư���������פʤ�)������ˤ����֤�
     *
     *  @access public
     *  @param  boolean $escape HTML���������ץե饰(true:���������פ���)
     *  @return array   �ե������ͤ��Ǽ��������
     */
    function &getAppNEArray($escape = false)
    {
        $retval = array();

        $this->_getArray($this->app_ne_vars, $retval, $escape);

        return $retval;
    }

    /**
     *  �ե����������ˤ����֤�(��������)
     *
     *  @access private
     *  @param  array   &$vars      �ե�����(��)������
     *  @param  array   &$retval    ����ؤ��Ѵ����
     *  @param  bool    $escape     HTML���������ץե饰(true:���������פ���)
     */
    function _getArray(&$vars, &$retval, $escape)
    {
        foreach (array_keys($vars) as $name) {
            if (is_array($vars[$name])) {
                $retval[$name] = array();
                $this->_getArray($vars[$name], $retval[$name], $escape);
            } else {
                $retval[$name] = $escape ? htmlspecialchars($vars[$name], ENT_QUOTES) : $vars[$name];
            }
        }
    }

    /**
     *  �ɲø��ڶ����ե饰���������
     *
     *  @access public
     *  @return bool    true:�ɲø��ڶ���(�̾︡�ڤǥ��顼��ȯ���������Ǥ�_validatePlus()���ƤӽФ����) false:�ɲø�������
     */
    function isForceValidatePlus()
    {
        return $this->force_validate_plus;
    }

    /**
     *  �ɲø��ڶ����ե饰�����ꤹ��
     *
     *  @access public
     *  @param  $force_validate_plus    �ɲø��ڶ����ե饰
     */
    function setForceValidatePlus($force_validate_plus)
    {
        $this->force_validate_plus = $force_validate_plus;
    }

    /**
     *  �ե������͸��ڥ᥽�å�
     *
     *  @access public
     *  @return int     ȯ���������顼�ο�
     */
    function validate()
    {
        foreach ($this->form as $name => $def) {
            // ����ǥ�åפ���
            unset($form_vars);
            if (is_null($this->form_vars[$name])) {
                $form_vars = array();
            } else if (is_array($def['type'])) {
                $form_vars =& $this->form_vars[$name];
            } else {
                $form_vars = array(& $this->form_vars[$name]);
            }

            // �ե�����ξ��������1��valid�ʤ�required���򥯥ꥢ����
            // TODO: ���ο������Ǥ���褦�ˤ���
            $type = is_array($def['type']) ? $def['type'][0] : $def['type'];
            $valid_keys = array();
            $required_num = max(1, $type == Ethna_Const::VAR_TYPE_FILE ? 1 : count($form_vars));

            foreach (array_keys($form_vars) as $key) {
                // filter
                if ($type != Ethna_Const::VAR_TYPE_FILE) {
                    $form_vars[$key] =
                        $this->_filter($form_vars[$key], $def['filter']);
                }

                // �ͤ����������å�
                if ($type == Ethna_Const::VAR_TYPE_FILE) {
                    if ($form_vars[$key]['size'] == 0
                        || is_uploaded_file($form_vars[$key]['tmp_name']) == false) {
                        continue;
                    }
                } else {
                    if (is_scalar($form_vars[$key]) == false
                        || strlen($form_vars[$key]) == 0) {
                        continue;
                    }
                }

                // valid_keys ���ɲ�
                $valid_keys[] = $key;

                // _validate
                $this->_validate($name, $form_vars[$key], $def);
            }

            // required ��Ƚ��
            if ($def['required'] && (count($valid_keys) < $required_num)) {
                $this->handleError($name, Ethna_Const::E_FORM_REQUIRED);
                continue;
            }

            // ��������᥽�åɤμ¹�
            // TODO: ����ȥ����顼�Ǥλ��ͤ����Τˤ���
            if ($def['custom'] != null && is_array($def['type'])) {
                $this->_validateCustom($def['custom'], $name);
            }
        }

        if ($this->ae->count() == 0 || $this->isForceValidatePlus()) {
            // �ɲø��ڥ᥽�å�
            $this->_validatePlus();
        }

        return $this->ae->count();
    }

    /**
     *  �����å��᥽�å�(���쥯�饹)
     *
     *  @access public
     *  @param  string  $name   �ե��������̾
     *  @return array   �����å��оݤΥե�������(���顼��̵������null)
     */
    function check($name)
    {
        if (is_null($this->form_vars[$name]) || $this->form_vars[$name] === "") {
            return null;
        }

        // Ethna_Backend������
        $c =& Ethna_Controller::getInstance();
        $this->backend =& $c->getBackend();

        return to_array($this->form_vars[$name]);
    }

    /**
     *  �����å��᥽�å�: �����¸ʸ��
     *
     *  @access public
     *  @param  string  $name   �ե��������̾
     *  @return object  Ethna_Error ���顼���֥�������(���顼��̵������null)
     */
    function &checkVendorChar($name)
    {
        $null = null;
        $string = $this->form_vars[$name];

        for ($i = 0; $i < strlen($string); $i++) {
            /* JIS13��Τߥ����å� */
            $c = ord($string{$i});
            if ($c < 0x80) {
                /* ASCII */
            } else if ($c == 0x8e) {
                /* Ⱦ�ѥ��� */
                $i++;
            } else if ($c == 0x8f) {
                /* JIS X 0212 */
                $i += 2;
            } else if ($c == 0xad || ($c >= 0xf9 && $c <= 0xfc)) {
                /* IBM��ĥʸ�� / NEC����IBM��ĥʸ�� */
                return $this->ae->add($name, '{form}�˵����¸ʸ�������Ϥ���Ƥ��ޤ�', Ethna_Const::E_FORM_INVALIDCHAR);
            } else {
                $i++;
            }
        }

        return $null;
    }

    /**
     *  �����å��᥽�å�: bool��
     *
     *  @access public
     *  @param  string  $name   �ե��������̾
     *  @return object  Ethna_Error ���顼���֥�������(���顼��̵������null)
     */
    function &checkBoolean($name)
    {
        $null = null;
        $form_vars = $this->check($name);

        if ($form_vars == null) {
            return $null;
        }

        foreach ($form_vars as $v) {
            if ($v === "") {
                continue;
            }
            if ($v != "0" && $v != "1") {
                return $this->ae->add($name, '{form}�����������Ϥ��Ƥ�������', Ethna_Const::E_FORM_INVALIDCHAR);
            }
        }

        return $null;
    }

    /**
     *  �����å��᥽�å�: �᡼�륢�ɥ쥹
     *
     *  @access public
     *  @param  string  $name   �ե��������̾
     *  @return object  Ethna_Error ���顼���֥�������(���顼��̵������null)
     */
    function &checkMailaddress($name)
    {
        $null = null;
        $form_vars = $this->check($name);

        if ($form_vars == null) {
            return $null;
        }

        foreach ($form_vars as $v) {
            if ($v === "") {
                continue;
            }
            if (Ethna_Util::checkMailaddress($v) == false) {
                return $this->ae->add($name, '{form}�����������Ϥ��Ƥ�������', Ethna_Const::E_FORM_INVALIDCHAR);
            }
        }

        return $null;
    }

    /**
     *  �����å��᥽�å�: URL
     *
     *  @access public
     *  @param  string  $name   �ե��������̾
     *  @return object  Ethna_Error ���顼���֥�������(���顼��̵������null)
     */
    function &checkURL($name)
    {
        $null = null;
        $form_vars = $this->check($name);

        if ($form_vars == null) {
            return $null;
        }

        foreach ($form_vars as $v) {
            if ($v === "") {
                continue;
            }
            if (preg_match('/^(http:\/\/|https:\/\/|ftp:\/\/)/', $v) == 0) {
                return $this->ae->add($name, '{form}�����������Ϥ��Ƥ�������', Ethna_Const::E_FORM_INVALIDCHAR);
            }
        }

        return $null;
    }

    /**
     *  �ե������ͤ�hidden�����Ȥ����֤�
     *
     *  @access public
     *  @param  array   $include_list   ���󤬻��ꤵ�줿��硢��������˴ޤޤ��ե�������ܤΤߤ��оݤȤʤ�
     *  @param  array   $exclude_list   ���󤬻��ꤵ�줿��硢��������˴ޤޤ�ʤ��ե�������ܤΤߤ��оݤȤʤ�
     *  @return string  hidden�����Ȥ��Ƶ��Ҥ��줿HTML
     */
    function getHiddenVars($include_list = null, $exclude_list = null)
    {
        $hidden_vars = "";
        foreach ($this->form as $key => $value) {
            if (is_array($include_list) == true && in_array($key, $include_list) == false) {
                continue;
            }
            if (is_array($exclude_list) == true && in_array($key, $exclude_list) == true) {
                continue;
            }

            $form_value = $this->form_vars[$key];
            if (is_array($form_value) == false) {
                $form_value = array($form_value);
                $form_array = false;
            } else {
                $form_array = true;
            }
            foreach ($form_value as $k => $v) {
                if ($form_array) {
                    $form_name = "$key" . "[$k]";
                } else {
                    $form_name = $key;
                }
                $hidden_vars .= sprintf("<input type=\"hidden\" name=\"%s\" value=\"%s\" />\n",
                    $form_name, htmlspecialchars($v, ENT_QUOTES));
            }
        }
        return $hidden_vars;
    }

    /**
     *  �ե������͸��ڤΥ��顼������Ԥ�
     *
     *  @access public
     *  @param  string      $name   �ե��������̾
     *  @param  int         $code   ���顼������
     */
    function handleError($name, $code)
    {
        $def = $this->getDef($name);

        // �桼��������顼��å�����
        $code_map = array(
            Ethna_Const::E_FORM_REQUIRED     => 'required_error',
            Ethna_Const::E_FORM_WRONGTYPE_SCALAR => 'type_error',
            Ethna_Const::E_FORM_WRONGTYPE_ARRAY  => 'type_error',
            Ethna_Const::E_FORM_WRONGTYPE_INT    => 'type_error',
            Ethna_Const::E_FORM_WRONGTYPE_FLOAT  => 'type_error',
            Ethna_Const::E_FORM_WRONGTYPE_DATETIME   => 'type_error',
            Ethna_Const::E_FORM_WRONGTYPE_BOOLEAN    => 'type_error',
            Ethna_Const::E_FORM_MIN_INT      => 'min_error',
            Ethna_Const::E_FORM_MIN_FLOAT    => 'min_error',
            Ethna_Const::E_FORM_MIN_DATETIME => 'min_error',
            Ethna_Const::E_FORM_MIN_FILE     => 'min_error',
            Ethna_Const::E_FORM_MIN_STRING   => 'min_error',
            Ethna_Const::E_FORM_MAX_INT      => 'max_error',
            Ethna_Const::E_FORM_MAX_FLOAT    => 'max_error',
            Ethna_Const::E_FORM_MAX_DATETIME => 'max_error',
            Ethna_Const::E_FORM_MAX_FILE     => 'max_error',
            Ethna_Const::E_FORM_MAX_STRING   => 'max_error',
            Ethna_Const::E_FORM_REGEXP       => 'regexp_error',
        );
        if (array_key_exists($code_map[$code], $def)) {
            $this->ae->add($name, $def[$code_map[$code]], $code);
            return;
        }

        if ($code == Ethna_Const::E_FORM_REQUIRED) {
            switch ($def['form_type']) {
            case Ethna_Const::FORM_TYPE_TEXT:
            case Ethna_Const::FORM_TYPE_PASSWORD:
            case Ethna_Const::FORM_TYPE_TEXTAREA:
            case Ethna_Const::FORM_TYPE_SUBMIT:
                $message = "{form}�����Ϥ��Ʋ�����";
                break;
            case Ethna_Const::FORM_TYPE_SELECT:
            case Ethna_Const::FORM_TYPE_RADIO:
            case Ethna_Const::FORM_TYPE_CHECKBOX:
            case Ethna_Const::FORM_TYPE_FILE:
                $message = "{form}�����򤷤Ʋ�����";
                break;
            default:
                $message = "{form}�����Ϥ��Ʋ�����";
                break;
            }
        } else if ($code == Ethna_Const::E_FORM_WRONGTYPE_SCALAR) {
            $message = "{form}�ˤϥ����顼�ͤ����Ϥ��Ʋ�����";
        } else if ($code == Ethna_Const::E_FORM_WRONGTYPE_ARRAY) {
            $message = "{form}�ˤ���������Ϥ��Ʋ�����";
        } else if ($code == Ethna_Const::E_FORM_WRONGTYPE_INT) {
            $message = "{form}�ˤϿ���(����)�����Ϥ��Ʋ�����";
        } else if ($code == Ethna_Const::E_FORM_WRONGTYPE_FLOAT) {
            $message = "{form}�ˤϿ���(����)�����Ϥ��Ʋ�����";
        } else if ($code == Ethna_Const::E_FORM_WRONGTYPE_DATETIME) {
            $message = "{form}�ˤ����դ����Ϥ��Ʋ�����";
        } else if ($code == Ethna_Const::E_FORM_WRONGTYPE_BOOLEAN) {
            $message = "{form}�ˤ�1�ޤ���0�Τ����ϤǤ��ޤ�";
        } else if ($code == Ethna_Const::E_FORM_MIN_INT) {
            $this->ae->add($name, "{form}�ˤ�%d�ʾ�ο���(����)�����Ϥ��Ʋ�����", $code, $def['min']);
            return;
        } else if ($code == Ethna_Const::E_FORM_MIN_FLOAT) {
            $this->ae->add($name, "{form}�ˤ�%f�ʾ�ο���(����)�����Ϥ��Ʋ�����", $code, $def['min']);
            return;
        } else if ($code == Ethna_Const::E_FORM_MIN_DATETIME) {
            $this->ae->add($name, "{form}�ˤ�%s�ʹߤ����դ����Ϥ��Ʋ�����", $code, $def['min']);
            return;
        } else if ($code == Ethna_Const::E_FORM_MIN_FILE) {
            $this->ae->add($name, "{form}�ˤ�%dKB�ʾ�Υե��������ꤷ�Ʋ�����", $code, $def['min']);
            return;
        } else if ($code == Ethna_Const::E_FORM_MIN_STRING) {
            $this->ae->add($name, "{form}�ˤ�����%dʸ���ʾ�(Ⱦ��%dʸ���ʾ�)���Ϥ��Ʋ�����", $code, intval($def['min']/2), $def['min']);
            return;
        } else if ($code == Ethna_Const::E_FORM_MAX_INT) {
            $this->ae->add($name, "{form}�ˤ�%d�ʲ��ο���(����)�����Ϥ��Ʋ�����", $code, $def['max']);
            return;
        } else if ($code == Ethna_Const::E_FORM_MAX_FLOAT) {
            $this->ae->add($name, "{form}�ˤ�%f�ʲ��ο���(����)�����Ϥ��Ʋ�����", $code, $def['max']);
            return;
        } else if ($code == Ethna_Const::E_FORM_MAX_DATETIME) {
            $this->ae->add($name, "{form}�ˤ�%s���������դ����Ϥ��Ʋ�����", $code, $def['max']);
            return;
        } else if ($code == Ethna_Const::E_FORM_MAX_FILE) {
            $this->ae->add($name, "{form}�ˤ�%dKB�ʲ��Υե��������ꤷ�Ʋ�����", $code, $def['max']);
            return;
        } else if ($code == Ethna_Const::E_FORM_MAX_STRING) {
            $this->ae->add($name, "{form}������%dʸ���ʲ�(Ⱦ��%dʸ���ʲ�)�����Ϥ��Ʋ�����", $code, intval($def['max']/2), $def['max']);
            return;
        } else if ($code == Ethna_Const::E_FORM_REGEXP) {
            $message = "{form}�����������Ϥ��Ƥ�������";
        }

        $this->ae->add($name, $message, $code);
    }

    /**
     *  �桼��������ڥ᥽�å�(�ե������ʹ֤�Ϣ�ȥ����å���)
     *
     *  @access protected
     */
    function _validatePlus()
    {
    }

    /**
     *  �ե������͸��ڥ᥽�å�(����)
     *
     *  @access private
     *  @param  string  $name       �ե��������̾
     *  @param  mixed   $var        �ե�������(����Ǥ���иġ������)
     *  @param  array   $def        �ե����������
     *  @param  bool    $test       ���顼���֥���������Ͽ�ե饰(true:��Ͽ���ʤ�)
     *  @return bool    true:���ｪλ false:���顼
     */
    function _validate($name, $var, $def, $test = false)
    {
        $type = is_array($def['type']) ? $def['type'][0] : $def['type'];

        // type
        if ($type == Ethna_Const::VAR_TYPE_INT) {
            if (!preg_match('/^-?\d+$/', $var)) {
                if ($test == false) {
                    $this->handleError($name, Ethna_Const::E_FORM_WRONGTYPE_INT);
                }
                return false;
            }
        } else if ($type == Ethna_Const::VAR_TYPE_FLOAT) {
            if (!preg_match('/^-?\d+$/', $var) && !preg_match('/^-?\d+\.\d+$/', $var)) {
                if ($test == false) {
                    $this->handleError($name, Ethna_Const::E_FORM_WRONGTYPE_FLOAT);
                }
                return false;
            }
        } else if ($type == Ethna_Const::VAR_TYPE_DATETIME) {
            $r = strtotime($var);
            if ($r == -1 || $r === false) {
                if ($test == false) {
                    $this->handleError($name, Ethna_Const::E_FORM_WRONGTYPE_DATETIME);
                }
                return false;
            }
        } else if ($type == Ethna_Const::VAR_TYPE_BOOLEAN) {
            if ($var != "1" && $var != "0") {
                if ($test == false) {
                    $this->handleError($name, Ethna_Const::E_FORM_WRONGTYPE_BOOLEAN);
                }
                return false;
            }
        } else if ($type == Ethna_Const::VAR_TYPE_STRING) {
            // nothing to do
        } else if ($type == Ethna_Const::VAR_TYPE_FILE) {
            // nothing to do
        }

        // min
        if ($type == Ethna_Const::VAR_TYPE_INT) {
            if (!is_null($def['min']) && $var < $def['min']) {
                if ($test == false) {
                    $this->handleError($name, Ethna_Const::E_FORM_MIN_INT);
                }
                return false;
            }
        } else if ($type == Ethna_Const::VAR_TYPE_FLOAT) {
            if (!is_null($def['min']) && $var < $def['min']) {
                if ($test == false) {
                    $this->handleError($name, Ethna_Const::E_FORM_MIN_FLOAT);
                }
                return false;
            }
        } else if ($type == Ethna_Const::VAR_TYPE_DATETIME) {
            if (!is_null($def['min'])) {
                $t_min = strtotime($def['min']);
                $t_var = strtotime($var);
                if ($t_var < $t_min) {
                    if ($test == false) {
                        $this->handleError($name, Ethna_Const::E_FORM_MIN_DATETIME);
                    }
                }
                return false;
            }
        } else if ($type == Ethna_Const::VAR_TYPE_FILE) {
            if (!is_null($def['min'])) {
                $st = @stat($var['tmp_name']);
                if ($st[7] < $def['min'] * 1024) {
                    if ($test == false) {
                        $this->handleError($name, Ethna_Const::E_FORM_MIN_FILE);
                    }
                    return false;
                }
            }
        } else {
            if (!is_null($def['min']) && strlen($var) < $def['min']) {
                if ($test == false) {
                    $this->handleError($name, Ethna_Const::E_FORM_MIN_STRING);
                }
                return false;
            }
        }

        // max
        if ($type == Ethna_Const::VAR_TYPE_INT) {
            if (!is_null($def['max']) && $var > $def['max']) {
                if ($test == false) {
                    $this->handleError($name, Ethna_Const::E_FORM_MAX_INT);
                }
                return false;
            }
        } else if ($type == Ethna_Const::VAR_TYPE_FLOAT) {
            if (!is_null($def['max']) && $var > $def['max']) {
                if ($test == false) {
                    $this->handleError($name, Ethna_Const::E_FORM_MAX_FLOAT);
                }
                return false;
            }
        } else if ($type == Ethna_Const::VAR_TYPE_DATETIME) {
            if (!is_null($def['max'])) {
                $t_min = strtotime($def['max']);
                $t_var = strtotime($var);
                if ($t_var > $t_min) {
                    if ($test == false) {
                        $this->handleError($name, Ethna_Const::E_FORM_MAX_DATETIME);
                    }
                }
                return false;
            }
        } else if ($type == Ethna_Const::VAR_TYPE_FILE) {
            if (!is_null($def['max'])) {
                $st = @stat($var['tmp_name']);
                if ($st[7] > $def['max'] * 1024) {
                    if ($test == false) {
                        $this->handleError($name, Ethna_Const::E_FORM_MAX_FILE);
                    }
                    return false;
                }
            }
        } else {
            if (!is_null($def['max']) && strlen($var) > $def['max']) {
                if ($test == false) {
                    $this->handleError($name, Ethna_Const::E_FORM_MAX_STRING);
                }
                return false;
            }
        }

        // regexp
        if ($type != Ethna_Const::VAR_TYPE_FILE && $def['regexp'] != null && strlen($var) > 0
            && preg_match($def['regexp'], $var) == 0) {
            if ($test == false) {
                $this->handleError($name, Ethna_Const::E_FORM_REGEXP);
            }
            return false;
        }

        // custom (TODO: respect $test flag)
        if ($def['custom'] != null) {
            if (isset($this->form[$name]['type']) && is_array($this->form[$name]['type']) == false) {
                $this->_validateCustom($def['custom'], $name);
            } else {
                // �������ξ��������ǰ��ǥ�������᥽�åɤ�¹Ԥ��뤿�᥹���å�
            }
        }

        return true;
    }

    /**
     *  ������������å��᥽�åɤ�¹Ԥ���
     *
     *  @access protected
     *  @param  string  $method_list    ��������᥽�å�̾(����޶��ڤ�)
     *  @param  string  $name           �ե��������̾
     */
    function _validateCustom($method_list, $name)
    {
        $method_list = preg_split('/\s*,\s*/', $method_list, -1, PREG_SPLIT_NO_EMPTY);
        if (is_array($method_list) == false) {
            return;
        }
        foreach ($method_list as $method) {
            $this->$method($name);
        }
    }

    /**
     *  �ե������ͤ��Ѵ��ե��륿��Ŭ�Ѥ���
     *
     *  @access private
     *  @param  mixed   $value  �ե�������
     *  @param  int     $filter �ե��륿���
     *  @return mixed   �Ѵ����
     */
    function _filter($value, $filter)
    {
        if (is_null($filter)) {
            return $value;
        }

        foreach (preg_split('/\s*,\s*/', $filter) as $f) {
            $method = sprintf('_filter_%s', $f);
            if (method_exists($this, $method) == false) {
                $this->logger->log(LOG_WARNING, 'filter method is not defined [%s]', $method);
                continue;
            }
            $value = $this->$method($value);
        }

        return $value;
    }

    /**
     *  �ե��������Ѵ��ե��륿: ���ѱѿ���->Ⱦ�ѱѿ���
     *
     *  @access protected
     *  @param  mixed   $value  �ե�������
     *  @return mixed   �Ѵ����
     */
    function _filter_alnum_zentohan($value)
    {
        return mb_convert_kana($value, "a");
    }

    /**
     *  �ե��������Ѵ��ե��륿: ���ѿ���->Ⱦ�ѿ���
     *
     *  @access protected
     *  @param  mixed   $value  �ե�������
     *  @return mixed   �Ѵ����
     */
    function _filter_numeric_zentohan($value)
    {
        return mb_convert_kana($value, "n");
    }

    /**
     *  �ե��������Ѵ��ե��륿: ���ѱѻ�->Ⱦ�ѱѻ�
     *
     *  @access protected
     *  @param  mixed   $value  �ե�������
     *  @return mixed   �Ѵ����
     */
    function _filter_alphabet_zentohan($value)
    {
        return mb_convert_kana($value, "r");
    }

    /**
     *  �ե��������Ѵ��ե��륿: ��������
     *
     *  @access protected
     *  @param  mixed   $value  �ե�������
     *  @return mixed   �Ѵ����
     */
    function _filter_ltrim($value)
    {
        return ltrim($value);
    }

    /**
     *  �ե��������Ѵ��ե��륿: ��������
     *
     *  @access protected
     *  @param  mixed   $value  �ե�������
     *  @return mixed   �Ѵ����
     */
    function _filter_rtrim($value)
    {
        return rtrim($value);
    }

    /**
     *  �ե��������Ѵ��ե��륿: NULL(0x00)���
     *
     *  @access protected
     *  @param  mixed   $value  �ե�������
     *  @return mixed   �Ѵ����
     */
    function _filter_ntrim($value)
    {
        return str_replace("\x00", "", $value);
    }

    /**
     *  �ե��������Ѵ��ե��륿: Ⱦ�ѥ���->���ѥ���
     *
     *  @access protected
     *  @param  mixed   $value  �ե�������
     *  @return mixed   �Ѵ����
     */
    function _filter_kana_hantozen($value)
    {
        return mb_convert_kana($value, "K");
    }

    /**
     *  �ե�����������ƥ�ץ졼�Ȥ����ꤹ��
     *
     *  @access protected
     *  @param  array   $form_template  �ե������ͥƥ�ץ졼��
     *  @return array   �ե������ͥƥ�ץ졼��
     */
    function _setFormTemplate($form_template)
    {
        return $form_template;
    }

    /**
     *  �ե���������������ꤹ��
     *
     *  @access protected
     */
    function _setFormDef()
    {
        foreach ($this->form as $key => $value) {
            if (array_key_exists($key, $this->form_template) && is_array($this->form_template)) {
                foreach ($this->form_template[$key] as $def_key => $def_value) {
                    if (array_key_exists($def_key, $value) == false) {
                        $this->form[$key][$def_key] = $def_value;
                    }
                }
            }
        }
    }
}
// }}}
?>