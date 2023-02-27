<?php
////////////////////////////////////////////////////////////////////////////////
// 機械稼働管理指示メンテナンス                                               //
//                                                              MVC Model 部  //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/03/24 Created monitoring_Model.php                                    //
// 2021/03/24 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード

require_once ('../../ComTableMntClass.php');   // TNK 全共通 テーブルメンテ&ページ制御Class

require_once ('../equip_function.php');        // 設備メニュー 共通 function (function.phpを含む)

/******************************************************************************
*          総合届（申請）用 MVCのModel部 base class 基底クラスの定義          *
******************************************************************************/
class Monitoring_Model extends ComTableMnt
{
    ///// Private properties
    private $plan_no;
    private $parts_no   = '--------';
    private $parts_name = '- - - - - - - -';
    private $plan       = '-,---,---';
    private $dead_lines = '';
    private $run_state  = '[停止]';
    private $run_time   = '--：--：-- ';
    private $state      = '';
    private $header_info= 'none';
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer の定義 (php5へ移行時は __construct() へ変更予定) (デストラクタ__destruct())
    public function __construct($request)
    {
        // 以下のリクエストはcontrollerより先に取得しているため空の場合がある。
        $this->plan_no = $request->get('plan_no');

        if( $this->plan_no == '--------' ) {
            $this->plan_no = '';
            return;    // キーフィールドが設定されていなければ何もしない
        } else {
            ;
        }

//        $sql_sum = "SELECT count(*) FROM user_detailes where uid like '%{$syainbangou}'";

        ///// Constructer を定義すると 基底クラスの Constructerが実行されない
        ///// 基底ClassのConstructerはプログラマーの責任で呼出す
//        parent::__construct($sql_sum, $request, 'monitoring.log');
    }

    // 計画番号はありますか？
    public function IsPlanNo()
    {
        if( $this->plan_no == '' ) {
            return false;
        } else {
            return true;
        }
    }

    // セットされている計画番号を返す。
    public function GetPlanNo()
    {
        return $this->plan_no;
    }

    // セットされている製品番号を返す。
    public function GetPartsNo()
    {
        return $this->parts_no;
    }

    // セットされている製品名を返す。
    public function GetPartsName()
    {
        return $this->parts_name;
    }

    // セットされている計画数を返す。
    public function GetPlan()
    {
        return $this->plan;
    }

    // セットされている完了日を納期に変換し返す。
    public function GetDeadLines()
    {
//        return $this->dead_lines;
        return ltrim(substr($this->dead_lines,4,2),0) . "月 " . ltrim(substr($this->dead_lines,6,2),0) . "日";
    }

    // セットされている生産数を返す。
    public function GetProNum($plan_no,$mac_no)
    {
        $query = "
                    SELECT  work_cnt
                    FROM    equip_work_log2_moni
                    WHERE   plan_no='{$plan_no}' and mac_no={$mac_no}
                    ORDER BY date_time DESC LIMIT 1
                 ";
        $res = array();
        if ( ($rows=getResult($query, $res)) < 0) {
            return 0;
        } else if( $rows == 0 ) {
            return 0;
        }
        $pro_num = $res[0][0];
        return $pro_num;
    }

    // セットされている稼働状況を返す。
    public function GetRunState($plan_no,$mac_no, &$bg_color, &$txt_color)
    {
        $koutei=1;
        $query = "select to_char(date_time AT TIME ZONE 'JST', 'YYYY/MM/DD') as date
                    ,to_char(date_time AT TIME ZONE 'JST', 'HH24:MI:SS') as time
                    ,mac_state
                    ,work_cnt
                from
                    equip_work_log2_moni
                where
                    plan_no='{$plan_no}' and mac_no={$mac_no} and koutei={$koutei}
                ORDER BY date_time DESC LIMIT 1
        ";
        /*
        $query = "select to_char(date_time AT TIME ZONE 'JST', 'YYYY/MM/DD') as date
                    ,to_char(date_time AT TIME ZONE 'JST', 'HH24:MI:SS') as time
                    ,mac_state
                    ,work_cnt
                from
                    equip_work_log2_moni
                where
                    equip_index_moni(mac_no, plan_no, koutei, date_time) >= '{$mac_no}{$plan_no}{$koutei}00000000000000'
                and
                    equip_index_moni(mac_no, plan_no, koutei, date_time) <= '{$mac_no}{$plan_no}{$koutei}99999999999999'
                order by
                    equip_index_moni(mac_no, plan_no, koutei, date_time) DESC
                limit 1
        ";
        */
        $res = array();
        if ( ($rows=getResult2($query, $res)) <= 0) {
            $mac_state_txt = '---';
        } else {
            $mac_state_txt = equip_machine_state($mac_no, $res[0][2], $bg_color, $txt_color);
        }
        return $mac_state_txt;
    }

