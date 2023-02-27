<?php
//////////////////////////////////////////////////////////////////////////////
// 製品グループ別 売上照会（総材料費比較）明細表 CSV出力                    //
// Copyright (C) 2011 - 2012 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2011/06/15 Created   material_compare_sale_view_csv.php                  //
// 2011/06/20 正式稼動                                                      //
// 2011/07/06 製品グループにカプラ標準とリニア標準を追加                    //
// 2012/03/29 製品グループの名称を変更                                      //
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
$csv_search = $_GET['csvsearch'];       // 共用サーチ
$first_ym   = $_GET['csvfirst_ym'];     // 基準年月
$d_start    = $_GET['csvd_start'];      // 開始年月日
$d_end      = $_GET['csvd_end'];        // 終了年月日
$div        = $_GET['csvdiv'];          // 製品グループ

// 製品グループ名の設定
if ($div == "A") $div_name  = "全グループ";
if ($div == "C") $div_name  = "標準品";
if ($div == "CC") $div_name = "カプラ標準品";
if ($div == "CL") $div_name = "リニア標準品";
if ($div == "S") $div_name  = "特注品";

$section    = $_GET['csvsection'];      // 分類名の取得
if ($section != " ") {
    $query_s = "
            SELECT  groupm.group_no                AS グループ番号     -- 0
                ,   groupm.group_name              AS グループ名       -- 1
            FROM
                product_serchGroup AS groupm
            WHERE
                groupm.group_no = {$section}
            ORDER BY
                group_name
        ";

    $res_s = array();
    if (($rows_s = getResultWithField2($query_s, $field_s, $res_s)) <= 0) {
        $section_name = '登録なし';
    } else {
        //$res_s[0][1] = mb_convert_kana($res_s[0][1], 'Ka', 'UTF-8'); // 半角カナを全角に 全角英数を半角英数に
        $section_name = $res_s[0][1];
    }
}

// 出力ファイル名の設定(分類名-開始年月日-終了年月日-基準年月-製品グループ形式）
$outputFile = $section_name . '-' . $d_start . '-' . $d_end . '-' . $first_ym . '-' . $div_name . '.csv';

// SQLのサーチ部で一時変更した部分を元に戻す
$search     = str_replace('keidate','計上日',$csv_search);
$search     = str_replace('jigyou','事業部',$search);
$search     = str_replace('denban','伝票番号',$search);
$search     = str_replace('/','\'',$search);

// サーチ部の文字コードをEUCに変更する（念のため）
$search     = mb_convert_encoding($search, 'UTF-8', 'auto');       // CSV用にEUCからSJISへ文字コード変換

// 実行者のパソコンにCSVを保存する為、ファイル名の文字コードをSJISに変換
$outputFile = mb_convert_encoding($outputFile, 'SJIS', 'auto');     // CSV用にEUCからSJISへ文字コード変換

// サーチ用年月日の計算
$cost1_ym = $first_ym;

$nen        = substr($cost1_ym, 0, 4);
$tsuki      = substr($cost1_ym, 4, 2);
$cost1_name = $nen . "/" . $tsuki;

if (substr($cost1_ym,4,2)!=12) {
    $cost1_ymd = $cost1_ym + 1;
    $cost1_ymd = $cost1_ymd . '10';
} else {
    $cost1_ymd = $cost1_ym + 100;
    $cost1_ymd = $cost1_ymd - 11;
    $cost1_ymd = $cost1_ymd . '10';
}
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
                                WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                WHEN m.midsc IS NULL THEN '&nbsp;'
                                ELSE substr(m.midsc,1,38)
                            END             as 製品名,                  -- 3
                            u.数量          as 数量,                    -- 4
                            u.単価          as 仕切単価,                -- 5
                            Uround(u.数量 * u.単価, 0) as 金額,         -- 6
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                            END                     as 総材料費,        -- 7
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN '-----'
                                ELSE to_char(mate.regdate, 'YYYY/MM/DD')
                            END                     AS 登録日,          -- 8
                            CASE
                                WHEN (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) 
                                    IS NULL THEN    CASE 
                                                        WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                                                        ELSE (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                                    END
                                ELSE (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                            END                     AS 基準総材料費     -- 9
                            ,
                            CASE
                                WHEN (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN '----------'
                                ELSE (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                            END                     AS 基準登録日       --10
                            ,
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE
                                CASE
                                    WHEN (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) 
                                    IS NULL THEN    CASE
                                                        WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                                                        ELSE (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                                    END
                                    ELSE (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                END
                            END                      AS 単価増減        --11
                            ,
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE
                                CASE
                                    WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                    ELSE
                                    CASE
                                        WHEN (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) 
                                        IS NULL THEN    CASE
                                                            WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                                                            ELSE Uround(((sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) * 100, 2)
                                                        END
                                        ELSE Uround(((sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) * 100, 2)
                                    END
                                END
                            END                      AS 増減率          --12
                            ,
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE
                                CASE
                                    WHEN (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) 
                                    IS NULL THEN    CASE
                                                        WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                                                        ELSE ((sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)) * u.数量
                                                    END
                                    ELSE ((sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)) * u.数量
                                END
                            END                      AS 金額増減        --13
                            ,
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE 
                                CASE
                                    WHEN u.単価 IS NULL THEN 0
                                    ELSE Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 2)
                                END
                            END                      AS 掛率            --14
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
                      left outer join
                        mshmas as p
                      on u.assyno=p.mipn
                      left outer join
                        -- mshgnm as gnm
                        msshg3 as gnm
                      -- on p.mhjcd=gnm.mhgcd
                      on p.mhshc=gnm.mhgcd
                      %s
                      order by 計上日, assyno
                      ", $search);   // 共用 $search で検索

$res_csv   = array();
$field_csv = array();
if (($rows_csv = getResultWithField3($query_csv, $field_csv, $res_csv)) <= 0) {
    exit();
} else {
    $num_csv = count($field_csv);       // フィールド数取得
    for ($r=0; $r<$rows_csv; $r++) {
        //$res_csv[$r][4] = mb_convert_kana($res_csv[$r][4], 'ka', 'UTF-8');       // 全角カナを半角カナへテスト的にコンバート
        $res_csv[$r][3] = str_replace(',',' ',$res_csv[$r][3]);                     // 製品名に,が入っているとCSVで桁がずれるので半角スペースに
        //$res_csv[$r][1] = mb_convert_encoding($res_csv[$r][1], 'SJIS', 'EUC');    // CSV用にEUCからSJISへ文字コード変換(EUC指定じゃないと直納・調整が化ける)
        $res_csv[$r][3] = mb_convert_encoding($res_csv[$r][3], 'SJIS', 'auto');     // CSV用にEUCからSJISへ文字コード変換
    }
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
touch($outputFile);
$fp = fopen($outputFile, "w");

foreach($csv_data as $line){
    fputcsv($fp,$line);         // ここでCSVファイルに書き出し
}
fclose($fp);

// ここからがCSVファイルのダウンロード（サーバー→クライアント）
touch($outputFile);
header("Content-Type: application/csv");
header("Content-Disposition: attachment; filename=".$outputFile);
header("Content-Length:".filesize($outputFile));
readfile($outputFile);
unlink("{$outputFile}");         // ダウンロード後ファイルを削除
?>