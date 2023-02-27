<?php
//////////////////////////////////////////////////////////////////////////////
// A伝状況の照会 CSV出力                                                    //
// Copyright (C) 2016-2016 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2016/03/25 Created   aden_details_csv.php                                //
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

$outputFile = $_GET['csvname'] . '-' . 'A伝状況.csv';
$csv_search = $_GET['csvsearch'];
$order      = $_GET['order'];
// SQLのサーチ部で一時変更した部分を元に戻す
//$search     = str_replace('keidate','計上日',$csv_search);
//$search     = str_replace('jigyou','事業部',$search);
//$search     = str_replace('denban','伝票番号',$search);
//$search     = str_replace('tokui','得意先',$search);
$search     = str_replace('/','\'',$csv_search);
// サーチ部の文字コードをEUCに変更する（念のため）
$search     = mb_convert_encoding($search, 'UTF-8', 'auto');   // CSV用にEUCからSJISへ文字コード変換

// ファイル名で一時変更した部分を元に戻す
$outputFile     = str_replace('ALL','全グループ',$outputFile);
$outputFile     = str_replace('C-all','カプラ',$outputFile);
$outputFile     = str_replace('L-all','リニア',$outputFile);
$outputFile     = str_replace('NKCT','NKCT',$outputFile);
$outputFile     = str_replace('NKT','NKT',$outputFile);

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
        SELECT
            publish_day AS A伝発行日,       -- 00
            receive_day AS A伝受注日,       -- 01
            aden_no     AS A伝No,           -- 02
            parts_no    AS ASSYNo,          -- 03
            substr(sale_name, 1, 20)
                        AS 製品名,          -- 04
            order_q     AS 数量,            -- 05
            espoir_deli AS 希望納期,        -- 06
            delivery    AS 回答納期,        -- 07
            deli_com    AS 納期コメント,    -- 08
            espoir_lt   AS 希望LT,          -- 09
            ans_lt      AS 納回答LT,        -- 10
            lt_diff     AS LT差,            -- 11
            order_price AS 販売価格,        -- 12
            finish_day  AS 実完成日,        -- 13
            finish_del  AS 完成遅れ,        -- 14
            kouji_no    AS SC工番,          -- 15
            plan_no     AS 計画No,          -- 16
            answer_day  AS A伝回答日,       -- 17
            ans_day_lt  AS A伝回答LT,       -- 18
            comment     AS 備考             -- 19
        FROM
            aden_details_master AS a
        %s 
        ORDER BY %s
    ", $search, $order);   // 共用 $search で検索
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
        $res_csv[$r][4]  = str_replace(',',' ',$res_csv[$r][4]);                   // 製品名に,が入っているとCSVで桁がずれるので半角スペースに
        $res_csv[$r][19] = str_replace(',',' ',$res_csv[$r][19]);                  // 備考に,が入っているとCSVで桁がずれるので半角スペースに
        //$res_csv[$r][3] = mb_convert_encoding($res_csv[$r][3], 'SJIS', 'EUC');   // CSV用にEUCからSJISへ文字コード変換(EUC指定じゃないと直納・調整が化ける)
        $res_csv[$r][4]  = mb_convert_encoding($res_csv[$r][4], 'SJIS', 'auto');   // CSV用にEUCからSJISへ文字コード変換
        $res_csv[$r][19] = mb_convert_encoding($res_csv[$r][19], 'SJIS', 'auto');  // CSV用にEUCからSJISへ文字コード変換
        if ($res_csv[$r][8] == 0) {
            if ($res_csv[$r][6] == $res_csv[$r][7]) {
                $res_csv[$r][8] = '希望通り';
            } else {
                $res_csv[$r][8] = '未入力';
            }
        } elseif ($res_csv[$r][8] == 1) {
            $res_csv[$r][8] = '部品遅れ';
        } elseif ($res_csv[$r][8] == 2) {
            $res_csv[$r][8] = '設計変更';
        } elseif ($res_csv[$r][8] == 3) {
            $res_csv[$r][8] = 'L/T不足';
        } elseif ($res_csv[$r][8] == 4) {
            $res_csv[$r][8] = '伝送遅れ';
        } elseif ($res_csv[$r][8] == 5) {
            $res_csv[$r][8] = 'その他';
        }
        $res_csv[$r][8] = mb_convert_encoding($res_csv[$r][8], 'SJIS', 'auto');   // CSV用にEUCからSJISへ文字コード変換
        
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