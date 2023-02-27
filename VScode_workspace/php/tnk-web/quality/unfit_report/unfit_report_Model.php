<?php
//////////////////////////////////////////////////////////////////////////////
// 全社共有 不適合報告書の照会・メンテナンス                                //
//                                                            MVC Model 部  //
// Copyright (C) 2008 Norihisa.Ohya usoumu@nitto-kohki.co.jp                //
// Changed history                                                          //
// 2008/05/30 Created   unfit_report_Model.php                              //
// 2008/08/29 masterstで本稼動開始                                          //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード

require_once ('../../ComTableMntClass.php');   // TNK 全共通 テーブルメンテ&ページ制御Class


/******************************************************************************
*     不適合報告書用 MVCのModel部 base class 基底クラスの定義     *
******************************************************************************/
class UnfitReport_Model extends ComTableMnt
{
    ////////// Private properties
    private $where;
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer の定義 (php5へ移行時は __construct() へ変更予定) (デストラクタ__destruct())
    public function __construct($request)
    {
        ////// 以下のリクエストはcontrollerより先に取得しているため空の場合がある。
        $year       = $request->get('year');
        $month      = $request->get('month');
        $day        = $request->get('day');
        $listSpan   = $request->get('listSpan');
        
        switch ($request->get('showMenu')) {
        case 'Group':
            $this->where = '';
            $sql_sum = "
                SELECT count(*) FROM (SELECT count(group_no) FROM meeting_mail_group GROUP BY group_no {$this->where})
                AS meeting_group
            ";
            break;
        case 'CompleteList':
        case 'Apend' :
        case 'Edit'  :
        case 'Print' :
        case 'Follow':
        default      :
            $this->where = "WHERE occur_time>='{$year}-{$month}-{$day}' AND occur_time<=(timestamp '{$year}-{$month}-{$day}' + interval '{$listSpan} day')";
            $sql_sum = "
                SELECT count(*) FROM unfit_report_header {$this->where}
            ";
            break;
        }
        ////// Constructer を定義すると 基底クラスの Constructerが実行されない
        ////// 基底ClassのConstructerはプログラマーの責任で呼出す
        parent::__construct($sql_sum, $request, 'unfit_report.log');
    }
    
    ////////// 不適合報告書の追加
    public function add($request)
    {
        ////// パラメーターの分割
        $year       = $request->get('yearReg');             // 発生年月日の年４桁
        $month      = $request->get('monthReg');            // 発生年月日の月２桁
        $day        = $request->get('dayReg');              // 発生年月日の日２桁
        $subject    = mb_convert_kana($request->get('subject'), 'KV'); // 不適合内容 全角変換
        $request->add('subject', $subject);
        $place      = $request->get('place');               // 発生場所
        $section    = $request->get('section');             // 責任部門
        $assy_no    = $request->get('assy_no');             // 製品番号
        $parts_no   = $request->get('parts_no');            // 部品番号
        $sponsor    = $request->get('sponsor');             // 作成者
        $atten      = $request->get('atten');               // 報告先(attendance) (配列)
        $mail       = $request->get('mail');                // メールの送信 Y/N
        ////// 年月日のチェック  現在は Main Controllerで初期値を設定しているので必要ないが、そのまま残す。
        if ($year == '') {
            // 本日の日付に設定
            $year = date('Y'); $month = date('m'); $day = date('d');
        }
        $serial_no = $this->add_execute($request);
        if ($serial_no) {
            if ($mail == 't') {
                if ($this->guideUnfitMail($request, $serial_no)) {
                    $_SESSION['s_sysmsg'] = 'メールを送信しました。';
                } else {
                    $_SESSION['s_sysmsg'] = 'メール送信できませんでした。';
                }
            }
            return true;
        } else {
            $_SESSION['s_sysmsg'] = '登録できませんでした。';
        }
        return false;
    }
    
    ////////// フォローアップの入力の追加
    public function follow($request)
    {
        ////// パラメーターの分割
        $serial_no      = $request->get('serial_no');           // シリアル番号
        $follow_section = $request->get('follow_section');      // フォローアップ 発行部門
        $follow_quality = $request->get('follow_quality');      // フォローアップ 品質保証課
        $follow_opinion = $request->get('follow_opinion');      // フォローアップ 意見
        $follow         = $request->get('follow');              // フォローアップ完了 Y/N
        $sponsor        = $request->get('sponsor');             // 作成者
        $atten          = $request->get('atten');               // 報告先(attendance) (配列)
        $mail           = $request->get('mail');                // メールの送信 Y/N
        $serial_no = $this->follow_execute($request);
        if ($serial_no) {
            if ($mail == 't') {
                if ($this->guideFollowMail($request, $serial_no)) {
                    $_SESSION['s_sysmsg'] = 'メールを送信しました。';
                } else {
                    $_SESSION['s_sysmsg'] = 'メール送信できませんでした。';
                }
            }
            return true;
        } else {
            $_SESSION['s_sysmsg'] = '登録できませんでした。';
        }
        return false;
    }
    
    ////////// 不適合報告書の完全削除
    public function delete($request)
    {
        ////// パラメーターの分割
        $serial_no  = $request->get('serial_no');           // シリアル番号
        $subject    = $request->get('subject');             // 不適合内容
        $mail       = $request->get('mail');                // メールの送信 Y/N
        ////// 対象不適合報告書の存在チェック
        $chk_sql = "
            SELECT subject FROM unfit_report_header WHERE serial_no={$serial_no}
        ";
        if ($this->getUniResult($chk_sql, $check) < 1) {     // 指定のシリアル番号の存在チェック
            $_SESSION['s_sysmsg'] = "「{$subject}」は他の人に変更されました！";
        } else {
            if ($mail == 't') {
                if ($this->guideUnfitMail($request, $serial_no, true)) {
                    $_SESSION['s_sysmsg'] = 'キャンセルのメールを送信しました。';
                } else {
                    $_SESSION['s_sysmsg'] = 'キャンセルのメール送信ができませんでした。';
                }
            }
            $response = $this->del_execute($serial_no, $subject);
            if ($response) {
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '削除できませんでした。';
            }
        }
        return false;
    }
    
    ////////// 不適合報告書の変更
    public function edit($request)
    {
        ////// パラメーターの分割
        $serial_no  = $request->get('serial_no');           // 連番(キーフィールド)
        $year       = $request->get('yearReg');             // 発生年月日の年４桁
        $month      = $request->get('monthReg');            // 発生年月日の月２桁
        $day        = $request->get('dayReg');              // 発生年月日予定の日２桁
        $subject    = mb_convert_kana($request->get('subject'), 'KV'); // 不適合内容 2005/12/27 全角変換追加
        $request->add('subject', $subject);
        $mail       = $request->get('mail');                // メールの送信 Y/N
        $reSend     = $request->get('reSend');              // 変更時のメールの再送信Yes/No
        ////// 年月日のチェック
        if ($year == '') {
            // 本日の日付に設定
            $year = date('Y'); $month = date('m'); $day = date('d');
        }
        
        $query = "
            SELECT subject FROM unfit_report_header WHERE serial_no={$serial_no}
        ";
        if ($this->getUniResult($query, $check) > 0) {  // 変更前のシリアル番号が登録されているか？
            $response = $this->edit_execute($request);
            if ($response) {
                if ($reSend == 't' && $mail == 't') {
                    if ($this->guideUnfitMail($request, $serial_no)) {
                        $_SESSION['s_sysmsg'] = 'メールを再送信しました。';
                    } else {
                        $_SESSION['s_sysmsg'] = 'メールの再送信ができませんでした。';
                    }
                }
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '変更できませんでした。';
            }
        } else {
            $_SESSION['s_sysmsg'] = "「{$subject}」は他の人に変更されました！";
        }
        return false;
    }
    
