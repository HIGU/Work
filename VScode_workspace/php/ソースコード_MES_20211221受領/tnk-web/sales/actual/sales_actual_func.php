<?php
//////////////////////////////////////////////////////////////////////////////
// ��� ���� �Ȳ�  new version  sales_actual_func.php                       //
// Copyright (C) 2020-2020 Ryota.Waki tnksys@nitto-kohki.co.jp              //
// Changed history                                                          //
// 2020/12/17 Created   sales_actual_func.php                               //
//////////////////////////////////////////////////////////////////////////////
require_once ('../../function.php');            // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');            // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');          // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php');// TNK ������ MVC Controller Class

// �ϣţͤǤ�����
function IsOem($line_no)
{
    $line_no = trim($line_no);
    if( $line_no == "LC2"
     || $line_no == "LO1"
     || $line_no == "3LC2"
     || $line_no == "3DL1"
     || $line_no == "3LO1" )
    {
        return true;    // �ϣţ�
    } else {
        return false;   // �ϣţ� �ʳ�
    }
}

// ���ѤǤ�����
function IsHoyou($line_no)
{
    if( $line_no == "3LH2"
     || $line_no == "3LH1"
     || $line_no == "3LP3" )
    {
        return true;    // ����
    } else {
        return false;   // ���� �ʳ�
    }
}

