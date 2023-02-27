<?php
//////////////////////////////////////////////////////////////////////////////
// 共用DBテーブルのページコントロールビュー・メンテナンス クラス            //
// Copyright (C) 2004-2020 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/08/31 Created   CommonTableClass.php                                //
// 2005/06/27 file名を  ComTableMntClass.php へ Class名を ComTableMnt へ変更//
// 2005/07/09 out_pageRecOptions_HTM()とout_pageControl_HTML()メソッド追加  //
// 2005/07/10 out_pageCtlOpt_HTML() メソッド追加 (上記を合わせたもの)       //
//                                                                          //
// 次バーションではテーブル編集時のログ処理機能ロガーを実装する予定         //
//  主な仕様は 編集時にUser_ID, 日時, 編集前データ, 編集後のデータ を記録   //
//  error log はDBマネジャーにまかせる                                      //
//                                                                          //
// 2005/07/15 上記のロガーを実装 Ver 1.00 で正式リリース                    //
// 2005/07/17 変更削除前のデータ保存log_sql_saveを手動からimplode()版へ変更 //
// 2005/07/20 DBアクセスを Data Access Object daoInterfaceClassへ変更       //
// 2005/08/18 リクエストオブジェクトを追加しページ制御のデータをカプセル化  //
// 2005/09/13 表示ページの設定 set_view_page()を上限500→10000へ変更        //
// 2005/09/17 <form name='pageControl'をコメント ユーザーがformの中に入れる //
//            if (!is_numeric($default)) $default = 5;→$default = 20;へ変更//
// 2005/10/04 page_recをコンストラクタで設定するように変更(初期値を変更する)//
//  Ver1.06     初期値を各スクリプトで変更出来るようにするため              //
// 2005/10/07 ページ制御をしないList表示のSQL文の実行メソッドを追加         //
//  Ver1.07     public function execute_ListNotPageControl($sql='', &$res)  //
// 2005/10/31 E_ALL → E_STRICT へ変更 但しコメントにしアプリで制御する     //
//  Ver1.08     上記はアプリ側の ?????_Main.php で制御させる事を前提とする  //
// 2005/11/14 out_pageControll_HTML() → out_pageControl_HTML() 変更        //
//  Ver1.09     タイプミス修正によるメソッド名の変更                        //
// 2005/11/25 php5.1.0へUPで__construct()メソッドのif文で($request == '')→ //
//  Ver1.10     (!is_object($request)) へ変更 Notice 対応                   //
// 2006/07/03 記述ミスを修正                                                //
//  Ver1.11                $con=funcConnect() → $con = $this->connectDB()  //
// 2017/11/06 out_pageCtlOpt_HTMLのデフォルトを30に変更後戻し（20）    大谷 //
// 2019/12/18 out_pageCtlOpt_HTMLのデフォルトを25に変更                大谷 //
// 2020/02/25 out_pageCtlOpt_HTMLのデフォルトを30に変更                大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// require_once ('function.php');              // define.php と pgsql.php を require_once している
require_once ('daoInterfaceClass.php');     // Data Access Object クラス

if (class_exists('ComTableMnt')) {
    return;
}
define('CTM_VERSION', '1.11');

