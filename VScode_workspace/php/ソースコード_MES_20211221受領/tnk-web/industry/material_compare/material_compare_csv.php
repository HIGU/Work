<?php
//////////////////////////////////////////////////////////////////////////////
// ����������� CSV����                                                   //
// Copyright (C) 2011 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2011/05/26 Created   material_compare_csv.php                            //
// 2011/05/30 ����������Ӥ��̥�˥塼�ˤޤȤ᤿��require_once�Υ���ѹ�//
// 2011/05/31 ���롼�ץ������ѹ���ȼ��SQLʸ���ѹ�                           //
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
require_once ('../../ControllerHTTP_Class.php'); // TNK ������ MVC Controller Class

// CSV�ǡ��������Ѥν���ǡ������㤦
$div        = $_GET['csv_div'];
$first_ym   = $_GET['csv_first_ym'];
$second_ym  = $_GET['csv_second_ym'];
$assy_no    = $_GET['csv_assy_no'];
$order      = $_GET['csv_order'];

$cost1_ym = $first_ym;
$cost2_ym = $second_ym;

$nen        = substr($cost1_ym, 0, 4);
$tsuki      = substr($cost1_ym, 4, 2);
$cost1_name = $nen . "/" . $tsuki;

$nen        = substr($cost2_ym, 0, 4);
$tsuki      = substr($cost2_ym, 4, 2);
$cost2_name = $nen . "/" . $tsuki;

if (substr($cost1_ym,4,2)!=12) {
    $cost1_ymd = $cost1_ym + 1;
    $cost1_ymd = $cost1_ymd . '10';
} else {
    $cost1_ymd = $cost1_ym + 100;
    $cost1_ymd = $cost1_ymd - 11;
    $cost1_ymd = $cost1_ymd . '10';
}
if (substr($cost2_ym,4,2)!=12) {
    $cost2_ymd = $cost2_ym + 1;
    $cost2_ymd = $cost2_ymd . '10';
} else {
    $cost2_ymd = $cost2_ym + 100;
    $cost2_ymd = $cost2_ymd - 11;
    $cost2_ymd = $cost2_ymd . '10';
}

$str_ymd = $second_ym - 300;
$str_ymd = $str_ymd . '01';
$end_ymd = $second_ym . '31';

if ($div == "C") {
    if ($second_ym < 200710) {
        $rate = 25.60;  // ���ץ�ɸ�� 2007/10/01���ʲ������
    } elseif ($second_ym < 201104) {
        $rate = 57.00;  // ���ץ�ɸ�� 2007/10/01���ʲ���ʹ�
    } else {
        $rate = 45.00;  // ���ץ�ɸ�� 2011/04/01���ʲ���ʹ�
    }
} elseif ($div == "L") {
    if ($second_ym < 200710) {
        $rate = 37.00;  // ��˥� 2008/10/01���ʲ������
    } elseif ($second_ym < 201104) {
        $rate = 44.00;  // ��˥� 2008/10/01���ʲ���ʹ�
    } else {
        $rate = 53.00;  // ��˥� 2011/04/01���ʲ���ʹ�
    }
} else {
    $rate = 65.00;
}

///////// ��ΨȽ����
///////// ��Ψ������ǤϤʤ��ʤä���ɽ�����Υ��å����ѹ����롣
$power_rate = 1.13;      // 2011/04/01�ܹ�

if ($order == 'assy') {
    $order_name = 'ORDER BY �����ֹ� ASC';
} elseif ($order == 'diff') {
    $order_name = 'ORDER BY ���������� DESC, Ψ�� DESC, �Ȳ�� ASC, ��ʬ��̾ ASC, �����ֹ� ASC';
} elseif ($order == 'per') {
    $order_name = 'ORDER BY Ψ�� DESC, ���������� DESC, �Ȳ�� ASC, ��ʬ��̾ ASC, �����ֹ� ASC';
} elseif ($order == 'power') {
    $order_name = 'ORDER BY ��Ψ DESC, Ψ�� DESC, ���������� DESC, �Ȳ�� ASC, ��ʬ��̾ ASC, �����ֹ� ASC';
} elseif ($order == 'sorder') {
    $order_name = 'ORDER BY �Ȳ�� ASC, ��ʬ��̾ ASC, �����ֹ� ASC';
} else {
    $order_name = 'ORDER BY �����ֹ� ASC';
}

//////////// �ե�����̾������
//////////// �о�ǯ���ɽ���ǡ����Խ�
$end_y = substr($second_ym,0,4);
$end_m = substr($second_ym,4,2);
$str_y = substr($second_ym,0,4) - 3;
$str_m = substr($second_ym,4,2);

if ($div == "C") {
    $cap_div= "���ץ�ɸ����"; 
} elseif ($div == "L") {
    $cap_div= "��˥�"; 
}

// CSV�ե�����̾��������裱�����-�裲�����-��������
$outputFile = $cost1_ym . '-' . $cost2_ym . '-' . $cap_div . '������񺹳�.csv';

