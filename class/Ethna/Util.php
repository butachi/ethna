<?php
namespace Ethna;
/**
 * Ethna_Util.php
 *
 * @todo 利用していないメソッドを消す
 * 
 * @author Masaki Fujimoto <fujimoto@php.net>
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @package Ethna
 * @version $Id$
 */
/**
 * ユーティリティクラス
 *
 * @author Masaki Fujimoto <fujimoto@php.net>
 * @access public
 * @package Ethna
 */
class Util
{
    /**
     * POSTのユニークチェックを行う
     *
     * @access public
     * @return bool true:2回目以降のPOST false:1回目のPOST
     */
    public static function isDuplicatePost()
    {
        $c = Ethna_Controller::getInstance();

        // use raw post data
        if (isset($_POST['uniqid'])) {
            $uniqid = $_POST['uniqid'];
        } elseif (isset($_GET['uniqid'])) {
            $uniqid = $_GET['uniqid'];
        } else {
            return false;
        }

        // purge old files
        Ethna_Util::purgeTmp("uniqid_", 60*60*1);

        $filename = sprintf("%s/uniqid_%s_%s", $c->getDirectory('tmp'), $_SERVER['REMOTE_ADDR'], $uniqid);
        if (file_exists($filename) == false) {
            touch($filename);

            return false;
        }

        $st = stat($filename);
        if ($st[9] + 60*60*1 < time()) {
            // too old
            return false;
        }

        return true;
    }

    /**
     * POSTのユニークチェックフラグをクリアする
     *
     * @access public
     * @return mixed 0:正常終了 Ethna_Error:エラー
     */
    public static function clearDuplicatePost()
    {
        $c = Ethna_Controller::getInstance();

        // use raw post data
        if (isset($_POST['uniqid'])) {
            $uniqid = $_POST['uniqid'];
        } else {
            return 0;
        }

        $filename = sprintf("%s/uniqid_%s_%s", $c->getDirectory('tmp'), $_SERVER['REMOTE_ADDR'], $uniqid);
        if (file_exists($filename)) {
            if (unlink($filename) == false) {
                return Ethna::raiseWarning(E_APP_WRITE, $filename);
            }
        }

        return 0;
    }

    /**
     * メールアドレスが正しいかどうかをチェックする
     *
     * @access public
     * @param  string $mailaddress チェックするメールアドレス
     * @return bool   true: 正しいメールアドレス false: 不正な形式
     */
    public static function checkMailAddress($mailaddress)
    {
        if (preg_match('/^([a-z0-9_]|\-|\.|\+)+@(([a-z0-9_]|\-)+\.)+[a-z]{2,6}$/i', $mailaddress)) {
            return true;
        }

        return false;
    }

