<?php
//////////////////////////////////////////////////////////////////////////////
// 要因分析の照会 CSV出力                                                   //
// Copyright (C) 2016-2016 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2016/06/24 Created   inventory_average_csv.php                           //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../../../ControllerHTTP_Class.php');// TNK 全共通 MVC Controller Class

$outputFile = '資材部品在庫金額要因分析.csv';
$csv_search = $_GET['csvsearch'];
// SQLのサーチ部で一時変更した部分を元に戻す
//$search     = str_replace('keidate','計上日',$csv_search);
//$search     = str_replace('jigyou','事業部',$search);
//$search     = str_replace('denban','伝票番号',$search);
//$search     = str_replace('tokui','得意先',$search);
$search     = str_replace('/','\'',$csv_search);
// サーチ部の文字コードをEUCに変更する（念のため）
$search     = mb_convert_encoding($search, 'UTF-8', 'auto');   // CSV用にEUCからSJISへ文字コード変換

// ファイル名で一時変更した部分を元に戻す
//$outputFile     = str_replace('ALL','全グループ',$outputFile);
//$outputFile     = str_replace('C-all','カプラ',$outputFile);
//$outputFile     = str_replace('L-all','リニア',$outputFile);
//$outputFile     = str_replace('NKCT','NKCT',$outputFile);
//$outputFile     = str_replace('NKT','NKT',$outputFile);

// ファイル名で一時変更した部分を元に戻す（得意先名）
/*
$outputFile     = str_replace('T-全グループ','全て',$outputFile);
$outputFile     = str_replace('T-NK','日東工器',$outputFile);
$outputFile     = str_replace('T-MEDO','メドー産業',$outputFile);
$outputFile     = str_replace('T-NKT','NKT',$outputFile);
$outputFile     = str_replace('T-MEDOTEC','メドテック',$outputFile);
$outputFile     = str_replace('T-SNK','白河日東工器',$outputFile);
$outputFile     = str_replace('T-NKCT','NKCT',$outputFile);
$outputFile     = str_replace('T-BRECO','BRECO',$outputFile);
$outputFile     = str_replace('T-SHO','諸口',$outputFile);
*/
// 実行者のパソコンにCSVを保存する為、ファイル名の文字コードをSJISに変換
$outputFile = mb_convert_encoding($outputFile, 'SJIS', 'auto');   // CSV用にEUCからSJISへ文字コード変換

//////////// CSV出力用のデータ出力
$query_csv = sprintf("
        SELECT   invent.parts_no    AS 部品番号         -- 00
                    , trim(substr(midsc, 1, 14))
                                        AS 部品名           -- 01
                    , CASE
                        WHEN mepnt = '' THEN ''
                        WHEN mepnt IS NULL THEN ''
                        ELSE trim(substr(mepnt, 1, 9))
                      END               AS 親機種           -- 02
                    , CASE
                        WHEN latest_parts_cost(invent.parts_no) IS NULL THEN 0
                        ELSE latest_parts_cost(invent.parts_no)
                      END               AS 最新単価         -- 03
                    , invent_pcs
                                        AS 前日在庫数       -- 04
                    , CASE
                        WHEN latest_parts_cost(invent.parts_no) IS NULL THEN 0
                        ELSE Uround(latest_parts_cost(invent.parts_no) * invent_pcs, 0)
                      END               AS 在庫金額         -- 05
                    , month_pickup_avr
                                        AS 月平均出庫数     -- 06
                    , hold_monthly_avr
                                        AS 保有月           -- 07
                    , CASE
                        WHEN factor_name IS NULL THEN ''
                        ELSE factor_name
                      END               AS 要因名           -- 08
                    , factor_explanation
                                        AS 要因説明         -- 09
                    , comment
                                        AS コメント         -- 10
                    , CASE
                        WHEN latest_parts_cost_regno(invent.parts_no) IS NULL THEN 0
                        ELSE latest_parts_cost_regno(invent.parts_no)
                      END               AS 登録番号         -- 11
                    FROM
                        inventory_average_summary AS invent
                    LEFT OUTER JOIN
                        miitem ON (invent.parts_no = mipn)
                    LEFT OUTER JOIN
                        inventory_average_comment USING (parts_no)
                    LEFT OUTER JOIN
                        inventory_average_factor USING (factor)
        %s 
        ORDER BY 在庫金額 DESC
    ", $search);   // 共用 $search で検索
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
        $res_csv[$r][1]  = str_replace(',',' ',$res_csv[$r][1]);                   // 部品名に,が入っているとCSVで桁がずれるので半角スペースに
        $res_csv[$r][2]  = str_replace(',',' ',$res_csv[$r][2]);                   // 親機種に,が入っているとCSVで桁がずれるので半角スペースに
        $res_csv[$r][8]  = str_replace(',',' ',$res_csv[$r][8]);                   // 要因名に,が入っているとCSVで桁がずれるので半角スペースに
        $res_csv[$r][9]  = str_replace(',',' ',$res_csv[$r][9]);                   // 要因説明に,が入っているとCSVで桁がずれるので半角スペースに
        $res_csv[$r][10] = str_replace(',',' ',$res_csv[$r][10]);                  // コメントに,が入っているとCSVで桁がずれるので半角スペースに
        //$res_csv[$r][3] = mb_convert_encoding($res_csv[$r][3], 'SJIS', 'EUC');   // CSV用にEUCからSJISへ文字コード変換(EUC指定じゃないと直納・調整が化ける)
        $res_csv[$r][1]  = mb_convert_encoding($res_csv[$r][1], 'SJIS', 'auto');   // CSV用にEUCからSJISへ文字コード変換
        $res_csv[$r][2]  = mb_convert_encoding($res_csv[$r][2], 'SJIS', 'auto');   // CSV用にEUCからSJISへ文字コード変換
        $res_csv[$r][8]  = mb_convert_encoding($res_csv[$r][8], 'SJIS', 'auto');   // CSV用にEUCからSJISへ文字コード変換
        $res_csv[$r][9]  = mb_convert_encoding($res_csv[$r][9], 'SJIS', 'auto');   // CSV用にEUCからSJISへ文字コード変換
        $res_csv[$r][10] = mb_convert_encoding($res_csv[$r][10], 'SJIS', 'auto');  // CSV用にEUCからSJISへ文字コード変換
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