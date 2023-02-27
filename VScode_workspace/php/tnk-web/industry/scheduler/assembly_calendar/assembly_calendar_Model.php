<?php
//////////////////////////////////////////////////////////////////////////////
// 組立ラインのカレンダー メンテナンス                       MVC Model 部   //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/07/11 Created   assembly_calendar_Model.php                         //
// 2006/07/12 getAuthority($id, $division)メソッドを追加し編集権限チェック  //
// 2006/09/29 営休コメント→稼停コメント, 営時コメント→稼時コメントへ変更  //
// 2006/10/04 編集権限 本番の$this->getCheckAuthority()を使用するように変更 //
// 2006/10/05 getCheckAuthority($id,$division)→getCheckAuthority($division)//
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント

require_once ('../../../daoInterfaceClass.php');   // TNK 全共通 DAOインターフェースクラス


/*****************************************************************************************
*       MVCのModel部 クラス定義 daoInterfaceClass(base class) 基底クラスを拡張           *
*****************************************************************************************/
class AssemblyCalendar_Model extends daoInterfaceClass
{
    ///// Private properties
    private $calendarStatus;                    // カレンダーのアクション切替
    private $calendarMsg;                       // カレンダーのチップヘルプ及びステータスバーのメッセージ
    private $calendarUrl;                       // カレンダー操作のURLアドレス
    
    private $sumBusinessHours = 0;              // 合計稼働時間(１ヶ月)
    private $sumAbsentTime = 0;                 // 合計停止時間(１ヶ月)
    private $netBusinessHours = 0;              // 実稼働時間計(１ヶ月)
    
    private $authDiv = 2;                       // このビジネスロジックの権限区分
    
    ///// Public properties
    // public  $graph;                             // GanttChartのインスタンス
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer の定義 (php5へ移行時は __construct() へ変更予定) (デストラクタ__destruct())
    public function __construct($request, $menu)
    {
        ///// カレンダーのアクション設定
        switch ($request->get('targetCalendar')) {
        case 'BDSwitch':
            $this->calendarStatus = 'BDSwitch';
            $this->calendarMsg = 'クリックする毎に停止日と稼働日を切替えます。';
            $this->calendarUrl = "window.parent.AssemblyCalendar.AjaxLoadUrl(\"{$menu->out_self()}?Action=Change&showMenu=Calendar%s\")";
            break;
        case 'Comment':
            $this->calendarStatus = 'Comment';
            $this->calendarMsg = 'クリックすると停止日または稼働日のコメント編集が出来ます。';
                                                    // 旧タイプ(location.replace)は CommentEdit で Comment を呼出す
            $this->calendarUrl = "AssemblyCalendar.win_open(\"{$menu->out_self()}?Action=Comment&showMenu=EditComment%s\", 400, 200, \"CommentWin\")";
            break;
        case 'SetTime':
            $this->calendarStatus = 'SetTime';
            $this->calendarMsg = 'クリックすると稼働時間および停止時間の編集が出来ます。';
            $this->calendarUrl = "window.parent.AssemblyCalendar.actionNameSwitch(); window.parent.AssemblyCalendar.AjaxLoadUrl(\"{$menu->out_self()}?Action=TimeList&showMenu=List%s\")";
            break;
        default:
            $this->calendarStatus = '';
            $this->calendarMsg = '';
            $this->calendarUrl = '';
        }
    }
    
    ///// 対象年月のHTML <select> option の出力
    public function getTargetDateYvalues($request)
    {
        // 初期化
        $option = "\n";
        $year = date('Y');
        $year++;
        for ($i=$year; $i>=2000; $i--) {
            $ki = $i - 2000 + 1;
            $ki = sprintf('%02d', $ki);
            $ki = mb_convert_kana($ki, 'N');
            if ($request->get('targetDateY') == $i) {
                $option .= "<option value='{$i}' selected>第{$ki}期</option>\n";
            } else {
                $option .= "<option value='{$i}'>第{$ki}期</option>\n";
            }
        }
        // $option .= "<option value='2006'>第０７期</option>\n";
        return $option;
    }
    
    ///// 対象期のカレンダーの出力
    public function showCalendar($request, $calendar, $menu, $uniq)
    {
        // カレンダーの年月取得
        $strYear  = substr($request->get('targetDateStr'), 0, 4);
        $strMonth = substr($request->get('targetDateStr'), 4, 2);
        // 初期化
        $table_list = "\n";     // 初期化
        $table_list .= "<table border='0' align='center'>\n";
        $colCount = 0;
        for ($i=0; $i<12; $i++) {
            // カレンダーの全日付にリンクを設定する
            $calendar[$i]->setAllLinkYMD($strYear, $strMonth, $this->calendarUrl, $this->calendarMsg);
            if ($colCount == 0) {
                $table_list .= "    <tr>\n";
            }
            $table_list .= "    <td valign='top'>\n";
            // 指定日のチェック
            if ($request->get('targetCalendar') != 'BDSwitch' && $request->get('year') == $strYear && $request->get('month') == $strMonth) {
                $table_list .= "        {$calendar[$i]->show_calendar($request->get('targetLine'), $strYear, $strMonth, $request->get('day'))}\n";
            } else {
                $table_list .= "        {$calendar[$i]->show_calendar($request->get('targetLine'), $strYear, $strMonth)}\n";
            }
            $table_list .= "    </td>\n";
            if ($colCount == 3) {
                $table_list .= "    </tr>\n";
            }
            $colCount++;
            if ($colCount == 4) $colCount = 0;
            $strMonth++;
            if ($strMonth == 13) {
                $strMonth = 1;
                $strYear++;
            }
            $strYM = sprintf('%04d%02d', $strYear, $strMonth);
            if ($strYM > $request->get('targetDateEnd')) break;
        }
        $table_list .= "</table>\n";
        // return mb_convert_encoding($table_list, 'UTF-8');
        // return $table_list;
        
        // 固定のHTMLヘッダーを取得
        $listHTML  = $this->getViewCalendarHTMLconst('header', $uniq);
        
        // カレンダーテーブルを付加
        $listHTML .= $table_list;
        
        // 固定のHTMLフッターを取得
        $listHTML .= $this->getViewCalendarHTMLconst('footer', $uniq);
        
        // HTMLファイル出力
        $file_name = "list/assembly_calendar_ViewCalendar-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
    }
    
