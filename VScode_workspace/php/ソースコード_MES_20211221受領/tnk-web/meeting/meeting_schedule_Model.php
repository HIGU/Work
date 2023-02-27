<?php
//////////////////////////////////////////////////////////////////////////////
// 全社共有 打合せ(会議)スケジュール表の照会・メンテナンス                  //
//                                                            MVC Model 部  //
// Copyright (C) 2005-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/11/01 Created   meeting_schedule_Model.php                          //
// 2005/11/21 出席者のグループ指定の追加                                    //
// 2005/11/22 getViewList()メソッドにsubjectの改行を<br>に置換える機能実装  //
//            get_caption()メソッドをList時以外は日付を出さない(仕様変更)   //
//            guideMeetingMail()が$request->get('year')→get('yearReg')修正 //
// 2005/11/27 グループ編集メソッドgroup_edit()に持主が同じかチェックを追加  //
// 2005/12/05 duplicateCheck()メソッドの件名に改行がある場合の対応追加      //
// 2005/12/27 メールの文字化け対策のため subjectの半角カナを全角カナに変換  //
// 2006/05/09 自分のスケジュールのみ表示(マイリスト)機能を追加              //
// 2006/06/19 duplicateCheck()メソッドの$this->whereを削除(日付を変える対策)//
// 2006/07/24 guideMeetingMail()変更   メール案内の日付の右に曜日の表示追加 //
// 2007/03/06 グループ編集時の合計レコード数取得SQL文の不具合を修正         //
//            mb_send_mail()messageの改行コードを\r\n→\nへマニュアルに従う //
// 2007/04/05 debug用のメール送信 tnksys@ をコメントアウト                  //
// 2007/05/08 guideMeetingMail()メソッドに$subject2/$subject3を追加         //
//            str_replace("\n", '...') → str_replace("\r\n", '...')へ変更  //
// 2007/05/10 会議削除時にキャンセルのメール送信のためdelete()メソッドを変更//
//            及びguideMeetingMail()メソッドをキャンセル対応に変更          //
// 2009/12/17 照会・印刷画面追加のテスト（Print）                      大谷 //
// 2015/06/19 計画有給の照会を追加                                     大谷 //
// 2017/11/06 会議室の取得execute_ListNotPageControlへ変更             大谷 //
// 2019/03/15 冷温水機稼働状況、社用車、不在者のメニューを追加         大谷 //
// 2019/03/19 cardupCheck()変更の際の$serial_noが抜けていたので修正    大谷 //
// 2021/06/10 カレンダー移動用の年月はいらなかったので削除             大谷 //
// 2021/07/14 社員一覧で日東工器とその他部門を表示しないよう変更       大谷 //
// 2021/11/17 総合届承認待ち情報の表示関連を追加                       和氣 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード

require_once ('../ComTableMntClass.php');   // TNK 全共通 テーブルメンテ&ページ制御Class


/******************************************************************************
*     打合せ(会議)スケジュール用 MVCのModel部 base class 基底クラスの定義     *
******************************************************************************/
class MeetingSchedule_Model extends ComTableMnt
{
    ///// Private properties
    private $where;
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer の定義 (php5へ移行時は __construct() へ変更予定) (デストラクタ__destruct())
    public function __construct($request)
    {
        // 以下のリクエストはcontrollerより先に取得しているため空の場合がある。
        $year       = $request->get('year');
        $month      = $request->get('month');
        $day        = $request->get('day');
        $listSpan   = $request->get('listSpan');
        $room_no    = $request->get('room_no');
        $car_no     = $request->get('car_no');
        $str_date   = $request->get('str_date');
        $end_date   = $request->get('end_date');
        $OnOff      = $request->get('OnOff');
        if ($str_date == '') {
            $str_date = $year . $month . $day;
        }
        if ($end_date == '') {
            $end_date = $year . $month . $day;
        }
        switch ($request->get('showMenu')) {
        case 'Room':
            $this->where = '';
            $sql_sum = "
                SELECT count(*) FROM meeting_room_master {$this->where}
            ";
            break;
       case 'Car':
            $this->where = '';
            $sql_sum = "
                SELECT count(*) FROM meeting_car_master {$this->where}
            ";
            break;
         case 'Group':
            $this->where = '';
            $sql_sum = "
                SELECT count(*) FROM (SELECT count(group_no) FROM meeting_mail_group GROUP BY group_no {$this->where})
                AS meeting_group
            ";
            break;
        case 'MyList':
            $this->where = "'{$_SESSION['User_ID']}', timestamp '{$year}-{$month}-{$day} 00:00:00', timestamp '{$year}-{$month}-{$day} 23:59:59' + interval '{$listSpan} day'";
            $sql_sum = "
                SELECT count(*) FROM meeting_schedule_mylist({$this->where})
            ";
            break;
        case 'Print' :
            if ($room_no != '') {
                $this->where = "WHERE room_no = {$room_no} and to_char(str_time, 'YYYYMMDD') >= {$str_date} and to_char(end_time, 'YYYYMMDD') <= {$end_date}";
            } else {
                $this->where = "WHERE to_char(str_time, 'YYYYMMDD') >= {$str_date} and to_char(end_time, 'YYYYMMDD') <= {$end_date}";
            }
            $sql_sum = "
                SELECT count(*) FROM meeting_schedule_header {$this->where}
            ";
            break;
        case 'Holyday'  :
            $this->where = "WHERE acq_date>='{$year}-{$month}-{$day}' AND acq_date<=(timestamp '{$year}-{$month}-{$day}' + interval '{$listSpan} day')";
            $sql_sum = "
                SELECT count(*) FROM user_holyday {$this->where}
            ";
            break;
        case 'Absence'  :
            $this->where = "WHERE (start_date = '{$year}-{$month}-{$day}' OR (start_date <= '{$year}-{$month}-{$day}' AND end_date >= '{$year}-{$month}-{$day}'))
                              AND admit_status != 'CANCEL' AND admit_status != 'DENY' 
                              AND content!='IDカード通し忘れ（出勤） '
                              AND content!='IDカード通し忘れ（退勤） '
                              AND content!='時限承認忘れ（残業申告漏れ）'
                              AND content!='IDカード通し忘れ（退勤）＋ 時限承認忘れ（残業申告漏れ）'
                            ";
            $sql_sum = "
                SELECT count(*) FROM sougou_deteils {$this->where}
            ";
            break;
        case 'List'  :
        case 'Apend' :
        case 'Edit'  :
        default      :
            $this->where = "WHERE str_time>='{$year}-{$month}-{$day} 00:00:00' AND str_time<=(timestamp '{$year}-{$month}-{$day} 23:59:59' + interval '{$listSpan} day')";
            $sql_sum = "
                SELECT count(*) FROM meeting_schedule_header {$this->where}
            ";
            break;
        }
        ///// Constructer を定義すると 基底クラスの Constructerが実行されない
        ///// 基底ClassのConstructerはプログラマーの責任で呼出す
        parent::__construct($sql_sum, $request, 'meeting_schedule.log');
    }
    