    // セットされている稼働時間を返す。
    public function GetRunTime($plan_no,$mac_no)
    {
        $koutei=1;
        $query = "select date_time
                from
                    equip_work_log2_moni
                where
                    plan_no='{$plan_no}' and mac_no={$mac_no} and koutei={$koutei}
                order by
                    date_time DESC
                limit 1
        ";
        /*
        $query = "select date_time
                from
                    equip_work_log2_moni
                where
                    equip_index_moni(mac_no, plan_no, koutei, date_time) >= '{$mac_no}{$plan_no}{$koutei}00000000000000'
                and
                    equip_index_moni(mac_no, plan_no, koutei, date_time) <= '{$mac_no}{$plan_no}{$koutei}99999999999999'
                order by
                    equip_index_moni(mac_no, plan_no, koutei, date_time) DESC
                limit 1
        ";
        */
        $res = array();
        if ( ($rows=getResult2($query, $res)) <= 0) {
            $run_time = '--：--：-- ';
        } else {
            $query2 = "select date_time
                from
                    equip_work_log2_moni
                where
                    plan_no='{$plan_no}' and mac_no={$mac_no} and koutei={$koutei}
                order by
                    date_time ASC
                limit 1
            ";
            /*
            $query2 = "select date_time
                from
                    equip_work_log2_moni
                where
                    equip_index_moni(mac_no, plan_no, koutei, date_time) >= '{$mac_no}{$plan_no}{$koutei}00000000000000'
                and
                    equip_index_moni(mac_no, plan_no, koutei, date_time) <= '{$mac_no}{$plan_no}{$koutei}99999999999999'
                order by
                    equip_index_moni(mac_no, plan_no, koutei, date_time) ASC
                limit 1
            ";
            */
            $res2 = array();
            if ( ($rows2=getResult2($query2, $res2)) <= 0) {
                $run_time = '--：--：-- ';
            } else {
                $timestamp  = strtotime($res[0][0]);
                $timestamp2 = strtotime($res2[0][0]);
                $time_temp  = $timestamp - $timestamp2;
                $hours      = floor($time_temp / 3600);
                $minutes    = floor(($time_temp / 60) % 60);
                $seconds    = $time_temp % 60;
//                $run_time = $hours . ':' . $minutes . ':' . $seconds;
                $run_time = sprintf("%d:%02d:%02d", $hours, $minutes, $seconds);
            }
        }
        return $run_time;
    }

    // 表示するキャプションを返す。
    public function GetCaption($mode='')
    {
        if( $mode == 'select' ) {
            return "初めに、運転を開始する機械を選択して下さい。";
        }
        
        if( $this->IsPlanNo() ) {
            return "※ 表示内容を確認して下さい。";
        } else {
            return "【計画番号】を入力して下さい。";
        }
    }

    // クリックされたボタン情報を返す。(start/reset/break/restart/delete/end)
    public function GetState()
    {
        return $this->state;
    }

    // セットされているヘッダー情報を返す。(none/run/break/end)
    public function GetHeaderInfo()
    {
        return $this->header_info;
    }

    // 機械($m_no)で機械名を返す
    public function GetMacName($m_no)
    {
        $query = "
                    SELECT  mac_name     AS 機械名 -- 00
                    FROM    equip_machine_master2
                    WHERE   mac_no='{$m_no}'
                 ";
        $res = array();
        if ( getUniResult($query, $res) <= 0) {
            return '--------';
        }
        return $res;
    }

