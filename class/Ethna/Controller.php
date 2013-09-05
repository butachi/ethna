<?php
namespace Ethna;
/**
 * Ethna_Controller.php
 *
 * @author Masaki Fujimoto <fujimoto@php.net>
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @package Ethna
 * @version $Id$
 */
abstract class Controller
{
    protected $namespace = '';

    /** @var array アプリケーションディレクトリ */
    // @deprecated
    protected $directory = array(
        'action'     => 'app/action',
        'app'        => 'app',
        'bin'        => 'bin',
        'etc'        => 'etc',
        'filter'     => 'app/filter',
        'tmp'        => 'tmp',
        'view'       => 'app/view',
    );

    /** @var array クラス設定 */
    // @todo pass these parameters from outside.
    protected $class = array(
        'error'   => '\\Ethna\\ActionError',
        'form'    => '\\Ethna\\ActionForm',
        'session' => '\\Ethna\\Session',
        'view'    => '\\Frontend\\View\\Smarty',
    );

    /** @var array フィルタ設定 */
    protected $filter = array(
    );

    /** @var string 現在実行中のアクション名 */
    protected $action_name;

    /** @var array フィルターチェイン(Ethna_Filterオブジェクトの配列) */
    protected $filter_chain = array();

    /**
     * Ethna_Controllerクラスのコンストラクタ
     *
     * @param string $namespace
     */
    public function __construct($app_root, $namespace = '')
    {
        $this->namespace = $namespace;

        foreach ($this->class as $key => $class) {
            DIContainerFactory::getContainer()->set($key, function() use ($class) {
                return new $class;
            });
        }

        DIContainerFactory::getContainer()->set('app_root', function() use ($app_root) {
            return $app_root;
        });

    }

    /**
     * アクションディレクトリ名を決定する
     *
     * @access public
     * @return string アクションディレクトリ
     */
    public function getActiondir()
    {
        $key = 'action';

        return $this->directory[$key];
    }

    /**
     * アプリケーションディレクトリ設定を返す
     *
     * @access public
     * @param  string $key ディレクトリタイプ("tmp", "template"...)
     * @return string $keyに対応したアプリケーションディレクトリ(設定が無い場合はnull)
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
     * 実行中のアクション名を返す
     *
     * @access public
     * @return string 実行中のアクション名
     */
    public function getCurrentActionName()
    {
        return $this->action_name;
    }

    /**
     * フレームワークの処理を開始する
     *
     * @param string $namespace
     */
    public function execute($action_name = '')
    {
        // フィルターの生成
        // @todo to be lazy load
        $this->_createFilterChain();

        // 実行前フィルタ
        for ($i = 0; $i < count($this->filter_chain); $i++) {
            $r = $this->filter_chain[$i]->preFilter();
        }

        // trigger
        // @todo use router to determine
        if (!$action_name) {
            $this->action_name = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'index';
        }
        $this->_trigger($action_name);

        // 実行後フィルタ
        for ($i = count($this->filter_chain) - 1; $i >= 0; $i--) {
            $r = $this->filter_chain[$i]->postFilter();
            if (Ethna::isError($r)) {
                return $r;
            }
        }
    }

    /**
     * Execute the action
     *
     */
    private function _trigger($action_name)
    {
        // Restore Session
        $session = DIContainerFactory::getContainer()->get('session');
        $session->restore();

        // Pre action filters
        foreach ($this->filter_chain as $filter) {
            $filter->preActionFilter($this->action_name);
        }

        // prepare action
        $action_class_name = $this->_getActionName();
        $action = new $action_class_name($this);
        DIContainerFactory::getContainer()->set('action', $action);

        // prepare form
        $form_class_name = $this->getActionFormName();
        $form = new $form_class_name();
        $form->setFormVars();
        DIContainerFactory::getContainer()->set('form', $form);

        // authenticate
        $result = $action->authenticate();
        if ($result === false) {
            throw new \Ethna\Exception('authentication is failed.');
        } elseif (isset($result)) {
            $forward_name = $result;
        }

        // execute action
        if (! isset($forward_name)) {
            $forward_name = $action->perform();
        }

        // Post action filters
        for ($i = count($this->filter_chain) - 1; $i >= 0; $i--) {
            $this->filter_chain[$i]->postActionFilter($action_name, $forward_name);
        }

        // pass the view id.
        $view = DIContainerFactory::getContainer()->get('view');
        $view->defaultOutput($forward_name);
    }
    /**
     * エラーメッセージを取得する
     *
     * @access public
     * @param  int    $code エラーコード
     * @return string エラーメッセージ
     */
    public function getErrorMessage($code)
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
     * フォームにより要求されたアクション名を返す
     *
     * アプリケーションの性質に応じてこのメソッドをオーバーライドして下さい。
     * デフォルトでは"action_"で始まるフォーム値の"action_"の部分を除いたもの
     * ("action_sample"なら"sample")がアクション名として扱われます
     *
     * @access protected
     * @return string フォームにより要求されたアクション名
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
     * アクション名を指定するクエリ/HTMLを生成する
     *
     * @access public
     * @param string $action action to request
     * @param string $type   hidden, url...
     */
    public function getActionRequest($action, $type = "hidden")
    {
        $s = null;
        if ($type == "hidden") {
            $s = sprintf('<input type="hidden" name="action_%s" value="true">', htmlspecialchars($action, ENT_QUOTES));
        } elseif ($type == "url") {
            $s = sprintf('action_%s=true', urlencode($action));
        }

        return $s;
    }

    /**
     * フォームにより要求されたアクション名に対応する定義を返す
     *
     * @access private
     * @param  string $action_name アクション名
     * @return array  アクション定義
     */
    private function _getActionName()
    {
        return $this->namespace .'\\Action\\'.$this->_snailCaseToCamelCase($this->action_name);
    }

    private function _snailCaseToCamelCase($before)
    {
        $after = '';
        if (strpos($before, '_') !==false) {
            // in the case of card_list
            // -> card list -> Card List -> Card\List
            $after = str_replace(' ', '\\', ucwords(str_replace('_', ' ', $before)));
        } else {
            $after= ucfirst($before);
        }

        return $after;
    }

    /**
     * フィルタチェインを生成する
     *
     * @access private
     */
    private function _createFilterChain()
    {
        $this->filter_chain = array();
        foreach ($this->filter as $filter) {
            $file = sprintf("%s/%s.php", $this->getDirectory('filter'), $filter);
            if (file_exists($file)) {
                include_once($file);
            }
            if (class_exists($filter)) {
                $this->filter_chain[] = new $filter($this);
            }
        }
    }

    /**
     * 指定されたアクションのフォームクラス名を返す(オブジェクトの生成は行わない)
     *
     * @return string アクションのフォームクラス名
     */
    private function getActionFormName()
    {
        return $this->namespace .'\\Form\\'.$this->_snailCaseToCamelCase($this->action_name);
    }

}
