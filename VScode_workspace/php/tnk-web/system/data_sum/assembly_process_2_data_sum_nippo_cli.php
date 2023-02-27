#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// assembly_process_time TO DATA SUM の日報データ(AS用)へ統合 自動処理cli版 //
// Copyright (C) 2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/10/20 Created  assembly_process_2_data_sum_nippo_cli.php            //
// 2005/12/10 応援者のチェックをし応援開始(９１６)の処理追加                //
// 2007/04/07 ディレクトリを data_sum/ へ ログファイルを共通化 パス方式変更 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', '0');             // echo print で flush させる(遅くなるがメールロジックのため)
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分

$currentFullPathName = realpath(dirname(__FILE__));
require_once ("{$currentFullPathName}/../../function.php");

$log_date = date('Y-m-d H:i:s');            // 日報用ログの日時
$fpa = fopen('/tmp/data_sum.log', 'a');     ///// 日報用ログファイルへの書込みでオープン
// $fpa = fopen('/tmp/assembly_2_dsum_nippo.log', 'a');     // 日報用ログファイルへの書込みでオープン
fwrite($fpa,"$log_date Assembly process time TO DATA SUM nippo Start \n");

// データサムのログファイル 日報処理 準備作業
$file_nippo  = "{$currentFullPathName}/backup/data_sum_nippo.log";
$fpw = fopen($file_nippo, 'a');    // 日報用ファイルのオープン(読込みもする場合は+を後につける)

// 前日のデータ取得
$yesterday = date('Ymd', mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 10);    // 前日の年月日
$str_date = "$yesterday 000000";
$end_date = "$yesterday 235959";
$query = "
    SELECT
        to_char(group_no, 'FM00') AS group_no
        , user_id
        , plan_no
        , to_char(end_time, 'YYMMDDHH24MI') AS end_time
        , to_char(str_time, 'YYMMDDHH24MI') AS str_time
    FROM
        assembly_process_time
    WHERE
        end_time >= '{$str_date}' AND end_time <= '{$end_date}'
    ORDER BY
        group_no ASC
";
$res = array();     // 初期化
if ( ($rows=getResult($query, $res)) > 0 ) {
    for ($i=0; $i<$rows; $i++) {
        ///// データ抽出・変換処理
        $group_no = $res[$i]['group_no'];
        $user_id  = $res[$i]['user_id'];
        $plan_no  = $res[$i]['plan_no'];
        if (substr($plan_no, 0, 1) == '@') $plan_no = 'Z' . substr($plan_no, 1, 7);
        $end_time = $res[$i]['end_time'];
        
        // 応援者のチェック
        if (substr($user_id, 0, 3) == '777') {
            // 応援開始の処理 916
            $str_time = $res[$i]['str_time'];
            $data = "{$group_no}{$str_time}916{$user_id}000000000000000000000000000000000000000000000000000000000000000000000000000000000000{$plan_no}00000        00000        00000        00000        00000        00000        00000        00000        00000        00000000\n";
            ///// 日報ファイルへ書込み
            if (!fwrite($fpw, $data, 300)) {
                fwrite($fpa,"$log_date data_sum_nippo.log Write Error \n");
            }
        }
        
        ///// データサム互換の書式でデータ生成
        $data = "{$group_no}{$end_time}910{$user_id}000000000000000000000000000000000000000000000000000000000000000000000000000000000000{$plan_no}00000        00000        00000        00000        00000        00000        00000        00000        00000        00000000\n";
        
        ///// 日報ファイルへ書込み
        if (!fwrite($fpw, $data, 300)) {
            fwrite($fpa,"$log_date data_sum_nippo.log Write Error \n");
        }
    }
}
fclose($fpw);   ////// 日報書込み終了
fwrite($fpa,"$log_date 対象データは {$rows}件でした。 \n");
fwrite($fpa,"$log_date Assembly process time TO DATA SUM nippo End \n");
fclose($fpa);   ////// 日報用ログ書込み終了
exit();

?>
