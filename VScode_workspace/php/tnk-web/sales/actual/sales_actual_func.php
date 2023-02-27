<?php
//////////////////////////////////////////////////////////////////////////////
// 売上 実績 照会  new version  sales_actual_func.php                       //
// Copyright (C) 2020-2020 Ryota.Waki tnksys@nitto-kohki.co.jp              //
// Changed history                                                          //
// 2020/12/17 Created   sales_actual_func.php                               //
//////////////////////////////////////////////////////////////////////////////
require_once ('../../function.php');            // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');            // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');          // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php');// TNK 全共通 MVC Controller Class

// ＯＥＭですか？
function IsOem($line_no)
{
    $line_no = trim($line_no);
    if( $line_no == "LC2"
     || $line_no == "LO1"
     || $line_no == "3LC2"
     || $line_no == "3DL1"
     || $line_no == "3LO1" )
    {
        return true;    // ＯＥＭ
    } else {
        return false;   // ＯＥＭ 以外
    }
}

// 補用ですか？
function IsHoyou($line_no)
{
    if( $line_no == "3LH2"
     || $line_no == "3LH1"
     || $line_no == "3LP3" )
    {
        return true;    // 補用
    } else {
        return false;   // 補用 以外
    }
}

// ラインNo.取得
function getLineNo($plan, $parts)
{
    $query = sprintf("
                SELECT
                        CASE
                            WHEN trim(line_no)='' THEN '----'         --NULLでなくてスペースで埋まっている場合はこれ！
                            ELSE line_no
                        END
                FROM
                        assembly_schedule
                WHERE
                        plan_no = '$plan' AND parts_no = '$parts'
                LIMIT 1
                ", $plan, $parts);

    $res   = array();
    $field = array();
    if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
        return "----";
    }
    return $res[0][0];
}

