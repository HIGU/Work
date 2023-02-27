<?php
////////////////////////////////////////////////////////////////////////////////
// 食堂メニュー予約                                                           //
//                                                              MVC Model 部  //
// Copyright (C) 2022-2022 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2022/04/29 Created meal_appli_ViewMenuSelect.php                           //
// 2022/05/07 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード

require_once ('../../ComTableMntClass.php');   // TNK 全共通 テーブルメンテ&ページ制御Class

/******************************************************************************
*          総合届（申請）用 MVCのModel部 base class 基底クラスの定義          *
******************************************************************************/
class meal_appli_Model extends ComTableMnt
{
    ///// Private properties
    private $debug = "";// デバッグフラグ
    private $menu_name = array();// デバッグフラグ
    private $uid;       // ログインユーザーID
    private $show_menu; // 表示モード
    private $event_date;// イベント日
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer の定義 (php5へ移行時は __construct() へ変更予定) (デストラクタ__destruct())
    public function __construct($request, $uid, $menu_name='')
    {
        $this->menu_name[0] = $menu_name[0];    // メニューカラム名
        $this->menu_name[1] = $menu_name[1];    // メニュー表示名

        // 以下のリクエストはcontrollerより先に取得しているため空の場合がある。
        if ($uid == '') {
            return;    // キーフィールドが設定されていなければ何もしない
        } else {
            $uid = sprintf('%06s', $uid);
            if( $uid == 0 ) return;
            $this->uid = $uid;    // Propertiesへの登録
            $request->add('uid', $uid);
        }
        $this->debug = $request->get('debug');   // デバッグON

        ///// メニュー切替 リクエスト データ取得
        $this->show_menu = $request->get('showMenu');   // ターゲットメニューを取得

        $sql_sum = "
            SELECT count(*) FROM user_detailes where uid like '%{$uid}'
        ";
        ///// Constructer を定義すると 基底クラスの Constructerが実行されない
        ///// 基底ClassのConstructerはプログラマーの責任で呼出す
        parent::__construct($sql_sum, $request, 'meal_appli.log');
    }
    
    // イベント日をセット
    public function setEventDate()
    {
        $work = date('Ym', strtotime("+1 month")) ."00";
        for( $n=0; $n<15; $n++) {
            if( date('w', strtotime($work)) == 3 ) {   // 3:水曜日
                if( ! $this->IsHoliday($work) ) break;
            }
            $work = date('Ymd', strtotime("$work -1 day"));
        }
        $this->event_date = date('Y-m-d', strtotime($work));
    }

    // イベント日を取得
    public function getEventDate()
    {
        return $this->event_date;
    }

    // 指定indx日を取得
    public function getIndexDate($indx)
    {
        $num = $indx - date('w');
        return date('Y-m-d', strtotime("+{$num} day"));
    }

    // メニューカラム名を取得
    public function getMenuIndex()
    {
        return $this->menu_name[0];
    }
    
    // メニュー表示名を取得
    public function getMenuName()
    {
        return $this->menu_name[1];
    }
    
    // 社員ですか？
    public function IsSyain($uid)
    {
        $query = "SELECT uid FROM user_detailes WHERE uid = '$uid' AND retire_date IS NULL";
        if( getResult2($query, $res) <= 0 ) {
            return false;
        }
        return true;
    }
    
    // 名前の取得
    public function getName($uid)
    {
        if($uid=="guest") return "来客者";
        
        $query = "SELECT name FROM user_detailes WHERE uid='$uid'";
        $res = array();
        if ( $this->getResult2($query, $res) <= 0 ) {
            return '';
        }
        return trim($res[0][0]);
    }
    
    // データ取得
    public function getMealData($str_date, $end_date, $uid, $whose)
    {
        $where = "date>='$str_date' AND date<='$end_date' AND uid='$uid' AND whose='$whose'";
        $query = "SELECT * FROM meal_appli WHERE $where ORDER BY date";
        $res = array();
///echo $query . "<BR>";
        if( getResult2($query, $res) > 0 ) return $res;
        return "";
    }

    // 新規追加
    public function MealInsert($date, $uid, $whose)
    {
        $columns = "  date,    uid,    whose";
        $values  = "'$date', '$uid', '$whose'";
        $insert_qry = "INSERT INTO meal_appli ($columns) VALUES ($values);";
//echo $insert_qry . "<BR>";
        return query_affected($insert_qry);
    }

    // 更新
    public function MealUpDate($set, $where)
    {
        $update_qry = "UPDATE meal_appli SET $set WHERE $where";
//echo $update_qry . "<BR>";
        return query_affected($update_qry);
    }

    // [保存]
    public function setMealData($str_date, $end_date, $uid, $whose, $request)
    {
        for($i=0, $date=0; $date<=$end_date; $i++) {
            $date = date('Ymd', strtotime("{$str_date} +{$i} day"));
            $week = date('w', strtotime("$date"));
            // チェック
            $where = "date='$date' AND uid='$uid' AND whose='$whose'";
            $query = "SELECT * FROM meal_appli WHERE $where";
            $res = array();
            if( getResult2($query, $res) <= 0 ) {
                $this->MealInsert($date, $uid, $whose);
            }
            if( $week == 6 || $week == 0 ) continue; // 土、日は除く（レコード作成は行うが更新しない）
            $w = floor($i/7);
            $max = count($this->menu_name[0]);
            $set = "";
            for( $r=0; $r<$max; $r++ ) {
                $cnt = $request->get("{$this->menu_name[0][$r]}_{$w}_{$week}");
                if( ! $cnt ) $cnt = 0;
                if( $set ) $set .= ", ";
                $set .= "{$this->menu_name[0][$r]}={$cnt}";
            }
            $comment = $request->get("comment_{$w}_{$week}");
            if( $i>7 ) { //echo "$date = [$comment]　";
                if( $comment ) {
                    $set .= ", comment='{$comment}'";
                } else {
                    $set .= ", comment=NULL";
                }
            }
            $this->MealUpDate($set, $where);
        }
    }

