<?php
//////////////////////////////////////////////////////////////////////////////
// ��� ���� �Ȳ�  new version  sales_actual_func.php                       //
// Copyright (C) 2020-2020 Ryota.Waki tnksys@nitto-kohki.co.jp              //
// Changed history                                                          //
// 2020/12/24 Created   assembly_comp_parts_list_func.php                   //
//////////////////////////////////////////////////////////////////////////////
require_once ('../../../function.php');            // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../../tnk_func.php');            // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../../MenuHeader.php');          // TNK ������ menu class
require_once ('../../../ControllerHTTP_Class.php');// TNK ������ MVC Controller Class

// CVS�����Ѵ�λ�������
function getKanryouDateCSV(&$res, &$field, $search)
{
    $range = "";    // �ϰϻ��ꤷ�ʤ��Τǡ����ߡ����ѿ����

    return getKanryouDate($res, $field, $search, $range);
}

// ɽ���Ѵ�λ�������
function getKanryouDateView(&$res, &$field, $search, $offset, $limit)
{
    $range = sprintf("OFFSET %d LIMIT %d", $offset, $limit);

    return getKanryouDate($res, $field, $search, $range);
}

// ��λ���پ������
function getKanryouDate(&$res, &$field, $search, $range)
{
    $query = sprintf("
                        SELECT DISTINCT
                                        assy_no                         AS �����ֹ�,    -- 0
                                        m.midsc                         AS ����̾,      -- 1
                                        plan_no                         AS �ײ��ֹ�,    -- 2
                                        comp_date                       AS ��Ω��λ��,  -- 3
                                        sub.parts_no                    AS �����ֹ�,    -- 4
                                        sub.midsc                       AS ����̾,      -- 5
                                        sub.unit_qt                     AS ���ѿ�,      -- 6
                                        comp_pcs                        AS ������,      -- 7
                                        ROUND(sub.unit_qt*comp_pcs, 2)  AS ����         -- 8�ʻ��ѿ��ߴ�������
                        FROM
                                        assembly_completion_history AS a                -- �������ʾ���������ֹ桦�ײ��ֹ桦��Ω��λ������������
                        LEFT OUTER JOIN
                                        assembly_schedule AS sche USING(plan_no)        -- ����ɸ���Ƚ����
                        LEFT OUTER JOIN
                                        miitem as m on assy_no=m.mipn                   -- ��̾�ѡ�����̾��
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
                                                                        )   -- �ǿ����ֹ����н���
                                        ) AS sub on assy_no=sub.p_parts_no            -- ���ʾ���������ֹ桦����̾�����ѿ���
                        %s
                        ORDER BY assy_no ASC, comp_date ASC, plan_no ASC, sub.parts_no ASC
                        %s
                     ", $search, $range);
    $res   = array();
    $field = array();
    if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
        return $rows;
    } else {
        $num = count($field);    // �ե�����ɿ�����
        // ���ѥ��ʤ�Ⱦ�ѥ��ʤإƥ���Ū�˥���С���
        for ($r=0; $r<$rows; $r++) {
            $res[$r][1] = mb_convert_kana($res[$r][1], 'ka', 'EUC-JP');   // ����̾
            $res[$r][5] = mb_convert_kana($res[$r][5], 'ka', 'EUC-JP');   // ����̾
        }
    }

//$_SESSION['s_sysmsg'] .= "offset ={$offset} : limit = {$limit} : rows = {$rows}";
/* ���֤�ʣ���������ʤϡ��Ť��Τ������ *
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

// ��λ���׼����ʷײ�����������������̹�ס�
function getKanryouAggregate(&$res, $search)
{
    $query = sprintf("
                        SELECT
                            sum(aggregate.plan) AS �ײ���,
                            sum(aggregate.comp) AS �������,
                            sum(aggregate.unit) AS ���̹��
                        FROM (
                                SELECT
                                                count(DISTINCT a.plan_no)           AS plan,
                                                sum(DISTINCT a.comp_pcs)            AS comp,
                                                sum(ROUND(sub.unit_qt*comp_pcs, 4)) AS unit
                                FROM
                                                assembly_completion_history AS a                -- �������ʾ���������ֹ桦�ײ��ֹ桦��Ω��λ������������
                                LEFT OUTER JOIN
                                                assembly_schedule AS sche USING(plan_no)        -- ����ɸ���Ƚ����
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
                                                                                )   -- �ǿ����ֹ����н���
                                                ) AS sub on assy_no=sub.p_parts_no            -- ���ʾ���������ֹ桦����̾�����ѿ���
                                %s
                                GROUP BY plan_no
                             ) AS aggregate
                     ", $search);
    $res   = array();
    $field = array();

    return getResultWithField3($query, $field, $res);
}
