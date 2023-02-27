<?php
//////////////////////////////////////////////////////////////////////////////
// グループ別売上明細の照会 CSV出力                                         //
// Copyright (C) 2021-2021 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2021/08/20 Created   sales_pro_csv.php                                   //
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
$act_name   = $_GET['actname'];
$divg       = $_GET['divg'];
// SQLのサーチ部で一時変更した部分を元に戻す
$search     = str_replace('keidate','計上日',$csv_search);
$search     = str_replace('jigyou','事業部',$search);
$search     = str_replace('denban','伝票番号',$search);
$search     = str_replace('suryo','数量',$search);
$search     = str_replace('tanka','単価',$search);
$search     = str_replace('/','\'',$search);
// サーチ部の文字コードをEUCに変更する（念のため）
$search     = mb_convert_encoding($search, 'UTF-8', 'auto');   // CSV用にEUCからSJISへ文字コード変換

// ファイル名で一時変更した部分を元に戻す
$outputFile     = str_replace('ALL','全グループ',$outputFile);
$outputFile     = str_replace('C-all','カプラ全体',$outputFile);
$outputFile     = str_replace('C-hyou','カプラ標準',$outputFile);
$outputFile     = str_replace('C-toku','カプラ特注',$outputFile);
$outputFile     = str_replace('L-all','リニア全体',$outputFile);
$outputFile     = str_replace('TOOL','ツール',$outputFile);

///// 製品グループ名の設定
if ($divg != " ") {
    $query_s = "
            SELECT  groupm.group_no                AS グループ番号     -- 0
                ,   groupm.group_name              AS グループ名       -- 1
            FROM
                product_serchGroup AS groupm
            WHERE
                groupm.group_no = {$divg}
            ORDER BY
                group_name
        ";

    $res_s = array();
    if (($rows_s = getResultWithField2($query_s, $field_s, $res_s)) <= 0) {
        $_SESSION['s_sysmsg'] = "グループの登録がありません！";
        $field[0]   = "グループ番号";
        $field[1]   = "グループ名";
        $_SESSION['s_sysmsg'] = "登録がありません！";
        //$result->add_array2('res_s', '');
        //$result->add_array2('field_s', '');
        //$result->add('num_s', 2);
        //$result->add('rows_s', '');
        $div_name = '';
    } else {
        $num_s = count($field_s);
        //$result->add_array2('res_s', $res_s);
        //$result->add_array2('field_s', $field_s);
        //$result->add('num_s', $num_s);
        //$result->add('rows_s', $rows_s);
        $div_name = $res_s[0][1];
    }
}

$outputFile = $outputFile . '-' . $div_name . '-' . '売上明細.csv';

// 実行者のパソコンにCSVを保存する為、ファイル名の文字コードをSJISに変換
$outputFile = mb_convert_encoding($outputFile, 'SJIS', 'auto');   // CSV用にEUCからSJISへ文字コード変換

