<?php
////////////////////////////////////////////////////////////////////////////////
// 総合届（申請）                                                             //
//                                                              MVC Model 部  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_Model.php                                        //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード

require_once ('../../ComTableMntClass.php');   // TNK 全共通 テーブルメンテ&ページ制御Class

/******************************************************************************
*          総合届（申請）用 MVCのModel部 base class 基底クラスの定義          *
******************************************************************************/
class Sougou_Model extends ComTableMnt
{
    ///// Private properties
    private $syain;
    private $syainbangou;
    private $mail;
    private $approval;
    private $approvalpath;
    private $indx;
    private $rows;
    private $res;
    private $yukyu = array(array(0,0,0,0,0));
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer の定義 (php5へ移行時は __construct() へ変更予定) (デストラクタ__destruct())
    public function __construct($request, $syainbangou='')
    {
        // 以下のリクエストはcontrollerより先に取得しているため空の場合がある。
        $this->syainbangou = $request->get("syain_no");

        if ($syainbangou == '') {
            return;    // キーフィールドが設定されていなければ何もしない
        } else {
            $syainbangou = sprintf('%06s', $syainbangou);
            if( $syainbangou == 0 ) return;
            $this->syainbangou = $syainbangou;    // Propertiesへの登録
            $request->add('syainbangou', $syainbangou);
            $this->getAMandTimeVacationData();
        }
        $sql_sum = "
            SELECT count(*) FROM user_detailes where uid like '%{$syainbangou}'
        ";

        // 退職してないかチェック
        $query = "
            SELECT      uid
            FROM        user_detailes
            WHERE       uid = '$syainbangou' AND retire_date IS NULL
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) {
            $this->syain = false;
        } else {
            $this->syain = true;
            if( $this->setYukyu() ) {
//if($syainbangou == '300667' ) {   // ↓ここではまだ、申請日セットされてない
//                $this->setYotei($request->get("sin_date"));
//}
            }
        }

        $this->approval = $this->getApprovalPath($request, $syainbangou);