    ////////// 会議スケジュールの追加
    public function add($request)
    {
        ///// パラメーターの分割
        $year       = $request->get('yearReg');             // 会議予定の年４桁
        $month      = $request->get('monthReg');            // 会議予定の月２桁
        $day        = $request->get('dayReg');              // 会議予定の日２桁
        $subject    = mb_convert_kana($request->get('subject'), 'KV'); // 会議件名 2005/12/27 全角変換追加
        $request->add('subject', $subject);
        $str_time   = $request->get('str_time');            // 開始時間
        $end_time   = $request->get('end_time');            // 終了時間
        $sponsor    = $request->get('sponsor');             // 主催者
        $atten      = $request->get('atten');               // 出席者(attendance) (配列)
        $room_no    = $request->get('room_no');             // 会議室番号
        $car_no     = $request->get('car_no');              // 社用車番号
        $mail       = $request->get('mail');                // メールの送信 Y/N
        // 年月日のチェック  現在は Main Controllerで初期値を設定しているので必要ないが、そのまま残す。
        if ($year == '') {
            // 本日の日付に設定
            $year = date('Y'); $month = date('m'); $day = date('d');
        }
        // 開始・終了 時間の重複チェック
        if ($this->duplicateCheck("{$year}-{$month}-{$day} {$str_time}:00", "{$year}-{$month}-{$day} {$end_time}:00", $room_no)) {
            if ($this->cardupCheck("{$year}-{$month}-{$day} {$str_time}:00", "{$year}-{$month}-{$day} {$end_time}:00", $car_no)) {
                $count_a   = 0;                                 // 出席者人数のカウント
                $count_a   = count($atten);
                $serial_no = $this->add_execute($request);
                if ($serial_no) {
                    if ($mail == 't') {
                        if ($this->guideMeetingMail($request, $serial_no)) {
                            $_SESSION['s_sysmsg'] = 'メールを送信しました。';
                        } else {
                            $_SESSION['s_sysmsg'] = 'メール送信できませんでした。';
                        }
                    }
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = '登録できませんでした。';
                }
            }
        }
        return false;
    }
    