    ////////// 報告先グループの登録・変更
    public function group_edit($group_no, $group_name, $atten, $owner)
    {
        ////// group_noの適正チェック
        if (!$this->checkGroupNo($group_no)) {
            return false;
        }
        $query = "
            SELECT owner, group_no, group_name FROM meeting_mail_group WHERE group_no={$group_no}
        ";
        $res = array();
        if ($this->getResult2($query, $res) <= 0) {
            // グループの登録
            $response = $this->groupInsert($group_no, $group_name, $atten, $owner);
            if ($response) {
                $_SESSION['s_sysmsg'] = "[{$group_no}] {$group_name} を登録しました。";
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '報告先グループの登録が出来ませんでした！';
            }
        } else {
            // グループの変更
            // データが変更されているかチェック
            // $atten[]の配列があるため省略する
            // 持主が同じかチェック
            if ($res[0][0] != '000000' && $res[0][0] != $_SESSION['User_ID']) {
                $_SESSION['s_sysmsg'] = '個人のグループ登録です。 変更できません！';
                return false;
            }
            // グループの変更 実行
            $response = $this->groupUpdate($group_no, $group_name, $atten, $owner);
            if ($response) {
                $_SESSION['s_sysmsg'] = "[{$group_no}] {$group_name} を変更しました。";
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '報告先グループの変更が出来ませんでした！';
            }
        }
        return false;
    }
    
    ////////// 報告先グループの 削除
    public function group_omit($group_no, $group_name)
    {
        ////// group_noの適正チェック
        if (!$this->checkGroupNo($group_no)) {
            return false;
        }
        $query = "
            SELECT group_no, group_name FROM meeting_mail_group WHERE group_no={$group_no}
        ";
        if ($this->getResult2($query, $res) <= 0) {
            $_SESSION['s_sysmsg'] = "[{$group_no}] {$group_name} は削除対象データがありません！";
        } else {
            // 削除しても問題ないか過去のデータをチェックは今回は必要ない
            $response = $this->groupDelete($group_no);
            if ($response) {
                $_SESSION['s_sysmsg'] = "[{$group_no}] {$group_name} を削除しました。";
                return true;
            } else {
                $_SESSION['s_sysmsg'] = "[{$group_no}] {$group_name} を削除出来ませんでした！";
            }
        }
        return false;
    }
    
    ////////// 報告先グループの 有効・無効
    public function group_activeSwitch($group_no, $group_name)
    {
        ////// group_noの適正チェック
        if (!$this->checkGroupNo($group_no)) {
            return false;
        }
        $query = "
            SELECT active FROM meeting_mail_group WHERE group_no={$group_no}
        ";
        if ($this->getUniResult($query, $active) <= 0) {
            $_SESSION['s_sysmsg'] = "[{$group_no}] {$group_name} の対象データがありません！";
        } else {
            // ここに last_date last_host の登録処理を入れる
            // regdate=自動登録
            $last_date = date('Y-m-d H:i:s');
            $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']);
            if ($active == 't') {
                $active = 'FALSE';
            } else {
                $active = 'TRUE';
            }
            // 保存用のSQL文を設定
            $save_sql = "
                SELECT active FROM meeting_mail_group WHERE group_no={$group_no}
            ";
            $update_sql = "
                UPDATE meeting_mail_group SET
                active={$active}, last_date='{$last_date}', last_host='{$last_host}'
                WHERE group_no={$group_no}
            "; 
            return $this->execute_Update($update_sql, $save_sql);
        }
        return false;
    }
    
