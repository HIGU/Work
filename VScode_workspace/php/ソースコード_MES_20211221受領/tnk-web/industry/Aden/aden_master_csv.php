<?php
//////////////////////////////////////////////////////////////////////////////
// ������٤ξȲ� CSV����                                                   //
// Copyright (C) 2010-2019 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2010/05/21 Created   materialNewSales_csv.php                            //
// 2010/12/20 ��������б�       ľǼ��Ĵ����������                       //
// 2010/12/24 ľǼ��Ĵ����ʸ���������б�                                    //
// 2011/11/10 �ƥ��Ȥ�NKCT��NKT���ɲ� �� �����ɲ� ���θ���                  //
// 2011/11/21 �ե�����̾�Ѵ��ǥ��ץ�����ȴ���Ƥ����Τ���                //
//            �ե�����̾����������.csv���ѹ�                              //
// 2013/01/29 �Х�������Υݥ�פ��ѹ� ɽ���Τߥǡ����ϥХ����Τޤ�     //
// 2013/05/28 ������λ�����ɲ�                                            //
// 2014/11/19 ����ξ��Ϲ����ֹ����Ϥ���褦���ѹ�                      //
// 2015/11/27 ����(���ܡ�5�ʹ�)���ϻ���������������������Ǥ��Ƥ��ʤ�     //
//            �Զ�����                                                  //
// 2018/06/22 �����������ʤ�ȴ���Ф��Ƥ��ʤ��ä��Τ�����                  //
// 2019/10/09 ��ɥƥå��ȥ�ɡ����ȤΥե�����̾�Ѵ�����äƤ����Τǽ�����ë//
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
$outputFile = 'A������.csv';
$csv_search = $_GET['csvsearch'];
$csv_sort   = $_GET['csvsort'];
// SQL�Υ��������ǰ���ѹ�������ʬ�򸵤��᤹
$search     = str_replace('/','\'',$csv_search);
$sort       = str_replace('/','\'',$csv_sort);
// ����������ʸ�������ɤ�EUC���ѹ������ǰ�Τ����
$search     = mb_convert_encoding($search, 'EUC-JP', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�
$sort       = mb_convert_encoding($sort, 'EUC-JP', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�

// �¹ԼԤΥѥ������CSV����¸����١��ե�����̾��ʸ�������ɤ�SJIS���Ѵ�
$outputFile = mb_convert_encoding($outputFile, 'SJIS', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�

//////////// CSV�����ѤΥǡ�������
$query_csv = sprintf("
        SELECT
            aden.aden_no     as ����,                        -- 0
            aden.eda_no      as ��,                          -- 1
            CASE
                WHEN trim(aden.parts_no) = '' THEN '---'
                ELSE aden.parts_no
            END         as �����ֹ�,                    -- 2
            CASE
                WHEN trim(aden.sale_name) = '' THEN '&nbsp;'
                ELSE trim(aden.sale_name)
            END         as ���侦��̾,                  -- 3
            CASE
                WHEN trim(aden.plan_no) = '' THEN '---'
                ELSE aden.plan_no
            END         as �ײ��ֹ�,                    -- 5
            CASE
                WHEN trim(aden.approval) = '' THEN '---'
                ELSE aden.approval
            END         as ��ǧ��,                      -- 6
            CASE
                WHEN trim(aden.ropes_no) = '' THEN '---'
                ELSE aden.ropes_no
            END         as ���ν�,                      -- 7
            CASE
                WHEN trim(aden.kouji_no) = '' THEN '---'
                ELSE aden.kouji_no
            END         as �����ֹ�,                    -- 8
            aden.order_q     as �������,                    -- 9
            aden.order_price as ����ñ��,                    --10
            Uround(aden.order_q * aden.order_price, 0) as ���,   --11
            aden.espoir_deli as ��˾Ǽ��,                    --12
            aden.delivery    as ����Ǽ��,                    --13
            aden.publish_day    AS  ȯ����,                  --14
            
            sche.line_no    AS  �饤��,                 --19
            (sche.plan - sche.cut_plan - sche.kansei)
                            AS  �ײ��,                 --18
            sche.syuka      AS  ������,                 --15
            sche.chaku      AS  �����,                 --16
            sche.kanryou    AS  ��λ��                  --17
        FROM
            aden_master             AS aden
        LEFT OUTER JOIN
            miitem                              ON aden.parts_no=mipn
        LEFT OUTER JOIN
            assembly_schedule       AS sche     using(plan_no)
        %s 
        ORDER BY
        %s
        
    ", $search, $sort);       // ���� $search �Ǹ���
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
        //$res_csv[$r][1] = mb_convert_encoding($res_csv[$r][1], 'SJIS', 'EUC');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�(EUC���ꤸ��ʤ���ľǼ��Ĵ����������)
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