<?php
//////////////////////////////////////////////////////////////////////////////
// 売上明細の照会 CSV出力                                                   //
// Copyright (C) 2010-2019 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2010/05/21 Created   materialNewSales_csv.php                            //
// 2010/12/20 試作売上に対応       直納・調整が化ける                       //
// 2010/12/24 直納・調整の文字化けに対応                                    //
// 2011/11/10 テストでNKCT・NKTを追加 → 正式追加 全体公開                  //
// 2011/11/21 ファイル名変換でカプラ特注が抜けていたのを修正                //
//            ファイル名を〜売上明細.csvに変更                              //
// 2013/01/29 バイモルを液体ポンプへ変更 表示のみデータはバイモルのまま     //
// 2013/05/28 得意先の指定を追加                                            //
// 2014/11/19 特注の場合は工事番号を出力するように変更                      //
// 2015/11/27 部品(科目：5以降)出力時、総材料費が正しく取得できていない     //
//            不具合を修正                                                  //
// 2018/06/22 特注の販売価格が抜き出せていなかったのを訂正                  //
// 2019/10/09 メドテックとメドー産業のファイル名変換が被っていたので修正大谷//
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
$outputFile = 'A伝情報.csv';
$csv_search = $_GET['csvsearch'];
$csv_sort   = $_GET['csvsort'];
// SQLのサーチ部で一時変更した部分を元に戻す
$search     = str_replace('/','\'',$csv_search);
$sort       = str_replace('/','\'',$csv_sort);
// サーチ部の文字コードをEUCに変更する（念のため）
$search     = mb_convert_encoding($search, 'EUC-JP', 'auto');   // CSV用にEUCからSJISへ文字コード変換
$sort       = mb_convert_encoding($sort, 'EUC-JP', 'auto');   // CSV用にEUCからSJISへ文字コード変換

// 実行者のパソコンにCSVを保存する為、ファイル名の文字コードをSJISに変換
$outputFile = mb_convert_encoding($outputFile, 'SJIS', 'auto');   // CSV用にEUCからSJISへ文字コード変換

//////////// CSV出力用のデータ出力
$query_csv = sprintf("
        SELECT
            aden.aden_no     as Ａ伝,                        -- 0
            aden.eda_no      as 枝,                          -- 1
            CASE
                WHEN trim(aden.parts_no) = '' THEN '---'
                ELSE aden.parts_no
            END         as 製品番号,                    -- 2
            CASE
                WHEN trim(aden.sale_name) = '' THEN '&nbsp;'
                ELSE trim(aden.sale_name)
            END         as 販売商品名,                  -- 3
            CASE
                WHEN trim(aden.plan_no) = '' THEN '---'
                ELSE aden.plan_no
            END         as 計画番号,                    -- 5
            CASE
                WHEN trim(aden.approval) = '' THEN '---'
                ELSE aden.approval
            END         as 承認図,                      -- 6
            CASE
                WHEN trim(aden.ropes_no) = '' THEN '---'
                ELSE aden.ropes_no
            END         as 要領書,                      -- 7
            CASE
                WHEN trim(aden.kouji_no) = '' THEN '---'
                ELSE aden.kouji_no
            END         as 工事番号,                    -- 8
            aden.order_q     as 受注数量,                    -- 9
            aden.order_price as 受注単価,                    --10
            Uround(aden.order_q * aden.order_price, 0) as 金額,   --11
            aden.espoir_deli as 希望納期,                    --12
            aden.delivery    as 回答納期,                    --13
            aden.publish_day    AS  発行日,                  --14
            
            sche.line_no    AS  ライン,                 --19
            (sche.plan - sche.cut_plan - sche.kansei)
                            AS  計画残,                 --18
            sche.syuka      AS  集荷日,                 --15
            sche.chaku      AS  着手日,                 --16
            sche.kanryou    AS  完了日                  --17
        FROM
            aden_master             AS aden
        LEFT OUTER JOIN
            miitem                              ON aden.parts_no=mipn
        LEFT OUTER JOIN
            assembly_schedule       AS sche     using(plan_no)
        %s 
        ORDER BY
        %s
        
    ", $search, $sort);       // 共用 $search で検索
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
        //$res_csv[$r][1] = mb_convert_encoding($res_csv[$r][1], 'SJIS', 'EUC');   // CSV用にEUCからSJISへ文字コード変換(EUC指定じゃないと直納・調整が化ける)
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