    /**
     * CSV形式の文字列を配列に分割する
     *
     * @access public
     * @param  string $csv       CSV形式の文字列(1行分)
     * @param  string $delimiter フィールドの区切り文字
     * @return mixed  (array) :分割結果 Ethna_Error:エラー(行継続)
     */
    public static function explodeCSV($csv, $delimiter = ",")
    {
        $space_list = '';
        foreach (array(" ", "\t", "\r", "\n") as $c) {
            if ($c != $delimiter) {
                $space_list .= $c;
            }
        }

        $line_end = "";
        if (preg_match("/([$space_list]+)\$/sS", $csv, $match)) {
            $line_end = $match[1];
        }
        $csv = substr($csv, 0, strlen($csv)-strlen($line_end));
        $csv .= ' ';

        $field = '';
        $retval = array();

        $index = 0;
        $csv_len = strlen($csv);
        do {
            // 1. skip leading spaces
            if (preg_match("/^([$space_list]+)/sS", substr($csv, $index), $match)) {
                $index += strlen($match[1]);
            }
            if ($index >= $csv_len) {
                break;
            }

            // 2. read field
            if ($csv{$index} == '"') {
                // 2A. handle quote delimited field
                $index++;
                while ($index < $csv_len) {
                    if ($csv{$index} == '"') {
                        // handle double quote
                        if ($csv{$index+1} == '"') {
                            $field .= $csv{$index};
                            $index += 2;
                        } else {
                            // must be end of string
                            while ($csv{$index} != $delimiter && $index < $csv_len) {
                                $index++;
                            }
                            if ($csv{$index} == $delimiter) {
                                $index++;
                            }
                            break;
                        }
                    } else {
                        // normal character
                        if (preg_match("/^([^\"]*)/S", substr($csv, $index), $match)) {
                            $field .= $match[1];
                            $index += strlen($match[1]);
                        }

                        if ($index == $csv_len) {
                            $field = substr($field, 0, strlen($field)-1);
                            $field .= $line_end;

                            // request one more line
                            return Ethna::raiseNotice(\Ethna\Constant::E_UTIL_CSV_CONTINUE);
                        }
                    }
                }
            } else {
                // 2B. handle non-quoted field
                if (preg_match("/^([^$delimiter]*)/S", substr($csv, $index), $match)) {
                    $field .= $match[1];
                    $index += strlen($match[1]);
                }

                // remove trailing spaces
                $field = preg_replace("/[$space_list]+\$/S", '', $field);
                if ($csv{$index} == $delimiter) {
                    $index++;
                }
            }
            $retval[] = $field;
            $field = '';
        } while ($index < $csv_len);

        return $retval;
    }

    /**
     * CSVエスケープ処理を行う
     *
     * @access public
     * @param  string $csv       エスケープ対象の文字列(CSVの各要素)
     * @param  bool   $escape_nl 改行文字(\r/\n)のエスケープフラグ
     * @return string CSVエスケープされた文字列
     */
    public static function escapeCSV($csv, $escape_nl = false)
    {
        if (preg_match('/[,"\r\n]/', $csv)) {
            if ($escape_nl) {
                $csv = preg_replace('/\r/', "\\r", $csv);
                $csv = preg_replace('/\n/', "\\n", $csv);
            }
            $csv = preg_replace('/"/', "\"\"", $csv);
            $csv = "\"$csv\"";
        }

        return $csv;
    }

    /**
     * 配列の要素を全てHTMLエスケープして返す
     *
     * @access public
     * @param  array $target HTMLエスケープ対象となる配列
     * @return array エスケープされた配列
     */
    public static function escapeHtml($target)
    {
        $r = array();
        Ethna_Util::_escapeHtml($target, $r);

        return $r;
    }

    /**
     * 配列の要素を全てHTMLエスケープして返す
     *
     * @access public
     * @param mixed $vars   HTMLエスケープ対象となる配列
     * @param mixed $retval HTMLエスケープ対象となる子要素
     */
    public static function _escapeHtml(&$vars, &$retval)
    {
        foreach (array_keys($vars) as $name) {
            if (is_array($vars[$name])) {
                $retval[$name] = array();
                Ethna_Util::_escapeHtml($vars[$name], $retval[$name]);
            } elseif (!is_object($vars[$name])) {
                $retval[$name] = htmlspecialchars($vars[$name], ENT_QUOTES);
            }
        }
    }

    /**
     * 文字列をMIMEエンコードする
     *
     * @access public
     * @param  string                            $string MIMEエンコードする文字列
     * @return エンコード済みの文字列
     */
    public static function encode_MIME($string)
    {
        $pos = 0;
        $split = 36;
        $_string = "";
        while ($pos < mb_strlen($string)) {
            $tmp = mb_strimwidth($string, $pos, $split, "");
            $pos += mb_strlen($tmp);
            $_string .= (($_string)? ' ' : '') . mb_encode_mimeheader($tmp, 'ISO-2022-JP');
        }

        return $_string;
    }

