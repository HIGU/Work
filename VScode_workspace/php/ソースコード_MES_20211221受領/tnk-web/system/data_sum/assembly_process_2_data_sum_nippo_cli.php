#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// assembly_process_time TO DATA SUM ������ǡ���(AS��)������ ��ư����cli�� //
// Copyright (C) 2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/10/20 Created  assembly_process_2_data_sum_nippo_cli.php            //
// 2005/12/10 ����ԤΥ����å��򤷱��糫��(������)�ν����ɲ�                //
// 2007/04/07 �ǥ��쥯�ȥ�� data_sum/ �� ���ե�������̲� �ѥ������ѹ� //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', '0');             // echo print �� flush ������(�٤��ʤ뤬�᡼����å��Τ���)
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ

$currentFullPathName = realpath(dirname(__FILE__));
require_once ("{$currentFullPathName}/../../function.php");

$log_date = date('Y-m-d H:i:s');            // �����ѥ�������
$fpa = fopen('/tmp/data_sum.log', 'a');     ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
// $fpa = fopen('/tmp/assembly_2_dsum_nippo.log', 'a');     // �����ѥ��ե�����ؤν���ߤǥ����ץ�
fwrite($fpa,"$log_date Assembly process time TO DATA SUM nippo Start \n");

// �ǡ�������Υ��ե����� ������� �������
$file_nippo  = "{$currentFullPathName}/backup/data_sum_nippo.log";
$fpw = fopen($file_nippo, 'a');    // �����ѥե�����Υ����ץ�(�ɹ��ߤ⤹�����+���ˤĤ���)

// �����Υǡ�������
$yesterday = date('Ymd', mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 10);    // ������ǯ����
$str_date = "$yesterday 000000";
$end_date = "$yesterday 235959";
$query = "
    SELECT
        to_char(group_no, 'FM00') AS group_no
        , user_id
        , plan_no
        , to_char(end_time, 'YYMMDDHH24MI') AS end_time
        , to_char(str_time, 'YYMMDDHH24MI') AS str_time
    FROM
        assembly_process_time
    WHERE
        end_time >= '{$str_date}' AND end_time <= '{$end_date}'
    ORDER BY
        group_no ASC
";
$res = array();     // �����
if ( ($rows=getResult($query, $res)) > 0 ) {
    for ($i=0; $i<$rows; $i++) {
        ///// �ǡ�����С��Ѵ�����
        $group_no = $res[$i]['group_no'];
        $user_id  = $res[$i]['user_id'];
        $plan_no  = $res[$i]['plan_no'];
        if (substr($plan_no, 0, 1) == '@') $plan_no = 'Z' . substr($plan_no, 1, 7);
        $end_time = $res[$i]['end_time'];
        
        // ����ԤΥ����å�
        if (substr($user_id, 0, 3) == '777') {
            // ���糫�Ϥν��� 916
            $str_time = $res[$i]['str_time'];
            $data = "{$group_no}{$str_time}916{$user_id}000000000000000000000000000000000000000000000000000000000000000000000000000000000000{$plan_no}00000        00000        00000        00000        00000        00000        00000        00000        00000        00000000\n";
            ///// ����ե�����ؽ����
            if (!fwrite($fpw, $data, 300)) {
                fwrite($fpa,"$log_date data_sum_nippo.log Write Error \n");
            }
        }
        
        ///// �ǡ�������ߴ��ν񼰤ǥǡ�������
        $data = "{$group_no}{$end_time}910{$user_id}000000000000000000000000000000000000000000000000000000000000000000000000000000000000{$plan_no}00000        00000        00000        00000        00000        00000        00000        00000        00000        00000000\n";
        
        ///// ����ե�����ؽ����
        if (!fwrite($fpw, $data, 300)) {
            fwrite($fpa,"$log_date data_sum_nippo.log Write Error \n");
        }
    }
}
fclose($fpw);   ////// �������߽�λ
fwrite($fpa,"$log_date �оݥǡ����� {$rows}��Ǥ����� \n");
fwrite($fpa,"$log_date Assembly process time TO DATA SUM nippo End \n");
fclose($fpa);   ////// �����ѥ�����߽�λ
exit();

?>