    ///// 会社の停止日・稼働日トグル切替
    public function changeHoliday($request, $result, $menu)
    {
        if (!$this->getAuthority($_SESSION['User_ID'], $this->authDiv)) {
            return false;
        }
        $query = "
            SELECT tdate FROM assembly_calendar WHERE
            line = '{$request->get('targetLine')}' AND tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
            AND bd_flg
        ";
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        if ($this->getUniResult($query, $check) < 1) {
            $sql = "
                UPDATE assembly_calendar SET bd_flg = TRUE, last_date = '{$last_date}', last_user = '{$last_user}'
                WHERE line = '{$request->get('targetLine')}' AND tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
            ";
        } else {
            $sql = "
                UPDATE assembly_calendar SET bd_flg = FALSE, last_date = '{$last_date}', last_user = '{$last_user}'
                WHERE line = '{$request->get('targetLine')}' AND tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
            ";
        }
        if ($this->query_affected($sql) <= 0) {
            $_SESSION['s_sysmsg'] = '停止日・稼働日の切替が出来ませんでした。管理担当者に連絡して下さい！';
        } else {
            if ($request->get('combinedEdit') == 'yes') {   // 稼働日／停止日切替とコメントを同時編集の場合
                $script = "AssemblyCalendar.win_open('{$menu->out_self()}?Action=Comment&showMenu=EditComment&year={$request->get('year')}&month={$request->get('month')}&day={$request->get('day')}', 400, 200);\n";
                $result->add('autoLoadScript', $script);
            }
        }
    }
    
    ///// カレンダーの指定日付のコメントを編集
    public function commentEdit($request, $result, $menu)
    {
        $script = "AssemblyCalendar.win_open('{$menu->out_self()}?Action=Comment&showMenu=EditComment&year={$request->get('year')}&month={$request->get('month')}&day={$request->get('day')}', 400, 200);\n";
        $result->add('autoLoadScript', $script);
    }
    
    ///// 停止日・稼働日のコメントを保存
    public function commentSave($request)
    {
        if (!$this->getAuthority($_SESSION['User_ID'], $this->authDiv)) {
            return false;
        }
        // コメントのパラメーターチェック(内容はチェック済み)
        // if ($request->get('note') == '') return;  // これを行うと削除できない
        if ($request->get('year') == '')  return '';
        if ($request->get('month') == '') return '';
        if ($request->get('day') == '')   return '';
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        $note = str_replace("\r\n", '', $request->get('note')); // CRLFを取除く
        // データの存在チェック
        $query = "
            SELECT note FROM assembly_calendar WHERE line = '{$request->get('targetLine')}' AND tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
        ";
        if ($this->getUniResult($query, $old_note) <= 0) {
            return;     // データが他の人に変更されたかＤＢエラー
        }
        if ($old_note == $note) return; // データは変更されていないので更新しない
        $sql = "
            UPDATE assembly_calendar SET note = '{$note}',
            last_date='{$last_date}', last_user='{$last_user}'
            WHERE line = '{$request->get('targetLine')}' AND tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
        ";
        if ($this->query_affected($sql) <= 0) {
            $_SESSION['s_sysmsg'] = 'コメントの保存が出来ませんでした！　管理担当者へ連絡して下さい。';
        } else {
            $_SESSION['s_sysmsg'] = '登録しました。';
        }
        return ;
    }
    
    ///// 停止日・稼働日のコメントを取得
    public function getComment($request, $result)
    {
        // コメントのパラメーターチェック(内容はチェック済み)
        if ($request->get('year') == '')  return false;
        if ($request->get('month') == '') return false;
        if ($request->get('day') == '')   return false;
        $query = "
            SELECT  note FROM assembly_calendar
            WHERE line = '{$request->get('targetLine')}' AND tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
        ";
        if ($this->getUniResult($query, $note) > 0) {
            $result->add('note', $note);
            $result->add('title', "{$request->get('year')}-{$request->get('month')}-{$request->get('day')} の停止日・稼働日のコメント編集");
            return true;
        } else {
            return false;
        }
    }
    
    ////////// MVC の Model 部の結果 表示用のデータ取得
    ///// List部    データの明細 一覧表
    public function outViewListHTML($request, $menu, $uniq)
    {
                /***** ヘッダー部を作成 *****/
        // 固定のHTMLソースを取得
        $headHTML  = $this->getViewHTMLconst('header', $uniq);
        // 可変部のHTMLソースを取得
        $headHTML .= $this->getViewHTMLheader($request);
        // 固定のHTMLソースを取得
        $headHTML .= $this->getViewHTMLconst('footer', $uniq);
        // HTMLファイル出力
        $file_name = "list/assembly_calendar_ViewListHeader-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        
                /***** 本文を作成 *****/
        // 固定のHTMLソースを取得
        $listHTML  = $this->getViewHTMLconst('header', $uniq);
        // 可変部のHTMLソースを取得
        $listHTML .= $this->getViewHTMLbody($request, $menu);
        // 固定のHTMLソースを取得
        $listHTML .= $this->getViewHTMLconst('footer', $uniq);
        // HTMLファイル出力
        $file_name = "list/assembly_calendar_ViewListBody-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        
                /***** フッター部を作成 *****/
        // 固定のHTMLソースを取得
        $footHTML  = $this->getViewHTMLconst('header', $uniq);
        // 可変部のHTMLソースを取得
        $footHTML .= $this->getViewHTMLfooter();
        // 固定のHTMLソースを取得
        $footHTML .= $this->getViewHTMLconst('footer', $uniq);
        // HTMLファイル出力
        $file_name = "list/assembly_calendar_ViewListFooter-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $footHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        return ;
    }
    
