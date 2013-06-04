変更点一覧
========================================================

master
-------------------------------------------------------


2.2.0
--------------------------------------------------------
- Updated document.


2.1.2-autoload
--------------------------------------------------------

- Supports PHP5.3=<
- Supported autoload and composer.
- Changed the behavior of ActionForm to use $_REQUEST instead of $_POST or $_GET.
- removed ?> in the end of PHP file.


2.1.1
--------------------------------------------------------

### bug fixes

- ethna.batのパスを修正

2.1.0
--------------------------------------------------------

### features

- ethnaコマンドのETHNA_HOMEをインストール時に決定するように改善
- Ethna_ActionForm::validate() で多次元配列が渡されたときのnoticeを回避
- Ethna_Backend::setActionForm(), Ethna_Backend::setActionClass()メソッドを追加
- Ethna_FilterのスケルトンにpreActionFilter()/postActionFilter()を追加
- Ethna_AppObject::_getPropDef()にキャッシュ処理を追加
- Ethna_CacheManagerクラスを追加(w/ localfile) - from GREE:)
- Ethna_DB::getDSN()メソッドを追加
- iniファイルのスケルトンにdsnサンプル追加
- add-templateコマンド追加(by nnno)
- add-project時のデフォルトテンプレートデザインを変更
- ethnaコマンドに-v(--version)オプションを追加
- smarty_modifier_select(), smarty_function_select()の"selected"属性のxhtml対応(selected="true")
- {form_name}, {form_input}プラグイン追加(激しくexperimentalというかongoing)
- Ethna_ViewClassでhelperアクションフォーム対応
-- Ethna_ViewClass->helper_action_form = array('some_action_name' => null, ...)とすると{form_name}とかで使えます
- [breaking B.C.] Ethna_ActionClassのpreforward()サポート(むかーしのコードにありましたのです)削除
- (ぷち)省エネブロックプラグイン{form}...{/form}追加
-- ethna_action引数も追加(勝手にhiddenタグ生成)
- Ethna_Controllerに$smarty_block_pluginプロパティを追加
- ethnaコマンドにadd-action-cliを追加
- [breaking B.C.] main_CLIのアクション定義ディレクトリをaction_cliに変更
-- controllerのdirectoryプロパティに'bin'要素を追加
- ethnaコマンドにadd-app-managerを追加(thanks butatic)
- Ethna_ActionForm リファクタリング (by いちい)
-- $this->form の省略値補正を setFormVars() からコンストラクタに移動
-- フォーム値のスカラー/配列チェックを setFormVars() でするように変更
--- vaildate() する前に setFormVars() でエラー (handleError()) が発生することがあります
-- フォーム値のスカラー/配列チェックでフォーム値定義と異なる場合は null にする
-- ファイルデータの再構成を常に行うように変更
-- フォーム値定義が配列で required, max/min の設定がある場合のバグを修正
-- _filter_alnum_zentohan() を追加 (mb_convert_kana($value, "a"))
- XMLRPCゲートウェイにfaultCodeサポートを追加
-- actionでEthna_Error(あるいはPEAR_Error)オブジェクトを返すとエラーを返せます
- XMLRPCゲートウェイサポート追加(experimental)
-- ethna add-action-xmlrpc [action]でXMLRPCメソッドを追加可能
-- 引数1つとフォーム定義1つが定義順に対応します
-- ToDo
--- 出力バッファチェック
--- method not foundなどエラー処理対応
- Ethna_ActionFormクラスのコンストラクタでsetFormVars()を実行しないように変更
- スケルトンに含まれる'your name'をマクロ({$author})に変更(~/.ethna対応)
- なげやり便利関数file_exists_ex(), is_absolute_path()を追加
- SimpleTestとの連携機能を追加(ethnaコマンドにadd-action-test,add-view-testの追加など)
-- SimpleTestのインストールチェックを追加
- package.xml生成スクリプト改善(ethnaコマンドインストール対応など)
- Haste_ADOdb, Haste_Creoleマージ(from Haste Project by haltさん)
- Ethna_AppObjectクラスのテーブル/プロパティ定義自動生成サポート追加(from generate_app_object originally by 井上さん+haltさん)
- Ethna_Controller::getAppdir()メソッドを追加
- Ethna_Controller::getDBType()の引数がnullだった場合に定義一覧を返すように変更
- ethnaコマンドラインハンドラを追加(+ハンドラをpluggableに+add-viewでテンプレート生成サポート)−please cp bin/ethna to /usr/local/bin or somewhere
 generate_project_skelton.php -> ethna add-project
 generate_action_script.php   -> ethna add-action
 generate_view_script.php     -> ethna add-view
 generate_app_object.php      -> ethna add-app-object
