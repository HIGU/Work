<?php 
//////////////////////////////////////////////////////////////////////////////
// 組立設備稼働管理システムの機械運転日報 日報作成ビジネスロジック          //
// Copyright (C) 2021-2021 norihisa_ooya@nitto-kohki.co.jp                  //
// Original by yamagishi@matehan.co.jp                                      //
// Changed history                                                          //
// 2021/03/26 Created  MakeReport.php                                       //
// 2021/10/25 組立自動機のみ未稼働分の日報は作成しないように変更。          //
//            前日分ではなく当日計画の完了分まで日報を作成するようにした場合//
//            当日分を再作成できない為、元に戻した。                        //
//            BUSINESS_DAY_CHANGE_TIMEをBUSINESS_DAY_CHANGE_TIME_KUMIにし   //
//            defineで0000に設定                                            //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);
ini_set('max_execution_time', 2400);    // 最大実行時間=40分 CLI CGI版

require_once ('../com/define.php');
require_once ('../com/function.php');
require_once ('../com/mu_date.php');
// require_once ('../../../function.php');
// access_log();                       // Script Name は自動取得

// ----------------------------------------------------- //
// -- テスト用  リリース時には削除                    -- //
// ----------------------------------------------------- //
/*
$con = getConnection();
pg_query ($con , "delete from equip_work_report where work_date=20040715");
pg_query ($con , "delete from equip_work_report_log where work_date=20040715");
MakeMachineReportOneDay(1361,1,20040715);
*/

// ----------------------------------------------------- //

