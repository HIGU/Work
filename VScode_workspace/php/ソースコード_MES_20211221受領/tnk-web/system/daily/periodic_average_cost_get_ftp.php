#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 総平均単価経歴 自動FTP Download cron で処理用       コマンドライン版     //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2010 - 2013 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp  //
// Changed histoy                                                           //
// 2010/02/23 Created  periodic_average_cost_get_ftp.php                    //
// 2012/08/03 四半期時（6・12月）にうまく取り込めなかったのを修正           //
// 2013/06/05 一月分取り忘れの為、年月を直接した。→戻し済み                //
// 2013/10/21 一月分取り忘れの為、年月を直接した。→戻し済み                //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるためcli版)
    // 現在はCLI版のdefault='1', SAPI版のdefault='0'になっている。CLI版のみスクリプトから変更出来る。
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$temp_ym  = date('Ym');                 ///// 仮総平均登録用の日時
///// 対象年月(実行年月の前の月が対象年月）
if (substr($temp_ym,4,2)!=01) {
    $yyyymm = $temp_ym - 1;
} else {
    $yyyymm = $temp_ym - 100;
    $yyyymm = $yyyymm + 11;
}
//// 対象月の取得
$mm = substr($yyyymm,4,2);
/////// 処理報告用 変数 初期化\
$flag2 = '';        // 処理実行フラグ アイテム
// 総平均単価 登録処理 準備作業
$file_orign  = '/home/guest/monthly/W#SGAVE@L.TXT';

///// 前回のデータを削除
//if (file_exists($file_orign)) {
//    unlink($file_orign);
//}
// 総平均単価 登録処理
if(file_exists($file_orign)){           // ファイルの存在チェック
    $row_in = 0;        // insert record number 成功した場合にカウントアップ
    $row_up = 0;        // update record number   〃
    $rec_ok = 0;        // 成功数カウント
    $fp = fopen($file_orign,"r");
    $parts_no      = array();  // 部品No.   アルファベット 9
    $period_ym     = array();  // 総平均年月               6
    $average_cost  = array();  // 総平均単価               10
    $mate_cost     = array();  // 原材料総平均             10
    $out_cost      = array();  // 外注総平均               10
    $manu_cost     = array();  // 工作総平均               10
    $assem_cost    = array();  // 組立総平均               10
    $other_cost    = array();  // その他総平均             10
    $indirect_cost = array();  // 間接費総平均             10
    $rec    = 0;        // レコードNo
    while (!feof($fp)) {            // ファイルのEOFチェック
        $data = fgets($fp,200);     // 実際には120 でOKだが余裕を持って
        $data = mb_convert_encoding($data, "EUC-JP", "auto");       // autoをEUC-JPへ変換
        $parts_no[$rec]      = substr($data,0,9);          // 部品No.
        $period_ym[$rec]     = substr($data,10,6);         // 総平均年月
        $average_cost[$rec]  = substr($data,16,11);        // 総平均単価
        $mate_cost[$rec]     = substr($data,27,11);        // 原材料総平均
        $out_cost[$rec]      = substr($data,38,11);        // 外注総平均
        $manu_cost[$rec]     = substr($data,49,11);        // 工作総平均
        $assem_cost[$rec]    = substr($data,60,11);        // 組立総平均
        $other_cost[$rec]    = substr($data,71,11);        // その他総平均
        $indirect_cost[$rec] = substr($data,82,11);        // 間接費総平均
        $rec++;
    }
    $rec--;             // レコード数の調整 最後のレコードの改行でカウントが１増加するため
    fclose($fp);
    
    /////////// begin トランザクション開始
    if ($con = db_connect()) {
        query_affected_trans($con, "begin");
    } else {
        echo "データベースに接続できません";
        header("Location: http:" . WEB_HOST . "system/system_menu.php");
        exit();
    }
    ///// データベースへの取り込み
    //echo "$parts_no[0]/$period_ym[0]/$average_cost[0]/$mate_cost[0]/$out_cost[0]/$manu_cost[0]/$assem_cost[0]/$other_cost[0]/$indirect_cost[0]/";
    //echo "$log_date/$yyyymm/$temp_ym/";
    $ok_row  = 0;       ///// 取り込み完了レコード数
    $res_chk = array();
    for ($i=0; $i < $rec; $i++) {
        //if ($mm !=3 || $mm !=6 || $mm !=9 || $mm !=12) {
            $period_ym[$i] = $yyyymm;   // 決算月じゃなければ総平均年月は強制入力
            //$period_ym[$i] = 201308;   // 直接入力用
        //}
        $query_chk = sprintf("select parts_no from periodic_average_cost_history2 where parts_no='%s' and period_ym=%d", $parts_no[$i], $period_ym[$i]);
        if (getResult($query_chk,$res_chk) <= 0) {  ///// 既登録済みのチェック
                                // 新規登録
            $query = sprintf("insert into periodic_average_cost_history2 (parts_no, period_ym, average_cost, mate_cost, out_cost, manu_cost, assem_cost, other_cost, indirect_cost) 
                values('%s',%d,%f,%f,%f,%f,%f,%f,%f)",
                $parts_no[$i], $period_ym[$i], $average_cost[$i], $mate_cost[$i], $out_cost[$i], 
                $manu_cost[$i], $assem_cost[$i], $other_cost[$i], $indirect_cost[$i]);
            if(query_affected_trans($con, $query) <= 0){        // 更新用クエリーの実行
                //$NG_row = ($i + 1);
                //echo "データベースの新規登録に失敗しました No$NG_row";
                query_affected_trans($con, "rollback");         // transaction rollback
                ////header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            } else {
                $row_in++;      // insert 成功
                $rec_ok++;      // 成功数カウント
            }
        } else {                // UPDATE
            $query = "UPDATE periodic_average_cost_history2 SET
                            average_cost  = {$average_cost[$i]},
                            mate_cost     = {$mate_cost[$i]},
                            out_cost      = {$out_cost[$i]},
                            manu_cost     = {$manu_cost[$i]},
                            assem_cost    = {$assem_cost[$i]},
                            other_cost    = {$other_cost[$i]},
                            indirect_cost = {$indirect_cost[$i]}
                WHERE parts_no='{$parts_no[$i]}' and period_ym={$period_ym[$i]}";
            if(query_affected_trans($con, $query) <= 0){        // 更新用クエリーの実行
                //$NG_row = ($i + 1);
                //echo "データベースのUPDATEに失敗しました No$NG_row";
                query_affected_trans($con, "rollback");         // transaction rollback
                ////header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            } else {
                $row_up++;      // update 成功
                $rec_ok++;      // 成功数カウント
            }
        }
    }
    $flag2 = 1;
    /////////// commit トランザクション終了
    query_affected_trans($con, "commit");
}
// メッセージを返す
if ($flag2==1) {
    echo "$log_date 総平均データの更新: $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 総平均データの更新: {$row_in}/{$rec} 件 追加 \n";
    echo "$log_date 総平均データの更新: {$row_up}/{$rec} 件 変更 \n";
} else {
    echo "{$log_date} 製品グループコードの更新データがありません。\n";
}
?>
