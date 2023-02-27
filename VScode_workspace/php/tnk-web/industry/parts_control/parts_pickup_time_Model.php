<?php
//////////////////////////////////////////////////////////////////////////////
// 資材管理の部品出庫 着手・完了時間 集計用  MVC Model 部                   //
// Copyright (C) 2005-2011 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/12 Created   parts_pickup_time_Model.php                         //
// 2005/09/27 WHERE end_time IS NULL ではインデックスが使えないため         //
//            NULL の変わりに '19700101 000000' を使用して未完了を表す      //
//            出庫完了一覧を interval '60 day' で 60日前まで表示するを追加  //
//            [権限がない]メッセージと登録がありませんのメッセージがダブル  //
//            .= '登録がありません！' で対応「.=」                          //
// 2005/10/04 出庫作業者の登録テーブルに有効・無効を追加  伴うメソッド追加  //
// 2005/10/07 作業者指示ボタンのデータ取得にページ制御をしないメソッドを使用//
// 2005/10/13 '計画番号：{$plan_no}'→"計画番号：{$plan_no}" タイプミス修正 //
// 2005/12/08 getViewDataEndList()メソッドに取消有効・無効のSQL文を追加     //
// 2005/12/10 着手・完了時間の修正用メソッド timeEdit_execute() を追加      //
// 2005/12/15 編集権限に生産部の500を追加(副部長の対応)その他の人もいるが？ //
// 2006/01/18 table_add()メソッドに@マークの計画数を修正する機能と日程計画の//
//            計画残のチェック機能を追加 組立データ集計と同じロジックを追加 //
// 2006/04/05 pickupAuthUser()メソッドを資材部門のみOKを 組立全体へ変更     //
// 2007/01/09 認証用メソッドを共通権限マスター対応へ変更。getCheckAuthority //
// 2011/06/28 １計画分の工数(分)を算出で０割エラー発生に対応           大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード

require_once ('../../ComTableMntClass.php');    // TNK 全共通 テーブルメンテ&ページ制御Class


/*****************************************************************************************
*  資材管理の部品出庫 着手・完了時間 集計用 MVCのModel部の base class 基底クラスの定義   *
*****************************************************************************************/
class PartsPickupTime_Model extends ComTableMnt
{
    ///// Private properties
    private $where;                             // 共用 SQLのWHERE句
    private $authDiv = 18;                      // このビジネスロジックの権限区分
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer の定義 (php5へ移行時は __construct() へ変更予定) (デストラクタ__destruct())
    public function __construct($request)
    {
        switch ($request->get('current_menu')) {
        case 'EndList':
        case 'TimeEdit':
            $where = "
            WHERE end_time <= '" . date('Ymd 235959') .
            "' AND end_time >= (timestamp '" . date('Ymd 000000') . "' - interval '62 day')
            ";
            break;
        case 'apend':
            $where = "WHERE end_time='19700101 000000' AND " . "user_id = '" . $request->get('user_id') . "'";
            break;
        case 'user':
            $where = '';
            break;
        default:        // 'list'を想定
            $where = "WHERE end_time='19700101 000000'";
        }
        if ($request->get('current_menu') == 'user') {
            $sql_sum = "
                SELECT count(*) FROM parts_pickup_user
            ";
            $log_file = 'parts_pickup_user.log';
        } else {
            $sql_sum = "
                SELECT count(*) FROM parts_pickup_time $where
            ";
            $log_file = 'parts_pickup_time.log';
        }
        $this->where = $where;
        ///// Constructer を定義すると 基底クラスの Constructerが実行されない
        ///// 基底ClassのConstructerはプログラマーの責任で呼出す
        if ($request->get('current_menu') == 'user') $page_rec = 15; else $page_rec = 20;
        parent::__construct($sql_sum, $request, $log_file, $page_rec);
    }
    
    ////////// 出庫 作業者の名前を検索し返す
    public function getUserName($user_id)
    {
        if ($this->pickupAuthUser()) {
            ///// user_idの適正チェック
            return $this->checkUserID($user_id);
        }
        return false;
    }
    