    ////////// MVC の Model 部の結果 表示用のデータ取得
    ////////// List部
    public function getViewList(&$result)
    {
        $query = "
            SELECT serial_no                            -- 00
                ,subject                                -- 01
                ,to_char(occur_time, 'YY/MM/DD')        -- 02
                ,section                                -- 03
                ,place                                  -- 04
                ,sponsor                                -- 05
                ,trim(name)             AS 氏名         -- 06
                ,atten_num                              -- 07
                ,to_char(unfit.regdate AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 08
                ,to_char(unfit.last_date AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 09
                ,unfit.last_host                         -- 10
                ,to_char(occur_time, 'YYYY')              -- 11
                ,to_char(occur_time, 'MM')                -- 12
                ,to_char(occur_time, 'DD')                -- 13
                ,CASE
                    WHEN mail THEN '送信する' ELSE '送信しない'
                 END                                    -- 14
                ,measure                                -- 15
            FROM
                unfit_report_header AS unfit
            LEFT OUTER JOIN
                unfit_report_measure USING(serial_no)
            LEFT OUTER JOIN
                user_detailes ON (sponsor=uid)
            {$this->where}
            ORDER BY
                occur_time ASC, serial_no ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            //$_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    ////////// CompleteList部
    public function getViewCompleteList(&$result)
    {
        $query = "
            SELECT serial_no                            -- 00
                ,subject                                -- 01
                ,to_char(occur_time, 'YY/MM/DD')        -- 02
                ,section                                -- 03
                ,place                                  -- 04
                ,sponsor                                -- 05
                ,trim(name)             AS 氏名         -- 06
                ,atten_num                              -- 07
                ,to_char(unfit.regdate AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 08
                ,to_char(unfit.last_date AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 09
                ,unfit.last_host                        -- 10
                ,to_char(occur_time, 'YYYY')            -- 11
                ,to_char(occur_time, 'MM')              -- 12
                ,to_char(occur_time, 'DD')              -- 13
                ,CASE
                    WHEN mail THEN '送信する' ELSE '送信しない'
                 END                                    -- 14
                ,measure                                -- 15
                ,to_char(follow_when, 'YY/MM/DD')       -- 16
                ,follow_sponsor                         -- 17
                ,follow                                 -- 18
                ,to_char(unfit_report_follow.regdate AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 19
                ,to_char(follow_date AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 20
            FROM
                unfit_report_header AS unfit
            LEFT OUTER JOIN
                unfit_report_measure USING(serial_no)
            LEFT OUTER JOIN
                unfit_report_follow USING(serial_no)
            LEFT OUTER JOIN
                user_detailes ON (sponsor=uid)
            WHERE
                measure = 't' AND follow = 'f'
            ORDER BY    
                follow_when ASC, occur_time ASC, serial_no ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            //$_SESSION['s_sysmsg'] = '登録がありません！';
        }
        for ($i=0; $i<$rows; $i++) {
            $serial = $res[$i][0];
            $query_f = "
                SELECT serial_no                            -- 00
                    ,follow_sponsor                         -- 01
                    ,trim(name)             AS 氏名         -- 02
                FROM
                    unfit_report_follow AS unfit
                LEFT OUTER JOIN
                    user_detailes ON (follow_sponsor=uid)
                WHERE
                    serial_no = '$serial'
                ORDER BY
                    serial_no ASC
            ";
            $res_f = array();
            // フォローアップ作成者の名前を$resの最後に追加
            if ( ($rows_f=$this->execute_List($query_f, $res_f)) < 1 ) {
                $res[$i][21] = '';
            } else {
                $res[$i][21] = $res_f[0][2];
            }
        }
        $result->add_array($res);
        return $rows;
    }
    ////////// IncompleteList部
    public function getViewIncompleteList(&$result)
    {
        $query = "
            SELECT serial_no                            -- 00
                ,subject                                -- 01
                ,to_char(occur_time, 'YY/MM/DD')        -- 02
                ,section                                -- 03
                ,place                                  -- 04
                ,sponsor                                -- 05
                ,trim(name)             AS 氏名         -- 06
                ,atten_num                              -- 07
                ,to_char(unfit.regdate AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 08
                ,to_char(unfit.last_date AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 09
                ,unfit.last_host                         -- 10
                ,to_char(occur_time, 'YYYY')              -- 11
                ,to_char(occur_time, 'MM')                -- 12
                ,to_char(occur_time, 'DD')                -- 13
                ,CASE
                    WHEN mail THEN '送信する' ELSE '送信しない'
                 END                                    -- 14
                ,measure                                -- 15
            FROM
                unfit_report_header AS unfit
            LEFT OUTER JOIN
                unfit_report_measure USING(serial_no)
            LEFT OUTER JOIN
                user_detailes ON (sponsor=uid)
            WHERE
                measure = 'f'
            ORDER BY
                occur_time DESC, serial_no ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            //$_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    ////////// FollowList部
    public function getViewFollowList(&$result)
    {
        $query = "
            SELECT serial_no                            -- 00
                ,subject                                -- 01
                ,to_char(occur_time, 'YY/MM/DD')        -- 02
                ,section                                -- 03
                ,place                                  -- 04
                ,sponsor                                -- 05
                ,trim(name)             AS 氏名         -- 06
                ,atten_num                              -- 07
                ,to_char(unfit.regdate AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 08
                ,to_char(unfit.last_date AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 09
                ,unfit.last_host                        -- 10
                ,to_char(occur_time, 'YYYY')            -- 11
                ,to_char(occur_time, 'MM')              -- 12
                ,to_char(occur_time, 'DD')              -- 13
                ,CASE
                    WHEN mail THEN '送信する' ELSE '送信しない'
                 END                                    -- 14
                ,measure                                -- 15
                ,to_char(follow_when, 'YY/MM/DD')       -- 16
                ,follow_sponsor                         -- 17
                ,follow                                 -- 18
                ,to_char(follow_date AT TIME ZONE 'JST', 'YY/MM/DD')
                                                        -- 19
            FROM
                unfit_report_header AS unfit
            LEFT OUTER JOIN
                unfit_report_measure USING(serial_no)
            LEFT OUTER JOIN
                unfit_report_follow USING(serial_no)
            LEFT OUTER JOIN
                user_detailes ON (sponsor=uid)
            WHERE
                measure = 't' AND follow = 't'
            ORDER BY
                follow_date ASC, occur_time ASC, serial_no ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            //$_SESSION['s_sysmsg'] = '登録がありません！';
        }
        for ($i=0; $i<$rows; $i++) {
            $serial = $res[$i][0];
            $query_f = "
                SELECT serial_no                            -- 00
                    ,follow_sponsor                         -- 01
                    ,trim(name)             AS 氏名         -- 02
                FROM
                    unfit_report_follow AS unfit
                LEFT OUTER JOIN
                    user_detailes ON (follow_sponsor=uid)
                WHERE
                    serial_no = '$serial'
                ORDER BY
                    serial_no ASC
            ";
            $res_f = array();
            // フォローアップ作成者の名前を$resの最後に追加
            if ( ($rows_f=$this->execute_List($query_f, $res_f)) < 1 ) {
                $res[$i][20] = '';
            } else {
                $res[$i][20] = $res_f[0][2];
            }
        }
        $result->add_array($res);
        return $rows;
    }
    ////////// 報告先の List部 attendance 複数対応
    public function getViewAttenList(&$result, $serial_no)
    {
        $query = "
            SELECT serial_no                            -- 00
                ,atten                                  -- 01
                ,trim(name)                             -- 02
                ,CASE
                    WHEN mail THEN '送信済'
                    ELSE '未送信'
                 END                                    -- 03
            FROM
                unfit_report_attendance AS unfit
            LEFT OUTER JOIN
                user_detailes ON (atten=uid)
            WHERE
                serial_no = {$serial_no}
            ORDER BY
                atten ASC
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            //$_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ////////// 部署毎の社員番号と氏名を取得
    /*** userId_name 配列を返す, atten 配列 selected の設定用 ***/
    public function getViewUserName(&$userID_name, $atten)
    {
        $query = "
            SELECT uid       AS 社員番号
                , trim(name) AS 氏名
            FROM
                user_detailes
            WHERE
                retire_date IS NULL
                AND
                sid != 31
            ORDER BY
                pid DESC, sid ASC, uid ASC
            
        ";
        $userID_name = array();
        if ( ($rows=$this->getResult2($query, $userID_name)) < 1 ) {
            $_SESSION['s_sysmsg'] = '社員データの登録がありません！';
        }
        if (is_array($atten)) {
            $r = count($atten);
            for ($i=0; $i<$rows; $i++) {
                for ($j=0; $j<$r; $j++) {
                    if ($userID_name[$i][0] == $atten[$j]) {
                        $userID_name[$i][2] = ' selected';
                        break;
                    } else {
                        $userID_name[$i][2] = '';
                    }
                }
            }
        }
        return $rows;
        
    }
    
    ////////// Edit 時の 1レコード分
    public function getViewEdit($serial_no, $result)
    {
        $query = "
            SELECT serial_no                    -- 00
                ,subject                        -- 01
                ,place                          -- 02
                ,section                        -- 03
                ,sponsor                        -- 04
                ,atten_num                      -- 05
                ,mail                           -- 06
                ,to_char(occur_time, 'YYYY')    -- 07
                ,to_char(occur_time, 'MM')      -- 08
                ,to_char(occur_time, 'DD')      -- 09
                ,receipt_no                     -- 10
            FROM
                unfit_report_header
            WHERE
                serial_no = {$serial_no}
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            $result->add_once('serial_no',  $res[0][0]);
            $result->add_once('subject',    $res[0][1]);
            $result->add_once('place',      $res[0][2]);
            $result->add_once('section',    $res[0][3]);
            $result->add_once('sponsor',    $res[0][4]);
            $result->add_once('atten_num',  $res[0][5]);
            $result->add_once('mail',       $res[0][6]);
            $result->add_once('editYear',   $res[0][7]);
            $result->add_once('editMonth',  $res[0][8]);
            $result->add_once('editDay',    $res[0][9]);
            $result->add_once('receipt_no', $res[0][10]);
        }
        return $rows;
    }
    
    ////////// 発生原因の List部 cause
    public function getViewCauseList(&$result, $serial_no)
    {
        $query = "
            SELECT serial_no                          -- 00
                ,assy_no                              -- 01
                ,parts_no                             -- 02
                ,occur_cause                          -- 03
                ,unfit_num                            -- 04
                ,issue_cause                          -- 05
                ,issue_num                            -- 06
            FROM
                unfit_report_cause AS unfit
            WHERE
                serial_no = {$serial_no}
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            $result->add_once('assy_no',     $res[0][1]);
            $result->add_once('parts_no',    $res[0][2]);
            $result->add_once('occur_cause', $res[0][3]);
            $result->add_once('unfit_num',   $res[0][4]);
            $result->add_once('issue_cause', $res[0][5]);
            $result->add_once('issue_num',   $res[0][6]);
        }
        return $rows;
    }
    
    ////////// 対策の List部 cause
    public function getViewMeasureList(&$result, $serial_no)
    {
        $query = "
            SELECT serial_no                         -- 00
                ,unfit_dispose                       -- 01
                ,occur_measure                       -- 02
                ,to_char(occurMeasure_date, 'YYYY')  -- 03
                ,to_char(occurMeasure_date, 'MM')    -- 04
                ,to_char(occurMeasure_date, 'DD')    -- 05
                ,issue_measure                       -- 06
                ,to_char(issueMeasure_date, 'YYYY')  -- 07
                ,to_char(issueMeasure_date, 'MM')    -- 08
                ,to_char(issueMeasure_date, 'DD')    -- 09
                ,follow_who                          -- 10
                ,follow_when                         -- 11
                ,follow_how                          -- 12
                ,measure                             -- 13
            FROM
                unfit_report_measure AS unfit
            WHERE
                serial_no = {$serial_no}
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            $result->add_once('unfit_dispose', $res[0][1]);
            $result->add_once('occur_measure', $res[0][2]);
            $result->add_once('occurYear',     $res[0][3]);
            $result->add_once('occurMonth',    $res[0][4]);
            $result->add_once('occurDay',      $res[0][5]);
            $result->add_once('issue_measure', $res[0][6]);
            $result->add_once('issueYear',     $res[0][7]);
            $result->add_once('issueMonth',    $res[0][8]);
            $result->add_once('issueDay',      $res[0][9]);
            $result->add_once('follow_who',    $res[0][10]);
            $result->add_once('follow_when',   $res[0][11]);
            $result->add_once('follow_how',    $res[0][12]);
            $result->add_once('measure',       $res[0][13]);
        }
        return $rows;
    }
    
    ////////// 展開の List部 develop
    public function getViewDevelopList(&$result, $serial_no)
    {
        $query = "
            SELECT serial_no             -- 00
                ,suihei                  -- 01
                ,kanai                   -- 02
                ,kagai                   -- 03
                ,hyoujyun                -- 04
                ,kyouiku                 -- 05
                ,system                  -- 06
            FROM
                unfit_report_develop AS unfit
            WHERE
                serial_no = {$serial_no}
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            $result->add_once('suihei',    $res[0][1]);
            $result->add_once('kanai',     $res[0][2]);
            $result->add_once('kagai',     $res[0][3]);
            $result->add_once('hyoujyun',  $res[0][4]);
            $result->add_once('kyouiku',   $res[0][5]);
            $result->add_once('system',    $res[0][6]);
        }
        return $rows;
    }
    
    ////////// 展開の List部 follow
    public function getViewFollow(&$result, $serial_no)
    {
        $query = "
            SELECT serial_no            -- 00
                ,follow_section         -- 01
                ,follow_quality         -- 02
                ,follow_opinion         -- 03
                ,follow_sponsor         -- 04
                ,follow                 -- 05
            FROM
                unfit_report_follow AS unfit
            WHERE
                serial_no = {$serial_no}
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            $result->add_once('follow_section',    $res[0][1]);
            $result->add_once('follow_quality',    $res[0][2]);
            $result->add_once('follow_opinion',    $res[0][3]);
            $result->add_once('follow_sponsor',    $res[0][4]);
            $result->add_once('follow',            $res[0][5]);
            $result->add_once('follow_flg',        't');
        } else {
            $result->add_once('follow_section',    '');
            $result->add_once('follow_quality',    '');
            $result->add_once('follow_opinion',    '');
            $result->add_once('follow_sponsor',    '');
            $result->add_once('follow',            '');
            $result->add_once('follow_flg',        'f');
        }
        return $rows;
    }
    
    ////////// List時の 表題(キャプション)の生成
    public function get_caption($switch, $year, $month, $day)
    {
        switch ($switch) {
        case 'List':
            $caption = '～';
            $caption = sprintf("%04d年%02d月%02d日{$caption}", $year, $month, $day);
            break;
        case 'Apend':
            $caption = '報告書の追加';
            break;
        case 'Edit':
            $caption = '報告書の編集';
            break;
        case 'Follow':
            $caption = 'フォローアップの編集';
            break;
        default:
            $caption = '';
        }
        return $caption;
        
    }
    
    ////////// List時の 登録データがない場合のメッセージ生成
    public function get_noDataMessage($year, $month, $day)
    {
        if ($year != '') {
            if (sprintf('%04d%02d%02d', $year, $month, $day) < date('Ymd')) {
                $noDataMessage = '報告書がありません。';  // 過去の場合
            } else {
                $noDataMessage = '報告書がありません。';  // 未来の場合
            }
        } else {
            // 本日の場合
            $noDataMessage = '報告書がありません。';
        }
        return $noDataMessage;
        
    }
    
    ////////// 報告先グループの List部
    public function getViewGroupList(&$result)
    {
        $query = "
            SELECT group_no                             -- 00
                ,group_name                             -- 01
                ,owner                                  -- 02
                ,CASE
                    WHEN active THEN '有効'
                    ELSE '無効'
                 END                    AS 有効無効     -- 03
                ,to_char(mail.regdate, 'YY/MM/DD HH24:MI')
                                                        -- 04
                ,to_char(mail.last_date, 'YY/MM/DD HH24:MI')
                                                        -- 05
                ,trim(name)                             -- 06
            FROM
                meeting_mail_group AS mail
            LEFT OUTER JOIN
                user_detailes ON (owner=uid)
            GROUP BY
                group_no, group_name, owner, active, mail.regdate, mail.last_date, name
            ORDER BY
                group_no ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            //$_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ////////// 報告先グループの １グループ分 Attendance List部
    public function getGroupAttenList(&$result, $group_no)
    {
        $query = "
            SELECT
                 trim(name)                             -- 00
                ,atten                                  -- 01
            FROM
                meeting_mail_group
            LEFT OUTER JOIN
                user_detailes ON (atten=uid)
            WHERE
                group_no={$group_no}
            ORDER BY
                pid DESC, sid ASC, uid ASC
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            //$_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ////////// 報告先グループの有効なリスト Active List部
    ////////// JSgroup_name=グループ名の１次元配列, JSgroup_member=グループ名に対応した報告先の２次元配列, 戻り値=有効件数
    ////////// owner='000000'は共有グループ, 指定がある場合は個人のグループ
    public function getActiveGroupList(&$JSgroup_name, &$JSgroup_member, $uid)
    {
        ////// 初期化
        $JSgroup_name = array();
        $JSgroup_member = array();
        ////// グループ名の配列の取得
        $query = "
            SELECT group_name                             -- 00
                 , group_no                               -- 01
            FROM
                meeting_mail_group
            WHERE
                active AND (owner='000000' OR owner='{$uid}')
            GROUP BY
                group_no, group_name
            ORDER BY
                group_no ASC
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            return false;
        }
        for ($i=0; $i<$rows; $i++) {
            $JSgroup_name[$i] = $res[$i][0];
            // グループメンバーの2次元配列の取得
            $query = "
                SELECT
                     atten                             -- 00
                FROM
                    meeting_mail_group
                LEFT OUTER JOIN
                    user_detailes ON (atten=uid)
                WHERE
                    group_no={$res[$i][1]}
                ORDER BY
                    pid DESC, sid ASC, uid ASC
            ";
            $resMem = array();
            if ( ($rowsMem=$this->getResult2($query, $resMem)) < 1 ) {
                return false;
            }
            for ($j=0; $j<$rowsMem; $j++) {
                $JSgroup_member[$i][$j] = $resMem[$j][0];
            }
        }
        return $rows;
    }
    
    ////////// 部品名の出力
    public function getTargetPartsNames($request)
    {
        $query = "
            SELECT
                midsc      AS 部品名
            FROM miitem
            WHERE mipn='{$request->get('parts_no')}'
        ";
        $res = array();
        $rows = $this->getResult2($query, $res);
        ////// 初期化
        $option = "\n";
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            $name = "　";
        } else {
            $name = "{$res[0][0]}";
        }
        return $name;
    }
    
    ////////// 部品名の出力(EDIT時)
    public function getTargetPartsNamesEdit($parts_no)
    {
        $query = "
            SELECT
                midsc      AS 部品名
            FROM miitem
            WHERE mipn='{$parts_no}'
        ";
        $res = array();
        $rows = $this->getResult2($query, $res);
        ////// 初期化
        $option = "\n";
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            $name = "　";
        } else {
            $name = "{$res[0][0]}";
        }
        return $name;
    }
    
    ////////// 製品名の出力
    public function getTargetAssyNames($request)
    {
        $query = "
            SELECT
                midsc      AS 製品名
            FROM miitem
            WHERE mipn='{$request->get('assy_no')}'
        ";
        $res = array();
        $rows = $this->getResult2($query, $res);
        ////// 初期化
        $option = "\n";
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            $name = "　";
        } else {
            $name = "{$res[0][0]}";
        }
        return $name;
    }
    
    ////////// 製品名の出力(EDIT時)
    public function getTargetAssyNamesEdit($assy_no)
    {
        $query = "
            SELECT
                midsc      AS 製品名
            FROM miitem
            WHERE mipn='{$assy_no}'
        ";
        $res = array();
        $rows = $this->getResult2($query, $res);
        ////// 初期化
        $option = "\n";
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            $name = "　";
        } else {
            $name = "{$res[0][0]}";
        }
        return $name;
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// group_noの適正をチェックしメッセージ＋結果(true=OK,false=NG)を返す
    protected function checkGroupNo($group_no)
    {
        ////// group_noの適正チェック
        if (is_numeric($group_no)) {
            if ($group_no >= 1 && $group_no <= 999) {   // int2 以内が実際の範囲
                return true;
            } else {
                $_SESSION['s_sysmsg'] = "出席者のグループ番号 {$group_no} は範囲外です！ 1～999までです。";
            }
        } else {
            $_SESSION['s_sysmsg'] = "出席者のグループ番号 {$group_no} は数字以外が含まれています。";
        }
        return false;
    }
    ////////// 不適合報告書の案内を email で出だす
    protected function guideUnfitMail($request, $serial_no, $cancel=false)
    {
        ////// パラメーターの分割
        $year       = $request->get('yearReg');             // 発生年月日の年４桁
        $month      = $request->get('monthReg');            // 発生年月日の月２桁
        $day        = $request->get('dayReg');              // 発生年月日の日２桁
        $subject    = $request->get('subject');             // 不適合内容
        $sponsor    = $request->get('sponsor');             // 作成者
        $atten      = $request->get('atten');               // 報告先(attendance) (配列)
        $place      = $request->get('place');               // 発生場所
        $section    = $request->get('section');             // 責任部門
        $atten_num  = count($atten);                        // 報告先数
        $mail       = $request->get('mail');                // メールの送信 Y/N
        ////// 曜日を取得する
        $week = array('日', '月', '火', '水', '木', '金', '土');
        $dayWeek = $week[date('w', mktime(0, 0, 0, $month, $day, $year))];
        ////// 作成者の名前を取得
        if (!$this->getSponsorName($sponsor, $res)) {
            $_SESSION['s_sysmsg'] = "メール案内で作成者の名前が見つかりません！ [ $sponsor ]";
        } else {
            $sponsor_name = $res[0][0];
            $sponsor_addr = $res[0][1];
            // 作成者の名前取得 (引数３個は全て配列)
            $this->getAttendanceName($atten, $atten_name, $flag);
            // 作成者のメールアドレスの取得とメール送信
            for ($i=0; $i<$atten_num; $i++) {
                if ($flag[$i] == 'NG') continue;
                ////// 報告先のメールアドレス取得
                if ( !($atten_addr=$this->getAttendanceAddr($atten[$i])) ) {
                    continue;
                }
                $to_addres = $atten_addr;
                $message  = "この案内は {$sponsor_name} さんが報告先にメール案内を出す設定にしたため送信されたものです。\n\n";
                $message .= "{$subject}\n\n";
                if ($cancel) {
                    $message .= "下記の不適合報告書が{$this->getUserName()}さんにより削除されましたので、ご連絡致します。\n\n";
                } else {
                    $message .= "下記の不適合報告書を作成しましたので、ご確認お願い致します。\n\n";
                }
                $message .= "                               記\n\n";
                $message .= "１. 発生日    ：{$year}年 {$month}月 {$day}日({$dayWeek})\n\n";
                $message .= "２. 不適合内容：{$subject}\n\n";
                $message .= "３. 発生場所  ：{$place}\n\n";
                $message .= "４. 責任部門  ：{$section}\n\n";
                $message .= "５. 作成者    ：{$sponsor_name}\n\n";
                $message .= "６. 報告先    ：{$this->getAttendanceNameList($atten, $atten_name)}";
                $message .= "\n\n";
                $message .= "以上、宜しくお願い致します。\n\n";
                $add_head = "From: {$sponsor_addr}\r\nReply-To: {$sponsor_addr}";
                $attenSubject = '宛先： ' . $atten_name[$i] . ' 様　 ' . $subject;
                if (mb_send_mail($to_addres, $attenSubject, $message, $add_head)) {
                    // 報告先へのメール送信履歴を保存
                    $this->setAttendanceMailHistory($serial_no, $atten[$i]);
                }
                ////// Debug
                if ($cancel) {
                    if ($i == 0) mb_send_mail('tnksys@nitto-kohki.co.jp', $attenSubject, $message, $add_head);
                }
            }
            return true;
        }
        return false;
    }
    ////////// フォローアップの案内を email で出だす
    protected function guideFollowMail($request, $serial_no)
    {
        ////// パラメーターの分割
        $year       = $request->get('yearReg');             // 発生年月日の年４桁
        $month      = $request->get('monthReg');            // 発生年月日の月２桁
        $day        = $request->get('dayReg');              // 発生年月日の日２桁
        $subject    = $request->get('subject');             // 不適合内容
        $sponsor    = $request->get('sponsor');             // フォローアップ作成者
        $atten      = $request->get('atten');               // 報告先(attendance) (配列)
        $place      = $request->get('place');               // 発生場所
        $section    = $request->get('section');             // 責任部門
        $atten_num  = count($atten);                        // 報告先数
        $mail       = $request->get('mail');                // メールの送信 Y/N
        ////// 曜日を取得する
        $week = array('日', '月', '火', '水', '木', '金', '土');
        $dayWeek = $week[date('w', mktime(0, 0, 0, $month, $day, $year))];
        ////// 作成者の名前を取得
        if (!$this->getSponsorName($sponsor, $res)) {
            $_SESSION['s_sysmsg'] = "メール案内で作成者の名前が見つかりません！ [ $sponsor ]";
        } else {
            $sponsor_name = $res[0][0];
            $sponsor_addr = $res[0][1];
            // 作成者の名前取得 (引数３個は全て配列)
            $this->getAttendanceName($atten, $atten_name, $flag);
            // 作成者のメールアドレスの取得とメール送信
            for ($i=0; $i<$atten_num; $i++) {
                if ($flag[$i] == 'NG') continue;
                ////// 報告先のメールアドレス取得
                if ( !($atten_addr=$this->getAttendanceAddr($atten[$i])) ) {
                    continue;
                }
                $to_addres = $atten_addr;
                $message  = "この案内は {$sponsor_name} さんが報告先にメール案内を出す設定にしたため送信されたものです。\n\n";
                $message .= "{$subject}\n\n";
                $message .= "下記の不適合報告書のフォローアップを作成しましたので、ご確認お願い致します。\n\n";
                $message .= "                               記\n\n";
                $message .= "１. 発生日    ：{$year}年 {$month}月 {$day}日({$dayWeek})\n\n";
                $message .= "２. 不適合内容：{$subject}\n\n";
                $message .= "３. 発生場所  ：{$place}\n\n";
                $message .= "４. 責任部門  ：{$section}\n\n";
                $message .= "５. 作成者    ：{$sponsor_name}\n\n";
                $message .= "６. 報告先    ：{$this->getAttendanceNameList($atten, $atten_name)}";
                $message .= "\n\n";
                $message .= "以上、宜しくお願い致します。\n\n";
                $add_head = "From: {$sponsor_addr}\r\nReply-To: {$sponsor_addr}";
                $attenSubject = '宛先： ' . $atten_name[$i] . ' 様　 ' . $subject;
                if (mb_send_mail($to_addres, $attenSubject, $message, $add_head)) {
                    // 報告先へのメール送信履歴を保存
                    $this->setAttendanceMailHistory($serial_no, $atten[$i]);
                }
            }
            return true;
        }
        return false;
    }
    ////////// 報告先グループの登録 (実行部)
    protected function groupInsert($group_no, $group_name, $atten, $owner)
    {
        ////// ここに last_date last_host の登録処理を入れる
        ////// regdate=自動登録
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        $insert_sql = '';
        $cnt = count($atten);
        for ($i=0; $i<$cnt; $i++) {
            $insert_sql .= "
                INSERT INTO meeting_mail_group
                (group_no, group_name, atten, owner, active, last_date, last_host)
                VALUES
                ('$group_no', '$group_name', '{$atten[$i]}', '$owner', TRUE, '$last_date', '$last_host')
                ;
            ";
        }
        return $this->execute_Insert($insert_sql);
    }
    
    ////////// 報告先グループの変更 (実行部)
    protected function groupUpdate($group_no, $group_name, $atten, $owner)
    {
        ////// ここに last_date last_host の登録処理を入れる
        ////// regdate=自動登録
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        ////// 保存用のSQL文を設定
        $save_sql = "
            SELECT * FROM meeting_mail_group WHERE group_no={$group_no}
        ";
        $update_sql = '';
        $update_sql .= "
            DELETE FROM meeting_mail_group WHERE group_no={$group_no}
            ;
        "; 
        $cnt = count($atten);
        ////// 有効・無効の active は変更時に 常に有効となる
        for ($i=0; $i<$cnt; $i++) {
            $update_sql .= "
                INSERT INTO meeting_mail_group
                (group_no, group_name, atten, owner, active, last_date, last_host)
                VALUES
                ('$group_no', '$group_name', '{$atten[$i]}', '$owner', TRUE, '$last_date', '$last_host')
                ;
            ";
        }
        return $this->execute_Update($update_sql, $save_sql);
    }
    
    ////////// 報告先グループの削除 (実行部)
    protected function groupDelete($group_no)
    {
        ////// 保存用のSQL文を設定
        $save_sql   = "
            SELECT * FROM meeting_mail_group WHERE group_no={$group_no}
        ";
        ////// 削除用SQL文を設定
        $delete_sql = "
            DELETE FROM meeting_mail_group WHERE group_no={$group_no}
        ";
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// 不適合報告書の実行部 追加
    private function add_execute($request)
    {
        ////// パラメーターの分割
        ////// unfit_report_header 項目
        $year       = $request->get('yearReg');            // 発生年月日の年４桁
        $month      = $request->get('monthReg');           // 発生年月日の月２桁
        $day        = $request->get('dayReg');             // 発生年月日の日２桁
        $subject    = $request->get('subject');            // 不適合内容
        $place      = $request->get('place');              // 発生場所
        $section    = $request->get('section');            // 責任部門
        $sponsor    = $request->get('sponsor');            // 作成者
        $receipt_no = $request->get('receipt_no');         // 受付No.
        ////// unfit_report_attendance 項目
        $atten = $request->get('atten');                // 報告先(attendance) (配列)
        $mail  = $request->get('mail');                 // メールの送信 Y/N
        ////// unfit_report_cause 項目
        $assy_no     = $request->get('assy_no');        // 製品番号
        $parts_no    = $request->get('parts_no');       // 部品番号
        $occur_cause = $request->get('occur_cause');    // 発生原因
        $unfit_num   = $request->get('unfit_num');      // 不適合数量
        $issue_cause = $request->get('issue_cause');    // 流出原因
        $issue_num   = $request->get('issue_num');      // 流出数量
        ////// unfit_report_measure 項目
        $unfit_dispose      = $request->get('unfit_dispose');     // 不適合品の処置
        $occur_measure      = $request->get('occur_measure');     // 発生源対策
        $occur_year         = $request->get('occur_yearReg');     // 発生源対策実施予定年４桁
        $occur_month        = $request->get('occur_monthReg');    // 発生源対策実施予定月２桁
        $occur_day          = $request->get('occur_dayReg');      // 発生源対策実施予定日２桁
        $issue_measure      = $request->get('issue_measure');     // 流出対策
        $issue_year         = $request->get('issue_yearReg');     // 流出対策実施予定年４桁
        $issue_month        = $request->get('issue_monthReg');    // 流出対策実施予定月２桁
        $issue_day          = $request->get('issue_dayReg');      // 流出対策実施予定日２桁
        $follow_who         = $request->get('follow_who');        // フォローアップ誰
        $follow_year        = $request->get('follow_yearReg');    // フォローアップ予定年４桁
        $follow_month       = $request->get('follow_monthReg');   // フォローアップ予定月２桁
        $follow_day         = $request->get('follow_dayReg');     // フォローアップ予定日２桁
        $follow_how         = $request->get('follow_how');        // フォローアップどのように
        $measure            = $request->get('measure');           // 対策完了 Y/N
        ////// unfit_report_develop 項目
        $suihei   = $request->get('suihei');            // 実施項目 水平展開
        $kanai    = $request->get('kanai');             // 実施項目 課内展開
        $kagai    = $request->get('kagai');             // 実施項目 課外展開
        $hyoujyun = $request->get('hyoujyun');          // 実施項目 標準書展開
        $kyouiku  = $request->get('kyouiku');           // 実施項目 教育実施
        $system   = $request->get('system');            // 実施項目 システム
        ////// 実施項目を boolean型に変換
        if ($suihei == 't') $suihei = 'TRUE'; else $suihei = 'FALSE';
        if ($kanai == 't') $kanai = 'TRUE'; else $kanai = 'FALSE';
        if ($kagai == 't') $kagai = 'TRUE'; else $kagai = 'FALSE';
        if ($hyoujyun == 't') $hyoujyun = 'TRUE'; else $hyoujyun = 'FALSE';
        if ($kyouiku == 't') $kyouiku = 'TRUE'; else $kyouiku = 'FALSE';
        if ($system == 't') $system = 'TRUE'; else $system = 'FALSE';
        ////// メール送信 Y/N を boolean型に変換
        if ($mail == 't') $mail = 'TRUE'; else $mail = 'FALSE';
        ////// 対策完了 Y/N を boolean型に変換
        if ($measure == 't') $measure = 'TRUE'; else $measure = 'FALSE';
        ////// ここに last_date last_host の登録処理を入れる
        ////// regdate=自動登録
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']);
        ////// 報告先の人数を取得
        $atten_num = count($atten);
        $insert_qry = "
            INSERT INTO unfit_report_header
            (subject, occur_time, place, section, sponsor, atten_num, mail, last_date, last_host, receipt_no)
            VALUES
            ('$subject', '{$year}-{$month}-{$day}', '$place', '$section', '$sponsor', $atten_num, $mail, '$last_date', '$last_host', '$receipt_no')
            ;
        ";
        for ($i=0; $i<$atten_num; $i++) {
            $insert_qry .= "
                INSERT INTO unfit_report_attendance
                (serial_no, atten, mail)
                VALUES
                ((SELECT max(serial_no) FROM unfit_report_header), '{$atten[$i]}', FALSE)
                ;
            ";
        }
        $insert_qry .= "
            INSERT INTO unfit_report_cause
            (serial_no, assy_no, parts_no, occur_cause, unfit_num, issue_cause, issue_num)
            VALUES
            ((SELECT max(serial_no) FROM unfit_report_header), '$assy_no', '$parts_no', '$occur_cause', $unfit_num, '$issue_cause', $issue_num)
            ;
        ";
        $insert_qry .= "
            INSERT INTO unfit_report_measure
            (serial_no, unfit_dispose, occur_measure, occurMeasure_date, issue_measure, issueMeasure_date, follow_who, follow_when, follow_how, measure)
            VALUES
            ((SELECT max(serial_no) FROM unfit_report_header), '$unfit_dispose', '$occur_measure', '{$occur_year}-{$occur_month}-{$occur_day}', '$issue_measure', '{$issue_year}-{$issue_month}-{$issue_day}', '$follow_who', '{$follow_year}-{$follow_month}-{$follow_day}', '$follow_how', $measure)
            ;
        ";
        $insert_qry .= "
            INSERT INTO unfit_report_develop
            (serial_no, suihei, kanai, kagai, hyoujyun, kyouiku, system)
            VALUES
            ((SELECT max(serial_no) FROM unfit_report_header), $suihei, $kanai, $kagai, $hyoujyun, $kyouiku, $system)
            ;
        ";
        $insert_qry .= "
                INSERT INTO unfit_report_follow
                (serial_no, follow)
                VALUES
                ((SELECT max(serial_no) FROM unfit_report_header), 'f')
                ;
            ";
        if ($this->execute_Insert($insert_qry)) {
            $query = "SELECT max(serial_no) FROM unfit_report_header";
            $serial_no = false;                                 // 初期値
            $this->getUniResult($query, $serial_no);
            return $serial_no;                                  // 登録したシリアル番号を返す
        } else {
            return false;
        }
    }
    
    ////////// 不適合報告書の実行部 フォローアップ
    private function follow_execute($request)
    {
        ////// パラメーターの分割
        $serial_no      = $request->get('serial_no');           // 連番(キーフィールド)
        $follow_section = $request->get('follow_section');      // フォローアップ 発行部門
        $follow_quality = $request->get('follow_quality');      // フォローアップ 品質保証課
        $follow_opinion = $request->get('follow_opinion');      // フォローアップ 意見
        $follow         = $request->get('follow');              // フォローアップ完了 Y/N
        $sponsor        = $request->get('sponsor');             // 作成者
        ////// unfit_report_attendance 項目
        $atten = $request->get('atten');                        // 報告先(attendance) (配列)
        $mail  = $request->get('mail');                         // メールの送信 Y/N
        ////// メール送信 Y/N を boolean型に変換
        if ($mail == 't') $mail = 'TRUE'; else $mail = 'FALSE';
        ////// フォローアップ完了 Y/N を boolean型に変換
        if ($follow == 't') $follow = 'TRUE'; else $follow = 'FALSE';
        ////// ここに last_date last_host の登録処理を入れる
        ////// 保存用のSQL文を設定
        $save_sql = "
            SELECT * FROM unfit_report_header WHERE serial_no={$serial_no}
        ";
        ////// regdate=自動登録
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']);
        ////// 報告先の人数を取得
        $atten_num = count($atten);
        $chk_sql = "
            SELECT * FROM unfit_report_follow WHERE serial_no={$serial_no}
        ";
        if ($this->getUniResult($chk_sql, $check) < 1) {        // 指定のシリアル番号の存在チェック
            $insert_qry = "
                DELETE FROM unfit_report_attendance WHERE serial_no={$serial_no}
                ;
            ";
        
            for ($i=0; $i<$atten_num; $i++) {
                $insert_qry .= "
                    INSERT INTO unfit_report_attendance
                    (serial_no, atten, mail)
                    VALUES
                    ({$serial_no}, '{$atten[$i]}', FALSE)
                    ;
                ";
            }
            $insert_qry .= "
                INSERT INTO unfit_report_follow
                (serial_no, follow_section, follow_quality, follow_opinion, follow_sponsor, follow, follow_date, last_host)
                VALUES
                ($serial_no, '$follow_section', '$follow_quality', '$follow_opinion', '$sponsor', $follow, '$last_date', '$last_host')
                ;
            ";
            if ($this->execute_Insert($insert_qry)) {
                return $serial_no;                              // 登録したシリアル番号を返す
            } else {
                return false;
            }
        } else {
            $update_sql = "
                UPDATE unfit_report_follow SET
                follow_section='$follow_section', follow_quality='$follow_quality', follow_opinion='$follow_opinion',
                follow_sponsor='$sponsor', follow='$follow',follow_date='{$last_date}', last_host='{$last_host}'
                where serial_no={$serial_no}
            ;
            ";
            $update_sql .= "
                DELETE FROM unfit_report_attendance WHERE serial_no={$serial_no}
                ;
            ";
        
            for ($i=0; $i<$atten_num; $i++) {
                $update_sql .= "
                    INSERT INTO unfit_report_attendance
                    (serial_no, atten, mail)
                    VALUES
                    ({$serial_no}, '{$atten[$i]}', FALSE)
                    ;
                ";
            }
            if ($this->execute_Update($update_sql, $save_sql)) {
                return $serial_no;                              // 登録したシリアル番号を返す
            } else {
                return false;
            }
        }
        
    }
    
    ////////// 不適合報告書の実行部 削除(完全)
    private function del_execute($serial_no, $subject)
    {
        ////// 保存用のSQL文を設定
        $save_sql   = "
            SELECT * FROM unfit_report_header WHERE serial_no={$serial_no}
        ";
        $delete_sql = "
            DELETE FROM unfit_report_header WHERE serial_no={$serial_no}
            ;
        ";
        $delete_sql .= "
            DELETE FROM unfit_report_cause WHERE serial_no={$serial_no}
            ;
        ";
        $delete_sql .= "
            DELETE FROM unfit_report_measure WHERE serial_no={$serial_no}
            ;
        ";
        $delete_sql .= "
            DELETE FROM unfit_report_develop WHERE serial_no={$serial_no}
            ;
        ";
        $delete_sql .= "
            DELETE FROM unfit_report_attendance WHERE serial_no={$serial_no}
            ;
        ";
        ////// 不適合報告書の削除と同時にフォローアップも削除する
        $delete_sql .= "
            DELETE FROM unfit_report_follow WHERE serial_no={$serial_no}
            ;
        ";
        ////// $save_sqlはオプションなので指定しなくても良い
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
    ////////// 不適合報告書の実行部 変更
    private function edit_execute($request)
    {
        ////// パラメーターの分割
        $serial_no  = $request->get('serial_no');               // 連番(キーフィールド)
        ////// unfit_report_header 項目
        $year       = $request->get('yearReg');                 // 発生年月日の年４桁
        $month      = $request->get('monthReg');                // 発生年月日の月２桁
        $day        = $request->get('dayReg');                  // 発生年月日の日２桁
        $subject    = $request->get('subject');                 // 不適合報告書
        $place      = $request->get('place');                   // 発生場所
        $section    = $request->get('section');                 // 責任部門
        $sponsor    = $request->get('sponsor');                 // 作成者
        $receipt_no = $request->get('receipt_no');              // 受付No.
        ////// unfit_report_attendance 項目
        $atten = $request->get('atten');                        // 報告先(attendance) (配列)
        $mail  = $request->get('mail');                         // メールの送信 Y/N
        ////// unfit_report_cause 項目
        $assy_no     = $request->get('assy_no');                // 製品番号
        $parts_no    = $request->get('parts_no');               // 部品番号
        $occur_cause = $request->get('occur_cause');            // 発生原因
        $unfit_num   = $request->get('unfit_num');              // 不適合数量
        $issue_cause = $request->get('issue_cause');            // 流出原因
        $issue_num   = $request->get('issue_num');              // 流出数量
        ////// unfit_report_measure 項目
        $unfit_dispose      = $request->get('unfit_dispose');   // 不適合品の処置
        $occur_measure      = $request->get('occur_measure');   // 発生源対策
        $occur_year         = $request->get('occur_yearReg');   // 発生源対策実施予定年４桁
        $occur_month        = $request->get('occur_monthReg');  // 発生源対策実施予定月２桁
        $occur_day          = $request->get('occur_dayReg');    // 発生源対策実施予定日２桁
        $issue_measure      = $request->get('issue_measure');   // 流出対策
        $issue_year         = $request->get('issue_yearReg');   // 流出対策実施予定年４桁
        $issue_month        = $request->get('issue_monthReg');  // 流出対策実施予定月２桁
        $issue_day          = $request->get('issue_dayReg');    // 流出対策実施予定日２桁
        $follow_who         = $request->get('follow_who');      // フォローアップ誰
        $follow_year        = $request->get('follow_yearReg');  // フォローアップ予定年４桁
        $follow_month       = $request->get('follow_monthReg'); // フォローアップ予定月２桁
        $follow_day         = $request->get('follow_dayReg');   // フォローアップ予定日２桁
        $follow_how         = $request->get('follow_how');      // フォローアップどのように
        $measure            = $request->get('measure');         // 対策完了 Y/N
        ////// unfit_report_develop 項目
        $suihei   = $request->get('suihei');                    // 実施項目 水平展開
        $kanai    = $request->get('kanai');                     // 実施項目 課内展開
        $kagai    = $request->get('kagai');                     // 実施項目 課外展開
        $hyoujyun = $request->get('hyoujyun');                  // 実施項目 標準書展開
        $kyouiku  = $request->get('kyouiku');                   // 実施項目 教育実施
        $system   = $request->get('system');                    // 実施項目 システム
        if ($suihei == 't') $suihei = 'TRUE'; else $suihei = 'FALSE';
        if ($kanai == 't') $kanai = 'TRUE'; else $kanai = 'FALSE';
        if ($kagai == 't') $kagai = 'TRUE'; else $kagai = 'FALSE';
        if ($hyoujyun == 't') $hyoujyun = 'TRUE'; else $hyoujyun = 'FALSE';
        if ($kyouiku == 't') $kyouiku = 'TRUE'; else $kyouiku = 'FALSE';
        if ($system == 't') $system = 'TRUE'; else $system = 'FALSE';
        ////// メール送信 Y/N を boolean型に変換
        if ($mail == 't') $mail = 'TRUE'; else $mail = 'FALSE';
        ////// 対策完了 Y/N を boolean型に変換
        if ($measure == 't') $measure = 'TRUE'; else $measure = 'FALSE';
        ////// 保存用のSQL文を設定
        $save_sql = "
            SELECT * FROM unfit_report_header WHERE serial_no={$serial_no}
        ";
        ////// ここに last_date last_host の登録処理を入れる
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']);
        ////// 報告先の人数を取得
        $atten_num = count($atten);
        $update_sql = "
            UPDATE unfit_report_header SET
            subject='{$subject}', occur_time='{$year}-{$month}-{$day}', place='$place',
            section='$section', sponsor='{$sponsor}', atten_num='{$atten_num}', mail='$mail',
            last_date='{$last_date}', last_host='{$last_host}', receipt_no='{$receipt_no}'
            where serial_no={$serial_no}
            ;
        ";
        $update_sql .= "
            UPDATE unfit_report_cause SET
            assy_no='{$assy_no}', parts_no='{$parts_no}', occur_cause='{$occur_cause}',
            unfit_num='$unfit_num', issue_cause='{$issue_cause}', issue_num='$issue_num'
            where serial_no={$serial_no}
            ;
        ";
        $update_sql .= "
            UPDATE unfit_report_measure SET
            unfit_dispose='{$unfit_dispose}', occur_measure='{$occur_measure}', occurMeasure_date='{$occur_year}-{$occur_month}-{$occur_day}',
            issue_measure='{$issue_measure}', issueMeasure_date='{$issue_year}-{$issue_month}-{$issue_day}', follow_who='{$follow_who}',
            follow_when='{$follow_year}-{$follow_month}-{$follow_day}', follow_how='{$follow_how}', measure='{$measure}'
            where serial_no={$serial_no}
            ;
        ";
        $update_sql .= "
            UPDATE unfit_report_develop SET
            suihei='$suihei', kanai='$kanai', kagai='$kagai',
            hyoujyun='$hyoujyun', kyouiku='$kyouiku', system='$system'
            where serial_no={$serial_no}
            ;
        ";
        $update_sql .= "
            DELETE FROM unfit_report_attendance WHERE serial_no={$serial_no}
            ;
        ";
        
        for ($i=0; $i<$atten_num; $i++) {
            $update_sql .= "
                INSERT INTO unfit_report_attendance
                (serial_no, atten, mail)
                VALUES
                ({$serial_no}, '{$atten[$i]}', FALSE)
                ;
            ";
        }
        ////// $save_sqlはオプションなので指定しなくても良い
        return $this->execute_Update($update_sql, $save_sql);
    }
    
    ////////// 作成者の名前を取得
    private function getSponsorName($sponsor, &$res)
    {
        $query = "
            SELECT trim(name), trim(mailaddr)
            FROM
                user_detailes
            LEFT OUTER JOIN
                user_master USING(uid)
            WHERE
                uid = '{$sponsor}'
                AND
                retire_date IS NULL     -- 退職していない
                AND
                sid != 31               -- 出向していない
        ";
        $res = array();                                         // 初期化
        if ($this->getResult2($query, $res) < 1) {
            return false;
        } else {
            return true;
        }
    }
    
    ////////// 報告先の名前取得
    private function getAttendanceName($atten, &$atten_name, &$flag)
    {
        $atten_num = count($atten);
        $atten_name = array();
        $flag = array();
        for ($i=0; $i<$atten_num; $i++) {
            $query = "
                SELECT trim(name) FROM user_detailes WHERE uid = '{$atten[$i]}' AND retire_date IS NULL AND sid != 31
            ";
            $atten_name[$i] = '';
            if ($this->getUniResult($query, $atten_name[$i]) < 1) {
                $_SESSION['s_sysmsg'] .= "メール案内で報告先の名前が見つかりません！ [ {$atten[$i]} ]";
                $flag[$i] = 'NG';
            } else {
                $flag[$i] = 'OK';
            }
        }
    }
    
    ////////// 報告先のメールアドレス取得
    private function getAttendanceAddr($atten)
    {
        $query = "
            SELECT trim(mailaddr) FROM user_master WHERE uid = '{$atten}'
        ";
        $atten_addr = '';
        if ($this->getUniResult($query, $atten_addr) < 1) {
            $_SESSION['s_sysmsg'] .= "メール案内で報告先のメールアドレスが見つかりません！ [ {$atten} ]";
        }
        return $atten_addr;
    }
    
    ////////// 報告先の名前をメールに載せるため文字列で一括取得
    private function getAttendanceNameList($atten, $atten_name)
    {
        $atten_num = count($atten);
        $message = '';
        for ($j=0; $j<$atten_num; $j++) {
            if (!$atten_name[$j]) continue;
            if ($j == 0) {
                $message .= "{$atten_name[$j]}";
            } else {
                $message .= ", {$atten_name[$j]}";
            }
        }
        return $message;
    }
    
    ////////// 報告先へのメール送信履歴を保存
    private function setAttendanceMailHistory($serial_no, $atten)
    {
        $update_sql = "
            UPDATE meeting_schedule_attendance SET
                mail=TRUE
            WHERE
                serial_no={$serial_no} AND atten='{$atten}'
        ";
        $this->execute_Update($update_sql);
    }
    
    ////////// クライアントの名前取得
    private function getUserName()
    {
        if (!$_SESSION['User_ID']) {
            return gethostbyaddr($_SERVER['REMOTE_ADDR']);
        }
        $query = "
            SELECT trim(name) FROM user_detailes WHERE uid = '{$_SESSION['User_ID']}' AND retire_date IS NULL AND sid != 31
        ";
        if ($this->getUniResult($query, $userName) < 1) {
            return gethostbyaddr($_SERVER['REMOTE_ADDR']);
        } else {
            return $userName;
        }
    }
    
} //////////// Class UnfitReport_Model End

?>
