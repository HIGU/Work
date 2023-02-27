<?php
//////////////////////////////////////////////////////////////////////////////
// 総材料費の比較 CSV出力                                                   //
// Copyright (C) 2011 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2011/05/26 Created   material_compare_csv.php                            //
// 2011/05/30 総材料費の比較を別メニューにまとめた為require_onceのリンク変更//
// 2011/05/31 グループコード変更に伴いSQL文を変更                           //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php'); // TNK 全共通 MVC Controller Class

// CSVデータ作成用の初期データを貰う
$div        = $_GET['csv_div'];
$first_ym   = $_GET['csv_first_ym'];
$second_ym  = $_GET['csv_second_ym'];
$assy_no    = $_GET['csv_assy_no'];
$order      = $_GET['csv_order'];

$cost1_ym = $first_ym;
$cost2_ym = $second_ym;

$nen        = substr($cost1_ym, 0, 4);
$tsuki      = substr($cost1_ym, 4, 2);
$cost1_name = $nen . "/" . $tsuki;

$nen        = substr($cost2_ym, 0, 4);
$tsuki      = substr($cost2_ym, 4, 2);
$cost2_name = $nen . "/" . $tsuki;

if (substr($cost1_ym,4,2)!=12) {
    $cost1_ymd = $cost1_ym + 1;
    $cost1_ymd = $cost1_ymd . '10';
} else {
    $cost1_ymd = $cost1_ym + 100;
    $cost1_ymd = $cost1_ymd - 11;
    $cost1_ymd = $cost1_ymd . '10';
}
if (substr($cost2_ym,4,2)!=12) {
    $cost2_ymd = $cost2_ym + 1;
    $cost2_ymd = $cost2_ymd . '10';
} else {
    $cost2_ymd = $cost2_ym + 100;
    $cost2_ymd = $cost2_ymd - 11;
    $cost2_ymd = $cost2_ymd . '10';
}

$str_ymd = $second_ym - 300;
$str_ymd = $str_ymd . '01';
$end_ymd = $second_ym . '31';

if ($div == "C") {
    if ($second_ym < 200710) {
        $rate = 25.60;  // カプラ標準 2007/10/01価格改定以前
    } elseif ($second_ym < 201104) {
        $rate = 57.00;  // カプラ標準 2007/10/01価格改定以降
    } else {
        $rate = 45.00;  // カプラ標準 2011/04/01価格改定以降
    }
} elseif ($div == "L") {
    if ($second_ym < 200710) {
        $rate = 37.00;  // リニア 2008/10/01価格改定以前
    } elseif ($second_ym < 201104) {
        $rate = 44.00;  // リニア 2008/10/01価格改定以降
    } else {
        $rate = 53.00;  // リニア 2011/04/01価格改定以降
    }
} else {
    $rate = 65.00;
}

///////// 掛率判定値
///////// 掛率が一定ではなくなったら表示部のロジックも変更する。
$power_rate = 1.13;      // 2011/04/01移行

if ($order == 'assy') {
    $order_name = 'ORDER BY 製品番号 ASC';
} elseif ($order == 'diff') {
    $order_name = 'ORDER BY 材料費増減 DESC, 率％ DESC, 照会順 ASC, 中分類名 ASC, 製品番号 ASC';
} elseif ($order == 'per') {
    $order_name = 'ORDER BY 率％ DESC, 材料費増減 DESC, 照会順 ASC, 中分類名 ASC, 製品番号 ASC';
} elseif ($order == 'power') {
    $order_name = 'ORDER BY 掛率 DESC, 率％ DESC, 材料費増減 DESC, 照会順 ASC, 中分類名 ASC, 製品番号 ASC';
} elseif ($order == 'sorder') {
    $order_name = 'ORDER BY 照会順 ASC, 中分類名 ASC, 製品番号 ASC';
} else {
    $order_name = 'ORDER BY 製品番号 ASC';
}

//////////// ファイル名の設定
//////////// 対象年月の表示データ編集
$end_y = substr($second_ym,0,4);
$end_m = substr($second_ym,4,2);
$str_y = substr($second_ym,0,4) - 3;
$str_m = substr($second_ym,4,2);

