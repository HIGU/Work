#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 受入検査の検査中が休み時間またいだ場合、強制的に中断にする処理     CLI版 //
// 休み時間の終了時間ジャストに起動させると自動実行する                     //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/09/04 Created  inspection_recess_time_update_cli.php                //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため)

function main()
{
    $currentFullPathName = realpath(dirname(__FILE__));
    require_once ("{$currentFullPathName}/../../../function.php");
    // require_once ('/var/www/html/function.php');
    
    /////////// begin トランザクション開始
    if ($con = db_connect()) {
        query_affected_trans($con, 'BEGIN');
    } else {
        logWriter('db_connect() error');
        fclose($fpa);
        return;
    }
    /********** 対象データ抽出し更新 処理 **********/
    if ( ($rows=getOverlappsInspection($con, $res)) <= 0) {
        logWriter('休み時間にオーバーラップしている検査中はありません！');
        return;
    }
    
    /////////// commit トランザクション終了
    query_affected_trans($con, 'COMMIT');
    return;
}
main();
exit();


/********** 中断開始の処理 **********/
function logWriter($message)
{
    $fpa = fopen('/tmp/timepro.log', 'a');  // 日報用ログファイルへの書込みでオープン
    $log_date = date('Y-m-d H:i:s');        // 日報用ログの日時
    fwrite($fpa, "{$log_date} {$message}\n");
    fclose($fpa);
    return;
}

/********** 対象データ抽出 処理 **********/
function getOverlappsInspection($con, &$res)
{
    ///// 現在の時刻より休み時間を選択
    $endTime = date('H:i:00');
    switch ($endTime) {
    case '12:45:00':
        $strTime = '12:00:00';
        break;
    case '15:10:00':
        $strTime = '15:00:00';
        break;
    case '17:30:00':
        $strTime = '17:15:00';
        break;
    default:
        logWriter('休み時間ではありません！');
        return 0;
        ///// 以下はテスト用 上記２行をコメントにして以下の時間を設定する。
        // $strTime = '15:00:00';
        // $endTime = '15:10:00';
    }
    /********** 現在の検査中データを抽出 **********/
    $query = "
        SELECT ken.order_seq, ken.uid FROM acceptance_kensa AS ken
        WHERE end_timestamp IS NULL
        AND str_timestamp IS NOT NULL
        AND (SELECT str_timestamp FROM inspection_holding WHERE order_seq = ken.order_seq AND str_timestamp IS NOT NULL AND end_timestamp IS NULL LIMIT 1)
            IS NULL
        AND CAST(str_timestamp AS TIME) <= TIME '{$strTime}'
    ";
    if ( ($rows=getResultTrs($con, $query, $res)) <= 0) {
        return $rows;
    }
    /********** 休み時間を中断時間として更新 **********/
    for ($i=0; $i<$rows; $i++) {
        $date = date('Ymd');
        $sql = "
            INSERT INTO inspection_holding (order_seq, str_timestamp, end_timestamp, client, uid)
            VALUES ({$res[$i][0]}, '{$date} {$strTime}', '{$date} {$endTime}', 'SYSTEM', '{$res[$i][1]}')
        ";
        if (query_affected_trans($con, $sql) <= 0) {
            logWriter("休み時間の中断時間更新に失敗しました。発行連番={$res[$i][0]} ユーザー={$res[$i][1]}");
        } else {
            logWriter("休み時間の中断時間を更新しました。発行連番={$res[$i][0]} ユーザー={$res[$i][1]}");
        }
    }
    return $rows;
}


?>