    // 工場($factory)の機械マスター情報を返す
    function GetFactoryMachineInfo(&$res, $factory)
    {
        $query = "
                    SELECT   mac_no     AS 機械番号 -- 00
                            ,mac_name   AS 機械名   -- 01
                            ,survey     AS 監視     -- 02
                    FROM    equip_machine_master2
                    WHERE   factory={$factory}
                    ORDER BY mac_no
                 ";
        $res = array();
        if ( ($rows=getResult($query, $res)) < 0) {
            $_SESSION['s_sysmsg'] = '機械マスターの取得に失敗。(equip_machine_master2)';
        } else if( $rows == 0 ) {
            echo "まず、<font style='color:Red;'>【機械マスターの保守】</font>より機械を登録して下さい。";
        }
        return $rows;
    }

    // 機械($m_no)で稼働中の計画番号を返す
    public function GetRunningPlanNo($m_no)
    {
        $query = "
                    SELECT  plan_no     AS 計画番号 -- 00
                    FROM    equip_work_log2_header_moni
                    WHERE   mac_no='{$m_no}' AND work_flg='t'
                 ";
        $res = array();
        if ( getUniResult($query, $res) <= 0) {
            return '--------';
        }
        return $res;
    }

    // 表示
    public function GetViewDate($request)
    {
        if( $this->SetDispPlanData() ) {    // 計画番号の情報取得
            $m_no = $request->get('m_no');
            $plan_no = $this->GetPlanNo();
            $parts_no = $this->GetPartsNo();
            $koutei_no = 1; // とりあえず、1 入れる。
            $plan = $this->GetPlan();
            $jisseki = $plan; // 計画数を入れる。
        } else {
            return; // 指定された計画番号の情報取得できなかった。
        }

        $this->state = $request->get('state');

        switch( $this->state ) {
            case 'delete':         // 削除
//                $_SESSION['s_sysmsg'] .= '【テストＭＳＧ：削除 処理。 ※工程（1）は仮】';
                $this->HeaderInfoDel($m_no, $plan_no, $koutei_no);
                $this->plan_no = '';
                break;
            case 'reset':       // リセット
//                $_SESSION['s_sysmsg'] .= '【テストＭＳＧ：リセット→計画番号空→読込みから】';
                $this->plan_no = '';
                break;
            case 'end':         // 完了
//                $_SESSION['s_sysmsg'] .= '【テストＭＳＧ：完了 処理。 ※工程（1）/実績（10）は仮】';
                $this->HeaderInfoEnd($m_no, $plan_no, $parts_no, $koutei_no, $jisseki);
                $this->state = 'start';
//                $this->plan_no = '';
                break;
            case 'break':       // 中断
//                $_SESSION['s_sysmsg'] .= '【テストＭＳＧ：中断 処理 予定】';
                $this->HeaderInfoBreak($m_no, $plan_no, $koutei_no, false);
                $this->state = 'start';
                $this->header_info = 'break';
//                $this->plan_no = '';
                break;
            case 'restart':     // 再開
//                $_SESSION['s_sysmsg'] .= '【テストＭＳＧ：再開 処理 予定 →】';
                $query = "select mac_no from equip_work_log2_header_moni where mac_no='$m_no' and work_flg is TRUE and end_timestamp is NULL";
                $res = array();
                if(($rows=getResult($query, $res)) >= 1) {    // ヘッダーに既にないかチェック
                    $_SESSION['s_sysmsg'] = "<font color='yellow'>機械番号 = $m_no は、現在稼動中です!!</font>";
                } else {
                    $this->HeaderInfoBreak($m_no, $plan_no, $koutei_no, true);
                }
            case 'plan_load':   // 読込み
            case 'start':       // 開始
                $this->header_info = $this->LoadHeaderInfo($m_no, $plan_no, $koutei_no);
                if( $this->header_info == 'none' ) {
                    if( $this->state == 'plan_load' ) {
//                        $_SESSION['s_sysmsg'] .= '【テストＭＳＧ：読込み】';
                        break;
                    }
                    if( $this->HeaderInfoAdd($m_no, $plan_no, $parts_no, $koutei_no, $plan) ) {
//                        $_SESSION['s_sysmsg'] .= '【テストＭＳＧ：運転開始！】';
                        $this->header_info = 'run';
                    }else{
                        $_SESSION['s_sysmsg'] .= '運転開始情報の作成に失敗(equip_work_log2_header_moni)';
                    }
                    break;
                } else if( $this->header_info == 'run' ) {
//                    $_SESSION['s_sysmsg'] .= '【テストＭＳＧ：稼働中】';
                } else if( $this->header_info == 'break' ) {
                    $_SESSION['s_sysmsg'] .= '中断計画にあります。';
                } else if( $this->header_info == 'end' ) {
                    $_SESSION['s_sysmsg'] .= '既に、完了されています。';
                }
                $this->state = 'start';
                break;
            default:    // 稼働中の場合
//                $_SESSION['s_sysmsg'] .= '【テストＭＳＧ：稼働中 情報 取得】';
                $this->header_info = $this->LoadHeaderInfo($m_no, $plan_no, $koutei_no);
                $this->state = 'start';
                break;
        }
    }

