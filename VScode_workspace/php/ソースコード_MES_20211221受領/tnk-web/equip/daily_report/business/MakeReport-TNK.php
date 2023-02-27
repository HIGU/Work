<?php 
require_once ('../com/define.php');
require_once ('../com/function.php');
require_once ('../com/mu_date.php');

 ini_set('max_execution_time', 12000);    // 最大実行時間=20分 CLI CGI版

// ----------------------------------------------------- //
// -- テスト用  リリース時には削除                    -- //
// ----------------------------------------------------- //
/*
$con = getConnection();
pg_query ($con , "delete from equip_work_report");
pg_query ($con , "delete from equip_work_report_log");
MakeMachineReportOneDay(1341 ,1,20040624);
//MakeReport();
echo("終わり");

*/
// ----------------------------------------------------- //

// --------------------------------------------------
// 機械運転日報作成メイン処理
// --------------------------------------------------
function MakeReport()
{
    global $con;
    // 排他
    $lock = fopen (DOCUMENT_ROOT.BUSINESS_PATH.'.LOCK','w');
    flock ($lock,LOCK_EX);
    // トランザクション開始
    pg_query ($con , 'BEGIN');
    
    // 機械マスタ取得
    $sql = 'select mac_no,csv_flg from equip_machine_master2 order by mac_no';
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
    $StartDate = $Day.BUSINESS_DAY_CHANGE_TIME;
    $EndDate   = mu_Date::addDay($Day,1).BUSINESS_DAY_CHANGE_TIME;
    
    // 対象日の一番最初に稼働する指示No.を取得
    $sql = "select siji_no from equip_work_log2 "
         . "where date_time = (select min(date_time) from equip_work_log2 where mac_no=$MacNo and date_time >= to_timestamp('$StartDate', 'YYYYMMDDHHMI') and date_time < to_timestamp('$EndDate', 'YYYYMMDDHHMI')) ";
    $rs = pg_query ($con , $sql);
    if($Row  = pg_fetch_array ($rs)) {
        $StartSijiNo = $Row['siji_no'];     // データ有り
    } else {
        $StartSijiNo = 0;                   // データ無し
    }
    $rs = pg_query ($con , $sql);
    
    // 対象日の一番最後に稼働する指示No.を取得
    $sql = "select siji_no from equip_work_log2 "
         . "where date_time = (select max(date_time) from equip_work_log2 where mac_no=$MacNo and date_time >= to_timestamp('$StartDate', 'YYYYMMDDHHMI') and date_time < to_timestamp('$EndDate', 'YYYYMMDDHHMI')) ";
    $rs = pg_query ($con , $sql);
    if($Row  = pg_fetch_array ($rs)) {
        $EndSijiNo = $Row['siji_no'];     // データ有り
    } else {
        $EndSijiNo = 0;                   // データ無し
    }
    
    
    // 抽出ＳＱＬ
    $sql = "select mac_no
                ,to_char(date_time, 'YYYY-MM-DD HH24:MI:SS') as date_time
                ,mac_state,work_cnt,siji_no,koutei from equip_work_log2 "
         . "where  mac_no=$MacNo and date_time >= to_timestamp('$StartDate', 'YYYYMMDDHHMI') and date_time < to_timestamp('$EndDate', 'YYYYMMDDHHMI') "
         . "order by siji_no,date_time ";
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
                MakeZeroReport($MacNo,$Day);
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
                $Log['siji_no']     = $PrevRow['siji_no'];
                $Log['koutei']      = $PrevRow['koutei'];
                $Log['mac_state']   = $PrevRow['mac_state'];
                if ($KeyCode == 1 || $KeyCode == 2) {
                    // ステータスが変わるか、指示，行程番号がかわったら現在のレコードの時刻をセット
                    $Log['ToDate']      = mu_Date::toString($NowRow['date_time'],"Ymd");
                    $Log['ToTime']      = mu_Date::toString($NowRow['date_time'],"Hi");
                }
                // レコードがなくなるて、対象日の一番最後に稼働する指示No.なら翌日までの時刻をセット
                if ($PrevRow['siji_no'] == $EndSijiNo && $KeyCode >= 2) {
                    $Log['ToDate']      = mu_Date::addDay($Log['work_date'] ,1);
                    $Log['ToTime']      = BUSINESS_DAY_CHANGE_TIME;
                }
                
                // 日報ログ書込
                $sql = "insert into equip_work_report_log(work_date,mac_no,siji_no,koutei,mac_state,from_date,from_time,to_date,to_time,cut_time,last_user) values ( "
                     . $Log['work_date']            . ","
                     . $Log['mac_no']               . ","
                     . $Log['siji_no']              . ","
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
            if ($NowRow['siji_no'] == $StartSijiNo && mu_Date::toString($NowRow['date_time'],"Hi") != BUSINESS_DAY_CHANGE_TIME) {
                $sql = " select * from equip_work_log2 where mac_no=$MacNo and date_time = (select max(date_time) from equip_work_log2 where mac_no=$MacNo and date_time < to_timestamp('" . $Day.BUSINESS_DAY_CHANGE_TIME . "','YYYYMMDDHHMI'))";
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
                        $Log['FromTime']            = BUSINESS_DAY_CHANGE_TIME;
                        $PrevSet = true;
                    } else {
                        $Log['work_date']           = $Day;
                        $Log['mac_no']              = $MacNo;
                        $Log['siji_no']             = $logrow['siji_no'];
                        $Log['koutei']              = $logrow['koutei'];
                        $Log['mac_state']           = $logrow['mac_state'];
                        $Log['FromDate']            = $Day;
                        $Log['FromTime']            = BUSINESS_DAY_CHANGE_TIME;
                        $Log['ToDate']              = mu_Date::toString($NowRow['date_time'],"Ymd");
                        $Log['ToTime']              = mu_Date::toString($NowRow['date_time'],"Hi");
                        $Log['CutTime']             = 0;
                        // 日報ログ書込
                        $sql = "insert into equip_work_report_log(work_date,mac_no,siji_no,koutei,mac_state,from_date,from_time,to_date,to_time,cut_time,last_user) values ( "
                             . $Log['work_date']            . ","
                             . $Log['mac_no']               . ","
                             . $Log['siji_no']              . ","
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
                        $PrevSet = false;
                    }
                }
            }
            // --------------------------------------------------
            // ログデータのセット
            // --------------------------------------------------
            $Log['work_date']   = getBusinessDay($NowRow['date_time']);
            $Log['mac_no']      = $MacNo;
            $Log['siji_no']     = $NowRow['siji_no'];
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
            $Report['siji_no']   = $NowRow['siji_no'];
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
    // 指示No.が変わった
    if ($NowRow['siji_no'] != $PrevRow['siji_no']) return 2;
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
// BUSINESS_DAY_CHANGE_TIME を使用
//
// (例)
// 2004/06/01 10:00  -> 2004/06/01
// 2004/06/02  3:00  -> 2004/06/01
// --------------------------------------------------
function getBusinessDay($DateTime)
{
    
    $Date = mu_Date::toString($DateTime,"Ymd");
    $Time = mu_Date::toString($DateTime,"Hi");
    
    if ($Time < BUSINESS_DAY_CHANGE_TIME) {
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
    $sql = " select koutei,parts_no,plan_cnt from equip_work_log2_header where mac_no=".$row['mac_no']." and siji_no=".$row['siji_no']." and koutei=".$row['koutei'];
    $rs = pg_query ($con , $sql);
    if (!$hed =  pg_fetch_array ($rs)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "システムエラーが発生しました\nログヘッダが見つかりませんでした\n\n$sql";
        require_once ('../com/'.ERROR_PAGE);
        exit();
    }
    
    // --------------------------------------------------
    // 前日良品数の取得
    // --------------------------------------------------
    $sql = " select yesterday + today as num from equip_work_report "
         . " where  work_date=".mu_Date::addDay($Report['work_date'],-1)." and mac_no=".$row['mac_no']." and siji_no=".$row['siji_no']." and koutei=".$row['koutei'];
         
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
/*
    $sql = ' SELECT                     '
         . '     item_code,             '
         . '     size,                  '
         . '     use_item,              '
         . '     abandonment,           '
         . '     type,                  '
         . '     weight,                '
         . '     length                 '
         . ' FROM                       '
         . '     equip_work_inst_header '
         . ' left outer join equip_parts on parts_no = item_code'
         . ' left outer join equip_materials on mtcode = use_item'
         . ' WHERE     inst_no = ' . $Report['siji_no'];
*/


    $sql = ' SELECT                     '
         . '     item_code,             '
         . '     size,                  '
         . '     use_item,              '
         . '     abandonment,           '
         . '     type,                  '
         . '     weight,                '
         . '     length                 '
         . ' FROM                       '
         . ' equip_parts'
         . ' left outer join equip_materials on mtcode = use_item'
         . " WHERE     item_code = '" . $hed['parts_no'] . "'";


    if (!$rs = pg_query ($con , $sql)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "システムエラーが発生しました\n$sql";
        require_once ('../com/'.ERROR_PAGE);
        exit();
    }
    if ($row =  pg_fetch_array ($rs)) {
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
                    if ($row2['length'] < $NeedLength) {
                        // 破材だけでは足りない
                        $NeedLength -= $row2['length'];
                        
                        // 投入数  破材つかった足りない長さ / １本当たりの長さ - 破材サイズ
                        $Report['injection'] = (int)($NeedLength / ($row['length']-($row['abandonment']/1000)));
                        // つかった破材分長さ
                        $Report['abandonment'] = Mfloor($NeedLength - ($row['length']-$row['abandonment']/1000) * $Report['injection'] , 4);
                        
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
                    } else {
                        // 破材だけでたりた
                        $Report['abandonment'] = $NeedLength;
                        // 破材マスタ更新 
                        $sql = "update equip_abandonment_item set length=" . ($row2['length'] - $NeedLength) ." , weight= " . ($row2['length'] - $NeedLength) * $row['weight'] ." where item_code='" . $row['use_item'] . "'";
                        pg_query ($con,$sql);
                        // 投入数 破材使ったら０本投入としてカウント
                        $Report['injection'] = 0;
                        // つかった破材分長さ
                        $Report['abandonment'] = 0;
                    }
                } else {
                    // 破材マスタ存在しないとき
                    // 投入数  破材つかった足りない長さ / １本当たりの長さ - 破材サイズ
                    $Report['injection'] = (int)($NeedLength / ($row['length']-($row['abandonment'])/1000));
                    // つかった破材分長さ
                    $Report['abandonment'] = Mfloor($NeedLength - ($row['length']-$row['abandonment']/1000) * $Report['injection'] , 4);
//                  $Report['abandonment'] = $NeedLength - ($row['length']-$row['abandonment']/1000) * $Report['injection'];
                    
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
    $sql = "insert into equip_work_report (work_date,mac_no,siji_no,koutei,yesterday,today,ng,ng_kbn,plan,end_flg,memo,injection_item,injection,abandonment,decision_flg,last_user) values ( "
         .       $Report['work_date']       . ","
         .       $Report['mac_no']          . ","
         .       $Report['siji_no']         . ","
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
    $date = $Day.BUSINESS_DAY_CHANGE_TIME;
    $sql = " select * from equip_work_log2 where mac_no=$MacNo and date_time = (select max(date_time) from equip_work_log2 where mac_no=$MacNo)";
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
    $Report['siji_no']          = $row['siji_no'];
    $Report['koutei']           = $row['koutei'];
    $Report['end_flg']          = '';
    $Report['memo']             = '';
    $Report['injection_item']   = '';
    $Report['injection']        = 0;
    $Report['abandonment']      = 0;
    
    $Log['work_date']           = $Day;
    $Log['mac_no']              = $MacNo;
    $Log['siji_no']             = $row['siji_no'];
    $Log['koutei']              = $row['koutei'];
    $Log['mac_state']           = $row['mac_state'];
    $Log['FromDate']            = $Day;
    $Log['FromTime']            = BUSINESS_DAY_CHANGE_TIME;
    $Log['ToDate']              = mu_Date::addDay($Day ,1);
    $Log['ToTime']              = BUSINESS_DAY_CHANGE_TIME;
    $Log['CutTime']             = 0;


    // --------------------------------------------------
    // 前日良品数の取得
    // --------------------------------------------------
    $sql = " select yesterday + today as num from equip_work_report "
         . " where  work_date=".mu_Date::addDay($Report['work_date'],-1)." and mac_no=".$row['mac_no']." and siji_no=".$row['siji_no']." and koutei=".$row['koutei'];
         
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

    $sql = ' SELECT                     '
         . '     use_item               '
         . ' FROM                       '
         . ' equip_work_log2_header a   '
         . ' left outer join  equip_parts b on a.parts_no=b.item_code '
         . ' WHERE a.mac_no =' . $Report['mac_no'] . ' and a.siji_no =' . $Report['siji_no'] . ' and a.koutei=' .$Report['koutei']; 
         
    $rs = pg_query ($con , $sql);
    if ($row =  pg_fetch_array ($rs)) {
        $Report['injection_item']   = $row['use_item'];
    }

    
    // ＳＱＬ
    $sql = "insert into equip_work_report (work_date,mac_no,siji_no,koutei,yesterday,today,ng,ng_kbn,plan,end_flg,memo,injection_item,injection,abandonment,decision_flg,last_user) values ( "
         .       $Report['work_date']       . ","
         .       $Report['mac_no']          . ","
         .       $Report['siji_no']         . ","
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
    $sql = "insert into equip_work_report_log(work_date,mac_no,siji_no,koutei,mac_state,from_date,from_time,to_date,to_time,cut_time,last_user) values ( "
         . $Log['work_date']            . ","
         . $Log['mac_no']               . ","
         . $Log['siji_no']              . ","
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
    
    $sql = "select max(work_date) as work_date from equip_work_report where mac_no=$MacNo";
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
    if ($Time < BUSINESS_DAY_CHANGE_TIME) {
        $Date = mu_Date::addDay($Date,-1);
    }
    
    $Date = mu_Date::addDay($Date,-1);
    
    return $Date;

}

?>