- [breaking B.C.] client_typeを廃止 -> gateway追加
-- CLIENT_TYPE定数廃止
-- Ethna_Controller::getClientType(), Ethna_Controller::setClientType()廃止
-- Ethna_Controller::setCLI()/Ethna_Controller::getCLI() -> obsolete
-- GATEWAY定数追加(GATEWAY_WWW, GATEWAY_CLI, GATEWAY_XMLRPC, GATEWAY_SOAP)
-- Ethna_Controller::setGateway()/Ethna_Controller::getGateway()追加
-- 作りかけのAMFゲートウェイサポートを(一旦)廃止
- Ethna_SkeltonGenerator::_checkAppId()をEthna_Controller::checkAppId()に移動
- generate_app_objectを追加
- クラスのメソッドもSmartyFunctionとして登録できるように修正

### bug fixes

- [[#8435>http://sourceforge.jp/tracker/index.php?func=detail&aid=8435&group_id=1343&atid=5092]](Ethna_AppObject prop_def[]['seq']が未設定)
- [[#8079>http://sourceforge.jp/tracker/index.php?func=detail&aid=8079&group_id=1343&atid=5092]](FilterでBackendを呼ぶとActionFormの値が空になる)
- [[#8200>http://sourceforge.jp/tracker/index.php?func=detail&aid=8200&group_id=1343&atid=5092]](PHP5.1.0以降でafのvalidate()で日付チェックが効かない)
- [[#8179>http://sourceforge.jp/tracker/index.php?func=detail&aid=8179&group_id=1343&atid=5092]](getManagerの戻り値が参照渡しになっていない)
- [[#8400>http://sourceforge.jp/tracker/index.php?func=detail&aid=8400&group_id=1343&atid=5092]](AppObject prop_def[]['form_name']がNULL)
- [[#7751>http://sourceforge.jp/tracker/index.php?func=detail&aid=7751&group_id=1343&atid=5092]](SAFE_MODEでmail関数の第５引数があるとWaning)を修正
- [[#8496>http://sourceforge.jp/tracker/index.php?func=detail&aid=8496&group_id=1343&atid=5092]](Ethna_AppObject.php内のtypo)を修正
- [[#8387>http://sourceforge.jp/tracker/index.php?func=detail&aid=8387&group_id=1343&atid=5092]](checkMailaddressやcheckURLでNotice)を修正
- [[#8130>http://sourceforge.jp/tracker/index.php?func=detail&aid=8130&group_id=1343&atid=5092]](Noticeつぶし)を修正
- typo fixed (aleady -> already)
- [[#7717>http://sourceforge.jp/tracker/index.php?func=detail&aid=7717&group_id=1343&atid=5092]](Ethna_AppObject::add()でNotice)を修正
- [[#7664>http://sourceforge.jp/tracker/index.php?func=detail&aid=7664&group_id=1343&atid=5092]](Ethna_AppObjectのバグ)を修正
- [[#7729>http://sourceforge.jp/tracker/index.php?func=detail&aid=7729&group_id=1343&atid=5092]](ethna_infoがFirefoxだとずれる)を修正

- (within beta) ethna_handle.phpが無用にob_end_clean()する問題を修正
- (within beta) ethna add-viewでプロジェクトディレクトリを指定した場合に正しくファイルが生成されない問題を修正
- (within beta) Windows版のethnaコマンドがパッケージからインストールした場合実行できない問題を修正
- (within beta) ActionFormの配列のフォーム値が破壊される問題を修正(by sfioさん)


[2006/01/29] 0.2.0
--------------------------------------------------------

### features

- 文字列のmin/maxエラーのデフォルトエラーメッセージを修正
- フォーム値定義にカスタムエラーメッセージを定義できるように変更
- Ethna_Controller::main_CLI()メソッドにフィルタを無効化させるオプションを追加
- Ethna_ActionFormクラスのフォーム値定義をダイナミックに変更出来るように修正
- Ethna_ActionFormクラスのフォーム値定義にテンプレート機能を追加
- Ethna_Backend::getActionClasss()メソッドの追加(実行中のアクションクラスを取得)
- ~/.ethnaファイルによるユーザ定義スケルトンマクロの追加
- smarty_function_selectに$empty引数を追加
- mb_*の変換元エンコーディングを、EUC-JP固定から内部エンコーディングに変更
- Ethna_Backend::begin()、Ethna_Backend::commit()、Ethna_Backend::rollback()を廃止
- Ethna_Controller::getDB()をEthna_Controller::getDBType()に変更
- Ethna_DBクラスを抽象クラス(扱い)として新たにEthna_DBクラスを実装したEthna_DB_PEARクラスを追加
- Ethna_LogWriterクラスを抽象クラス(扱い)として新たにEthna_LogWriterクラスを実装したEthna_LogWriter_Echo、Ethna_LogWriter_File、Ethna_LogWriter_Syslogクラスを追加
- log_facilityがnullの場合のログ出力クラスをEthna_LogWriter_EchoからEthna_LogWriterに変更(ログ出力なし)
- log_facilityにクラス名を書いた場合はそのクラスをログ出力クラスとして利用するように変更
- Ethna_Filter::preFilter()、Ethna_Filter::postFilter()がEthna_Errorオブジェクトを返した場合は実行を中止するように変更
- Ethna_InfoManagerの設定表示項目を追加
- Ethna_ActionForm::isForceValidatePlus()、Ethna_ActionForm::setForceValidatePlus()メソッドと、$force_validate_plusメンバを追加($force_validate_plusをtrueに設定すると、通常検証でエラーが発生した場合でも_validatePlus()メソッドが実行される−デフォルト:false)
- フォーム値定義のcustom属性にカンマ区切りでの複数メソッドサポートを追加

### bug fixes

- htmlspecialcharsにENT_QUOTESオプションを追加
- Ethna_AppSQLクラスのコンストラクタメソッド名を修正
- [[#7659>http://sourceforge.jp/tracker/index.php?func=detail&aid=7659&group_id=1343&atid=5092]](Ethna_Config.phpでNoticeエラー)を修正
- Ethna_SOAP_ActionForm.phpのtypoを修正
- [[#6616>http://sourceforge.jp/tracker/index.php?func=detail&aid=6616&group_id=1343&atid=5092]](セッションにObjectを格納できない)を修正
- [[#7640>https://sourceforge.jp/tracker/index.php?func=detail&aid=7640&group_id=1343&atid=5092]](機種依存文字のチェックでエラーメッセージが表示されない。)を修正
- [[#6566>https://sourceforge.jp/tracker/index.php?func=detail&aid=6566&group_id=1343&atid=5092]](skel.action.phpのサンプルでtypo)を修正
- [[#7451>https://sourceforge.jp/tracker/index.php?func=detail&aid=7451&group_id=1343&atid=5092]](PHP 5.0.5対応)を修正
- .museum対応
- Ethna_Backendクラスのクラスメンバ多重定義を修正
- BASE定数の影響でコントローラの継承が困難な問題を修正
- Windows環境で定義されていないLOG_LOCAL定数を評価してしまう問題を修正
- [[#6423>http://sourceforge.jp/tracker/index.php?func=detail&aid=6423&group_id=1343&atid=5092]](php-4.4.0で大量のエラーの後、Segv(11))を修正(patch by ramsyさん)
- [[#6074>http://sourceforge.jp/tracker/index.php?func=detail&aid=6074&group_id=1343&atid=5092]](generate_project_skelton.phpの動作異常)を修正
- safe_mode=onの場合にuid/gid warningが発生する(可能性のある)問題を修正
- 不要な参照渡しを削除
- その他細かな修正(elseif -> else if等)
- PATH_SEPARATOR/DIRECTORY_SEPARATORが未定義の場合(PHP 4.1.x等)の問題を修正
- smarty_modifier_wordwrap_i18n()の改行対応
- ユーザ定義フォーム検証メソッドが呼び出されない(ことがある)問題を修正
- マルチカラムプライマリキー利用時にオブジェクトの正当性が正しく判別できない問題を修正
- Ethna_AppObjectのJOIN検索がSQLエラーになる（ことがある）問題を修正
- セッションを復帰させるタイミングを遅延(無限ループする問題を修正)
- Ethna_MalSenderからmail()関数にオプションを渡せるように修正
- Ethna_View_List::_fixNameObjectに対象オブジェクトも渡すように修正


[2005/03/02] 0.1.5
--------------------------------------------------------

### features

- Ethna_Controller::getCLI()(CLIで実行中かどうかを返すメソッド)を追加
- ethna_error_handlerがphp.iniの設定に応じてPHPログも出力するように変更
- Smartyプラグイン(truncate_i18n)を追加
- Ethna_AppObject/Ethna_AppManagerにキャッシュ機構を追加(experimental)
- メールテンプレートエンジンのフックメソッドを追加
- MIMEエンコード用ユーティリティメソッドを追加
- include_pathのセパレータのwin32対応

### bug fixes

- ethna_error_handlerのtypoを修正
- Ethna_Sessionクラスでログが正しく出力されない問題を修正


[2005/01/14] 0.1.4
--------------------------------------------------------

### features

- Ethna_AppObjectでJOINした場合に、(可能なら)プライマリキーでGROUP BYするように変更

### bug fixes

- __ethna_info__が全く動作しない問題を修正:(


[2005/01/13] 0.1.3
--------------------------------------------------------

### features

- Ethna_AppSearchObjectの複合条件対応
- Ethna_ClassFactoryクラスを追加
- Ethna_Controllerのbackend, i18n, session, action_errorメンバを廃止
- Ethna_Controller::getClass()メソッドを廃止
- Ethna_ActionClassにauthenticateメソッドを追加
- preActionFilter/postActionFilterを追加(experimental)
- Ethna_View_List(リスト表示用ビュー基底クラス)のソート対応
- 組み込みSmarty関数is_error()を追加
- Ethna_ActionForm::handleErrorの第2引数を廃止
- Ethna_ActionForm::_handleErrorをpublicメソッドに変更(Ethna_ActionForm::handleErrorに名称変更)
- Ethna_ActionForm::getDefメソッドに引数を追加(省略可)

### bug fixes

- フォーム定義に配列を指定していた場合のカスタムチェックメソッドの呼び出しが正しく行われない問題を修正
- フォーム定義に配列を指定していた場合の必須チェックが正しく行われない問題を修正
- __ethna_info__がサブディレクトリに定義されたアクションを正しく取得できない問題を修正
- VAR_TYPE_FILEの場合はregexp属性が無効になるように修正


[2004/12/23] 0.1.2
--------------------------------------------------------

### features

- __ethna_info__アクションを追加
- class_path, form_path, view_path属性のフルパス指定サポートを追加
- スクリプトを1ファイルにまとめるツール(bin/unify_script.php)を追加

### bug fixes

- プロジェクトスケルトン生成時にアプリケーションIDの文字種/予約語をチェックするように修正
- 'form_name'を指定すると無用に警告が発生する問題を修正
- 絶対パス判定のプラットフォーム依存を修正(Windows対応改善)
- VAR_TYPE_INTとVAR_TYPE_FLOATの定義値が重複していた問題を修正
- SOAP/Mobile(AU)でアクションスクリプトのパスが正しく取得できない問題を修正
- Ethna_Util::getRandom()でmt_srand()しつつrand()を呼んでいた箇所をmt_rand()を呼び出すように修正
- CHANGESのエンコーディング修正(ISO-2022-JP -> EUC-JP)
- フレームワークが発行するSQL文に一部残っていたセミコロンを削除
- エントリポイント(index.php)に記述されたデフォルトアクション名の1要素目にアスタリスクが使用されていると、正しく動作しない(かもしれない)問題を修正~
例(こんな場合):
 <?php
 include_once('../../app/Sample_Controller.php');
 Sample_Controller::Main('Sample_Controller', array(
  'login*',
 ));
 ?>


[2004/12/10] 0.1.1
--------------------------------------------------------

### bug fixes

- ビューオブジェクトのpreforward()が呼ばれないことがある問題を修正
- アクション/ビューのスケルトン生成時にファイルを上書きしないように修正
- ビューのスケルトンでクラス名が正しく置換されない問題を修正

[2004/12/09] 0.1.0
--------------------------------------------------------

- 初期リリース