    // 表示する計画番号の情報をセット
    public function SetDispPlanData()
    {
        // 計画番号から製品情報の取得
        $query = "
                    SELECT          parts_no                AS 製品番号     -- 00
                                    ,substr(midsc, 1, 20)   AS 製品名       -- 01
                                    ,plan-cut_plan          AS 計画数       -- 02
                                    ,kanryou                AS 完了日       -- 03
                    FROM            assembly_schedule
                    LEFT OUTER JOIN miitem ON (parts_no=mipn)
                    WHERE plan_no='{$this->GetPlanNo()}'
                 ";
        $res = array();
        if ($this->getResult2($query, $res) <= 0) {
            $_SESSION['s_sysmsg'] = '計画番号【'.$this->GetPlanNo().'】の製品情報を取得できません。';
            $this->plan_no      = '';
            return false;
        } else {
            $this->parts_no     = $res[0][0];
            $this->parts_name   = $res[0][1];
            $this->plan         = $res[0][2];
            $this->dead_lines   = $res[0][3];   // 納期
            return true;
        }
    }

    // DBよりヘッダー情報を読み込みステータスを返す。(none/run/break/end)
    public function LoadHeaderInfo($m_no, $plan_no, $koutei_no)
    {
        $query = "
                    SELECT  end_timestamp, work_flg
                    FROM    equip_work_log2_header_moni
                    WHERE   mac_no=$m_no AND plan_no='$plan_no' AND koutei=$koutei_no
                 ";
        $res = array();
        if( getResult($query, $res) <= 0 ) {
            return 'none';  // まだ存在しない。
        }
        if( $res[0][0] == '' && $res[0][1] == 't' ) {
            return 'run';   // 稼働中
        } else if( $res[0][0] == '' && $res[0][1] == 'f' ) {
            return 'break'; // 中断中
        } else {
            return 'end';   // 完了
        }
    }