    ////////// 出庫着手の入力 (追加)
    public function table_add($plan_no, $user_id)
    {
        if ($this->pickupAuthUser()) {
            ///// user_idの適正チェック
            if (!$this->checkUserID($user_id)) {
                return false;
            }
            ///// 計画番号の適正チェック
            $chk = "select plan - cut_plan - kansei from assembly_schedule where plan_no='{$plan_no}'";
            if ($this->getUniResult($chk, $check) <= 0) {   // 計画番号が登録されているか
                $_SESSION['s_sysmsg'] = "計画番号：{$plan_no} が見つかりません！";
                return false;
            } else {
                if (substr($plan_no, 0, 1) == '@' && $check <= 0) {
                    $sei_no = substr($plan_no, 1, 7);
                    $query = "SELECT order_q, utikiri, nyuko FROM order_plan WHERE sei_no={$sei_no} limit 1";
                    $order = array();
                    if ($this->getResult2($query, $order) > 0) {   // 製造番号で発注数をチェック
                        $order_q = $order[0][0]; $utikiri = $order[0][1]; $nyuko = $order[0][2];
                        $update_sql = "UPDATE assembly_schedule SET plan={$order_q}, cut_plan={$utikiri}, kansei={$nyuko} WHERE plan_no='{$plan_no}'";
                        $this->execute_Update($update_sql);
                        if ( ($order_q - $utikiri - $nyuko) <= 0 ) {
                            $_SESSION['s_sysmsg'] = "計画番号：{$plan_no} は計画残がありません！ 管理担当者へ連絡して下さい。";
                            return false;
                        }
                    } else {
                        $_SESSION['s_sysmsg'] = "計画番号：{$plan_no} が見つかりません！ 管理担当者へ連絡して下さい。";
                        return false;
                    }
                } elseif ($check <= 0) {
                    $_SESSION['s_sysmsg'] = "計画番号：{$plan_no} は計画残がありません！";
                    return false;
                }
            }
            $chk_sql = "select plan_no from parts_pickup_time where end_time='19700101 000000' and user_id='{$user_id}' and plan_no='{$plan_no}' limit 1";
            if ($this->getUniResult($chk_sql, $check) > 0) {    // user_id plan_noで未完了あり(重複)のチェック
                $_SESSION['s_sysmsg'] = "同一作業者で 計画番号：[{$plan_no}] の出庫着手が既に指示されています。";
            } else {
                $response = $this->add_execute($plan_no, $user_id);
                if ($response) {
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = '登録できませんでした。';
                }
            }
        }
        return false;
    }
    
    ////////// 出庫着手の取消 (完全削除)
    public function table_delete($serial_no, $plan_no, $user_id)
    {
        if ($this->pickupAuthUser()) {
            $chk_sql = "select plan_no from parts_pickup_time where serial_no={$serial_no} and end_time='19700101 000000'";
            if ($this->getUniResult($chk_sql, $check) < 1) {     // 完了入力前(着手指示分)が登録されているか？
                $_SESSION['s_sysmsg'] = "計画番号：{$plan_no} は他の人に変更されました！";
            } else {
                $response = $this->del_execute($serial_no, $user_id);
                if ($response) {
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = "計画番号：{$plan_no} 出庫着手の取消ができませんでした。";
                }
            }
        }
        return false;
    }
    
    ////////// 出庫完了の入力・取消 (変更)
    public function table_change($status, $serial_no, $user_id)
    {
        if ($this->pickupAuthUser()) {
            if ($status == 'end') {     // 出庫完了入力
                $query = "select str_time from parts_pickup_time where serial_no={$serial_no} and end_time='19700101 000000'";
                if ($this->getUniResult($query, $str_time) > 0) {  // 完了入力前のserial_noが登録されているか？
                    $response = $this->chg_execute($status, $serial_no, $user_id, $str_time);
                    if ($response) {
                        return true;
                    } else {
                        $_SESSION['s_sysmsg'] = '完了入力ができませんでした。';
                    }
                } else {
                    $_SESSION['s_sysmsg'] = "Serial番号：{$serial_no} は他の人に変更されました！";
                }
            } elseif ($status == 'cancel') {     // 出庫完了の取消
                $query = "select str_time from parts_pickup_time where serial_no={$serial_no} and end_time != '19700101 000000'";
                if ($this->getUniResult($query, $str_time) > 0) {  // 完了入力済のserial_noが登録されているか？
                    $response = $this->chg_execute($status, $serial_no, $user_id, $str_time);
                    if ($response) {
                        return true;
                    } else {
                        $_SESSION['s_sysmsg'] = '完了の取消ができませんでした。';
                    }
                } else {
                    $_SESSION['s_sysmsg'] = "Serial番号：{$serial_no} は他の人に変更されました！";
                }
            }
        }
        return false;
    }
    
