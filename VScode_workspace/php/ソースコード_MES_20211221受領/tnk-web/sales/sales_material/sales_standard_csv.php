<?php
//////////////////////////////////////////////////////////////////////////////
// 原価率分析 CSV出力                                                       //
// Copyright (C) 2010-2015 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2010/05/21 Created   sales_standard_csv.php                              //
// 2010/12/24 CSV出力時'+'なども文字化けする為修正                          //
// 2011/11/10 テストでNKCTとNKTを追加 → 正式追加 全体公開                  //
// 2011/11/14 ファイル名に条件表示を追加                                    //
// 2013/01/29 バイモルを液体ポンプへ変更 表示のみデータはバイモルのまま     //
// 2015/05/20 スラッシュの変換が割り算と重なりCSV出力エラーになる為修正     //
// 2015/05/25 機工生産に対応                                                //
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
$outputFile  = $_GET['csvname'];
$csv_search  = $_GET['csvsearch'];
// SQLのサーチ部で一時変更した部分を元に戻す
$search     = str_replace('keidate','計上日',$csv_search);
$search     = str_replace('jigyou','事業部',$search);
$search     = str_replace('tanka','単価',$search);
$search     = str_replace('|','\'',$search);
$search     = str_replace('-','+',$search);
// サーチ部の文字コードをEUCに変更する（念のため）
$search     = mb_convert_encoding($search, 'EUC-JP', 'auto');   // CSV用にEUCからSJISへ文字コード変換

// ファイル名で一時変更した部分を元に戻す
$outputFile     = str_replace('ALL','全体',$outputFile);
$outputFile     = str_replace('C-all','カプラ全体',$outputFile);
$outputFile     = str_replace('C-hyou','カプラ標準',$outputFile);
$outputFile     = str_replace('C-toku','カプラ特注',$outputFile);
$outputFile     = str_replace('L-all','リニア全体',$outputFile);
$outputFile     = str_replace('L-hyou','リニアのみ',$outputFile);
$outputFile     = str_replace('L-bimor','液体ポンプ',$outputFile);
$outputFile     = str_replace('T-all','ツール',$outputFile);
$outputFile     = str_replace('NKCT','NKCT',$outputFile);
$outputFile     = str_replace('NKT','NKT',$outputFile);

$outputFile     = str_replace('J0','全体',$outputFile);
$outputFile     = str_replace('J1','条件１',$outputFile);
$outputFile     = str_replace('J2','条件２',$outputFile);
$outputFile     = str_replace('J3','条件３',$outputFile);
$outputFile     = str_replace('J4','その他',$outputFile);

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
                            Uround(u.数量 * u.単価, 0) as 売上高,       -- 6
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) = 0 THEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=u.assyno order by assy_no DESC, regdate DESC limit 1)
                                ELSE sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                            END                     as 総材料費,        -- 7
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) = 0 THEN Uround(u.数量 * ((select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=u.assyno order by assy_no DESC, regdate DESC limit 1)), 0)
                                ELSE Uround(u.数量 * (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 0)
                            END                     as 総材料金額       -- 8
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
                      left outer join
                            product_support_master AS groupm
                      on u.assyno=groupm.assy_no
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