    // 運転開始情報を作成
    public function HeaderInfoAdd($m_no, $plan_no, $parts_no, $koutei_no, $plan)
    {
        $str_timestamp = date('Y-m-d H:i:s');
        $insert_qry = "insert into equip_work_log2_header_moni (mac_no, plan_no, parts_no, koutei, plan_cnt, str_timestamp, work_flg) 
                values($m_no, '$plan_no', '$parts_no', $koutei_no, $plan, '$str_timestamp', TRUE)";
        if (funcConnect()) {
            execQuery('begin');
            if (execQuery($insert_qry)>=0) {
                execQuery('commit');
                disConnectDB();
                return true;
            } else {
                execQuery('rollback');
                disConnectDB();
                $error_msg  = date('Y/m/d H:i:s', mktime());
                $error_msg .= "-execQuery: $insert_qry";
                `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
            }
        } else {
            $error_msg  = date('Y/m/d H:i:s', mktime());
            $error_msg .= "-funcConnect: $insert_qry";
            `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
        }
        return false;
    }

    // 製造課の機械運転 完了指示 equip_header のend_timestampに完了時間書込み work_flg を FALSE へ
    public function HeaderInfoEnd($m_no, $plan_no, $parts_no, $koutei_no, $jisseki)
    {
        $query = "select mac_no,plan_no,parts_no,koutei from equip_work_log2_header_moni where work_flg=TRUE 
                and mac_no='$m_no' and plan_no='$plan_no' and parts_no='$parts_no' and koutei='$koutei_no'";
        $res = array();
        if( getResult($query, $res) >= 1) {         // データベースのヘッダーより運転中のデータをチェック
            ; // OK
        } else {
            $_SESSION['s_sysmsg'] = "機械番号:$m_no 計画番号:$plan_no 部品番号:$parts_no 工程:$koutei_no では登録されていません";
            return; // NG
        }

        $end_timestamp = date('Y-m-d H:i:s');
        $update_qry = "update equip_work_log2_header_moni set end_timestamp='$end_timestamp', work_flg=FALSE, jisseki={$jisseki}
                       where mac_no={$m_no} and plan_no='{$plan_no}' and koutei={$koutei_no}"; 
        if (funcConnect()) {
            execQuery('begin');
            if (execQuery($update_qry) >= 0) {
                execQuery('commit');
                disConnectDB();
            } else {
                execQuery('rollback');
                disConnectDB();
                $error_msg = date('Y/m/d H:i:s', mktime());
                $error_msg .= "-execQuery:完了:$update_qry";
                `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
            }
        } else {
            $error_msg = date('Y/m/d H:i:s', mktime());
            $error_msg .= "-funcConnect:完了:$update_qry";
            `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
        }
    }

    // 製造課の機械運転 中断/再開時の ヘッダーファイル処理 work_flg IS FALSE(中断) TRUE(再開)
    public function HeaderInfoBreak($m_no, $plan_no, $koutei_no, $flag)
    {
        if( $flag ) {
//          $_SESSION['s_sysmsg'] .= '【テストＭＳＧ：** 再開 **】';
            $update_qry = "update equip_work_log2_header_moni set work_flg=TRUE
                           where mac_no={$m_no} and plan_no='{$plan_no}' and koutei={$koutei_no}";
        } else {
//          $_SESSION['s_sysmsg'] .= '【テストＭＳＧ：** 中断 **】';
            ///// 機械マスターの csv_flg から Netmoni/ロータリースイッチ方式の取得
            $query = "select mac_name, csv_flg from equip_machine_master2 where mac_no={$m_no} limit 1";
            $res = array();
            if (($rows=getResult($query,$res))>=1) {      // 機械マスターから機械名を取得する
                $name = substr($res[0][0],0,10);
                $csv_flg = $res[0][1];
            } else {
                $name = "     ";
                $csv_flg = 0;       // 1以外はロータリースイッチ方式とする
            }
            
            // equip_work_log へ中断データを書き込むため最新データを確認する
                // 旧SQL = where mac_no={$m_no} and siji_no={$s_no} and koutei={$k_no} and mac_state<>0 order by date_time DESC limit 1";
            $query = "select work_cnt from equip_work_log2_moni
                WHERE
                plan_no='{$plan_no}' and mac_no={$m_no} and koutei={$koutei_no}
                AND
                mac_state != 0
                ORDER BY date_time DESC
                LIMIT 1
            ";
            /*
            $query = "select work_cnt from equip_work_log2_moni
                WHERE
                equip_moni_index(mac_no, plan_no, koutei, date_time) > '{$m_no}{$plan_no}{$koutei_no}00000000000000'
                AND
                equip_moni_index(mac_no, plan_no, koutei, date_time) < '{$m_no}{$plan_no}{$koutei_no}99999999999999'
                AND
                mac_state != 0
                ORDER BY equip_moni_index(mac_no, plan_no, koutei, date_time) DESC
                LIMIT 1
            ";
            */
            $res=array();
            if (($rows=getResult($query,$res))>=1) {      // 最新データがあれば前のデータをセットする
                $pre_cnt  = $res[0][0];
                if ($csv_flg == 1) {    // Netmoni方式 = 15(中断)
                    $insert_qry = "insert into equip_work_log2_moni (mac_no, date_time, mac_state, work_cnt, plan_no, koutei)
                                values({$m_no}, '" . date('Y-m-d H:i:s') . "', 15, $pre_cnt, '{$plan_no}', {$koutei_no})
                            ";
                } else {                // ロータリースイッチ方式 = 9(中断)
                    $insert_qry = "insert into equip_work_log2_moni (mac_no, date_time, mac_state, work_cnt, plan_no, koutei)
                                values({$m_no}, '" . date('Y-m-d H:i:s') . "', 9, $pre_cnt, '{$plan_no}', {$koutei_no})
                            ";
                }
            } else {
                if ($csv_flg == 1) {    // Netmoni方式 = 15(中断)
                    $insert_qry = "insert into equip_work_log2_moni (mac_no, date_time, mac_state, work_cnt, plan_no, koutei)
                                    values({$m_no}, '" . date('Y-m-d H:i:s') . "', 15, 0, '{$plan_no}', {$koutei_no})
                                ";
                } else {                // ロータリースイッチ方式 = 9(中断)
                    $insert_qry = "insert into equip_work_log2_moni (mac_no, date_time, mac_state, work_cnt, plan_no, koutei)
                                    values({$m_no}, '" . date('Y-m-d H:i:s') . "', 9, 0, '{$plan_no}', {$koutei_no})
                                ";
                }
            }
            if (funcConnect()) {
                execQuery('begin');
                if (execQuery($insert_qry) >= 0) {
                    execQuery('commit');
                    disConnectDB();
                } else {
                    execQuery("rollback");
                    disConnectDB();
                    $error_msg = date('Y/m/d H:i:s', mktime());
                    $error_msg .= "-execQuery: $insert_qry";
                    `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
                }
            } else {
                $error_msg = date('Y/m/d H:i:s', mktime());
                $error_msg .= "-funcConnect: $insert_qry";
                `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
            }

            $update_qry = "update equip_work_log2_header_moni set work_flg=FALSE
                           where mac_no={$m_no} and plan_no='{$plan_no}' and koutei={$koutei_no}"; 
        }
        if (funcConnect()) {
            execQuery('begin');
            if (execQuery($update_qry) >= 0) {
                execQuery('commit');
                disConnectDB();
            } else {
                execQuery('rollback');
                disConnectDB();
                $error_msg = date('Y/m/d H:i:s', mktime());
                $error_msg .= "-execQuery:完了:$update_qry";
                `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
            }
        } else {
            $error_msg = date('Y/m/d H:i:s', mktime());
            $error_msg .= "-funcConnect:完了:$update_qry";
            `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
        }
    }

    // 製造課の機械運転開始データ削除(ヘッダーファイル & 経歴)(トランザクション処理)
    public function HeaderInfoDel($m_no, $plan_no, $koutei_no)
    {
        $delete_header = "delete from equip_work_log2_header_moni where mac_no={$m_no} and plan_no='{$plan_no}' and 
                            koutei={$koutei_no}";
        $delete_work = "
            DELETE FROM equip_work_log2_moni
            WHERE
            plan_no='{$plan_no}' and mac_no={$m_no} and koutei={$koutei_no}
        ";
        /*
        $delete_work = "
            DELETE FROM equip_work_log2_moni
            WHERE
            equip_moni_index(mac_no, plan_no, koutei, date_time) > '{$m_no}{$plan_no}{$koutei_no}00000000000000'
            AND
            equip_moni_index(mac_no, plan_no, koutei, date_time) < '{$m_no}{$plan_no}{$koutei_no}99999999999999'
        ";
        */
        if (funcConnect()) {
            execQuery('begin');
            if (execQuery($delete_header)>=0) {
//                execQuery('commit');
//                disConnectDB();
//                return true;
                if (execQuery($delete_work)>=0) {
                    execQuery('commit');
                    disConnectDB();
                    return true;
                } else {
                    execQuery('rollback');
                    disConnectDB();
                    $error_msg = date('Y/m/d H:i:s', mktime());
                    $error_msg .= "-execQuery: $delete_work Transaction";
                    `echo "$error_msg" >> /tmp/equipment_write_error.log`;
                }
            } else {
                execQuery('rollback');
                disConnectDB();
                $error_msg = date('Y/m/d H:i:s', mktime());
                $error_msg .= "-execQuery: $delete_header Transaction";
                `echo "$error_msg" >> /tmp/equipment_write_error.log`;
            }
        } else {
            $error_msg = date('Y/m/d H:i:s', mktime());
            $error_msg .= "-funcConnect: $delete_header Transaction";
            `echo "$error_msg" >> /tmp/equipment_write_error.log`;
        }
        return false;
    }
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/

    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
} // Class Monitoring_Model End
?>
