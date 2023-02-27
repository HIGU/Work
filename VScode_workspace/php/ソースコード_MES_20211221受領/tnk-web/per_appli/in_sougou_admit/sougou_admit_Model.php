<?php
////////////////////////////////////////////////////////////////////////////////
// 総合届（承認）                                                             //
//                                                              MVC Model 部  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_admit_Model.php                                  //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード

require_once ('../../ComTableMntClass.php');   // TNK 全共通 テーブルメンテ&ページ制御Class
require_once ('../../ControllerHTTP_Class.php');       // TNK 全共通 MVC Controller Class


/******************************************************************************
*          総合届（承認）用 MVCのModel部 base class 基底クラスの定義          *
******************************************************************************/
class Sougou_Admit_Model extends ComTableMnt
{
    ///// Private properties
    private $admit;
    private $uid;
    private $mail;
    private $indx;
    private $rows;
    private $last_rows;
    private $res;
    private $yukyu = array(array(0,0,0,0,0));

    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer の定義 (php5へ移行時は __construct() へ変更予定) (デストラクタ__destruct())
    public function __construct($request, $uid='')
    {
        $this->last_rows = $request->get('rows');
        // 以下のリクエストはcontrollerより先に取得しているため空の場合がある。
        if ($uid == '') {
            return;    // キーフィールドが設定されていなければ何もしない
        } else {
            $uid = sprintf('%06d', $uid);
            if( $uid == 0 ) return;
            $this->uid = $uid;    // Propertiesへの登録
        }
        $sql_sum = "
            SELECT count(*) FROM sougou_deteils where admit_status like '%{$uid}'
        ";

        $query = "
            SELECT      admit_status
            FROM        sougou_deteils
            WHERE       admit_status = '$uid'
        ";
        $res = array();
        if ( ($this->admit = $this->getResult2($query, $res)) <= 0 ) {
            $this->admit = 0;
        }

        ///// Constructer を定義すると 基底クラスの Constructerが実行されない
        ///// 基底ClassのConstructerはプログラマーの責任で呼出す
        parent::__construct($sql_sum, $request, 'sougou_admit.log');
    }

    // 承認画面へ表示する総合届がある
    public function IsAdmit()
    {
        return $this->admit;
    }

    // 課長、課長代理 ですか？
    public function IsKatyou($uid)
    {
        $query = "
            SELECT          ct.act_id
            FROM            user_detailes   AS ud
            LEFT OUTER JOIN cd_table        AS ct   USING(uid)
            WHERE           ud.uid = '$uid' AND (ud.pid=46 OR ud.pid=50 )
        ";
        $res = array();

        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) {
            return false;
        }