    ////////// 出庫 作業者の 登録・変更
    public function user_edit($user_id, $user_name)
    {
        ///// user_idの適正チェック
        if (!$this->checkUserID($user_id)) {
            return false;
        }
        if ($this->pickupAuthUser()) {
            $query = "
                SELECT user_id, user_name FROM parts_pickup_user WHERE user_id='{$user_id}'
            ";
            $res = array();
            if ($this->getResult2($query, $res) <= 0) {
                // 出庫 作業者 登録
                $response = $this->user_insert($user_id, $user_name);
                if ($response) {
                    $_SESSION['s_sysmsg'] = "[{$user_id}] {$user_name} さんを登録しました。";
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = '作業者の登録が出来ませんでした！';
                }
            } else {
                // データが変更されているかチェック
                if ($user_id == $res[0][0] && $user_name == $res[0][1]) return true;
                // 出庫 作業者 変更
                $response = $this->user_update($user_id, $user_name);
                if ($response) {
                    $_SESSION['s_sysmsg'] = "[{$user_id}] {$user_name} さんに変更しました。";
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = '作業者の変更が出来ませんでした！';
                }
            }
        }
        return false;
    }
    
    ////////// 出庫 作業者の 有効・無効
    public function user_active($user_id, $user_name)
    {
        if ($this->pickupAuthUser()) {
            $query = "
                SELECT active FROM parts_pickup_user WHERE user_id='{$user_id}'
            ";
            if ($this->getUniResult($query, $active) <= 0) {
                $_SESSION['s_sysmsg'] = "[{$user_id}] {$user_name} さんの対象データがありません！";
            } else {
                // ここに last_date last_host の登録処理を入れる
                // regdate=自動登録
                $last_date = date('Y-m-d H:i:s');
                $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
                if ($active == 't') {
                    $active = 'FALSE';
                } else {
                    $active = 'TRUE';
                }
                $update_sql = "
                    UPDATE parts_pickup_user SET
                    active={$active}, last_date='{$last_date}', last_host='{$last_host}'
                    WHERE user_id='{$user_id}'
                "; 
                return $this->execute_Update($update_sql);
            }
        }
        return false;
    }
    
    ////////// 出庫 作業者の 削除
    public function user_omit($user_id, $user_name)
    {
        ///// user_idの適正チェック
        if (!$this->checkUserID($user_id)) {
            return false;
        }
        if ($this->pickupAuthUser()) {
            $query = "
                SELECT user_id, user_name FROM parts_pickup_user WHERE user_id='{$user_id}'
            ";
            if ($this->getResult2($query, $res) <= 0) {
                $_SESSION['s_sysmsg'] = "[{$user_id}] {$user_name} さんの削除対象データがありません！";
            } else {
                $response = $this->user_delete($user_id);
                if ($response) {
                    $_SESSION['s_sysmsg'] = "[{$user_id}] {$user_name} さんを削除しました。";
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = "[{$user_id}] {$user_name} さんを削除出来ませんでした！";
                }
            }
        }
        return false;
    }
    
