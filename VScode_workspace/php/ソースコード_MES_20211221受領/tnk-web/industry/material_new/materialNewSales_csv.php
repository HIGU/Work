<?php
//////////////////////////////////////////////////////////////////////////////
// 仕切単価影響額の照会 CSV出力                                             //
// Copyright (C) 2010 - 2012 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2010/05/21 Created   materialNewSales_csv.php                            //
// 2011/07/11 基準年月に総材料費の登録がなかった場合、同じ計画NOのデータか  //
//            指定年月日の最終月のデータを表示するように変更                //
// 2012/03/13 現掛率の計算が画面表示と違っていた為修正                      //
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
require_once ('../../ControllerHTTP_Class.php');// TNK 全共通 MVC Controller Class

// ファイル名とSQLのサーチ部を受け取る
$outputFile = $_GET['csvname'];
$csv_search = $_GET['csvsearch'];
$target_ym  = $_GET['targetym'];
$second_ym  = $_GET['secondym'];
// SQLのサーチ部で一時変更した部分を元に戻す
$search     = str_replace('keidate','計上日',$csv_search);
$search     = str_replace('jigyou','事業部',$search);
$search     = str_replace('/','\'',$search);
// サーチ部の文字コードをEUCに変更する（念のため）
$search     = mb_convert_encoding($search, 'EUC-JP', 'auto');   // CSV用にEUCからSJISへ文字コード変換

// ファイル名で一時変更した部分を元に戻す
$outputFile     = str_replace('C-hyou','カプラ標準',$outputFile);
$outputFile     = str_replace('L-all','リニア全体',$outputFile);
$outputFile     = str_replace('L-hyou','リニアのみ',$outputFile);
$outputFile     = str_replace('L-bimor','バイモル',$outputFile);
$outputFile     = str_replace('Tool','ツール',$outputFile);

// 実行者のパソコンにCSVを保存する為、ファイル名の文字コードをSJISに変換
$outputFile = mb_convert_encoding($outputFile, 'SJIS', 'auto');   // CSV用にEUCからSJISへ文字コード変換