    ///// 対象日の時間編集を行うため明細取得
    public function getTimeDetail($request, $result, $flg=1)
    {
        // パラメーターチェック
        if ($request->get('year') == '') {
            $_SESSION['s_sysmsg'] = '対象日が取得出来ませんでした。';
            return false;
        }
        if ($request->get('month') == '') {
            $_SESSION['s_sysmsg'] = '対象日が取得出来ませんでした。';
            return false;
        }
        if ($request->get('day') == '') {
            $_SESSION['s_sysmsg'] = '対象日が取得出来ませんでした。';
            return false;
        }
        if ($flg == 1) {
            return $this->getTimeDetailExecute($request, $result);
        } else {
            return $this->setTimeCopyExecute($request, $result);
        }
    }
    
    ///// 稼働時間・停止時間の編集用 hour <select> option の出力
    public function getHourValues($para_hour)
    {
        // 初期化
        $option = "\n";
        for ($i=0; $i<=24; $i++) {  // 23 → 24 にした理由は24時間連続稼動があるため、24時間を超えるチェックを行う
            $hour = sprintf('%02d', $i);
            $mbHour = mb_convert_kana($hour, 'N');
            if ($para_hour == $i) {
                $option .= "<option value='{$hour}' selected>{$mbHour}</option>\n";
            } else {
                $option .= "<option value='{$hour}'>{$mbHour}</option>\n";
            }
        }
        // 例 <option value='08'>０８</option>
        return $option;
    }
    
    ///// 稼働時間・停止時間の編集用 minute <select> option の出力
    public function getMinuteValues($para_minute)
    {
        // 初期化
        $option = "\n";
        for ($i=0; $i<=59; $i++) {
            $minute = sprintf('%02d', $i);
            $mbMinute = mb_convert_kana($minute, 'N');
            if ($para_minute == $i) {
                $option .= "<option value='{$minute}' selected>{$mbMinute}</option>\n";
            } else {
                $option .= "<option value='{$minute}'>{$mbMinute}</option>\n";
            }
        }
        // 例 <option value='35'>３５</option>
        return $option;
    }
    
    ///// 稼働時間・停止時間の編集データ保存
    public function timeSave($request)
    {
        // データの存在チェック
        if ($request->get('day')   == '')       return;
        if ($request->get('str_hour')   == '')  return;
        if ($request->get('str_minute') == '')  return;
        if ($request->get('end_hour')   == '')  return;
        if ($request->get('end_minute') == '')  return;
        // 稼働時間の登録・削除か停止時間の登録・削除かチェック
        if ($request->get('bdSave') != '') {    // 稼働時間の登録
            // データの変更チェック
            if ($request->get('old_str_time') == "{$request->get('str_hour')}:{$request->get('str_minute')}") {
                if ($request->get('old_end_time') == "{$request->get('end_hour')}:{$request->get('end_minute')}") {
                    if ($request->get('old_bh_note') == $request->get('bh_note')) {
                        return;
                    }
                }
            }
            // 開始時間と終了時間の適正チェック
            if ("{$request->get('str_hour')}{$request->get('str_minute')}" >= "{$request->get('end_hour')}{$request->get('end_minute')}") {
                $_SESSION['s_sysmsg'] = '稼働時間が同じか逆転しています。';
                return;
            }
            // 登録実行
            $this->bhTimeSaveExecute($request);
        } elseif ($request->get('bdDelete') != '') {    // 稼働時間の削除
            // 削除実行
            $this->bhTimeDeleteExecute($request);
        } elseif ($request->get('atSave') != '') {      // 停止時間の登録
            // データの変更チェック
            if ($request->get('old_str_time') == "{$request->get('str_hour')}:{$request->get('str_minute')}") {
                if ($request->get('old_end_time') == "{$request->get('end_hour')}:{$request->get('end_minute')}") {
                    if ($request->get('old_absent_note') == $request->get('absent_note')) {
                        return;
                    }
                }
            }
            // 開始時間と終了時間の適正チェック
            if ("{$request->get('str_hour')}{$request->get('str_minute')}" >= "{$request->get('end_hour')}{$request->get('end_minute')}") {
                $_SESSION['s_sysmsg'] = '停止時間が同じか逆転しています。';
                return;
            }
            // 登録実行
            $this->atTimeSaveExecute($request);
        } elseif ($request->get('atDelete') != '') {    // 停止時間の削除
            // 削除実行
            $this->atTimeDeleteExecute($request);
        }
        return;
    }
    