// 未検収データ取得
function getMikenData(&$res)
{
    $file_orign     = '../..' . SYS . 'backup/W#TIUKSL.TXT';
//    $res            = array();
    $total_price    = 0;    // 金額
    $total_ken      = 0;    // 件数
    $total_count    = 0;    // 数量
    $rec            = 0;    // レコード№
    if (file_exists($file_orign)) {         // ファイルの存在チェック
        $fp = fopen($file_orign, 'r');
        while (!(feof($fp))) {
            $data = fgetcsv($fp, 130, '_');     // 実レコードは103バイトなのでちょっと余裕をデリミタは'_'に注意
            if (feof($fp)) {
                break;
            }
            $num  = count($data);       // フィールド数の取得
            if ($num != 14) {   // AS側の削除レコードは php-4.3.5で0返し php-4.3.6で1を返す仕様になった。fgetcsvの仕様変更による
               continue;
            }
            for ($f=0; $f<$num; $f++) {
                $res[$rec][$f] = mb_convert_encoding($data[$f], 'UTF-8', 'SJIS');       // SJISをEUC-JPへ変換
                $res[$rec][$f] = addslashes($res[$rec][$f]);    // "'"等がデータにある場合に\でエスケープする
                // $data_KV[$f] = mb_convert_kana($data[$f]);   // 半角カナを全角カナに変換
            }
            if($res[$rec][5] !='C8385407') {
                $query = sprintf("select midsc from miitem where mipn='%s' limit 1", $res[$rec][3]);
                getUniResult($query, $res[$rec][4]);       // 製品名の取得 (製品コードを上書きする)
                /******** 総材料費の登録済みの項目追加 *********/
                $sql = "
                    SELECT plan_no FROM material_cost_header WHERE plan_no='{$res[$rec][5]}'
                ";
                if (getUniResult($sql, $temp) <= 0) {
                    $res[$rec][13] = '登録';
                    $sql_c = "
                        SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE assy_no = '{$res[$rec][3]}' ORDER BY assy_no DESC, regdate DESC LIMIT 1
                    ";
                    if (($rows_c = getResultWithField3($sql_c, $field_c, $res_c)) <= 0) {
                    } else {
                    }
                } else {
                    $res[$rec][13] = '登録済';
                    $sql_c = "
                        SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no='{$res[$rec][5]}' AND assy_no = '{$res[$rec][3]}' ORDER BY assy_no DESC, regdate DESC LIMIT 1
                    ";
                    if (($rows_c = getResultWithField3($sql_c, $field_c, $res_c)) <= 0) {
                    } else {
                    }
                }
                /******** 特注・標準の項目追加 *********/
                $sql2 = "
                    SELECT substr(note15, 1, 2) FROM assembly_schedule WHERE plan_no='{$res[$rec][5]}'
                ";
                $sc = '';
                getUniResult($sql2, $sc);
                if ($sc == 'SC') {
                    $res[$rec][15] = '特注';
                } else {
                    $res[$rec][15] = '標準';
                }
                /******** 仕切単価が元データにない場合の上書き処理 *********/
                if ($res[$rec][12] == 0) {                                  // 元データに仕切があるかどうか
                    $res[$rec][14] = '1';
                    $sql = "
                        SELECT price FROM sales_price_nk WHERE parts_no='{$res[$rec][3]}'
                    ";
                    if (getUniResult($sql, $sales_price) <= 0) {            // 最新仕切が登録されているか
                        $sql = "
                            SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no='{$res[$rec][5]}' AND assy_no = '{$res[$rec][3]}' ORDER BY assy_no DESC, regdate DESC LIMIT 1
                        ";
                        if (getUniResult($sql, $sales_price) <= 0) {        // 計画の総材料費が登録されているか
                            $sql_c = "
                                SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE assy_no = '{$res[$rec][3]}' ORDER BY assy_no DESC, regdate DESC LIMIT 1
                            ";
                            if (getUniResult($sql, $sales_price) <= 0) {    // 製品の総材料費が登録されているか
                                $res[$rec][12] = 0;
                            } else {
                                if ($res[$rec][15] == '特注') {
                                    $res[$rec][12] = round(($sales_price * 1.27), 2);   // 特注のときの倍率？
                                } else {
                                    $res[$rec][12] = round(($sales_price * 1.13), 2);
                                }
                            }
                        } else {
                            if ($res[$rec][15] == '特注') {
                                $res[$rec][12] = round(($sales_price * 1.27), 2);       // 特注のときの倍率？
                            } else {
                                $res[$rec][12] = round(($sales_price * 1.13), 2);
                            }
                        }
                    } else {
                        $res[$rec][12] = $sales_price;
                    }
                } else {
                    $res[$rec][14] = '0';
                }
                /******** 集計 計算 *********/
                $res[$rec][16] = round(($res[$rec][11] * $res[$rec][12]), 0);

                $rec++;
            }
        }
        // 0=>'事業部', 1=>'完成日', 3=>'製品番号', 4=>'製品名', 5=>'計画番号', 11=>'完成数', 12=>'仕切単価'
    }
    return $rec;
}

// 未検収データ取得
function getMikenSlectData(&$res, $div, $target_ym)
{
    $rows_miken_all = getMikenData($res_miken_all);

    if( $rows_miken_all <= 0 ) return 0;

    if( substr($res_miken_all[0][1], 0, 6) != $target_ym ) return 0;

    if( $div == "D") {  // カプラ標準
//$_SESSION['s_sysmsg'] .= "カプラ標準:";
        $jigyou = 'C';
        $type = '標準';
    } else if( $div == "S") {  // カプラ特注
//$_SESSION['s_sysmsg'] .= "カプラ特注:";
        $jigyou = 'C';
        $type = '特注';
    } else if( $div == "L" ) {  // リニア
//$_SESSION['s_sysmsg'] .= "リニア:";
        $jigyou = 'L';
        $type = '標準';
    }

    $rows_miken = $rec = 0;
    for( $r=0; $r<$rows_miken_all; $r++ ) {
        if( $res_miken_all[$r][0] == $jigyou && $res_miken_all[$r][15] == $type ) {
            $res[$rec][0] = $res_miken_all[$r][0]; // 事業部
            $res[$rec][1] = $res_miken_all[$r][1]; // 完成日
            $res[$rec][2] = $res_miken_all[$r][3]; // 製品番号
            $res[$rec][3] = $res_miken_all[$r][4]; // 製品名
            $res[$rec][3] = mb_convert_kana($res[$rec][3], 'ka', 'UTF-8');   // 全角カナを半角カナへテスト的にコンバート
            $res[$rec][4] = $res_miken_all[$r][5]; // 計画番号
            $res[$rec][5] = $res_miken_all[$r][11]; // 完成数
            $res[$rec][6] = $res_miken_all[$r][12]; // 仕切単価
            $res[$rec][7] = $res_miken_all[$r][16]; // 金額
            $rec++;
        }
    }
    return $rec;
}