// �¹ԼԤΥѥ������CSV����¸����١��ե�����̾��ʸ�������ɤ�SJIS���Ѵ�
$outputFile = mb_convert_encoding($outputFile, 'SJIS', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�

//////////// �оݥǡ����μ���
$query_csv = "
    SELECT
        u.assyno                    AS �����ֹ� --- 0
        ,
        trim(substr(m.midsc,1,40))  AS ����̾   --- 1
        ,
        CASE
            WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
            ELSE (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
        END                         AS �裱������� --- 2
        ,
        CASE
            WHEN (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN '----------'
            ELSE (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
        END
                                    AS �裱��Ͽ�� --- 3
        ,
        CASE
            WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
            ELSE (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
        END                         AS �裲������� --- 4
        ,
        CASE
            WHEN (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN '----------'
            ELSE (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
        END
                                    AS �裲��Ͽ�� --- 5
        ,
        CASE
            WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
            ELSE 
                CASE
                    WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                    ELSE (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                          - (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                END
        END                         AS ����������   --- 6
        ,
        CASE
            WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
            ELSE 
                 CASE
                    WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                    ELSE Uround(((SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                          - (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)) 
                          / (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1), 4) * 100
                 END
        END                         AS ����Ψ��         --- 7
        ,
        CASE
            WHEN (SELECT price FROM sales_price_nk WHERE parts_no = u.assyno ORDER BY parts_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
            ELSE (SELECT price FROM sales_price_nk WHERE parts_no = u.assyno ORDER BY parts_no DESC, regdate DESC LIMIT 1)
        END                         AS �ǿ�����     --- 8
        ,
        CASE
            WHEN (SELECT price FROM sales_price_nk WHERE parts_no = u.assyno ORDER BY parts_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
            ELSE 
                 CASE
                    WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                    ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = u.assyno ORDER BY parts_no DESC, regdate DESC LIMIT 1)
                          /(SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1), 2)
                 END
        END                         AS ��Ψ��         --- 9
        ,
        CASE
            WHEN tgrp.top_name IS NULL THEN '------'
            ELSE tgrp.top_name
        END                         AS ��ʬ��̾     --- 10
        ,
        CASE
            WHEN mgrp.group_name IS NULL THEN '------'
            ELSE mgrp.group_name               
        END                         AS ��ʬ��̾     --- 11
        ---------------- �ꥹ�ȳ� -----------------
        ,
        tgrp.s_order                AS �Ȳ��         -- 12
    FROM
          hiuuri AS u
    LEFT OUTER JOIN
          assembly_schedule AS a
    ON (u.�ײ��ֹ� = a.plan_no)
    LEFT OUTER JOIN
          miitem AS m
    ON (u.assyno = m.mipn)
    LEFT OUTER JOIN
          material_old_product AS mate
    ON (u.assyno = mate.assy_no)
    LEFT OUTER JOIN
          mshmas AS mas
    ON (u.assyno = mas.mipn)
    LEFT OUTER JOIN
          mshmas AS hmas
    ON (u.assyno = hmas.mipn)
    LEFT OUTER JOIN
          -- mshgnm AS gnm
          msshg3 AS gnm
    -- ON (hmas.mhjcd = gnm.mhgcd)
    ON (hmas.mhshc = gnm.mhgcd)
    LEFT OUTER JOIN
          product_serchGroup AS mgrp
    ON (gnm.mhggp = mgrp.group_no)
    LEFT OUTER JOIN
          product_top_serchgroup AS tgrp
    ON (mgrp.top_code = tgrp.top_no)
    WHERE �׾��� >= {$str_ymd} AND �׾��� <= {$end_ymd} AND ������ = '{$div}' AND (note15 NOT LIKE 'SC%%' OR note15 IS NULL) AND datatype='1'
        AND mate.assy_no IS NULL
        -- ������ɲä���м�ư������Ͽ�������� AND (SELECT a_rate FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) > 0
    GROUP BY u.assyno, m.midsc, tgrp.top_name, mgrp.group_name, tgrp.s_order
    {$order_name}
    OFFSET 0 LIMIT 10000
";

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
        $res_csv[$r][1]  = str_replace(',',' ',$res_csv[$r][1]);                   // ����̾��,�����äƤ����CSV�Ƿ夬�����Τ�Ⱦ�ѥ��ڡ�����
        $res_csv[$r][10] = str_replace(',',' ',$res_csv[$r][10]);                  // ����̾��,�����äƤ����CSV�Ƿ夬�����Τ�Ⱦ�ѥ��ڡ�����
        $res_csv[$r][11] = str_replace(',',' ',$res_csv[$r][11]);                  // ����̾��,�����äƤ����CSV�Ƿ夬�����Τ�Ⱦ�ѥ��ڡ�����
        $res_csv[$r][1] = mb_convert_encoding($res_csv[$r][1], 'SJIS', 'EUC');     // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�(EUC���ꤸ��ʤ���ľǼ��Ĵ����������)
        $res_csv[$r][10] = mb_convert_encoding($res_csv[$r][10], 'SJIS', 'EUC');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�(EUC���ꤸ��ʤ���ľǼ��Ĵ����������)
        $res_csv[$r][11] = mb_convert_encoding($res_csv[$r][11], 'SJIS', 'EUC');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�(EUC���ꤸ��ʤ���ľǼ��Ĵ����������)
        
    }
    //$_SESSION['SALES_TEST'] = sprintf("order by �׾��� offset %d limit %d", $offset, PAGE);
    $i = 1;                             // CSV�񤭽Ф��ѥ�����ȡʥե������̾��0������Τǣ������
    $csv_data = array();                // CSV�񤭽Ф�������
    for ($s=0; $s<$num_csv-1; $s++) {     // �ե������̾��CSV�񤭽Ф�������˽���
        $field_csv[$s]   = mb_convert_encoding($field_csv[$s], 'SJIS', 'auto');
        $csv_data[0][$s] = $field_csv[$s];
    }
    for ($r=0; $r<$rows_csv; $r++) {    // �ǡ�����CSV�񤭽Ф�������˽���
        for ($s=0; $s<$num_csv-1; $s++) {
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