    /**
     * Google風リンクリストを返す
     *
     * @access public
     * @param  int   $total  検索総件数
     * @param  int   $offset 表示オフセット
     * @param  int   $count  表示件数
     * @return array リンク情報を格納した配列
     */
    public static function getDirectLinkList($total, $offset, $count)
    {
        $direct_link_list = array();

        if ($total == 0) {
            return array();
        }

        // backwards
        $current = $offset - $count;
        while ($current > 0) {
            array_unshift($direct_link_list, $current);
            $current -= $count;
        }
        if ($offset != 0 && $current <= 0) {
            array_unshift($direct_link_list, 0);
        }

        // current
        $backward_count = count($direct_link_list);
        array_push($direct_link_list, $offset);

        // forwards
        $current = $offset + $count;
        for ($i = 0; $i < 10; $i++) {
            if ($current >= $total) {
                break;
            }
            array_push($direct_link_list, $current);
            $current += $count;
        }
        $forward_count = count($direct_link_list) - $backward_count - 1;

        $backward_count -= 4;
        if ($forward_count < 5) {
            $backward_count -= 5 - $forward_count;
        }
        if ($backward_count < 0) {
            $backward_count = 0;
        }

        // add index
        $n = 1;
        $r = array();
        foreach ($direct_link_list as $direct_link) {
            $v = array('offset' => $direct_link, 'index' => $n);
            $r[] = $v;
            $n++;
        }

        return array_splice($r, $backward_count, 10);
    }

    /**
     * 元号制での年を返す
     *
     * @access public
     * @param  int    $t unix time
     * @return string 元号(不明な場合はnull)
     */
    public static function getEra($t)
    {
        $tm = localtime($t, true);
        $year = $tm['tm_year'] + 1900;

        if ($year >= 1989) {
            return array('平成', $year - 1988);
        } elseif ($year >= 1926) {
            return array('昭和', $year - 1925);
        }

        return null;
    }

    /**
     * getimagesize()の返すイメージタイプに対応する拡張子を返す
     *
     * @access public
     * @param  int    $type getimagesize()関数の返すイメージタイプ
     * @return string $typeに対応する拡張子
     */
    public static function getImageExtName($type)
    {
        $ext_list = array(
            1 => 'gif',
            2 => 'jpg',
            3 => 'png',
            4 => 'swf',
            5 => 'psd',
            6 => 'bmp',
            7 => 'tiff',
            8 => 'tiff',
            9 => 'jpc',
            10 => 'jp2',
            11 => 'jpx',
            12 => 'jb2',
            13 => 'swc',
            14 => 'iff',
            15 => 'wbmp',
            16 => 'xbm',
        );

        return @$ext_list[$type];
    }

    /**
     * ランダムなハッシュ値を生成する
     *
     * 決して高速ではないので乱用は避けること
     *
     * @access public
     * @param  int    $length ハッシュ値の長さ(〜64)
     * @return string ハッシュ値
     */
    public static function getRandom($length = 64)
    {
        static $srand = false;

        if ($srand == false) {
            list($usec, $sec) = explode(' ', microtime());
            mt_srand((float) $sec + ((float) $usec * 100000) + getmypid());
            $srand = true;
        }

        $value = "";
        for ($i = 0; $i < 2; $i++) {
            // for Linux
            if (file_exists('/proc/net/dev')) {
                $rx = $tx = 0;
                $fp = fopen('/proc/net/dev', 'r');
                if ($fp != null) {
                    $header = true;
                    while (feof($fp) === false) {
                        $s = fgets($fp, 4096);
                        if ($header) {
                            $header = false;
                            continue;
                        }
                        $v = preg_split('/[:\s]+/', $s);
                        if (is_array($v) && count($v) > 10) {
                            $rx += $v[2];
                            $tx += $v[10];
                        }
                    }
                }
                $platform_value = $rx . $tx . mt_rand() . getmypid();
            } else {
                $platform_value = mt_rand() . getmypid();
            }
            $now = strftime('%Y%m%d %T');
            $time = gettimeofday();
            $v = $now . $time['usec'] . $platform_value . mt_rand(0, time());
            $value .= md5($v);
        }

        if ($length < 64) {
            $value = substr($value, 0, $length);
        }

        return $value;
    }

