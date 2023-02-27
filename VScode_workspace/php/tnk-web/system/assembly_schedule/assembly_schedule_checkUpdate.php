#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 組立日程計画データの生産引当品＠の CHECK UPDATE 暫定版             CLI版 //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/05/22 Created  assembly_schedule_checkUpdate.php                    //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため)
ini_set('max_execution_time', 300);     // 最大実行時間=20分
require_once ('/var/www/html/function.php');

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン

/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'BEGIN');
} else {
    fwrite($fpa, "$log_date 組立日程計画＠ db_connect() error \n");
    exit();
}
fwrite($fpa, "$log_date 組立日程計画＠生産引当分データチェック開始 \n");

/////////// 開始日の算出
$year = date('Y'); $month = date('m');
if ($month == 1) {
    $month = 12;
    $year -= 1;
} else {
    $month -= 1;    // 1ヶ月前
    $month = sprintf('%02d', $month);
}
$startDate = ($year . $month . '01');
/////////// 終了日の算出
$year = date('Y'); $month = date('m');
$month += 3;        // 3ヶ月先
if ($month > 12) {
    $month -= 12;
    $year  += 1;
}
$month = sprintf('%02d', $month);
$endDate = ($year . $month . '01');

/////////// 対象データ抜出し
$query = "
    SELECT plan_no
        , parts_no AS 製品番号
        , substr(midsc, 1, 20) AS 製品名
        , plan-cut_plan AS 計画数
        , kansei
        , line_no
    FROM assembly_schedule
    LEFT OUTER JOIN miitem ON(parts_no=mipn)
    WHERE kanryou>={$startDate} AND kanryou<{$endDate} AND plan_no LIKE '@%' AND assy_site='01111'
    AND (plan-cut_plan-kansei) > 0
";
$res = array();
if ( ($rows=getResultTrs($con, $query, $res)) < 1) {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date 組立日程計画 対照データがありません。$startDate ～ $endDate \n");
    query_affected_trans($con, 'ROLLBACK');
    fclose($fpa);
    exit();
}

/////////// 引当チェック(暫定的に引当の無いものを削除対象にする)
$delCount = 0;
for ($i=0; $i<$rows; $i++) {
    $plan_no = $res[$i][0];
    $query = "
        SELECT parts_no FROM allocated_parts WHERE plan_no='{$plan_no}' LIMIT 1
    ";
    if (getUniResTrs($con, $query, $parts_no) < 1) {
        ///// 引当無しのため削除
        $del_sql = "
            DELETE FROM assembly_schedule WHERE plan_no='{$plan_no}'
        ";
        if (query_affected_trans($con, $del_sql) < 1) {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa, "$log_date 削除に失敗しました。計画番号：$plan_no 製品名：{$res[$i][2]} \n");
        } else {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa, "$log_date 削除しました。計画番号：$plan_no 製品名：{$res[$i][2]} \n");
        }
        $delCount++;
    }
}
$log_date = date('Y-m-d H:i:s');
if ($delCount <= 0) {
    fwrite($fpa, "$log_date 削除対象がありませんでした。 \n");
}
fwrite($fpa, "$log_date 組立日程計画＠生産引当分データチェック終了 \n");

/////////// commit トランザクション終了
query_affected_trans($con, 'COMMIT');
fclose($fpa);      ////// 日報用ログ書込み終了
?>
