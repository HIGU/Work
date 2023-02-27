<?php
//////////////////////////////////////////////////////////////////////////////
// 長期滞留部品の照会 CSV出力                                               //
// Copyright (C) 2013-2014 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2013/06/13 Created   long_holding_parts_csv.php                          //
// 2014/05/07 CSV出力時のみ材質を表示するように変更                         //
// 2014/09/11 CSV出力時棚番2E2等が2.00E〜と指数表示になるのを防ぐよう変更   //
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

// ファイル名とSQLのサーチ部を受け取る
$outputFile = '長期滞留部品.csv';
$csv_search = $_GET['csvsearch'];
// SQLのサーチ部で一時変更した部分を元に戻す
$search     = str_replace('saitanka','最新単価',$csv_search);
$search     = str_replace('kingaku','金額',$search);
$search     = str_replace('/','\'',$search);
// サーチ部の文字コードをEUCに変更する（念のため）
$search     = mb_convert_encoding($search, 'EUC-JP', 'auto');   // CSV用にEUCからSJISへ文字コード変換

// 実行者のパソコンにCSVを保存する為、ファイル名の文字コードをSJISに変換
$outputFile = mb_convert_encoding($outputFile, 'SJIS', 'auto');   // CSV用にEUCからSJISへ文字コード変換

//////////// CSV出力用のデータ出力
$query_csv = sprintf("SELECT CASE
                                WHEN trim(long.tnk_tana) = '' THEN ''
                                ELSE long.tnk_tana
                                END             AS 棚番号           -- 00
                                ,long.parts_no  AS 部品番号         -- 01
                                ,trim(substr(long.parts_name, 1, 16))
                                                AS 部品名           -- 02
                                ,CASE
                                    WHEN mepnt='' THEN ''
                                    WHEN mepnt IS NULL THEN ''
                                    ELSE mepnt
                                END             AS 親機種           -- 03
                                ,CASE
                                    WHEN mzist='' THEN ''
                                    WHEN mzist IS NULL THEN ''
                                    ELSE mzist
                                END             AS 材質             -- 04
                                ,to_char(long.in_date, 'FM0000/00/00')
                                                AS 入庫日           -- 05
                                ,in_pcs         AS 入庫数           -- 06
                                ,tnk_stock + nk_stock
                                                AS 現在庫           -- 07
                                ,CASE
                                    WHEN tanka IS NULL THEN 0
                                    ELSE tanka
                                END             AS 最新単価         -- 08
                                ,CASE
                                    WHEN tanka IS NULL THEN 0
                                    ELSE UROUND((tnk_stock + nk_stock) * tanka, 0)
                                END             AS 金額             -- 09
                                FROM
                                    long_holding_parts_work1 AS long
                                LEFT OUTER JOIN
                                    act_payable
                                    ON (long.parts_no=act_payable.parts_no AND long.in_date=act_payable.act_date AND long.den_no=act_payable.uke_no)
                                    -- 同じ部品番号で同日に２回検収した場合の対応で(long.in_pcs=act_payable.genpin)を追加→(long.den_no=act_payable.uke_no)へ変更
                                LEFT OUTER JOIN
                                    order_plan USING(sei_no)
                                LEFT OUTER JOIN
                                    miitem ON (long.parts_no=miitem.mipn)
                                %s
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
        $res_csv[$r][0] = '="' . $res_csv[$r][0] . '"';                         // 2E2等の棚番がCSV上指数と判断されるのを防ぐ
        $res_csv[$r][2] = mb_convert_kana($res_csv[$r][2], 'ka', 'EUC-JP');     // 全角カナを半角カナへテスト的にコンバート
        $res_csv[$r][3] = mb_convert_kana($res_csv[$r][3], 'ka', 'EUC-JP');     // 全角カナを半角カナへテスト的にコンバート
        $res_csv[$r][4] = mb_convert_kana($res_csv[$r][4], 'ka', 'EUC-JP');     // 全角カナを半角カナへテスト的にコンバート
        $res_csv[$r][2] = str_replace(',',' ',$res_csv[$r][2]);                 // 部品名に,が入っているとCSVで桁がずれるので半角スペースに
        $res_csv[$r][3] = str_replace(',',' ',$res_csv[$r][3]);                 // 親機種に,が入っているとCSVで桁がずれるので半角スペースに
        $res_csv[$r][4] = str_replace(',',' ',$res_csv[$r][4]);                 // 親機種に,が入っているとCSVで桁がずれるので半角スペースに
        $res_csv[$r][0] = mb_convert_encoding($res_csv[$r][0], 'SJIS', 'EUC');  // CSV用にEUCからSJISへ文字コード変換(EUC指定じゃないと直納・調整が化ける)
        $res_csv[$r][2] = mb_convert_encoding($res_csv[$r][2], 'SJIS', 'EUC');  // CSV用にEUCからSJISへ文字コード変換(EUC指定じゃないと直納・調整が化ける)
        $res_csv[$r][3] = mb_convert_encoding($res_csv[$r][3], 'SJIS', 'EUC');  // CSV用にEUCからSJISへ文字コード変換
        $res_csv[$r][4] = mb_convert_encoding($res_csv[$r][4], 'SJIS', 'EUC');  // CSV用にEUCからSJISへ文字コード変換
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