    ///// 対象期のカレンダーを初期化するため一旦削除する
    public function deleteCalendar($request)
    {
        if (!$this->getAuthority($_SESSION['User_ID'], $this->authDiv)) {
            return false;
        }
        // パラメーターチェック(内容はチェック済み)
        if ($request->get('targetDateStr') == '') {
            $_SESSION['s_sysmsg'] = '開始日が取得出来ませんでした。';
            return false;
        }
        if ($request->get('targetDateEnd') == '') {
            $_SESSION['s_sysmsg'] = '終了日が取得出来ませんでした。';
            return false;
        }
        // DBのコネクション取得
        if ($con = $this->connectDB()) {
            // トランザクション開始
            $this->query_affected_trans($con, 'BEGIN');
        } else {
            $_SESSION['s_sysmsg'] = 'DBシステムエラー';
            return false;
        }
        $query = "
            DELETE FROM assembly_calendar
            WHERE line = '{$request->get('targetLine')}' AND tdate >= DATE '{$request->get('targetDateStr')}01' AND tdate <= DATE '{$request->get('targetDateEnd')}31'
        ";
        if ($this->query_affected_trans($con, $query) <= 0) {   // カレンダーはデータがあるため <= に注意
            $_SESSION['s_sysmsg'] = 'カレンダーの初期化出来ませんでした！　管理担当者へ連絡して下さい。';
            $this->query_affected_trans($con, 'ROLLBACK');
            return false;
        } else {
            $query = "
                DELETE FROM assembly_plan_hours
                WHERE line = '{$request->get('targetLine')}' AND tdate >= DATE '{$request->get('targetDateStr')}01' AND tdate <= DATE '{$request->get('targetDateEnd')}31'
            ";
            if ($this->query_affected_trans($con, $query) < 0) {
                $_SESSION['s_sysmsg'] = '稼働時間の初期化出来ませんでした！　管理担当者へ連絡して下さい。';
                $this->query_affected_trans($con, 'ROLLBACK');
                return false;
            } else {
                $query = "
                    DELETE FROM assembly_absent_time
                    WHERE line = '{$request->get('targetLine')}' AND tdate >= DATE '{$request->get('targetDateStr')}01' AND tdate <= DATE '{$request->get('targetDateEnd')}31'
                ";
                if ($this->query_affected_trans($con, $query) < 0) {
                    $_SESSION['s_sysmsg'] = '停止時間の初期化出来ませんでした！　管理担当者へ連絡して下さい。';
                    $this->query_affected_trans($con, 'ROLLBACK');
                    return false;
                }
            }
        }
        $this->query_affected_trans($con, 'COMMIT');
        // $_SESSION['s_sysmsg'] = '初期化完了しました。'; Ajaxのためコメント
        return true;
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// リクエストによりSQL文の基本WHERE区を設定
    protected function SetInitWhere($request)
    {
        // このメソッドは現在使用していない
        $year  = $request->get('year');
        $month = $request->get('month');
        if ($month == 12) {
            $endYear  = $year + 1;
            $endMonth = '01';
        } else {
            $endYear  = $year;
            $endMonth = $month + 1;
            $endMonth = sprintf('%02d', $endMonth);
        }
        $where = "
            calen.tdate >= DATE '{$year}-{$month}-01' AND calen.tdate < DATE '{$endYear}-{$endMonth}-01'
        ";
        return $where;
    }
    
    ////////// カレンダーの明細データ取得 実行部
    protected function getCalendarDetail($request, $result)
    {
        ///// カレンダーの明細 取得
        $query = "
            SELECT bd_flg, note FROM assembly_calendar WHERE line = '{$request->get('targetLine')}' AND tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
        ";
        if ($this->getResult2($query, $res) <= 0) {
            $_SESSION['s_sysmsg'] = 'カレンダーのデータがありません。';
            return false;   // システムエラーなので続行しない
        }
        $result->add('bd_flg',  $res[0][0]);
        $result->add('bd_note', $res[0][1]);
        return true;
    }
    
    ////////// 時間編集用の明細データ取得 実行部
    protected function getTimeDetailExecute($request, $result)
    {
        ///// カレンダーの明細 取得
        if (!$this->getCalendarDetail($request, $result)) {
            return false;
        }
        ///// 稼働時間の取得
        $query = "
            SELECT
                 to_char(str_time, 'HH24')      AS str_hour
                ,to_char(str_time, 'MI')        AS str_minute
                ,to_char(end_time, 'HH24')      AS end_hour
                ,to_char(end_time, 'MI')        AS end_minute
                ,hours                          AS hours
                ,note                           AS bh_note
            FROM
                assembly_plan_hours
            WHERE
                line = '{$request->get('targetLine')}' AND tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
            ORDER BY tdate DESC -- <= → = に変更(実際に登録されているデータのみにした)
            LIMIT 1
        ";
        $res = array();
        if ($this->getResult2($query, $res) <= 0) {
            // $_SESSION['s_sysmsg'] = '稼働時間のデータがありません。';
            // return false;    // 初回登録を考慮してコメントにする
        } else {
            $result->add('str_hour',   $res[0][0]);
            $result->add('str_minute', $res[0][1]);
            $result->add('end_hour',   $res[0][2]);
            $result->add('end_minute', $res[0][3]);
            $result->add('hours',      $res[0][4]);
            $result->add('bh_note',    $res[0][5]);
        }
        ///// 停止時間の取得
        $query = "
            SELECT
                 to_char(str_time, 'HH24')      AS str_hour
                ,to_char(str_time, 'MI')        AS str_minute
                ,to_char(end_time, 'HH24')      AS end_hour
                ,to_char(end_time, 'MI')        AS end_minute
                ,absent_time                    AS absent_time
                ,note                           AS absent_note
            FROM
                assembly_absent_time
            WHERE
                line = '{$request->get('targetLine')}' AND tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
            ORDER BY
                str_time ASC
        ";
        $res = array(); // 初期化
        if (($rows=$this->getResult($query, $res)) <= 0) {
            // 過去の最終登録で停止時間の取得をしていたロジックを削除
            // $_SESSION['s_sysmsg'] = '停止時間の取得が出来ませんでした。';
            // return false;    // 初回登録を考慮してコメントにする
        }
        $result->add_array($res);
        $result->add('array_rows', $rows);
        return true;
    }
    
    ////////// 時間編集用の過去の直近データコピー保存 実行部
    protected function setTimeCopyExecute($request, $result)
    {
        if (!$this->getAuthority($_SESSION['User_ID'], $this->authDiv)) {
            return false;
        }
        ///// 稼働時間のコピー
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        $sql = "
            INSERT INTO assembly_plan_hours (line, tdate, str_time, end_time, hours, note, last_date, last_user)
            SELECT
                 '{$request->get('targetLine')}'
                ,DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
                ,str_time
                ,end_time
                ,hours
                ,note
                ,'{$last_date}'
                ,'{$last_user}'
            FROM
                assembly_plan_hours
            WHERE
                line = '{$request->get('targetLine')}' AND tdate <= DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
            ORDER BY tdate DESC -- = → <= に注意(実際には<が正確)
            LIMIT 1
        ";
        if ($this->query_affected($sql) <= 0) {
            $_SESSION['s_sysmsg'] = 'コピーする稼働時間のデータがありません。';
        } else {
            $_SESSION['s_sysmsg'] = '稼働時間のデータをコピーしました。';
        }
        ///// 停止時間のコピー
        // 対象データの有り無しチェック
        $query = "
            SELECT tdate FROM assembly_absent_time WHERE line = '{$request->get('targetLine')}' AND tdate <= DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
            ORDER BY tdate DESC
            LIMIT 1
        ";
        if ($this->getUniResult($query, $tdate) <= 0) {
            if ($_SESSION['s_sysmsg'] == '') {
                $_SESSION['s_sysmsg'] = 'コピーする停止時間のデータがありません。';
            } else {
                $_SESSION['s_sysmsg'] .= '\n\nコピーする停止時間のデータがありません。';
            }
        } else {
            $sql = "
                INSERT INTO assembly_absent_time (line, tdate, str_time, end_time, absent_time, note, last_date, last_user)
                SELECT
                     '{$request->get('targetLine')}'
                    ,DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
                    ,str_time
                    ,end_time
                    ,absent_time
                    ,note
                    ,'{$last_date}'
                    ,'{$last_user}'
                FROM
                    assembly_absent_time
                WHERE
                    line = '{$request->get('targetLine')}' AND tdate = '{$tdate}'
                ORDER BY
                    str_time ASC
            ";
            if ($this->query_affected($sql) <= 0) {
                if ($_SESSION['s_sysmsg'] == '') {
                    $_SESSION['s_sysmsg'] = '停止時間のコピーに失敗しました。';
                } else {
                    $_SESSION['s_sysmsg'] .= '\n\n停止時間のコピーに失敗しました。';
                }
            } else {
                if ($_SESSION['s_sysmsg'] == '') {
                    $_SESSION['s_sysmsg'] = '停止時間のデータをコピーしました。';
                } else {
                    $_SESSION['s_sysmsg'] .= '\n\n停止時間のデータをコピーしました。';
                }
            }
        }
        return true;
    }
    
    ////////// 稼働時間の保存 実行部
    protected function bhTimeSaveExecute($request)
    {
        if (!$this->getAuthority($_SESSION['User_ID'], $this->authDiv)) {
            return false;
        }
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        // データの存在チェック
        $query = "
            SELECT tdate FROM assembly_plan_hours WHERE line = '{$request->get('targetLine')}' AND tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
        ";
        if ($this->getUniResult($query, $check) <= 0) {
            // データ無しinsert
            $sql = "
                INSERT INTO assembly_plan_hours (line, tdate, str_time, end_time, hours, note, last_date, last_user)
                VALUES (
                    '{$request->get('targetLine')}' ,
                    DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}' ,
                    TIME '{$request->get('str_hour')}:{$request->get('str_minute')}:00' ,
                    TIME '{$request->get('end_hour')}:{$request->get('end_minute')}:00' ,
                    EXTRACT(HOUR FROM (TIME '{$request->get('end_hour')}:{$request->get('end_minute')}:00' - TIME '{$request->get('str_hour')}:{$request->get('str_minute')}:00')) * 60 + EXTRACT(MINUTE FROM (TIME '{$request->get('end_hour')}:{$request->get('end_minute')}:00' - TIME '{$request->get('str_hour')}:{$request->get('str_minute')}:00')) ,
                    '{$request->get('bh_note')}' ,
                    '{$last_date}' , '{$last_user}'
                )
            ";
        } else {
            // データありupdate
            $sql = "
                UPDATE assembly_plan_hours SET
                str_time = TIME '{$request->get('str_hour')}:{$request->get('str_minute')}:00' ,
                end_time = TIME '{$request->get('end_hour')}:{$request->get('end_minute')}:00' ,
                hours = EXTRACT(HOUR FROM (TIME '{$request->get('end_hour')}:{$request->get('end_minute')}:00' - TIME '{$request->get('str_hour')}:{$request->get('str_minute')}:00')) * 60 + EXTRACT(MINUTE FROM (TIME '{$request->get('end_hour')}:{$request->get('end_minute')}:00' - TIME '{$request->get('str_hour')}:{$request->get('str_minute')}:00')) ,
                note = '{$request->get('bh_note')}' ,
                last_date = '{$last_date}', last_user = '{$last_user}'
                WHERE line = '{$request->get('targetLine')}' AND tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
            ";
        }
        if ($this->query_affected($sql) <= 0) {
            $_SESSION['s_sysmsg'] = '稼働時間の登録に失敗しました！　管理担当者へ連絡して下さい。';
        } else {
            $_SESSION['s_sysmsg'] = '稼働時間を登録しました。';
        }
        return;
    }
    