if ($div == "C") {
    $cap_div= "カプラ標準品"; 
} elseif ($div == "L") {
    $cap_div= "リニア"; 
}

// CSVファイル名を作成（第１基準日-第２基準日-事業部）
$outputFile = $cost1_ym . '-' . $cost2_ym . '-' . $cap_div . '総材料費差額.csv';

// 実行者のパソコンにCSVを保存する為、ファイル名の文字コードをSJISに変換
$outputFile = mb_convert_encoding($outputFile, 'SJIS', 'auto');   // CSV用にEUCからSJISへ文字コード変換

//////////// 対象データの取得
$query_csv = "
    SELECT
        u.assyno                    AS 製品番号 --- 0
        ,
        trim(substr(m.midsc,1,40))  AS 製品名   --- 1
        ,
        CASE
            WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
            ELSE (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
        END                         AS 第１総材料費 --- 2
        ,
        CASE
            WHEN (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN '----------'
            ELSE (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
        END
                                    AS 第１登録日 --- 3
        ,
        CASE
            WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
            ELSE (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
        END                         AS 第２総材料費 --- 4
        ,
        CASE
            WHEN (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN '----------'
            ELSE (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
        END
                                    AS 第２登録日 --- 5
        ,
        CASE
            WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
            ELSE 
                CASE
                    WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                    ELSE (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                          - (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                END
        END                         AS 材料費増減   --- 6
        ,
        CASE
            WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
            ELSE 
                 CASE
                    WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                    ELSE Uround(((SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                          - (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)) 
                          / (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1), 4) * 100
                 END
        END                         AS 増減率％         --- 7
        ,
        CASE
            WHEN (SELECT price FROM sales_price_nk WHERE parts_no = u.assyno ORDER BY parts_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
            ELSE (SELECT price FROM sales_price_nk WHERE parts_no = u.assyno ORDER BY parts_no DESC, regdate DESC LIMIT 1)
        END                         AS 最新仕切     --- 8
        ,
        CASE
            WHEN (SELECT price FROM sales_price_nk WHERE parts_no = u.assyno ORDER BY parts_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
            ELSE 
                 CASE
                    WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                    ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = u.assyno ORDER BY parts_no DESC, regdate DESC LIMIT 1)
                          /(SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1), 2)
                 END
        END                         AS 掛率％         --- 9
        ,
        CASE
            WHEN tgrp.top_name IS NULL THEN '------'
            ELSE tgrp.top_name
        END                         AS 大分類名     --- 10
        ,
        CASE
            WHEN mgrp.group_name IS NULL THEN '------'
            ELSE mgrp.group_name               
        END                         AS 中分類名     --- 11
        ---------------- リスト外 -----------------
        ,
        tgrp.s_order                AS 照会順         -- 12
    FROM
          hiuuri AS u
    LEFT OUTER JOIN
          assembly_schedule AS a
    ON (u.計画番号 = a.plan_no)
    LEFT OUTER JOIN
          miitem AS m
    ON (u.assyno = m.mipn)
    LEFT OUTER JOIN
          material_old_product AS mate
    ON (u.assyno = mate.assy_no)
    LEFT OUTER JOIN
          mshmas AS mas
    ON (u.assyno = mas.mipn)
    LEFT OUTER JOIN
          mshmas AS hmas
    ON (u.assyno = hmas.mipn)
    LEFT OUTER JOIN
          -- mshgnm AS gnm
          msshg3 AS gnm
    -- ON (hmas.mhjcd = gnm.mhgcd)
    ON (hmas.mhshc = gnm.mhgcd)
    LEFT OUTER JOIN
          product_serchGroup AS mgrp
    ON (gnm.mhggp = mgrp.group_no)
    LEFT OUTER JOIN
          product_top_serchgroup AS tgrp
    ON (mgrp.top_code = tgrp.top_no)
    WHERE 計上日 >= {$str_ymd} AND 計上日 <= {$end_ymd} AND 事業部 = '{$div}' AND (note15 NOT LIKE 'SC%%' OR note15 IS NULL) AND datatype='1'
        AND mate.assy_no IS NULL
        -- これを追加すれば自動機の登録があるもの AND (SELECT a_rate FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) > 0
    GROUP BY u.assyno, m.midsc, tgrp.top_name, mgrp.group_name, tgrp.s_order
    {$order_name}
    OFFSET 0 LIMIT 10000
";

$res_csv   = array();
$field_csv = array();
if (($rows_csv = getResultWithField3($query_csv, $field_csv, $res_csv)) <= 0) {
    //$_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>売上明細のデータがありません。<br>%s～%s</font>", format_date($d_start), format_date($d_end) );
    //header('Location: ' . H_WEB_HOST . $menu->out_RetUrl() . '?sum_exec=on');    // 直前の呼出元へ戻る
    exit();
} else {
    $num_csv = count($field_csv);       // フィールド数取得
    for ($r=0; $r<$rows_csv; $r++) {
        //$res_csv[$r][4] = mb_convert_kana($res_csv[$r][4], 'ka', 'UTF-8');   // 全角カナを半角カナへテスト的にコンバート
        $res_csv[$r][1]  = str_replace(',',' ',$res_csv[$r][1]);                   // 製品名に,が入っているとCSVで桁がずれるので半角スペースに
        $res_csv[$r][10] = str_replace(',',' ',$res_csv[$r][10]);                  // 製品名に,が入っているとCSVで桁がずれるので半角スペースに
        $res_csv[$r][11] = str_replace(',',' ',$res_csv[$r][11]);                  // 製品名に,が入っているとCSVで桁がずれるので半角スペースに
        $res_csv[$r][1] = mb_convert_encoding($res_csv[$r][1], 'SJIS', 'EUC');     // CSV用にEUCからSJISへ文字コード変換(EUC指定じゃないと直納・調整が化ける)
        $res_csv[$r][10] = mb_convert_encoding($res_csv[$r][10], 'SJIS', 'EUC');   // CSV用にEUCからSJISへ文字コード変換(EUC指定じゃないと直納・調整が化ける)
        $res_csv[$r][11] = mb_convert_encoding($res_csv[$r][11], 'SJIS', 'EUC');   // CSV用にEUCからSJISへ文字コード変換(EUC指定じゃないと直納・調整が化ける)
        
    }
    //$_SESSION['SALES_TEST'] = sprintf("order by 計上日 offset %d limit %d", $offset, PAGE);
    $i = 1;                             // CSV書き出し用カウント（フィールド名が0に入るので１から）
    $csv_data = array();                // CSV書き出し用配列
    for ($s=0; $s<$num_csv-1; $s++) {     // フィールド名をCSV書き出し用配列に出力
        $field_csv[$s]   = mb_convert_encoding($field_csv[$s], 'SJIS', 'auto');
        $csv_data[0][$s] = $field_csv[$s];
    }
    for ($r=0; $r<$rows_csv; $r++) {    // データをCSV書き出し用配列に出力
        for ($s=0; $s<$num_csv-1; $s++) {
            $csv_data[$i][$s]  = $res_csv[$r][$s];
        }
        $i++;
    }
}

// ここからがCSVファイルの作成（一時ファイルをサーバーに作成）
//$outputFile = 'csv/' . $d_start . '-' . $d_end . '.csv';
//$outputFile = 'csv/' . $d_start . '-' . $d_end . '-' . $act_name . '.csv';
//$outputFile = "test.csv";
touch($outputFile);
$fp = fopen($outputFile, "w");

foreach($csv_data as $line){
    fputcsv($fp,$line);         // ここでCSVファイルに書き出し
}
fclose($fp);
//$outputFile = $d_start . '-' . $d_end . '.csv';
//$outputFile = $d_start . '-' . $d_end . '-' . $act_name . '.csv';

// ここからがCSVファイルのダウンロード（サーバー→クライアント）
touch($outputFile);
header("Content-Type: application/csv");
header("Content-Disposition: attachment; filename=".$outputFile);
header("Content-Length:".filesize($outputFile));
readfile($outputFile);
unlink("{$outputFile}");         // ダウンロード後ファイルを削除
?>