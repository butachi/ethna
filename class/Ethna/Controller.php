<?php
/**
 *  Ethna_Controller.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */
/**
 *  コントローラクラス
 *
 *  @todo       gatewayでswitchしてるところがダサダサ
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Controller
{
    /** @var    string      アプリケーションID */
    protected $appid = 'ETHNA';

    /** @var    string      アプリケーションベースディレクトリ */
    protected $base = '';

    /** @var    string      アプリケーションベースURL */
    protected $url = '';

    /** @var    array       アプリケーションディレクトリ */
    protected $directory = array(
        'action'        => 'app/action',
        'action_cli'    => 'app/action_cli',
        'app'           => 'app',
        'bin'           => 'bin',
        'etc'           => 'etc',
        'filter'        => 'app/filter',
        'locale'        => 'locale',
        'plugins'       => array(),
        'template'      => 'template',
        'template_c'    => 'tmp',
        'tmp'           => 'tmp',
        'view'          => 'app/view',
    );

    /** @var    array       拡張子設定 */
    protected $ext = array(
        'php'           => 'php',
        'tpl'           => 'tpl',
    );

    /** @var    array       クラス設定 */
    protected $class = array(
        'class'         => 'Ethna_ClassFactory',
        'backend'       => 'Ethna_Backend',
        'config'        => 'Ethna_Config',
        'error'         => 'Ethna_ActionError',
        'form'          => 'Ethna_ActionForm',
        'session'       => 'Ethna_Session',
        'sql'           => 'Ethna_AppSQL',
        'view'          => 'Ethna_ViewClass',
    );

    /** @var    array       フィルタ設定 */
    protected $filter = array(
    );

    /** @var    string      使用言語設定 */
    protected $language;

    /** @var    string      システム側エンコーディング */
    protected $system_encoding;

    /** @var    string      クライアント側エンコーディング */
    protected $client_encoding;

    /** @var    string  現在実行中のアクション名 */
    protected $action_name;

    /** @var    array   forward定義 */
    protected $forward = array();

    /** @var    array   action定義 */
    protected $action = array();

    /** @var    array   action(CLI)定義 */
    protected $action_cli = array();

    /** @var    array   アプリケーションマネージャ定義 */
    protected $manager = array();

    /** @var    array   フィルターチェイン(Ethna_Filterオブジェクトの配列) */
    protected $filter_chain = array();

    /** @var    object  Ethna_ClassFactory  クラスファクトリオブジェクト */
    protected $class_factory = null;

    /** @var    object  Ethna_ActionForm    フォームオブジェクト */
    protected $action_form = null;

    /** @var    object  Ethna_View          ビューオブジェクト */
    protected $view = null;

    /** @var    object  Ethna_Config        設定オブジェクト */
    protected $config = null;

    /** @var    string  リクエストのゲートウェイ(www/cli/rest/...) */
    protected $gateway = Ethna_Const::GATEWAY_WWW;

    /**#@-*/
    /**
     *  Ethna_Controllerクラスのコンストラクタ
     *
     *  @access     public
     */
    public function __construct($gateway = Ethna_Const::GATEWAY_WWW)
    {
        $GLOBALS['_Ethna_controller'] = $this;
        if ($this->base == "") {
            $this->base = BASE;
        }

        $this->gateway = $gateway;

        // クラスファクトリオブジェクトの生成
        $class_factory = $this->class['class'];
        $this->class_factory = new $class_factory($this, $this->class);

        // エラーハンドラの設定
        Ethna::setErrorCallback(array(&$this, 'handleError'));

        // ディレクトリ名の設定(相対パス->絶対パス)
        foreach ($this->directory as $key => $value) {
            if ($key == 'plugins') {
                // Smartyプラグインディレクトリは配列で指定する
                $tmp = array(SMARTY_DIR . 'plugins');
                foreach (to_array($value) as $elt) {
                    if (Ethna_Util::isAbsolute($elt) == false) {
                        $tmp[] = $this->base . (empty($this->base) ? '' : '/') . $elt;
                    }
                }
                $this->directory[$key] = $tmp;
            } else {
                if (Ethna_Util::isAbsolute($value) == false) {
                    $this->directory[$key] = $this->base . (empty($this->base) ? '' : '/') . $value;
                }
            }
        }

        // 初期設定
        list($this->language, $this->system_encoding, $this->client_encoding) = $this->_getDefaultLanguage();

        $this->config = $this->getConfig();
        $this->url = $this->config->get('url');
    }

    /**
     *  (現在アクティブな)コントローラのインスタンスを返す
     *
     *  @access public
     *  @return object  Ethna_Controller    コントローラのインスタンス
     *  @static
     */
    function getInstance()
    {
        if (isset($GLOBALS['_Ethna_controller'])) {
            return $GLOBALS['_Ethna_controller'];
        } else {
            $_ret_object = null;
            return $_ret_object;
        }
    }

    /**
     *  アプリケーションIDを返す
     *
     *  @access public
     *  @return string  アプリケーションID
     */
    function getAppId()
    {
        return ucfirst(strtolower($this->appid));
    }

    /**
     *  アプリケーションIDをチェックする
     *
     *  @access public
     *  @param  string  $id     アプリケーションID
     *  @return mixed   true:OK Ethna_Error:NG
     */
    function checkAppId($id)
    {
        if (strcasecmp($id, 'ethna') == 0) {
            return Ethna::raiseError(sprintf("Application Id [%s] is reserved\n", $id));
        }
        if (preg_match('/^[0-9a-zA-Z]+$/', $id) == 0) {
            return Ethna::raiseError(sprintf("Only Numeric(0-9) and Alphabetical(A-Z) is allowed for Application Id\n"));
        }

        return true;
    }

    /**
     *  アプリケーションベースURLを返す
     *
     *  @access public
     *  @return string  アプリケーションベースURL
     */
    function getURL()
    {
        return $this->url;
    }

    /**
     *  アプリケーションベースディレクトリを返す
     *
     *  @access public
     *  @return string  アプリケーションベースディレクトリ
     */
    function getBasedir()
    {
        return $this->base;
    }
    /**
     *  アクションディレクトリ名を決定する
     *
     *  @access public
     *  @return string  アクションディレクトリ
     */
    function getActiondir($gateway = null)
    {
        $key = 'action';
        $gateway = is_null($gateway) ? $this->getGateway() : $gateway;
        switch ($gateway) {
        case Ethna_Const::GATEWAY_WWW:
            $key = 'action';
            break;
        case Ethna_Const::GATEWAY_CLI:
            $key = 'action_cli';
            break;
        }

        return (empty($this->directory[$key]) ? ($this->base . (empty($this->base) ? '' : '/')) : ($this->directory[$key] . "/"));
    }

    /**
     *  ビューディレクトリ名を決定する
     *
     *  @access public
     *  @return string  アクションディレクトリ
     */
    function getViewdir()
    {
        return (empty($this->directory['view']) ? ($this->base . (empty($this->base) ? '' : '/')) : ($this->directory['view'] . "/"));
    }

    /**
     *  アプリケーションディレクトリ設定を返す
     *
     *  @access public
     *  @param  string  $key    ディレクトリタイプ("tmp", "template"...)
     *  @return string  $keyに対応したアプリケーションディレクトリ(設定が無い場合はnull)
     */
    public function getDirectory($key)
    {
        // for B.C.
        if ($key == 'app' && isset($this->directory[$key]) == false) {
            return BASE . '/app';
        }

        if (isset($this->directory[$key]) == false) {
            return null;
        }
        return $this->directory[$key];
    }

    /**
     *  アプリケーション拡張子設定を返す
     *
     *  @access public
     *  @param  string  $key    拡張子タイプ("php", "tpl"...)
     *  @return string  $keyに対応した拡張子(設定が無い場合はnull)
     */
    function getExt($key)
    {
        if (isset($this->ext[$key]) == false) {
            return null;
        }
        return $this->ext[$key];
    }

    /**
     *  クラスファクトリオブジェクトのアクセサ(R)
     *
     *  @access public
     *  @return object  Ethna_ClassFactory  クラスファクトリオブジェクト
     */
    function getClassFactory()
    {
        return $this->class_factory;
    }

    /**
     *  アクションエラーオブジェクトのアクセサ
     *
     *  @access public
     *  @return object  Ethna_ActionError   アクションエラーオブジェクト
     */
    function getActionError()
    {
        return $this->class_factory->getObject('error');
    }

    /**
     *  アクションフォームオブジェクトのアクセサ
     *
     *  @access public
     *  @return object  Ethna_ActionForm    アクションフォームオブジェクト
     */
    function getActionForm()
    {
        // 明示的にクラスファクトリを利用していない
        return $this->action_form;
    }

    /**
     *  ビューオブジェクトのアクセサ
     *
     *  @access public
     *  @return object  Ethna_View          ビューオブジェクト
     */
    function getView()
    {
        // 明示的にクラスファクトリを利用していない
        return $this->view;
    }

    /**
     *  backendオブジェクトのアクセサ
     *
     *  @access public
     *  @return object  Ethna_Backend   backendオブジェクト
     */
    function getBackend()
    {
        return $this->class_factory->getObject('backend');
    }

    /**
     *  設定オブジェクトのアクセサ
     *
     *  @access public
     *  @return object  Ethna_Config    設定オブジェクト
     */
    function getConfig()
    {
        return $this->class_factory->getObject('config');
    }

    /**
     *  セッションオブジェクトのアクセサ
     *
     *  @access public
     *  @return object  Ethna_Session       セッションオブジェクト
     */
    function getSession()
    {
        return $this->class_factory->getObject('session');
    }

    /**
     *  SQLオブジェクトのアクセサ
     *
     *  @access public
     *  @return object  Ethna_AppSQL    SQLオブジェクト
     */
    function getSQL()
    {
        return $this->class_factory->getObject('sql');
    }

    /**
     *  マネージャ一覧を返す
     *
     *  @access public
     *  @return array   マネージャ一覧
     */
    function getManagerList()
    {
        return $this->manager;
    }

    /**
     *  実行中のアクション名を返す
     *
     *  @access public
     *  @return string  実行中のアクション名
     */
    function getCurrentActionName()
    {
        return $this->action_name;
    }

    /**
     *  使用言語を取得する
     *
     *  @access public
     *  @return array   使用言語,システムエンコーディング名,クライアントエンコーディング名
     */
    function getLanguage()
    {
        return array($this->language, $this->system_encoding, $this->client_encoding);
    }

    /**
     *  ゲートウェイを取得する
     *
     *  @access public
     */
    function getGateway()
    {
        return $this->gateway;
    }

    /**
     *  ゲートウェイモードを設定する
     *
     *  @access public
     */
    function setGateway($gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     *  アプリケーションのエントリポイント
     *
     *  @access public
     *  @param  string  $class_name     アプリケーションコントローラのクラス名
     *  @param  mixed   $action_name    指定のアクション名(省略可)
     *  @param  mixed   $fallback_action_name   アクションが決定できなかった場合に実行されるアクション名(省略可)
     *  @static
     */
    public function main($class_name, $action_name = "", $fallback_action_name = "")
    {
        $c = new $class_name;
        $c->trigger($action_name, $fallback_action_name);
    }

    /**
     *  CLIアプリケーションのエントリポイント
     *
     *  @access public
     *  @param  string  $class_name     アプリケーションコントローラのクラス名
     *  @param  string  $action_name    実行するアクション名
     *  @param  bool    $enable_filter  フィルタチェインを有効にするかどうか
     *  @static
     */
    function main_CLI($class_name, $action_name, $enable_filter = true)
    {
        $c = new $class_name(Ethna_Const::GATEWAY_CLI);
        $c->action_cli[$action_name] = array();
        $c->trigger($action_name, "", $enable_filter);
    }

    /**
     *  フレームワークの処理を開始する
     *
     *  @access public
     *  @param  mixed   $default_action_name    指定のアクション名
     *  @param  mixed   $fallback_action_name   アクション名が決定できなかった場合に実行されるアクション名
     *  @param  bool    $enable_filter  フィルタチェインを有効にするかどうか
     *  @return mixed   0:正常終了 Ethna_Error:エラー
     */
    function trigger($default_action_name = "", $fallback_action_name = "", $enable_filter = true)
    {
        // フィルターの生成
        if ($enable_filter) {
            $this->_createFilterChain();
        }

        // 実行前フィルタ
        for ($i = 0; $i < count($this->filter_chain); $i++) {
            $r = $this->filter_chain[$i]->preFilter();
            if (Ethna::isError($r)) {
                return $r;
            }
        }

        // trigger
        switch ($this->getGateway()) {
        case Ethna_Const::GATEWAY_WWW:
            $this->_trigger_WWW($default_action_name, $fallback_action_name);
            break;
        case Ethna_Const::GATEWAY_CLI:
            $this->_trigger_CLI($default_action_name);
            break;
        }

        // 実行後フィルタ
        for ($i = count($this->filter_chain) - 1; $i >= 0; $i--) {
            $r = $this->filter_chain[$i]->postFilter();
            if (Ethna::isError($r)) {
                return $r;
            }
        }
    }

    /**
     *  フレームワークの処理を実行する(WWW)
     *
     *  引数$default_action_nameに配列が指定された場合、その配列で指定された
     *  アクション以外は受け付けない(指定されていないアクションが指定された
     *  場合、配列の先頭で指定されたアクションが実行される)
     *
     *  @access private
     *  @param  mixed   $default_action_name    指定のアクション名
     *  @param  mixed   $fallback_action_name   アクション名が決定できなかった場合に実行されるアクション名
     *  @return mixed   0:正常終了 Ethna_Error:エラー
     */
    function _trigger_WWW($default_action_name = "", $fallback_action_name = "")
    {
        // アクション名の取得
        $action_name = $this->_getActionName($default_action_name, $fallback_action_name);

        // アクション定義の取得
        $action_obj = $this->_getAction($action_name);
        if (is_null($action_obj)) {
            if ($fallback_action_name != "") {
                $action_obj = $this->_getAction($fallback_action_name);
            }
            if (is_null($action_obj)) {
                return Ethna::raiseError("undefined action [%s]", Ethna_Const::E_APP_UNDEFINED_ACTION, $action_name);
            } else {
                $action_name = $fallback_action_name;
            }
        }

        // アクション実行前フィルタ
        for ($i = 0; $i < count($this->filter_chain); $i++) {
            $r = $this->filter_chain[$i]->preActionFilter($action_name);
            if ($r != null) {
                $action_name = $r;
            }
        }
        $this->action_name = $action_name;

        // 言語設定
        $this->_setLanguage($this->language, $this->system_encoding, $this->client_encoding);

        // オブジェクト生成
        $form_name = $this->getActionFormName($action_name);
        $this->action_form = new $form_name($this);
        $this->action_form->setFormVars();

        // バックエンド処理実行
        $backend = $this->getBackend();
        $backend->setActionForm($this->action_form);

        $session = $this->getSession();
        $session->restore();
        $forward_name = $backend->perform($action_name);

        // アクション実行後フィルタ
        for ($i = count($this->filter_chain) - 1; $i >= 0; $i--) {
            $r = $this->filter_chain[$i]->postActionFilter($action_name, $forward_name);
            if ($r != null) {
                $forward_name = $r;
            }
        }

        // コントローラで遷移先を決定する(オプション)
        $forward_name = $this->_sortForward($action_name, $forward_name);

        if ($forward_name != null) {
            $view_class_name = $this->getViewClassName($forward_name);
            $this->view = new $view_class_name($backend, $forward_name, $this->_getForwardPath($forward_name));
            $this->view->preforward();
            $this->view->forward();
        }

        return 0;
    }

    /**
     *  フレームワークの処理を実行する(CLI)
     *
     *  @access private
     *  @param  mixed   $default_action_name    指定のアクション名
     *  @return mixed   0:正常終了 Ethna_Error:エラー
     */
    function _trigger_CLI($default_action_name = "")
    {
        return $this->_trigger_WWW($default_action_name);
    }

    /**
     *  エラーハンドラ
     *
     *  エラー発生時の追加処理を行いたい場合はこのメソッドをオーバーライドする
     *  (アラートメール送信等−デフォルトではログ出力時にアラートメール
     *  が送信されるが、エラー発生時に別にアラートメールをここで送信
     *  させることも可能)
     *
     *  @access public
     *  @param  object  Ethna_Error     エラーオブジェクト
     */
    function handleError(&$error)
    {
        // ログ出力
        $message = $error->getMessage();


        // @todo and will be removed after supporting exception.
        die($message);
    }

    /**
     *  エラーメッセージを取得する
     *
     *  @access public
     *  @param  int     $code       エラーコード
     *  @return string  エラーメッセージ
     */
    function getErrorMessage($code)
    {
        $message_list = $GLOBALS['_Ethna_error_message_list'];
        for ($i = count($message_list)-1; $i >= 0; $i--) {
            if (array_key_exists($code, $message_list[$i])) {
                return $message_list[$i][$code];
            }
        }
        return null;
    }

    /**
     *  実行するアクション名を返す
     *
     *  @access private
     *  @param  mixed   $default_action_name    指定のアクション名
     *  @return string  実行するアクション名
     */
    function _getActionName($default_action_name, $fallback_action_name)
    {
        // フォームから要求されたアクション名を取得する
        $form_action_name = $this->_getActionName_Form();
        $form_action_name = preg_replace('/[^a-z0-9\-_]+/i', '', $form_action_name);

        // Ethnaマネージャへのフォームからのリクエストは拒否
        if ($form_action_name == "__ethna_info__" ||
            $form_action_name == "__ethna_unittest__") {
            $form_action_name = "";
        }

        // フォームからの指定が無い場合はエントリポイントに指定されたデフォルト値を利用する
        if ($form_action_name == "" && count($default_action_name) > 0) {
            $tmp = is_array($default_action_name) ? $default_action_name[0] : $default_action_name;
            if ($tmp{strlen($tmp)-1} == '*') {
                $tmp = substr($tmp, 0, -1);
            }
            $action_name = $tmp;
        } else {
            $action_name = $form_action_name;
        }

        // エントリポイントに配列が指定されている場合は指定以外のアクション名は拒否する
        if (is_array($default_action_name)) {
            if ($this->_isAcceptableActionName($action_name, $default_action_name) == false) {
                // 指定以外のアクション名で合った場合は$fallback_action_name(or デフォルト)
                $tmp = $fallback_action_name != "" ? $fallback_action_name : $default_action_name[0];
                if ($tmp{strlen($tmp)-1} == '*') {
                    $tmp = substr($tmp, 0, -1);
                }
                $action_name = $tmp;
            }
        }

        return $action_name;
    }

    /**
     *  フォームにより要求されたアクション名を返す
     *
     *  アプリケーションの性質に応じてこのメソッドをオーバーライドして下さい。
     *  デフォルトでは"action_"で始まるフォーム値の"action_"の部分を除いたもの
     *  ("action_sample"なら"sample")がアクション名として扱われます
     *
     *  @access protected
     *  @return string  フォームにより要求されたアクション名
     */
    protected function _getActionName_Form()
    {
        if (isset($_SERVER['REQUEST_METHOD']) == false) {
            return null;
        }

        if (strcasecmp($_SERVER['REQUEST_METHOD'], 'post') == 0) {
            $http_vars = $_POST;
        } else {
            $http_vars = $_GET;
        }

        // フォーム値からリクエストされたアクション名を取得する
        $action_name = $sub_action_name = null;
        foreach ($http_vars as $name => $value) {
            if ($value == "" || strncmp($name, 'action_', 7) != 0) {
                continue;
            }

            $tmp = substr($name, 7);

            // type="image"対応
            if (preg_match('/_x$/', $name) || preg_match('/_y$/', $name)) {
                $tmp = substr($tmp, 0, strlen($tmp)-2);
            }

            // value="dummy"となっているものは優先度を下げる
            if ($value == "dummy") {
                $sub_action_name = $tmp;
            } else {
                $action_name = $tmp;
            }
        }
        if ($action_name == null) {
            $action_name = $sub_action_name;
        }

        return $action_name;
    }

    /**
     *  アクション名を指定するクエリ/HTMLを生成する
     *
     *  @access public
     *  @param  string  $action action to request
     *  @param  string  $type   hidden, url...
     *  @todo   consider gateway
     */
    function getActionRequest($action, $type = "hidden")
    {
        $s = null; 
        if ($type == "hidden") {
            $s = sprintf('<input type="hidden" name="action_%s" value="true">', htmlspecialchars($action, ENT_QUOTES));
        } else if ($type == "url") {
            $s = sprintf('action_%s=true', urlencode($action));
        }
        return $s;
    }

    /**
     *  フォームにより要求されたアクション名に対応する定義を返す
     *
     *  @access private
     *  @param  string  $action_name    アクション名
     *  @return array   アクション定義
     */
    function _getAction($action_name, $gateway = null)
    {
        $action = array();
        $gateway = is_null($gateway) ? $this->getGateway() : $gateway;
        switch ($gateway) {
        case Ethna_Const::GATEWAY_WWW:
            $action = $this->action;
            break;
        case Ethna_Const::GATEWAY_CLI:
            $action = $this->action_cli;
            break;
        }

        $action_obj = array();
        if (isset($action[$action_name])) {
            $action_obj = $action[$action_name];
            if (isset($action_obj['inspect']) && $action_obj['inspect']) {
                return $action_obj;
            }
        }

        // アクションスクリプトのインクルード
        $this->_includeActionScript($action_obj, $action_name);

        // 省略値の補正
        if (isset($action_obj['class_name']) == false) {
            $action_obj['class_name'] = $this->getDefaultActionClass($action_name);
        }

        if (isset($action_obj['form_name']) == false) {
            $action_obj['form_name'] = $this->getDefaultFormClass($action_name);
        } 

        // 必要条件の確認
        if (class_exists($action_obj['class_name']) == false) {
            $_ret_object = null;
            return $_ret_object;
        }
        if (class_exists($action_obj['form_name']) == false) {
            // フォームクラスは未定義でも良い
            $class_name = $this->class_factory->getObjectName('form');
            $action_obj['form_name'] = $class_name;
        }

        $action_obj['inspect'] = true;
        $action[$action_name] = $action_obj;
        return $action[$action_name];
    }

    /**
     *  アクション名とアクションクラスからの戻り値に基づいて遷移先を決定する
     *
     *  @access protected
     *  @param  string  $action_name    アクション名
     *  @param  string  $retval         アクションクラスからの戻り値
     *  @return string  遷移先
     */
    function _sortForward($action_name, $retval)
    {
        return $retval;
    }

    /**
     *  フィルタチェインを生成する
     *
     *  @access private
     */
    function _createFilterChain()
    {
        $this->filter_chain = array();
        foreach ($this->filter as $filter) {
            $file = sprintf("%s/%s.%s", $this->getDirectory('filter'), $filter, $this->getExt('php'));
            if (file_exists($file)) {
                include_once($file);
            }
            if (class_exists($filter)) {
                $this->filter_chain[] = new $filter($this);
            }
        }
    }

    /**
     *  アクション名が実行許可されているものかどうかを返す
     *
     *  @access private
     *  @param  string  $action_name            リクエストされたアクション名
     *  @param  array   $default_action_name    許可されているアクション名
     *  @return bool    true:許可 false:不許可
     */
    function _isAcceptableActionName($action_name, $default_action_name)
    {
        foreach (to_array($default_action_name) as $name) {
            if ($action_name == $name) {
                return true;
            } else if ($name{strlen($name)-1} == '*') {
                if (strncmp($action_name, substr($name, 0, -1), strlen($name)-1) == 0) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     *  指定されたアクションのフォームクラス名を返す(オブジェクトの生成は行わない)
     *
     *  @access public
     *  @param  string  $action_name    アクション名
     *  @return string  アクションのフォームクラス名
     */
    function getActionFormName($action_name)
    {
        $action_obj = $this->_getAction($action_name);
        if (is_null($action_obj)) {
            return null;
        }

        return $action_obj['form_name'];
    }

    /**
     *  アクションに対応するフォームクラス名が省略された場合のデフォルトクラス名を返す
     *
     *  デフォルトでは[プロジェクトID]_Form_[アクション名]となるので好み応じてオーバライドする
     *
     *  @access public
     *  @param  string  $action_name    アクション名
     *  @return string  アクションフォーム名
     */
    function getDefaultFormClass($action_name, $gateway = null)
    {
        $gateway_prefix = $this->_getGatewayPrefix($gateway);

        $postfix = preg_replace('/_(.)/e', "strtoupper('\$1')", ucfirst($action_name));
        $r = sprintf("%s_%sForm_%s", $this->getAppId(), $gateway_prefix ? $gateway_prefix . "_" : "", $postfix);

        return $r;
    }

    /**
     *  getDefaultFormClass()で取得したクラス名からアクション名を取得する
     *
     *  getDefaultFormClass()をオーバーライドした場合、こちらも合わせてオーバーライド
     *  することを推奨(必須ではない)
     *
     *  @access public
     *  @param  string  $class_name     フォームクラス名
     *  @return string  アクション名
     */
    function actionFormToName($class_name)
    {
        $prefix = sprintf("%s_Form_", $this->getAppId());
        if (preg_match("/$prefix(.*)/", $class_name, $match) == 0) {
            // 不明なクラス名
            return null;
        }
        $target = $match[1];

        $action_name = substr(preg_replace('/([A-Z])/e', "'_' . strtolower('\$1')", $target), 1);

        return $action_name;
    }

    /**
     *  アクションに対応するフォームパス名が省略された場合のデフォルトパス名を返す
     *
     *  デフォルトでは_getDefaultActionPath()と同じ結果を返す(1ファイルに
     *  アクションクラスとフォームクラスが記述される)ので、好みに応じて
     *  オーバーライドする
     *
     *  @access public
     *  @param  string  $action_name    アクション名
     *  @return string  form classが定義されるスクリプトのパス名
     */
    function getDefaultFormPath($action_name)
    {
        return $this->getDefaultActionPath($action_name);
    }

    /**
     *  指定されたアクションのクラス名を返す(オブジェクトの生成は行わない)
     *
     *  @access public
     *  @param  string  $action_name    アクションの名称
     *  @return string  アクションのクラス名
     */
    function getActionClassName($action_name)
    {
        $action_obj = $this->_getAction($action_name);
        if ($action_obj == null) {
            return null;
        }

        return $action_obj['class_name'];
    }

    /**
     *  アクションに対応するアクションクラス名が省略された場合のデフォルトクラス名を返す
     *
     *  デフォルトでは[プロジェクトID]_Action_[アクション名]となるので好み応じてオーバライドする
     *
     *  @access public
     *  @param  string  $action_name    アクション名
     *  @return string  アクションクラス名
     */
    function getDefaultActionClass($action_name, $gateway = null)
    {
        $gateway_prefix = $this->_getGatewayPrefix($gateway);

        $postfix = preg_replace('/_(.)/e', "strtoupper('\$1')", ucfirst($action_name));
        $r = sprintf("%s_%sAction_%s", $this->getAppId(), $gateway_prefix ? $gateway_prefix . "_" : "", $postfix);

        return $r;
    }

    /**
     *  getDefaultActionClass()で取得したクラス名からアクション名を取得する
     *
     *  getDefaultActionClass()をオーバーライドした場合、こちらも合わせてオーバーライド
     *  することを推奨(必須ではない)
     *
     *  @access public
     *  @param  string  $class_name     アクションクラス名
     *  @return string  アクション名
     */
    function actionClassToName($class_name)
    {
        $prefix = sprintf("%s_Action_", $this->getAppId());
        if (preg_match("/$prefix(.*)/", $class_name, $match) == 0) {
            // 不明なクラス名
            return null;
        }
        $target = $match[1];

        return substr(preg_replace('/([A-Z])/e', "'_' . strtolower('\$1')", $target), 1);
    }

    /**
     *  アクションに対応するアクションパス名が省略された場合のデフォルトパス名を返す
     *
     *  デフォルトでは"foo_bar" -> "/Foo/Bar.php"となるので好み応じてオーバーライドする
     *
     *  @access public
     *  @param  string  $action_name    アクション名
     *  @return string  アクションクラスが定義されるスクリプトのパス名
     */
    function getDefaultActionPath($action_name)
    {
        return preg_replace('/_(.)/e', "'/' . strtoupper('\$1')", ucfirst($action_name)) . '.' . $this->getExt('php');
    }

    /**
     *  指定された遷移名に対応するビュークラス名を返す(オブジェクトの生成は行わない)
     *
     *  @access public
     *  @param  string  $forward_name   遷移先の名称
     *  @return string  view classのクラス名
     */
    function getViewClassName($forward_name)
    {
        if ($forward_name == null) {
            return null;
        }

        if (isset($this->forward[$forward_name])) {
            $forward_obj = $this->forward[$forward_name];
        } else {
            $forward_obj = array();
        }

        if (isset($forward_obj['view_name'])) {
            $class_name = $forward_obj['view_name'];
            if (class_exists($class_name)) {
                return $class_name;
            }
        } else {
            $class_name = null;
        }

        // viewのインクルード
        $this->_includeViewScript($forward_obj, $forward_name);

        if (is_null($class_name) == false && class_exists($class_name)) {
            return $class_name;
        } else if (is_null($class_name) == false) {
        }

        $class_name = $this->getDefaultViewClass($forward_name);
        if (class_exists($class_name)) {
            return $class_name;
        } else {
            $class_name = $this->class_factory->getObjectName('view');
            return $class_name;
        }
    }

    /**
     *  遷移名に対応するビュークラス名が省略された場合のデフォルトクラス名を返す
     *
     *  デフォルトでは[プロジェクトID]_View_[遷移名]となるので好み応じてオーバライドする
     *
     *  @access public
     *  @param  string  $forward_name   forward名
     *  @return string  view classクラス名
     */
    function getDefaultViewClass($forward_name, $gateway = null)
    {
        $gateway_prefix = $this->_getGatewayPrefix($gateway);

        $postfix = preg_replace('/_(.)/e', "strtoupper('\$1')", ucfirst($forward_name));
        return  sprintf("%s_%sView_%s", $this->getAppId(), $gateway_prefix ? $gateway_prefix . "_" : "", $postfix);
    }

    /**
     *  遷移名に対応するビューパス名が省略された場合のデフォルトパス名を返す
     *
     *  デフォルトでは"foo_bar" -> "/Foo/Bar.php"となるので好み応じてオーバーライドする
     *
     *  @access public
     *  @param  string  $forward_name   forward名
     *  @return string  view classが定義されるスクリプトのパス名
     */
    function getDefaultViewPath($forward_name)
    {
        return preg_replace('/_(.)/e', "'/' . strtoupper('\$1')", ucfirst($forward_name)) . '.' . $this->getExt('php');
    }

    /**
     *  遷移名に対応するテンプレートパス名が省略された場合のデフォルトパス名を返す
     *
     *  デフォルトでは"foo_bar"というforward名が"foo/bar" + テンプレート拡張子となる
     *  ので好み応じてオーバライドする
     *
     *  @access public
     *  @param  string  $forward_name   forward名
     *  @return string  forwardパス名
     */
    function getDefaultForwardPath($forward_name)
    {
        return str_replace('_', '/', $forward_name) . '.' . $this->ext['tpl'];
    }

    /**
     *  テンプレートパス名から遷移名を取得する
     *
     *  getDefaultForwardPath()をオーバーライドした場合、こちらも合わせてオーバーライド
     *  することを推奨(必須ではない)
     *
     *  @access public
     *  @param  string  $forward_path   テンプレートパス名
     *  @return string  遷移名
     */
    function forwardPathToName($forward_path)
    {
        $forward_path = preg_replace('/^\/+/', '', $forward_path);
        $forward_path = preg_replace(sprintf('/\.%s$/', $this->getExt('tpl')), '', $forward_path);

        return str_replace('/', '_', $forward_path);
    }

    /**
     *  遷移名からテンプレートファイルのパス名を取得する
     *
     *  @access private
     *  @param  string  $forward_name   forward名
     *  @return string  テンプレートファイルのパス名
     */
    function _getForwardPath($forward_name)
    {
        $forward_obj = null;

        if (isset($this->forward[$forward_name]) == false) {
            // try default
            $this->forward[$forward_name] = array();
        }
        $forward_obj = $this->forward[$forward_name];
        if (isset($forward_obj['forward_path']) == false) {
            // 省略値補正
            $forward_obj['forward_path'] = $this->getDefaultForwardPath($forward_name);
        }

        return $forward_obj['forward_path'];
    }

    /**
     *  テンプレートエンジン取得する(現在はsmartyのみ対応)
     *
     *  @access public
     *  @return object  Smarty  テンプレートエンジンオブジェクト
     */
    public function getTemplateEngine()
    {
        $smarty = new Smarty();
        $smarty->template_dir = $this->getDirectory('template');
        $smarty->compile_dir = $this->getDirectory('template_c');
        $smarty->compile_id = md5($smarty->template_dir);

        // 一応がんばってみる
        if (!is_dir($smarty->compile_dir)) {
            mkdir($smarty->compile_dir, 0755);
        }

        return $smarty;
    }
    /**
     *  使用言語を設定する
     *
     *  将来への拡張のためのみに存在しています。現在は特にオーバーライドの必要はありません。
     *
     *  @access protected
     *  @param  string  $language           言語定義(Ethna_Const::LANG_JA, Ethna_Const::LANG_EN...)
     *  @param  string  $system_encoding    システムエンコーディング名
     *  @param  string  $client_encoding    クライアントエンコーディング
     */
    function _setLanguage($language, $system_encoding = null, $client_encoding = null)
    {
        $this->language = $language;
        $this->system_encoding = $system_encoding;
        $this->client_encoding = $client_encoding;
    }

    /**
     *  デフォルト状態での使用言語を取得する
     *
     *  @access protected
     *  @return array   使用言語,システムエンコーディング名,クライアントエンコーディング名
     */
    public function _getDefaultLanguage()
    {
        return array(Ethna_Const::LANG_JA, null, null);
    }

    /**
     *  デフォルト状態でのゲートウェイを取得する
     *
     *  @access protected
     *  @return int     ゲートウェイ定義(Ethna_Const::GATEWAY_WWW, Ethna_Const::GATEWAY_CLI...)
     */
    function _getDefaultGateway($gateway)
    {
        if (is_null($GLOBALS['_Ethna_gateway']) == false) {
            return $GLOBALS['_Ethna_gateway'];
        }
        return Ethna_Const::GATEWAY_WWW;
    }

    /**
     *  ゲートウェイに対応したクラス名のプレフィクスを取得する
     *
     *  @access public
     *  @param  string  $gateway    ゲートウェイ
     *  @return string  ゲートウェイクラスプレフィクス
     */
    function _getGatewayPrefix($gateway = null)
    {
        $gateway = is_null($gateway) ? $this->getGateway() : $gateway;
        switch ($gateway) {
        case Ethna_Const::GATEWAY_WWW:
            $prefix = '';
            break;
        case Ethna_Const::GATEWAY_CLI:
            $prefix = 'Cli';
            break;
        default:
            $prefix = '';
            break;
        }

        return $prefix;
    }

    /**
     *  マネージャクラス名を取得する
     *
     *  @access public
     *  @param  string  $name   マネージャ名
     *  @return string  マネージャクラス名
     */
    function getManagerClassName($name)
    {
        return sprintf('%s_%sManager', $this->getAppId(), ucfirst($name));
    }

    /**
     *  アクションスクリプトをインクルードする
     *
     *  ただし、インクルードしたファイルにクラスが正しく定義されているかどうかは保証しない
     *
     *  @access private
     *  @param  array   $action_obj     アクション定義
     *  @param  string  $action_name    アクション名
     */
    function _includeActionScript($action_obj, $action_name)
    {
        $class_path = $form_path = null;

        $action_dir = $this->getActiondir();

        // class_path属性チェック
        if (isset($action_obj['class_path'])) {
            // フルパス指定サポート
            $tmp_path = $action_obj['class_path'];
            if (Ethna_Util::isAbsolute($tmp_path) == false) {
                $tmp_path = $action_dir . $tmp_path;
            }

            if (file_exists($tmp_path) != false) {
                include_once($tmp_path);
                $class_path = $tmp_path;
            }
        }

        // デフォルトチェック
        if (is_null($class_path)) {
            $class_path = $this->getDefaultActionPath($action_name);
            if (file_exists($action_dir . $class_path)) {
                include_once($action_dir . $class_path);
            } else {
                $class_path = null;
            }
        }

        // 全ファイルインクルード
        if (is_null($class_path)) {
            $this->_includeDirectory($this->getActiondir());
            return;
        }

        // form_path属性チェック
        if (isset($action_obj['form_path'])) {
            // フルパス指定サポート
            $tmp_path = $action_obj['class_path'];
            if (Ethna_Util::isAbsolute($tmp_path) == false) {
                $tmp_path = $action_dir . $tmp_path;
            }

            if ($tmp_path == $class_path) {
                return;
            }
            if (file_exists($tmp_path) == false) {
            } else {
                include_once($tmp_path);
                $form_path = $tmp_path;
            }
        }

        // デフォルトチェック
        if (is_null($form_path)) {
            $form_path = $this->getDefaultFormPath($action_name);
            if ($form_path == $class_path) {
                return;
            }
            if (file_exists($action_dir . $form_path)) {
                include_once($action_dir . $form_path);
            }
        }
    }

    /**
     *  ビュースクリプトをインクルードする
     *
     *  ただし、インクルードしたファイルにクラスが正しく定義されているかどうかは保証しない
     *
     *  @access private
     *  @param  array   $forward_obj    遷移定義
     *  @param  string  $forward_name   遷移名
     */
    function _includeViewScript($forward_obj, $forward_name)
    {
        $view_dir = $this->getViewdir();

        // view_path属性チェック
        if (isset($forward_obj['view_path'])) {
            // フルパス指定サポート
            $tmp_path = $forward_obj['view_path'];
            if (Ethna_Util::isAbsolute($tmp_path) == false) {
                $tmp_path = $view_dir . $tmp_path;
            }

            if (file_exists($tmp_path) != false) {
                include_once($tmp_path);
                return;
            }
        }

        // デフォルトチェック
        $view_path = $this->getDefaultViewPath($forward_name);
        if (file_exists($view_dir . $view_path)) {
            include_once($view_dir . $view_path);
            return;
        } else {
            $view_path = null;
        }
    }

    /**
     *  ディレクトリ以下の全てのスクリプトをインクルードする
     *
     *  @access private
     */
    function _includeDirectory($dir)
    {
        $ext = "." . $this->ext['php'];
        $ext_len = strlen($ext);

        if (is_dir($dir) == false) {
            return;
        }

        $dh = opendir($dir);
        if ($dh) {
            while (($file = readdir($dh)) !== false) {
                if ($file != '.' && $file != '..' && is_dir("$dir/$file")) {
                    $this->_includeDirectory("$dir/$file");
                }
                if (substr($file, -$ext_len, $ext_len) != $ext) {
                    continue;
                }
                include_once("$dir/$file");
            }
        }
        closedir($dh);
    }
    /**
     *  CLI実行中フラグを取得する
     *
     *  @access public
     *  @return bool    CLI実行中フラグ
     *  @obsolete
     */
    function getCLI()
    {
        return $this->gateway == Ethna_Const::GATEWAY_CLI ? true : false;
    }

    /**
     *  CLI実行中フラグを設定する
     *
     *  @access public
     *  @param  bool    CLI実行中フラグ
     *  @obsolete
     */
    function setCLI($cli)
    {
        $this->gateway = $cli ? Ethna_Const::GATEWAY_CLI : $this->_getDefaultGateway();
    }
}
