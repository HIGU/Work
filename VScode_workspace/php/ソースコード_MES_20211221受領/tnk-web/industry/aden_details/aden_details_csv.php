<?php
//////////////////////////////////////////////////////////////////////////////
// A�������ξȲ� CSV����                                                    //
// Copyright (C) 2016-2016 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2016/03/25 Created   aden_details_csv.php                                //
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

$outputFile = $_GET['csvname'] . '-' . 'A������.csv';
$csv_search = $_GET['csvsearch'];
$order      = $_GET['order'];
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
            publish_day AS A��ȯ����,       -- 00
            receive_day AS A��������,       -- 01
            aden_no     AS A��No,           -- 02
            parts_no    AS ASSYNo,          -- 03
            substr(sale_name, 1, 20)
                        AS ����̾,          -- 04
            order_q     AS ����,            -- 05
            espoir_deli AS ��˾Ǽ��,        -- 06
            delivery    AS ����Ǽ��,        -- 07
            deli_com    AS Ǽ��������,    -- 08
            espoir_lt   AS ��˾LT,          -- 09
            ans_lt      AS Ǽ����LT,        -- 10
            lt_diff     AS LT��,            -- 11
            order_price AS �������,        -- 12
            finish_day  AS �´�����,        -- 13
            finish_del  AS �����٤�,        -- 14
            kouji_no    AS SC����,          -- 15
            plan_no     AS �ײ�No,          -- 16
            answer_day  AS A��������,       -- 17
            ans_day_lt  AS A������LT,       -- 18
            comment     AS ����             -- 19
        FROM
            aden_details_master AS a
        %s 
        ORDER BY %s
    ", $search, $order);   // ���� $search �Ǹ���
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
        $res_csv[$r][4]  = str_replace(',',' ',$res_csv[$r][4]);                   // ����̾��,�����äƤ����CSV�Ƿ夬�����Τ�Ⱦ�ѥ��ڡ�����
        $res_csv[$r][19] = str_replace(',',' ',$res_csv[$r][19]);                  // ���ͤ�,�����äƤ����CSV�Ƿ夬�����Τ�Ⱦ�ѥ��ڡ�����
        //$res_csv[$r][3] = mb_convert_encoding($res_csv[$r][3], 'SJIS', 'EUC');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�(EUC���ꤸ��ʤ���ľǼ��Ĵ����������)
        $res_csv[$r][4]  = mb_convert_encoding($res_csv[$r][4], 'SJIS', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�
        $res_csv[$r][19] = mb_convert_encoding($res_csv[$r][19], 'SJIS', 'auto');  // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�
        if ($res_csv[$r][8] == 0) {
            if ($res_csv[$r][6] == $res_csv[$r][7]) {
                $res_csv[$r][8] = '��˾�̤�';
            } else {
                $res_csv[$r][8] = '̤����';
            }
        } elseif ($res_csv[$r][8] == 1) {
            $res_csv[$r][8] = '�����٤�';
        } elseif ($res_csv[$r][8] == 2) {
            $res_csv[$r][8] = '�߷��ѹ�';
        } elseif ($res_csv[$r][8] == 3) {
            $res_csv[$r][8] = 'L/T��­';
        } elseif ($res_csv[$r][8] == 4) {
            $res_csv[$r][8] = '�����٤�';
        } elseif ($res_csv[$r][8] == 5) {
            $res_csv[$r][8] = '����¾';
        }
        $res_csv[$r][8] = mb_convert_encoding($res_csv[$r][8], 'SJIS', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�
        
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