    ////////// 稼働時間の削除 実行部
    protected function bhTimeDeleteExecute($request)
    {
        if (!$this->getAuthority($_SESSION['User_ID'], $this->authDiv)) {
            return false;
        }
        // 停止時間の登録チェック
        $query = "
            SELECT tdate FROM assembly_absent_time WHERE line = '{$request->get('targetLine')}' AND tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
        ";
        if ($this->getResult2($query, $check) > 0) {
            $_SESSION['s_sysmsg'] = '先に停止時間を削除して下さい。';
            return;
        }
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        // データの存在チェック
        $query = "
            SELECT tdate FROM assembly_plan_hours WHERE line = '{$request->get('targetLine')}' AND tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
        ";
        if ($this->getUniResult($query, $check) <= 0) {
            // データ無し 登録されていない部分の削除か、既に他のクライアントが削除
            return;
        } else {
            // データあり(ログを処理するか検討中)
            $sql = "
                DELETE FROM assembly_plan_hours WHERE line = '{$request->get('targetLine')}' AND tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
            ";
        }
        if ($this->query_affected($sql) <= 0) {
            $_SESSION['s_sysmsg'] = '稼働時間の削除に失敗しました！　管理担当者へ連絡して下さい。';
        } else {
            $_SESSION['s_sysmsg'] = '稼働時間を削除しました。';
        }
        return;
    }
    