    /**
     * 1次元配列をm x nに再構成する
     *
     * @access public
     * @param  array $array 処理対象の1次元配列
     * @param  int   $m     軸の要素数
     * @param  int   $order $mをX軸と見做すかY軸と見做すか(0:X軸 1:Y軸)
     * @return array m x nに再構成された配列
     */
    public static function get2dArray($array, $m, $order)
    {
        $r = array();

        $n = intval(count($array) / $m);
        if ((count($array) % $m) > 0) {
            $n++;
        }
        for ($i = 0; $i < $n; $i++) {
            $elts = array();
            for ($j = 0; $j < $m; $j++) {
                if ($order == 0) {
                    // 横並び(横：$m列 縦：無制限)
                    $key = $i*$m+$j;
                } else {
                    // 縦並び(横：無制限 縦：$m行)
                    $key = $i+$n*$j;
                }
                if (array_key_exists($key, $array) == false) {
                    $array[$key] = null;
                }
                $elts[] = $array[$key];
            }
            $r[] = $elts;
        }

        return $r;
    }

    /**
     * パス名が絶対パスかどうかを返す
     *
     * port from File in PEAR (for BC)
     *
     * @access public
     * @param  string $path
     * @return bool   true:絶対パス false:相対パス
     */
    public static function isAbsolute($path)
    {
        if (!$path) {
            return false;
        }
        if (!is_string($path)) {
            return false;
        }

        if (DIRECTORY_SEPARATOR == '/' && (substr($path, 0, 1) == '/' OR substr($path, 0, 1) == '~')) {
            return true;
        } elseif (DIRECTORY_SEPARATOR == '\\' && preg_match('/^[a-z]:\\\/i', $path)) {
            return true;
        }

        return false;
    }

    /**
     * テンポラリディレクトリのファイルを削除する
     *
     * @access public
     * @param string $prefix  ファイルのプレフィクス
     * @param int    $timeout 削除対象閾値(秒−60*60*1なら1時間)
     */
    public static function purgeTmp($prefix, $timeout)
    {
        $c = Ethna_Controller::getInstance();

        $dh = opendir($c->getDirectory('tmp'));
        if ($dh) {
            while (($file = readdir($dh)) !== false) {
                if (strncmp($file, $prefix, strlen($prefix)) == 0) {
                    $f = $c->getDirectory('tmp') . "/" . $file;
                    $st = @stat($f);
                    if ($st[9] + $timeout < time()) {
                        unlink($f);
                    }
                }
            }
            closedir($dh);
        }
    }

    /**
     * ファイルをロックする
     *
     * @access public
     * @param  string $file    ロックするファイル名
     * @param  int    $mode    ロックモード('r', 'rw')
     * @param  int    $timeout ロック待ちタイムアウト(秒−0なら無限)
     * @return int    ロックハンドル(falseならエラー)
     */
    public static function lockFile($file, $mode, $timeout = 0)
    {
        $lh = @fopen($file, 'r');
        if ($lh == null) {
            return false;
        }

        $lock_mode = $mode == 'r' ? LOCK_SH : LOCK_EX;

        for ($i = 0; $i < $timeout || $timeout == 0; $i++) {
            $r = flock($lh, $lock_mode | LOCK_NB);
            if ($r == true) {
                break;
            }
            sleep(1);
        }
        if ($timeout > 0 && $i == $timeout) {
            // timed out
            return false;
        }

        return $lh;
    }

    /**
     * ファイルのロックを解除する
     *
     * @access public
     * @param int $lh ロックハンドル
     */
    public static function unlockFile($lh)
    {
        fclose($lh);
    }