/****************************************************************************
*                        sub class 拡張クラスの定義                         *
****************************************************************************/
///// namespace Common {} は現在使用しない 使用例：Common::ComTableMnt → $obj = new Common::ComTableMnt;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class ComTableMnt extends daoInterfaceClass
{
    ///// Private properties
        /*********** ページ制御用 *************/
    private $sum_page;                  // 合計ページ数
    private $sum_rec;                   // 合計レコード数
    private $page_rec;                  // １ページのレコード数(仕様 1〜10000)
    private $view_page;                 // 表示ページ番号(1〜XX)(仕様 1〜500)
    private $offset;                    // 表示ページのオフセット値(SQL文のOFFSET)
    private $request;                   // リクエストオブジェクト
        /*********** テーブルメンテナンス用 *************/
    private $sql_select_sum;            // 合計レコード数取得のSQL文
    private $sql_select;                // List表示のSQL文      (最後に実行したSQL文の保存)
    private $sql_insert;                // データ追加のSQL文            〃
    private $sql_delete;                // データ削除のSQL文            〃
    private $sql_update;                // データ変更のSQL文            〃
        /*********** ログ用 *************/
    private $log_file;                  // ログファイル名
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer の定義 (php5へ移行時は __construct() へ変更予定) (デストラクタ__destruct())
    public function __construct($sql_sum='', $request='', $log_file='', $pageRec=20)
    {
        if (!isset($_SESSION)) @session_start();    // セッションの開始チェック Notice を避ける場合は頭に@
        if ($log_file == '') {                      // ログファイルのチェック
            $this->log_openCheck('/tmp/ComTableMnt.log');   // Default値
        } else {
            $this->log_openCheck($log_file);
        }
        if ($sql_sum == '') {
            $this->log_writer('パラメーター$sql_sumが設定されていません！');
            exit;                                   // error exit
        } elseif (!is_object($request)) {
            $this->log_writer('パラメーター$requestが設定されていません！');
            exit;                                   // error exit
        }
        $this->set_page_rec($pageRec);              // 初期化(順番が重要)
        $this->set_PageRequest($request, $sql_sum); // ページ制御用のリクエスト解析 & 設定
    }
    
    /*************************** Set & Check methods ************************/
    // １ページのレコード数の設定 (仕様 1〜10000)
    public function set_page_rec($page_rec=20)
    {
        $page_rec = (int)$page_rec;
        if ($page_rec < 1) {
            $this->page_rec = 1;
        } elseif ($page_rec > 10000) {
            $this->page_rec = 10000;
        } else {
            $this->page_rec = $page_rec;
        }
        return $this->page_rec;
    }
    // 表示ページの設定 (仕様 1〜10000) 2005/09/13 500→10000へ変更
    public function set_view_page($view_page=1)
    {
        $view_page = (int)$view_page;
        if ($view_page < 1) {
            $this->view_page = 1;
        } elseif ($view_page > $this->sum_page) {
            $this->view_page = $this->sum_page;
        } elseif ($view_page > 10000) {
            $this->view_page = 10000;
        } else {
            $this->view_page = $view_page;
        }
        return $this->view_page;
    }
    
    /*************************** Execute methods ************************/
    // List表示のSQL文の実行    (SQL文にはoffset/limit句がないことが前提)
    // $view_page(表示ページ番号)はオプションとして指定可能
    public function execute_List($sql='', &$res, $view_page='')
    {
        if ($sql == '') {
            return false;
        } elseif (!preg_match('/\bSELECT\b/i', $sql)) {
            return false;
        } else {
            if ($view_page != '') $view_page = $this->set_view_page($view_page);
            if (preg_match('/\bOFFSET\b/i', $sql)) return false;
            if (preg_match('/\bLIMIT\b/i', $sql))  return false;
            $sql .= $this->out_offsetLimit();
            $this->sql_select = $sql;
            $res = array();
            if( ($rows = $this->getResult2($sql, $res)) < 0) {
                return false;   // SQL文のエラー
            } else {
                return $rows;
            }
        }
    }
    // List表示のSQL文の実行 行番号付で返す (SQL文にはoffset/limit句がないことが前提)
    // $view_page(表示ページ番号)はオプションとして指定可能
    public function execute_ListRec($sql='', &$res, $view_page='')
    {
        if ($sql == '') {
            return false;
        } elseif (!preg_match('/\bSELECT\b/i', $sql)) {
            return false;
        } else {
            if ($view_page != '') $view_page = $this->set_view_page($view_page);
            if (preg_match('/\bOFFSET\b/i', $sql)) return false;
            if (preg_match('/\bLIMIT\b/i', $sql))  return false;
            $sql .= $this->get_offsetLimit();
            $this->sql_select = $sql;
            $res = array();
            if( ($rows = $this->getResult2($sql, $res)) < 0) {
                return false;   // SQL文のエラー
            } else {
                $offset = $this->get_offset();
                $tmp = array();
                $field = count($res[0]);
                for ($r=0; $r<$rows; $r++) {
                    $tmp[$r][0] = ($offset + $r + 1);
                    for ($f=0; $f<$field; $f++) {
                        $tmp[$r][$f+1] = $res[$r][$f];
                    }
                }
                $res = $tmp;    // 行番号付に変更
                return $rows;
            }
        }
    }
    // List表示のSQL文の実行 (ページ制御をしない)
    public function execute_ListNotPageControl($sql='', &$res)
    {
        if ($sql == '') {
            return false;
        } elseif (!preg_match('/\bSELECT\b/i', $sql)) {
            return false;
        } else {
            $this->sql_select = $sql;
            $res = array();
            if( ($rows = $this->getResult2($sql, $res)) < 0) {
                return false;   // SQL文のエラー
            } else {
                return $rows;
            }
        }
    }
    // データ追加のSQL文の実行
    public function execute_Insert($sql='')
    {
        if ($sql == '') {
            return false;
        } else {
            $this->sql_insert = $sql;
            if ($con = $this->connectDB()) {
                $this->query_affected_trans($con, 'BEGIN');
                if ( ($rows = $this->query_affected_trans($con, $sql)) > 0 ) {
                    $this->sum_rec += $rows;
                    $this->set_sumPage();   // 合計ページ数の再設定
                    $this->query_affected_trans($con, 'COMMIT');
                    $this->log_writer("Insert: OK SQL={$sql}");
                    return $rows;
                }
                $this->query_affected_trans($con, 'ROLLBACK');
                $this->log_writer("Insert: NG SQL={$sql}");
            }
        }
        return false;
    }
    // データ削除のSQL文の実行 オプションで$save_sqlを指定すれば削除前のデータをログに保存
    public function execute_Delete($sql='', $save_sql='')
    {
        if ($sql == '') {
            return false;
        } else {
            $this->sql_delete = $sql;
            if ($con = $this->connectDB()) {
                $this->query_affected_trans($con, 'BEGIN');
                if ($save_sql != '') {
                    if (!$this->log_sql_save('Delete: ', $save_sql)) {
                        $this->query_affected_trans($con, 'ROLLBACK');
                        return false;
                    }
                }
                if ( ($rows = $this->query_affected_trans($con, $sql)) > 0 ) {
                    $this->sum_rec -= $rows;
                    $this->set_sumPage();   // 合計ページ数の再設定
                    $this->query_affected_trans($con, 'COMMIT');
                    $this->log_writer("Delete: OK SQL={$sql}");
                    return $rows;
                }
                $this->query_affected_trans($con, 'ROLLBACK');
                $this->log_writer("Delete: NG SQL={$sql}");
            }
        }
        return false;
    }
    // データ変更のSQL文の実行 オプションで$save_sqlを指定すれば変更前のデータをログに保存
    public function execute_Update($sql='', $save_sql='')
    {
        if ($sql == '') {
            return false;
        } else {
            $this->sql_update = $sql;
            if ($con = $this->connectDB()) {
                $this->query_affected_trans($con, 'BEGIN');
                if ($save_sql != '') {
                    if (!$this->log_sql_save('Update: ', $save_sql)) {
                        $this->query_affected_trans($con, 'ROLLBACK');
                        return false;
                    }
                }
                if ( ($rows = $this->query_affected_trans($con, $sql)) > 0 ) {
                    $this->query_affected_trans($con, 'COMMIT');
                    $this->log_writer("Update: OK SQL={$sql}");
                    return $rows;
                }
                $this->query_affected_trans($con, 'ROLLBACK');
                $this->log_writer("Update: NG SQL={$sql}");
            }
        }
        return false;
    }
    
    /******************************* Get methods ****************************/
    // 合計ページ数の取得
    public function get_sumPage()
    {
        return $this->sum_page;
    }
    // 合計レコード数の取得
    public function get_sumRec()
    {
        return $this->sum_rec;
    }
    // １ページのレコード数の取得
    public function get_pageRec()
    {
        return $this->page_rec;
    }
    // 表示ページ番号の取得
    public function get_viewPage()
    {
        return $this->view_page;
    }
    ///// 表示ページ番号からSQL文のoffset値を取得
    public function get_offset()
    {
        // $offset = ($this->page_rec * $this->view_page - $this->page_rec);
        return $this->offset;
    }
    
    /******************************* Out methods ****************************/
    // ページコントロール用 行数／ページ 設定View HTML版の出力
    // 使用方法
    // <select name='pageRec' onChange='submit()'><タグ= $obj->out_pageRecOptions_HTML() タグ></select>
    public function out_pageRecOptions_HTML($default=30)
    {
        if (!is_numeric($default)) $default = 30;
        $Options = '';
        switch ($default) {
        case    5:
        case   10:
        case   15:
        case   20:
        case   25:
        case   30:
        case   50:
        case  100:
        case  500:
        case 1000:
            break;
        default:
            $Options .= "<option value='{$default}' selected>{$default}行</option>";
        }
        if ($default == 5) {
            $Options .= "<option value='5' selected>5行</option>";
        } else {
            $Options .= "<option value='5'>5行</option>";
        }
        if ($default == 10) {
            $Options .= "<option value='10' selected>10行</option>";
        } else {
            $Options .= "<option value='10'>10行</option>";
        }
        if ($default == 15) {
            $Options .= "<option value='15' selected>15行</option>";
        } else {
            $Options .= "<option value='15'>15行</option>";
        }
        if ($default == 20) {
            $Options .= "<option value='20' selected>20行</option>";
        } else {
            $Options .= "<option value='20'>20行</option>";
        }
        if ($default == 25) {
            $Options .= "<option value='25' selected>25行</option>";
        } else {
            $Options .= "<option value='25'>25行</option>";
        }
        if ($default == 30) {
            $Options .= "<option value='30' selected>30行</option>";
        } else {
            $Options .= "<option value='30'>30行</option>";
        }
        if ($default == 50) {
            $Options .= "<option value='50' selected>50行</option>";
        } else {
            $Options .= "<option value='50'>50行</option>";
        }
        if ($default == 100) {
            $Options .= "<option value='100' selected>100行</option>";
        } else {
            $Options .= "<option value='100'>100行</option>";
        }
        if ($default == 500) {
            $Options .= "<option value='500' selected>500行</option>";
        } else {
            $Options .= "<option value='500'>500行</option>";
        }
        if ($default == 1000) {
            $Options .= "<option value='1000' selected>1000行</option>";
        } else {
            $Options .= "<option value='1000'>1000行</option>";
        }
        return $Options;
    }
    // ページコントロールView HTML版の出力
    // 使用方法
    // MVC の View(HTML版) の任意の個所に <タグ= $obj->out_pageControl_HTML($menu->out_self()."?id={$uniq}") タグ> を埋め込む
    // 初期値の変数名は 'back', 'next', 'selectPage' である事に注意
    public function out_pageControl_HTML($action='')
    {
        if ($action == '') return '';
        $controll = "\n";
        // $controll .= "<form name='pageControl' method='get' action='{$action}'>\n";
        $controll .= "<table border='0'>\n";
        $controll .= "    <tr>\n";
        $controll .= "        <td nowrap>\n";
        if ($this->view_page > 1) $disabled = ''; else $disabled = ' disabled';
        $controll .= "            <input name='CTM_back' type='submit' value='←前へ'{$disabled}>\n";
        $controll .= "        </td>\n";
        $controll .= "        <td nowrap align='center'>\n";
        $controll .= "            <select name='CTM_selectPage' onChange='submit()' style='text-align:right;'>\n";
        for ($i=1; $i<=$this->sum_page; $i++) {
            if ($i == $this->view_page) $selected = ' selected '; else $selected = '';
            $controll .= "                <option value='$i'{$selected}>$i</option>\n";
        }
        $controll .= "            </select>\n";
        $controll .= "            ／\n"; //{$this->sum_page}\n";
        $controll .= "            <select name='dummy' disabled style='text-align:right;'>\n";
        $controll .= "                <option value='{$this->sum_page}'>{$this->sum_page}</option>\n";
        $controll .= "            </select>\n";
        $controll .= "        </td>\n";
        $controll .= "        <td nowrap>\n";
        if ($this->view_page < $this->sum_page) $disabled = ''; else $disabled = ' disabled';
        $controll .= "            <input name='CTM_next' type='submit' value='次へ→'{$disabled}>\n";
        $controll .= "        </td>\n";
        $controll .= "        <input type='hidden' name='CTM_prePage' value='{$this->view_page}'>\n";
        $controll .= "    </tr>\n";
        $controll .= "</table>\n";
        // $controll .= "</form>\n";
        return $controll;
    }
    // ページコントロール 1ページのレコード数設定オプション付 View HTML版の出力
    // 使用方法
    // MVC の View(HTML版) の任意の個所に <タグ= $obj->out_pageCtlOpt_HTML($menu->out_self()) タグ> を埋め込む
    // 初期値の変数名は 'back', 'next', 'selectPage' である事に注意
    public function out_pageCtlOpt_HTML($action='')
    {
        if ($action == '') return '';
        $controll = "\n";
        // $controll .= "<form name='pageControl' method='get' action='{$action}'>\n";
        $controll .= "<table border='0'>\n";
        $controll .= "    <tr>\n";
        $controll .= "        <td nowrap>\n";
        if ($this->view_page > 1) $disabled = ''; else $disabled = ' disabled';
        $controll .= "            <input name='CTM_back' type='submit' value='←前へ'{$disabled}>\n";
        $controll .= "        </td>\n";
        $controll .= "        <td nowrap align='center'>\n";
        $controll .= "            <select name='CTM_selectPage' onChange='submit()' style='text-align:right;'>\n";
        for ($i=1; $i<=$this->sum_page; $i++) {
            if ($i == $this->view_page) $selected = ' selected '; else $selected = '';
            $controll .= "                <option value='$i'{$selected}>$i</option>\n";
        }
        $controll .= "            </select>\n";
        $controll .= "            ／\n"; //{$this->sum_page}\n";
        $controll .= "            <select name='dummy' disabled style='text-align:right;'>\n";
        $controll .= "                <option value='{$this->sum_page}'>{$this->sum_page}</option>\n";
        $controll .= "            </select>\n";
        $controll .= "        </td>\n";
        $controll .= "        <td nowrap>\n";
        if ($this->view_page < $this->sum_page) $disabled = ''; else $disabled = ' disabled';
        $controll .= "            <input name='CTM_next' type='submit' value='次へ→'{$disabled}>\n";
        $controll .= "        </td>\n";
        $controll .= "        <input type='hidden' name='CTM_prePage' value='{$this->view_page}'>\n";
        $controll .= "        <td nowrap>\n";
        $controll .= "            <select name='CTM_pageRec' onChange='submit()' style='text-align:right;'>\n";
        $controll .= "                {$this->out_pageRecOptions_HTML($this->page_rec)}\n";
        $controll .= "            </select>\n";
        $controll .= "        </td>\n";
        $controll .= "    </tr>\n";
        $controll .= "</table>\n";
        // $controll .= "</form>\n";
        return $controll;
    }
    
    ///// ページ制御用のHTML GETメソッド用パラメーター取得
    public function get_htmlGETparm()
    {
        return "CTM_viewPage={$this->view_page}&CTM_pageRec={$this->page_rec}";
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ///// リクエストのページ制御用データ取得と設定
    protected function set_PageRequest($request, $sql_sum)
    {
        $pageRec    = $request->get('CTM_pageRec');
        $back       = $request->get('CTM_back');
        $next       = $request->get('CTM_next');
        $selectPage = $request->get('CTM_selectPage');
        $prePage    = $request->get('CTM_prePage');
        $viewPage   = $request->get('CTM_viewPage');    // ある処理をして一覧に戻る場合のページ復元用
        if ($pageRec != '') {
            if (!is_numeric($pageRec)) $pageRec = $this->page_rec;
        } else {
            $pageRec = $this->page_rec;  // 初期値はコンストラクタで設定される
        }
        if ($back != '') {
            $viewPage = ($prePage - 1);
        } elseif ($next != '') {
            $viewPage = ($prePage + 1);
        } elseif ($selectPage != '') {
            if (is_numeric($selectPage)) $viewPage = $selectPage; else $viewPage = 1;
        } elseif ($viewPage != '') {
            if (!is_numeric($viewPage)) $viewPage = 1;
        } else {
            $viewPage = 1;  // 初期値
        }
        $this->set_page_rec($pageRec);              // 初期化(順番が重要)
        $this->set_sumPageRec($sql_sum);            // 初期化で合計ページ数・レコード数の設定
        $this->set_view_page($viewPage);            // 初期化(順番が重要)
        return;
    }
    ///// 合計ページ数・レコード数の取得と設定 (SQL文はcount()を使用することが前提)
    protected function set_sumPageRec($sql='')
    {
        if ($sql == '') {
            return false;
        } else {
            $this->sql_select_sum = $sql;
            $sum_rec = 0;
            $this->getUniResult($sql, $sum_rec);
            $this->sum_rec = $sum_rec;
            $this->set_sumPage();
            return $sum_rec;
            // $_SESSION['s_sysmsg'] = "データベースのエラーです！ 管理担当者へ連絡して下さい。";
            // $this->log_writer("DB sum error SQL={$sql_sum}");
            // header('location: ' . H_WEB_HOST . ERROR . 'ErrorComTableMntClass.php?status=2');
            // exit;   // SQL文のerror又はDB error
        }
    }
    ///// 合計ページ数の設定
    protected function set_sumPage()
    {
        if ($this->sum_rec > 0) {
            $this->sum_page  = (int)($this->sum_rec / $this->page_rec);
            if ( ($this->sum_rec % $this->page_rec) > 0 ) $this->sum_page += 1;
        } else {
            $this->sum_page = 1;    // テーブルにレコードがない場合は強制的に合計ページ数を１にする
        }
    }
    ///// 表示ページ番号からSQL文のoffset/limit句を返す
    protected function out_offsetLimit()
    {
        $offset = ($this->page_rec * $this->view_page - $this->page_rec);
        $this->offset = $offset;    // プロパティへ保存
        $limit  = $this->page_rec;
        return " OFFSET {$offset} LIMIT {$limit}";
    }
    
    ///// ログファイルオープンのチェック
    protected function log_openCheck($log_name)
    {
        if ( !($fp_log = fopen($log_name, 'a')) ) {
            if (isset($_SESSION)) {
                $_SESSION['s_sysmsg'] = "ログファイル：{$log_name} をオープンできません！";
                header('location: ' . H_WEB_HOST . ERROR . 'ErrorComTableMntClass.php?status=1');
                exit;
            } else {
                echo "ログファイル：{$log_name} をオープンできません！\n";
                exit;
            }
        } else {
            fclose($fp_log);
            $this->log_name = $log_name;
        }
        return;
    }
    ///// クラス内共用ログ書込みメソッド
    protected function log_writer($msg)
    {
        $msg = date('Y-m-d H:i:s ') . "User={$_SESSION['User_ID']} Auth={$_SESSION['Auth']}\n    {$msg}\n";
        if ( ($fp_log = fopen($this->log_name, 'a')) ) {
            fwrite($fp_log, $msg);
        } else {
            ///// 一度だけ再試行する
            sleep(1);
            if ( ($fp_log = fopen($this->log_name, 'a')) ) {
                fwrite($fp_log, $msg);
            }
        }
        fclose($fp_log);
        return;
    }
    ///// テーブル変更・削除 前のデータをログに出力
    protected function log_sql_save($prefix='', $save_sql='')
    {
        if (!preg_match('/\bSELECT\b/i', $save_sql)) return false;
        $res = array();
        if ( ($rows = $this->getResult2($save_sql, $res)) > 0) {
            /************
            $save_data = $res[0][0];    // 最初の1フィールド
            $field     = count($res[0]);
            for ($r=0; $r<$rows; $r++) {
                for ($f=1; $f<$field; $f++) {
                    $save_data .= ("\t" . $res[$r][$f]);    // TAB区切り
                }
            }
            ************/
            for ($r=0; $r<$rows; $r++) {
                $save_data = implode("\t", $res[$r]);   // 手動からimplode()版へ変更
                $this->log_writer("{$prefix}save data=\n{$save_data}");
            }
            return true;
        } else {
            $this->log_writer("{$prefix}save error={$save_sql}");
            return false;
        }
    }
    
} // Class ComTableMnt End

?>