    ////////// 停止時間の保存 実行部
    protected function atTimeSaveExecute($request)
    {
        if (!$this->getAuthority($_SESSION['User_ID'], $this->authDiv)) {
            return false;
        }
        // 稼働時間の登録チェック
        $query = "
            SELECT to_char(str_time, 'HH24MI'), to_char(end_time, 'HH24MI') FROM assembly_plan_hours
            WHERE
                line = '{$request->get('targetLine')}' AND tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
        ";
        $res = array();
        if ($this->getResult2($query, $res) <= 0) {
            $_SESSION['s_sysmsg'] = '稼働時間を先に登録して下さい。';
            return;
        }
        // 稼働時間内の停止かチェック
        if ($res[0][0] > "{$request->get('str_hour')}{$request->get('str_minute')}") {
            $_SESSION['s_sysmsg'] = '開始時間が稼働時間外です。';
            return;
        }
        if ($res[0][1] < "{$request->get('end_hour')}{$request->get('end_minute')}") {
            $_SESSION['s_sysmsg'] = '終了時間が稼働時間外です。';
            return;
        }
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        // データの重複チェック
        if (!$this->atTimeDuplicate($request)) {
            return;
        }
        // OLD VALUE のチェック
        if (str_replace(':', '', $request->get('old_str_time')) == '') {
            // データ無しinsert
            $sql = "
                INSERT INTO assembly_absent_time (line, tdate, str_time, end_time, absent_time, note, last_date, last_user)
                VALUES (
                    '{$request->get('targetLine')}' ,
                    DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}' ,
                    TIME '{$request->get('str_hour')}:{$request->get('str_minute')}:00' ,
                    TIME '{$request->get('end_hour')}:{$request->get('end_minute')}:00' ,
                    EXTRACT(HOUR FROM (TIME '{$request->get('end_hour')}:{$request->get('end_minute')}:00' - TIME '{$request->get('str_hour')}:{$request->get('str_minute')}:00')) * 60 + EXTRACT(MINUTE FROM (TIME '{$request->get('end_hour')}:{$request->get('end_minute')}:00' - TIME '{$request->get('str_hour')}:{$request->get('str_minute')}:00')) ,
                    '{$request->get('absent_note')}' ,
                    '{$last_date}' , '{$last_user}'
                )
            ";
        } else {
            // データありupdate
            $sql = "
                UPDATE assembly_absent_time SET
                    str_time = TIME '{$request->get('str_hour')}:{$request->get('str_minute')}:00' ,
                    end_time = TIME '{$request->get('end_hour')}:{$request->get('end_minute')}:00' ,
                    absent_time = EXTRACT(HOUR FROM (TIME '{$request->get('end_hour')}:{$request->get('end_minute')}:00' - TIME '{$request->get('str_hour')}:{$request->get('str_minute')}:00')) * 60 + EXTRACT(MINUTE FROM (TIME '{$request->get('end_hour')}:{$request->get('end_minute')}:00' - TIME '{$request->get('str_hour')}:{$request->get('str_minute')}:00')) ,
                    note = '{$request->get('absent_note')}' ,
                    last_date = '{$last_date}', last_user = '{$last_user}'
                WHERE
                    line = '{$request->get('targetLine')}' AND tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
                AND
                    str_time = TIME '{$request->get('old_str_time')}:00'
                AND
                    end_time = TIME '{$request->get('old_end_time')}:00'
            ";
        }
        if ($this->query_affected($sql) <= 0) {
            $_SESSION['s_sysmsg'] = '停止時間の登録に失敗しました！　管理担当者へ連絡して下さい。';
        } else {
            $_SESSION['s_sysmsg'] = '停止時間を登録しました。';
        }
        return;
    }
    
    ////////// 停止時間の重複チェック
    protected function atTimeDuplicate($request)
    {
        // OLD VALUE のチェック
        if ($request->get('old_str_time') != '') {  // 変更の場合
            $query = "
            SELECT tdate FROM assembly_absent_time
            WHERE
                line = '{$request->get('targetLine')}' AND tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
            AND
                str_time != TIME '{$request->get('old_str_time')}:00'
            AND
                end_time != TIME '{$request->get('old_end_time')}:00'
            AND
                (str_time, end_time) OVERLAPS (TIME'{$request->get('str_hour')}:{$request->get('str_minute')}:00', TIME '{$request->get('end_hour')}:{$request->get('end_minute')}:00')
            ";
        } else {    // 追加の場合
            $query = "
            SELECT tdate FROM assembly_absent_time
            WHERE
                line = '{$request->get('targetLine')}' AND tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
            AND
                (str_time, end_time) OVERLAPS (TIME'{$request->get('str_hour')}:{$request->get('str_minute')}:00', TIME '{$request->get('end_hour')}:{$request->get('end_minute')}:00')
            ";
        }
        if ($this->getResult2($query, $check) >= 1) {
            $_SESSION['s_sysmsg'] = '停止時間が他と重複しています。';
            return false;
        } else {
            return true;
        }
    }
    
    ////////// 停止時間の削除 実行部
    protected function atTimeDeleteExecute($request)
    {
        if (!$this->getAuthority($_SESSION['User_ID'], $this->authDiv)) {
            return false;
        }
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        // データの存在チェック
        $query = "
            SELECT tdate FROM assembly_absent_time WHERE line = '{$request->get('targetLine')}' AND tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
            AND
                str_time = TIME '{$request->get('old_str_time')}:00'
            AND
                end_time = TIME '{$request->get('old_end_time')}:00'
        ";
        if ($this->getUniResult($query, $check) <= 0) {
            // データ無し 登録されていない部分の削除か、既に他のクライアントが削除
            return;
        } else {
            // データあり(ログを処理するか検討中)
            $sql = "
                DELETE FROM assembly_absent_time WHERE line = '{$request->get('targetLine')}' AND tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
                AND
                    str_time = TIME '{$request->get('old_str_time')}:00'
                AND
                    end_time = TIME '{$request->get('old_end_time')}:00'
            ";
        }
        if ($this->query_affected($sql) <= 0) {
            $_SESSION['s_sysmsg'] = '停止時間の削除に失敗しました！　管理担当者へ連絡して下さい。';
        } else {
            $_SESSION['s_sysmsg'] = '停止時間を削除しました。';
        }
        return;
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ///// List部   一覧表の ヘッダー部を作成
    private function getViewHTMLheader($request)
    {
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width='12%'>月日 (曜日)</th>\n";
        $listTable .= "        <th class='winbox' width='15%'>稼停コメント</th>\n";
        $listTable .= "        <th class='winbox' width='11%'>開始</th>\n";
        $listTable .= "        <th class='winbox' width='11%'>終了</th>\n";
        $listTable .= "        <th class='winbox' width='12%'>時間(分)</th>\n";
        $listTable .= "        <th class='winbox' width='15%'>稼時コメント</th>\n";
        $listTable .= "        <th class='winbox' width='12%'>停止(分)</th>\n";
        $listTable .= "        <th class='winbox' width='12%'>実時(分)</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        return $listTable;
    }
    
    ///// List部   クリックされたカレンダー１ヶ月間のリスト 明細データ作成
    private function getViewHTMLbody($request, $menu)
    {
        $query = $this->getQueryStatement($request);
        
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='100%' align='center' class='winbox'>データがありません。</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        } else {
            $week = array('日', '月', '火', '水', '木', '金', '土');
            for ($i=0; $i<$rows; $i++) {
                $dayWeek = $week[date('w', mktime(0, 0, 0, $res[$i][12], $res[$i][13], $res[$i][11]))];
                if ($res[$i][10] == 't') {
                    $listTable .= "    <tr style='font-weight:bold;'\n";
                    $listTable .= "        onClick='AssemblyCalendar.win_open(\"{$menu->out_self()}?showMenu=TimeEdit&year={$res[$i][11]}&month={$res[$i][12]}&day={$res[$i][13]}\", 800, 600, \"timeEditWin\");'\n";
                    $listTable .= "        title='{$res[$i][12]}/{$res[$i][13]}の稼働時間と停止時間の編集を行います。'\n";
                    $listTable .= "        onMouseover=\"this.style.backgroundColor='#ceffce'; this.style.color='black'; this.style.cursor='hand'; \"\n";
                    $listTable .= "        onMouseout =\"this.style.backgroundColor=''; this.style.color=''; this.style.cursor='auto'; \"\n";
                    $listTable .= "    >\n";
                } else {
                    $listTable .= "    <tr style='color:white;'\n";
                    $listTable .= "        onClick='AssemblyCalendar.win_open(\"{$menu->out_self()}?showMenu=TimeEdit&year={$res[$i][11]}&month={$res[$i][12]}&day={$res[$i][13]}\", 800, 600, \"timeEditWin\");'\n";
                    $listTable .= "        title='{$res[$i][12]}/{$res[$i][13]}の稼働・停止日の切替及びコメントの編集を行います。'\n";
                    $listTable .= "        onMouseover=\"this.style.backgroundColor='#ceffce'; this.style.color='red'; this.style.cursor='hand'; \"\n";
                    $listTable .= "        onMouseout =\"this.style.backgroundColor=''; this.style.color='white'; this.style.cursor='auto'; \"\n";
                    $listTable .= "    >\n";
                }
                $listTable .= "        <td class='winbox' width='12%' align='center'>{$res[$i][0]} ({$dayWeek})</td>\n"; // 月日(曜日)
                $listTable .= "        <td class='winbox' width='15%' align='left'  >{$res[$i][1]}</td>\n";         // コメント
                if ($res[$i][3] == '') $res[$i][3] = '&nbsp;';
                $listTable .= "        <td class='winbox' width='11%' align='center'>{$res[$i][3]}</td>\n";         // 開始時間
                if ($res[$i][4] == '') $res[$i][4] = '&nbsp;';
                $listTable .= "        <td class='winbox' width='11%' align='center'>{$res[$i][4]}</td>\n";         // 終了時間
                $listTable .= "        <td class='winbox' width='12%' align='right' ><span style='color:blue;'>{$res[$i][2]}</span>".number_format($res[$i][5])."</td>\n";// 稼働時間(分)
                if ($res[$i][6] == '') $res[$i][6] = '&nbsp;';
                $listTable .= "        <td class='winbox' width='15%' align='left'  >{$res[$i][6]}</td>\n";         // 稼働コメント
                $listTable .= "        <td class='winbox' width='12%' align='right' ><span style='color:blue;'>{$res[$i][7]}</span>".number_format($res[$i][8])."</td>\n";// 停止時間合計(分)
                $listTable .= "        <td class='winbox' width='12%' align='right' >".number_format($res[$i][5]-$res[$i][8])."</td>\n";// 実時(分)
                $listTable .= "    </tr>\n";
                if ($res[$i][10] == 't') {
                    $this->sumBusinessHours += $res[$i][5];
                    $this->sumAbsentTime    += $res[$i][8];
                }
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
            $this->netBusinessHours = ($this->sumBusinessHours - $this->sumAbsentTime);
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List部   一覧表の フッター部を作成
    private function getViewHTMLfooter()
    {
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox' width='38%' align='right'>&nbsp;</td>\n";
        $listTable .= "        <td class='winbox' width='11%' align='right'>合計</td>\n";   // 稼働時間
        $listTable .= "        <td class='winbox' width='12%' align='right'>".number_format($this->sumBusinessHours)."</td>\n";
        $listTable .= "        <td class='winbox' width='15%' align='right'>&nbsp;</td>\n";   // 停止時間
        $listTable .= "        <td class='winbox' width='12%' align='right'>".number_format($this->sumAbsentTime)."</td>\n";
        $listTable .= "        <td class='winbox' width='12%' align='right'>".number_format($this->netBusinessHours)."</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        return $listTable;
    }
    
    ///// List部   一覧表のSQLステートメント取得
    private function getQueryStatement($request)
    {
        $year  = $request->get('year');
        $month = $request->get('month');
        if ($month == 12) {
            $endYear  = $year + 1;
            $endMonth = '01';
        } else {
            $endYear  = $year;
            $endMonth = $month + 1;
            $endMonth = sprintf('%02d', $endMonth);
        }
        $endYMD = date('Y-m-d', mktime(0, 0, 0, $endMonth, 0, $endYear));
        $query = "
            SELECT
                  to_char(tdate, 'MM/DD')
                                            AS 日付         -- 00
                , CASE
                    WHEN bd_note = '' THEN '&nbsp;'
                    ELSE bd_note
                  END                       AS コメント     -- 01
                , CASE
                    WHEN bh_flg THEN '＊'
                    ELSE ''
                  END                       AS 稼働時間登録 -- 02
                , to_char(bh_str_time, 'HH24:MI')
                                            AS 開始時間     -- 03
                , to_char(bh_end_time, 'HH24:MI')
                                            AS 終了時間     -- 04
                , bh_hours                  AS 稼働時間     -- 05
                , CASE
                    WHEN bh_note IS NULL THEN '&nbsp;'
                    WHEN bh_note = '' THEN '&nbsp;'
                    ELSE bh_note
                  END                       AS 稼働コメント -- 06
                , CASE
                    WHEN at_flg THEN '＊'
                    ELSE ''
                  END                       AS 停止時間登録 -- 07
                , at_sum                    AS 停止合計時間 -- 08
                , at_count                  AS 停止回数     -- 09
                --------------------------------------------以下はリスト外
                , bd_flg                    AS 稼働停止日     -- 10
                , to_char(tdate, 'YYYY')
                                            AS 年           -- 11
                , to_char(tdate, 'MM')
                                            AS 月           -- 12
                , to_char(tdate, 'DD')
                                            AS 日           -- 13
            FROM
                assembly_calendar_schedule('{$request->get('targetLine')}', DATE '{$year}-{$month}-01', DATE '{$endYMD}', FALSE)
            ORDER BY
                tdate ASC
        ";
        return $query;
    }
    
    ///// 固定のList部    HTMLファイル出力
    private function getViewHTMLconst($status, $uniq)
    {
        if ($status == 'header') {
            $listHTML = 
"<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title>稼働時間・停止時間の編集</title>
<script type='text/javascript' src='/base_class.js'?id={$uniq}></script>
<link rel='stylesheet' href='/menu_form.css?id={$uniq}' type='text/css' media='screen'>
<link rel='stylesheet' href='../assembly_calendar.css?id={$uniq}' type='text/css' media='screen'>
<script type='text/javascript' src='../assembly_calendar.js?id={$uniq}'></script>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
</head>
<body style='background-color:#d6d3ce;'>  <!--  -->
<center>
";
        } elseif ($status == 'footer') {
            $listHTML = "\n";   // 初期化
            if ($_SESSION['s_sysmsg'] == '') {
                $listHTML .= "</center>\n";
                $listHTML .= "</body>\n";
                $listHTML .= "</html>\n";
            } else {
                $listHTML .= "</center>\n";
                $listHTML .= "</body>\n";
                $listHTML .= "<script type='text/javascript'>\n";
                $listHTML .= "    alert('{$_SESSION['s_sysmsg']}');\n";
                $listHTML .= "</script>\n";
                $listHTML .= "</html>\n";
                $_SESSION['s_sysmsg'] = '';     // メッセージクリア
            }
        } else {
            $listHTML = '';
        }
        return $listHTML;
    }
    
    ///// カレンダー 固定のHTMLファイル出力
    private function getViewCalendarHTMLconst($status, $uniq)
    {
        if ($status == 'header') {
            $listHTML = 
"<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title>組立ラインのカレンダー</title>
<script type='text/javascript' src='/base_class.js?id={$uniq}'></script>
<link rel='stylesheet' href='/menu_form.css?id={$uniq}' type='text/css' media='screen'>
<link rel='stylesheet' href='../calendar.css?id={$uniq}' type='text/css' media='screen'>
<link rel='stylesheet' href='../assembly_calendar.css?id={$uniq}' type='text/css' media='screen'>
<script type='text/javascript' src='../assembly_calendar.js?id={$uniq}'></script>
<style type='text/css'>
<!--
body {
    background-image:none;
    background-color:transparent;
}
-->
</style>
</head>
<body>
<center>
";
        } elseif ($status == 'footer') {
            $listHTML = "\n";   // 初期化
            if ($_SESSION['s_sysmsg'] == '') {
                $listHTML .= "</center>\n";
                $listHTML .= "</body>\n";
                $listHTML .= "</html>\n";
            } else {
                $listHTML .= "</center>\n";
                $listHTML .= "</body>\n";
                $listHTML .= "<script type='text/javascript'>\n";
                $listHTML .= "    alert('{$_SESSION['s_sysmsg']}');\n";
                $listHTML .= "</script>\n";
                $listHTML .= "</html>\n";
                $_SESSION['s_sysmsg'] = '';     // メッセージクリア
            }
        } else {
            $listHTML = '';
        }
        return $listHTML;
    }
    
    ////////// 編集権限チェック
    private function getAuthority($id, $division)
    {
        /******************************
        switch ($id) {
        case '010561':  // 小林
        case '300101':  // 大谷(旧)
        case '300144':  // 大谷
        case '009580':  // 五十嵐
        case '008982':  // 笹崎
        case '011045':  // 八木沢
        case '011011':  // 千田
        case '016951':  // 和気
        case '011061':  // 小森谷
        case '010529':  // 飯島
        case '013013':  // 鈴木
        case '017507':  // 小山
        case '018058':  // 川上
        case '300161':  // 斎藤千尋
        case '007340':  // 千葉
        case '007315':  // 安達
        case '014834':  // 石崎
            return true;
        }
        $_SESSION['s_sysmsg'] = '編集権限がありません。必要な場合には、担当者に連絡して下さい。';
        return false;
        ******************************/
        
        ///// DAO クラスより取得
        // 権限No.のみでチェック
        if ($this->getCheckAuthority($division)) {
            return true;    // 権限あり
        }
        // $divisionがその他なら以下の書式 (権限No.と権限No.に対応するID)
        // if ($this->getCheckAuthority($division, $act_id)) {
        //     return true;
        // }
        $_SESSION['s_sysmsg'] = '編集権限がありません。必要な場合には、担当者に連絡して下さい。';
        return false;
    }
    
} // Class AssemblyCalendar_Model End

?>
