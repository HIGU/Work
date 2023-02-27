<?php
//////////////////////////////////////////////////////////////////////////////
// ���ץ�ɸ��������ñ�� CSV����                                           //
// Copyright (C) 2010-2021 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2010/05/21 Created   materialNewLinear_csv.php                           //
// 2010/05/26 �����ȥ뤬��˥����ä��Τ򥫥ץ�ɸ����ѹ�                    //
// 2011/03/04 ������Ψ��$rate�ǥޥ�������                                 //
// 2017/09/15 �����ڤ���Ͽ���ν��Ϥ��ɲ�                                    //
// 2021/09/22 ���������Ͽ������ײ�δ��������ѹ�������1����             //
//            ���֤��ǯ����Ⱦǯ���ѹ�������ʬ�ʹߡ�                    //
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
$ind_ym   = $_GET['indym'];
$cost_ymd = $_GET['costymd'];
$str_ymd  = $_GET['strymd'];
$end_ymd  = $_GET['endymd'];

$outputFile = $ind_ym . '-���ץ�������ñ��.csv';

//$outputFile = $_GET['csvname'];
//$csv_search = $_GET['csvsearch'];
// SQL�Υ��������ǰ���ѹ�������ʬ�򸵤��᤹
//$search     = str_replace('keidate','�׾���',$csv_search);
//$search     = str_replace('jigyou','������',$search);
//$search     = str_replace('/','\'',$search);
// ����������ʸ�������ɤ�EUC���ѹ������ǰ�Τ����
//$search     = mb_convert_encoding($search, 'EUC-JP', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�

// �ե�����̾�ǰ���ѹ�������ʬ�򸵤��᤹
//$outputFile     = str_replace('ALL','����',$outputFile);
//$outputFile     = str_replace('C-all','���ץ�����',$outputFile);
//$outputFile     = str_replace('C-hyou','���ץ�ɸ��',$outputFile);
//$outputFile     = str_replace('C-toku','���ץ�����',$outputFile);
//$outputFile     = str_replace('L-all','��˥�����',$outputFile);
//$outputFile     = str_replace('L-hyou','��˥��Τ�',$outputFile);
//$outputFile     = str_replace('L-bimor','�Х����',$outputFile);

if ($ind_ym < 200710) {
    $rate = 25.60;  // ���ץ�ɸ�� 2007/10/01���ʲ������
} elseif ($ind_ym < 201104) {
    $rate = 57.00;  // ���ץ�ɸ�� 2007/10/01���ʲ���ʹ�
} else {
    $rate = 45.00;  // ���ץ�ɸ�� 2011/04/01���ʲ���ʹ�
}
// �¹ԼԤΥѥ������CSV����¸����١��ե�����̾��ʸ�������ɤ�SJIS���Ѵ�
$outputFile = mb_convert_encoding($outputFile, 'SJIS', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�

//////////// CSV�����ѤΥǡ�������
$query_csv = "
        SELECT
            u.assyno                    AS �����ֹ�     -- 0
            ,
            trim(substr(m.midsc,1,30))  AS ����̾       -- 1
            ,
            (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header as c LEFT OUTER JOIN assembly_completion_history AS hist USING(plan_no) WHERE comp_date < {$cost_ymd} AND c.assy_no = u.assyno ORDER BY c.assy_no DESC, regdate DESC LIMIT 1)
                                        AS �ǿ�������� -- 2
            ,
            (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header as c LEFT OUTER JOIN assembly_completion_history AS hist USING(plan_no) WHERE comp_date < {$cost_ymd} AND c.assy_no = u.assyno ORDER BY c.assy_no DESC, regdate DESC LIMIT 1)
                                        AS �������Ͽ�� -- 3
            ,
            credit.credit_per           AS ��Ψ         -- 4
            ,
            0                           AS �ǿ�����     -- 5
            ,
            sale.price                  AS ������       -- 6
            ,
            sale.regdate                AS ��������Ͽ�� -- 7
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
              parts_credit_per AS credit
        ON (u.assyno = credit.parts_no)
        LEFT OUTER JOIN
              sales_price_nk AS sale
        ON (u.assyno = sale.parts_no)
        WHERE �׾��� >= {$str_ymd} AND �׾��� <= {$end_ymd} AND ������ = 'C' AND (note15 NOT LIKE 'SC%%' OR note15 IS NULL) AND datatype='1'
            AND mate.assy_no IS NULL
            -- ������ɲä���м�ư������Ͽ�������� AND (SELECT a_rate FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) > 0
        GROUP BY u.assyno, m.midsc, credit.credit_per, sale.price , sale.regdate
        ORDER BY u.assyno ASC
        OFFSET 0 LIMIT 5000
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
        $res_csv[$r][1] = str_replace(',',' ',$res_csv[$r][1]);                   // ����̾��,�����äƤ����CSV�Ƿ夬�����Τ�Ⱦ�ѥ��ڡ�����
        $res_csv[$r][1] = mb_convert_encoding($res_csv[$r][1], 'SJIS', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�
    }
    //$_SESSION['SALES_TEST'] = sprintf("order by �׾��� offset %d limit %d", $offset, PAGE);
    $i = 1;                             // CSV�񤭽Ф��ѥ�����ȡʥե������̾��0������Τǣ������
    $csv_data = array();                // CSV�񤭽Ф�������
    for ($s=0; $s<$num_csv; $s++) {     // �ե������̾��CSV�񤭽Ф�������˽���
        $field_csv[$s]   = mb_convert_encoding($field_csv[$s], 'SJIS', 'auto');
        $csv_data[0][$s] = $field_csv[$s];
    }
    for ($r=0; $r<$rows_csv; $r++) {    // �ǡ�����CSV�񤭽Ф�������˽���
        if (comp_date($res_csv[$r][0], $end_ymd)) {
            $res_csv[$r][4] = 1.18;
        }
        $res_csv[$r][5] = ROUND(($res_csv[$r][2] * $res_csv[$r][4]), 2);
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

function comp_date($assyNo, $endymd)
{
    $assy = $assyNo;
    $query = "SELECT hist.comp_date                               AS �������
                FROM
                    assembly_completion_history AS hist
                LEFT OUTER JOIN
                    material_cost_header AS mate USING(plan_no)
                LEFT OUTER JOIN
                    assembly_schedule AS asse USING(plan_no)
                LEFT OUTER JOIN
                    miitem AS item ON (hist.assy_no=item.mipn)
                WHERE hist.assy_no LIKE '{$assy}' -- '{$assy}'
                ORDER BY hist.assy_no DESC, hist.comp_date ASC --�ײ��� DESC
                LIMIT 1";
    $rows=getResult2($query, $res_i);
    $comp_year  = substr($res_i[0][0], 0, 4);
    $comp_month = substr($res_i[0][0], 4, 2);
    if ($comp_month < 4) {
        $comp_year  = $comp_year + 1;
    } else {
        $comp_year  = $comp_year + 2;
    }
    $comp_date = $comp_year . '03' . '31';
    if ($comp_date <= $endymd) {
        return false;
    } else {
        return true;
    }
}
?>