    /**
     * バックトレースをフォーマットして返す
     *
     * @access public
     * @param  array  $bt debug_backtrace()関数で取得したバックトレース
     * @return string 文字列にフォーマットされたバックトレース
     */
    public static function formatBacktrace($bt)
    {
        $r = "";
        $i = 0;
        foreach ($bt as $elt) {
            $r .= sprintf("[%02d] %s:%d:%s.%s\n", $i, $elt['file'], $elt['line'], isset($elt['class']) ? $elt['class'] : 'global', $elt['public static function']);
            $i++;

            if (isset($elt['args']) == false || is_array($elt['args']) == false) {
                continue;
            }

            // 引数のダンプ
            foreach ($elt['args'] as $arg) {
                $r .= Ethna_Util::_formatBacktrace($arg);
            }
        }

        return $r;
    }

    /**
     * バックトレース引数をフォーマットして返す
     *
     * @access private
     * @param  string $arg   バックトレースの引数
     * @param  int    $level バックトレースのネストレベル
     * @param  int    $wrap  改行フラグ
     * @return string 文字列にフォーマットされたバックトレース
     */
    public static function _formatBacktrace($arg, $level = 0, $wrap = true)
    {
        $pad = str_repeat(" ", $level);
        if (is_array($arg)) {
            $r = sprintf(" %s[array] => (\n", $pad);
            if ($level+1 > 4) {
                $r .= sprintf(" %s *too deep*\n", $pad);
            } else {
                foreach ($arg as $key => $elt) {
                    $r .= Ethna_Util::_formatBacktrace($key, $level, false);
                    $r .= " => \n";
                    $r .= Ethna_Util::_formatBacktrace($elt, $level+1);
                }
            }
            $r .= sprintf(" %s)\n", $pad);
        } elseif (is_object($arg)) {
            $r = sprintf(" %s[object]%s%s", $pad, get_class($arg), $wrap ? "\n" : "");
        } else {
            $r = sprintf(" %s[%s]%s%s", $pad, gettype($arg), $arg, $wrap ? "\n" : "");
        }

        return $r;
    }

    /**
     * グローバルユーティリティ関数: スカラー値を要素数1の配列として返す
     *
     * @param  mixed $v 配列として扱う値
     * @return array 配列に変換された値
     */
    public static function to_array($v)
    {
        if (is_array($v)) {
            return $v;
        } else {
            return array($v);
        }
    }

    /**
     * グローバルユーティリティ関数: 指定されたフォーム項目にエラーがあるかどうかを返す
     *
     * @param  string $name フォーム項目名
     * @return bool   true:エラー有り false:エラー無し
     */
    public static function is_error($name)
    {
        $c = Ethna_Controller::getInstance();

        $action_error = $c->getActionError();

        return $action_error->isError($name);
    }

    /**
     * グローバルユーティリティ関数: include_pathを検索しつつfile_exists()する
     *
     * @param  string $path             ファイル名
     * @param  bool   $use_include_path include_pathをチェックするかどうか
     * @return bool   true:有り false:無し
     */
    public static function file_exists_ex($path, $use_include_path = true)
    {
        if ($use_include_path == false) {
            return file_exists($path);
        }

        // check if absolute
        if (is_absolute_path($path)) {
            return file_exists($path);
        }

        $include_path_list = explode(PATH_SEPARATOR, get_include_path());
        if (is_array($include_path_list) == false) {
            return file_exists($path);
        }

        foreach ($include_path_list as $include_path) {
            if (file_exists($include_path . DIRECTORY_SEPARATOR . $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * グローバルユーティリティ関数: 絶対パスかどうかを返す
     *
     * @param  string $path ファイル名
     * @return bool   true:絶対 false:相対
     */
    public static function is_absolute_path($path)
    {
        if (OS_WINDOWS) {
            if (preg_match('/^[a-z]:/i', $path) && $path{2} == DIRECTORY_SEPARATOR) {
                return true;
            }
        } else {
            if ($path{0} == DIRECTORY_SEPARATOR) {
                return true;
            }
        }

        return false;
    }

}
