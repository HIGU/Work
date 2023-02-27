<?php
//////////////////////////////////////////////////////////////////////////////
// 買掛実績の照会 CSV出力                                                   //
// Copyright (C) 2010-2018 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2013/10/10 Created   act_payable_csv.php                                 //
// 2013/10/12 ファイル名に発注先の表示を追加                                //
// 2018/01/29 カプラ特注・標準を追加                                   大谷 //
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
$vendor     = $_GET['csvvendor'];
//////// 協力工場名の取得
if ($vendor == '') {
    $vendor_name = '発注先指定なし';
} else {
    $query = "select name from vendor_master where vendor='{$vendor}'";
    if (getUniResult($query, $vendor_name) < 1) {
        $vendor_name = '未登録';
    }
}
$vendor_name = trim($vendor_name);
$vendor_name = rtrim($vendor_name, "　");

$outputFile = $_GET['csvname'] . '-' . $vendor_name . '-' . '買掛実績.csv';
$csv_search = $_GET['csvsearch'];
// SQLのサーチ部で一時変更した部分を元に戻す
//$search     = str_replace('keidate','計上日',$csv_search);
//$search     = str_replace('jigyou','事業部',$search);
//$search     = str_replace('denban','伝票番号',$search);
//$search     = str_replace('tokui','得意先',$search);
$search     = str_replace('/','\'',$csv_search);
// サーチ部の文字コードをEUCに変更する（念のため）
$search     = mb_convert_encoding($search, 'EUC-JP', 'auto');   // CSV用にEUCからSJISへ文字コード変換

// ファイル名で一時変更した部分を元に戻す
$outputFile     = str_replace('ALL','全グループ',$outputFile);
$outputFile     = str_replace('C-all','カプラ',$outputFile);
$outputFile     = str_replace('C-hyo','Ｃ標準',$outputFile);
$outputFile     = str_replace('C-toku','Ｃ特注',$outputFile);
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
            -- act_date    as 処理日,
            -- type_no     as \"T\",
            uke_no      as 受付番,          -- 00
            uke_date    as 受付日,          -- 01
            ken_date    as 検収日,          -- 02
            substr(trim(name), 1, 8)
                        as 発注先名,        -- 03
            a.parts_no    as 部品番号,        -- 04
            substr(midsc, 1, 12)
                        AS 部品名,          -- 05
            substr(mepnt, 1, 10)
                        AS 親機種,          -- 06
            koutei      as 工程,            -- 07
            mtl_cond    as 条,      -- 条件    08
            order_price as 発注単価,        -- 09
            genpin      as 現品数,          -- 10
            siharai     as 支払数,          -- 11
            Uround(order_price * siharai,0)
                        as 買掛金額,        -- 12
            sei_no      as 製造番号,        -- 13
            a.div       as 事,              -- 14
            kamoku      as 科,              -- 15
            order_no    as 注文番号,        -- 16
            vendor      as 発注先,          -- 17
            o.kouji_no  as 工事番号         -- 18
        FROM
            act_payable AS a
        LEFT OUTER JOIN
            vendor_master USING(vendor)
        LEFT OUTER JOIN
            miitem ON (parts_no = mipn)
        LEFT OUTER JOIN
            parts_stock_master AS m ON (m.parts_no=a.parts_no)
        LEFT OUTER JOIN
            order_plan AS o USING(sei_no)
        %s 
        ORDER BY act_date DESC
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
        $res_csv[$r][3] = str_replace(',',' ',$res_csv[$r][3]);                   // 発注先名に,が入っているとCSVで桁がずれるので半角スペースに
        $res_csv[$r][5] = str_replace(',',' ',$res_csv[$r][5]);                   // 部品名に,が入っているとCSVで桁がずれるので半角スペースに
        $res_csv[$r][6] = str_replace(',',' ',$res_csv[$r][6]);                   // 親期首に,が入っているとCSVで桁がずれるので半角スペースに
        //$res_csv[$r][3] = mb_convert_encoding($res_csv[$r][3], 'SJIS', 'EUC');   // CSV用にEUCからSJISへ文字コード変換(EUC指定じゃないと直納・調整が化ける)
        $res_csv[$r][3] = mb_convert_encoding($res_csv[$r][3], 'SJIS', 'auto');   // CSV用にEUCからSJISへ文字コード変換
        $res_csv[$r][5] = mb_convert_encoding($res_csv[$r][5], 'SJIS', 'auto');   // CSV用にEUCからSJISへ文字コード変換
        $res_csv[$r][6] = mb_convert_encoding($res_csv[$r][6], 'SJIS', 'auto');   // CSV用にEUCからSJISへ文字コード変換
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