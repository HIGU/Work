<?php
//////////////////////////////////////////////////////////////////////////////
// ����Ψʬ�� CSV����                                                       //
// Copyright (C) 2010-2015 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2010/05/21 Created   sales_standard_csv.php                              //
// 2010/12/24 CSV���ϻ�'+'�ʤɤ�ʸ����������ٽ���                          //
// 2011/11/10 �ƥ��Ȥ�NKCT��NKT���ɲ� �� �����ɲ� ���θ���                  //
// 2011/11/14 �ե�����̾�˾��ɽ�����ɲ�                                    //
// 2013/01/29 �Х�������Υݥ�פ��ѹ� ɽ���Τߥǡ����ϥХ����Τޤ�     //
// 2015/05/20 ����å�����Ѵ�����껻�ȽŤʤ�CSV���ϥ��顼�ˤʤ�ٽ���     //
// 2015/05/25 �����������б�                                                //
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
$outputFile  = $_GET['csvname'];
$csv_search  = $_GET['csvsearch'];
// SQL�Υ��������ǰ���ѹ�������ʬ�򸵤��᤹
$search     = str_replace('keidate','�׾���',$csv_search);
$search     = str_replace('jigyou','������',$search);
$search     = str_replace('tanka','ñ��',$search);
$search     = str_replace('|','\'',$search);
$search     = str_replace('-','+',$search);
// ����������ʸ�������ɤ�EUC���ѹ������ǰ�Τ����
$search     = mb_convert_encoding($search, 'EUC-JP', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�

// �ե�����̾�ǰ���ѹ�������ʬ�򸵤��᤹
$outputFile     = str_replace('ALL','����',$outputFile);
$outputFile     = str_replace('C-all','���ץ�����',$outputFile);
$outputFile     = str_replace('C-hyou','���ץ�ɸ��',$outputFile);
$outputFile     = str_replace('C-toku','���ץ�����',$outputFile);
$outputFile     = str_replace('L-all','��˥�����',$outputFile);
$outputFile     = str_replace('L-hyou','��˥��Τ�',$outputFile);
$outputFile     = str_replace('L-bimor','���Υݥ��',$outputFile);
$outputFile     = str_replace('T-all','�ġ���',$outputFile);
$outputFile     = str_replace('NKCT','NKCT',$outputFile);
$outputFile     = str_replace('NKT','NKT',$outputFile);

$outputFile     = str_replace('J0','����',$outputFile);
$outputFile     = str_replace('J1','��',$outputFile);
$outputFile     = str_replace('J2','��',$outputFile);
$outputFile     = str_replace('J3','��',$outputFile);
$outputFile     = str_replace('J4','����¾',$outputFile);

// �¹ԼԤΥѥ������CSV����¸����١��ե�����̾��ʸ�������ɤ�SJIS���Ѵ�
$outputFile = mb_convert_encoding($outputFile, 'SJIS', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�

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
                                WHEN trim(substr(m.midsc,1,25)) = '' THEN '-----'
                                ELSE substr(m.midsc,1,38)
                            END             as ����̾,                  -- 3
                            u.����          as ����,                    -- 4
                            u.ñ��          as ����ñ��,                -- 5
                            Uround(u.���� * u.ñ��, 0) as ����,       -- 6
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) = 0 THEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=u.assyno order by assy_no DESC, regdate DESC limit 1)
                                ELSE sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                            END                     as �������,        -- 7
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) = 0 THEN Uround(u.���� * ((select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=u.assyno order by assy_no DESC, regdate DESC limit 1)), 0)
                                ELSE Uround(u.���� * (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 0)
                            END                     as ��������       -- 8
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
                      left outer join
                            product_support_master AS groupm
                      on u.assyno=groupm.assy_no
                      %s
                      order by �׾���, assyno
                      ", $search);   // ���� $search �Ǹ���

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