    // 予約情報取得
    public function getOrderInfo($str_date, $end_date)
    {
        $max = count($this->menu_name[0]);
        $select = "date";
        for( $r=0; $r<$max; $r++ ) {
            if( $select ) $select .= ", ";
            $select .= "SUM({$this->menu_name[0][$r]}) AS {$this->menu_name[0][$r]}";
        }

        $where = "date>='$str_date' AND date<='$end_date'";
        $query = "SELECT $select FROM meal_appli WHERE $where GROUP BY date ORDER BY date";
        $res = array();
//echo "　" . $query . "<BR>";
        if( getResult2($query, $res) > 0 ) return $res;
        return "";
    }

    // 予約詳細情報取得
    public function getOrderDetail($str_date, $end_date)
    {
        $max = count($this->menu_name[0]);
        
        $res2 = array();
        $date = $str_date;
        for( $d=0; $date<$end_date; $d++ ) {
            for($m=0; $m<$max; $m++) {
                $select = "TRIM(uid), whose, {$this->menu_name[0][$m]}, comment";   // 社員番号、誰の、注文数、コメント
                $where = "date='$date' AND {$this->menu_name[0][$m]}>0";    // 日付 注文あり
                $query = "SELECT $select FROM meal_appli WHERE $where ORDER BY date, whose DESC, uid";
                $res = array();
if($date == "20220621" ) {
//echo "$d $m ****";
//echo "　" . $query . "<BR>";
}
                if( getResult2($query, $res) > 0 ) {
                    $res2[$d][$m] = $res;
                } else {
                    $res2[$d][$m] = array();
                }
            }
            $date = date('Ymd', strtotime("$date +1 day")); // 次の日をセット
        }
        // [日付][メニュー][行数][0：UID、1：誰の、2：数量]
//echo count($res2[9][0]) . "　" . $res2[9][0][1][1] . "<BR>";
        
        return $res2;
    }

    // 注文可能
    public function IsOrderable()
    {
        // 現在時刻チェック
        if( date('H') < 13 ) return false;  // ～13:00未満なら注文不可
        // 現在曜日チェック
        $week = date('w');
        if( $week == 5 ) return true;   // 金曜日なら注文可能
        
        // 翌日以降の休日チェック
        $date = date('Ymd');
        for($i=1; $week<5; $i++) {
            $date = date('Ymd', strtotime("+{$i} day"));
            $week = date('w', strtotime("$date"));
//echo $i . "日後：" . $date . "[" . $week . "]" . "<BR>";
            if( $this->IsHoliday($date) ) {
                $orderable = true;  // 注文可能
            } else {
                $orderable = false; // 注文不可
            }
        }
        return $orderable;
    }

    // 総合届情報
    public function getSougouInfo($uid)
    {
        $select = "start_date, end_date, start_time, end_time, content, TRIM(admit_status)";
        $where = "start_date>to_char(CURRENT_DATE, 'YYYY-MM-DD') AND uid='$uid' AND (admit_status != 'CANCEL' AND admit_status != 'DENY')";
        $query = "SELECT $select FROM sougou_deteils WHERE $where";
        $res = array();
//echo "　" . $query . "<BR>";
        if( getResult2($query, $res) > 0 ) return $res;
        return "";
    }

// ============================================================================
// 内部で使用する関数 =========================================================
// ============================================================================
// ============================================================================
// 共通 =======================================================================
// ============================================================================
    // ログインユーザーID 取得
    public function getUID()
    {
        return $this->uid;
    }
    
    // 指定期間の休日情報取得
    public function getHolidayRang($str_date, $end_date)
    {
        if( !$str_date || !$end_date ) return "";
        
        $query = "
                    SELECT  tdate           AS 日付
                    FROM    company_calendar
                    WHERE   tdate >= '{$str_date}' AND tdate <= '{$end_date}' AND bd_flg = 'f'
                 ";
        if( getResult2($query, $res) <= 0 ) {
            return "";
        }
        return $res;
    }
    
    // 休日ですか？
    public function IsHoliday($date)
    {
        if( !$date ) return false;
        
        $query = "
                    SELECT  tdate           AS 日付,     -- 0
                            bd_flg          AS 営業日,   -- 1
                            note            AS コメント  -- 2
                    FROM    company_calendar
                    WHERE   tdate = '{$date}' AND bd_flg = 'f'
                 ";
        if( getResult2($query, $res) <= 0 ) {
            return false;
        }
        return true;
    }
    
// ============================================================================
// テスト =====================================================================
// ============================================================================
    // TEST 
    public function TEST()
    {
    }

    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/

    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
} // Class meal_appli_Model End

?>
