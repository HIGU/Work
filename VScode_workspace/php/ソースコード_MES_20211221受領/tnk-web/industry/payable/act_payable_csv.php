<?php
//////////////////////////////////////////////////////////////////////////////
// ��ݼ��ӤξȲ� CSV����                                                   //
// Copyright (C) 2010-2018 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2013/10/10 Created   act_payable_csv.php                                 //
// 2013/10/12 �ե�����̾��ȯ�����ɽ�����ɲ�                                //
// 2018/01/29 ���ץ�����ɸ����ɲ�                                   ��ë //
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
$vendor     = $_GET['csvvendor'];
//////// ���Ϲ���̾�μ���
if ($vendor == '') {
    $vendor_name = 'ȯ�������ʤ�';
} else {
    $query = "select name from vendor_master where vendor='{$vendor}'";
    if (getUniResult($query, $vendor_name) < 1) {
        $vendor_name = '̤��Ͽ';
    }
}
$vendor_name = trim($vendor_name);
$vendor_name = rtrim($vendor_name, "��");

$outputFile = $_GET['csvname'] . '-' . $vendor_name . '-' . '��ݼ���.csv';
$csv_search = $_GET['csvsearch'];
// SQL�Υ��������ǰ���ѹ�������ʬ�򸵤��᤹
//$search     = str_replace('keidate','�׾���',$csv_search);
//$search     = str_replace('jigyou','������',$search);
//$search     = str_replace('denban','��ɼ�ֹ�',$search);
//$search     = str_replace('tokui','������',$search);
$search     = str_replace('/','\'',$csv_search);
// ����������ʸ�������ɤ�EUC���ѹ������ǰ�Τ����
$search     = mb_convert_encoding($search, 'EUC-JP', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�

// �ե�����̾�ǰ���ѹ�������ʬ�򸵤��᤹
$outputFile     = str_replace('ALL','�����롼��',$outputFile);
$outputFile     = str_replace('C-all','���ץ�',$outputFile);
$outputFile     = str_replace('C-hyo','��ɸ��',$outputFile);
$outputFile     = str_replace('C-toku','������',$outputFile);
$outputFile     = str_replace('L-all','��˥�',$outputFile);
$outputFile     = str_replace('NKCT','NKCT',$outputFile);
$outputFile     = str_replace('NKT','NKT',$outputFile);

// �ե�����̾�ǰ���ѹ�������ʬ�򸵤��᤹��������̾��
/*
$outputFile     = str_replace('T-�����롼��','����',$outputFile);
$outputFile     = str_replace('T-NK','���칩��',$outputFile);
$outputFile     = str_replace('T-MEDO','��ɡ�����',$outputFile);
$outputFile     = str_replace('T-NKT','NKT',$outputFile);
$outputFile     = str_replace('T-MEDOTEC','��ɥƥå�',$outputFile);
$outputFile     = str_replace('T-SNK','������칩��',$outputFile);
$outputFile     = str_replace('T-NKCT','NKCT',$outputFile);
$outputFile     = str_replace('T-BRECO','BRECO',$outputFile);
$outputFile     = str_replace('T-SHO','����',$outputFile);
*/
// �¹ԼԤΥѥ������CSV����¸����١��ե�����̾��ʸ�������ɤ�SJIS���Ѵ�
$outputFile = mb_convert_encoding($outputFile, 'SJIS', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�

//////////// CSV�����ѤΥǡ�������
$query_csv = sprintf("
        SELECT
            -- act_date    as ������,
            -- type_no     as \"T\",
            uke_no      as ������,          -- 00
            uke_date    as ������,          -- 01
            ken_date    as ������,          -- 02
            substr(trim(name), 1, 8)
                        as ȯ����̾,        -- 03
            a.parts_no    as �����ֹ�,        -- 04
            substr(midsc, 1, 12)
                        AS ����̾,          -- 05
            substr(mepnt, 1, 10)
                        AS �Ƶ���,          -- 06
            koutei      as ����,            -- 07
            mtl_cond    as ��,      -- ���    08
            order_price as ȯ��ñ��,        -- 09
            genpin      as ���ʿ�,          -- 10
            siharai     as ��ʧ��,          -- 11
            Uround(order_price * siharai,0)
                        as ��ݶ��,        -- 12
            sei_no      as ��¤�ֹ�,        -- 13
            a.div       as ��,              -- 14
            kamoku      as ��,              -- 15
            order_no    as ��ʸ�ֹ�,        -- 16
            vendor      as ȯ����,          -- 17
            o.kouji_no  as �����ֹ�         -- 18
        FROM
            act_payable AS a
        LEFT OUTER JOIN
            vendor_master USING(vendor)
        LEFT OUTER JOIN
            miitem ON (parts_no = mipn)
        LEFT OUTER JOIN
            parts_stock_master AS m ON (m.parts_no=a.parts_no)
        LEFT OUTER JOIN
            order_plan AS o USING(sei_no)
        %s 
        ORDER BY act_date DESC
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
        $res_csv[$r][3] = str_replace(',',' ',$res_csv[$r][3]);                   // ȯ����̾��,�����äƤ����CSV�Ƿ夬�����Τ�Ⱦ�ѥ��ڡ�����
        $res_csv[$r][5] = str_replace(',',' ',$res_csv[$r][5]);                   // ����̾��,�����äƤ����CSV�Ƿ夬�����Τ�Ⱦ�ѥ��ڡ�����
        $res_csv[$r][6] = str_replace(',',' ',$res_csv[$r][6]);                   // �ƴ����,�����äƤ����CSV�Ƿ夬�����Τ�Ⱦ�ѥ��ڡ�����
        //$res_csv[$r][3] = mb_convert_encoding($res_csv[$r][3], 'SJIS', 'EUC');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�(EUC���ꤸ��ʤ���ľǼ��Ĵ����������)
        $res_csv[$r][3] = mb_convert_encoding($res_csv[$r][3], 'SJIS', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�
        $res_csv[$r][5] = mb_convert_encoding($res_csv[$r][5], 'SJIS', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�
        $res_csv[$r][6] = mb_convert_encoding($res_csv[$r][6], 'SJIS', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�
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