//////////// CSV出力用のデータ出力
$query_csv = sprintf("select
                            u.計上日        as 計上日,                  -- 0
                            CASE
                                WHEN trim(u.計画番号)='' THEN '---'         --NULLでなくてスペースで埋まっている場合はこれ！
                                ELSE u.計画番号
                            END                     as 計画番号,        -- 1
                            CASE
                                WHEN trim(u.assyno) = '' THEN '---'
                                ELSE u.assyno
                            END                     as 製品番号,        -- 2
                            CASE
                                WHEN trim(substr(m.midsc,1,25)) = '' THEN '-----'
                                ELSE substr(m.midsc,1,38)
                            END             as 製品名,                  -- 3
                            u.数量          as 数量,                    -- 4
                            u.単価          as 仕切単価,                -- 5
                            Uround(u.数量 * u.単価, 0) as 金額,       -- 6
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) = 0 THEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=u.assyno order by assy_no DESC, regdate DESC limit 1)
                                ELSE sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                            END                     as 総材料費,        -- 7
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) = 0 THEN Uround(u.単価 / ((select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=u.assyno order by assy_no DESC, regdate DESC limit 1)), 2)
                                ELSE Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 2)
                            END                     as 現掛率,            -- 8
                            CASE
                                WHEN (SELECT cost_new FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$target_ym} limit 1) IS NULL 
                                    THEN CASE
                                            WHEN (SELECT cost_new FROM sales_price_new WHERE parts_no=u.assyno AND plan_no=u.計画番号 limit 1) IS NULL THEN (SELECT cost_new FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$second_ym} limit 1)
                                            ELSE (SELECT cost_new FROM sales_price_new WHERE parts_no=u.assyno AND plan_no=u.計画番号 limit 1)
                                         END
                                ELSE (SELECT cost_new FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$target_ym} limit 1)
                            END                     AS 基準総材,        -- 9
                            CASE
                                WHEN (SELECT credit_per FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$target_ym} limit 1) IS NULL 
                                    THEN CASE
                                            WHEN (SELECT credit_per FROM sales_price_new WHERE parts_no=u.assyno AND plan_no=u.計画番号 limit 1) IS NULL THEN (SELECT credit_per FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$second_ym} limit 1)
                                            ELSE (SELECT credit_per FROM sales_price_new WHERE parts_no=u.assyno AND plan_no=u.計画番号 limit 1)
                                         END
                                ELSE (SELECT credit_per FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$target_ym} limit 1)
                            END                     AS 掛率,            --10
                            CASE
                                WHEN (SELECT new_price FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$target_ym} limit 1) IS NULL 
                                    THEN CASE
                                            WHEN (SELECT new_price FROM sales_price_new WHERE parts_no=u.assyno AND plan_no=u.計画番号 limit 1) IS NULL THEN (SELECT new_price FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$second_ym} limit 1)
                                            ELSE (SELECT new_price FROM sales_price_new WHERE parts_no=u.assyno AND plan_no=u.計画番号 limit 1)
                                         END
                                ELSE (SELECT new_price FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$target_ym} limit 1)
                            END                     AS 改定仕切,        --11
                            CASE
                                WHEN (SELECT new_price FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$target_ym} limit 1) IS NULL 
                                    THEN CASE
                                            WHEN (SELECT new_price FROM sales_price_new WHERE parts_no=u.assyno AND plan_no=u.計画番号 limit 1) IS NULL THEN Uround(u.数量 * ((SELECT new_price FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$second_ym} limit 1)), 0)
                                            ELSE Uround(u.数量 * ((SELECT new_price FROM sales_price_new WHERE parts_no=u.assyno AND plan_no=u.計画番号 limit 1)), 0)
                                         END               
                                ELSE Uround(u.数量 * ((SELECT new_price FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$target_ym} limit 1)), 0)
                            END                     AS 改定金額,        --12
                            CASE
                                WHEN (SELECT new_price FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$target_ym} limit 1) IS NULL 
                                    THEN CASE
                                            WHEN (SELECT new_price FROM sales_price_new WHERE parts_no=u.assyno AND plan_no=u.計画番号 limit 1) IS NULL THEN (Uround(u.数量 * ((SELECT new_price FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$second_ym} limit 1)), 0)) - (Uround(u.数量 * u.単価, 0))
                                            ELSE (Uround(u.数量 * ((SELECT new_price FROM sales_price_new WHERE parts_no=u.assyno AND plan_no=u.計画番号 limit 1)), 0)) - (Uround(u.数量 * u.単価, 0))
                                         END                               
                                ELSE (Uround(u.数量 * ((SELECT new_price FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$target_ym} limit 1)), 0)) - (Uround(u.数量 * u.単価, 0))
                            END                     AS 差額             --13
                      from
                            hiuuri as u
                      left outer join
                            assembly_schedule as a
                      on u.計画番号=a.plan_no
                      left outer join
                            miitem as m
                      on u.assyno=m.mipn
                      left outer join
                            material_cost_header as mate
                      on u.計画番号=mate.plan_no
                      LEFT OUTER JOIN
                            sales_parts_material_history AS pmate
                      ON (u.assyno=pmate.parts_no AND u.計上日=pmate.sales_date)
                      %s
                      order by 計上日, assyno
                      ", $search);   // 共用 $search で検索

$res_csv   = array();
$field_csv = array();
if (($rows_csv = getResultWithField3($query_csv, $field_csv, $res_csv)) <= 0) {
    //$_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>売上明細のデータがありません。<br>%s〜%s</font>", format_date($d_start), format_date($d_end) );
    //header('Location: ' . H_WEB_HOST . $menu->out_RetUrl() . '?sum_exec=on');    // 直前の呼出元へ戻る
    exit();
} else {
    $num_csv = count($field_csv);       // フィールド数取得
    for ($r=0; $r<$rows_csv; $r++) {
        //$res_csv[$r][4] = mb_convert_kana($res_csv[$r][4], 'ka', 'EUC-JP');   // 全角カナを半角カナへテスト的にコンバート
        $res_csv[$r][3] = str_replace(',',' ',$res_csv[$r][3]);                   // 製品名に,が入っているとCSVで桁がずれるので半角スペースに
        $res_csv[$r][3] = mb_convert_encoding($res_csv[$r][3], 'SJIS', 'auto');   // CSV用にEUCからSJISへ文字コード変換
    }
    //$_SESSION['SALES_TEST'] = sprintf("order by 計上日 offset %d limit %d", $offset, PAGE);
    $i = 1;                             // CSV書き出し用カウント（フィールド名が0に入るので１から）
    $csv_data = array();                // CSV書き出し用配列
    for ($s=0; $s<$num_csv; $s++) {     // フィールド名をCSV書き出し用配列に出力
        $field_csv[$s]   = mb_convert_encoding($field_csv[$s], 'SJIS', 'auto');
        $csv_data[0][$s] = $field_csv[$s];
    }
    for ($r=0; $r<$rows_csv; $r++) {    // データをCSV書き出し用配列に出力
        for ($s=0; $s<$num_csv; $s++) {
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