<?php
//////////////////////////////////////////////////////////////////////////////
// ���ʥ��롼���� ���Ȳ�����������ӡ�����ɽ CSV����                    //
// Copyright (C) 2011 - 2012 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2011/06/15 Created   material_compare_sale_view_csv.php                  //
// 2011/06/20 ������ư                                                      //
// 2011/07/06 ���ʥ��롼�פ˥��ץ�ɸ��ȥ�˥�ɸ����ɲ�                    //
// 2012/03/29 ���ʥ��롼�פ�̾�Τ��ѹ�                                      //
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
$csv_search = $_GET['csvsearch'];       // ���ѥ�����
$first_ym   = $_GET['csvfirst_ym'];     // ���ǯ��
$d_start    = $_GET['csvd_start'];      // ����ǯ����
$d_end      = $_GET['csvd_end'];        // ��λǯ����
$div        = $_GET['csvdiv'];          // ���ʥ��롼��

// ���ʥ��롼��̾������
if ($div == "A") $div_name  = "�����롼��";
if ($div == "C") $div_name  = "ɸ����";
if ($div == "CC") $div_name = "���ץ�ɸ����";
if ($div == "CL") $div_name = "��˥�ɸ����";
if ($div == "S") $div_name  = "������";

$section    = $_GET['csvsection'];      // ʬ��̾�μ���
if ($section != " ") {
    $query_s = "
            SELECT  groupm.group_no                AS ���롼���ֹ�     -- 0
                ,   groupm.group_name              AS ���롼��̾       -- 1
            FROM
                product_serchGroup AS groupm
            WHERE
                groupm.group_no = {$section}
            ORDER BY
                group_name
        ";

    $res_s = array();
    if (($rows_s = getResultWithField2($query_s, $field_s, $res_s)) <= 0) {
        $section_name = '��Ͽ�ʤ�';
    } else {
        //$res_s[0][1] = mb_convert_kana($res_s[0][1], 'Ka', 'EUC-JP'); // Ⱦ�ѥ��ʤ����Ѥ� ���ѱѿ���Ⱦ�ѱѿ���
        $section_name = $res_s[0][1];
    }
}

// ���ϥե�����̾������(ʬ��̾-����ǯ����-��λǯ����-���ǯ��-���ʥ��롼�׷�����
$outputFile = $section_name . '-' . $d_start . '-' . $d_end . '-' . $first_ym . '-' . $div_name . '.csv';

// SQL�Υ��������ǰ���ѹ�������ʬ�򸵤��᤹
$search     = str_replace('keidate','�׾���',$csv_search);
$search     = str_replace('jigyou','������',$search);
$search     = str_replace('denban','��ɼ�ֹ�',$search);
$search     = str_replace('/','\'',$search);

// ����������ʸ�������ɤ�EUC���ѹ������ǰ�Τ����
$search     = mb_convert_encoding($search, 'EUC-JP', 'auto');       // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�

// �¹ԼԤΥѥ������CSV����¸����١��ե�����̾��ʸ�������ɤ�SJIS���Ѵ�
$outputFile = mb_convert_encoding($outputFile, 'SJIS', 'auto');     // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�

// ��������ǯ�����η׻�
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
//////////// CSV�����ѤΥǡ�������
$query_csv = sprintf("select
                            u.�׾���        as �׾���,                  -- 0
                            CASE
                                WHEN trim(u.�ײ��ֹ�)='' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                ELSE u.�ײ��ֹ�
                            END                     as �ײ��ֹ�,        -- 1
                            CASE
                                WHEN trim(u.assyno) = '' THEN '---'
                                ELSE u.assyno
                            END                     as �����ֹ�,        -- 2
                            CASE
                                WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                WHEN m.midsc IS NULL THEN '&nbsp;'
                                ELSE substr(m.midsc,1,38)
                            END             as ����̾,                  -- 3
                            u.����          as ����,                    -- 4
                            u.ñ��          as ����ñ��,                -- 5
                            Uround(u.���� * u.ñ��, 0) as ���,         -- 6
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                            END                     as �������,        -- 7
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN '-----'
                                ELSE to_char(mate.regdate, 'YYYY/MM/DD')
                            END                     AS ��Ͽ��,          -- 8
                            CASE
                                WHEN (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) 
                                    IS NULL THEN    CASE 
                                                        WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                                                        ELSE (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                                    END
                                ELSE (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                            END                     AS ����������     -- 9
                            ,
                            CASE
                                WHEN (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN '----------'
                                ELSE (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                            END                     AS �����Ͽ��       --10
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
                            END                      AS ñ������        --11
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
                            END                      AS ����Ψ          --12
                            ,
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE
                                CASE
                                    WHEN (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) 
                                    IS NULL THEN    CASE
                                                        WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                                                        ELSE ((sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)) * u.����
                                                    END
                                    ELSE ((sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)) * u.����
                                END
                            END                      AS �������        --13
                            ,
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE 
                                CASE
                                    WHEN u.ñ�� IS NULL THEN 0
                                    ELSE Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 2)
                                END
                            END                      AS ��Ψ            --14
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
                        mshmas as p
                      on u.assyno=p.mipn
                      left outer join
                        -- mshgnm as gnm
                        msshg3 as gnm
                      -- on p.mhjcd=gnm.mhgcd
                      on p.mhshc=gnm.mhgcd
                      %s
                      order by �׾���, assyno
                      ", $search);   // ���� $search �Ǹ���

$res_csv   = array();
$field_csv = array();
if (($rows_csv = getResultWithField3($query_csv, $field_csv, $res_csv)) <= 0) {
    exit();
} else {
    $num_csv = count($field_csv);       // �ե�����ɿ�����
    for ($r=0; $r<$rows_csv; $r++) {
        //$res_csv[$r][4] = mb_convert_kana($res_csv[$r][4], 'ka', 'EUC-JP');       // ���ѥ��ʤ�Ⱦ�ѥ��ʤإƥ���Ū�˥���С���
        $res_csv[$r][3] = str_replace(',',' ',$res_csv[$r][3]);                     // ����̾��,�����äƤ����CSV�Ƿ夬�����Τ�Ⱦ�ѥ��ڡ�����
        //$res_csv[$r][1] = mb_convert_encoding($res_csv[$r][1], 'SJIS', 'EUC');    // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�(EUC���ꤸ��ʤ���ľǼ��Ĵ����������)
        $res_csv[$r][3] = mb_convert_encoding($res_csv[$r][3], 'SJIS', 'auto');     // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�
    }
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
touch($outputFile);
$fp = fopen($outputFile, "w");

foreach($csv_data as $line){
    fputcsv($fp,$line);         // ������CSV�ե�����˽񤭽Ф�
}
fclose($fp);

// �������餬CSV�ե�����Υ�������ɡʥ����С������饤����ȡ�
touch($outputFile);
header("Content-Type: application/csv");
header("Content-Disposition: attachment; filename=".$outputFile);
header("Content-Length:".filesize($outputFile));
readfile($outputFile);
unlink("{$outputFile}");         // ��������ɸ�ե��������
?>