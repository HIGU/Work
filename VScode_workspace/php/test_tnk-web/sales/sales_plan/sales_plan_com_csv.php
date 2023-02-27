<?php
//////////////////////////////////////////////////////////////////////////////
// 売上予定の照会 CSV出力 特注仕切 A伝販売単価比較                          //
// Copyright (C) 2018-2018 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2018/08/21 Created   sales_plan_com_csv.php                              //
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
$outputFile = $_GET['csvname'] . '売上予定-比較表.csv';
$csv_search = $_GET['csvsearch'];
$div        = $_GET['div'];
$shikiri    = $_GET['shikiri'];
// SQLのサーチ部で一時変更した部分を元に戻す
$search     = str_replace('/','\'',$csv_search);
// サーチ部の文字コードをEUCに変更する（念のため）
$search     = mb_convert_encoding($search, 'UTF-8', 'auto');   // CSV用にEUCからSJISへ文字コード変換

// ファイル名で一時変更した部分を元に戻す
$outputFile     = str_replace('ALL','全体',$outputFile);
$outputFile     = str_replace('C-all','カプラ全体',$outputFile);
$outputFile     = str_replace('C-hyou','カプラ標準',$outputFile);
$outputFile     = str_replace('C-toku','カプラ特注',$outputFile);
$outputFile     = str_replace('L-all','リニア全体',$outputFile);
$outputFile     = str_replace('L-hyou','リニアのみ',$outputFile);
$outputFile     = str_replace('L-bimor','液体ポンプのみ',$outputFile);
$outputFile     = str_replace('C-shuri','カプラ試修',$outputFile);
$outputFile     = str_replace('L-shuri','リニア試修',$outputFile);
$outputFile     = str_replace('NKB','商品管理',$outputFile);
$outputFile     = str_replace('TOOL','ツール',$outputFile);
$outputFile     = str_replace('NONE','なし',$outputFile);
$outputFile     = str_replace('SHISAKU','試作',$outputFile);
$outputFile     = str_replace('NKCT','NKCT',$outputFile);
$outputFile     = str_replace('NKT','NKT',$outputFile);
$outputFile     = str_replace('NKTB','NKT部品出庫分',$outputFile);
$outputFile     = str_replace('NONE','なし',$outputFile);

// 実行者のパソコンにCSVを保存する為、ファイル名の文字コードをSJISに変換
$outputFile = mb_convert_encoding($outputFile, 'SJIS', 'auto');   // CSV用にEUCからSJISへ文字コード変換

//////////// CSV出力用のデータ出力
$query_csv = sprintf("select
                                a.kanryou                     AS 完了予定日,  -- 0
                                CASE
                                    WHEN trim(a.plan_no)='' THEN '---'        --NULLでなくてスペースで埋まっている場合はこれ！
                                    ELSE a.plan_no
                                END                           AS 計画番号,    -- 1
                                CASE
                                    WHEN trim(a.parts_no) = '' THEN '---'
                                    ELSE a.parts_no
                                END                           AS 製品番号,    -- 2
                                CASE
                                    WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                    WHEN m.midsc IS NULL THEN '&nbsp;'
                                    ELSE substr(m.midsc,1,38)
                                END                           AS 製品名,      -- 3
                                a.plan -a.cut_plan - a.kansei AS 数量,        -- 4
                                (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                                                              AS 最新仕切単価,    -- 5
                                Uround((a.plan -a.cut_plan - kansei) * (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1), 0)
                                                              AS 金額,        -- 6
                                (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1)
                                                              AS A伝仕切単価,    -- 7
                                Uround((a.plan -a.cut_plan - kansei) * (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1), 0)
                                                              AS 金額         -- 8
                          FROM
                                assembly_schedule as a
                          left outer join
                                miitem as m
                          on a.parts_no=m.mipn
                          left outer join
                                material_cost_header as mate
                          on a.plan_no=mate.plan_no
                          LEFT OUTER JOIN
                                sales_parts_material_history AS pmate
                          ON (a.parts_no=pmate.parts_no AND a.plan_no=pmate.sales_date)
                          left outer join
                                product_support_master AS groupm
                          on a.parts_no=groupm.assy_no
                          %s
                          order by a.kanryou, 計画番号
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
        $res_csv[$r][3] = str_replace(',',' ',$res_csv[$r][3]);                   // 製品名に,が入っているとCSVで桁がずれるので半角スペースに
        $res_csv[$r][3] = mb_convert_encoding($res_csv[$r][3], 'SJIS', 'auto');   // CSV用にEUCからSJISへ文字コード変換
        $res_csv[$r][4] = str_replace(',',' ',$res_csv[$r][4]);                   // 製品名に,が入っているとCSVで桁がずれるので半角スペースに
        $res_csv[$r][4] = mb_convert_encoding($res_csv[$r][4], 'SJIS', 'auto');   // CSV用にEUCからSJISへ文字コード変換
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