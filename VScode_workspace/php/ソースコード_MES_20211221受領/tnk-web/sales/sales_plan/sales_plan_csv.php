<?php
//////////////////////////////////////////////////////////////////////////////
// ���ͽ��ξȲ� CSV����                                                   //
// Copyright (C) 2011-2018 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2011/11/21 Created   sales_plan_csv.php                                  //
// 2012/03/28 �Σˣ����ʽи�ʬ(NKTB)�ξȲ���ɲ�                            //
// 2013/01/28 �Х�������Υݥ�פ��ѹ� ɽ���Τߥǡ����ϥХ����Τޤ�     //
// 2016/03/03 NKTB��SQL�ߥ�����������̾�ǤϤʤ�����̾�������               //
// 2018/08/21 ����A������ñ��52����б�                                ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php');// TNK ������ MVC Controller Class

// �ե�����̾��SQL�Υ���������������
$outputFile = $_GET['csvname'] . '���ͽ��.csv';
$csv_search = $_GET['csvsearch'];
$div        = $_GET['div'];
$shikiri    = $_GET['shikiri'];
// SQL�Υ��������ǰ���ѹ�������ʬ�򸵤��᤹
$search     = str_replace('/','\'',$csv_search);
// ����������ʸ�������ɤ�EUC���ѹ������ǰ�Τ����
$search     = mb_convert_encoding($search, 'EUC-JP', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�

// �ե�����̾�ǰ���ѹ�������ʬ�򸵤��᤹
$outputFile     = str_replace('ALL','����',$outputFile);
$outputFile     = str_replace('C-all','���ץ�����',$outputFile);
$outputFile     = str_replace('C-hyou','���ץ�ɸ��',$outputFile);
$outputFile     = str_replace('C-toku','���ץ�����',$outputFile);
$outputFile     = str_replace('L-all','��˥�����',$outputFile);
$outputFile     = str_replace('L-hyou','��˥��Τ�',$outputFile);
$outputFile     = str_replace('L-bimor','���Υݥ�פΤ�',$outputFile);
$outputFile     = str_replace('C-shuri','���ץ�',$outputFile);
$outputFile     = str_replace('L-shuri','��˥��',$outputFile);
$outputFile     = str_replace('NKB','���ʴ���',$outputFile);
$outputFile     = str_replace('TOOL','�ġ���',$outputFile);
$outputFile     = str_replace('NONE','�ʤ�',$outputFile);
$outputFile     = str_replace('SHISAKU','���',$outputFile);
$outputFile     = str_replace('NKCT','NKCT',$outputFile);
$outputFile     = str_replace('NKT','NKT',$outputFile);
$outputFile     = str_replace('NKTB','NKT���ʽи�ʬ',$outputFile);
$outputFile     = str_replace('NONE','�ʤ�',$outputFile);

// �¹ԼԤΥѥ������CSV����¸����١��ե�����̾��ʸ�������ɤ�SJIS���Ѵ�
$outputFile = mb_convert_encoding($outputFile, 'SJIS', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�

//////////// CSV�����ѤΥǡ�������
if ($div == "NKTB") {  // NKT���ʽи�ʬ�ξ�����SQL�ǽ���
    $query_csv = sprintf("select
                        a.chaku                     AS �и�ͽ��,  -- 0
                        CASE
                            WHEN trim(a.plan_no)='' THEN '---'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                            ELSE a.plan_no
                        END                           AS �ײ��ֹ�,    -- 1
                        CASE
                            WHEN trim(allo.parts_no) = '' THEN '---'
                            ELSE allo.parts_no
                        END                           AS �����ֹ�,    -- 2
                        CASE
                            WHEN trim(substr(m.midsc,1,38)) = '' THEN ''
                            WHEN m.midsc IS NULL THEN ''
                            ELSE substr(m.midsc,1,38)
                        END                           AS ����̾,      -- 3
                        CASE
                            WHEN trim(substr(m2.midsc,1,38)) = '' THEN ''
                            WHEN m2.midsc IS NULL THEN ''
                            ELSE substr(m2.midsc,1,38)
                        END                           AS ����̾,      -- 4
                        (allo.allo_qt - allo.sum_qt) AS ����,        -- 5
                        (SELECT price FROM sales_price_nk WHERE parts_no = allo.parts_no LIMIT 1)
                                                      AS ����ñ��,    -- 6
                        Uround((allo.allo_qt - allo.sum_qt) * (SELECT price FROM sales_price_nk WHERE parts_no = allo.parts_no LIMIT 1), 0)
                                                      AS ���        -- 7
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
                  ", $search);   // ���� $search �Ǹ���
} elseif ($div == "C-toku") {    // ����ξ��
    if ($shikiri == "A") {    // A������ñ��52��ξ��
        $query_csv = sprintf("select
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
                                (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1)
                                                              AS ����ñ��,    -- 5
                                Uround((a.plan -a.cut_plan - kansei) * (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1), 0)
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
    } elseif ($shikiri == "AS") {    // A������ñ��52���ǿ����ڤξ��
        $query_csv = sprintf("select
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
                                CASE
                                    WHEN (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1) = 0 THEN (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                                    WHEN (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1) IS NULL THEN (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                                    ELSE (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1)
                                END                           AS ����ñ��,    -- 5
                                CASE
                                    WHEN (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1) = 0 THEN Uround((a.plan -a.cut_plan - kansei) * (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1), 0)
                                    WHEN (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1) IS NULL THEN Uround((a.plan -a.cut_plan - kansei) * (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1), 0)
                                    ELSE Uround((a.plan -a.cut_plan - kansei) * (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1), 0)
                                END
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
    } else {    // �ǿ����ڤξ��
        $query_csv = sprintf("select
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
                          order by a.kanryou, �ײ��ֹ�
                          ", $search);   // ���� $search �Ǹ���
    }
} else {
    $query_csv = sprintf("select
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
}
$res_csv   = array();
$field_csv = array();
if (($rows_csv = getResultWithField3($query_csv, $field_csv, $res_csv)) <= 0) {
    //$_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>������٤Υǡ���������ޤ���<br>%s��%s</font>", format_date($d_start), format_date($d_end) );
    //header('Location: ' . H_WEB_HOST . $menu->out_RetUrl() . '?sum_exec=on');    // ľ���θƽи������
    exit();
} else {
    $num_csv = count($field_csv);       // �ե�����ɿ�����
    for ($r=0; $r<$rows_csv; $r++) {
        //$res_csv[$r][4] = mb_convert_kana($res_csv[$r][4], 'ka', 'EUC-JP');   // ���ѥ��ʤ�Ⱦ�ѥ��ʤإƥ���Ū�˥���С���
        $res_csv[$r][3] = str_replace(',',' ',$res_csv[$r][3]);                   // ����̾��,�����äƤ����CSV�Ƿ夬�����Τ�Ⱦ�ѥ��ڡ�����
        $res_csv[$r][3] = mb_convert_encoding($res_csv[$r][3], 'SJIS', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�
        $res_csv[$r][4] = str_replace(',',' ',$res_csv[$r][4]);                   // ����̾��,�����äƤ����CSV�Ƿ夬�����Τ�Ⱦ�ѥ��ڡ�����
        $res_csv[$r][4] = mb_convert_encoding($res_csv[$r][4], 'SJIS', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�
    }
    //$_SESSION['SALES_TEST'] = sprintf("order by �׾��� offset %d limit %d", $offset, PAGE);
    $i = 1;                             // CSV�񤭽Ф��ѥ�����ȡʥե������̾��0������Τǣ������
    $csv_data = array();                // CSV�񤭽Ф�������
    for ($s=0; $s<$num_csv; $s++) {     // �ե������̾��CSV�񤭽Ф�������˽���
        $field_csv[$s]   = mb_convert_encoding($field_csv[$s], 'SJIS', 'auto');
        $csv_data[0][$s] = $field_csv[$s];
    }
    for ($r=0; $r<$rows_csv; $r++) {    // �ǡ�����CSV�񤭽Ф�������˽���
        for ($s=0; $s<$num_csv; $s++) {
            $csv_data[$i][$s]  = $res_csv[$r][$s];
        }
        $i++;
    }
}

// �������餬CSV�ե�����κ����ʰ���ե�����򥵡��С��˺�����
//$outputFile = 'csv/' . $d_start . '-' . $d_end . '.csv';
//$outputFile = 'csv/' . $d_start . '-' . $d_end . '-' . $act_name . '.csv';
//$outputFile = "test.csv";
touch($outputFile);
$fp = fopen($outputFile, "w");

foreach($csv_data as $line){
    fputcsv($fp,$line);         // ������CSV�ե�����˽񤭽Ф�
}
fclose($fp);
//$outputFile = $d_start . '-' . $d_end . '.csv';
//$outputFile = $d_start . '-' . $d_end . '-' . $act_name . '.csv';

// �������餬CSV�ե�����Υ�������ɡʥ����С������饤����ȡ�
touch($outputFile);
header("Content-Type: application/csv");
header("Content-Disposition: attachment; filename=".$outputFile);
header("Content-Length:".filesize($outputFile));
readfile($outputFile);
unlink("{$outputFile}");         // ��������ɸ�ե��������
?>