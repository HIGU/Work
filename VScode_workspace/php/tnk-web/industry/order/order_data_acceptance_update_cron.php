#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 検査の検済で10分毎に巡回し検収日時を登録 自動 Update                     //
// Copyright (C) 2004-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/10/20 Created  order_data_acceptance_update_cron.php                //
// 2005/05/26 php-5.0.2-cli → php へ変更(変更時の版は5.0.4)                //
//            現在の問題点を下記に記述する                                  //
//            1.ken_date(検収日)のみ登録しているので現品数・検収数がない    //
//            2.genpin siharai を登録する場合、検査員に数量を入力してもらう //
//              必要がある。また、order_process order_plan との同期も必要   //
//            3.ある程度時間がくれば AS/400との同期で上記は解消されるが時間 //
//              差は解消しない。この改善のため order_dataのUPDATEをやめて   //
//            4.acceptance_kensa の検査完了時間を見て検査仕掛や検査依頼の   //
//              画面で表示・非表示を制御するように改善する。                //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるためcli版)
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分
require_once ('/var/www/html/function.php');

$log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
$fpa = fopen('/tmp/order_data_acceptance.log', 'a');    ///// 日報用ログファイルへの書込みでオープン
$today = date('Ymd');

/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    exit;
}
////////// order_data の対象レコード検索
$query = "select ord.order_seq
            from
                acceptance_kensa    AS acc
            left outer join
                order_data          AS ord      using(order_seq)
            where
                acc.end_timestamp IS NOT NULL
                and
                (CURRENT_TIMESTAMP - acc.end_timestamp) >= (interval '10 minute')
                and
                ord.ken_date = 0
";
$res = array();
if ( ($rows = getResultTrs($con, $query, $res)) <= 0) {    // トランザクション内での 照会専用クエリー
    fwrite($fpa, "$log_date 対象データなし \n");
    query_affected_trans($con, 'commit');
    exit();
} else {
    for ($i=0; $i<$rows; $i++) {
        $query = "UPDATE order_data SET ken_date={$today} where order_seq={$res[$i][0]}";
        if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
            fwrite($fpa, "$log_date 検収日の登録に失敗:発行連番:{$res[$i][0]}:検収日:{$today}\n");
        }
    }
}
/////////// commit トランザクション終了
query_affected_trans($con, 'commit');
fwrite($fpa, "$log_date 処理完了:対象件数:{$rows} \n");
fclose($fpa);      ////// 日報用ログ書込み終了
exit();
?>
