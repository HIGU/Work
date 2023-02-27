#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 特注標準配賦率計算・登録 cron.d tnk_daily 処理で実行                     //
// 計算ルール �〜鞍彰�の買掛と標準部品番号を抜き出し                        //
//            �∩鞍彰�の特注・標準出庫量を抜出し、特注の出庫率を計算        //
//             （１）始めに対象月(前半期最終月)で特注率が無いものを抜出     //
//                  （A）あれば部品番号は登録済みなので先へ                 //
//                  （B）なければ部品番号抜出しへ                           //
//                       (a) 部品番号を抜出し、特注再チェック後登録         //
//             （２）登録されている部品番号で特注率が無いものを抜出し       //
//                   この時2000件まで（処理が重いので３日くらい掛けてやる） //
//             （３）対象部品で出庫率計算。特注の出庫がなければ番号削除     //
// Copyright (C) 2017-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2017/11/13 Created   ctoku_allocation_cal.php                            //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
//ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため)
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "特注標準配賦率計算・更新\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/ctoku_allocation_cal.php\n");
echo "/home/www/html/tnk-web/system/daily/ctoku_allocation_cal.php\n";

/////////// 日付データの取得
$target_ym   = date('Ym');          //201710
$b_target_ym = $target_ym - 100;    //201610
$today       = date('Ymd');         //20171012
$b_today     = $today - 10000;      //20161012

// 前半期のデータを取得
$end_mm  = substr($target_ym, -2, 2);
$end_yy  = substr($target_ym,  0, 4);
$end_mm  = $end_mm * 1;
if ($end_mm > 9) {          // 下期(10〜12月)の場合
    $str_ym  = $end_yy . '04';
    $str_ymd = $str_ym . '01';
    $str_ym  = $str_ym * 1;
    $str_ymd = $str_ymd * 1;
    $end_ym  = $end_yy . '09';
    $end_ymd = $end_ym . '31';
    $end_ym  = $end_ym * 1;
    $end_ymd = $end_ymd * 1;
} elseif ($end_mm < 4)  {   // 下期(1〜3月)の場合
    $end_yy  = $end_yy * 1;
    $str_ym  = $end_yy - 1 . '04';
    $str_ymd = $str_ym . '01';
    $str_ym  = $str_ym * 1;
    $str_ymd = $str_ymd * 1;
    $end_ym  = $end_yy - 1 . '09';
    $end_ymd = $end_ym . '31';
    $end_ym  = $end_ym * 1;
    $end_ymd = $end_ymd * 1;
} else {                    // 上期の場合
    $end_ym  = $end_yy . '03';
    $end_ymd = $end_ym . '31';
    $end_ym  = $end_ym * 1;
    $end_ymd = $end_ymd * 1;
    $end_yy  = $end_yy * 1;
    $str_ym  = $end_yy - 1 . '10';
    $str_ymd = $str_ym . '01';
    $str_ym  = $str_ym * 1;
    $str_ymd = $str_ymd * 1;
    
}
/* テスト用
$str_ym  = 201704;
$str_ymd = 20170401;
$end_ym  = 201709;
$end_ymd = 20170931;
*/
/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, "begin");
} else {
    echo "$log_date 特注標準配賦率 db_connect() error \n";
    fwrite($fpa,"$log_date 特注標準配賦率 db_connect() error \n");
    fwrite($fpb,"$log_date 特注標準配賦率 db_connect() error \n");
    exit();
}
$query_g = sprintf("SELECT parts_no FROM inventory_ctoku_par WHERE ctoku_ym=%d and ctoku_allo is NULL", $end_ym);
$res_g   = array();
$field_g = array();
if (($rows_g = getResultWithField3($query_g, $field_g, $res_g)) > 0) {
} else {
    $query_g2 = sprintf("SELECT parts_no FROM inventory_ctoku_par WHERE ctoku_ym=%d", $end_ym);
    $res_g2   = array();
    $field_g2 = array();
    if (($rows_g2 = getResultWithField3($query_g2, $field_g2, $res_g2)) > 0) {
        exit();
    } else {
        // 対象部品の取得
        $query_t = getQueryStatement1($end_ym, $str_ym, $end_ymd, $str_ymd);
        $res_t   = array();
        $field_t = array();
        if (($rows_t = getResultWithField3($query_t, $field_t, $res_t)) > 0) {
            for ($r=0; $r<$rows_t; $r++) {
                $query_u = getQueryStatement4($res_t[$r][0], $end_ym, $str_ym);
                $res_u   = array();
                $field_u = array();
                if (($rows_u = getResultWithField3($query_u, $field_u, $res_u)) > 0) {
                    // チェックでデータがあれば特注の為、なにもしない
                } else {
                    // チェックでデータが無いので特注、DB登録
                    $query = sprintf("INSERT INTO inventory_ctoku_par (parts_no, ctoku_ym) values('%s', %d)", $res_t[$r][0], $end_ym);
                    query_affected_trans($con, $query);
                }
            }
        }
    }
}