// --------------------------------------------------
// 機械運転日報作成メイン処理
// --------------------------------------------------
function MakeReport()
{
    //return;
    global $con;
    
    // トランザクション開始
    pg_query ($con , 'BEGIN');
    
    // 機械マスタ取得
    if (isset($_SESSION['factory'])) {
        $factory = $_SESSION['factory'];
    } else {
        $factory = '';
    }
    
    // 排他
    $lock = fopen (DOCUMENT_ROOT.BUSINESS_PATH . ".LOCK{$factory}", 'w');
    flock ($lock,LOCK_EX);
    
    if ($factory != '') {
        $sql = "select   mac_no
                        ,csv_flg
                    from
                        equip_machine_master2
                    where
                        survey='Y'
                        and
                        mac_no!=9999
                        and
                        factory='{$factory}'
                    order by
                        mac_no
        ";
    } else {
        $sql = "select   mac_no
                        ,csv_flg
                    from
                        equip_machine_master2
                    where
                        survey='Y'
                        and
                        mac_no!=9999
                    order by
                        mac_no
        ";
    }
    $rs  = pg_query ($con , $sql);
    
    // 機械マスタの数だけ日報を作成する
    while ($row = pg_fetch_array ($rs)) {
        MakeMachineReport($row['mac_no'],$row['csv_flg']);
    }
    pg_query ($con , 'COMMIT');
    
    // 排他解除
    flock ($lock,LOCK_UN);

}
// --------------------------------------------------
// 機械毎の日報を作成
// --------------------------------------------------
function MakeMachineReport($MacNo,$CsvFlg)
{
    global $con,$Report;
    // 日報作成日範囲取得
    $StartDate = getMakeReportStartDate($MacNo);
    $EndDate   = getMakeReportEndDate();
    //2021/10/25 組立日報用
    //$EndDate   = mu_Date::addDay($EndDate,1);
    
    // 最後に日報が作成された次の日〜昨日までの運転ログを集計
    for ($i=$StartDate;$i<=$EndDate;$i=mu_Date::addDay($i,1)) {
        MakeMachineReportOneDay($MacNo,$CsvFlg,$i);
    }
}
// --------------------------------------------------
// 指定された機械，日付の運転日報を作成
// --------------------------------------------------
function MakeMachineReportOneDay($MacNo,$CsvFlg,$Day)
{

    global $con,$Report;
    global $PrevRow,$NowRow,$Log;
    // 日報作成日取得
    $StartDate = $Day.BUSINESS_DAY_CHANGE_TIME_KUMI;
    $EndDate   = mu_Date::addDay($Day,1).BUSINESS_DAY_CHANGE_TIME_KUMI;
    //////////////////////////////////////////////////////////////
    $StartDate = substr($StartDate, 0, 4) . '/' . substr($StartDate, 4, 2) . '/' . substr($StartDate, 6, 2) . ' ' . substr($StartDate, 8, 2) . ':' . substr($StartDate, 10, 2) . ':00';
    $EndDate   = substr($EndDate, 0, 4) . '/' . substr($EndDate, 4, 2) . '/' . substr($EndDate, 6, 2) . ' ' . substr($EndDate, 8, 2) . ':' . substr($EndDate, 10, 2) . ':00';
    //////////////////////////////////////////////////////////////
    
    // 対象日の一番最初に稼働する指示No.を取得
    // $sql = "select siji_no from equip_work_log2 "
    //      . "where date_time = (select min(date_time) from equip_work_log2 where mac_no=$MacNo and date_time >= to_timestamp('$StartDate', 'YYYYMMDDHHMI') and date_time < to_timestamp('$EndDate', 'YYYYMMDDHHMI')) "
    //      . "and mac_no=$MacNo ";
    $sql = "select plan_no
                from
                    equip_work_log2_moni
                where
                    equip_index2(mac_no, date_time) >= '{$MacNo}{$StartDate}'
                    and
                    equip_index2(mac_no, date_time) <  '{$MacNo}{$EndDate}'
                order by
                    equip_index2(mac_no, date_time) ASC
                limit 1 offset 0
    ";
    $rs = pg_query ($con , $sql);
    if($Row  = pg_fetch_array ($rs)) {
        $StartSijiNo = $Row['plan_no'];     // データ有り
    } else {
        $StartSijiNo = 0;                   // データ無し
    }
    $rs = pg_query ($con , $sql);
    
    // 対象日の一番最後に稼働する指示No.を取得
    // $sql = "select siji_no from equip_work_log2 "
    //      . "where date_time = (select max(date_time) from equip_work_log2 where mac_no=$MacNo and date_time >= to_timestamp('$StartDate', 'YYYYMMDDHHMI') and date_time < to_timestamp('$EndDate', 'YYYYMMDDHHMI')) "
    //      . "and mac_no=$MacNo ";
    $sql = "select plan_no
                from
                    equip_work_log2_moni AS a
                where
                    equip_index2(mac_no, date_time) >= '{$MacNo}{$StartDate}'
                    and
                    equip_index2(mac_no, date_time) <  '{$MacNo}{$EndDate}'
                order by
                    equip_index2(mac_no, date_time) DESC
                limit 1 offset 0
    ";
    /* 2021/10/25 組立日報用
    $sql = "select plan_no
                from
                    equip_work_log2_moni AS a
                where
                    equip_index2(mac_no, date_time) >= '{$MacNo}{$StartDate}'
                    and
                    equip_index2(mac_no, date_time) <  '{$MacNo}{$EndDate}'
                    and 
                      (SELECT h.plan_no
                           FROM
                          equip_work_log2_header_moni AS h
                          WHERE
                         h.mac_no={$MacNo} and
                         h.work_flg=false
                         and h.plan_no=a.plan_no)<>''
                order by
                    equip_index2(mac_no, date_time) DESC
                limit 1 offset 0
    ";
    */
    $rs = pg_query ($con , $sql);
    if($Row  = pg_fetch_array ($rs)) {
        $EndSijiNo = $Row['plan_no'];     // データ有り
    } else {
        $EndSijiNo = 0;                   // データ無し
    }
    
    // 抽出ＳＱＬ
    //$sql = "select mac_no,to_char(date_time, 'YYYY-MM-DD HH24:MI:SS') as date_time,mac_state,work_cnt,siji_no,koutei from equip_work_log2 "
    //     . "where  mac_no=$MacNo and date_time >= to_timestamp('$StartDate', 'YYYYMMDDHHMI') and date_time < to_timestamp('$EndDate', 'YYYYMMDDHHMI') "
    //     . "order by siji_no,date_time ";
    $sql = " SELECT "
         . "     a.mac_no, "
         . "     to_char(a.date_time, 'YYYY-MM-DD HH24:MI:SS') as date_time, "
         . "     a.mac_state, "
         . "     a.work_cnt, "
         . "     a.plan_no, "
         . "     a.koutei, "
         . "     b.mintime "
         . " FROM "
         . "     equip_work_log2_moni a  "
         . " left outer join (SELECT "
         . "                       mac_no, "
         . "                       plan_no, "
         . "                       min(date_time) as mintime "
         . "                   FROM "
         . "                       equip_work_log2_moni "
         . "                   WHERE "
         // . "                       mac_no=$MacNo AND "
         // . "                       date_time >= to_timestamp('$StartDate', 'YYYYMMDDHHMI') AND "
         // . "                       date_time < to_timestamp('$EndDate', 'YYYYMMDDHHMI') "
         . "                       equip_index2(mac_no, date_time) >= '{$MacNo}{$StartDate}' AND "
         . "                       equip_index2(mac_no, date_time) <  '{$MacNo}{$EndDate}' "
         . "                   GROUP BY "
         . "                       mac_no, "
         . "                       plan_no "
         . "                   ) b on a.mac_no=b.mac_no and a.plan_no=b.plan_no "
         . " WHERE "
         // . "     a.mac_no=$MacNo AND "
         // . "     a.date_time >= to_timestamp('$StartDate', 'YYYYMMDDHHMI') AND "
         // . "     a.date_time < to_timestamp('$EndDate', 'YYYYMMDDHHMI') "
         . "     equip_index2(a.mac_no, a.date_time) >= '{$MacNo}{$StartDate}' AND "
         . "     equip_index2(a.mac_no, a.date_time) <  '{$MacNo}{$EndDate}'"
         //2021/10/25 組立日報用
         //. "     equip_index2(a.mac_no, a.date_time) <  '{$MacNo}{$EndDate}' AND "
         //. "     (SELECT h.plan_no FROM equip_work_log2_header_moni AS h WHERE h.mac_no={$MacNo} and h.work_flg=false and h.plan_no=a.plan_no)<>''"
         . " ORDER BY "
         . "     b.mintime, "
         . "     a.plan_no, "
         . "     a.date_time ";
    $rs = pg_query ($con , $sql);
    
    // １件前のレコード
    $PrevRow = null;
    //  現在のレコード
    $NowRow  = null;
    
    $PrevSet = false;
    // ログを集計
    while (true) {
        // 現在のレコードを１件前のレコードにセット
        $PrevRow = $NowRow;
        // 次のレコード読み込み
        $NowRow  = pg_fetch_array ($rs);
        // キーブレイクコードの取得
        $KeyCode = KeyBreakCheck($NowRow,$PrevRow,$CsvFlg);
        // １件目の時の処理
        if (!$PrevRow) {
            // ログがない場合は機械の電源が入っていない状態
            if (!$NowRow) {
                // 停止中の日報を作成する
                // 2021/10/25 組立日報用
                //MakeZeroReport($MacNo,$Day);
                break;
            }
        }
        // (KeyCode:1) ステータスが変わった時
        if ($KeyCode >= 1) {
            // (KeyCode:3)１件目はスルー
            if ($KeyCode != 3) {
                // --------------------------------------------------
                // ログデータのセット
                // --------------------------------------------------
                $Log['work_date']   = getBusinessDay($PrevRow['date_time']);
                $Log['mac_no']      = $MacNo;
                $Log['plan_no']     = $PrevRow['plan_no'];
                $Log['koutei']      = $PrevRow['koutei'];
                $Log['mac_state']   = $PrevRow['mac_state'];
                // ステータスが変わるか、指示，行程番号がかわったら現在のレコードの時刻をセット
                $Log['ToDate']      = mu_Date::toString($NowRow['date_time'],"Ymd");
                $Log['ToTime']      = mu_Date::toString($NowRow['date_time'],"Hi");
                if ($KeyCode == 4) {
                    $Log['ToDate']      = mu_Date::toString($PrevRow['date_time'],"Ymd");
                    $Log['ToTime']      = mu_Date::toString($PrevRow['date_time'],"Hi");
                }
                
                // 日報が変わるとき
                if ($KeyCode >= 2) {
                    // 対象日の一番最後に稼働する指示No.なら翌日までの時刻をセット
                    if ($PrevRow['plan_no'] == $EndSijiNo) {
                        // 2021/10/25 組立日報用
                        //$Log['ToDate']      = mu_Date::addDay($Log['work_date'] ,1);
                        //$Log['ToTime']      = BUSINESS_DAY_CHANGE_TIME_KUMI;
                    }
                    
                    // 運転ログヘッダ読み込み
                    $sql = "SELECT "
                         . "    to_char(end_timestamp, 'YYYY-MM-DD HH24:MI:SS') as end_timestamp "
                         . "FROM "
                         . "    equip_work_log2_header_moni "
                         . "WHERE "
                         . "    mac_no=$MacNo AND "
                         . "    plan_no='".$PrevRow['plan_no']."' AND "
                         . "    koutei=".$PrevRow['koutei']." AND "
                         . "    work_flg=false ";
                    if (!$rsloghed = pg_query ($con , $sql)) {
                        pg_query ($con , 'ROLLBACK');
                        $SYSTEM_MESSAGE = "データベースの更新に失敗しました\n$sql";
                        require_once ('../com/' . ERROR_PAGE);
                        exit();
                    }
                    if ($LogHedRow  = pg_fetch_array ($rsloghed)) {
                        // 完了日付が対象日なら 完了時刻を最終完了ログ時刻とする
                        if (getBusinessDay($LogHedRow['end_timestamp']) == $Day) {
                            $Log['ToDate']      = mu_Date::toString($LogHedRow['end_timestamp'],"Ymd");
                            $Log['ToTime']      = mu_Date::toString($LogHedRow['end_timestamp'],"Hi");
                        }
                    }
                    
                }
                // 日報ログ書込
                $sql = "insert into equip_work_report_moni_log(work_date,mac_no,plan_no,koutei,mac_state,from_date,from_time,to_date,to_time,cut_time,last_user) values ( "
                     . $Log['work_date']            . ","
                     . $Log['mac_no']               . ",'"
                     . $Log['plan_no']              . "',"
                     . $Log['koutei']               . ","
                     . $Log['mac_state']            . ","
                     . $Log['FromDate']             . ","
                     . $Log['FromTime']             . ","
                     . $Log['ToDate']               . ","
                     . $Log['ToTime']               . ","
                     . $Log['CutTime']              . ","
                     . "'" . $_SESSION['User_ID']   . "')";
                if (!pg_query ($con , $sql)) {
                    pg_query ($con , 'ROLLBACK');
                    $SYSTEM_MESSAGE = "データベースの更新に失敗しました\n$sql";
                    require_once ('../com/' . ERROR_PAGE);
                    exit();
                }
                // --------------------------------------------------
                // ログデータのセット
                // --------------------------------------------------
                $Log['FromDate'] = mu_Date::toString($NowRow['date_time'],'Ymd');
                $Log['FromTime'] = mu_Date::toString($NowRow['date_time'],'Hi');
                $Log['CutTime']     = 0;
            }
        }
        // (KeyCode:2) 指示，行程，日付が変わった時 （日報が別になる）
        if ($KeyCode >= 2) {
            // (KeyCode:3) １件目のデータではない時
            if ($KeyCode != 3) {
                // ヘッダレコードの作成 
                MakeHeaderRecode($PrevRow);
            }
            // 対象日一番最初に始まる指示No.で8:30のログでなければ、前日分の最終ログを開始時刻まで引き継ぐ
            if ($NowRow['plan_no'] == $StartSijiNo && mu_Date::toString($NowRow['date_time'],"Hi") != BUSINESS_DAY_CHANGE_TIME_KUMI) {
                // $sql = " select * from equip_work_log2 where mac_no=$MacNo and date_time = (select max(date_time) from equip_work_log2 where mac_no=$MacNo and date_time < to_timestamp('" . $Day.BUSINESS_DAY_CHANGE_TIME_KUMI . "','YYYYMMDDHHMI'))";
                $basicDate = REPORT_START_DATE . BUSINESS_DAY_CHANGE_TIME_KUMI;
                $basicDate = substr($basicDate, 0, 4) . '/' . substr($basicDate, 4, 2) . '/' . substr($basicDate, 6, 2) . ' ' . substr($basicDate, 8, 2) . ':' . substr($basicDate, 10, 2) . ':00';
                $sql = " select * 
                            from
                                equip_work_log2_moni
                            where
                                equip_index2(mac_no, date_time) < '{$MacNo}{$StartDate}'
                                and
                                equip_index2(mac_no, date_time) >= '{$MacNo}{$basicDate}'
                            order by
                                equip_index2(mac_no, date_time) DESC
                            limit 1 offset 0
                ";
                if (!$logrs = pg_query ($con , $sql)) {
                    pg_query ($con , 'ROLLBACK');
                    $SYSTEM_MESSAGE = "システムエラーが発生しました\n$sql";
                    require_once ('../com/'.ERROR_PAGE);
                    exit();
                }
                // ここでレコードが取得できなければ全く稼働したことがない機械なので
                // 日報はつくらない（情報不足でつくれない）。 マスタだけ存在するのかも。
                if ($logrow =  pg_fetch_array ($logrs)) {
                    // 同一のステータスの場合合算
                    if ($logrow['mac_state'] == $NowRow['mac_state']) {
                        $Log['FromDate']            = $Day;
                        $Log['FromTime']            = BUSINESS_DAY_CHANGE_TIME_KUMI;
                        $PrevSet = true;
                    } else {
                        $Log['work_date']           = $Day;
                        $Log['mac_no']              = $MacNo;
                        $Log['plan_no']             = $logrow['plan_no'];
                        $Log['koutei']              = $logrow['koutei'];
                        $Log['mac_state']           = $logrow['mac_state'];
                        $Log['FromDate']            = $Day;
                        $Log['FromTime']            = BUSINESS_DAY_CHANGE_TIME_KUMI;
                        $Log['ToDate']              = mu_Date::toString($NowRow['date_time'],"Ymd");
                        $Log['ToTime']              = mu_Date::toString($NowRow['date_time'],"Hi");
                        $Log['CutTime']             = 0;
                        // 日報ログ書込
                        $sql = "insert into equip_work_report_moni_log(work_date,mac_no,plan_no,koutei,mac_state,from_date,from_time,to_date,to_time,cut_time,last_user) values ( "
                             . $Log['work_date']            . ","
                             . $Log['mac_no']               . ",'"
                             . $Log['plan_no']              . "',"
                             . $Log['koutei']               . ","
                             . $Log['mac_state']            . ","
                             . $Log['FromDate']             . ","
                             . $Log['FromTime']             . ","
                             . $Log['ToDate']               . ","
                             . $Log['ToTime']               . ","
                             . $Log['CutTime']              . ","
                             . "'" . $_SESSION['User_ID']   . "')";
                        if (!pg_query ($con , $sql)) {
                            pg_query ($con , 'ROLLBACK');
                            $SYSTEM_MESSAGE = "データベースの更新に失敗しました\n$sql";
                            require_once ('../com/' . ERROR_PAGE);
                            exit();
                        }
                        if ($logrow['plan_no'] != $NowRow['plan_no']) {
                            $Report['mac_no']    = $MacNo;
                            $Report['plan_no']   = $logrow['plan_no'];
                            $Report['koutei']    = $logrow['koutei'];
                            $Report['work_date'] = getBusinessDay($NowRow['date_time']);
                            $Report['siji']      = 0;
                            $Report['ng']        = 0;
                            $Report['plan']      = 0;
                            $Report['injection'] = 0;
                            $Report['end_flg']   = 0;
                            $Report['memo']      = '';
                            $Report['min']       = 0;
                            $Report['max']       = 0;
                            MakeHeaderRecode($logrow);
                        }
                        $PrevSet = false;
                    }
                }
            }
            // --------------------------------------------------
            // ログデータのセット
            // --------------------------------------------------
            $Log['work_date']   = getBusinessDay($NowRow['date_time']);
            $Log['mac_no']      = $MacNo;
            $Log['plan_no']     = $NowRow['plan_no'];
            $Log['koutei']      = $NowRow['koutei'];
            $Log['mac_state']   = $NowRow['mac_state'];
            if ($PrevSet == false) {
                $Log['FromDate']    = mu_Date::toString($NowRow['date_time'],"Ymd");
                $Log['FromTime']    = mu_Date::toString($NowRow['date_time'],"Hi");
            }
            $Log['ToDate']      = mu_Date::toString($NowRow['date_time'],"Ymd");
            $Log['ToTime']      = mu_Date::toString($NowRow['date_time'],"Hi");
            $Log['CutTime']     = 0;
            $PrevSet = false;
            // --------------------------------------------------
            // 日報データの初期セット
            // --------------------------------------------------
            $Report['mac_no']    = $MacNo;
            $Report['plan_no']   = $NowRow['plan_no'];
            $Report['koutei']    = $NowRow['koutei'];
            $Report['work_date'] = getBusinessDay($NowRow['date_time']);
            $Report['siji']      = 0;
            $Report['ng']        = 0;
            $Report['plan']      = 0;
            $Report['injection'] = 0;
            $Report['end_flg']   = 0;
            $Report['memo']      = '';
            $Report['min']       = 999999999;
            $Report['max']       = 0;
        }
        
        // (KeyCode:4) データが読めなくなったら終了
        if ($KeyCode == 4) {
            break;
        }
        
        if ($NowRow['work_cnt'] < $Report['min']) $Report['min'] = $NowRow['work_cnt'];
        if ($NowRow['work_cnt'] > $Report['max']) $Report['max'] = $NowRow['work_cnt'];
        // **************************************** //
        // 仕様変更：中断はそのまま使用 2004/07/08  //
        // **************************************** //
        // カット時間の集計
        //if (($CsvFlg == '1' && $NowRow['mac_state'] == '15') || ($CsvFlg == '2' && $NowRow['mac_state'] ==  '9')) {
        //    $Log['CutTime'] += CalWorkTime(mu_Date::toString($PrevRow['date_time'],"Hi"),mu_Date::toString($NowRow['date_time'],"Hi"));
        //    $NowRow['mac_state'] = $PrevRow['mac_state'];
        //}
    }

}
// --------------------------------------------------
// キーブレイクの判定
// --------------------------------------------------
function KeyBreakCheck($NowRow,$PrevRow,$CsvFlg)
{
    // レコードが読めなくなった
    if (!$NowRow) return 4;
    // １件目のレコード
    if (!$PrevRow) return 3;
    // 計画No.が変わった
    if ($NowRow['plan_no'] != $PrevRow['plan_no']) return 2;
    // 行程No.が変わった
    if ($NowRow['koutei'] != $PrevRow['koutei']) return 2;
    // 業務日付が変わった
    if (getBusinessDay($NowRow['date_time']) != getBusinessDay($PrevRow['date_time'])) return 2;
    
    // **************************************** //
    // 仕様変更：中断はそのまま使用 2004/07/08  //
    // **************************************** //
    // ステータス中断はブレイクしない
    //if (($CsvFlg == '1' && $NowRow['mac_state'] == '15') || ($CsvFlg == '2' && $NowRow['mac_state'] ==  '9')) {
    //    return 0;
    //}
    //if ($CsvFlg == '1' && $NowRow['mac_state'] == '15') return 0;
    //if ($CsvFlg == '2' && $NowRow['mac_state'] ==  '9') return 0;
    
    // ステータスが変わった
    if ($NowRow['mac_state'] != $PrevRow['mac_state']) return 1;
    
    // 変化なし
    return 0;
}
// --------------------------------------------------
// 渡された日時から業務日付を取得
// 日付変更時刻は define.php の
// BUSINESS_DAY_CHANGE_TIME_KUMI を使用
//
// (例)
// 2004/06/01 10:00  -> 2004/06/01
// 2004/06/02  3:00  -> 2004/06/01
// --------------------------------------------------
function getBusinessDay($DateTime)
{
    
    $Date = mu_Date::toString($DateTime,"Ymd");
    $Time = mu_Date::toString($DateTime,"Hi");
    
    if ($Time < BUSINESS_DAY_CHANGE_TIME_KUMI) {
        $Date = mu_Date::addDay($Date ,-1);
    }
    
    return $Date;
}
// --------------------------------------------------
// 運転日報ヘッダレコード作成
// --------------------------------------------------
function MakeHeaderRecode($row)
{
    global $con,$Report;
    // ログヘッダ読み込み
    $sql = " select koutei,parts_no,plan_cnt from equip_work_log2_header_moni where mac_no=".$row['mac_no']." and plan_no='".$row['plan_no']."' and koutei=".$row['koutei'];
    $rs = pg_query ($con , $sql);
    if (!$hed =  pg_fetch_array ($rs)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "システムエラーが発生しました\nログヘッダが見つかりませんでした\n\n$sql";
        require_once ('../com/'.ERROR_PAGE);
        exit();
    }
    // 材料の計算でmac_noを使用するため、ここで退避 2006/06/12
    $MacNo = $row['mac_no'];
    // --------------------------------------------------
    // 前日良品数の取得
    // --------------------------------------------------
    $sql = " select yesterday + today as num from equip_work_report_moni "
         . " where  work_date=".mu_Date::addDay($Report['work_date'],-1)." and mac_no=".$row['mac_no']." and plan_no='".$row['plan_no']."' and koutei=".$row['koutei'];
         
    if (!$rs = pg_query ($con , $sql)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "システムエラーが発生しました\n$sql";
        require_once ('../com/'.ERROR_PAGE);
        exit();
    }
    if ($row =  pg_fetch_array ($rs)) {
        $Report['yesterday'] = $row['num'];
    } else {
        $Report['yesterday'] = 0;
    }
    
    // 当日良品数
    $Report['today'] = $Report['max'] - $Report['min'];
    
    // --------------------------------------------------
    // 投入材料の計算
    // --------------------------------------------------
    if ($row = getPartsMaster($hed['parts_no'], $MacNo)) {
        $JoinChecker = 0;
        // 部品マスタ join 成功時
        if (isset($row['item_code'])) {
            $Report['injection_item']   = $row['use_item'];        // 部品番号
            $JoinChecker++;
        } else {
            // 投入アイテム
            $Report['injection_item'] = '';
        }
        
        // 材料マスタ join 成功時
        if (isset($row['weight'])) {
            $JoinChecker++;
        }
        // 全ての情報が取得できれば投入材料の計算が可能
        if ($JoinChecker == 2) {
            // バー材の計算
            if ($row['type'] == 'B') {
                // 必要な長さ
                $NeedLength = $row['size'] / 1000 * $Report['today'];
                
                // 破材があるか
                $sql = " select length,weight from equip_abandonment_item where item_code='" . $row['use_item'] . "'";
                $rs2 = pg_query ($con,$sql);
                if ($row2 = pg_fetch_array($rs2)) {
                    // 使った破材
                    if (($row2['length'] - $row['abandonment'] / 1000) < $NeedLength) {
                        // 破材だけでは足りない
                        
                        /**********************/
                        /** 端材管理用の計算 **/
                        /**********************/
                        $CalNeedLength = $NeedLength;
                        // $CalNeedLength -= ($row2['length'] - $row['abandonment'] / 1000);
                        
                        // 投入数  破材つかった足りない長さ / １本当たりの長さ - 破材サイズ
                        $Report['injection'] = round(($CalNeedLength / ($row['length']-($row['abandonment']/1000))), 2);
                        // つかった破材分長さ
                        // $Report['abandonment'] = Mfloor($CalNeedLength - ($row['length']-$row['abandonment']/1000) * $injection_int , 4);
                        $Report['abandonment'] = ($Report['injection'] - ((int)$Report['injection'])) * $row['length'];
                        
                        // 余った破材
                        if ($Report['abandonment'] != 0) {
                            // 破材出たら
                            $AbandonmentLength = $row['length'] - $Report['abandonment'];
                        } else {
                            // 破材出てない
                            $AbandonmentLength = 0;
                        }
                        $AbandonmentWeight = $AbandonmentLength * $row['weight'];
                        
                        // 破材マスタ更新 
                        $sql = "update equip_abandonment_item set length=$AbandonmentLength , weight=$AbandonmentWeight where item_code='" . $row['use_item'] . "'";
                        pg_query ($con,$sql);
                        
                        /**********************/
                        /** 日報表示用の計算 **/
                        /**********************/
                        // $NeedLength -= ($row2['length'] - $row['abandonment'] / 1000);
                        
                        // 投入数  破材つかった足りない長さ / １本当たりの長さ - 破材サイズ
                        $Report['injection'] = round(($NeedLength / ($row['length']-($row['abandonment']/1000))), 2);
                        // つかった破材分長さ
                        // $Report['abandonment'] = Mfloor($NeedLength - ($row['length']-$row['abandonment']/1000) * $injection_int , 4);
                        $Report['abandonment'] = ($Report['injection'] - ((int)$Report['injection'])) * $row['length'];
                        
                    } else {
                        // 破材だけでたりた
                        $Report['abandonment'] = $NeedLength;
                        // 破材マスタ更新 
                        $sql = "update equip_abandonment_item set length=" . ($row2['length'] - $NeedLength) ." , weight= " . ($row2['length'] - $NeedLength) * $row['weight'] ." where item_code='" . $row['use_item'] . "'";
                        pg_query ($con,$sql);
                        // 投入数 破材使ったら０本投入としてカウント→小数部も必要に変更
                        $Report['injection'] = round($NeedLength, 2);
                        // つかった破材分長さ
                        $Report['abandonment'] = $NeedLength;
                    }
                } else {
                    // 破材マスタ存在しないとき
                    // 投入数  破材つかった足りない長さ / １本当たりの長さ - 破材サイズ
                    $Report['injection'] = round(($NeedLength / ($row['length']-($row['abandonment'])/1000)), 2);
                    // つかった破材分長さ
                    // $Report['abandonment'] = Mfloor($NeedLength - ($row['length']-$row['abandonment']/1000) * $injection_int , 4);
                    $Report['abandonment'] = ($Report['injection'] - ((int)$Report['injection'])) * $row['length'];
                    
                    // 余った破材
                    if ($Report['abandonment'] != 0) {
                        // 破材出たら
                        $AbandonmentLength = $row['length'] - $Report['abandonment'];
                    } else {
                        // 破材出てない
                        $AbandonmentLength = 0;
                    }
                    $AbandonmentWeight = $AbandonmentLength * $row['weight'];
                    
                    // 破材マスタ更新 
                    $sql = "insert into equip_abandonment_item(item_code,length,weight) values('" . $row['use_item'] . "',$AbandonmentLength,$AbandonmentWeight)";
                    pg_query ($con,$sql);
                }
            }
            // カット材の計算
            if ($row['type'] == 'C') {
                // 投入数
                $Report['injection'] = $Report['today'];
                // つかった破材分長さ
                $Report['abandonment'] = 0;
            }
        } else {
            // つかった破材分長さ
            $Report['abandonment'] = 0;
        }
    } else {
        // equip_work_inst_header にない
        
        // 投入アイテム
        $Report['injection_item'] = '';
        // 投入数
        $Report['injection'] = 0;
        // つかった破材分長さ
        $Report['abandonment'] = 0;
    }
    // ＳＱＬ
    $sql = "insert into equip_work_report_moni (work_date,mac_no,plan_no,koutei,yesterday,today,ng,ng_kbn,plan,end_flg,memo,injection_item,injection,abandonment,decision_flg,last_user) values ( "
         .       $Report['work_date']       . ","
         .       $Report['mac_no']          . ",'"
         .       $Report['plan_no']         . "',"
         .       $Report['koutei']          . ","
         .       $Report['yesterday']       . ","
         .       $Report['today']           . ","
         .       "0,"                                           // 不良数
         .       "'',"                                          // 不良区分
         .       "0,"                                           // 段取り数
         . "'" . $Report['end_flg']         . "',"
         . "'" . $Report['memo']            . "',"
         . "'" . $Report['injection_item']  . "',"
         .       $Report['injection']       . ","
         .       $Report['abandonment']     . ","
         .       "0,"                                           // 確定フラグ（未確定）
         . "'" . $_SESSION['User_ID']   . "')";
    if (!pg_query ($con , $sql)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "データベースの更新に失敗しました\n$sql";
        require_once ('../com/' . ERROR_PAGE);
        exit();
    }
}
// --------------------------------------------------
// 運転ログがない場合の 停止中日報の作成
// --------------------------------------------------
function MakeZeroReport($MacNo,$Day)
{

    global $con,$Report;
    // 対象日付の以前の最後にできたログを取得
    $date = $Day.BUSINESS_DAY_CHANGE_TIME_KUMI;
    // $sql = " select * from equip_work_log2 where mac_no=$MacNo and date_time = (select max(date_time) from equip_work_log2 where mac_no=$MacNo and date_time < to_timestamp('" . $Day.BUSINESS_DAY_CHANGE_TIME_KUMI . "','YYYYMMDDHHMI'))";
    $StartDate = substr($date, 0, 4) . '/' . substr($date, 4, 2) . '/' . substr($date, 6, 2) . ' ' . substr($date, 8, 2) . ':' . substr($date, 10, 2) . ':00';
    $basicDate = REPORT_START_DATE . BUSINESS_DAY_CHANGE_TIME_KUMI;
    $basicDate = substr($basicDate, 0, 4) . '/' . substr($basicDate, 4, 2) . '/' . substr($basicDate, 6, 2) . ' ' . substr($basicDate, 8, 2) . ':' . substr($basicDate, 10, 2) . ':00';
    $sql = " select * 
                from
                    equip_work_log2_moni
                where
                    equip_index2(mac_no, date_time) < '{$MacNo}{$StartDate}'
                    and
                    equip_index2(mac_no, date_time) >= '{$MacNo}{$basicDate}'
                order by
                    equip_index2(mac_no, date_time) DESC
                limit 1 offset 0
    ";
    if (!$rs = pg_query ($con , $sql)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "システムエラーが発生しました\n$sql";
        require_once ('../com/'.ERROR_PAGE);
        exit();
    }
    if (!$row =  pg_fetch_array ($rs)) {
        // ここでレコードが取得できなければ全く稼働したことがない機械なので
        // 日報はつくらない（情報不足でつくれない）。 マスタだけ存在するのかも。
        return;
    }

    $Report['work_date']        = $Day;
    $Report['mac_no']           = $MacNo;
    $Report['plan_no']          = $row['plan_no'];
    $Report['koutei']           = $row['koutei'];
    $Report['end_flg']          = '';
    $Report['memo']             = '';
    $Report['injection_item']   = '';
    $Report['injection']        = 0;
    $Report['abandonment']      = 0;
    
    $Log['work_date']           = $Day;
    $Log['mac_no']              = $MacNo;
    $Log['plan_no']             = $row['plan_no'];
    $Log['koutei']              = $row['koutei'];
    $Log['mac_state']           = $row['mac_state'];
    $Log['FromDate']            = $Day;
    $Log['FromTime']            = BUSINESS_DAY_CHANGE_TIME_KUMI;
    $Log['ToDate']              = mu_Date::addDay($Day ,1);
    $Log['ToTime']              = BUSINESS_DAY_CHANGE_TIME_KUMI;
    $Log['CutTime']             = 0;


    // 運転ログヘッダ読み込み
    $sql = "SELECT "
         . "    to_char(end_timestamp, 'YYYY-MM-DD HH24:MI:SS') as end_timestamp "
         . "FROM "
         . "    equip_work_log2_header_moni "
         . "WHERE "
         . "    mac_no=$MacNo AND "
         . "    plan_no='".$row['plan_no']."' AND "
         . "    koutei=".$row['koutei']." AND "
         . "    work_flg=false ";
    if (!$rsloghed = pg_query ($con , $sql)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "データベースの更新に失敗しました\n$sql";
        require_once ('../com/' . ERROR_PAGE);
        exit();
    }
    if ($LogHedRow  = pg_fetch_array ($rsloghed)) {
        // 今日以前に完了しているのであれば 日報つくらない
        if (getBusinessDay($LogHedRow['end_timestamp']) < $Day) {
            return;
        }
        // 完了日付が対象日なら 完了時刻を最終完了ログ時刻とする
        if (getBusinessDay($LogHedRow['end_timestamp']) == $Day) {
            $Log['ToDate']      = mu_Date::toString($LogHedRow['end_timestamp'],"Ymd");
            $Log['ToTime']      = mu_Date::toString($LogHedRow['end_timestamp'],"Hi");
        }
    }
    

    // --------------------------------------------------
    // 前日良品数の取得
    // --------------------------------------------------
    $sql = " select yesterday + today as num from equip_work_report_moni "
         . " where  work_date=".mu_Date::addDay($Report['work_date'],-1)." and mac_no=".$row['mac_no']." and plan_no='".$row['plan_no']."' and koutei=".$row['koutei'];
         
    if (!$rs = pg_query ($con , $sql)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "システムエラーが発生しました\n$sql";
        require_once ('../com/'.ERROR_PAGE);
        exit();
    }
    if ($row =  pg_fetch_array ($rs)) {
        $Report['yesterday'] = $row['num'];
    } else {
        $Report['yesterday'] = 0;
    }
    
    // 当日良品数
    $Report['today'] = 0;
    
    $sql = "
        SELECT
            use_item
        FROM
            equip_work_log2_header_moni a
        LEFT OUTER JOIN  equip_parts b ON (a.parts_no=b.item_code)
        WHERE a.mac_no = {$Report['mac_no']} AND a.plan_no = '{$Report['plan_no']}' AND a.koutei = {$Report['koutei']}
        AND b.mac_no = {$Report['mac_no']}
    ";
    $rs = pg_query ($con , $sql);
    if ($row =  pg_fetch_array ($rs)) {
        $Report['injection_item']   = $row['use_item'];     // 機械専用があれば入れる
    } else {
        $sql = "
            SELECT
                use_item
            FROM
                equip_work_log2_header_moni a
            LEFT OUTER JOIN  equip_parts b ON (a.parts_no=b.item_code)
            WHERE a.mac_no = {$Report['mac_no']} AND a.plan_no = '{$Report['plan_no']}' AND a.koutei = {$Report['koutei']}
        ";
        if ($row =  pg_fetch_array ($rs)) {
            $Report['injection_item']   = $row['use_item'];     // 機械専用が無ければ共用データを入れる
        }
    }
    
    // ＳＱＬ
    $sql = "insert into equip_work_report_moni (work_date,mac_no,plan_no,koutei,yesterday,today,ng,ng_kbn,plan,end_flg,memo,injection_item,injection,abandonment,decision_flg,last_user) values ( "
         .       $Report['work_date']       . ","
         .       $Report['mac_no']          . ",'"
         .       $Report['plan_no']         . "',"
         .       $Report['koutei']          . ","
         .       $Report['yesterday']       . ","
         .       $Report['today']           . ","
         .       "0,"                                           // 不良数
         .       "'',"                                          // 不良区分
         .       "0,"                                           // 段取り数
         . "'" . $Report['end_flg']         . "',"
         . "'" . $Report['memo']            . "',"
         . "'" . $Report['injection_item']  . "',"
         .       $Report['injection']       . ","
         .       $Report['abandonment']     . ","
         .       "0,"                                           // 確定フラグ（未確定）
         . "'" . $_SESSION['User_ID']   . "')";

    if (!pg_query ($con , $sql)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "データベースの更新に失敗しました\n$sql";
        require_once ('../com/' . ERROR_PAGE);
        exit();
    }
    // 日報ログ書込
    $sql = "insert into equip_work_report_moni_log(work_date,mac_no,plan_no,koutei,mac_state,from_date,from_time,to_date,to_time,cut_time,last_user) values ( "
         . $Log['work_date']            . ","
         . $Log['mac_no']               . ",'"
         . $Log['plan_no']              . "',"
         . $Log['koutei']               . ","
         . $Log['mac_state']            . ","
         . $Log['FromDate']             . ","
         . $Log['FromTime']             . ","
         . $Log['ToDate']               . ","
         . $Log['ToTime']               . ","
         . $Log['CutTime']              . ","
         . "'" . $_SESSION['User_ID']   . "')";
    if (!pg_query ($con , $sql)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "データベースの更新に失敗しました\n$sql";
        require_once ('../com/' . ERROR_PAGE);
        exit();
    }

}
// --------------------------------------------------
// 渡された機械番号の日報作成開示日付を取得
// --------------------------------------------------
function getMakeReportStartDate($MacNo)
{

    global $con;
    
    $sql = "select max(work_date) as work_date from equip_work_report_moni where mac_no=$MacNo";
    $rs  = pg_query ($con , $sql);
    if ($row =  pg_fetch_array ($rs)) {
        $WorkDate = $row['work_date'];
        if (isset($WorkDate)) {
            $StartDate = mu_Date::addDay($WorkDate,1);
        } else {
            $StartDate = REPORT_START_DATE;
        }
    } else {
        $SYSTEM_MESSAGE = "システムエラーが発生しました\n$sql";
        require_once ('../com/'.ERROR_PAGE);
        exit();
    }
    
    return $StartDate;

}
// --------------------------------------------------
// 日報作成終了日付の取得
// --------------------------------------------------
function getMakeReportEndDate()
{
    /*
    $Year       = date('Y', time()); 
    $Month      = date('m', time()); 
    $Day        = date('d', time()); 
    $Hour       = date('H', time()); 
    $Minutes    = date('i', time()); 
    */
    $Date       = date('Y', time()) . date('m', time()) . date('d', time());
    $Time       = date('H', time()) . date('i', time());
    if ($Time < BUSINESS_DAY_CHANGE_TIME_KUMI) {
        $Date = mu_Date::addDay($Date,-1);
    }
    
    $Date = mu_Date::addDay($Date,-1);
    
    return $Date;

}
// --------------------------------------------------
// 部品マスターの取得 部品と機械番号でのキー対応版
// --------------------------------------------------
function getPartsMaster($parts_no, $mac_no)
{
    global $con;
    $sql_basic = "
        SELECT
            item_code       ,
            size            ,
            use_item        ,
            abandonment     ,
            type            ,
            weight          ,
            length
        FROM
            equip_parts
        LEFT OUTER JOIN equip_materials ON (mtcode = use_item)
    ";
    $sql_where = "
        WHERE
            item_code = '{$parts_no}' AND mac_no = {$mac_no}
    ";
    $sql = ($sql_basic . $sql_where);
    if (!$rs = pg_query ($con , $sql)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "システムエラーが発生しました\n$sql";
        require_once ('../com/'.ERROR_PAGE);
        exit();
    }
    if ( !($row=pg_fetch_array($rs)) ) {
        $sql_where = "
            WHERE
                item_code = '{$parts_no}' AND mac_no = 0
        ";
        $sql = ($sql_basic . $sql_where);
        if (!$rs = pg_query ($con , $sql)) {
            pg_query ($con , 'ROLLBACK');
            $SYSTEM_MESSAGE = "システムエラーが発生しました\n$sql";
            require_once ('../com/'.ERROR_PAGE);
            exit();
        }
        $row = pg_fetch_array($rs);
    }
    return $row;
}

?>