    ////////// MVC の Model 部の結果 表示用のデータ取得
    ///// List部    出庫着手 一覧表
    public function getViewDataList(&$result)
    {
        ///// 以下で AS pickuser は AS user でエラーになる(予約語)ため冗長化した
        $query = "SELECT plan_no        AS 計画番号
                        ,parts_no       AS 製品番号
                        ,substr(midsc, 1, 20)
                                        AS 製品名
                        ,plan           AS 計画数
                        ,user_id        AS 社員番号
                        ,trim(pickuser.user_name)
                                        AS 作業者
                        ,to_char(str_time, 'YY/MM/DD HH24:MI')
                                        AS 開始日時
                        ,serial_no      AS 連番
                    FROM
                        parts_pickup_time
                    LEFT OUTER JOIN
                        assembly_schedule using(plan_no)
                    LEFT OUTER JOIN
                        miitem on (parts_no=mipn)
                    LEFT OUTER JOIN
                        parts_pickup_user AS pickuser USING(user_id)
                    {$this->where}
                    ORDER BY
                        str_time ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            $_SESSION['s_sysmsg'] .= '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// List部    出庫完了 一覧表
    public function getViewDataEndList(&$result)
    {
        $query = "SELECT plan_no        AS 計画番号         -- 00
                        ,parts_no       AS 製品番号         -- 01
                        ,substr(midsc, 1, 20)
                                        AS 製品名           -- 02
                        ,plan           AS 計画数           -- 03
                        ,user_id        AS 社員番号         -- 04
                        ,trim(pickuser.user_name)
                                        AS 作業者           -- 05
                        ,to_char(str_time, 'YY/MM/DD HH24:MI')
                                        AS 開始日時         -- 06
                        ,to_char(end_time, 'YY/MM/DD HH24:MI')
                                        AS 完了日時         -- 07
                        ,serial_no      AS 連番             -- 08
                        ,pick_time      AS 出庫工数         -- 09
                        -----------------------------リストは上記まで
                        ,CASE
                            WHEN CURRENT_DATE = CAST(end_time AS date)
                            THEN '取消有効'
                            ELSE '取消無効'
                        END             AS 取消             -- 10
                    FROM
                        parts_pickup_time
                    LEFT OUTER JOIN
                        assembly_schedule using(plan_no)
                    LEFT OUTER JOIN
                        miitem on (parts_no=mipn)
                    LEFT OUTER JOIN
                        parts_pickup_user AS pickuser USING(user_id)
                    {$this->where}
                    ORDER BY
                        end_time DESC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            $_SESSION['s_sysmsg'] .= '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// List部    出庫着手 入力後の確認用 一覧表
    public function getViewDataApendList($user_id, &$result)
    {
        // __construct で設定した$this->where が使えれば getViewDataList()と共用する
        $query = "SELECT plan_no        AS 計画番号
                        ,parts_no       AS 製品番号
                        ,substr(midsc, 1, 20)
                                        AS 製品名
                        ,plan           AS 計画数
                        ,user_id        AS 社員番号
                        ,trim(pickuser.user_name)
                                        AS 作業者
                        ,to_char(str_time, 'YY/MM/DD HH24:MI')
                                        AS 開始日時
                        ,serial_no      AS 連番
                    FROM
                        parts_pickup_time
                    LEFT OUTER JOIN
                        assembly_schedule using(plan_no)
                    LEFT OUTER JOIN
                        miitem on (parts_no=mipn)
                    LEFT OUTER JOIN
                        parts_pickup_user AS pickuser USING(user_id)
                    {$this->where}
                    ORDER BY
                        str_time ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            $_SESSION['s_sysmsg'] .= '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// Edit Confirm_delete 1レコード分
    public function getViewDataEdit($serial_no, &$result)
    {
        $query = "SELECT plan_no        AS 計画番号         -- 00
                        ,parts_no       AS 製品番号         -- 01
                        ,substr(midsc, 1, 20)
                                        AS 製品名           -- 02
                        ,plan           AS 計画数           -- 03
                        ,user_id        AS 社員番号         -- 04
                        ,trim(pickuser.user_name)
                                        AS 作業者           -- 05
                        ,to_char(str_time, 'YY/MM/DD HH24:MI')
                                        AS 開始日時         -- 06
                        ,to_char(end_time, 'YY/MM/DD HH24:MI')
                                        AS 完了日時         -- 07
                        ,serial_no      AS 連番             -- 08
                        ,pick_time      AS 連番             -- 09
                        --------------- これ以下はリストデータではない
                        ,to_char(str_time, 'YYYY') AS str_year   -- 10
                        ,to_char(str_time, 'MM')   AS str_month  -- 11
                        ,to_char(str_time, 'DD')   AS str_day    -- 12
                        ,to_char(str_time, 'HH24') AS str_hour   -- 13
                        ,to_char(str_time, 'MI')   AS str_minute -- 14
                        ,to_char(end_time, 'YYYY') AS end_year   -- 15
                        ,to_char(end_time, 'MM')   AS end_month  -- 16
                        ,to_char(end_time, 'DD')   AS end_day    -- 17
                        ,to_char(end_time, 'HH24') AS end_hour   -- 18
                        ,to_char(end_time, 'MI')   AS end_minute -- 19
                    FROM
                        parts_pickup_time
                    LEFT OUTER JOIN
                        assembly_schedule using(plan_no)
                    LEFT OUTER JOIN
                        miitem on (parts_no=mipn)
                    LEFT OUTER JOIN
                        parts_pickup_user AS pickuser USING(user_id)
                    WHERE
                        serial_no = {$serial_no}
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            $result->add('plan_no',    $res[0][0]);
            $result->add('assy_no',    $res[0][1]);
            $result->add('assy_name',  $res[0][2]);
            $result->add('plan_pcs',   $res[0][3]);
            $result->add('user_id',    $res[0][4]);
            $result->add('user_name',  $res[0][5]);
            $result->add('str_time',   $res[0][6]);
            $result->add('end_time',   $res[0][7]);
            $result->add('serial_no',  $res[0][8]);
            $result->add('pick_time',  $res[0][9]);
            // これより以下は修正用データ
            $result->add('str_year',   $res[0][10]);
            $result->add('str_month',  $res[0][11]);
            $result->add('str_day',    $res[0][12]);
            $result->add('str_hour',   $res[0][13]);
            $result->add('str_minute', $res[0][14]);
            $result->add('end_year',   $res[0][15]);
            $result->add('end_month',  $res[0][16]);
            $result->add('end_day',    $res[0][17]);
            $result->add('end_hour',   $res[0][18]);
            $result->add('end_minute', $res[0][19]);
        }
        return $rows;
    }
    
    ////////// MVC の Model 部の結果 表示用のデータ取得
    ///// List部    出庫 作業者 登録 一覧表
    public function getViewUserList(&$result)
    {
        $query = "SELECT user_id        AS 社員番号
                        ,user_name      AS 氏名
                        ,to_char(last_date, 'YY/MM/DD HH24:MI')
                                        AS 開始日時
                        ,CASE
                            WHEN active THEN '有効'
                            ELSE '無効'
                         END            AS 有効無効
                    FROM
                        parts_pickup_user
                    ORDER BY
                        user_id ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// List部    出庫 作業者 指示用 ボタン表示
    public function getViewActiveUser(&$result)
    {
        $query = "SELECT user_id        AS 社員番号
                        ,user_name      AS 氏名
                    FROM
                        parts_pickup_user
                    WHERE
                        active
                    ORDER BY
                        user_id ASC
        ";
        $res = array();
        ///// ページ制御をしないメソッドを使用する
        if ( ($rows=$this->execute_ListNotPageControl($query, $res)) < 1 ) {
            $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ////////// 出庫 着手・完了 時間の変更 (日時の整形及びチェック)
    public function timeEdit($request)
    {
        // 編集権限のチェック
        if (!$this->pickupAuthUser()) return false;     // 編集権限NG
        // リクエストデータの抽出
        $serial_no = $request->get('serial_no');
        $user_id   = $request->get('user_id');
        $str_year   = $request->get('str_year');
        $str_month  = $request->get('str_month');
        $str_day    = $request->get('str_day');
        $str_hour   = $request->get('str_hour');
        $str_minute = $request->get('str_minute');
        $end_year   = $request->get('end_year');
        $end_month  = $request->get('end_month');
        $end_day    = $request->get('end_day');
        $end_hour   = $request->get('end_hour');
        $end_minute = $request->get('end_minute');
        // 日付のチェック
        if ("{$str_year}{$str_month}{$str_day}" != "{$end_year}{$end_month}{$end_day}") {
            $_SESSION['s_sysmsg'] = '着手と完了の年月日は同じでなくてはなりません！';
            return false;
        }
        // 時間のチェック
        if ("{$str_hour}{$str_minute}" >= "{$end_hour}{$end_minute}") {
            $_SESSION['s_sysmsg'] = '着手と完了の時間が同じか逆転しています！';
            return false;
        }
        // 日時の整形
        $str_time = "{$str_year}-{$str_month}-{$str_day} {$str_hour}:{$str_minute}:00";
        $end_time = "{$end_year}-{$end_month}-{$end_day} {$end_hour}:{$end_minute}:00";
        // 変更実行
        return $this->timeEdit_execute($serial_no, $user_id, $str_time, $end_time);
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// 資材部品出庫関係の編集権限メソッド(後日、共用メソッド移行する)
    protected function pickupAuthUser()
    {
        if ($this->getCheckAuthority($this->authDiv)) {
            return true;
        } else {
            $_SESSION['s_sysmsg'] = '資材部品出庫メニューの編集権限がありません！';
            return false;
        }
        
        ///// 以下は現在使用していない
        $LoginUser = $_SESSION['User_ID'];
        $query = "select act_id from cd_table where uid='$LoginUser'";
        if (getUniResult($query, $sid) > 0) {
            switch ($sid) {
            case '514':         // カプラ資材ならOK
            case '534':         // リニア資材ならOK
            case '500':         // 生産部ならOK (2005/12/15追加)
            case '522':         // カプラ組立MA担当
            case '523':         // カプラ組立HA担当
            case '176':         // カプラ組立 課長・事務
            case '551':         // リニア組立事務
            case '175':         // リニア組立担当
            case '560':         // リニア組立バイモル担当
            case '537':         // リニア組立検査担当
                return true;
                break;
            default:
                // NG
            }
            if ($_SESSION['Auth'] >= 3) { // テスト用
                return true;
            }
        }
        $_SESSION['s_sysmsg'] = '資材部品出庫メニューの編集権限がありません！';
        return false;
    }
    ////////// 資材部品出庫時間(休み時間を除く)の合計(分)を返す
    protected function getSumTime($str_time, $end_time)
    {
        // 合計時間(分)を取得(休み時間を除く前)
        $query = "
            SELECT
            Uround(CAST(extract(epoch from timestamp '{$end_time}' - timestamp '{$str_time}') / 60 AS NUMERIC), 0)
        ";
        $res = 0;
        $this->getUniResult($query, $res);
        $str_date = substr($str_time, 0, 10);
        $end_date = substr($end_time, 0, 10);
        // 朝礼の５分
        $query = "
            SELECT CASE WHEN timestamp '{$str_time}' <= timestamp '{$str_date} 08:30:00' THEN
            CASE WHEN timestamp '{$end_time}' >= timestamp '{$end_date} 08:35:00' THEN '1'
            ELSE '0' END ELSE '0' END ;
        ";
        $flg = '0';
        $this->getUniResult($query, $flg);
        if ($flg) $res -= 5;
        // 10:30の５分
        $query = "
            SELECT CASE WHEN timestamp '{$str_time}' <= timestamp '{$str_date} 10:30:00' THEN
            CASE WHEN timestamp '{$end_time}' >= timestamp '{$end_date} 10:35:00' THEN '1'
            ELSE '0' END ELSE '0' END ;
        ";
        $flg = '0';
        $this->getUniResult($query, $flg);
        if ($flg) $res -= 5;
        // 昼休みの４５分
        $query = "
            SELECT CASE WHEN timestamp '{$str_time}' <= timestamp '{$str_date} 12:00:00' THEN
            CASE WHEN timestamp '{$end_time}' >= timestamp '{$end_date} 12:45:00' THEN '1'
            ELSE '0' END ELSE '0' END ;
        ";
        $flg = '0';
        $this->getUniResult($query, $flg);
        if ($flg) $res -= 45;
        // 15:00の１０分
        $query = "
            SELECT CASE WHEN timestamp '{$str_time}' <= timestamp '{$str_date} 15:00:00' THEN
            CASE WHEN timestamp '{$end_time}' >= timestamp '{$end_date} 15:10:00' THEN '1'
            ELSE '0' END ELSE '0' END ;
        ";
        $flg = '0';
        $this->getUniResult($query, $flg);
        if ($flg) $res -= 10;
        // 17:15の１５分
        $query = "
            SELECT CASE WHEN timestamp '{$str_time}' <= timestamp '{$str_date} 17:15:00' THEN
            CASE WHEN timestamp '{$end_time}' >= timestamp '{$end_date} 17:30:00' THEN '1'
            ELSE '0' END ELSE '0' END ;
        ";
        $flg = '0';
        $this->getUniResult($query, $flg);
        if ($flg) $res -= 15;
        
        // エラーチェック
        if ($res < 0) $res = 0;
        return $res;
    }
    ////////// 同時出庫部品点数の合計を再計算し更新する
    protected function plan_pcsUpdate($user_id)
    {
        // 同時出庫分の合計部品点数(plan_pcs)を取得 同一作業者の場合を同時出庫分と見なす
        $query = "
            SELECT sum(parts_pcs) FROM parts_pickup_time
            WHERE end_time='19700101 000000' and user_id='{$user_id}'
        ";
        $plan_pcs = 0;     // 初期化
        $this->getUniResult($query, $plan_pcs);
        // ここで同時出庫分の他の計画があればplan_pcsをUPDATEする
        $query = "
            SELECT serial_no FROM parts_pickup_time WHERE end_time='19700101 000000' and user_id='{$user_id}'
        ";
        if ($this->getUniResult($query, $tmp) > 0) {    // 1件でもあれば UPDATE する
            $query = "
                UPDATE parts_pickup_time SET plan_pcs={$plan_pcs}
                WHERE end_time='19700101 000000' and user_id='{$user_id}'
            ";
            if (!$this->execute_Update($query)) {
                $_SESSION['s_sysmsg'] = '同時出庫分の合計部品点数の変更が出来ませんでした！ 管理担当者へ連絡して下さい。';
            }
        }
        return $plan_pcs;
    }
    ////////// 資材部品出庫メニューの作業者の登録(実行部)
    protected function user_insert($user_id, $user_name)
    {
        if (strlen($user_id) != 6) return false;
        
        // ここに last_date last_host の登録処理を入れる
        // regdate=自動登録
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        
        $insert_sql = "
            INSERT INTO parts_pickup_user
            (user_id, user_name, active, last_date, last_host)
            VALUES
            ('$user_id', '$user_name', TRUE, '$last_date', '$last_host')
        ";
        return $this->execute_Insert($insert_sql);
    }
    ////////// 資材部品出庫メニューの作業者の変更(実行部)
    protected function user_update($user_id, $user_name)
    {
        if (strlen($user_id) != 6) return false;
        
        // ここに last_date last_host の登録処理を入れる
        // regdate=自動登録
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        
        $update_sql = "
            UPDATE parts_pickup_user SET
            user_id='{$user_id}', user_name='{$user_name}', last_date='{$last_date}', last_host='{$last_host}'
            WHERE user_id='{$user_id}'
        "; 
        return $this->execute_Update($update_sql);
    }
    ////////// 資材部品出庫メニューの作業者の削除(実行部)
    protected function user_delete($user_id)
    {
        // 保存用のSQL文を設定
        $save_sql   = "SELECT * FROM parts_pickup_user WHERE user_id='{$user_id}'";
        // 削除用SQL文を設定
        $delete_sql = "DELETE FROM parts_pickup_user WHERE user_id='{$user_id}'";
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
    ////////// 出庫 作業者のuser_idの適正をチェックしメッセージ＋結果(氏名=OK,false=NG)を返す
    protected function checkUserID($user_id)
    {
        ///// user_idの適正チェック
        $chk = "SELECT trim(name) FROM user_detailes WHERE uid='{$user_id}'";
        if ($this->getUniResult($chk, $user_name) <= 0) {   // 社員登録されているか
            if ($user_id < 777001 || $user_id > 777999) {   // 臨時(応援)でなければ
                $_SESSION['s_sysmsg'] = "社員番号：{$user_id} は不正です！";
            } else {
                return ('応援者' . substr($user_id, 3, 3) );
            }
        } else {
            return $user_name;
        }
        return false;
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// 出庫開始の入力 (追加)
    private function add_execute($plan_no, $user_id)
    {
        // ここに last_date last_host の登録処理を入れる
        // regdate=自動登録
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        
        // 同時出庫計画の初回計画か？
        $query = "
            SELECT str_time FROM parts_pickup_time
            WHERE end_time='19700101 000000' and user_id='{$user_id}'
            LIMIT 1
        ";
        if ($this->getUniResult($query, $str_time) <= 0) {
            // 初回計画のため時間を設定する
            $str_time = date('Y-m-d H:i:s');
        }
        
        // この計画(plan_no)の部品点数(parts_pcs)を取得
        $query = "
            SELECT count(parts_no) FROM allocated_parts LEFT OUTER JOIN miccc ON (parts_no=mipn)
            WHERE plan_no='{$plan_no}' and miccc IS NULL
        ";
        $parts_pcs = 0;     // 初期化
        $this->getUniResult($query, $parts_pcs);
        
        // 登録実行 (この時点ではplan_pcsはparts_pcsと同じにする)
        $end_time = '19700101 000000';
        $insert_qry = "
            insert into parts_pickup_time
            (plan_no, user_id, str_time, end_time, plan_pcs, parts_pcs, last_date, last_host)
            values
            ('$plan_no', '$user_id', '$str_time', '$end_time', $parts_pcs, $parts_pcs, '$last_date', '$last_host')
        ";
        $result_flg = $this->execute_Insert($insert_qry);
        
        // 同時出庫分のplan_pcsを更新
        if ($result_flg) {
            $this->plan_pcsUpdate($user_id);
        }
        return $result_flg;
    }
    
    ////////// 出庫完了の入力・取消 (変更)
    private function chg_execute($status, $serial_no, $user_id, $str_time)
    {
        // ここに last_date last_host の登録処理を入れる
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        // status を見て完了入力か取消かを判断する
        if ($status == 'end') {             // 完了入力
            $end_time = date('Y-m-d H:i:s');
            $sum_time = $this->getSumTime($str_time, $end_time);    // 休み時間を除いた合計工数(分)を取得
            $query = "
                SELECT serial_no, plan_pcs, parts_pcs FROM parts_pickup_time
                WHERE str_time='{$str_time}' AND user_id='{$user_id}'
            ";
            if ( ($rows=$this->getResult2($query, $res)) <= 0 ) return false;
            // debug $_SESSION['sum_time'] = $sum_time;
            for ($i=0; $i<$rows; $i++) {
                $serial_no = $res[$i][0];
                $plan_pcs  = $res[$i][1];
                $parts_pcs = $res[$i][2];
                // debug $_SESSION["pick_time$i"] = ($parts_pcs / $plan_pcs) * $sum_time;
                // debug $_SESSION["pick_round$i"] = round(($parts_pcs / $plan_pcs) * $sum_time, 0);
                if ($plan_pcs == 0) {
                    $pick_time = 0;    // １計画分の工数(分)を算出
                } else {
                    $pick_time = round(($parts_pcs / $plan_pcs) * $sum_time, 0);    // １計画分の工数(分)を算出
                }
                $update_sql = "
                    UPDATE parts_pickup_time SET
                    end_time='{$end_time}', pick_time={$pick_time}, last_date='{$last_date}', last_host='{$last_host}'
                    WHERE serial_no={$serial_no}
                "; 
                $this->execute_Update($update_sql);
            }
        } elseif ($status == 'cancel') {     // 完了の取消
            // serial_noで指定された user_idとend_time で同時完了計画分を取得する
            $query = "
                SELECT serial_no FROM parts_pickup_time
                WHERE str_time='{$str_time}' AND user_id='{$user_id}'
            ";
            if ( ($rows=$this->getResult2($query, $res)) <= 0 ) return false;
            for ($i=0; $i<$rows; $i++) {
                $serial_no = $res[$i][0];
                // 保存用のSQL文を設定
                $save_sql = "SELECT * FROM parts_pickup_time WHERE serial_no={$res[$i][0]}";
                $update_sql = "
                    UPDATE parts_pickup_time SET
                    end_time='19700101 000000', pick_time=NULL, last_date='{$last_date}', last_host='{$last_host}'
                    WHERE serial_no={$serial_no}
                "; 
                // $save_sqlはオプションなので指定しなくても良い
                $this->execute_Update($update_sql, $save_sql);
            }
        }
        return true;
    }
    
    ////////// 出庫着手の取消 (完全削除)
    private function del_execute($serial_no, $user_id)
    {
        // 保存用のSQL文を設定
        $save_sql   = "select * from parts_pickup_time where serial_no={$serial_no}";
        $delete_sql = "delete from parts_pickup_time where serial_no={$serial_no}";
        // $save_sqlはオプションなので指定しなくても良い
        $result_flg = $this->execute_Delete($delete_sql, $save_sql);
        
        // 同時出庫分のplan_pcsを更新
        if ($result_flg) {
            $this->plan_pcsUpdate($user_id);
        }
        return $result_flg;
    }
    
    ////////// 出庫 着手・完了 時間の変更
    private function timeEdit_execute($serial_no, $user_id, $str_time, $end_time)
    {
        // 休み時間を除いた合計工数(分)を取得        
        $sum_time = $this->getSumTime($str_time, $end_time);
        // 旧の着手時間を取得
        $query = "
            SELECT str_time FROM parts_pickup_time
            WHERE serial_no={$serial_no}
        ";
        if ($this->getUniResult($query, $old_str_time) <= 0) {
            $_SESSION['s_sysmsg'] = '現在の着手時間を取得できません！';
            return false;
        }
        // 自分を含めた同時計画分を取得
        $query = "
            SELECT serial_no, plan_pcs, parts_pcs FROM parts_pickup_time
            WHERE str_time='{$old_str_time}' AND user_id='{$user_id}'
        ";
        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) {
            $_SESSION['s_sysmsg'] = '対象データがありません！';
            return false;
        }
        // ここに last_date last_host の登録処理を入れる
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        for ($i=0; $i<$rows; $i++) {
            $serial_no = $res[$i][0];
            $plan_pcs  = $res[$i][1];
            $parts_pcs = $res[$i][2];
            $pick_time = round(($parts_pcs / $plan_pcs) * $sum_time, 0);    // １計画分の工数(分)を算出
            $update_sql = "
                UPDATE parts_pickup_time SET
                str_time='{$str_time}', end_time='{$end_time}', pick_time={$pick_time}, last_date='{$last_date}', last_host='{$last_host}'
                WHERE serial_no={$serial_no}
            "; 
            $this->execute_Update($update_sql);
        }
        return true;
    }
    
} // Class PartsPickupTime_Model End

?>
