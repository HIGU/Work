<?php
//////////////////////////////////////////////////////////////////////////////
// 売上 実績 照会  new version  sales_actual_func.php                       //
// Copyright (C) 2020-2020 Ryota.Waki tnksys@nitto-kohki.co.jp              //
// Changed history                                                          //
// 2020/12/24 Created   assembly_comp_parts_list_func.php                   //
//////////////////////////////////////////////////////////////////////////////
require_once ('../../../function.php');            // define.php と pgsql.php を require_once している
require_once ('../../../tnk_func.php');            // TNK に依存する部分の関数を require_once している
require_once ('../../../MenuHeader.php');          // TNK 全共通 menu class
require_once ('../../../ControllerHTTP_Class.php');// TNK 全共通 MVC Controller Class

// CVS出力用完了情報取得
function getKanryouDateCSV(&$res, &$field, $search)
{
    $range = "";    // 範囲指定しないので、ダミーで変数宣言

    return getKanryouDate($res, $field, $search, $range);
}

// 表示用完了情報取得
function getKanryouDateView(&$res, &$field, $search, $offset, $limit)
{
    $range = sprintf("OFFSET %d LIMIT %d", $offset, $limit);

    return getKanryouDate($res, $field, $search, $range);
}

// 完了明細情報取得
function getKanryouDate(&$res, &$field, $search, $range)
{
    $query = sprintf("
                        SELECT DISTINCT
                                        assy_no                         AS 製品番号,    -- 0
                                        m.midsc                         AS 製品名,      -- 1
                                        plan_no                         AS 計画番号,    -- 2
                                        comp_date                       AS 組立完了日,  -- 3
                                        sub.parts_no                    AS 部品番号,    -- 4
                                        sub.midsc                       AS 部品名,      -- 5
                                        sub.unit_qt                     AS 使用数,      -- 6
                                        comp_pcs                        AS 完成数,      -- 7
                                        ROUND(sub.unit_qt*comp_pcs, 2)  AS 数量         -- 8（使用数×完成数）
                        FROM
                                        assembly_completion_history AS a                -- 完成製品情報（製品番号・計画番号・組立完了日・完成数）
                        LEFT OUTER JOIN
                                        assembly_schedule AS sche USING(plan_no)        -- 特注・標準の判別用
                        LEFT OUTER JOIN
                                        miitem as m on assy_no=m.mipn                   -- 品名用（製品名）
                        LEFT OUTER JOIN
                                        (SELECT
                                                            pc.p_parts_no, pc.parts_no, mi.midsc, pc.unit_qt
                                         FROM               parts_configuration AS pc
                                         LEFT OUTER JOIN
                                                            miitem as mi on pc.parts_no=mi.mipn
                                         WHERE              pc.parts_no=(SELECT pc2.parts_no
                                                                         FROM   parts_configuration AS pc2
                                                                         WHERE  pc2.p_parts_no=pc.p_parts_no
                                                                           AND  SUBSTRING(pc.parts_no,1,8)=SUBSTRING(pc2.parts_no,1,8)
                                                                         ORDER BY pc2.p_parts_no ASC, pc2.parts_no DESC LIMIT 1
                                                                        )   -- 最新枝番号の抽出処理
                                        ) AS sub on assy_no=sub.p_parts_no            -- 部品情報（部品番号・部品名・使用数）
                        %s
                        ORDER BY assy_no ASC, comp_date ASC, plan_no ASC, sub.parts_no ASC
                        %s
                     ", $search, $range);
    $res   = array();
    $field = array();
    if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
        return $rows;
    } else {
        $num = count($field);    // フィールド数取得
        // 全角カナを半角カナへテスト的にコンバート
        for ($r=0; $r<$rows; $r++) {
            $res[$r][1] = mb_convert_kana($res[$r][1], 'ka', 'EUC-JP');   // 製品名
            $res[$r][5] = mb_convert_kana($res[$r][5], 'ka', 'EUC-JP');   // 部品名
        }
    }

//$_SESSION['s_sysmsg'] .= "offset ={$offset} : limit = {$limit} : rows = {$rows}";
/* 枝番が複数ある部品は、古いのを取り除く *
    $rec = 0;
    $num = count($field);
    for( $r=0; $r<$rows-1; $r++ ) {
        if( substr($res[$r][4], 0, 8) != substr($res[$r+1][4], 0, 8) ) {
            for( $f=0; $f<$num; $f++ ) {
                $res[$rec][$f] = $res[$r][$f];
            }
            $rec++;
        }
    }
    $rows = $rec;
/**/

    return $rows;
}

// 完了集計取得（計画件数・完成台数・数量合計）
function getKanryouAggregate(&$res, $search)
{
    $query = sprintf("
                        SELECT
                            sum(aggregate.plan) AS 計画件数,
                            sum(aggregate.comp) AS 完成台数,
                            sum(aggregate.unit) AS 数量合計
                        FROM (
                                SELECT
                                                count(DISTINCT a.plan_no)           AS plan,
                                                sum(DISTINCT a.comp_pcs)            AS comp,
                                                sum(ROUND(sub.unit_qt*comp_pcs, 4)) AS unit
                                FROM
                                                assembly_completion_history AS a                -- 完成製品情報（製品番号・計画番号・組立完了日・完成数）
                                LEFT OUTER JOIN
                                                assembly_schedule AS sche USING(plan_no)        -- 特注・標準の判別用
                                LEFT OUTER JOIN
                                                (SELECT
                                                                    pc.p_parts_no, pc.parts_no, mi.midsc, pc.unit_qt
                                                 FROM               parts_configuration AS pc
                                                 LEFT OUTER JOIN
                                                                    miitem as mi on pc.parts_no=mi.mipn
                                                 WHERE              pc.parts_no=(SELECT pc2.parts_no
                                                                                 FROM   parts_configuration AS pc2
                                                                                 WHERE  pc2.p_parts_no=pc.p_parts_no
                                                                                   AND  SUBSTRING(pc.parts_no,1,8)=SUBSTRING(pc2.parts_no,1,8)
                                                                                 ORDER BY pc2.p_parts_no ASC, pc2.parts_no DESC LIMIT 1
                                                                                )   -- 最新枝番号の抽出処理
                                                ) AS sub on assy_no=sub.p_parts_no            -- 部品情報（部品番号・部品名・使用数）
                                %s
                                GROUP BY plan_no
                             ) AS aggregate
                     ", $search);
    $res   = array();
    $field = array();

    return getResultWithField3($query, $field, $res);
}