//////////// 表形式のデータ表示用のサンプル Query & 初期化
if ($act_name != 'C-toku') {      // Ｃ特注 以外なら
    $query_csv = sprintf("select
                            u.計上日        as 計上日,                  -- 0
                            CASE
                                WHEN u.datatype=1 THEN '完成'
                                WHEN u.datatype=2 THEN '個別'
                                WHEN u.datatype=3 THEN '手打'
                                WHEN u.datatype=4 THEN '調整'
                                WHEN u.datatype=5 THEN '移動'
                                WHEN u.datatype=6 THEN '直納'
                                WHEN u.datatype=7 THEN '売上'
                                WHEN u.datatype=8 THEN '振替'
                                WHEN u.datatype=9 THEN '受注'
                                ELSE u.datatype
                            END             as 区分,                    -- 1
                            CASE
                                WHEN trim(u.計画番号)='' THEN '---'         --NULLでなくてスペースで埋まっている場合はこれ！
                                ELSE u.計画番号
                            END                     as 計画番号,        -- 2
                            CASE
                                WHEN trim(u.assyno) = '' THEN '---'
                                ELSE u.assyno
                            END                     as 製品番号,        -- 3
                            CASE
                                WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                WHEN m.midsc IS NULL THEN '&nbsp;'
                                ELSE substr(m.midsc,1,38)
                            END             as 製品名,                  -- 4
                            CASE
                                WHEN trim(u.入庫場所)='' THEN '--'         --NULLでなくてスペースで埋まっている場合はこれ！
                                ELSE u.入庫場所
                            END                     as 入庫,            -- 5
                            u.数量          as 数量,                    -- 6
                            u.単価          as 仕切単価,                -- 7
                            Uround(u.数量 * u.単価, 0) as 金額,         -- 8
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL 
                                    THEN CASE
                                            WHEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=u.assyno order by assy_no DESC, regdate DESC limit 1) IS NULL
                                                THEN (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=u.assyno AND as_regdate<計上日 AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                            ELSE (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=u.assyno order by assy_no DESC, regdate DESC limit 1)
                                         END
                                ELSE sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                            END                     as 総材料費,        -- 9
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL
                                    THEN CASE
                                            WHEN Uround(u.単価 / ((select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=u.assyno order by assy_no DESC, regdate DESC limit 1)), 3) * 100 IS NULL
                                                THEN Uround(u.単価 / (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=u.assyno AND as_regdate<計上日 AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1), 3) * 100
                                            ELSE Uround(u.単価 / ((select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=u.assyno order by assy_no DESC, regdate DESC limit 1)), 3) * 100
                                         END
                                ELSE Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                            END                     as 率％             -- 10
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
} else {    ////////////////////////////////////////// Ｃ特注の場合
    $query_csv = sprintf("select
                            u.計上日        as 計上日,                  -- 0
                            CASE
                                WHEN u.datatype=1 THEN '完成'
                                WHEN u.datatype=2 THEN '個別'
                                WHEN u.datatype=3 THEN '手打'
                                WHEN u.datatype=4 THEN '調整'
                                WHEN u.datatype=5 THEN '移動'
                                WHEN u.datatype=6 THEN '直納'
                                WHEN u.datatype=7 THEN '売上'
                                WHEN u.datatype=8 THEN '振替'
                                WHEN u.datatype=9 THEN '受注'
                                ELSE u.datatype
                            END             as 区分,                    -- 1
                            CASE
                                WHEN trim(u.計画番号)='' THEN '---'        --NULLでなくてスペースで埋まっている場合はこれ！
                                ELSE u.計画番号
                            END                     as 計画番号,        -- 2
                            u.assyno        as 製品番号,                -- 3
                            CASE
                                WHEN m.midsc IS NULL THEN '&nbsp;'
                                ELSE substr(m.midsc,1,18)
                            END                     as 製品名,          -- 4
                            CASE
                                WHEN trim(u.入庫場所)='' THEN '--'         --NULLでなくてスペースで埋まっている場合はこれ！
                                ELSE u.入庫場所
                            END                     as 入庫,            -- 5
                            u.数量          as 数量,                    -- 6
                            u.単価          as 仕切単価,                -- 7
                            Uround(u.数量 * u.単価, 0) as 金額,         -- 8
                            trim(a.note15)  as 工事番号,                -- 9
                            aden.order_price  as 販売単価,              --10
                            CASE
                                WHEN aden.order_price <= 0 THEN '0'
                                ELSE Uround(u.単価 / aden.order_price, 3) * 100
                            END                     as 率％,            --11
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) = 0 THEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=u.assyno order by assy_no DESC, regdate DESC limit 1)
                                ELSE sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                            END                     as 総材料費,        -- 12
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) = 0 THEN Uround(u.単価 / ((select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=u.assyno order by assy_no DESC, regdate DESC limit 1)), 3) * 100
                                ELSE Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                            END                     as 率％             -- 13
                      from
                            (hiuuri as u left outer join miitem as m on u.assyno=m.mipn)
                      left outer join
                            assembly_schedule as a
                      on u.計画番号=a.plan_no
                      left outer join
                            aden_master as aden
                      -- on (a.parts_no=aden.parts_no and a.plan_no=aden.plan_no)
                      on (a.plan_no=aden.plan_no)
                      left outer join
                            material_cost_header as mate
                      on u.計画番号=mate.plan_no
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
}

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
        $res_csv[$r][4] = str_replace(',',' ',$res_csv[$r][4]);                   // 製品名に,が入っているとCSVで桁がずれるので半角スペースに
        $res_csv[$r][1] = mb_convert_encoding($res_csv[$r][1], 'SJIS', 'EUC');   // CSV用にEUCからSJISへ文字コード変換(EUC指定じゃないと直納・調整が化ける)
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