<?php
//////////////////////////////////////////////////////////////////////////////
// Ĺ����α���ʤξȲ� CSV����                                               //
// Copyright (C) 2013-2014 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2013/06/13 Created   long_holding_parts_csv.php                          //
// 2014/05/07 CSV���ϻ��Τߺ����ɽ������褦���ѹ�                         //
// 2014/09/11 CSV���ϻ�ê��2E2����2.00E���Ȼؿ�ɽ���ˤʤ�Τ��ɤ��褦�ѹ�   //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../../MenuHeader.php');      // TNK ������ menu class
require_once ('../../../ControllerHTTP_Class.php');// TNK ������ MVC Controller Class

// �ե�����̾��SQL�Υ���������������
$outputFile = 'Ĺ����α����.csv';
$csv_search = $_GET['csvsearch'];
// SQL�Υ��������ǰ���ѹ�������ʬ�򸵤��᤹
$search     = str_replace('saitanka','�ǿ�ñ��',$csv_search);
$search     = str_replace('kingaku','���',$search);
$search     = str_replace('/','\'',$search);
// ����������ʸ�������ɤ�EUC���ѹ������ǰ�Τ����
$search     = mb_convert_encoding($search, 'EUC-JP', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�

// �¹ԼԤΥѥ������CSV����¸����١��ե�����̾��ʸ�������ɤ�SJIS���Ѵ�
$outputFile = mb_convert_encoding($outputFile, 'SJIS', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�

//////////// CSV�����ѤΥǡ�������
$query_csv = sprintf("SELECT CASE
                                WHEN trim(long.tnk_tana) = '' THEN ''
                                ELSE long.tnk_tana
                                END             AS ê�ֹ�           -- 00
                                ,long.parts_no  AS �����ֹ�         -- 01
                                ,trim(substr(long.parts_name, 1, 16))
                                                AS ����̾           -- 02
                                ,CASE
                                    WHEN mepnt='' THEN ''
                                    WHEN mepnt IS NULL THEN ''
                                    ELSE mepnt
                                END             AS �Ƶ���           -- 03
                                ,CASE
                                    WHEN mzist='' THEN ''
                                    WHEN mzist IS NULL THEN ''
                                    ELSE mzist
                                END             AS ���             -- 04
                                ,to_char(long.in_date, 'FM0000/00/00')
                                                AS ������           -- 05
                                ,in_pcs         AS ���˿�           -- 06
                                ,tnk_stock + nk_stock
                                                AS ���߸�           -- 07
                                ,CASE
                                    WHEN tanka IS NULL THEN 0
                                    ELSE tanka
                                END             AS �ǿ�ñ��         -- 08
                                ,CASE
                                    WHEN tanka IS NULL THEN 0
                                    ELSE UROUND((tnk_stock + nk_stock) * tanka, 0)
                                END             AS ���             -- 09
                                FROM
                                    long_holding_parts_work1 AS long
                                LEFT OUTER JOIN
                                    act_payable
                                    ON (long.parts_no=act_payable.parts_no AND long.in_date=act_payable.act_date AND long.den_no=act_payable.uke_no)
                                    -- Ʊ�������ֹ��Ʊ���ˣ��󸡼����������б���(long.in_pcs=act_payable.genpin)���ɲâ�(long.den_no=act_payable.uke_no)���ѹ�
                                LEFT OUTER JOIN
                                    order_plan USING(sei_no)
                                LEFT OUTER JOIN
                                    miitem ON (long.parts_no=miitem.mipn)
                                %s
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
        $res_csv[$r][0] = '="' . $res_csv[$r][0] . '"';                         // 2E2����ê�֤�CSV��ؿ���Ƚ�Ǥ����Τ��ɤ�
        $res_csv[$r][2] = mb_convert_kana($res_csv[$r][2], 'ka', 'EUC-JP');     // ���ѥ��ʤ�Ⱦ�ѥ��ʤإƥ���Ū�˥���С���
        $res_csv[$r][3] = mb_convert_kana($res_csv[$r][3], 'ka', 'EUC-JP');     // ���ѥ��ʤ�Ⱦ�ѥ��ʤإƥ���Ū�˥���С���
        $res_csv[$r][4] = mb_convert_kana($res_csv[$r][4], 'ka', 'EUC-JP');     // ���ѥ��ʤ�Ⱦ�ѥ��ʤإƥ���Ū�˥���С���
        $res_csv[$r][2] = str_replace(',',' ',$res_csv[$r][2]);                 // ����̾��,�����äƤ����CSV�Ƿ夬�����Τ�Ⱦ�ѥ��ڡ�����
        $res_csv[$r][3] = str_replace(',',' ',$res_csv[$r][3]);                 // �Ƶ����,�����äƤ����CSV�Ƿ夬�����Τ�Ⱦ�ѥ��ڡ�����
        $res_csv[$r][4] = str_replace(',',' ',$res_csv[$r][4]);                 // �Ƶ����,�����äƤ����CSV�Ƿ夬�����Τ�Ⱦ�ѥ��ڡ�����
        $res_csv[$r][0] = mb_convert_encoding($res_csv[$r][0], 'SJIS', 'EUC');  // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�(EUC���ꤸ��ʤ���ľǼ��Ĵ����������)
        $res_csv[$r][2] = mb_convert_encoding($res_csv[$r][2], 'SJIS', 'EUC');  // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�(EUC���ꤸ��ʤ���ľǼ��Ĵ����������)
        $res_csv[$r][3] = mb_convert_encoding($res_csv[$r][3], 'SJIS', 'EUC');  // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�
        $res_csv[$r][4] = mb_convert_encoding($res_csv[$r][4], 'SJIS', 'EUC');  // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�
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