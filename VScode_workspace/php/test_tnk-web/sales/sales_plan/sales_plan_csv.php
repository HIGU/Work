<?php
//////////////////////////////////////////////////////////////////////////////
// 売上予定の照会 CSV出力                                                   //
// Copyright (C) 2011-2018 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2011/11/21 Created   sales_plan_csv.php                                  //
// 2012/03/28 ＮＫＴ部品出庫分(NKTB)の照会を追加                            //
// 2013/01/28 バイモルを液体ポンプへ変更 表示のみデータはバイモルのまま     //
// 2016/03/03 NKTBのSQLミス訂正（部品名ではなく製品名を取得）               //
// 2018/08/21 特注A伝販売単価52％に対応                                大谷 //
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
$outputFile = $_GET['csvname'] . '売上予定.csv';
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
if ($div == "NKTB") {  // NKT部品出庫分の場合は別SQLで集計
    $query_csv = sprintf("select
                        a.chaku                     AS 出庫予定,  -- 0
                        CASE
                            WHEN trim(a.plan_no)='' THEN '---'        --NULLでなくてスペースで埋まっている場合はこれ！
                            ELSE a.plan_no
                        END                           AS 計画番号,    -- 1
                        CASE
                            WHEN trim(allo.parts_no) = '' THEN '---'
                            ELSE allo.parts_no
                        END                           AS 部品番号,    -- 2
                        CASE
                            WHEN trim(substr(m.midsc,1,38)) = '' THEN ''
                            WHEN m.midsc IS NULL THEN ''
                            ELSE substr(m.midsc,1,38)
                        END                           AS 部品名,      -- 3
                        CASE
                            WHEN trim(substr(m2.midsc,1,38)) = '' THEN ''
                            WHEN m2.midsc IS NULL THEN ''
                            ELSE substr(m2.midsc,1,38)
                        END                           AS 製品名,      -- 4
                        (allo.allo_qt - allo.sum_qt) AS 数量,        -- 5
                        (SELECT price FROM sales_price_nk WHERE parts_no = allo.parts_no LIMIT 1)
                                                      AS 仕切単価,    -- 6
                        Uround((allo.allo_qt - allo.sum_qt) * (SELECT price FROM sales_price_nk WHERE parts_no = allo.parts_no LIMIT 1), 0)
                                                      AS 金額        -- 7
                  FROM
                        assembly_schedule as a
                  LEFT OUTER JOIN
                        allocated_parts as allo
                  on a.plan_no=allo.plan_no
                  left outer join
                        miitem as m
                  on allo.parts_no=m.mipn
                  left outer join
                        miitem as m2
                  on a.parts_no=m2.mipn
                  %s
                  order by a.chaku, a.plan_no, allo.parts_no
                  ", $search);   // 共用 $search で検索
} elseif ($div == "C-toku") {    // 特注の場合
    if ($shikiri == "A") {    // A伝販売単価52％の場合
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
                                (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1)
                                                              AS 仕切単価,    -- 5
                                Uround((a.plan -a.cut_plan - kansei) * (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1), 0)
                                                              AS 金額,        -- 6
                                sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                              AS 総材料費,    -- 7
                                CASE
                                    WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                    ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                                END                           AS 率％,        -- 8
                                (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS 総材料費2,   -- 9
                                (select Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS 率２,        --10
                                (select plan_no from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS 計画番号2,   --11
                                (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                              AS 部品材料費,  --12
                                (SELECT reg_no FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                              AS 単価登録番号, --13
                                CASE
                                    WHEN trim(a.plan_no)='' THEN '&nbsp;'        --NULLでなくてスペースで埋まっている場合はこれ！
                                    ELSE substr(a.plan_no,4,5)
                                END                           AS 計画番号3    -- 14
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
                          order by a.kanryou, 計画番号3
                          ", $search);   // 共用 $search で検索
    } elseif ($shikiri == "AS") {    // A伝販売単価52％＞最新仕切の場合
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
                                CASE
                                    WHEN (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1) = 0 THEN (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                                    WHEN (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1) IS NULL THEN (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                                    ELSE (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1)
                                END                           AS 仕切単価,    -- 5
                                CASE
                                    WHEN (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1) = 0 THEN Uround((a.plan -a.cut_plan - kansei) * (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1), 0)
                                    WHEN (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1) IS NULL THEN Uround((a.plan -a.cut_plan - kansei) * (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1), 0)
                                    ELSE Uround((a.plan -a.cut_plan - kansei) * (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1), 0)
                                END
                                                              AS 金額,        -- 6
                                sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                              AS 総材料費,    -- 7
                                CASE
                                    WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                    ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                                END                           AS 率％,        -- 8
                                (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS 総材料費2,   -- 9
                                (select Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS 率２,        --10
                                (select plan_no from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS 計画番号2,   --11
                                (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                              AS 部品材料費,  --12
                                (SELECT reg_no FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                              AS 単価登録番号, --13
                                CASE
                                    WHEN trim(a.plan_no)='' THEN '&nbsp;'        --NULLでなくてスペースで埋まっている場合はこれ！
                                    ELSE substr(a.plan_no,4,5)
                                END                           AS 計画番号3    -- 14
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
                          order by a.kanryou, 計画番号3
                          ", $search);   // 共用 $search で検索
    } else {    // 最新仕切の場合
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
                                                              AS 仕切単価,    -- 5
                                Uround((a.plan -a.cut_plan - kansei) * (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1), 0)
                                                              AS 金額,        -- 6
                                sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                              AS 総材料費,    -- 7
                                CASE
                                    WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                    ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                                END                           AS 率％,        -- 8
                                (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS 総材料費2,   -- 9
                                (select Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS 率２,        --10
                                (select plan_no from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS 計画番号2,   --11
                                (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                              AS 部品材料費,  --12
                                (SELECT reg_no FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                              AS 単価登録番号, --13
                                CASE
                                    WHEN trim(a.plan_no)='' THEN '&nbsp;'        --NULLでなくてスペースで埋まっている場合はこれ！
                                    ELSE substr(a.plan_no,4,5)
                                END                           AS 計画番号3    -- 14
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
    }
} else {
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
                                                          AS 仕切単価,    -- 5
                            Uround((a.plan -a.cut_plan - kansei) * (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1), 0)
                                                          AS 金額,        -- 6
                            sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                          AS 総材料費,    -- 7
                            CASE
                                WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                            END                           AS 率％,        -- 8
                            (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                          AS 総材料費2,   -- 9
                            (select Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                          AS 率２,        --10
                            (select plan_no from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                          AS 計画番号2,   --11
                            (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                          AS 部品材料費,  --12
                            (SELECT reg_no FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                          AS 単価登録番号, --13
                            CASE
                                WHEN trim(a.plan_no)='' THEN '&nbsp;'        --NULLでなくてスペースで埋まっている場合はこれ！
                                ELSE substr(a.plan_no,4,5)
                            END                           AS 計画番号3    -- 14
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
                      order by a.kanryou, 計画番号3
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