// �饤��No.����
function getLineNo($plan, $parts)
{
    $query = sprintf("
                SELECT
                        CASE
                            WHEN trim(line_no)='' THEN '----'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
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

// ̤�����ǡ�������
function getMikenData(&$res)
{
    $file_orign     = '../..' . SYS . 'backup/W#TIUKSL.TXT';
//    $res            = array();
    $total_price    = 0;    // ���
    $total_ken      = 0;    // ���
    $total_count    = 0;    // ����
    $rec            = 0;    // �쥳���ɭ�
    if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
        $fp = fopen($file_orign, 'r');
        while (!(feof($fp))) {
            $data = fgetcsv($fp, 130, '_');     // �¥쥳���ɤ�103�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���'_'�����
            if (feof($fp)) {
                break;
            }
            $num  = count($data);       // �ե�����ɿ��μ���
            if ($num != 14) {   // AS¦�κ���쥳���ɤ� php-4.3.5��0�֤� php-4.3.6��1���֤����ͤˤʤä���fgetcsv�λ����ѹ��ˤ��
               continue;
            }
            for ($f=0; $f<$num; $f++) {
                $res[$rec][$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
                $res[$rec][$f] = addslashes($res[$rec][$f]);    // "'"�����ǡ����ˤ������\�ǥ��������פ���
                // $data_KV[$f] = mb_convert_kana($data[$f]);   // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
            }
            if($res[$rec][5] !='C8385407') {
                $query = sprintf("select midsc from miitem where mipn='%s' limit 1", $res[$rec][3]);
                getUniResult($query, $res[$rec][4]);       // ����̾�μ��� (���ʥ����ɤ��񤭤���)
                /******** ����������Ͽ�Ѥߤι����ɲ� *********/
                $sql = "
                    SELECT plan_no FROM material_cost_header WHERE plan_no='{$res[$rec][5]}'
                ";
                if (getUniResult($sql, $temp) <= 0) {
                    $res[$rec][13] = '��Ͽ';
                    $sql_c = "
                        SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE assy_no = '{$res[$rec][3]}' ORDER BY assy_no DESC, regdate DESC LIMIT 1
                    ";
                    if (($rows_c = getResultWithField3($sql_c, $field_c, $res_c)) <= 0) {
                    } else {
                    }
                } else {
                    $res[$rec][13] = '��Ͽ��';
                    $sql_c = "
                        SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no='{$res[$rec][5]}' AND assy_no = '{$res[$rec][3]}' ORDER BY assy_no DESC, regdate DESC LIMIT 1
                    ";
                    if (($rows_c = getResultWithField3($sql_c, $field_c, $res_c)) <= 0) {
                    } else {
                    }
                }
                /******** ����ɸ��ι����ɲ� *********/
                $sql2 = "
                    SELECT substr(note15, 1, 2) FROM assembly_schedule WHERE plan_no='{$res[$rec][5]}'
                ";
                $sc = '';
                getUniResult($sql2, $sc);
                if ($sc == 'SC') {
                    $res[$rec][15] = '����';
                } else {
                    $res[$rec][15] = 'ɸ��';
                }
                /******** ����ñ�������ǡ����ˤʤ����ξ�񤭽��� *********/
                if ($res[$rec][12] == 0) {                                  // ���ǡ����˻��ڤ����뤫�ɤ���
                    $res[$rec][14] = '1';
                    $sql = "
                        SELECT price FROM sales_price_nk WHERE parts_no='{$res[$rec][3]}'
                    ";
                    if (getUniResult($sql, $sales_price) <= 0) {            // �ǿ����ڤ���Ͽ����Ƥ��뤫
                        $sql = "
                            SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no='{$res[$rec][5]}' AND assy_no = '{$res[$rec][3]}' ORDER BY assy_no DESC, regdate DESC LIMIT 1
                        ";
                        if (getUniResult($sql, $sales_price) <= 0) {        // �ײ�����������Ͽ����Ƥ��뤫
                            $sql_c = "
                                SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE assy_no = '{$res[$rec][3]}' ORDER BY assy_no DESC, regdate DESC LIMIT 1
                            ";
                            if (getUniResult($sql, $sales_price) <= 0) {    // ���ʤ����������Ͽ����Ƥ��뤫
                                $res[$rec][12] = 0;
                            } else {
                                if ($res[$rec][15] == '����') {
                                    $res[$rec][12] = round(($sales_price * 1.27), 2);   // ����ΤȤ�����Ψ��
                                } else {
                                    $res[$rec][12] = round(($sales_price * 1.13), 2);
                                }
                            }
                        } else {
                            if ($res[$rec][15] == '����') {
                                $res[$rec][12] = round(($sales_price * 1.27), 2);       // ����ΤȤ�����Ψ��
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
                /******** ���� �׻� *********/
                $res[$rec][16] = round(($res[$rec][11] * $res[$rec][12]), 0);

                $rec++;
            }
        }
        // 0=>'������', 1=>'������', 3=>'�����ֹ�', 4=>'����̾', 5=>'�ײ��ֹ�', 11=>'������', 12=>'����ñ��'
    }
    return $rec;
}

// ̤�����ǡ�������
function getMikenSlectData(&$res, $div, $target_ym)
{
    $rows_miken_all = getMikenData($res_miken_all);

    if( $rows_miken_all <= 0 ) return 0;

    if( substr($res_miken_all[0][1], 0, 6) != $target_ym ) return 0;

    if( $div == "D") {  // ���ץ�ɸ��
//$_SESSION['s_sysmsg'] .= "���ץ�ɸ��:";
        $jigyou = 'C';
        $type = 'ɸ��';
    } else if( $div == "S") {  // ���ץ�����
//$_SESSION['s_sysmsg'] .= "���ץ�����:";
        $jigyou = 'C';
        $type = '����';
    } else if( $div == "L" ) {  // ��˥�
//$_SESSION['s_sysmsg'] .= "��˥�:";
        $jigyou = 'L';
        $type = 'ɸ��';
    }

    $rows_miken = $rec = 0;
    for( $r=0; $r<$rows_miken_all; $r++ ) {
        if( $res_miken_all[$r][0] == $jigyou && $res_miken_all[$r][15] == $type ) {
            $res[$rec][0] = $res_miken_all[$r][0]; // ������
            $res[$rec][1] = $res_miken_all[$r][1]; // ������
            $res[$rec][2] = $res_miken_all[$r][3]; // �����ֹ�
            $res[$rec][3] = $res_miken_all[$r][4]; // ����̾
            $res[$rec][3] = mb_convert_kana($res[$rec][3], 'ka', 'EUC-JP');   // ���ѥ��ʤ�Ⱦ�ѥ��ʤإƥ���Ū�˥���С���
            $res[$rec][4] = $res_miken_all[$r][5]; // �ײ��ֹ�
            $res[$rec][5] = $res_miken_all[$r][11]; // ������
            $res[$rec][6] = $res_miken_all[$r][12]; // ����ñ��
            $res[$rec][7] = $res_miken_all[$r][16]; // ���
            $rec++;
        }
    }
    return $rec;
}

// ������ͽ�����
function getFirstPlan(&$res, &$field, $search)
{
    $query = sprintf("
                SELECT
                        m.kanryou           AS ��λͽ����,  -- 0
                        m.plan_no           AS �ײ��ֹ�,    -- 1
                        m.parts_no          AS �����ֹ�,    -- 2
                        m.midsc             AS ����̾,      -- 3
                        m.plan              AS ����,        -- 4
                        m.partition_price   AS ���ڶ��,    -- 5
                        m.price             AS ���,        -- 6
                        m.line_no           AS �饤��No,    -- 7
                        m.state             AS ��ʬ,        -- 8
                        m.complete_plan     AS ������,      -- 9
                        m.complete_price    AS �������,    -- 10
                        m.remarks           AS ����         -- 11
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

// ���ͽ�����
function getSalsePlan(&$res, $search)
{
    $query = sprintf("select
                            a.kanryou                     AS ��λͽ����,  -- 0
                            CASE
                                WHEN trim(a.plan_no)='' THEN '---'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                ELSE a.plan_no
                            END                           AS �ײ��ֹ�,    -- 1
                            CASE
                                WHEN trim(a.parts_no) = '' THEN '---'
                                ELSE a.parts_no
                            END                           AS �����ֹ�,    -- 2
                            CASE
                                WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                WHEN m.midsc IS NULL THEN '&nbsp;'
                                ELSE substr(m.midsc,1,38)
                            END                           AS ����̾,      -- 3
                            a.plan -a.cut_plan - a.kansei AS ����,        -- 4
                            (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                                                          AS ����ñ��,    -- 5
                            Uround((a.plan -a.cut_plan - kansei) * (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1), 0)
                                                          AS ���,        -- 6
                            sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                          AS �������,    -- 7
                            CASE
                                WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                            END                           AS Ψ��,        -- 8
                            (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                          AS �������2,   -- 9
                            (select Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                          AS Ψ��,        --10
                            (select plan_no from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                          AS �ײ��ֹ�2,   --11
                            (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                          AS ���ʺ�����,  --12
                            (SELECT reg_no FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                          AS ñ����Ͽ�ֹ�, --13
                            CASE
                                WHEN trim(a.plan_no)='' THEN '&nbsp;'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                ELSE substr(a.plan_no,4,5)
                            END                           AS �ײ��ֹ�3    -- 14
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
                      order by a.kanryou, �ײ��ֹ�3
                      ", $search);   // ���� $search �Ǹ���

    $res   = array();
    $field = array();
    if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
//        $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>���ͽ��Υǡ���������ޤ���<br>%s��%s</font><BR>", format_date($d_start), format_date($d_end));
//        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
//        exit();
    } else {
        $num = count($field);       // �ե�����ɿ�����
        for ($r=0; $r<$rows; $r++) {
            $res[$r][3] = mb_convert_kana($res[$r][3], 'ka', 'EUC-JP');   // ���ѥ��ʤ�Ⱦ�ѥ��ʤإƥ���Ū�˥���С���
        }
    }
    return $rows;
}

// ������ټ���
function getSalesDetails(&$res, $div, $search)
{
    if ($div != 'S') {      // ������ �ʳ��ʤ�
        $query = sprintf("select
                                u.�׾���        as �׾���,                  -- 0
                                CASE
                                    WHEN u.datatype=1 THEN '����'
                                    ELSE u.datatype
                                END             as ��ʬ,                    -- 1
                                CASE
                                    WHEN trim(u.�ײ��ֹ�)='' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                    ELSE u.�ײ��ֹ�
                                END                     as �ײ��ֹ�,        -- 2
                                CASE
                                    WHEN trim(u.assyno) = '' THEN '---'
                                    ELSE u.assyno
                                END                     as �����ֹ�,        -- 3
                                CASE
                                    WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                    WHEN m.midsc IS NULL THEN '&nbsp;'
                                    ELSE substr(m.midsc,1,38)
                                END             as ����̾,                  -- 4
                                CASE
                                    WHEN trim(u.���˾��)='' THEN '--'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                    ELSE u.���˾��
                                END                     as ����,            -- 5
                                u.����          as ����,                    -- 6
                                u.ñ��          as ����ñ��,                -- 7
                                Uround(u.���� * u.ñ��, 0) as ���,         -- 8
                                CASE
                                    WHEN trim(a.line_no)='' THEN '----'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                    ELSE a.line_no
                                END                     as �饤��,
                                sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                        as �������,        -- 9
                                CASE
                                    WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                    ELSE Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                                END                     as Ψ��,            --10
                                (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=u.assyno AND regdate<=�׾��� order by assy_no DESC, regdate DESC limit 1)
                                                        AS �������2,       --11
                                (select Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=u.assyno AND regdate<=�׾��� order by assy_no DESC, regdate DESC limit 1)
                                                        AS Ψ��,            --12
                                (select plan_no from material_cost_header where assy_no=u.assyno AND regdate<=�׾��� order by assy_no DESC, regdate DESC limit 1)
                                                        AS �ײ��ֹ�2,       --13
                                (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=u.assyno AND as_regdate<�׾��� AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                        AS ���ʺ�����,      --14
                                (SELECT reg_no FROM parts_cost_history WHERE parts_no=u.assyno AND as_regdate<�׾��� AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                        AS ñ����Ͽ�ֹ�     --15
                          from
                                hiuuri as u
                          left outer join
                                assembly_schedule as a
                          on u.�ײ��ֹ�=a.plan_no
                          left outer join
                                miitem as m
                          on u.assyno=m.mipn
                          left outer join
                                material_cost_header as mate
                          on u.�ײ��ֹ�=mate.plan_no
                          LEFT OUTER JOIN
                                sales_parts_material_history AS pmate
                          ON (u.assyno=pmate.parts_no AND u.�׾���=pmate.sales_date)
                          left outer join
                                product_support_master AS groupm
                          on u.assyno=groupm.assy_no
                          %s
                          order by �׾���, assyno
                          ", $search);   // ���� $search �Ǹ���
    } else {    ////////////////////////////////////////// ������ξ��
        $query = sprintf("select
                                u.�׾���        as �׾���,                  -- 0
                                CASE
                                    WHEN u.datatype=1 THEN '����'
                                    ELSE u.datatype
                                END             as ��ʬ,                    -- 1
                                CASE
                                    WHEN trim(u.�ײ��ֹ�)='' THEN '---'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                    ELSE u.�ײ��ֹ�
                                END                     as �ײ��ֹ�,        -- 2
                                u.assyno        as �����ֹ�,                -- 3
                                CASE
                                    WHEN m.midsc IS NULL THEN '&nbsp;'
                                    ELSE substr(m.midsc,1,18)
                                END                     as ����̾,          -- 4
                                CASE
                                    WHEN trim(u.���˾��)='' THEN '--'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                    ELSE u.���˾��
                                END                     as ����,            -- 5
                                u.����          as ����,                    -- 6
                                u.ñ��          as ����ñ��,                -- 7
                                Uround(u.���� * u.ñ��, 0) as ���,         -- 8
                                CASE
                                    WHEN trim(a.line_no)='' THEN '----'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                    ELSE a.line_no
                                END                     as �饤��,
                                trim(a.note15)  as �����ֹ�,                -- 9
                                aden.order_price  as ����ñ��,              --10
                                CASE
                                    WHEN aden.order_price <= 0 THEN '0'
                                    ELSE Uround(u.ñ�� / aden.order_price, 3) * 100
                                END                     as Ψ��,            --11
                                sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                        as �������,        --12
                                CASE
                                    WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                    ELSE Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                                END                     as Ψ��             --13
                          from
                                (hiuuri as u left outer join miitem as m on u.assyno=m.mipn)
                          left outer join
                                assembly_schedule as a
                          on u.�ײ��ֹ�=a.plan_no
                          left outer join
                                aden_master as aden
                          -- on (a.parts_no=aden.parts_no and a.plan_no=aden.plan_no)
                          on (a.plan_no=aden.plan_no)
                          left outer join
                                material_cost_header as mate
                          on u.�ײ��ֹ�=mate.plan_no
                          left outer join
                                product_support_master AS groupm
                          on u.assyno=groupm.assy_no
                          %s
                          order by �׾���, assyno
                          ", $search);   // ���� $search �Ǹ���
    }

    $res   = array();
    $field = array();
    if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
//        $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>������٤Υǡ���������ޤ���<br>%s��%s</font>", format_date($d_start), format_date($d_end) );
//        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
//        exit();
    } else {
        for ($r=0; $r<$rows; $r++) {
            $res[$r][4] = mb_convert_kana($res[$r][4], 'ka', 'EUC-JP');   // ���ѥ��ʤ�Ⱦ�ѥ��ʤإƥ���Ū�˥���С���
        }
    }
    return $rows;
}

// ���ͽ����������
function DiffFirstDetails(&$res_f, &$rows_f, &$res_d, $rows_d)
{
    // ��λ�򣱥쥳���ɤ��ķ��η��ͽ������
    for( $d=0; $d<$rows_d; $d++ ) {
        $flg_add = true;
        for( $f=0; $f<$rows_f; $f++ ) {
            if( $res_d[$d][2] == $res_f[$f][1] && $res_d[$d][3] == $res_f[$f][2] ) { // Ʊ��ײ衦�����ֹ�
                $res_f[$f][9] = $res_f[$f][9] + $res_d[$d][6];
                $res_f[$f][10] = round($res_f[$f][9]*$res_d[$d][7]);
                if( $res_f[$f][8] != "" ) {
//                    $res_f[$f][10] = round($res_f[$f][9]*$res_d[$d][7]);
                } else {
                    $res_f[$f][8] = "��λ";
//                    $res_f[$f][10] = round($res_f[$f][9]*$res_d[$d][7]);
                }
                $flg_add = false;
            }
        }
        if( $flg_add ) {
            $rows_f++;
            $res_f[$f][0] = $res_d[$d][0];                          // ����ͽ�����ʴ�������
            $res_f[$f][1] = $res_d[$d][2];                          // �ײ��ֹ�
            $res_f[$f][2] = $res_d[$d][3];                          // �����ֹ�
            $res_f[$f][3] = $res_d[$d][4];                          // ����̾
            $res_f[$f][4] = "��";                                   // ����
            $res_f[$f][5] = $res_d[$d][7];                          // ����ñ��
            $res_f[$f][6] = "��";                                   // ���
            if( !empty($res_d[$d][9]) ) {
                $res_f[$f][7] = $res_d[$d][9];                          // �饤��No.
            } else {
                $res_f[$f][7] = "��";                          // �饤��No.
            }

            $res_f[$f][8] = "�ɲ�";                                 // ��ʬ
            $res_f[$f][9] = $res_d[$d][6];                          // ������
            $res_f[$f][10] = round($res_f[$f][9]*$res_d[$d][7]);    // �������
        }
    }
}

// ���ͽ���̤�������
function DiffFirstMiken(&$res_f, &$rows_f, &$res_m, $rows_m)
{
    for( $m=0; $m<$rows_m; $m++ ) {
        $flg_add = true;
        for( $f=0; $f<$rows_f; $f++ ) {
            if( $res_m[$m][4] == $res_f[$f][1] && $res_m[$m][2] == $res_f[$f][2] ) { // Ʊ��ײ衦�����ֹ�
                $cnt = sprintf( "%d", $res_m[$m][5] );
                $res_f[$f][9] += $cnt;
                $res_f[$f][10] = round($res_f[$f][9]*$res_f[$f][5]);

                if( $res_f[$f][8] != "" ) {
//                    $res_f[$f][4] = $res_f[$f][9];
//                    $res_f[$f][6] = round($res_f[$f][4]*$res_f[$f][5]);
//                    $res_f[$f][10] = round($res_f[$f][9]*$res_f[$f][5]);
                } else {
                    $res_f[$f][8] = "̤����";
//                    $res_f[$f][10] = round($res_f[$f][9]*$res_f[$f][5]);
                }
                $flg_add = false;
            }
        }
        if( $flg_add ) {
            $rows_f++;
            $res_f[$f][0]   = $res_m[$m][1];                            // ����ͽ�����ʴ�������
            $res_f[$f][1]   = $res_m[$m][4];                            // �ײ��ֹ�
            $res_f[$f][2]   = $res_m[$m][2];                            // �����ֹ�
            $res_f[$f][3]   = $res_m[$m][3];                            // ����̾
            $res_f[$f][4]   = "��";                                     // ����
            $res_f[$f][5]   = $res_m[$m][6];                            // ����ñ��
            $res_f[$f][6]   = "��";                                     // ���
            $res_f[$f][7]   = getLineNo($res_f[$f][1], $res_f[$f][2]);  // �饤��No.
            $res_f[$f][8]   = "�ɲ�";                                   // ��ʬ
            $res_f[$f][9]   = sprintf( "%d", $res_m[$m][5] );           // ������
            $res_f[$f][10]  = round($res_f[$f][9]*$res_f[$f][5]);       // �������
        }
    }
}

// ���ͽ���ͽ�����
function DiffFirstPlan(&$res_f, $rows_f, &$res_p, $rows_p)
{
    for( $p=0; $p<$rows_p; $p++ ) {
        for( $f=0; $f<$rows_f; $f++ ) {
            if( $res_p[$p][1] == $res_f[$f][1] && $res_p[$p][2] == $res_f[$f][2] ) { // Ʊ��ײ衦�����ֹ�
                $res_f[$f][8] = "ͽ�ꤢ��";
            }
        }
    }
}

// ������
function getSales($d_start, $d_end, $div)
{
    ///////////// ��׶�ۡ�����������
    if ( ($div != 'S') && ($div != 'D') ) {      // �������ɸ�� �ʳ��ʤ�
        $query = "select
                        count(DISTINCT �ײ��ֹ�) as t_ken,
                        sum(����) as t_kazu,
                        sum(Uround(����*ñ��,0)) as t_kingaku
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
                        count(DISTINCT �ײ��ֹ�) as t_ken,
                        sum(����) as t_kazu,
                        sum(Uround(����*ñ��,0)) as t_kingaku
                  from
                        hiuuri
                  left outer join
                        assembly_schedule as a
                  on �ײ��ֹ�=plan_no
                  left outer join
                        product_support_master AS groupm
                  on assyno=groupm.assy_no
                  left outer join
                        miitem as m
                  on assyno=m.mipn";
    }
    //////////// SQL where ��� ���Ѥ���
    $search = "where �׾���>=$d_start and �׾���<=$d_end";
    if ($div == 'S') {    // ������ʤ�
        $search .= " and ������='C' and note15 like 'SC%%'";
        $search .= " and (assyno not like 'NKB%%') and (assyno not like 'SS%%')";
        $search .= " and CASE WHEN �׾���>=20111101 and �׾���<20130501 THEN groupm.support_group_code IS NULL ELSE ������='C' END";
    } elseif ($div == 'D') {    // ��ɸ��ʤ�
        $search .= " and ������='C' and (note15 NOT like 'SC%%' OR note15 IS NULL)";    // ��������ɸ��ؤ���
        $search .= " and (assyno not like 'NKB%%') and (assyno not like 'SS%%')";
        $search .= " and (CASE WHEN �׾���>=20111101 and �׾���<20130501 THEN groupm.support_group_code IS NULL ELSE ������='C' END)";
    } elseif ($div == "L") {
        $search .= " and ������='$div'";
        $search .= " and (assyno not like 'NKB%%') and (assyno not like 'SS%%')";
    }
    $search .= " and datatype='1'";

    $query = sprintf("$query %s", $search);     // SQL query ʸ�δ���
    $res_sum = array();
    if (getResult($query, $res_sum) <= 0) {
//        $_SESSION['s_sysmsg'] = '��׶�ۤμ����˼��Ԥ��ޤ�����';
//        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
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

// ����������
function getPreviousSeasonSales($target_ym, $div)
{
    $d_start = substr($target_ym,0,4)-1 . substr($target_ym,5,2) . "01";
    $d_end   = substr($target_ym,0,4)-1 . substr($target_ym,5,2) . "99";

    return getSales($d_start, $d_end, $div);
}
