<?php
//////////////////////////////////////////////////////////////////////////////
// カプラ標準改定仕切単価 CSV出力                                           //
// Copyright (C) 2010-2021 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2010/05/21 Created   materialNewLinear_csv.php                           //
// 2010/05/26 タイトルがリニアだったのをカプラ標準に変更                    //
// 2011/03/04 手作業賃率を$rateでマスター化                                 //
// 2017/09/15 現仕切と登録日の出力を追加                                    //
// 2021/09/22 基準日を登録日から計画の完成日へ変更し翌月の1日へ             //
//            期間を過去３年から半年に変更（前回分以降）                    //
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
$ind_ym   = $_GET['indym'];
$cost_ymd = $_GET['costymd'];
$str_ymd  = $_GET['strymd'];
$end_ymd  = $_GET['endymd'];

$outputFile = $ind_ym . '-カプラ改定仕切単価.csv';

//$outputFile = $_GET['csvname'];
//$csv_search = $_GET['csvsearch'];
// SQLのサーチ部で一時変更した部分を元に戻す
//$search     = str_replace('keidate','計上日',$csv_search);
//$search     = str_replace('jigyou','事業部',$search);
//$search     = str_replace('/','\'',$search);
// サーチ部の文字コードをEUCに変更する（念のため）
//$search     = mb_convert_encoding($search, 'EUC-JP', 'auto');   // CSV用にEUCからSJISへ文字コード変換

// ファイル名で一時変更した部分を元に戻す
//$outputFile     = str_replace('ALL','全体',$outputFile);
//$outputFile     = str_replace('C-all','カプラ全体',$outputFile);
//$outputFile     = str_replace('C-hyou','カプラ標準',$outputFile);
//$outputFile     = str_replace('C-toku','カプラ特注',$outputFile);
//$outputFile     = str_replace('L-all','リニア全体',$outputFile);
//$outputFile     = str_replace('L-hyou','リニアのみ',$outputFile);
//$outputFile     = str_replace('L-bimor','バイモル',$outputFile);

if ($ind_ym < 200710) {
    $rate = 25.60;  // カプラ標準 2007/10/01価格改定以前
} elseif ($ind_ym < 201104) {
    $rate = 57.00;  // カプラ標準 2007/10/01価格改定以降
} else {
    $rate = 45.00;  // カプラ標準 2011/04/01価格改定以降
}
// 実行者のパソコンにCSVを保存する為、ファイル名の文字コードをSJISに変換
$outputFile = mb_convert_encoding($outputFile, 'SJIS', 'auto');   // CSV用にEUCからSJISへ文字コード変換

//////////// CSV出力用のデータ出力
$query_csv = "
        SELECT
            u.assyno                    AS 製品番号     -- 0
            ,
            trim(substr(m.midsc,1,30))  AS 製品名       -- 1
            ,
            (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header as c LEFT OUTER JOIN assembly_completion_history AS hist USING(plan_no) WHERE comp_date < {$cost_ymd} AND c.assy_no = u.assyno ORDER BY c.assy_no DESC, regdate DESC LIMIT 1)
                                        AS 最新総材料費 -- 2
            ,
            (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header as c LEFT OUTER JOIN assembly_completion_history AS hist USING(plan_no) WHERE comp_date < {$cost_ymd} AND c.assy_no = u.assyno ORDER BY c.assy_no DESC, regdate DESC LIMIT 1)
                                        AS 総材料登録日 -- 3
            ,
            credit.credit_per           AS 掛率         -- 4
            ,
            0                           AS 最新仕切     -- 5
            ,
            sale.price                  AS 現仕切       -- 6
            ,
            sale.regdate                AS 現仕切登録日 -- 7
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
              parts_credit_per AS credit
        ON (u.assyno = credit.parts_no)
        LEFT OUTER JOIN
              sales_price_nk AS sale
        ON (u.assyno = sale.parts_no)
        WHERE 計上日 >= {$str_ymd} AND 計上日 <= {$end_ymd} AND 事業部 = 'C' AND (note15 NOT LIKE 'SC%%' OR note15 IS NULL) AND datatype='1'
            AND mate.assy_no IS NULL
            -- これを追加すれば自動機の登録があるもの AND (SELECT a_rate FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) > 0
        GROUP BY u.assyno, m.midsc, credit.credit_per, sale.price , sale.regdate
        ORDER BY u.assyno ASC
        OFFSET 0 LIMIT 5000
";
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
        $res_csv[$r][1] = str_replace(',',' ',$res_csv[$r][1]);                   // 製品名に,が入っているとCSVで桁がずれるので半角スペースに
        $res_csv[$r][1] = mb_convert_encoding($res_csv[$r][1], 'SJIS', 'auto');   // CSV用にEUCからSJISへ文字コード変換
    }
    //$_SESSION['SALES_TEST'] = sprintf("order by 計上日 offset %d limit %d", $offset, PAGE);
    $i = 1;                             // CSV書き出し用カウント（フィールド名が0に入るので１から）
    $csv_data = array();                // CSV書き出し用配列
    for ($s=0; $s<$num_csv; $s++) {     // フィールド名をCSV書き出し用配列に出力
        $field_csv[$s]   = mb_convert_encoding($field_csv[$s], 'SJIS', 'auto');
        $csv_data[0][$s] = $field_csv[$s];
    }
    for ($r=0; $r<$rows_csv; $r++) {    // データをCSV書き出し用配列に出力
        if (comp_date($res_csv[$r][0], $end_ymd)) {
            $res_csv[$r][4] = 1.18;
        }
        $res_csv[$r][5] = ROUND(($res_csv[$r][2] * $res_csv[$r][4]), 2);
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

function comp_date($assyNo, $endymd)
{
    $assy = $assyNo;
    $query = "SELECT hist.comp_date                               AS 初回完成日
                FROM
                    assembly_completion_history AS hist
                LEFT OUTER JOIN
                    material_cost_header AS mate USING(plan_no)
                LEFT OUTER JOIN
                    assembly_schedule AS asse USING(plan_no)
                LEFT OUTER JOIN
                    miitem AS item ON (hist.assy_no=item.mipn)
                WHERE hist.assy_no LIKE '{$assy}' -- '{$assy}'
                ORDER BY hist.assy_no DESC, hist.comp_date ASC --計画日 DESC
                LIMIT 1";
    $rows=getResult2($query, $res_i);
    $comp_year  = substr($res_i[0][0], 0, 4);
    $comp_month = substr($res_i[0][0], 4, 2);
    if ($comp_month < 4) {
        $comp_year  = $comp_year + 1;
    } else {
        $comp_year  = $comp_year + 2;
    }
    $comp_date = $comp_year . '03' . '31';
    if ($comp_date <= $endymd) {
        return false;
    } else {
        return true;
    }
}
?>