        return true;
    }

    // 休日ですか？
    public function IsHoliday($date)
    {
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

    // 再申請ですか？
    public function IsReAppl($date, $uid)
    {
        $query = "
                SELECT  *
                FROM    sougou_reappl
                WHERE   uid='{$uid}' AND re_date='{$date}';
            ";
        if( getResult2($query, $res) <= 0 || $res[0][2]!='t') {
            return false;
        }
        return true;
    }

    // 前申請日時取得
    public function GetPreviousDate($date, $uid)
    {
        $query = "
                SELECT  *
                FROM    sougou_reappl
                WHERE   uid='{$uid}' AND re_date='{$date}';
            ";
        if( getResult2($query, $res) <= 0 || $res[0][2]!='t') {
            return "";
        }
        return $res[0][0];
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

    // ？代理承認 作成中 テスト
    public function getAgentList()
    {
        if( $this->IsKatyou($this->uid) ) {
            $query = "
                SELECT          DISTINCT kakarityo
                FROM            approval_path
                WHERE           kakarityo!='------' AND katyo='$this->uid'
            ";
        } else if( $this->IsButyou($this->uid) ) {
            $query = "
                SELECT          DISTINCT katyo
                FROM            approval_path
                WHERE           katyo!='------' AND butyo='$this->uid'
            ";
        } else {
            return '';
        }
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) {
            return '';
        }

        return $res;
    }

    // 承認者UID取得
    public function getAdmitUID(&$res)
    {
        $query = "
            SELECT DISTINCT admit_status
            FROM            sougou_deteils
            WHERE           admit_status!='END' AND admit_status!='DENY' AND admit_status!='CANCEL'
        ";
        $res = array();
        return getResult2($query, $res);
    }

    // 承認待ち件数取得
    public function getAdmitCnt($uid)
    {
        $query = "SELECT count(*) FROM sougou_deteils where admit_status='$uid'";
        $res = array();
        if( getResult2($query, $res) <= 0 ) return 0;
        
        return $res[0][0];
    }

    // 承認要請メール送信
    public function AdmitRequestMaile($send_uid)
    {
        $query_m = "SELECT trim(name), trim(mailaddr) FROM user_detailes LEFT OUTER JOIN user_master USING(uid)";
        $search_m = "WHERE uid='$send_uid'";
        $search_m = "WHERE uid='300667'";   //  TEST 強制的に変換、自分にメールを送る
        $query_m = sprintf("$query_m %s", $search_m);     // SQL query 文の完成
        $res_sum_m = array();
        if ($this->getResult2($query_m, $res_sum_m) <= 0) {
            exit();
        } else {
            $sendna = $res_sum_m[0][0];
            $sendna = trim($this->getSyainName($send_uid));    // TEST 強制的に変換
            $mailad = $res_sum_m[0][1];
            $_SESSION['u_mailad']  = $mailad;
            $to_addres = $mailad;
            $add_head = "";
            $attenSubject = "宛先： {$sendna} 様 総合届【承認要請】のお知らせ";
            $request_name = trim($this->getSyainName($this->uid));
            $message  = "{$sendna} 様\n\n";
            $message .= "{$request_name} 様 より 承認待ち総合届を処理するよう要請がありました。\n\n";
            $message .= "早急に、承認処理をするようお願いします。\n\n";
            $message .= "http://masterst/per_appli/in_sougou_admit/sougou_admit_Main.php?calUid={$send_uid}";
            echo $message;
//            mb_send_mail($to_addres, $attenSubject, $message, $add_head);
        }
    }

    // 部長、部長代理 ですか？ 95=副工場長
    public function IsButyou($uid)
    {
        $query = "
            SELECT          ct.act_id
            FROM            user_detailes   AS ud
            LEFT OUTER JOIN cd_table        AS ct   USING(uid)
            WHERE           ud.uid = '$uid' AND (ud.pid=47 OR ud.pid=70 OR ud.pid=95 )
        ";
        $res = array();

        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) {
            return false;
        }

        return true;
    }

    // 工場長 ですか？
    public function IsKoujyoutyou()
    {
        $query = "
            SELECT          ct.act_id
            FROM            user_detailes   AS ud
            LEFT OUTER JOIN cd_table        AS ct   USING(uid)
            WHERE           ud.uid = '$this->uid' AND ud.pid=110
        ";
        $res = array();

        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) {
            return false;
        }

        return true;
    }

    // ログインしているユーザーID
    public function getUid()
    {
        return $this->uid;
    }

    // 承認画面へ表示する総合届DB列数
    public function getIndx()
    {
        return $this->indx;
    }

    // 承認画面へ表示する総合届DB行数
    public function getRows()
    {
        return $this->rows;
    }

    // 承認画面へ表示する総合届DB
    public function getRes()
    {
        return $this->res;
    }

    // 承認経路更新
    public function ApprovalPathUpdate($no, $admit_date, $date, $uid )
    {
        switch ($no) {
        case 31:
            $update_qry = sprintf("UPDATE approval_path SET kakarityo_date='%s' WHERE date='%s' AND uid='%s'", $admit_date, $date, $uid);
            break;
        case 33:
            $update_qry = sprintf("UPDATE approval_path SET katyo_date='%s' WHERE date='%s' AND uid='%s'", $admit_date, $date, $uid);
            break;
        case 35:
            $update_qry = sprintf("UPDATE approval_path SET butyo_date='%s' WHERE date='%s' AND uid='%s'", $admit_date, $date, $uid);
            break;
        case 37:
            $update_qry = sprintf("UPDATE approval_path SET somukatyo_date='%s' WHERE date='%s' AND uid='%s'", $admit_date, $date, $uid);
            break;
        case 39:
            $update_qry = sprintf("UPDATE approval_path SET kanributyo_date='%s' WHERE date='%s' AND uid='%s'", $admit_date, $date, $uid);
            break;
        case 41:
            $update_qry = sprintf("UPDATE approval_path SET kojyotyo_date='%s' WHERE date='%s' AND uid='%s'", $admit_date, $date, $uid);
            break;
        }

//      $_SESSION['s_sysmsg'] .= $update_qry;
        if( query_affected($update_qry) <= 0 ) {
            $_SESSION['s_sysmsg'] .= "承認の更新に失敗しました。";
        }
    }

    // 受電者登録
    public function JyudenUpdate($request)
    {
        $last_indx = $request->get('indx');
        $last_rows = $request->get('rows');

        if( $last_rows == '' ) return; // 初回は何もしない。

        // 総合届の枚数分ループ
        for( $r=0; $r<$last_rows; $r++ ) {
            $state = $request->get('jyu_register' . $r);
            if( $state != 'ok' ) continue;  // 受電者登録以外は飛ばす。

            // 受電者登録に必要なデータを収集。
            $res = $request->get(sprintf("res-%s", $r));
            $date                   = $res[0];                          // 申請年月日
            $uid                    = $res[1];                          // 申請者 社員番号

            $received_phone_date    = $request->get('jyu_date' . $r);   // 受電者（日時）
            $received_phone_name    = $request->get('outai' . $r);      // 受電者（応対者）
            if( is_numeric(trim($received_phone_name)) ) {
                $received_phone_name = $this->getSyainName($received_phone_name);
            }

            $update_qry = sprintf("UPDATE sougou_deteils SET received_phone='受電者', received_phone_date='%s', received_phone_name='%s' WHERE date='%s' AND uid='%s'", $received_phone_date, $received_phone_name, $date, $uid);
//            $_SESSION['s_sysmsg'] .= "{$update_qry}";
            if( query_affected($update_qry) <= 0 ) {
                $_SESSION['s_sysmsg'] .= "受電者の登録に失敗しました。";
            }
        }
    }

    // 承認状態の更新と否認時メール送信
    public function admitUpdate($request)
    {
        $last_indx = $request->get('indx');
        $last_rows = $request->get('rows');

        if( $last_rows == '' ) return; // 初回は何もしない。

        $next_mail = array();
        // 配列を復元
        $last_res = array();
        for( $r=0; $r<$last_rows; $r++ ) {
            $state = $request->get(15000+$r);       // 承認 or 否認
            if( $state != '' ) {
                $res = $request->get(sprintf("res-%s", $r));
                for( $i=0; $i<$last_indx; $i++ ) {
                    $last_res[$r][$i] = $res[$i];
                }

                $date = $request->get(20000+$r);    // 日付
                // 承認経路テーブル更新
                for( $i=31; $i<$last_indx; $i++ ) {
                    if( !strstr($last_res[$r][$i], $this->uid) ) continue;

                    $this->ApprovalPathUpdate($i, $date, $last_res[$r][0], $last_res[$r][1] );
                    $last_res[$r][$i+1] = $date;    // 名前・承認日チェックがある為、値入れとく。

                    if( $state == '承認' ) {
                        $next_admit = 'END';    // 次の承認者コードをセット
                        // 申請者が課長代理、課長、部長代理、部長 のとき 承認者が 工場長 なら
                        if( ($this->IsKatyou( $last_res[$r][1] ) || $this->IsButyou($last_res[$r][1])) && $this->IsKoujyoutyou() ) {
                            $i = 31; // 承認者テーブルの先頭から検索再開
                        }

                        // 次の承認者を探す。
                        for( $n=$i+2; $n<$last_indx; $n=$n+2 ) {
                            $next_admit = 'END';    // 次の承認者コードをセット
                            // 承認者コードあり・承認日なしの人を次の承認者へセット
                            if( is_numeric(($last_res[$r][$n])) && $last_res[$r][$n+1] == "" ) {
                                // 管理部長承認時、申請者が管理部長なら、承認日セット
                                if( $last_res[$r][$n] == $last_res[$r][1] ) {
                                    $this->ApprovalPathUpdate($n, $date, $last_res[$r][0], $last_res[$r][1] );
                                    continue;
                                }

                                $next_admit = $last_res[$r][$n];    // 次の承認者
                                $next_no = $n;                      // その場所
                                // 既に承認しているなら承認日セット
                                for( $x=33; $x<$next_no+1; $x=$x+2 ) {
                                    if( $next_admit == $last_res[$r][$x] && $last_res[$r][$x+1] != ""  ) {
                                        $this->ApprovalPathUpdate($next_no, $date, $last_res[$r][0], $last_res[$r][1] );
                                        break;
                                    }
                                }
                                if( $x>$next_no+1) break;
                            }
                        }

                        // 申請者が課長、課長代理 のとき 工場長 未承認なら
                        if( $this->IsKatyou($last_res[$r][1]) && $last_res[$r][42] == "" ) {
                            $next_admit = $last_res[$r][41];    // 次の承認者を工場長へ
                        }
                    } else {
                        $next_admit = 'DENY'; // 否認
                        $reason = $request->get(10000+$r);  // 理由

                        // 選択された人へ、否認メールを送信
                        if( $request->get(70000+$r."_sinsei") ) {
                            $this->DenyMaile(trim($last_res[$r][1]), $last_res, $r, $reason);
                        }
                        if( $request->get(70000+$r."_kakari") ) {
                            $this->DenyMaile(trim($last_res[$r][31]), $last_res, $r, $reason);
                        }
                        if( $request->get(70000+$r."_katyo") ) {
                            $this->DenyMaile(trim($last_res[$r][33]), $last_res, $r, $reason);
                        }
                        if( $request->get(70000+$r."_butyo") ) {
                            $this->DenyMaile(trim($last_res[$r][35]), $last_res, $r, $reason);
                        }
                        if( $request->get(70000+$r."_soumu") ) {
                            $this->DenyMaile(trim($last_res[$r][37]), $last_res, $r, $reason);
                        }
                        if( $request->get(70000+$r."_kanri") ) {
                            $this->DenyMaile(trim($last_res[$r][39]), $last_res, $r, $reason);
                        }
                        if( $request->get(70000+$r."_kojyo") ) {
                            $this->DenyMaile(trim($last_res[$r][41]), $last_res, $r, $reason);
                        }

                        $insert_qry = "
                            INSERT INTO admit_stop_reason (date, uid, state, reason) VALUES
                                ('{$last_res[$r][0]}', '{$last_res[$r][1]}', '$next_admit', '$reason');
                        ";
                        if( query_affected($insert_qry) <= 0 ) {
                            $_SESSION['s_sysmsg'] .= "否認理由の登録に失敗しました。";
                        }
                    }

                    $update_qry = sprintf("UPDATE sougou_deteils SET admit_status = '%s' WHERE date='%s' AND uid='%s'", $next_admit, $last_res[$r][0], $last_res[$r][1]);
//                    $_SESSION['s_sysmsg'] .= $update_qry;

                    if( query_affected($update_qry) <= 0 ) {
                        $_SESSION['s_sysmsg'] .= "次の承認者へ更新に失敗しました。";
                    }

                    if( is_numeric($next_admit) && $last_res[$r][24] == "至急" ) {
                        $this->hurryMaile( $next_admit );
                    }
                    break;
                } // 承認経路テーブル更新

                // 次の承認者へ通知する準備
                if( $request->get('next') != '' && is_numeric($next_admit) ) {
                    if( ! in_array($next_admit, $next_mail) ) {
                        array_push( $next_mail, $next_admit );
                    }
                }
            }
        }
        // 次の承認者へ通知する準備
        $max = count($next_mail);
        for( $r=0; $r<$max; $r++ ) {
//            $_SESSION['s_sysmsg'] .= "[" . $next_mail[$r] . "]";
            $this->nextMaile( $next_admit );
        }

        return ;
    }

    // 承認画面へ表示する総合届DB取得
    public function getViewDataList(&$result)
    {
        ///// 常に $partsKey フィールドでの検索
        $query = "
            SELECT      *
            FROM        sougou_deteils AS sd
            LEFT JOIN   approval_path AS ap
            ON          sd.uid = ap.uid AND sd.date = ap.date
            WHERE       sd.admit_status = '{$this->uid}'
            ORDER BY    sd.hurry ASC, sd.date ASC, ap.uid ASC
        ";
        $res = $field = array();
        $rows = getResultWithField2( $query, $field, $res );
        if ( $rows <= 0 ) {
            $this->admit = 0;
//            $_SESSION['s_sysmsg'] = '登録がありません！';
        }

        $this->indx = count($field);
        $this->rows = $rows;
        $this->res = $res;

        return $rows;
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

    // 社員の名前を取得
    public function getSyainName($uid)
    {
        if( $uid == '------' ) return $uid;
        $uid = sprintf('%06d', $uid);
        $query = "
            SELECT      name
            FROM        user_detailes
            WHERE       uid = '$uid'
        ";
        $res = array();
        if ( $this->getResult2($query, $res) <= 0 ) {
            return $uid;
        }
        return $res[0][0];
    }

    // 申請された総合届に対する承認ルートを取得
    public function getAdmit(&$request, $date, $uid)
    {
        $query = "
            SELECT      *
            FROM        approval_path
            WHERE       date = '$date' AND uid = '$uid'
        ";
        $res = array();
        if ( $this->getResult2($query, $res) <= 0 ) {
            return '';
        }
        $request->add("kakarityo", $res[0][3]);
        if($res[0][4]=='') $res[0][4] = "------";
        $request->add("kakarityo_date", $res[0][4]);
        $request->add("katyo", $res[0][5]);
        if($res[0][6]=='') $res[0][6] = "------";
        $request->add("katyo_date", $res[0][6]);
        $request->add("butyo", $res[0][7]);
        if($res[0][8]=='') $res[0][8] = "------";
        $request->add("butyo_date", $res[0][8]);
        $request->add("somukatyo", $res[0][9]);
        if($res[0][10]=='') $res[0][10] = "------";
        $request->add("somukatyo_date", $res[0][10]);
        $request->add("kanributyo", $res[0][11]);
        if($res[0][12]=='') $res[0][12] = "------";
        $request->add("kanributyo_date", $res[0][12]);
        $request->add("kojyotyo", $res[0][13]);
        if($res[0][14]=='') $res[0][14] = "------";
        $request->add("kojyotyo_date", $res[0][14]);
        return $res;
    }

    // 至急時メール送信
    public function nextMaile($admit_uid)
    {
        $query_m = "SELECT trim(name), trim(mailaddr)
                        FROM
                            user_detailes
                        LEFT OUTER JOIN
                            user_master USING(uid)
                        ";

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
            // タイトル
            $attenSubject = "宛先： {$sendna} 様 総合届（承認）よりお知らせ";
            // メッセージ
            $name = trim($this->getSyainName($this->getUid()));
            $message  = "{$name} 様が承認を行いました。\n";
            $message .= "承認待ちの総合届がありますので\n";
            $message .= "確認してください。\n\n";
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
            //$message  = "{$admit_uid} 様 至急の総合届があります。\n\n";
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

    // 否認時メール送信
    public function DenyMaile($send_uid, $res, $r, $reason)
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
        $search_m = "WHERE uid='$send_uid'";

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
            $attenSubject = "宛先： {$sendna} 様 総合届【否認】のお知らせ";
            $syozoku = trim($this->getSyozoku($res[$r][1]));
            $name = trim($this->getSyainName($res[$r][1]));
            $deny_name = trim($this->getSyainName($this->uid));
            //テスト用 メッセージ
            //$message  = "{$send_uid} 様へ {$deny_name} 様により、以下の総合届が否認されました。\n\n";
            //テスト用 下に変更すること
            $message  = "*** {$name} 様 が共有メールの場合、上長よりお知らせ下さい。***\n\n";
            $message .= "{$deny_name} 様により、以下の総合届が否認されました。\n\n";
            $message .= "{$syozoku} {$name}\n";         // 所属 氏名
            $message .= "{$res[$r][2]}";                // 取得日
            if( $res[$r][2] != $res[$r][4] ) {
                $message .= " 〜 {$res[$r][4]}";        // 取得期間
            }
            $message .= "\n{$res[$r][6]}\n\n";          // 取得内容
            $message .= "【否認理由】{$reason}\n\n";    // 否認理由
            $res[$r][0] = str_replace(' ','@', $res[$r][0]);
            if( $send_uid == $res[$r][1] ) {
                $message .= "再申請は、こちら →→→ ";
                $message .= "http://masterst/per_appli/in_sougou/sougou_Main.php?calUid={$send_uid}&showMenu=Re&date={$res[$r][0]}&syainbangou={$send_uid}&deny_uid={$this->uid}\n\n";
                $message .= "申請の取消は、こちら → ";
                $message .= "http://masterst/per_appli/in_sougou/sougou_Main.php?calUid={$send_uid}&showMenu=Del&date={$res[$r][0]}&syainbangou={$send_uid}&deny_uid={$this->uid}\n\n";
            }
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

    // 有休取得数（実績）
    public function KeikakuCnt()
    {
        $this->yukyu[0][0]; // 当期有給日数
        $this->yukyu[0][1]; // 当期有給残
        $this->yukyu[0][2]; // 半日有給回数
        $this->yukyu[0][3]; // 時間休取得分
        $this->yukyu[0][4]; // 時間有給限度
        
        if( $this->yukyu[0][3] == 0 ) {
            $jisseki = $this->yukyu[0][0] - ($this->yukyu[0][1] );
        }else {
            $jisseki = $this->yukyu[0][0] - ($this->yukyu[0][1] + (round($this->yukyu[0][3]/($this->yukyu[0][4]/5), 3)) );
        }

        return $jisseki;
    }

    // 予定有給（承認用）
    public function YoteiKyuka($uid, $sin_date, $start_date, $end_date, $half)
    {
        if( $sin_date == "" ) return;

        $cnt = 0;
        if( $half ) {
            $s_day = substr($sin_date,0,4) . "-03-31";
            $e_day = substr($sin_date,0,4) . "-10-01";
        } else {
            $s_day = substr($sin_date,0,4)+1 . "-09-30";
            $e_day = substr($sin_date,0,4)+1 . "-04-01";
        }

        // 予定有給情報取得
        $query = "
                SELECT  start_date      AS 開始日,      -- 0
                        end_date        AS 終了日,      -- 1
                        content         AS 項目         -- 2
                FROM    sougou_deteils
                WHERE   (start_date>'{$sin_date}' OR end_date>'{$sin_date}')
                    AND (start_date!='{$start_date}' AND end_date!='{$end_date}')
                    AND start_date>'{$s_day}' AND end_date<'{$e_day}'
                    AND uid='$uid'
                    AND (content='有給休暇' OR content='AM半日有給休暇' OR content='PM半日有給休暇' )
                    AND (admit_status != 'CANCEL' AND admit_status != 'DENY')
            ";
        if( ($rows = getResult2($query, $res)) <= 0 ) {
//            $_SESSION['s_sysmsg'] .= $sin_date . ' : 予定なし';
            return 0;
        }

        // 有給残へ予定有給の加算処理
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

    // 有給残情報を取得
    public function getYukyu()
    {
        return $this->yukyu;
    }

    // 有給残情報計算・・・社員メニュー view_mineinfo.php ファイル内より引用
    public function setYukyu($uid)
    {
        $timeDate = date('Ym');
        $today_ym = date('Ymd');
        $tmp = $timeDate - 195603;     // 期計算係数195603
        $tmp = $tmp / 100;             // 年の部分を取り出す
        $ki  = ceil($tmp);             // roundup と同じ
        $query = "
                SELECT
                     current_day    AS 当期有給日数     -- 0
                    ,holiday_rest   AS 当期有給残       -- 1
                    ,half_holiday   AS 半日有給回数     -- 2
                    ,time_holiday   AS 時間休取得分     -- 3
                    ,time_limit     AS 時間有給限度     -- 4
                    ,web_ymd        AS 更新年月日       -- 5
                FROM holiday_rest_master
                WHERE uid='{$uid}' and ki<={$ki}
                ORDER BY ki DESC LIMIT 1
            ";
        if( getResult2($query, $this->yukyu) <= 0 ) {
            $this->yukyu = array(array(0,0,0,0,0));
            return false;
        }
        return true;
    }

    // 有給残へ予定有給加算
    public function setYotei($sin_date, $uid)
    {
        // 予定有給情報取得
        $query = "
                SELECT  start_date      AS 開始日,      -- 0
                        end_date        AS 終了日,      -- 1
                        start_time      AS 開始時間,    -- 2
                        end_time        AS 終了時間,    -- 3
                        content         AS 項目         -- 4
                FROM    sougou_deteils
                WHERE   (start_date>'{$sin_date}' OR end_date>'{$sin_date}')
                    AND uid='{$uid}'
                    AND (content='有給休暇' OR content='AM半日有給休暇' OR content='PM半日有給休暇' OR content='時間単位有給休暇' )
                    AND (admit_status != 'CANCEL' AND admit_status != 'DENY')
            ";
        if( ($rows = getResult2($query, $res)) <= 0 ) {
//            $_SESSION['s_sysmsg'] .= $sin_date . ' : 予定なし';
            return;
        }

        // 有給残へ予定有給の加算処理
        $timecount = false;     // 時間休フラグ初期化
        for( $i=0; $i<$rows; $i++ ) {
            if( strtotime($res[$i][0]) <= strtotime($sin_date) ) {  // 開始日と申請日を比較
                // 開始日と申請日を比較し、申請日以前の場合。開始日に申請日の次の日をセット
                $res[$i][0] = date('Ymd', strtotime($sin_date . ' 1 day'));
            }
            $day = $this->getDayCount($res[$i][0], $res[$i][1]);    // 営業日内の取得日数
            if( trim($res[$i][4]) == '有給休暇' ) {
                $this->yukyu[0][1] -= $day;         // 当期有給残
            } else if( trim($res[$i][4]) == 'AM半日有給休暇' || trim($res[$i][4]) == 'PM半日有給休暇' ) {
                $this->yukyu[0][1] -= (0.5 * $day); // 当期有給残
                $this->yukyu[0][2] += $day;         // 半日有給回数
            } else if( trim($res[$i][4]) == '時間単位有給休暇' ) {
                if( !$timecount ) {
                    // 当期有給残へ既に取得している時間休を加算する。
                    // ※そのまま有給残より減算するとおかしな値になってしまうため。
                    $this->yukyu[0][1] += round($this->yukyu[0][3]/($this->yukyu[0][4]/5), 3);
                    $timecount = true;  // 時間休フラグセット
                }
                $this->yukyu[0][3] += ($this->getTimeCount($res[$i][2], $res[$i][3]) * $day);    // 時間休加算
            }
        }
        if( $timecount ) {
            // 当期有給残より時間休減算
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

    // 編集する総合届のデータを復元
    public function getEditData($request)
    {
        $last_indx = $request->get('indx');
        $last_rows = $request->get('rows');

        if( $last_rows == '' ) return; // 初回は何もしない。

        // 配列を復元
        $last_res = array();
        for( $r=0; $r<$last_rows; $r++ ) {
            if( $request->get(90000+$r) != '' ) {
                $request->add('showMenu', 'Edit');
                break;
            }
        }

        if( $r>=$last_rows ) return false;

        $res = $request->get(sprintf("res-%s", $r));
        for( $i=0; $i<$last_indx; $i++ ) {
            $last_res[0][$i] = $res[$i];
        }
        if( $this->setYukyu($last_res[0][1]) ) {
//            $this->setYotei($last_res[0][0], $last_res[0][1]);
        }
        $this->indx = $last_indx;
        $this->rows = $last_rows;
        $this->res = $last_res;
    }

    // 総合届の申請内容をDBへ登録
    public function SougouUpdate($request)
    {
        ///// パラメーターの分割
        $date                   = $request->get("sin_date");        // 申請年月日
        $uid                    = $request->get("syain_no");        // 申請者 社員番号
        $start_date             = $request->get("str_date");        // 期間 開始 日付
        $start_time             = $request->get("str_time");        // 期間 開始 時間
        $end_date               = $request->get("end_date");        // 期間 終了 日付
        $end_time               = $request->get("end_time");        // 期間 終了 時間
        $content                = $request->get('r1');              // 内容（ラジオ1）
        $yukyu                  = $request->get('r2');              // 内容（ラジオ2）有給関連
        $ticket01               = $request->get('r3');              // 内容（ラジオ3）乗車券
        $ticket02               = $request->get('r4');              // 内容（ラジオ4）新幹線
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
        if( is_numeric(trim($received_phone_name)) ) {
            $received_phone_name = $this->getSyainName($received_phone_name);
        }
        if( $received_phone_name) $received_phone = '受電者';       // 応対者がいる時、チェックを受電者にする

        $hurry                  = $request->get('c2');              // 至急（チェック）

        if( $ticket01 == "片道" || $ticket01 == "往復" || $ticket02 == "片道" || $ticket02 == "往復" ) {
//        if( ($ticket01 != "" && $ticket01 != "不要") || ($ticket02 != "" && $ticket02 != "不要") ) {
            $ticket             = true;     // 回数券の有
        } else {
            $ticket             = false;    // 回数券の無
        }
        $approval_status        = 0;        // 承認状況
        $amano_input            = 0;        // アマノ入力の有無

        if( $content == "有給休暇" || $content == "AM半日有給休暇" || $content == "PM半日有給休暇" ||
            $content == "時間単位有給休暇" || $content == "欠勤"  || $content == "遅刻早退" ) {

            $update_qry = sprintf("UPDATE sougou_deteils SET start_date='%s', start_time='%s', end_date='%s', end_time='%s', content='%s', yukyu='%s', special=NULL, others=NULL, place=NULL, purpose=NULL, doukousya=NULL, remarks='%s' WHERE date='%s' AND uid='%s'", $start_date, $start_time, $end_date, $end_time, $content, $yukyu, $remarks, $date, $uid);

        } else if( $content == "出張（日帰り）" || $content == "出張（宿泊）"
            || $content == "直行" || $content == "直帰" || $content == "直行/直帰" ) {
            if(!$ticket02) $ticket02 = "不要";

            if( $content == "直行" ) {
                $update_qry = sprintf("UPDATE sougou_deteils SET start_date='%s', start_time='%s', end_date='%s', end_time=NULL, content='%s', yukyu=NULL, ticket01='%s', ticket02='%s', special=NULL, others='%s', place='%s', purpose='%s', doukousya='%s', remarks='%s', ticket='%b' WHERE date='%s' AND uid='%s'", $start_date, $start_time, $end_date, $content, $ticket01, $ticket02, $others, $place, $purpose, $doukousya, $remarks, $ticket, $date, $uid);
            } else if( $content == "直帰" ) {
                $update_qry = sprintf("UPDATE sougou_deteils SET start_date='%s', start_time=NULL, end_date='%s', end_time='%s', content='%s', yukyu=NULL, ticket01='%s', ticket02='%s', special=NULL, others='%s', place='%s', purpose='%s', doukousya='%s', remarks='%s', ticket='%b' WHERE date='%s' AND uid='%s'", $start_date, $end_date, $end_time, $content, $ticket01, $ticket02, $others, $place, $purpose, $doukousya, $remarks, $ticket, $date, $uid);
            } else {
                $update_qry = sprintf("UPDATE sougou_deteils SET start_date='%s', start_time='%s', end_date='%s', end_time='%s', content='%s', yukyu=NULL, ticket01='%s', ticket02='%s', special=NULL, others='%s', place='%s', purpose='%s', doukousya='%s', remarks='%s', ticket='%b' WHERE date='%s' AND uid='%s'", $start_date, $start_time, $end_date, $end_time, $content, $ticket01, $ticket02, $others, $place, $purpose, $doukousya, $remarks, $ticket, $date, $uid);
            }
        } else if( $content == "特別休暇" ) {
            if( $special != "その他" ) {

                $update_qry = sprintf("UPDATE sougou_deteils SET start_date='%s', start_time='%s', end_date='%s', end_time='%s', content='%s', yukyu=NULL, special='%s', others=NULL, place=NULL, purpose=NULL, doukousya=NULL, remarks='%s' WHERE date='%s' AND uid='%s'", $start_date, $start_time, $end_date, $end_time, $content, $special, $remarks, $date, $uid);

            } else {

                $update_qry = sprintf("UPDATE sougou_deteils SET start_date='%s', start_time='%s', end_date='%s', end_time='%s', content='%s', yukyu=NULL, special='%s', others='%s', place=NULL, purpose=NULL, doukousya=NULL, remarks='%s' WHERE date='%s' AND uid='%s'", $start_date, $start_time, $end_date, $end_time, $content, $special, $others, $remarks, $date, $uid);

            }
        } else if( $content == "振替休日" || $content == "その他" ) {

            $update_qry = sprintf("UPDATE sougou_deteils SET start_date='%s', start_time='%s', end_date='%s', end_time='%s', content='%s', yukyu=NULL, special=NULL, others='%s', place=NULL, purpose=NULL, doukousya=NULL, remarks='%s' WHERE date='%s' AND uid='%s'", $start_date, $start_time, $end_date, $end_time, $content, $others, $remarks, $date, $uid);

        } else if( $content == "IDカード通し忘れ（出勤）" ) {

            $update_qry = sprintf("UPDATE sougou_deteils SET start_date='%s', start_time='%s', end_date='%s', end_time=NULL, content='%s', yukyu=NULL, special=NULL, others=NULL, place=NULL, purpose=NULL, doukousya=NULL, remarks='%s' WHERE date='%s' AND uid='%s'", $start_date, $start_time, $end_date, $content, $remarks, $date, $uid);

        } else if( $content == "IDカード通し忘れ（退勤）" ) {

            $update_qry = sprintf("UPDATE sougou_deteils SET start_date='%s', start_time=NULL, end_date='%s', end_time='%s', content='%s', yukyu=NULL, special=NULL, others=NULL, place=NULL, purpose=NULL, doukousya=NULL, remarks='%s' WHERE date='%s' AND uid='%s'", $start_date, $end_date, $end_time, $content, $remarks, $date, $uid);

        } else if( $content == "時限承認忘れ（残業申告漏れ）" || $content == "IDカード通し忘れ（退勤）＋ 時限承認忘れ（残業申告漏れ）" ) {

            $update_qry = sprintf("UPDATE sougou_deteils SET start_date='%s', start_time='%s', end_date='%s', end_time='%s', content='%s', yukyu=NULL, special=NULL, others=NULL, place=NULL, purpose=NULL, doukousya=NULL, remarks='%s' WHERE date='%s' AND uid='%s'", $start_date, $start_time, $end_date, $end_time, $content, $remarks, $date, $uid);

        } else {

            $update_qry = sprintf("UPDATE sougou_deteils SET start_date='%s', start_time='%s', end_date='%s', end_time='%s', content='%s', yukyu=NULL, special=NULL, others=NULL, place=NULL, purpose=NULL, doukousya=NULL, remarks='%s' WHERE date='%s' AND uid='%s'", $start_date, $start_time, $end_date, $end_time, $content, $remarks, $date, $uid);

        }

        if( query_affected($update_qry) <= 0 ) {
            $_SESSION['s_sysmsg'] = "総合届の更新に失敗しました。" . $update_qry;
            return false;
        }

        if( $ticket ) {
            if( $ticket01 != "不要" && $ticket02 != "不要") {
                $update_qry = sprintf("UPDATE sougou_deteils SET ticket01_set='%s', ticket02_set='%s' WHERE date='%s' AND uid='%s'", $ticket01_set, $ticket02_set, $date, $uid);
            } else if( $ticket01 != "不要" ) {
                $update_qry = sprintf("UPDATE sougou_deteils SET ticket01_set='%s', ticket02_set=NULL WHERE date='%s' AND uid='%s'", $ticket01_set, $date, $uid);
            } else if( $ticket02 != "不要" ) {
                $update_qry = sprintf("UPDATE sougou_deteils SET ticket01_set=NULL, ticket02_set='%s' WHERE date='%s' AND uid='%s'", $ticket02_set, $date, $uid);
            }
        } else {
            if( $place != '' ) {
                $update_qry = sprintf("UPDATE sougou_deteils SET ticket01='不要', ticket02='不要', ticket01_set=NULL, ticket02_set=NULL, ticket='%d' WHERE date='%s' AND uid='%s'", $ticket, $date, $uid);
            } else {
                $update_qry = sprintf("UPDATE sougou_deteils SET ticket01=NULL, ticket02=NULL, ticket01_set=NULL, ticket02_set=NULL, ticket=NULL WHERE date='%s' AND uid='%s'", $date, $uid);
            }
        }
        if( query_affected($update_qry) <= 0 ) {
            $_SESSION['s_sysmsg'] .= "回数券の登録に失敗しました。";
        }

        if( $contact == "その他" ) {
            $update_qry = sprintf("UPDATE sougou_deteils SET contact='%s', contact_other='%s', contact_tel='%s' WHERE date='%s' AND uid='%s'", $contact, $contact_other, $contact_tel, $date, $uid);
        } else if( $contact == "出張先" ) {
            $update_qry = sprintf("UPDATE sougou_deteils SET contact='%s', contact_other=NULL, contact_tel='%s' WHERE date='%s' AND uid='%s'", $contact, $contact_tel, $date, $uid);
        } else {
            $update_qry = sprintf("UPDATE sougou_deteils SET contact='%s', contact_other=NULL, contact_tel=NULL WHERE date='%s' AND uid='%s'", $contact, $date, $uid);
        }
        if( query_affected($update_qry) <= 0 ) {
            $_SESSION['s_sysmsg'] .= "連絡先の登録に失敗しました。";
        }

        if( $received_phone == "受電者" ) {
            $update_qry = sprintf("UPDATE sougou_deteils SET received_phone='%s', received_phone_date='%s', received_phone_name='%s' WHERE date='%s' AND uid='%s'", $received_phone, $received_phone_date, $received_phone_name, $date, $uid);
        } else {
            $update_qry = sprintf("UPDATE sougou_deteils SET received_phone=NULL, received_phone_date=NULL, received_phone_name=NULL WHERE date='%s' AND uid='%s'", $date, $uid);
        }
        if( query_affected($update_qry) <= 0 ) {
            $_SESSION['s_sysmsg'] .= "受電者の登録に失敗しました。";
        }

        if( $hurry == "至急" ) {
            $update_qry = sprintf("UPDATE sougou_deteils SET hurry='%s' WHERE date='%s' AND uid='%s'", $hurry, $date, $uid);
        } else {
            $update_qry = sprintf("UPDATE sougou_deteils SET hurry=NULL WHERE date='%s' AND uid='%s'", $date, $uid);
        }
        if( query_affected($update_qry) <= 0 ) {
            $_SESSION['s_sysmsg'] .= "至急の登録に失敗しました。";
        }
    }

    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/

    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/

} // Class Sougou_Admit_Model End

?>
