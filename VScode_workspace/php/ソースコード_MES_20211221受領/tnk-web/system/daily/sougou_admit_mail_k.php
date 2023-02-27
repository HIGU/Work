#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ����Ͼ�ǧ�Ԥ�����᡼������ cron.d tnk_daily �����Ǽ¹�                 //
// ������16��40���˥᡼������ ������                                      //
// SELECT DISTINCT admit_status FROM sougou_deteils                         //
//                                             WHERE admit_status ='300055' //
// 300055=���Ĺ���� ���߸����� ID�����ɲäξ��� or �ǷҤ�              //
// Copyright (C) 2020-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2020/09/24 Created   sougou_admit_mail.php                               //
// 2021/02/17 ��ʸ����˾�ǧ�ڡ����Υ��ɥ쥹���ɲ�                          //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
//ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');    ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤǥ����ץ�
fwrite($fpb, "����Ͼ�ǧ�Ԥ�����᡼������\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/sougou_admit_mail.php\n");
echo "/home/www/html/tnk-web/system/daily/sougou_admit_mail.php\n";

if ($con = db_connect()) {
    query_affected_trans($con, "begin");
} else {
    echo "$log_date ����Ͼ�ǧ�Ԥ�����᡼������ db_connect() error \n";
    fwrite($fpa,"$log_date ����Ͼ�ǧ�Ԥ�����᡼������ db_connect() error \n");
    fwrite($fpb,"$log_date ����Ͼ�ǧ�Ԥ�����᡼������ db_connect() error \n");
    exit();
}

/////////// ���եǡ����μ���
$target_ym   = date('Ym');          //201710
$b_target_ym = $target_ym - 100;    //201610
$today       = date('Ymd');         //20171012
$b_today     = $today - 10000;      //20161012

/////////// begin �ȥ�󥶥�����󳫻�
$query = sprintf("SELECT DISTINCT admit_status FROM sougou_deteils WHERE admit_status ='300055'");
$res   = array();
$field = array();
if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
    exit();
} else {
    $num = count($field);       // �ե�����ɿ�����
    for ($r=0; $r<$rows; $r++) {
        $query_t = "SELECT 
                                count(admit_status) as t_ken
                          FROM sougou_deteils ";
        $search_t = "WHERE admit_status='{$res[$r][0]}'";
        $query_t = sprintf("$query_t %s", $search_t);     // SQL query ʸ�δ���
        $res_t   = array();
        $field_t = array();
        $res_sum_t = array();
        if (getResult($query_t, $res_sum_t) <= 0) {
            exit();
        } else {
            $t_ken     = $res_sum_t[0]['t_ken'];
            $_SESSION['u_t_ken']  = $t_ken;
            if ($t_ken>0) {
                $query_m = "SELECT trim(name), trim(mailaddr)
                                FROM
                                    user_detailes
                                LEFT OUTER JOIN
                                    user_master USING(uid)
                                ";
                //$search_m = "WHERE uid='300144'";
                // ��ϥƥ����� ����Ū�˼�ʬ�˥᡼�������
                $search_m = "WHERE uid='{$res[$r][0]}'";
                $query_m = sprintf("$query_m %s", $search_m);     // SQL query ʸ�δ���
                $res_m   = array();
                $field_m = array();
                $res_sum_m = array();
                if (getResult($query_m, $res_sum_m) <= 0) {
                    exit();
                } else {
                    $sendna = $res_sum_m[0][0];
                    $mailad = $res_sum_m[0][1];
                    $_SESSION['u_mailad']  = $mailad;
                    $to_addres = $mailad;
                    $add_head = "";
                    $attenSubject = "���衧 {$sendna} �� ����Ͼ�ǧ�Ԥ��Τ��Τ餻";
                    $message   = "{$sendna} ��\n\n";
                    $message  .= "����Ϥξ�ǧ�Ԥ���{$t_ken}�濫��ޤ���\n\n";
                    //�ƥ����� �����ѹ����뤳��
                    //$message  = "����Ϥξ�ǧ�Ԥ���{$t_ken}�濫��ޤ���\n\n";
                    $message .= "����Ϥξ�ǧ�����򤪴ꤤ���ޤ���\n\n";
                    // ��ǧ�ڡ����Υ��ɥ쥹(Uid)��ɽ��������å��Ǿ�ǧ�ڡ�����
                    $message .= "http://masterst/per_appli/in_sougou_admit/sougou_admit_Main.php?calUid=";
                    $message .= $res[$r][0];
                    if (mb_send_mail($to_addres, $attenSubject, $message, $add_head)) {
                        // ���ʼԤؤΥ᡼�������������¸
                        //$this->setAttendanceMailHistory($serial_no, $atten[$i]);
                    }
                    ///// Debug
                    //if ($cancel) {
                    //    if ($i == 0) mb_send_mail('tnksys@nitto-kohki.co.jp', $attenSubject, $message, $add_head);
                    //}
                }
            } else {
                
            }
        }
    }
}


/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, "commit");
// echo $query . "\n";
fclose($fpa);      ////// �����ѥ�����߽�λ
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߽�λ

exit();

?>