    ////////// 会議スケジュールの完全削除
    public function delete($request)
    {
        ///// パラメーターの分割
        $serial_no  = $request->get('serial_no');           // シリアル番号
        $subject    = $request->get('subject');             // 会議件名
        $mail       = $request->get('mail');                // メールの送信 Y/N
        // 対象スケジュールの存在チェック
        $chk_sql = "
            SELECT subject FROM meeting_schedule_header WHERE serial_no={$serial_no}
        ";
        if ($this->getUniResult($chk_sql, $check) < 1) {     // 指定のシリアル番号の存在チェック
            $_SESSION['s_sysmsg'] = "「{$subject}」は他の人に変更されました！";
        } else {
            if ($mail == 't') {
                if ($this->guideMeetingMail($request, $serial_no, true)) {
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
    
    ////////// 会議スケジュールの変更
    public function edit($request)
    {
        ///// パラメーターの分割
        $serial_no  = $request->get('serial_no');           // 連番(キーフィールド)
        $year       = $request->get('yearReg');             // 会議予定の年４桁
        $month      = $request->get('monthReg');            // 会議予定の月２桁
        $day        = $request->get('dayReg');              // 会議予定の日２桁
        $subject    = mb_convert_kana($request->get('subject'), 'KV'); // 会議件名 2005/12/27 全角変換追加
        $request->add('subject', $subject);
        $str_time   = $request->get('str_time');            // 開始時間
        $end_time   = $request->get('end_time');            // 終了時間
        $room_no    = $request->get('room_no');             // 会議室番号
        $car_no     = $request->get('car_no');             // 社用車番号
        $mail       = $request->get('mail');                // メールの送信 Y/N
        $reSend     = $request->get('reSend');              // 変更時のメールの再送信Yes/No
        // 年月日のチェック
        if ($year == '') {
            // 本日の日付に設定
            $year = date('Y'); $month = date('m'); $day = date('d');
        }
        
        $query = "
            SELECT subject FROM meeting_schedule_header WHERE serial_no={$serial_no}
        ";
        if ($this->getUniResult($query, $check) > 0) {  // 変更前のシリアル番号が登録されているか？
            // 開始・終了 時間の重複チェック
            if ($this->duplicateCheck("{$year}-{$month}-{$day} {$str_time}:00", "{$year}-{$month}-{$day} {$end_time}:00", $room_no, $serial_no)) {
                if ($this->cardupCheck("{$year}-{$month}-{$day} {$str_time}:00", "{$year}-{$month}-{$day} {$end_time}:00", $car_no, $serial_no)) {
                    $response = $this->edit_execute($request);
                    if ($response) {
                        if ($reSend == 't' && $mail == 't') {
                            if ($this->guideMeetingMail($request, $serial_no)) {
                                $_SESSION['s_sysmsg'] = 'メールを再送信しました。';
                            } else {
                                $_SESSION['s_sysmsg'] = 'メールの再送信ができませんでした。';
                            }
                        }
                        return true;
                    } else {
                        $_SESSION['s_sysmsg'] = '変更できませんでした。';
                    }
                }
            }
        } else {
            $_SESSION['s_sysmsg'] = "「{$subject}」は他の人に変更されました！";
        }
        return false;
    }
    
    ////////// 会議室の登録・変更
    public function room_edit($room_no, $room_name, $duplicate)
    {
        ///// room_noの適正チェック
        if (!$this->checkRoomNo($room_no)) {
            return false;
        }
        $query = "
            SELECT room_no, room_name, duplicate FROM meeting_room_master WHERE room_no={$room_no}
        ";
        $res = array();
        if ($this->getResult2($query, $res) <= 0) {
            // 会議室の登録
            $response = $this->roomInsert($room_no, $room_name, $duplicate);
            if ($response) {
                $_SESSION['s_sysmsg'] = "[{$room_no}] {$room_name} を登録しました。";
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '会議室の登録が出来ませんでした！';
            }
        } else {
            // 会議室の変更
            // データが変更されているかチェック
            if ($room_no == $res[0][0] && $room_name == $res[0][1] && $duplicate == $res[0][2]) return true;
            // 会議室の変更 実行
            $response = $this->roomUpdate($room_no, $room_name, $duplicate);
            if ($response) {
                $_SESSION['s_sysmsg'] = "[{$room_no}] {$room_name} を変更しました。";
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '会議室の変更が出来ませんでした！';
            }
        }
        return false;
    }
    
    ////////// 会議室の 削除
    public function room_omit($room_no, $room_name)
    {
        ///// room_noの適正チェック
        if (!$this->checkRoomNo($room_no)) {
            return false;
        }
        $query = "
            SELECT room_no, room_name FROM meeting_room_master WHERE room_no={$room_no}
        ";
        if ($this->getResult2($query, $res) <= 0) {
            $_SESSION['s_sysmsg'] = "[{$room_no}] {$room_name} は削除対象データがありません！";
        } else {
            ///// 削除しても問題ないか過去のデータをチェック
            $query = "
                SELECT subject, to_char(str_time, 'YYYY/MM/DD') FROM meeting_schedule_header WHERE room_no={$room_no} limit 1;
            ";
            $res = array();
            if ($this->getResult2($query, $res) <= 0) {
                $response = $this->roomDelete($room_no);
                if ($response) {
                    $_SESSION['s_sysmsg'] = "[{$room_no}] {$room_name} を削除しました。";
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = "[{$room_no}] {$room_name} を削除出来ませんでした！";
                }
            } else {
                $_SESSION['s_sysmsg'] = "[{$room_no}] {$room_name} は過去 [ {$res[0][1]} ] の日に [ {$res[0][0]} ] で使用されています。削除できません！ 無効にして下さい。";
            }
        }
        return false;
    }
    
    ////////// 会議室の 有効・無効
    public function room_activeSwitch($room_no, $room_name)
    {
        ///// room_noの適正チェック
        if (!$this->checkRoomNo($room_no)) {
            return false;
        }
        $query = "
            SELECT active FROM meeting_room_master WHERE room_no={$room_no}
        ";
        if ($this->getUniResult($query, $active) <= 0) {
            $_SESSION['s_sysmsg'] = "[{$room_no}] {$room_name} の対象データがありません！";
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
                SELECT active FROM meeting_room_master WHERE room_no={$room_no}
            ";
            $update_sql = "
                UPDATE meeting_room_master SET
                active={$active}, last_date='{$last_date}', last_host='{$last_host}'
                WHERE room_no={$room_no}
            "; 
            return $this->execute_Update($update_sql, $save_sql);
        }
        return false;
    }
    
    ////////// 社用車の登録・変更
    public function car_edit($car_no, $car_name, $car_dup)
    {
        ///// car_noの適正チェック
        if (!$this->checkCarNo($car_no)) {
            return false;
        }
        $query = "
            SELECT car_no, car_name, duplicate FROM meeting_car_master WHERE car_no={$car_no}
        ";
        $res = array();
        if ($this->getResult2($query, $res) <= 0) {
            // 社用車の登録
            $response = $this->carInsert($car_no, $car_name, $car_dup);
            if ($response) {
                $_SESSION['s_sysmsg'] = "[{$car_no}] {$car_name} を登録しました。";
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '社用車の登録が出来ませんでした！';
            }
        } else {
            // 社用車の変更
            // データが変更されているかチェック
            if ($car_no == $res[0][0] && $car_name == $res[0][1] && $car_dup == $res[0][2]) return true;
            // 社用車の変更 実行
            $response = $this->carUpdate($car_no, $car_name, $car_dup);
            if ($response) {
                $_SESSION['s_sysmsg'] = "[{$car_no}] {$car_name} を変更しました。";
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '社用車の変更が出来ませんでした！';
            }
        }
        return false;
    }
    
    ////////// 社用車の 削除
    public function car_omit($car_no, $car_name)
    {
        ///// car_noの適正チェック
        if (!$this->checkCarNo($car_no)) {
            return false;
        }
        $query = "
            SELECT car_no, car_name FROM meeting_car_master WHERE car_no={$car_no}
        ";
        if ($this->getResult2($query, $res) <= 0) {
            $_SESSION['s_sysmsg'] = "[{$car_no}] {$car_name} は削除対象データがありません！";
        } else {
            ///// 削除しても問題ないか過去のデータをチェック
            $query = "
                SELECT subject, to_char(str_time, 'YYYY/MM/DD') FROM meeting_schedule_header WHERE car_no={$car_no} limit 1;
            ";
            $res = array();
            if ($this->getResult2($query, $res) <= 0) {
                $response = $this->carDelete($car_no);
                if ($response) {
                    $_SESSION['s_sysmsg'] = "[{$car_no}] {$car_name} を削除しました。";
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = "[{$car_no}] {$car_name} を削除出来ませんでした！";
                }
            } else {
                $_SESSION['s_sysmsg'] = "[{$car_no}] {$car_name} は過去 [ {$res[0][1]} ] の日に [ {$res[0][0]} ] で使用されています。削除できません！ 無効にして下さい。";
            }
        }
        return false;
    }
    
    ////////// 社用車の 有効・無効
    public function car_activeSwitch($car_no, $car_name)
    {
        ///// car_noの適正チェック
        if (!$this->checkCarNo($car_no)) {
            return false;
        }
        $query = "
            SELECT active FROM meeting_car_master WHERE car_no={$car_no}
        ";
        if ($this->getUniResult($query, $active) <= 0) {
            $_SESSION['s_sysmsg'] = "[{$car_no}] {$car_name} の対象データがありません！";
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
                SELECT active FROM meeting_car_master WHERE car_no={$car_no}
            ";
            $update_sql = "
                UPDATE meeting_car_master SET
                active={$active}, last_date='{$last_date}', last_host='{$last_host}'
                WHERE car_no={$car_no}
            "; 
            return $this->execute_Update($update_sql, $save_sql);
        }
        return false;
    }
    
    ////////// 出席者グループの登録・変更
    public function group_edit($group_no, $group_name, $atten, $owner)
    {
        ///// group_noの適正チェック
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
                $_SESSION['s_sysmsg'] = '出席者グループの登録が出来ませんでした！';
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
                $_SESSION['s_sysmsg'] = '出席者グループの変更が出来ませんでした！';
            }
        }
        return false;
    }
    
    ////////// 出席者グループの 削除
    public function group_omit($group_no, $group_name)
    {
        ///// group_noの適正チェック
        if (!$this->checkGroupNo($group_no)) {
            return false;
        }
        $query = "
            SELECT group_no, group_name FROM meeting_mail_group WHERE group_no={$group_no}
        ";
        if ($this->getResult2($query, $res) <= 0) {
            $_SESSION['s_sysmsg'] = "[{$group_no}] {$group_name} は削除対象データがありません！";
        } else {
            ///// 削除しても問題ないか過去のデータをチェックは今回は必要ない
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
    
    ////////// 出席者グループの 有効・無効
    public function group_activeSwitch($group_no, $group_name)
    {
        ///// group_noの適正チェック
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
    ///// List部
    public function getViewList(&$result)
    {
        $query = "
            SELECT serial_no                            -- 00
                ,subject                                -- 01
                ,to_char(str_time, 'YY/MM/DD HH24:MI')  -- 02
                ,to_char(end_time, 'YY/MM/DD HH24:MI')  -- 03
                ,room_name                              -- 04
                ,sponsor                                -- 05
                ,trim(name)             AS 氏名         -- 06
                ,atten_num                              -- 07
                ,CASE
                    WHEN end_time > CURRENT_TIMESTAMP
                    THEN '有効'
                    ELSE '無効'
                 END                    AS 期限         -- 08
                ,to_char(meet.regdate AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 09
                ,to_char(meet.last_date AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 10
                ,meet.last_host                         -- 11
                ,to_char(str_time, 'YYYY')              -- 12
                ,to_char(str_time, 'MM')                -- 13
                ,to_char(str_time, 'DD')                -- 14
                ,CASE
                    WHEN mail THEN '送信する' ELSE '送信しない'
                 END                                    -- 15
                ,car_name                               -- 16
            FROM
                meeting_schedule_header AS meet
            LEFT OUTER JOIN
                meeting_room_master USING(room_no)
            LEFT OUTER JOIN
                meeting_car_master USING(car_no)
            LEFT OUTER JOIN
                user_detailes ON (sponsor=uid)
            {$this->where}
            ORDER BY
                str_time ASC, end_time ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        for ($i=0; $i<$rows; $i++) {
            $res[$i][1] = str_replace("\r\n", '<br>', $res[$i][1]);   // subjectの改行を<br>に置換え
        }
        $result->add_array($res);
        return $rows;
    }
    ////////// MVC の Model 部の結果 表示用のデータ取得
    ///// Holyday部
    public function getViewHolyday(&$result)
    {
        $query = "
            SELECT acq_date                       AS 取得日     -- 01
                ,trim(s.section_name)             AS 所属       -- 02
                ,d.uid                            AS 社員番号   -- 03
                ,trim(d.name)                     AS 氏名       -- 04
            FROM
                user_holyday AS h
            LEFT OUTER JOIN
                user_detailes AS d ON (h.uid=d.uid)
            LEFT OUTER JOIN
                section_master AS s ON (d.sid=s.sid)
            {$this->where}
            ORDER BY
                acq_date ASC, s.section_name ASC, d.uid ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    ///// MyList部
    public function getViewMyList(&$result)
    {
        $query = "
            SELECT serial_no                            -- 00
                ,subject                                -- 01
                ,to_char(str_time, 'YY/MM/DD HH24:MI')  -- 02
                ,to_char(end_time, 'YY/MM/DD HH24:MI')  -- 03
                ,room_name                              -- 04
                ,sponsor                                -- 05
                ,trim(name)             AS 氏名         -- 06
                ,atten_num                              -- 07
                ,CASE
                    WHEN end_time > CURRENT_TIMESTAMP
                    THEN '有効'
                    ELSE '無効'
                 END                    AS 期限         -- 08
                ,to_char(meet.regdate AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 09
                ,to_char(meet.last_date AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 10
                ,meet.last_host                         -- 11
                ,to_char(str_time, 'YYYY')              -- 12
                ,to_char(str_time, 'MM')                -- 13
                ,to_char(str_time, 'DD')                -- 14
                ,CASE
                    WHEN mail THEN '送信する' ELSE '送信しない'
                 END                                    -- 15
            FROM
                meeting_schedule_mylist({$this->where}) AS meet
            LEFT OUTER JOIN
                meeting_room_master USING(room_no)
            LEFT OUTER JOIN
                user_detailes ON (sponsor=uid)
            ORDER BY
                str_time ASC, end_time ASC
        ";
        /*
        $query = "
            SELECT serial_no                            -- 00
                ,subject                                -- 01
                ,to_char(str_time, 'YY/MM/DD HH24:MI')  -- 02
                ,to_char(end_time, 'YY/MM/DD HH24:MI')  -- 03
                ,room_name                              -- 04
                ,sponsor                                -- 05
                ,trim(name)             AS 氏名         -- 06
                ,atten_num                              -- 07
                ,CASE
                    WHEN end_time > CURRENT_TIMESTAMP
                    THEN '有効'
                    ELSE '無効'
                 END                    AS 期限         -- 08
                ,to_char(meet.regdate AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 09
                ,to_char(meet.last_date AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 10
                ,meet.last_host                         -- 11
                ,to_char(str_time, 'YYYY')              -- 12
                ,to_char(str_time, 'MM')                -- 13
                ,to_char(str_time, 'DD')                -- 14
                ,CASE
                    WHEN mail THEN '送信する' ELSE '送信しない'
                 END                                    -- 15
                ,room_name                              -- 16
            FROM
                meeting_schedule_mylist({$this->where}) AS meet
            LEFT OUTER JOIN
                meeting_room_master USING(room_no)
            LEFT OUTER JOIN
                meeting_car_master USING(car_no)
            LEFT OUTER JOIN
                user_detailes ON (sponsor=uid)
            ORDER BY
                str_time ASC, end_time ASC
        ";
        */
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        for ($i=0; $i<$rows; $i++) {
            $res[$i][1] = str_replace("\r\n", '<br>', $res[$i][1]);   // subjectの改行を<br>に置換え
        }
        $result->add_array($res);
        return $rows;
    }
    ///// 出席者の List部 attendance 複数対応
    public function getViewAttenList(&$result, $serial_no)
    {
        $query_a = "
            SELECT serial_no                            -- 00
                ,atten                                  -- 01
                ,trim(name)                             -- 02
                ,CASE
                    WHEN mail THEN '送信済'
                    ELSE '未送信'
                 END                                    -- 03
            FROM
                meeting_schedule_attendance AS meet
            LEFT OUTER JOIN
                user_detailes ON (atten=uid)
            WHERE
                serial_no = {$serial_no}
            ORDER BY
                atten ASC
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query_a, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// 照会・印刷 List部
    public function getPrintList(&$result)
    {
        $query_p = "
            SELECT serial_no                            -- 00
                ,subject                                -- 01
                ,to_char(str_time, 'YY/MM/DD HH24:MI')  -- 02
                ,to_char(end_time, 'YY/MM/DD HH24:MI')  -- 03
                ,room_name                              -- 04
                ,sponsor                                -- 05
                ,trim(name)             AS 氏名         -- 06
                ,atten_num                              -- 07
                ,CASE
                    WHEN end_time > CURRENT_TIMESTAMP
                    THEN '有効'
                    ELSE '無効'
                 END                    AS 期限         -- 08
                ,to_char(meet.regdate AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 09
                ,to_char(meet.last_date AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 10
                ,meet.last_host                         -- 11
                ,to_char(str_time, 'YYYY')              -- 12
                ,to_char(str_time, 'MM')                -- 13
                ,to_char(str_time, 'DD')                -- 14
                ,to_char(end_time, 'YYYY')              -- 15
                ,to_char(end_time, 'MM')                -- 16
                ,to_char(end_time, 'DD')                -- 17
                ,CASE
                    WHEN mail THEN '送信する' ELSE '送信しない'
                 END                                    -- 18
            FROM
                meeting_schedule_header AS meet
            LEFT OUTER JOIN
                meeting_room_master USING(room_no)
            LEFT OUTER JOIN
                user_detailes ON (sponsor=uid)
            {$this->where}
            ORDER BY
                room_no ASC, str_time ASC, end_time ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query_p, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        for ($i=0; $i<$rows; $i++) {
            $res[$i][1] = str_replace("\r\n", '<br>', $res[$i][1]);   // subjectの改行を<br>に置換え
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// 部署毎の社員番号と氏名を取得
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
                sid != 31 AND sid != 95 AND sid != 90
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
    
    ///// Edit 時の 1レコード分
    public function getViewEdit($serial_no, $result)
    {
        $query = "
            SELECT serial_no                    -- 00
                ,subject                        -- 01
                ,to_char(str_time, 'HH24:MI')   -- 02
                ,to_char(end_time, 'HH24:MI')   -- 03
                ,room_no                        -- 04
                ,sponsor                        -- 05
                ,atten_num                      -- 06
                ,mail                           -- 07
                ,room_name                      -- 08
                ,to_char(str_time, 'YYYY')      -- 09
                ,to_char(str_time, 'MM')        -- 10
                ,to_char(str_time, 'DD')        -- 11
                ,car_no                         -- 12
            FROM
                meeting_schedule_header
            LEFT OUTER JOIN
                meeting_room_master USING(room_no)
            WHERE
                serial_no = {$serial_no}
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            $result->add_once('serial_no',  $res[0][0]);
            $result->add_once('subject',    $res[0][1]);
            $result->add_once('str_time',   $res[0][2]);
            $result->add_once('end_time',   $res[0][3]);
            $result->add_once('room_no',    $res[0][4]);
            $result->add_once('sponsor',    $res[0][5]);
            $result->add_once('atten_num',  $res[0][6]);
            $result->add_once('mail',       $res[0][7]);
            $result->add_once('room_name',  $res[0][8]);
            $result->add_once('editYear',   $res[0][9]);
            $result->add_once('editMonth',  $res[0][10]);
            $result->add_once('editDay',    $res[0][11]);
            $result->add_once('car_no',     $res[0][12]);
        }
        return $rows;
    }
    
    ///// List時の 表題(キャプション)の生成
    public function get_caption($switch, $year, $month, $day)
    {
        switch ($switch) {
        case 'List':
            // $caption = '会議(打合せ) 一覧';
            $caption = '〜';
            $caption = sprintf("%04d年%02d月%02d日{$caption}", $year, $month, $day);
            break;
        case 'Apend':
            $caption = '会議(打合せ)の追加';
            break;
        case 'Edit':
            $caption = '会議(打合せ)の編集';
            break;
        default:
            $caption = '';
        }
        return $caption;
        
    }
    
    ///// List時の 登録データがない場合のメッセージ生成
    public function get_noDataMessage($year, $month, $day)
    {
        if ($year != '') {
            if (sprintf('%04d%02d%02d', $year, $month, $day) < date('Ymd')) {
                $noDataMessage = '登録がありません。';  // 過去の場合
            } else {
                $noDataMessage = '予定がありません。';  // 未来の場合
            }
        } else {
            // 本日の場合
            $noDataMessage = '予定がありません。';
        }
        return $noDataMessage;
        
    }
    
    ///// 会議室の List部
    public function getViewRoomList(&$result)
    {
        $query = "
            SELECT room_no                              -- 00
                ,room_name                              -- 01
                ,CASE
                    WHEN duplicate THEN 'する'
                    ELSE 'しない'
                 END                    AS 重複         -- 02
                ,CASE
                    WHEN active THEN '有効'
                    ELSE '無効'
                 END                    AS 有効無効     -- 03
                ,to_char(regdate AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 04
                ,to_char(last_date AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 05
            FROM
                meeting_room_master
            ORDER BY
                room_no ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// 会議室の <select>表示用 List部
    public function getActiveRoomList(&$result)
    {
        $query = "
            SELECT room_no                              -- 00
                ,room_name                              -- 01
            FROM
                meeting_room_master
            WHERE
                active IS TRUE
            ORDER BY
                room_no ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_ListNotPageControl($query, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// 社用車の List部
    public function getViewCarList(&$result)
    {
        $query = "
            SELECT car_no                              -- 00
                ,car_name                              -- 01
                ,CASE
                    WHEN duplicate THEN 'する'
                    ELSE 'しない'
                 END                    AS 重複         -- 02
                ,CASE
                    WHEN active THEN '有効'
                    ELSE '無効'
                 END                    AS 有効無効     -- 03
                ,to_char(regdate AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 04
                ,to_char(last_date AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 05
            FROM
                meeting_car_master
            ORDER BY
                car_no ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// 社用車の <select>表示用 List部
    public function getActiveCarList(&$result)
    {
        $query = "
            SELECT car_no                              -- 00
                ,car_name                              -- 01
            FROM
                meeting_car_master
            WHERE
                active IS TRUE
            ORDER BY
                car_no ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_ListNotPageControl($query, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// 出席者グループの List部
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
            // $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// 出席者グループの １グループ分 Attendance List部
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
            // $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// 出席者グループの有効なリスト Active List部
    // JSgroup_name=グループ名の１次元配列, JSgroup_member=グループ名に対応した出席者の２次元配列, 戻り値=有効件数
    // owner='000000'は共有グループ, 指定がある場合は個人のグループ
    public function getActiveGroupList(&$JSgroup_name, &$JSgroup_member, $uid)
    {
        // 初期化
        $JSgroup_name = array();
        $JSgroup_member = array();
        // グループ名の配列の取得
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
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// 会議室のroom_noの適正をチェックしメッセージ＋結果(true=OK,false=NG)を返す
    protected function checkRoomNo($room_no)
    {
        ///// room_noの適正チェック
        if (is_numeric($room_no)) {
            if ($room_no >= 1 && $room_no <= 32000) {   // int2に対応
                return true;
            } else {
                $_SESSION['s_sysmsg'] = "会議室の番号 {$room_no} は範囲外です！ 1〜32000までです。";
            }
        } else {
            $_SESSION['s_sysmsg'] = "会議室の番号 {$room_no} は数字以外が含まれています。";
        }
        return false;
    }
    
    ////////// 社用車のcar_noの適正をチェックしメッセージ＋結果(true=OK,false=NG)を返す
    protected function checkCarNo($car_no)
    {
        ///// car_noの適正チェック
        if (is_numeric($car_no)) {
            if ($car_no >= 1 && $car_no <= 32000) {   // int2に対応
                return true;
            } else {
                $_SESSION['s_sysmsg'] = "社用車の番号 {$car_no} は範囲外です！ 1〜32000までです。";
            }
        } else {
            $_SESSION['s_sysmsg'] = "社用車の番号 {$car_no} は数字以外が含まれています。";
        }
        return false;
    }
    
    ////////// 会議室のroom_noの適正をチェックしメッセージ＋結果(true=OK,false=NG)を返す
    protected function checkGroupNo($group_no)
    {
        ///// group_noの適正チェック
        if (is_numeric($group_no)) {
            if ($group_no >= 1 && $group_no <= 999) {   // int2 以内が実際の範囲
                return true;
            } else {
                $_SESSION['s_sysmsg'] = "出席者のグループ番号 {$group_no} は範囲外です！ 1〜999までです。";
            }
        } else {
            $_SESSION['s_sysmsg'] = "出席者のグループ番号 {$group_no} は数字以外が含まれています。";
        }
        return false;
    }
    
    ////////// 会議室の登録 (実行部)
    protected function roomInsert($room_no, $room_name, $duplicate)
    {
        // ここに last_date last_host の登録処理を入れる
        // regdate=自動登録
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        // $duplicate は 't' 又は 'f' なので そのまま使う
        $insert_sql = "
            INSERT INTO meeting_room_master
            (room_no, room_name, duplicate, active, last_date, last_host)
            VALUES
            ('$room_no', '$room_name', '$duplicate', TRUE, '$last_date', '$last_host')
        ";
        return $this->execute_Insert($insert_sql);
    }
    
    ////////// 会議室の変更 (実行部)
    protected function roomUpdate($room_no, $room_name, $duplicate)
    {
        // ここに last_date last_host の登録処理を入れる
        // regdate=自動登録
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        // 保存用のSQL文を設定
        $save_sql = "
            SELECT * FROM meeting_room_master WHERE room_no={$room_no}
        ";
        // $duplicate は 't' 又は 'f' なので そのまま使う
        $update_sql = "
            UPDATE meeting_room_master SET
            room_no={$room_no}, room_name='{$room_name}', duplicate='{$duplicate}', last_date='{$last_date}', last_host='{$last_host}'
            WHERE room_no={$room_no}
        "; 
        return $this->execute_Update($update_sql, $save_sql);
    }
    
    ////////// 会議室の削除 (実行部)
    protected function roomDelete($room_no)
    {
        // 保存用のSQL文を設定
        $save_sql   = "
            SELECT * FROM meeting_room_master WHERE room_no={$room_no}
        ";
        // 削除用SQL文を設定
        $delete_sql = "
            DELETE FROM meeting_room_master WHERE room_no={$room_no}
        ";
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
    ////////// 社用車の登録 (実行部)
    protected function carInsert($car_no, $car_name, $car_dup)
    {
        // ここに last_date last_host の登録処理を入れる
        // regdate=自動登録
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        // $duplicate は 't' 又は 'f' なので そのまま使う
        $insert_sql = "
            INSERT INTO meeting_car_master
            (car_no, car_name, duplicate, active, last_date, last_host)
            VALUES
            ('$car_no', '$car_name', '$car_dup', TRUE, '$last_date', '$last_host')
        ";
        return $this->execute_Insert($insert_sql);
    }
    
    ////////// 社用車の変更 (実行部)
    protected function carUpdate($car_no, $car_name, $car_dup)
    {
        // ここに last_date last_host の登録処理を入れる
        // regdate=自動登録
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        // 保存用のSQL文を設定
        $save_sql = "
            SELECT * FROM meeting_car_master WHERE car_no={$car_no}
        ";
        // $duplicate は 't' 又は 'f' なので そのまま使う
        $update_sql = "
            UPDATE meeting_car_master SET
            car_no={$car_no}, car_name='{$car_name}', duplicate='{$car_dup}', last_date='{$last_date}', last_host='{$last_host}'
            WHERE car_no={$car_no}
        "; 
        return $this->execute_Update($update_sql, $save_sql);
    }
    
    ////////// 社用車の削除 (実行部)
    protected function carDelete($car_no)
    {
        // 保存用のSQL文を設定
        $save_sql   = "
            SELECT * FROM meeting_car_master WHERE car_no={$car_no}
        ";
        // 削除用SQL文を設定
        $delete_sql = "
            DELETE FROM meeting_car_master WHERE car_no={$car_no}
        ";
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
    ////////// 会議(打合せ)の案内を email で出だす
    protected function guideMeetingMail($request, $serial_no, $cancel=false)
    {
        ///// パラメーターの分割
        $year       = $request->get('yearReg');             // 会議予定の年４桁
        $month      = $request->get('monthReg');            // 会議予定の月２桁
        $day        = $request->get('dayReg');              // 会議予定の日２桁
        $subject    = $request->get('subject');             // 会議件名
        $subject2   = str_replace("\r\n", "\r\n　　　　　　", $subject);  // subjectの改行をスペースを付加したものに置換え
        $subject3   = str_replace("\r\n", '　', $subject);  // subjectの改行をスペースに置換え
        $str_time   = $request->get('str_time');            // 開始時間
        $end_time   = $request->get('end_time');            // 終了時間
        $sponsor    = $request->get('sponsor');             // 主催者
        $atten      = $request->get('atten');               // 出席者(attendance) (配列)
        $atten_num  = count($atten);                        // 出席者数
        $room_no    = $request->get('room_no');             // 会議室番号
        $mail       = $request->get('mail');                // メールの送信 Y/N
        ///// 曜日を取得する 2006/07/24 ADD
        $week = array('日', '月', '火', '水', '木', '金', '土');
        $dayWeek = $week[date('w', mktime(0, 0, 0, $month, $day, $year))];
        // 主催者の名前を取得
        if (!$this->getSponsorName($sponsor, $res)) {
            $_SESSION['s_sysmsg'] = "メール案内で主催者の名前が見つかりません！ [ $sponsor ]";
        } else {
            $sponsor_name = $res[0][0];
            $sponsor_addr = $res[0][1];
            // 会議室名の取得
            $room_name = $this->getRoomName($room_no);
            // 出席者の名前取得 (引数３個は全て配列)
            $this->getAttendanceName($atten, $atten_name, $flag);
            // 出席者のメールアドレスの取得とメール送信
            for ($i=0; $i<$atten_num; $i++) {
                if ($flag[$i] == 'NG') continue;
                // 出席者のメールアドレス取得
                if ( !($atten_addr=$this->getAttendanceAddr($atten[$i])) ) {
                    continue;
                }
                $to_addres = $atten_addr;
                $message  = "この案内は {$sponsor_name} さんが出席者にメール案内を出す設定にしたため送信されたものです。\n\n";
                $message .= "{$subject}\n\n";
                if ($cancel) {
                    $message .= "下記の会議(打合せ)が{$this->getUserName()}さんによりキャンセル(削除)されましたので、ご連絡致します。\n\n";
                } else {
                    $message .= "下記の日時で行われますので、ご出席お願い致します。\n\n";
                }
                $message .= "                               記\n\n";
                $message .= "１. 開催日：{$year}年 {$month}月 {$day}日({$dayWeek})\n\n";
                $message .= "２. 時　間：{$str_time} 〜 {$end_time}\n\n";
                $message .= "３. 場　所：{$room_name}\n\n";
                $message .= "４. 主催者：{$sponsor_name}\n\n";
                $message .= "５. 出席者：{$this->getAttendanceNameList($atten, $atten_name)}";
                $message .= "\n\n";
                $message .= "６. 会議名：{$subject2}\n\n";
                $message .= "以上、宜しくお願い致します。\n\n";
                $add_head = "From: {$sponsor_addr}\r\nReply-To: {$sponsor_addr}";
                $attenSubject = '宛先： ' . $atten_name[$i] . ' 様　 ' . $subject3;
                if (mb_send_mail($to_addres, $attenSubject, $message, $add_head)) {
                    // 出席者へのメール送信履歴を保存
                    $this->setAttendanceMailHistory($serial_no, $atten[$i]);
                }
                ///// Debug
                if ($cancel) {
                    if ($i == 0) mb_send_mail('tnksys@nitto-kohki.co.jp', $attenSubject, $message, $add_head);
                }
            }
            return true;
        }
        return false;
    }
    
    ////////// 出席者グループの登録 (実行部)
    protected function groupInsert($group_no, $group_name, $atten, $owner)
    {
        // ここに last_date last_host の登録処理を入れる
        // regdate=自動登録
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
    
    ////////// 出席者グループの変更 (実行部)
    protected function groupUpdate($group_no, $group_name, $atten, $owner)
    {
        // ここに last_date last_host の登録処理を入れる
        // regdate=自動登録
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        // 保存用のSQL文を設定
        $save_sql = "
            SELECT * FROM meeting_mail_group WHERE group_no={$group_no}
        ";
        $update_sql = '';
        $update_sql .= "
            DELETE FROM meeting_mail_group WHERE group_no={$group_no}
            ;
        "; 
        $cnt = count($atten);
        ///// 有効・無効の active は変更時に 常に有効となる
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
    
    ////////// 出席者グループの削除 (実行部)
    protected function groupDelete($group_no)
    {
        // 保存用のSQL文を設定
        $save_sql   = "
            SELECT * FROM meeting_mail_group WHERE group_no={$group_no}
        ";
        // 削除用SQL文を設定
        $delete_sql = "
            DELETE FROM meeting_mail_group WHERE group_no={$group_no}
        ";
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// 会議の重複チェック(会議室の重複チェック指定がされているものだけ)
    // string $str_timestamp=開始時間(DBのTIMESTAMP型), string $end_time=終了時間(DBのTIMESTAMP型),
    // int $room=会議室番号, [int $serial_no=変更時の元データの連番]
    private function duplicateCheck($str_timestamp, $end_timestamp, $room_no, $serial_no=0)
    {
        // データ変更時の元データの除外指定
        $deselect = "AND serial_no != {$serial_no}";
        // 会議室マスターで重複チェックになっているか？
        $query = "
            SELECT duplicate FROM meeting_room_master WHERE room_no={$room_no}
        ";
        if ($this->getUniResult($query, $duplicate) <= 0) {
            return true;
        } else {
            if ($duplicate == 'f') return true;
        }

        $no_mi_so_all = 23;   // 大会議室（北/中/南）
        $no_mi_multi  = 24;   // 大会議室（北/中/  ）
        $mi_so_multi  = 25;   // 大会議室（  /中/南）
        $north_only   = 20;     // 大会議室（北/  /  ）
        $middle_only  = 21;     // 大会議室（  /中/  ）
        $south_only   = 22;     // 大会議室（  /  /南）
        $where_room = "(room_no = $room_no";
        switch ($room_no) {
            case $no_mi_so_all: // 大会議室（北/中/南）
                $where_room .= " OR room_no = $no_mi_multi OR room_no = $mi_so_multi OR room_no = $north_only OR room_no = $middle_only OR room_no = $south_only) ";
                break;
            case $no_mi_multi:  // 大会議室（北/中/  ）
                $where_room .= " OR room_no = $no_mi_so_all OR room_no = $mi_so_multi OR room_no = $north_only OR room_no = $middle_only ) ";
                break;
            case $mi_so_multi:  // 大会議室（  /中/南）
                $where_room .= " OR room_no = $no_mi_so_all OR room_no = $no_mi_multi OR room_no = $middle_only OR room_no = $south_only) ";
                break;
            case $north_only:   // 大会議室（北/  /  ）
                $where_room .= " OR room_no = $no_mi_so_all OR room_no = $no_mi_multi) ";
                break;
            case $middle_only:  // 大会議室（  /中/  ）
                $where_room .= " OR room_no = $no_mi_so_all OR room_no = $no_mi_multi OR room_no = $mi_so_multi) ";
                break;
            case $south_only:   // 大会議室（  /  /南）
                $where_room .= " OR room_no = $no_mi_so_all OR room_no = $mi_so_multi) ";
                break;
            default:            // それ以外
                $where_room .= ") ";
                break;
        }

        // 開始時間の重複チェック
        $chk_sql1 = "
            SELECT subject FROM meeting_schedule_header
            WHERE str_time < '{$str_timestamp}'
            AND end_time > '{$str_timestamp}'
            AND {$where_room}
            {$deselect}
            limit 1
        ";
        // 終了時間の重複チェック
        $chk_sql2 = "
            SELECT subject FROM meeting_schedule_header
            WHERE str_time < '{$end_timestamp}'
            AND end_time > '{$end_timestamp}'
            AND {$where_room}
            {$deselect}
            limit 1
        ";
        // 全体の重複チェック
        $chk_sql3 = "
            SELECT subject FROM meeting_schedule_header
            WHERE str_time >= '{$str_timestamp}'
            AND end_time <= '{$end_timestamp}'
            AND {$where_room}
            {$deselect}
            limit 1
        ";
        if ($this->getUniResult($chk_sql1, $check) > 0) {           // 開始時間の重複チェック
            $check = str_replace("\r", '　', $check);               // 件名の改行をスペースへ変換
            $check = str_replace("\n", '　', $check);               // 件名の改行をスペースへ変換
            $_SESSION['s_sysmsg'] = "開始時間が　「{$check}」　と重複しています。";
            return false;
        } elseif ($this->getUniResult($chk_sql2, $check) > 0) {     // 終了時間の重複チェック
            $check = str_replace("\r", '　', $check);               // 件名の改行をスペースへ変換
            $check = str_replace("\n", '　', $check);               // 件名の改行をスペースへ変換
            $_SESSION['s_sysmsg'] = "終了時間が　「{$check}」　と重複しています。";
            return false;
        } elseif ($this->getUniResult($chk_sql3, $check) > 0) {     // 全体の重複チェック
            $check = str_replace("\r", '　', $check);               // 件名の改行をスペースへ変換
            $check = str_replace("\n", '　', $check);               // 件名の改行をスペースへ変換
            $_SESSION['s_sysmsg'] = "「{$check}」　と重複しています。";
            return false;
        } else {
            return true;    // 重複なし
        }
    }
    
    ////////// 社用車の重複チェック(社用車の重複チェック指定がされているものだけ)
    // string $str_timestamp=開始時間(DBのTIMESTAMP型), string $end_time=終了時間(DBのTIMESTAMP型),
    // int $car=社用車番号, [int $serial_no=変更時の元データの連番]
    private function cardupCheck($str_timestamp, $end_timestamp, $car_no, $serial_no=0)
    {
        if ($car_no !='') {
            // データ変更時の元データの除外指定
            $deselect = "AND serial_no != {$serial_no}";
            // 社用車マスターで重複チェックになっているか？
            $query = "
                SELECT duplicate FROM meeting_car_master WHERE car_no={$car_no}
            ";
            if ($this->getUniResult($query, $car_dup) <= 0) {
                return true;
            } else {
                if ($car_dup == 'f') return true;
            }
            // 開始時間の重複チェック
            $chk_sql1 = "
                SELECT subject FROM meeting_schedule_header
                WHERE str_time < '{$str_timestamp}'
                AND end_time > '{$str_timestamp}'
                AND car_no = {$car_no}
                {$deselect}
                limit 1
            ";
            // 終了時間の重複チェック
            $chk_sql2 = "
                SELECT subject FROM meeting_schedule_header
                WHERE str_time < '{$end_timestamp}'
                AND end_time > '{$end_timestamp}'
                AND car_no = {$car_no}
                {$deselect}
                limit 1
            ";
            // 全体の重複チェック
            $chk_sql3 = "
                SELECT subject FROM meeting_schedule_header
                WHERE str_time >= '{$str_timestamp}'
                AND end_time <= '{$end_timestamp}'
                AND car_no = {$car_no}
                {$deselect}
                limit 1
            ";
            if ($this->getUniResult($chk_sql1, $check) > 0) {           // 開始時間の重複チェック
                $check = str_replace("\r", '　', $check);               // 件名の改行をスペースへ変換
                $check = str_replace("\n", '　', $check);               // 件名の改行をスペースへ変換
                $_SESSION['s_sysmsg'] = "開始時間が　「{$check}」　と重複しています。";
                return false;
            } elseif ($this->getUniResult($chk_sql2, $check) > 0) {     // 終了時間の重複チェック
                $check = str_replace("\r", '　', $check);               // 件名の改行をスペースへ変換
                $check = str_replace("\n", '　', $check);               // 件名の改行をスペースへ変換
                $_SESSION['s_sysmsg'] = "終了時間が　「{$check}」　と重複しています。";
                return false;
            } elseif ($this->getUniResult($chk_sql3, $check) > 0) {     // 全体の重複チェック
                $check = str_replace("\r", '　', $check);               // 件名の改行をスペースへ変換
                $check = str_replace("\n", '　', $check);               // 件名の改行をスペースへ変換
                $_SESSION['s_sysmsg'] = "「{$check}」　と重複しています。";
                return false;
            } else {
                return true;    // 重複なし
            }
        } else {
            return true;    // 登録無し
        }
    }
    
    ////////// 会議スケジュールの実行部 追加
    private function add_execute($request)
    {
        ///// パラメーターの分割
        $year       = $request->get('yearReg');             // 会議予定の年４桁
        $month      = $request->get('monthReg');            // 会議予定の月２桁
        $day        = $request->get('dayReg');              // 会議予定の日２桁
        $subject    = $request->get('subject');             // 会議件名
        $str_time   = $request->get('str_time');            // 開始時間
        $end_time   = $request->get('end_time');            // 終了時間
        $sponsor    = $request->get('sponsor');             // 主催者
        $atten      = $request->get('atten');               // 出席者(attendance) (配列)
        $room_no    = $request->get('room_no');             // 会議室番号
        $car_no     = $request->get('car_no');              // 社用車番号
        $mail       = $request->get('mail');                // メールの送信 Y/N
        // メール送信 Y/N を boolean型に変換
        if ($mail == 't') $mail = 'TRUE'; else $mail = 'FALSE';
        // ここに last_date last_host の登録処理を入れる
        // regdate=自動登録
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']);
        // 出席者の人数を取得
        $atten_num = count($atten);
        $insert_qry = "
            INSERT INTO meeting_schedule_header
            (subject, str_time, end_time, room_no, sponsor, atten_num, mail, last_date, last_host, car_no)
            VALUES
            ('$subject', '{$year}-{$month}-{$day} {$str_time}', '{$year}-{$month}-{$day} {$end_time}', $room_no, '$sponsor', $atten_num, $mail, '$last_date', '$last_host', $car_no)
            ;
        ";
        for ($i=0; $i<$atten_num; $i++) {
            $insert_qry .= "
                INSERT INTO meeting_schedule_attendance
                (serial_no, atten, mail)
                VALUES
                ((SELECT max(serial_no) FROM meeting_schedule_header), '{$atten[$i]}', FALSE)
                ;
            ";
        }
        if ($this->execute_Insert($insert_qry)) {
            $query = "SELECT max(serial_no) FROM meeting_schedule_header";
            $serial_no = false;     // 初期値
            $this->getUniResult($query, $serial_no);
            return $serial_no;      // 登録したシリアル番号を返す
        } else {
            return false;
        }
    }
    
    ////////// 会議スケジュールの実行部 削除(完全)
    private function del_execute($serial_no, $subject)
    {
        // 保存用のSQL文を設定
        $save_sql   = "
            SELECT * FROM meeting_schedule_header WHERE serial_no={$serial_no}
        ";
        $delete_sql = "
            DELETE FROM meeting_schedule_header WHERE serial_no={$serial_no}
            ;
        ";
        $delete_sql .= "
            DELETE FROM meeting_schedule_attendance WHERE serial_no={$serial_no}
            ;
        ";
        // $save_sqlはオプションなので指定しなくても良い
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
    ////////// 会議スケジュールの実行部 変更
    private function edit_execute($request)
    {
        ///// パラメーターの分割
        $serial_no  = $request->get('serial_no');           // 連番(キーフィールド)
        $year       = $request->get('yearReg');             // 会議予定の年４桁
        $month      = $request->get('monthReg');            // 会議予定の月２桁
        $day        = $request->get('dayReg');              // 会議予定の日２桁
        $subject    = $request->get('subject');             // 会議件名
        $str_time   = $request->get('str_time');            // 開始時間
        $end_time   = $request->get('end_time');            // 終了時間
        $sponsor    = $request->get('sponsor');             // 主催者
        $atten      = $request->get('atten');               // 出席者(attendance) (配列)
        $room_no    = $request->get('room_no');             // 会議室番号
        $car_no     = $request->get('car_no');              // 社用車番号
        $mail       = $request->get('mail');                // メールの送信 Y/N
        // 保存用のSQL文を設定
        $save_sql = "
            SELECT * FROM meeting_schedule_header WHERE serial_no={$serial_no}
        ";
        // ここに last_date last_host の登録処理を入れる
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']);
        // 出席者の人数を取得
        $atten_num = count($atten);
        $update_sql = "
            UPDATE meeting_schedule_header SET
            subject='{$subject}', str_time='{$year}-{$month}-{$day} {$str_time}', end_time='{$year}-{$month}-{$day} {$end_time}',
            room_no={$room_no}, sponsor='{$sponsor}', atten_num='{$atten_num}', mail='{$mail}',
            last_date='{$last_date}', last_host='{$last_host}', car_no='{$car_no}'
            where serial_no={$serial_no}
            ;
        "; 
        $update_sql .= "
            DELETE FROM meeting_schedule_attendance WHERE serial_no={$serial_no}
            ;
        ";
        for ($i=0; $i<$atten_num; $i++) {
            $update_sql .= "
                INSERT INTO meeting_schedule_attendance
                (serial_no, atten, mail)
                VALUES
                ({$serial_no}, '{$atten[$i]}', FALSE)
                ;
            ";
        }
        // $save_sqlはオプションなので指定しなくても良い
        return $this->execute_Update($update_sql, $save_sql);
    }
    
    ////////// 主催者の名前を取得
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
        $res = array();     // 初期化
        if ($this->getResult2($query, $res) < 1) {
            return false;
        } else {
            return true;
        }
    }
    
    ////////// 会議室名の取得
    private function getRoomName($room_no)
    {
        $query = "
            SELECT trim(room_name) FROM meeting_room_master WHERE room_no={$room_no}
        ";
        $room_name = '';    // 初期化
        $this->getUniResult($query, $room_name);
        return $room_name;
    }
    
    ////////// 社用車名の取得
    private function getCarName($room_no)
    {
        $query = "
            SELECT trim(car_name) FROM meeting_car_master WHERE car_no={$car_no}
        ";
        $car_name = '';    // 初期化
        $this->getUniResult($query, $car_name);
        return $car_name;
    }
    
    ////////// 出席者の名前取得
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
                $_SESSION['s_sysmsg'] .= "メール案内で出席者の名前が見つかりません！ [ {$atten[$i]} ]";
                $flag[$i] = 'NG';
            } else {
                $flag[$i] = 'OK';
            }
        }
    }
    
    ////////// 出席者のメールアドレス取得
    private function getAttendanceAddr($atten)
    {
        $query = "
            SELECT trim(mailaddr) FROM user_master WHERE uid = '{$atten}'
        ";
        $atten_addr = '';
        if ($this->getUniResult($query, $atten_addr) < 1) {
            $_SESSION['s_sysmsg'] .= "メール案内で出席者のメールアドレスが見つかりません！ [ {$atten} ]";
        }
        return $atten_addr;
    }
    
    ////////// 出席者の名前をメールに載せるため文字列で一括取得
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
    
    ////////// 出席者へのメール送信履歴を保存
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
    
    ////////// 計画有給登録の削除
    public function hdelete($request)
    {
        ///// パラメーターの分割
        $uid        = $request->get('uid_no');           // 社員番号
        $acq_date   = $request->get('acq_date');         // 取得日
        // 対象計画有給の存在チェック
        $chk_sql = "
            SELECT uid FROM user_holyday WHERE uid='$uid' and acq_date='$acq_date'
        ";
        if ($this->getUniResult($chk_sql, $check) < 1) {     // 指定の計画有給の存在チェック
            $_SESSION['s_sysmsg'] = "対象の計画有給はありません！";
        } else {
            $query="DELETE FROM user_holyday WHERE uid='$uid' and acq_date='$acq_date'";
            if ($this->getUniResult($query, $rows) < 1) {     // 指定の計画有給の存在チェック
                $_SESSION['s_sysmsg'] = '指定の計画有給を削除しました。';
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '削除できませんでした。';
            }
        }
        return false;
    }

    ////////// 不在予定の取得
    public function getViewAbsence(&$result)
    {
        $query = sprintf( "
            SELECT admit_status, sm.section_name, ud.name, content, start_time, end_time
            FROM                sougou_deteils
            LEFT OUTER JOIN     user_detailes   AS ud    USING(uid)
            LEFT OUTER JOIN     cd_table        AS ct    USING(uid)
            LEFT OUTER JOIN     section_master  AS sm    USING(sid)
            LEFT OUTER JOIN     act_table       AS at    USING(act_id)
            {$this->where}
            ORDER BY ct.orga_id ASC, start_date ASC, start_time ASC, end_time
        ");
        $res = array();
        if( ($rows=$this->getResult2($query, $res)) < 1 ) {
            return false;
        }
        $result->add_array($res);
        return $rows;
    }

    ////////// 指定UIDは、総合届の承認者（工場長）ですか？
    public function IsSogoAdmitrKo($uid)
    {
        $query = "SELECT kojyotyo FROM approval_path_master_late WHERE standards_date<now() ORDER BY standards_date DESC LIMIT 1";
        $res = array();
        if( getResult2($query, $res) <= 0 ) return false;
        if( $res[0][0] == $uid ) return true;   // 承認者（工場長）です。
        return false;   // 承認者（工場長）ではない。
    }

    ////////// 指定UIDは、総合届の承認者ですか？
    public function IsSogoAdmitr($uid)
    {
        $post = array("kakarityo", "katyo", "butyo");
        
        for( $n=0; $n<3; $n++ ) {
            $query = "SELECT act_id FROM approval_path_master WHERE {$post[$n]}='$uid' LIMIT 1";
            $res = array();
            if( getResult2($query, $res) > 0 ) return true; // 承認者です。
        }
        return false;   // 承認者ではない。
    }

    ////////// 指定UIDの未承認総合届件数取得
    public function getSougouAdmitCnt($uid)
    {
        $query = "SELECT count(*) FROM sougou_deteils where admit_status='$uid'";
        $res = array();
        $cnt = getResult2($query, $res);
        if( $cnt > 0 ) {
            return $res[0][0];
        } else {
            return 0;
        }
    }

    ////////// 指定UIDの名前取得
    public function getUidName($uid)
    {
        $query = "SELECT trim(name) FROM user_detailes WHERE uid = '$uid'";
        if ($this->getUniResult($query, $UidName) < 1) {
            return "×取得不可";
        }
        return $UidName;
    }

    ////////// 指定日付/UIDの不在理由取得
    // 指定UIDは不在ですか？
    public function getAbsence($uid)
    {
        $date  = date('Ymd');  // 今の年月日
        $query = "
                    SELECT absence, str_time, end_time FROM working_hours_report_data_new
                    WHERE uid='$uid' AND working_date='$date' AND (absence!='00' OR str_time='0000' OR end_time!='0000')
                 ";
        $res = array();
        if( $this->getResult2($query, $res) <= 0 ) {
            return "<font style='background-color:blue; color:white;'>出勤</font>";
        }
        return $this->getAbsenceReason($res);
    }

    ////////// 不在理由取得
    public function getAbsenceReason($res)
    {
        $state = "";
        switch ($res[0][0]) {
            case '11': $state = "有給"; break;
            case '12': $state = "欠勤"; break;
            case '13': $state = "無欠"; break;
            case '14': $state = "出張"; break;
            case '15': $state = "振休"; break;
            case '16': $state = "特休"; break;
            case '17': $state = "慶事"; break;
            case '18': $state = "弔事"; break;
            case '19': $state = "産休"; break;
            case '20': $state = "育休"; break;
            case '21': $state = "生休"; break;
            case '22': $state = "休職"; break;
            case '23': $state = "労災"; break;
            default  : break;
        }
        if($state) return "<font style='background-color:red; color:white;'>{$state}</font>";
        
        if($res[0][1] == '0000') $state = "不在";
        if($res[0][2] != '0000') $state = "退勤";
        
        if($state) return "<font style='background-color:red; color:white;'>{$state}</font>";
        
        return "<font style='background-color:red; color:white;'>不明</font>";
    }

} // Class MeetingSchedule_Model End

?>
