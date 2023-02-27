<?php
//////////////////////////////////////////////////////////////////////////////
// MVC の Controller 部に必要なクラス HTTP 用                               //
// Copyright (C) 2005-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/06/28 Created   ControllerHTTP_Class.php                            //
//            初版は Request Class の定義 $request オブジェクトで処理する   //
// 2005/07/28 Session Class を追加                                          //
//   Ver 1.00                                                               //
// 2005/09/13 Requestの protected function add() → public メソッドへ変更   //
//   Ver 1.01                                                               //
// 2005/11/07 Requestの２次元配列に対応のためadd_array()を追加 ロジック変更 //
//   Ver 1.02                                                               //
// 2005/11/12 ini_set E_ALL が設定されているのでコメントアウト              //
//   Ver 1.03                                                               //
// 2005/11/17 class Request に del()メソッド追加 add_array()メソッド等で    //
//   Ver 1.04   配列変数を解除するのに使用する。                            //
// 2005/11/20 class Result に add() get() メソッド追加 add_once() get_once()//
//   Ver 1.05 ラッパーメソッドのようなもの他のクラスと同じインターフェースへ//
// 2005/11/20 class Result の get_once() get() に指定された名前のデータが   //
//   Ver 1.06 無い場合のロジック追加 class Request に合わせる Ver 1.02      //
// 2006/06/29 class Result にdata_array2 プロパティーを追加し複数配列に対応 //
//   Ver 1.07 class ResultをVer1.03へ add_array2(),get_array2()メソッド追加 //
// 2007/09/11 Result Class の add_array()/add_array2()メソッドの&(参照)削除 //
//   Ver 1.08 class ResultをVer1.04へ 第２引数の &$data → $data へ変更     //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// require_once ('function.php');

define('CHC_VERSION', '1.08');              // 全体 ControllerHTTP_Class VERSION

if (class_exists('Request')) {
    return;
}
define('RQE_VERSION', '1.04');

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Common {} は現在使用しない 使用例：Common::ComTableMnt → $obj = new Common::ComTableMnt;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class Request
{
    ///// Private properties
    private $params = array();                  // リクエストパラメータ(配列)
    private $magic_quotes_gpc;                  // システム設定のマジッククォート(boolean)
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer の定義 (php5へ移行時は __construct() へ変更予定)
    public function __construct($security = false)
    {
        $this->magic_quotes_gpc = ini_get('magic_quotes_gpc');
        if ($security) {
            // $_GET, $_POST, $_COOKIE, $_FILES の内容を格納した連想配列 ← $_FILESは余計だが
            // 現在 MenuHeader Class で以下の機能を実装しているので $security = false の初期値とする
            foreach ($_REQUEST as $key => $value) {
                $_REQUEST[$key] = strip_tags(htmlspecialchars($value, ENT_QUOTES));
                // データ抽出用のCLI版スクリプト等のために上記の逆関数を以下に示す｡
                // function unhtmlentities ($string)
                // {
                //     $trans_tbl = get_html_translation_table (HTML_ENTITIES, ENT_QUOTES);
                //     $trans_tbl = array_flip ($trans_tbl);
                //     return strtr ($string, $trans_tbl);
                // }
            }
        }
        // 通常のリクエストパラメータをプロパティへ登録
        if ( is_array($_REQUEST) ) {
            foreach ($_REQUEST as $name => $value) {
                if ($this->magic_quotes_gpc) {
                    if (is_array($value)) {
                        foreach ($value as $name2 => $value2) {
                            $this->add_array($name, $name2, stripslashes($value2));
                        }
                    } else {
                        $this->add($name, stripslashes($value));
                    }
                } else {
                    if (is_array($value)) {
                        foreach ($value as $name2 => $value2) {
                            $this->add_array($name, $name2, $value2);
                        }
                    } else {
                        $this->add($name, $value);
                    }
                }
            }
        }
    }
    /*************************** Get methods ************************/
    public function get($name)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        } else {
            return '';
        }
    }
    /*************************** Set methods ************************/
    public function add($name, $data)
    {
        $this->params[$name] = $data;
    }
    public function add_array($name, $name2, $data)
    {
        $this->params[$name][$name2] = $data;
    }
    ///// 主にadd_array()でセットした配列の割り当てを解除する 勿論add()でセットしたものにも使用できる
    public function del($name)
    {
        unset($this->params[$name]);
    }
    
    /************************************************************************
    *                             Protected methods                         *
    ************************************************************************/
    
} // Class Request End


define('RES_VERSION', '1.04');
/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
class Result
{
    ///// Private properties
    private $data_once   = array();  // １行データ
    private $data_array  = array();  // 行列データ(配列)
    private $data_array2 = array();  // 行列データ(２次元配列)
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    /*************************** Set methods ************************/
    public function add_once($name, $data)
    {
        $this->data_once[$name] = $data;
    }
    public function add($name, $data)
    {
        $this->data_once[$name] = $data;
    }
    public function add_array($data)
    {
        $this->data_array = $data;
    }
    public function add_array2($name, $data)
    {
        $this->data_array2[$name] = $data;
    }
    /*************************** Get methods ************************/
    public function get_once($name)
    {
        if (isset($this->data_once[$name])) {
            return $this->data_once[$name];
        } else {
            return '';
        }
    }
    public function get($name)
    {
        if (isset($this->data_once[$name])) {
            return $this->data_once[$name];
        } else {
            return '';
        }
    }
    public function get_array()
    {
        return $this->data_array;
    }
    public function get_array2($name)
    {
        if (isset($this->data_array2[$name])) {
            return $this->data_array2[$name];
        } else {
            return array();
        }
    }
} // Class Result End


define('SESSION_VERSION', '1.00');
/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
class Session
{
    ///// Private properties
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    /*************************** Set methods ************************/
    public function add($name, $data)
    {
        $_SESSION[$name] = $data;
    }
    public function add_local($name, $data)
    {
        $_SESSION[$_SERVER['PHP_SELF']][$name] = $data;
    }
    /*************************** Get methods ************************/
    public function get($name)
    {
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        } else {
            return '';
        }
    }
    public function get_local($name)
    {
        if (isset($_SESSION[$_SERVER['PHP_SELF']][$name])) {
            return $_SESSION[$_SERVER['PHP_SELF']][$name];
        } else {
            return '';
        }
    }
} // Class Session End

?>