        ///// Constructer を定義すると 基底クラスの Constructerが実行されない
        ///// 基底ClassのConstructerはプログラマーの責任で呼出す
        parent::__construct($sql_sum, $request, 'sougou.log');
    }

    // 総合届を申請できる社員
    public function IsInputPossible()
    {
        if( !$this->IsSyain() ) {
            return false;
        }
        if( !$this->IsApproval() ) {
            return false;
        }
        return true;
    }

    // TNKの社員
    public function IsSyain()
    {
        return $this->syain;
    }

    // 承認ルート登録されている
    public function IsApproval()
    {
        return $this->approval;
    }

    // 承認ルート
    public function getApproval()
    {
        return $this->approvalpath;
    }

    // DB列数
    public function getIndx()
    {
        return $this->indx;
    }

    // DB行数
    public function getRows()
    {
        return $this->rows;
    }

    // DB
    public function getRes()
    {
        return $this->res;
    }

    // 休日ですか？
    public function IsHoliday($date)
    {
//if( $this->syainbangou == '300667' ) $_SESSION['s_sysmsg'] .= $date . ' : です';
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
//        $_SESSION['s_sysmsg'] .= $date . ' : 休日です';

        return true;
    }

    // 指定期間の休日情報取得
    public function getHolidayRang($s_year, $e_year)
    {
        if( !$s_year || !$e_year ) return "";

        $query = "
                SELECT  tdate           AS 日付
                FROM    company_calendar
                WHERE   tdate >= '{$s_year}0101' AND tdate <= '{$e_year}1231' AND bd_flg = 'f'
            ";
        if( getResult2($query, $res) <= 0 ) {
            return "";
        }
        return $res;
    }

    // 有休取得数（実績）
    public function KeikakuCnt()
    {
        $this->yukyu[0][0]; // 当期有休日数
        $this->yukyu[0][1]; // 当期有休残
        $this->yukyu[0][2]; // 半日有休回数
        $this->yukyu[0][3]; // 時間休取得分
        $this->yukyu[0][4]; // 時間有休限度
        
        if( $this->yukyu[0][3] == 0 ) {
            $jisseki = $this->yukyu[0][0] - ($this->yukyu[0][1] );
        }else {
            $jisseki = $this->yukyu[0][0] - ($this->yukyu[0][1] + (round($this->yukyu[0][3]/($this->yukyu[0][4]/5), 3)) );
        }

        return $jisseki;
    }

    // 特別計画有休数を取得
    public function GetSpecialPlans($sin_date)
    {
        $s_day = substr($sin_date,0,4)-1 . "-03-31";
        $query = "
                SELECT  start_date      AS 開始日,      -- 0
                        end_date        AS 終了日,      -- 1
                        start_time      AS 開始時間,    -- 2
                        end_time        AS 終了時間,    -- 3
                        content         AS 項目         -- 4
                FROM    sougou_deteils
                WHERE   (start_date<'{$sin_date}' OR end_date<'{$sin_date}')
                    AND (start_date>'{$s_day}' OR end_date>'{$s_day}')
                    AND uid='{$this->syainbangou}'
                    AND yukyu='特別計画'
                    AND (admit_status != 'CANCEL' AND admit_status != 'DENY')
            ";
        if( ($rows = getResult2($query, $res)) <= 0 ) {
//            $_SESSION['s_sysmsg'] .= $sin_date . ' : 予定なし';
            return 0;
        }
        // 特別計画有休数の計算処理
        $cnt = 0;   // カウンター
        for( $i=0; $i<$rows; $i++ ) {
            if( strtotime($res[$i][0]) <= strtotime($s_day) ) {  // 開始日と期のはじめを比較
                // 期のはじめ以前の場合。開始日に期のはじめ日をセット
                $res[$i][0] = date('Ymd', strtotime($s_day . '1 day'));
            }
            if( strtotime($res[$i][1]) > strtotime($sin_date) ) {  // 終了日と申請日を比較
                // 申請日以降の場合。終了日に申請日の前日をセット
                $res[$i][1] = date('Ymd', strtotime($sin_date . '- 1 day'));
            }
            $day = $this->getDayCount($res[$i][0], $res[$i][1]);    // 営業日内の取得日数
            if( trim($res[$i][4]) == '有給休暇' ) {
                $cnt += $day;
            } else if( trim($res[$i][4]) == 'AM半日有給休暇' || trim($res[$i][4]) == 'PM半日有給休暇' ) {
                $cnt += (0.5 * $day);
            } else if( trim($res[$i][4]) == '時間単位有給休暇' ) {
                $houer = ($this->getTimeCount($res[$i][2], $res[$i][3]) * $day);    // 時間休計算;
                $cnt += round($houer/($this->yukyu[0][4]/5), 3);
            }
        }
        return $cnt;
    }

    // 予定有休（申請用）
    public function YoteiKyuka($sin_date, $half)
    {
        if( $sin_date == "" ) return;

        $cnt = 0;
        if( $half ) {
            $s_day = substr($sin_date,0,4) . "-03-31";
            $e_day = substr($sin_date,0,4) . "-10-01";
        } else {
            $s_day = substr($sin_date,0,4)   . "-09-30";
            $e_day = substr($sin_date,0,4)+1 . "-04-01";
        }
        // 予定有休情報取得
        $query = "
                    SELECT  start_date      AS 開始日,      -- 0
                            end_date        AS 終了日,      -- 1
                            content         AS 項目         -- 2
                    FROM    sougou_deteils
                    WHERE   (start_date>'{$sin_date}' OR end_date>'{$sin_date}')
                        AND start_date>'{$s_day}' AND end_date<'{$e_day}'
                        AND uid='{$this->syainbangou}'
                        AND (content='有給休暇' OR content='AM半日有給休暇' OR content='PM半日有給休暇' )
                        AND yukyu!='特別計画'
                        AND (admit_status != 'CANCEL' AND admit_status != 'DENY')
                 ";
        if( ($rows = getResult2($query, $res)) <= 0 ) {
//            $_SESSION['s_sysmsg'] .= $sin_date . ' : 予定なし';
            return 0;
        }

        // 有休残へ予定有休の加算処理
        for( $i=0; $i<$rows; $i++ ) {
            if( strtotime($res[$i][0]) <= strtotime($sin_date) ) {  // 開始日と申請日を比較
                // 開始日と申請日を比較し、申請日以前の場合。開始日に申請日の次の日をセット
                $res[$i][0] = date('Ymd', strtotime($sin_date . ' 1 day'));
            }
            $day = $this->getDayCount($res[$i][0], $res[$i][1]);    // 営業日内の取得日数
            if( trim($res[$i][2]) == '有給休暇' ) {
                $cnt += $day;
            } else if( trim($res[$i][2]) == 'AM半日有給休暇' || trim($res[$i][2]) == 'PM半日有給休暇' ) {
                $cnt += (0.5 * $day);
            }
        }
        return $cnt;
    }

    // 期を取得
    public function getKi()
    {
        $timeDate = date('Ym');
        $today_ym = date('Ymd');
        $tmp = $timeDate - 195603;     // 期計算係数195603
        $tmp = $tmp / 100;             // 年の部分を取り出す
        $ki  = ceil($tmp);             // roundup と同じ

        return $ki;
    }

    // 指定した期の有休残情報ファイルが存在するか？
    public function IsKiInfoFile($ki)
    {
        $query = "
                    SELECT  *
                    FROM    holiday_rest_master
                    WHERE   uid='{$this->syainbangou}' and ki={$ki}
                    ORDER   BY ki DESC LIMIT 1
                 ";
        if( getResult2($query, $this->yukyu) <= 0 ) {
            return false;
        }

        return true;
    }

    // 有休残情報を取得
    public function getYukyu()
    {
        return $this->yukyu;
    }

    // 有休残情報計算・・・社員メニュー view_mineinfo.php ファイル内より引用
    public function setYukyu()
    {
        $timeDate = date('Ym');
        $today_ym = date('Ymd');
        $tmp = $timeDate - 195603;     // 期計算係数195603
        $tmp = $tmp / 100;             // 年の部分を取り出す
        $ki  = ceil($tmp);             // roundup と同じ
        $query = "
                SELECT
                     current_day    AS 当期有休日数     -- 0
                    ,holiday_rest   AS 当期有休残       -- 1
                    ,half_holiday   AS 半日有休回数     -- 2
                    ,time_holiday   AS 時間休取得分     -- 3
                    ,time_limit     AS 時間有休限度     -- 4
                    ,web_ymd        AS 更新年月日       -- 5
                FROM holiday_rest_master
                WHERE uid='{$this->syainbangou}' and ki<={$ki}
                ORDER BY ki DESC LIMIT 1
            ";
        if( getResult2($query, $this->yukyu) <= 0 ) {
            $this->yukyu = array(array(0,0,0,0,0));
            return false;
        }
        return true;
    }

    // 有休残へ予定有休加算
    public function setYotei($sin_date)
    {
        if( $sin_date == "" ) return;

        // 予定有休情報取得
        $query = "
                SELECT  start_date      AS 開始日,      -- 0
                        end_date        AS 終了日,      -- 1
                        start_time      AS 開始時間,    -- 2
                        end_time        AS 終了時間,    -- 3
                        content         AS 項目         -- 4
                FROM    sougou_deteils
                WHERE   (start_date>'{$sin_date}' OR end_date>'{$sin_date}')
                    AND uid='{$this->syainbangou}'
                    AND (content='有給休暇' OR content='AM半日有給休暇' OR content='PM半日有給休暇' OR content='時間単位有給休暇' )
                    AND (admit_status != 'CANCEL' AND admit_status != 'DENY')
            ";
        if( ($rows = getResult2($query, $res)) <= 0 ) {
//            $_SESSION['s_sysmsg'] .= $sin_date . ' : 予定なし';
            return;
        }

        // 有休残へ予定有休の加算処理
        $timecount = false;     // 時間休フラグ初期化
        for( $i=0; $i<$rows; $i++ ) {
            if( strtotime($res[$i][0]) <= strtotime($sin_date) ) {  // 開始日と申請日を比較
                // 開始日と申請日を比較し、申請日以前の場合。開始日に申請日の次の日をセット
                $res[$i][0] = date('Ymd', strtotime($sin_date . ' 1 day'));
            }
            $day = $this->getDayCount($res[$i][0], $res[$i][1]);    // 営業日内の取得日数
            if( trim($res[$i][4]) == '有給休暇' ) {
                $this->yukyu[0][1] -= $day;         // 当期有休残
            } else if( trim($res[$i][4]) == 'AM半日有給休暇' || trim($res[$i][4]) == 'PM半日有給休暇' ) {
                $this->yukyu[0][1] -= (0.5 * $day); // 当期有休残
                $this->yukyu[0][2] += $day;         // 半日有休回数
            } else if( trim($res[$i][4]) == '時間単位有給休暇' ) {
                if( !$timecount ) {
                    // 当期有休残へ既に取得している時間休を加算する。
                    // ※そのまま有休残より減算するとおかしな値になってしまうため。
                    $this->yukyu[0][1] += round($this->yukyu[0][3]/($this->yukyu[0][4]/5), 3);
                    $timecount = true;  // 時間休フラグセット
                }
                $this->yukyu[0][3] += ($this->getTimeCount($res[$i][2], $res[$i][3]) * $day);    // 時間休加算
            }
        }
        if( $timecount ) {
            // 当期有休残より時間休減算
            $this->yukyu[0][1] -= round($this->yukyu[0][3]/($this->yukyu[0][4]/5), 3);
        }
    }

    // 営業日内の取得日数
    public function getDayCount( $sday, $eday)
    {
        $query = "
                SELECT  tdate           AS 日付,     -- 0
                        bd_flg          AS 営業日,   -- 1
                        note            AS コメント  -- 2
                FROM    company_calendar
                WHERE   tdate >= '{$sday}' AND tdate <= '{$eday}' ORDER BY tdate
            ";
        if( ($rows = getResult2($query, $res)) <= 0 ) {
            return 0;
        }
        $cnt = 0;
        for( $r=0; $r<$rows; $r++ ) {
            if( $res[$r][1] == 't' ) {
                $cnt++;
            }
        }
        return $cnt;
    }

    // 時間単位の取得時間を取得
    public function getTimeCount( $stime, $etime)
    {
        $t_hiru = $t_kyukei = 0;
        $t_1200 = strtotime('12:00');
        $t_1245 = strtotime('12:45');
        $t_1500 = strtotime('15:00');
        $t_1510 = strtotime('15:10');
        $t_str = strtotime($stime);
        $t_end = strtotime($etime);
        if( $t_str < $t_1200 &&  $t_end > $t_1245 ) $t_hiru = ($t_1245 - $t_1200);      // 45
        if( $t_str < $t_1500 &&  $t_end > $t_1510 ) $t_kyukei = ($t_1510 - $t_1500);    // 10
        $t_end -= ($t_hiru + $t_kyukei);
        $t_cnt = ($t_end - $t_str) / 60 / 60;
        return $t_cnt;
    }

    // 既に登録されている、AM半日有給休暇、時間単位有給休暇(12:45〜)のデータ取得
    // [送信]する時にチェックする為
    public function getAMandTimeVacationData()
    {
        $query = "
            SELECT      content, start_date, end_date
            FROM        sougou_deteils
            WHERE       uid='{$this->syainbangou}' AND (content='AM半日有給休暇' OR (content='時間単位有給休暇' AND start_time = '12:45'))
                    AND admit_status!='CANCEL' AND admit_status!='DENY'
            ORDER BY    start_date ASC
        ";
        $res = $field = array();
        $rows = getResultWithField2( $query, $field, $res );
        if ( $rows <= 0 ) {
            return false;
        }

        $this->indx = count($field);
        $this->rows = $rows;
        $this->res = $res;

        return true;
    }

    // 表示する為のデータを取得
    public function getViewDataList(&$result)
    {
        $query = "
            SELECT      uid, name, sm.section_name
            FROM        user_detailes  AS ud
            LEFT JOIN   section_master AS sm
            ON          ud.sid = sm.sid
            WHERE       uid = '{$this->syainbangou}' AND retire_date IS NULL
            ORDER BY    uid ASC
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) {
            $_SESSION['s_sysmsg'] .= '登録がありません！';
            $this->syain = '';
            return false;
        }
        $result->add_array($res);
        return $rows;
    }

    // 嘱託 or 契約社員 ですか？
    public function IsKeiyaku($uid)
    {
        $query = "
            SELECT          pid
            FROM            user_detailes
            WHERE           uid = '$uid' AND (pid=8 OR pid=9)
        ";
        $res = array();

        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) {
            return false;
        }

        return true;
    }

    // 課長、課長代理 なら
    public function IsKatyou()
    {
        $query = "
            SELECT          ct.act_id
            FROM            user_detailes   AS ud
            LEFT OUTER JOIN cd_table        AS ct   USING(uid)
            WHERE           ud.uid = '$this->syainbangou' AND (ud.pid=46 OR ud.pid=50 )
        ";
        $res = array();

        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) {
            return false;
        }

        return true;
    }

    // 課長代理以上？ 95=副工場長
    public function IsBukatyou()
    {
        $query = "
            SELECT          ct.act_id
            FROM            user_detailes   AS ud
            LEFT OUTER JOIN cd_table        AS ct   USING(uid)
            WHERE           ud.uid = '$this->syainbangou' AND (ud.pid=46 OR ud.pid=47 OR ud.pid=50 OR ud.pid=70 OR ud.pid=95 )
        ";
        $res = array();

        if ( $this->getResult2($query, $res) <= 0 ) {
            return false;
        }

        return true;
    }

    // 承認ルート取得
    public function getApprovalPath(&$request, $uid)
    {
        $get_qry = "
            SELECT
                ct.act_id, apm.kakarityo, apm.katyo, apm.butyo, apm.somukatyo, apm.kanributyo, apm.kojyotyo
            FROM
                cd_table                AS ct
            LEFT JOIN
                approval_path_master    AS apm
            ON
                ct.act_id = apm.act_id
            WHERE
                uid = '$uid'
            LIMIT 1
        ";
        $res = array();
        if( getResultWithField2($get_qry, $field, $res) <= 0 ) {
            $this->approvalpath = "承認経路マスター取得に失敗しました。";
            return false;
        }

        $max = count($field);
        // 自分の社員番号の所はスキップする。
        for( $i=0; $i<$max; $i++) {
            if( $res[0][$i] == $uid ) $res[0][$i] = '------';
        }

        $get_qry2 = "
            SELECT
                standards_date      AS 基準日
                ,somukatyo           AS 総務課長
                ,kanributyo          AS 管理部長
                ,kojyotyo            AS 工場長
            FROM
                approval_path_master_Late
            WHERE
                standards_date <= CURRENT_TIMESTAMP
            ORDER BY
                standards_date DESC
            LIMIT 1
        ";
        $res2 = array();

        if( getResultWithField2($get_qry2, $field2, $res2) <= 0 ) {
            $this->approvalpath = "承認経路マスター（後半部分）取得失敗しました。";
            return false;
        }

        // 承認経路マスターより'on'の所に、総務課長〜工場長までの社員コードをセット
        for( $i=4; $i<$max; $i++ ) {
            if( trim($res[0][$i]) == 'on' ) $res[0][$i] = $res2[0][$i-3];
        }

        $request->add("act_id", $res[0][0]);
        $request->add("kakarityo", $res[0][1]);
        $request->add("katyo", $res[0][2]);
        $request->add("butyo", $res[0][3]);
        $request->add("somukatyo", $res[0][4]);
        $request->add("kanributyo", $res[0][5]);
        $request->add("kojyotyo", $res[0][6]);

        $app_path = '';

        if( $this->IsBukatyou() ){    // 課長代理、課長、部長代理、部長は、初めに工場長承認になる。
            if( $res[0][6] != '------' ) {
                $app_path .= "工 場 長";
            } else {
                $this->approvalpath = "経理コード（{$res[0][0]}）工場長 登録 なし。管理者へ連絡して下さい。";
                return false;
            }
            $max--; // 工場長は既にセットしている為、最大を-1する。
        }
        if( $res[0][6] == $uid ) {
            $max--; // 工場長は既にセットしている為、最大を-1する。
        }

        for( $i=1; $i<$max; $i++ ) {
//$_SESSION['s_sysmsg'] .= "[{$i}]:{$res[0][$i]} ";
            if( is_numeric(($res[0][$i])) ) {
                if($app_path != "") $app_path .= " → ";

                switch ($i) {
                case 1:
                case 2:
                case 3:
                    $app_path .= $this->getSyainName($res[0][$i]);
                    break;
                case 4:
                    $app_path .= "総務課長";
                    break;
                case 5:
                    $app_path .= "管理部長";
                    break;
                case 6:
                    $app_path .= "工 場 長";
                    break;
                }
            }
        }

        if( $app_path == '' ) {
            $this->approvalpath = "経理コード（{$res[0][0]}）登録 なし。管理者へ連絡して下さい。";
            return false;
        }

        $this->approvalpath = $app_path;
        return true;
    }

    // 社員の所属を取得
    public function getSyozoku($uid)
    {
        ///// 常に $partsKey フィールドでの検索
        $query = "
            SELECT      sm.section_name
            FROM        user_detailes  AS ud
            LEFT JOIN   section_master AS sm
            ON          ud.sid = sm.sid
            WHERE       uid = '$uid'
            ORDER BY    uid ASC
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) {
            $_SESSION['s_sysmsg'] = '登録がありません！';
            return "------";
        }
        return $res[0][0];
    }

    // 社員名取得
    public function getSyainName($str)
    {
            $query = "
                SELECT      name
                FROM        user_detailes
                WHERE       uid = '$str'
            ";
            $res = array();
            if ( $this->getResult2($query, $res) <= 0 ) {
                return '';
            }
            return $res[0][0];
    }

    // 申請した総合届に対する承認ルートをDBへ登録
    public function setApprovalPath($request)
    {
            $date                   = $request->get("sin_date");        // 申請年月日
            $uid                    = $request->get("syain_no");        // 申請者 社員番号
            $this->getApprovalPath($request, $uid);
            $act_id                 = $request->get("act_id");
            $kakarityo              = $request->get("kakarityo");
            $katyo                  = $request->get("katyo");
            $butyo                  = $request->get("butyo");
            $somukatyo              = $request->get("somukatyo");
            $kanributyo             = $request->get("kanributyo");
            $kojyotyo               = $request->get("kojyotyo");

            $insert_qry = "
                INSERT INTO approval_path
                (date, uid, act_id, kakarityo, katyo, butyo, somukatyo, kanributyo, kojyotyo)
                VALUES
                ('$date', '$uid', '$act_id', '$kakarityo', '$katyo', '$butyo', '$somukatyo', '$kanributyo', '$kojyotyo');
            ";
            if( query_affected($insert_qry) <= 0 ) {
                $_SESSION['s_sysmsg'] .= "経路の登録に失敗しました。";
                return false;
            }
        return true;
    }

    // 至急時メール送信
    public function hurryMaile($admit_uid)
    {
        $query_m = "SELECT trim(name), trim(mailaddr)
                        FROM
                            user_detailes
                        LEFT OUTER JOIN
                            user_master USING(uid)
                        ";

        //$search_m = "WHERE uid='300144'";
        //$search_m = "WHERE uid='300667'";
        // 上はテスト用 強制的に自分にメールを送る
        $search_m = "WHERE uid='$admit_uid'";

        $query_m = sprintf("$query_m %s", $search_m);     // SQL query 文の完成
        $res_m   = array();
        $field_m = array();
        $res_sum_m = array();
        if ($this->getResult2($query_m, $res_sum_m) <= 0) {
            exit();
        } else {
            $sendna = $res_sum_m[0][0];
            $mailad = $res_sum_m[0][1];
            $_SESSION['u_mailad']  = $mailad;
            $to_addres = $mailad;
            $add_head = "";
            $attenSubject = "宛先： {$sendna} 様 【至急】総合届承認のお知らせ";
            //テスト用 メッセージ
            //$message  = "{$admit_uid}様 至急の総合届があります。\n\n";
            //テスト用 下に変更すること
            $message  = "至急の総合届があります。\n\n";
            $message .= "総合届の承認処理をお願いします。\n\n";
            $message .= "http://masterst/per_appli/in_sougou_admit/sougou_admit_Main.php?calUid=";
            $message .= $admit_uid;
            if (mb_send_mail($to_addres, $attenSubject, $message, $add_head)) {
                // 出席者へのメール送信履歴を保存
                //$this->setAttendanceMailHistory($serial_no, $atten[$i]);
            }
            ///// Debug
            //if ($cancel) {
            //    if ($i == 0) mb_send_mail('tnksys@nitto-kohki.co.jp', $attenSubject, $message, $add_head);
            //}
        }
    }

    // 総合届の申請内容をDBへ登録
    public function add($request)
    {
        ///// パラメーターの分割
        $date                   = $request->get("sin_date");        // 申請年月日
        $uid                    = $request->get("syain_no");        // 申請者 社員番号
        $start_date             = $request->get("str_date");        // 期間 開始 日付
        $start_time             = $request->get("str_time");        // 期間 開始 時間
        $end_date               = $request->get("end_date");        // 期間 終了 日付
        $end_time               = $request->get("end_time");        // 期間 終了 時間
        $content                = $request->get('r1');              // 内容（ラジオ1）
        $yukyu                  = $request->get('r2');              // 内容（ラジオ2）有休関連
        $ticket01               = $request->get('r3');              // 内容（ラジオ3）乗車券
        $ticket02               = $request->get('r4');              // 内容（ラジオ4）新幹線：不要
        $special                = $request->get('r5');              // 内容（ラジオ5）特別関連
        $others                 = $request->get('ikisaki');         // 内容（文字列1）行先・振替休日・その他
        if( $others == '' )
            $others             = $request->get('tokubetu_sonota'); // 内容（文字列1）行先・振替休日・その他
        if( $others == '' )
            $others             = $request->get('hurikae');         // 内容（文字列1）行先・振替休日・その他
        if( $others == '' )
            $others             = $request->get('syousai_sonota');  // 内容（文字列1）行先・振替休日・その他

        $place                  = $request->get('todouhuken');      // 内容（文字列2）都道府県
        $purpose                = $request->get('mokuteki');        // 内容（文字列3）目的
        $ticket01_set           = $request->get('setto1');          // 内容（文字列4）乗車券セット数
        $ticket02_set           = $request->get('setto2');          // 内容（文字列5）新幹線セット数
        $doukousya              = $request->get('doukou');          // 内容（文字列6）同行者
        if( $doukousya == '' )
            $doukousya             = '---';                         // 内容（文字列6）同行者
        $remarks                = $request->get('bikoutext');       // 備考
        if( $remarks == '' )
            $remarks             = '---';                           // 備考

        $contact                = $request->get('r6');              // 連絡先（ラジオ）
        if( $contact == '' )
            $contact             = '---';                           // 連絡先（ラジオ）
        $contact_other          = $request->get('tel_sonota');      // 連絡先（その他）
        $contact_tel            = $request->get('tel_no');          // 連絡先（TEL）
        $received_phone         = '';                               // 受電者（チェック）
        $received_phone_date    = $request->get('jyu_date');        // 受電者（日時）
        $received_phone_name    = $request->get('outai');           // 受電者（応対者）
        if( $received_phone_name) $received_phone = '受電者';       // 応対者がいる時、チェックを受電者にする

        $hurry                  = $request->get('c2');              // 至急（チェック）

        $jyuden_skip = false; // 課長代理、課長 の事後申請の場合初めに上長(部長)

//        if( ($ticket01 != "" && $ticket01 != "不要") || ($ticket02 != "" && $ticket02 != "不要") ) {
        if( $ticket01 == "片道" || $ticket01 == "往復" || $ticket02 == "片道" || $ticket02 == "往復" ) {
            $ticket             = true;     // 回数券の有/無
        } else {
            $ticket             = false;    // 回数券の有/無
        }
        $approval_status        = 0;        // 承認状況
        $amano_input            = 0;        // アマノ入力の有無

        if( $content == "有給休暇" || $content == "AM半日有給休暇" || $content == "PM半日有給休暇" ||
            $content == "時間単位有給休暇" || $content == "欠勤"  || $content == "遅刻早退" ) {
            $insert_qry = "
                INSERT INTO sougou_deteils
                (date, uid, start_date, start_time, end_date, end_time, content, yukyu, remarks)
                VALUES
                ('$date', '$uid', '$start_date', '$start_time', '$end_date', '$end_time', '$content', '$yukyu', '$remarks');
            ";
        } else if( $content == "出張（日帰り）" || $content == "出張（宿泊）"
            || $content == "直行" || $content == "直帰" || $content == "直行/直帰" ) {
            if( !$ticket ) {
                $ticket = 'f';
            }
            if(!$ticket01) $ticket01 = "不要";
            if(!$ticket02) $ticket02 = "不要";
            if( $content == "直行" ) {
                $insert_qry = "
                    INSERT INTO sougou_deteils
                    (date, uid, start_date, start_time, end_date, content,
                     ticket01, ticket02, others, place, purpose, doukousya, remarks, ticket)
                    VALUES
                    ('$date', '$uid', '$start_date', '$start_time', '$end_date', '$content',
                     '$ticket01', '$ticket02', '$others', '$place','$purpose', '$doukousya', '$remarks', '$ticket');
                ";
            } else if( $content == "直帰" ) {
                $insert_qry = "
                    INSERT INTO sougou_deteils
                    (date, uid, start_date, end_date, end_time, content,
                     ticket01, ticket02, others, place, purpose, doukousya, remarks, ticket)
                    VALUES
                    ('$date', '$uid', '$start_date', '$end_date', '$end_time', '$content',
                     '$ticket01', '$ticket02', '$others', '$place','$purpose', '$doukousya', '$remarks', '$ticket');
                ";
            } else {
                $insert_qry = "
                    INSERT INTO sougou_deteils
                    (date, uid, start_date, start_time, end_date, end_time, content,
                     ticket01, ticket02, others, place, purpose, doukousya, remarks, ticket)
                    VALUES
                    ('$date', '$uid', '$start_date', '$start_time', '$end_date', '$end_time', '$content',
                     '$ticket01', '$ticket02', '$others', '$place','$purpose', '$doukousya', '$remarks', '$ticket');
                ";
            }
            if( !$ticket01_set && !$ticket02_set ) $ticket = false;
        } else if( $content == "特別休暇" ) {
            if( $special != "その他" ) {
                $insert_qry = "
                    INSERT INTO sougou_deteils
                    (date, uid, start_date, start_time, end_date, end_time, content, special, remarks)
                    VALUES
                    ('$date', '$uid', '$start_date', '$start_time', '$end_date', '$end_time', '$content', '$special', '$remarks');
                ";
            } else {
                $insert_qry = "
                    INSERT INTO sougou_deteils
                    (date, uid, start_date, start_time, end_date, end_time, content, special, others, remarks)
                    VALUES
                    ('$date', '$uid', '$start_date', '$start_time', '$end_date', '$end_time', '$content', '$special', '$others', '$remarks');
                ";
            }
        } else if( $content == "振替休日" || $content == "その他" ) {
            $insert_qry = "
                INSERT INTO sougou_deteils
                (date, uid, start_date, start_time, end_date, end_time, content, others, remarks)
                VALUES
                ('$date', '$uid', '$start_date', '$start_time', '$end_date', '$end_time', '$content', '$others', '$remarks');
            ";
        } else if( $content == "IDカード通し忘れ（出勤）" ) {
            $insert_qry = "
                INSERT INTO sougou_deteils
                (date, uid, start_date, start_time, end_date, content, remarks)
                VALUES
                ('$date', '$uid', '$start_date', '$start_time', '$end_date', '$content', '$remarks');
            ";
            $jyuden_skip = true; // IDカード系
        } else if( $content == "IDカード通し忘れ（退勤）" ) {
            $insert_qry = "
                INSERT INTO sougou_deteils
                (date, uid, start_date, end_date, end_time, content, remarks)
                VALUES
                ('$date', '$uid', '$start_date', '$end_date', '$end_time', '$content', '$remarks');
            ";
            $jyuden_skip = true; // IDカード系
        } else if( $content == "時限承認忘れ（残業申告漏れ）" || $content == "IDカード通し忘れ（退勤）＋ 時限承認忘れ（残業申告漏れ）" ) {
            $insert_qry = "
                INSERT INTO sougou_deteils
                (date, uid, start_date, start_time, end_date, end_time, content, remarks)
                VALUES
                ('$date', '$uid', '$start_date', '$start_time', '$end_date', '$end_time', '$content', '$remarks');
            ";
            $jyuden_skip = true; // IDカード系
        } else {
            $insert_qry = "
                INSERT INTO sougou_deteils
                (date, uid, start_date, start_time, end_date, end_time, content, remarks)
                VALUES
                ('$date', '$uid', '$start_date', '$start_time', '$end_date', '$end_time', '$content', '$remarks');
            ";
        }

        if( query_affected($insert_qry) <= 0 ) {
            $_SESSION['s_sysmsg'] = "登録に失敗しました。";
            return false;
        }

        if( $ticket ) {
            if( $ticket01 != "不要" && $ticket02 != "不要") {
                $update_qry = sprintf("UPDATE sougou_deteils SET ticket01_set='%s', ticket02_set='%s' WHERE date='%s' AND uid='%s'", $ticket01_set, $ticket02_set, $date, $uid);
            } else if( $ticket01 != "不要" ) {
                $update_qry = sprintf("UPDATE sougou_deteils SET ticket01_set='%s' WHERE date='%s' AND uid='%s'", $ticket01_set, $date, $uid);
            } else if( $ticket02 != "不要" ) {
                $update_qry = sprintf("UPDATE sougou_deteils SET ticket02_set='%s' WHERE date='%s' AND uid='%s'", $ticket02_set, $date, $uid);
            } else {
                $update_qry = "";
            }
            if( $update_qry != "" && query_affected($update_qry) <= 0 ) {
                $_SESSION['s_sysmsg'] .= "回数券の登録に失敗しました。";
            }
        }

        if( $contact == "その他" ) {
            $update_qry = sprintf("UPDATE sougou_deteils SET contact='%s', contact_other='%s', contact_tel='%s' WHERE date='%s' AND uid='%s'", $contact, $contact_other, $contact_tel, $date, $uid);
        } else if( $contact == "出張先" ) {
            $update_qry = sprintf("UPDATE sougou_deteils SET contact='%s', contact_tel='%s' WHERE date='%s' AND uid='%s'", $contact, $contact_tel, $date, $uid);
        } else {
            $update_qry = sprintf("UPDATE sougou_deteils SET contact='%s' WHERE date='%s' AND uid='%s'", $contact, $date, $uid);
        }
        if( query_affected($update_qry) <= 0 ) {
            $_SESSION['s_sysmsg'] .= "連絡先の登録に失敗しました。";
        }

        if( $received_phone == "受電者" ) {
            $update_qry = sprintf("UPDATE sougou_deteils SET received_phone='%s', received_phone_date='%s', received_phone_name='%s' WHERE date='%s' AND uid='%s'", $received_phone, $received_phone_date, $received_phone_name, $date, $uid);
            if( query_affected($update_qry) <= 0 ) {
                $_SESSION['s_sysmsg'] .= "受電者の登録に失敗しました。";
            }
        }

        $this->setApprovalPath($request);

        if( $this->IsKatyou() ) {           // 課長代理、課長 は、初めに工場長承認になる。
            $approval              = $request->get("kojyotyo");
/**/
            $sin_dt = new DateTime($date);                          // 申請日時
            $end_dt = new DateTime("{$end_date} {$end_time}");      // 対象日時(終了)
            if( $sin_dt >= $end_dt && !$jyuden_skip ) {
                $approval          = $request->get("butyo");
            }
/**/
        } else if( $this->IsBukatyou() ) {  // 部長代理、部長は、初めに工場長承認になる。
            $approval              = $request->get("kojyotyo");
        } else {
            $approval              = $request->get("kakarityo");
        }
        if( !is_numeric($approval) ) $approval = $request->get("katyo");
        if( !is_numeric($approval) ) $approval = $request->get("butyo");
        if( !is_numeric($approval) ) $approval = $request->get("somukatyo");
        if( !is_numeric($approval) ) $approval = $request->get("kanributyo");
        if( !is_numeric($approval) ) $approval = $request->get("kojyotyo");

        if( $approval != '' ) {
            $update_qry = sprintf("UPDATE sougou_deteils SET admit_status='%s' WHERE date='%s' AND uid='%s'", $approval, $date, $uid);
            if( query_affected($update_qry) <= 0 ) {
                $_SESSION['s_sysmsg'] .= "$approval 承認先の登録に失敗しました。";
            }
        }

        if( $hurry == "至急" ) {
            $update_qry = sprintf("UPDATE sougou_deteils SET hurry='%s' WHERE date='%s' AND uid='%s'", $hurry, $date, $uid);
            if( query_affected($update_qry) <= 0 ) {
                $_SESSION['s_sysmsg'] .= "至急の登録に失敗しました。";
            }
            $this->hurryMaile($approval);
        }

        if( $request->get('reappl') == 'on' ) {
            $deny_uid = $request->get("deny_uid");
            $previous_date = $request->get("previous_date");
            $insert_qry = "
                INSERT INTO sougou_reappl
                (date, uid, reappl, deny_uid, re_date)
                VALUES
                ('$previous_date', '$uid', 't', '$deny_uid', '$date');
            ";
            if( query_affected($insert_qry) <= 0 ) {
                $_SESSION['s_sysmsg'] = "再申請のDB登録に失敗しました。";
            }
        }

    }

    // 再申請可能ですか？
    public function IsReApplPossible($request)
    {
        $date = str_replace('@',' ', $request->get("date"));
        $uid = $request->get("syainbangou");

        $query = "
                SELECT  *
                FROM    sougou_reappl
                WHERE   date='{$date}' AND uid='{$uid}';
            ";
        if( getResult2($query, $res) <= 0 ) {
            return true;    // 可能
        }
        return false;       // 既に申請済み
    }

    // 再申請の為表示するデータを取得
    public function GetReViewData(&$request)
    {
        $date = str_replace('@',' ', $request->get("date"));

        $query = "
            SELECT      *
            FROM        sougou_deteils
            WHERE       date='{$date}'
                    AND uid='{$request->get("syainbangou")}'
        ";
        $res = $field = array();
        $rows = getResultWithField2( $query, $field, $res );
        if ( $rows <= 0 ) {
            return false;
        }

//        $request->add("sin_date", $res[0][0]);              // 申請年月日
        $request->add("syain_no", $res[0][1]);              // 申請者 社員番号
        $res[0][2] = str_replace('-','', $res[0][2]);
        $request->add("str_date", $res[0][2]);              // 期間 開始 日付
        $request->add("str_time", $res[0][3]);              // 期間 開始 時間
        $res[0][4] = str_replace('-','', $res[0][4]);
        $request->add("end_date", $res[0][4]);              // 期間 終了 日付
        $request->add("end_time", $res[0][5]);              // 期間 終了 時間
        $request->add('r1', trim($res[0][6]));              // 内容（ラジオ1）
        $request->add('r2', trim($res[0][7]));              // 内容（ラジオ2）有休関連
        $request->add('r3', trim($res[0][8]));              // 内容（ラジオ3）乗車券
        $request->add('r4', trim($res[0][9]));              // 内容（ラジオ4）新幹線：不要
        $request->add('r5', trim($res[0][10]));             // 内容（ラジオ5）特別関連
        $res[0][11] = trim($res[0][11]);

        if( $request->get('r1') == "特別休暇" ) {
            $request->add('tokubetu_sonota', $res[0][11]);  // 内容（文字列1）特別関連その他
        } else if( $request->get('r1') == "振替休日" ) {
            $request->add('hurikae', $res[0][11]);          // 内容（文字列1）振替休日
        } else if( $request->get('r1') == "その他" ) {
            $request->add('syousai_sonota', $res[0][11]);   // 内容（文字列1）その他
        } else {
            $request->add('ikisaki', $res[0][11]);          // 内容（文字列1）行先
        }

        $request->add('todouhuken', trim($res[0][12]));     // 内容（文字列2）都道府県
        $request->add('mokuteki', trim($res[0][13]));       // 内容（文字列3）目的
        $request->add('setto1', trim($res[0][14]));         // 内容（文字列4）乗車券セット数
        $request->add('setto2', trim($res[0][15]));         // 内容（文字列5）新幹線セット数
        $request->add('doukou', trim($res[0][16]));         // 内容（文字列6）同行者
        if( trim($res[0][17]) == '---' ) $res[0][17] = '';
        $res[0][17] = trim($res[0][17]) . '(' . substr($request->get('date'), 0, 10) . " 申請の再申請)";
        $request->add('bikoutext', $res[0][17]);            // 備考
        $request->add('r6', trim($res[0][18]));             // 連絡先（ラジオ）
        $request->add('tel_sonota', trim($res[0][19]));     // 連絡先（その他）
        $request->add('tel_no', trim($res[0][20]));         // 連絡先（TEL）

        $request->add('received', $res[0][21]);             // 受電者
        $request->add('jyu_date', $res[0][22]);             // 応対日
        $request->add('outai', trim($res[0][23]));          // 応対者

        $request->add('c2', $res[0][24]);                   // 至急（チェック）
        $request->add('ticket', $res[0][25]);

        $request->add('reappl', 'on');                      // 再申請フラグ
        $request->add('previous_date', $date);              // 前の申請日時

        return true;
    }

    // 申請の取消へ表示するデータを取得
    public function GetDelViewData(&$request)
    {
        $date = str_replace('@',' ', $request->get("date"));

        $query = "
            SELECT      reason              -- 否認理由
            FROM        admit_stop_reason
            WHERE       date='{$date}'
                    AND uid='{$request->get("syainbangou")}'
        ";
        $res = $field = array();
        $rows = getResultWithField2( $query, $field, $res );
        if ( $rows <= 0 ) {
            return "";
        }

        return $res[0][0];
    }

    // 取消メール送信
    public function DelReasonMail($request)
    {
        $deny_uid = $request->get("deny_uid"); //否認者No.
//$deny_uid = '300667';// テスト用

        $query_m = "SELECT trim(name), trim(mailaddr)
                        FROM
                            user_detailes
                        LEFT OUTER JOIN
                            user_master USING(uid)
                        ";

        //$search_m = "WHERE uid='300144'";
        //$search_m = "WHERE uid='300667'";
        // 上はテスト用 強制的に自分にメールを送る
        $search_m = "WHERE uid='$deny_uid'";

        $query_m = sprintf("$query_m %s", $search_m);     // SQL query 文の完成
        $res_m   = array();
        $field_m = array();
        $res_sum_m = array();
        if ($this->getResult2($query_m, $res_sum_m) <= 0) {
            exit();
        } else {
            $sendna = $res_sum_m[0][0];
            $mailad = $res_sum_m[0][1];
            $_SESSION['u_mailad']  = $mailad;
            $to_addres = $mailad;
            $add_head = "";
            $attenSubject = "宛先： {$sendna} 様 総合届【取消】のお知らせ";
            // メッセージ
            $name = trim($this->getSyainName($request->get("syainbangou")));
            $message  = "{$name} 様が、以下の理由により総合届の申請を取り下げました。\n\n";
            $message .= "【取消理由】\n{$request->get('del_reason')}";
            if (mb_send_mail($to_addres, $attenSubject, $message, $add_head)) {
                // 出席者へのメール送信履歴を保存
                //$this->setAttendanceMailHistory($serial_no, $atten[$i]);
            }
            ///// Debug
            //if ($cancel) {
            //    if ($i == 0) mb_send_mail('tnksys@nitto-kohki.co.jp', $attenSubject, $message, $add_head);
            //}
            $this->DelReasonSave($request);
        }
    }

    // 取消可能ですか？
    public function IsDelPossible($request)
    {
        $date = str_replace('@',' ', $request->get("date"));
        $uid = $request->get("syainbangou");

        $query = "
                SELECT  *
                FROM    sougou_del_reason
                WHERE   date='{$date}' AND uid='{$uid}';
            ";
        if( getResult2($query, $res) <= 0 ) {
            return true;    // 可能
        }
        return false;       // 既に取消済み
    }

    // 取消理由をDBへ保存
    public function DelReasonSave($request)
    {
        $date = str_replace('@',' ', $request->get('date'));
        $uid = $request->get('syainbangou');
        $reason = $request->get('del_reason');

        $insert_qry = "
            INSERT INTO sougou_del_reason
            (date, uid, reason)
            VALUES
            ('$date', '$uid', '$reason');
        ";
        if( query_affected($insert_qry) <= 0 ) {
            $_SESSION['s_sysmsg'] = "取消理由のDB登録に失敗しました。";
        }
    }

    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/

    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
} // Class Sougou_Model End

?>