// 月初売上予定取得
function getFirstPlan(&$res, &$field, $search)
{
    $query = sprintf("
                SELECT
                        m.kanryou           AS 完了予定日,  -- 0
                        m.plan_no           AS 計画番号,    -- 1
                        m.parts_no          AS 製品番号,    -- 2
                        m.midsc             AS 製品名,      -- 3
                        m.plan              AS 数量,        -- 4
                        m.partition_price   AS 仕切金額,    -- 5
                        m.price             AS 金額,        -- 6
                        m.line_no           AS ラインNo,    -- 7
                        m.state             AS 区分,        -- 8
                        m.complete_plan     AS 完成数,      -- 9
                        m.complete_price    AS 完成金額,    -- 10
                        m.remarks           AS 備考         -- 11
                FROM
                        month_first_sales_plan  AS m
                LEFT OUTER JOIN
                        assembly_schedule       AS a    USING(plan_no)
                %s
                ORDER BY m.kanryou ASC, m.plan_no ASC, m.parts_no ASC
                ", $search);

    $res   = array();
    $field = array();
    return getResultWithField3($query, $field, $res);
}

// 売上予定取得
function getSalsePlan(&$res, $search)
{
    $query = sprintf("select
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

    $res   = array();
    $field = array();
    if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
//        $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>売上予定のデータがありません。<br>%s～%s</font><BR>", format_date($d_start), format_date($d_end));
//        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
//        exit();
    } else {
        $num = count($field);       // フィールド数取得
        for ($r=0; $r<$rows; $r++) {
            $res[$r][3] = mb_convert_kana($res[$r][3], 'ka', 'UTF-8');   // 全角カナを半角カナへテスト的にコンバート
        }
    }
    return $rows;
}

// 売上明細取得
function getSalesDetails(&$res, $div, $search)
{
    if ($div != 'S') {      // Ｃ特注 以外なら
        $query = sprintf("select
                                u.計上日        as 計上日,                  -- 0
                                CASE
                                    WHEN u.datatype=1 THEN '完成'
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
                                    WHEN trim(a.line_no)='' THEN '----'         --NULLでなくてスペースで埋まっている場合はこれ！
                                    ELSE a.line_no
                                END                     as ライン,
                                sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                        as 総材料費,        -- 9
                                CASE
                                    WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                    ELSE Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                                END                     as 率％,            --10
                                (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=u.assyno AND regdate<=計上日 order by assy_no DESC, regdate DESC limit 1)
                                                        AS 総材料費2,       --11
                                (select Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=u.assyno AND regdate<=計上日 order by assy_no DESC, regdate DESC limit 1)
                                                        AS 率２,            --12
                                (select plan_no from material_cost_header where assy_no=u.assyno AND regdate<=計上日 order by assy_no DESC, regdate DESC limit 1)
                                                        AS 計画番号2,       --13
                                (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=u.assyno AND as_regdate<計上日 AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                        AS 部品材料費,      --14
                                (SELECT reg_no FROM parts_cost_history WHERE parts_no=u.assyno AND as_regdate<計上日 AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                        AS 単価登録番号     --15
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
                                product_support_master AS groupm
                          on u.assyno=groupm.assy_no
                          %s
                          order by 計上日, assyno
                          ", $search);   // 共用 $search で検索
    } else {    ////////////////////////////////////////// Ｃ特注の場合
        $query = sprintf("select
                                u.計上日        as 計上日,                  -- 0
                                CASE
                                    WHEN u.datatype=1 THEN '完成'
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
                                CASE
                                    WHEN trim(a.line_no)='' THEN '----'         --NULLでなくてスペースで埋まっている場合はこれ！
                                    ELSE a.line_no
                                END                     as ライン,
                                trim(a.note15)  as 工事番号,                -- 9
                                aden.order_price  as 販売単価,              --10
                                CASE
                                    WHEN aden.order_price <= 0 THEN '0'
                                    ELSE Uround(u.単価 / aden.order_price, 3) * 100
                                END                     as 率％,            --11
                                sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                        as 総材料費,        --12
                                CASE
                                    WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                    ELSE Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                                END                     as 率％             --13
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
                                product_support_master AS groupm
                          on u.assyno=groupm.assy_no
                          %s
                          order by 計上日, assyno
                          ", $search);   // 共用 $search で検索
    }

    $res   = array();
    $field = array();
    if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
//        $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>売上明細のデータがありません。<br>%s～%s</font>", format_date($d_start), format_date($d_end) );
//        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
//        exit();
    } else {
        for ($r=0; $r<$rows; $r++) {
            $res[$r][4] = mb_convert_kana($res[$r][4], 'ka', 'UTF-8');   // 全角カナを半角カナへテスト的にコンバート
        }
    }
    return $rows;
}

// 月初予定と明細比較
function DiffFirstDetails(&$res_f, &$rows_f, &$res_d, $rows_d)
{
    // 完了を１レコードずつ月初の月初予定と比較
    for( $d=0; $d<$rows_d; $d++ ) {
        $flg_add = true;
        for( $f=0; $f<$rows_f; $f++ ) {
            if( $res_d[$d][2] == $res_f[$f][1] && $res_d[$d][3] == $res_f[$f][2] ) { // 同一計画・製品番号
                $res_f[$f][9] = $res_f[$f][9] + $res_d[$d][6];
                $res_f[$f][10] = round($res_f[$f][9]*$res_d[$d][7]);
                if( $res_f[$f][8] != "" ) {
//                    $res_f[$f][10] = round($res_f[$f][9]*$res_d[$d][7]);
                } else {
                    $res_f[$f][8] = "完了";
//                    $res_f[$f][10] = round($res_f[$f][9]*$res_d[$d][7]);
                }
                $flg_add = false;
            }
        }
        if( $flg_add ) {
            $rows_f++;
            $res_f[$f][0] = $res_d[$d][0];                          // 完成予定日（完成日）
            $res_f[$f][1] = $res_d[$d][2];                          // 計画番号
            $res_f[$f][2] = $res_d[$d][3];                          // 製品番号
            $res_f[$f][3] = $res_d[$d][4];                          // 製品名
            $res_f[$f][4] = "　";                                   // 数量
            $res_f[$f][5] = $res_d[$d][7];                          // 仕切単価
            $res_f[$f][6] = "　";                                   // 金額
            if( !empty($res_d[$d][9]) ) {
                $res_f[$f][7] = $res_d[$d][9];                          // ラインNo.
            } else {
                $res_f[$f][7] = "　";                          // ラインNo.
            }

            $res_f[$f][8] = "追加";                                 // 区分
            $res_f[$f][9] = $res_d[$d][6];                          // 完成数
            $res_f[$f][10] = round($res_f[$f][9]*$res_d[$d][7]);    // 完成金額
        }
    }
}

// 月初予定と未検収比較
function DiffFirstMiken(&$res_f, &$rows_f, &$res_m, $rows_m)
{
    for( $m=0; $m<$rows_m; $m++ ) {
        $flg_add = true;
        for( $f=0; $f<$rows_f; $f++ ) {
            if( $res_m[$m][4] == $res_f[$f][1] && $res_m[$m][2] == $res_f[$f][2] ) { // 同一計画・製品番号
                $cnt = sprintf( "%d", $res_m[$m][5] );
                $res_f[$f][9] += $cnt;
                $res_f[$f][10] = round($res_f[$f][9]*$res_f[$f][5]);

                if( $res_f[$f][8] != "" ) {
//                    $res_f[$f][4] = $res_f[$f][9];
//                    $res_f[$f][6] = round($res_f[$f][4]*$res_f[$f][5]);
//                    $res_f[$f][10] = round($res_f[$f][9]*$res_f[$f][5]);
                } else {
                    $res_f[$f][8] = "未検収";
//                    $res_f[$f][10] = round($res_f[$f][9]*$res_f[$f][5]);
                }
                $flg_add = false;
            }
        }
        if( $flg_add ) {
            $rows_f++;
            $res_f[$f][0]   = $res_m[$m][1];                            // 完成予定日（完成日）
            $res_f[$f][1]   = $res_m[$m][4];                            // 計画番号
            $res_f[$f][2]   = $res_m[$m][2];                            // 製品番号
            $res_f[$f][3]   = $res_m[$m][3];                            // 製品名
            $res_f[$f][4]   = "　";                                     // 数量
            $res_f[$f][5]   = $res_m[$m][6];                            // 仕切単価
            $res_f[$f][6]   = "　";                                     // 金額
            $res_f[$f][7]   = getLineNo($res_f[$f][1], $res_f[$f][2]);  // ラインNo.
            $res_f[$f][8]   = "追加";                                   // 区分
            $res_f[$f][9]   = sprintf( "%d", $res_m[$m][5] );           // 完成数
            $res_f[$f][10]  = round($res_f[$f][9]*$res_f[$f][5]);       // 完成金額
        }
    }
}

// 月初予定と予定比較
function DiffFirstPlan(&$res_f, $rows_f, &$res_p, $rows_p)
{
    for( $p=0; $p<$rows_p; $p++ ) {
        for( $f=0; $f<$rows_f; $f++ ) {
            if( $res_p[$p][1] == $res_f[$f][1] && $res_p[$p][2] == $res_f[$f][2] ) { // 同一計画・製品番号
                $res_f[$f][8] = "予定あり";
            }
        }
    }
}

// 売上取得
function getSales($d_start, $d_end, $div)
{
    ///////////// 合計金額・件数等を取得
    if ( ($div != 'S') && ($div != 'D') ) {      // Ｃ特注と標準 以外なら
        $query = "select
                        count(DISTINCT 計画番号) as t_ken,
                        sum(数量) as t_kazu,
                        sum(Uround(数量*単価,0)) as t_kingaku
                  from
                        hiuuri
                  left outer join
                        product_support_master AS groupm
                  on assyno=groupm.assy_no
                  left outer join
                        miitem as m
                  on assyno=m.mipn";
    } else {
        $query = "select
                        count(DISTINCT 計画番号) as t_ken,
                        sum(数量) as t_kazu,
                        sum(Uround(数量*単価,0)) as t_kingaku
                  from
                        hiuuri
                  left outer join
                        assembly_schedule as a
                  on 計画番号=plan_no
                  left outer join
                        product_support_master AS groupm
                  on assyno=groupm.assy_no
                  left outer join
                        miitem as m
                  on assyno=m.mipn";
    }
    //////////// SQL where 句を 共用する
    $search = "where 計上日>=$d_start and 計上日<=$d_end";
    if ($div == 'S') {    // Ｃ特注なら
        $search .= " and 事業部='C' and note15 like 'SC%%'";
        $search .= " and (assyno not like 'NKB%%') and (assyno not like 'SS%%')";
        $search .= " and CASE WHEN 計上日>=20111101 and 計上日<20130501 THEN groupm.support_group_code IS NULL ELSE 事業部='C' END";
    } elseif ($div == 'D') {    // Ｃ標準なら
        $search .= " and 事業部='C' and (note15 NOT like 'SC%%' OR note15 IS NULL)";    // 部品売りを標準へする
        $search .= " and (assyno not like 'NKB%%') and (assyno not like 'SS%%')";
        $search .= " and (CASE WHEN 計上日>=20111101 and 計上日<20130501 THEN groupm.support_group_code IS NULL ELSE 事業部='C' END)";
    } elseif ($div == "L") {
        $search .= " and 事業部='$div'";
        $search .= " and (assyno not like 'NKB%%') and (assyno not like 'SS%%')";
    }
    $search .= " and datatype='1'";

    $query = sprintf("$query %s", $search);     // SQL query 文の完成
    $res_sum = array();
    if (getResult($query, $res_sum) <= 0) {
//        $_SESSION['s_sysmsg'] = '合計金額の取得に失敗しました。';
//        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
//        exit();
        return 0;
    } else {
//        $t_ken     = $res_sum[0]['t_ken'];
//        $t_kazu    = $res_sum[0]['t_kazu'];
//        $t_kingaku = $res_sum[0]['t_kingaku'];
//        return $res_sum[0]['t_kingaku'];
        return $res_sum;
    }
}

// 前期売上取得
function getPreviousSeasonSales($target_ym, $div)
{
    $d_start = substr($target_ym,0,4)-1 . substr($target_ym,5,2) . "01";
    $d_end   = substr($target_ym,0,4)-1 . substr($target_ym,5,2) . "99";

    return getSales($d_start, $d_end, $div);
}