$query_p = sprintf("SELECT parts_no FROM inventory_ctoku_par WHERE ctoku_ym=%d and ctoku_allo is NULL limit 2000", $end_ym);
$res_p   = array();
$field_p = array();
if (($rows_p = getResultWithField3($query_p, $field_p, $res_p)) > 0) {
    for ($r=0; $r<$rows_p; $r++) {
        // 特注再チェック
        $query_s = getQueryStatement2($res_p[$r][0], $end_ymd, $str_ymd);
        $res_s   = array();
        $field_s = array();
        if (($rows_s = getResultWithField3($query_s, $field_s, $res_s)) > 0) {
            $query_h = getQueryStatement3($res_p[$r][0], $end_ymd, $str_ymd);
            $res_h   = array();
            $field_h = array();
            if (($rows_h = getResultWithField3($query_h, $field_h, $res_h)) > 0) {
                $toku_num   = 0;
                $hyo_num    = 0;
                $total_num  = 0;
                $toku_ritsu = 0;
                
                $toku_num  = $res_s[0][0];
                $hyo_num   = $res_h[0][0];
                $total_num = $toku_num + $hyo_num;
                if ($toku_num > 0) {
                    $toku_ritsu = round(($toku_num / $total_num),5);
                    ////////// Insert Start
                    $query = sprintf("UPDATE inventory_ctoku_par SET total_num=%d, ctoku_num=%d, hyo_num=%d, ctoku_allo=%1.4f WHERE parts_no='%s' and ctoku_ym=%d", $total_num, $toku_num, $hyo_num, $toku_ritsu, $res_p[$r][0], $end_ym);
                    //$query = sprintf("INSERT INTO inventory_ctoku_par (parts_no, ctoku_num) VALUES ('%s', %d)", $res_t[$r][0], $toku_num);
                    query_affected_trans($con, $query);
                } else {
                    $query = sprintf("DELETE FROM inventory_ctoku_par WHERE parts_no='%s' and ctoku_ym=%d", $res_p[$r][0], $end_ym);
                    query_affected_trans($con, $query);
                }
            }
        }
    }
}
/////////// commit トランザクション終了
query_affected_trans($con, "commit");
// echo $query . "\n";
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了

exit();

    ///// List部   一覧表のSQLステートメント取得
    // 対象部品番号の一覧を取得
    function getQueryStatement1($target_ym, $b_target_ym, $today, $b_today)
    {
        $query = "SELECT
                            DISTINCT a.parts_no    as 部品番号        -- 04
                        FROM
                            act_payable AS a
                        LEFT OUTER JOIN 
                            order_plan AS p
                                using(sei_no)
                        LEFT OUTER JOIN
                            miitem ON (a.parts_no = mipn)
                        LEFT OUTER JOIN
                            parts_stock_master AS m ON (m.parts_no=a.parts_no)
                        WHERE act_date>={$b_today} and act_date<={$today} and a.div='C' and a.parts_no<>'' and
                                a.parts_no not like '##%' and LENGTH(a.parts_no)=9 and (a.parts_no like 'C%' or a.parts_no like 'S%') and p.kouji_no NOT like 'SC%%'
                  UNION
                  SELECT
                            DISTINCT inv.parts_no                as 部品番号        -- 0
                        FROM
                            inventory_monthly as inv
                        LEFT OUTER JOIN
                            inventory_monthly_ctoku as o
                                on inv.parts_no = o.parts_no
                        WHERE inv.invent_ym>={$b_target_ym} and inv.invent_ym<={$target_ym} and inv.num_div='5' and o.parts_no is null
        ";
        return $query;
    }
    // 部品毎の特注出庫数を計算
    function getQueryStatement2($parts_no, $today, $b_today)
    {
        $query = "SELECT 
                        SUM(p.stock_mv) 
                    FROM parts_stock_history as p
                         left outer join assembly_schedule as s
                            on(p.plan_no=s.plan_no) 
                    WHERE p.parts_no='{$parts_no}' and p.ent_date>={$b_today} and p.ent_date <={$today}
                          and p.out_id <>'' and p.plan_no like 'C%' and s.note15 like 'SC%'
        ";
        return $query;
    }
    // 部品毎の標準出庫数を計算
    function getQueryStatement3($parts_no, $today, $b_today)
    {
        $query = "SELECT 
                        SUM(p.stock_mv) 
                    FROM parts_stock_history as p
                         left outer join assembly_schedule as s
                            on(p.plan_no=s.plan_no) 
                    WHERE p.parts_no='{$parts_no}' and p.ent_date>={$b_today} and p.ent_date <={$today}
                          and p.out_id <>'' and p.plan_no like 'C%' and s.note15 not like 'SC%'
        ";
        return $query;
    }
    // 特注在庫再チェック
    function getQueryStatement4($parts_no, $today, $b_today)
    {
        $query = "SELECT 
                        *
                    FROM inventory_ctoku_par as inv
                         LEFT OUTER JOIN inventory_monthly_ctoku as o
                            on inv.parts_no = o.parts_no
                    WHERE inv.parts_no='{$parts_no}' and inv.ctoku_ym={$today} and o.invent_ym>={$b_today} and o.invent_ym <={$today}
                          and o.parts_no is not NULL
        ";
        return $query;
    }
?>
