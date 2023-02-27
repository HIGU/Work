#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ����ֳ���ȿ���Ķȷ��������᡼������ cron.d tnk_daily �����Ǽ¹�   //
// �����λ�����˥᡼������ ������ �Ķȷ���������ǧ�Ԥ�����᡼��   //
// Copyright (C) 2021-2021 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2021/10/06 Created   over_time_work_report_input_mail.php                //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
//ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');    ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤǥ����ץ�
fwrite($fpb, "����ֳ���ȿ���Ķȷ��������᡼������\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/over_time_work_report_admit_mail.php\n");
echo "/home/www/html/tnk-web/system/daily/over_time_work_report_admit_mail.php\n";

if ($con = db_connect()) {
    query_affected_trans($con, "begin");
} else {
    echo "$log_date ����ֳ���ȿ���Ķȷ��������᡼������ db_connect() error \n";
    fwrite($fpa,"$log_date ����ֳ���ȿ���Ķȷ��������᡼������ db_connect() error \n");
    fwrite($fpb,"$log_date ����ֳ���ȿ���Ķȷ��������᡼������ db_connect() error \n");
    exit();
}

/////////// begin �ȥ�󥶥�����󳫻�
// ����Ĺ����Ĺ����Ĺ
$where = "(ud.pid=110)";
$where = "(ud.pid=47 OR ud.pid=70 OR ud.pid=95)";
$where = "(ud.pid=46 OR ud.pid=50)";
$where = "((ud.pid=110) OR (ud.pid=47 OR ud.pid=70 OR ud.pid=95) OR (ud.pid=46 OR ud.pid=50))";

// ����Ĺ�� uid �� act_id ����
$query = "
            SELECT          uid, ct.act_id, pid, trim(name)
            FROM            user_detailes   AS ud
            LEFT OUTER JOIN cd_table        AS ct   USING(uid)
            WHERE           retire_date IS NULL AND $where
         ";
$res_list = array();
if( ($rows_list = getResult($query, $res_list)) <= 0) exit(); // �����ԲĤʤ齪λ��

for( $r=0; $r<$rows_list; $r++ ) {
    // ������
    if( $res_list[$r][1] == 600 ) {  // ����Ĺ
        if( $res_list[$r][0] == '012394' ) {  // ������Ĺ
            $deploy = "(deploy='��¤�� ��¤����' OR deploy='��¤�� ��¤����')";
        } else {
            $deploy = "(deploy IS NOT NULL)";
        }
    } else if( $res_list[$r][1] == 610 ) {  // ������
        $deploy = "(deploy='��̳��' OR deploy='���ʴ�����')";
    } else if( $res_list[$r][1] == 605 || $res_list[$r][1] == 650 || $res_list[$r][1] == 651 || $res_list[$r][1] == 660 ) { // �ɣӣϻ�̳�� ������ ��̳�� ��̳ ��̳
        $deploy = "(deploy='��̳��')";
    } else if( $res_list[$r][1] == 670 ) {  // ������ ���ʴ�����
        $deploy = "(deploy='���ʴ�����')";
    } else if( $res_list[$r][1] == 501 ) {  // ������
        $deploy = "(deploy='�ʼ��ݾڲ�' OR deploy='���Ѳ�')";
    } else if( $res_list[$r][1] == 174 || $res_list[$r][1] == 517 || $res_list[$r][1] == 537 || $res_list[$r][1] == 581 ) { // ������ �ʼ�������
        $deploy = "(deploy='�ʼ��ݾڲ�')";
    } else if( $res_list[$r][1] == 173 || $res_list[$r][1] == 515 || $res_list[$r][1] == 535 ) { // ������ ���Ѳ�
        $deploy = "(deploy='���Ѳ�')";
    } else if( $res_list[$r][1] == 582 ) {  // ��¤��
        $deploy = "(deploy='��¤�� ��¤����' OR deploy='��¤�� ��¤����')";
    } else if( $res_list[$r][1] == 518 || $res_list[$r][1] == 519 || $res_list[$r][1] == 556 || $res_list[$r][1] == 520 ) { // ��¤�� ��¤����
        $deploy = "(deploy='��¤�� ��¤����')";
    } else if( $res_list[$r][1] == 547 || $res_list[$r][1] == 528 || $res_list[$r][1] == 527 ) { // ��¤�� ��¤����
        $deploy = "(deploy='��¤�� ��¤����')";
    } else if( $res_list[$r][1] == 500 ) {  // ������
        $deploy = "(deploy='���������� �ײ衦���㷸' OR deploy='���������� ��෸' OR deploy='���ץ���Ω�� ɸ�෸�ͣ�' OR deploy='���ץ���Ω�� ɸ�෸�ȣ�' OR deploy='���ץ���Ω�� ����' OR deploy='��˥���Ω��')";
    } else if( $res_list[$r][1] == 545 || $res_list[$r][1] == 512 || $res_list[$r][1] == 532 || $res_list[$r][1] == 513|| $res_list[$r][1] == 533 || $res_list[$r][1] == 514 || $res_list[$r][1] == 534 ) { // ������ ����������
        $deploy = "(deploy='���������� �ײ衦���㷸' OR deploy='���������� ��෸')";
    } else if( $res_list[$r][1] == 176 || $res_list[$r][1] == 522 || $res_list[$r][1] == 523 || $res_list[$r][1] == 525 ) { // ������ ���ץ���Ω��
        $deploy = "(deploy='���ץ���Ω�� ɸ�෸�ͣ�' OR deploy='���ץ���Ω�� ɸ�෸�ȣ�' OR deploy='���ץ���Ω�� ����')";
    } else if( $res_list[$r][1] == 551 || $res_list[$r][1] == 175 || $res_list[$r][1] == 572 ) { // ������ ��˥���Ω��
        $deploy = "(deploy='��˥���Ω��')";
    } else {
        $deploy = "(deploy IS NULL)";   // ���顼
    }
    // ����������
    $noinput1 = "yo_ad_rt!='-1' AND yo_ad_rt<=yo_ad_st AND ji_ad_rt=0 AND date!=date('today')";
    if( $res_list[$r][2] == 110 ) {
        $noinput = "yo_ad_ka IS NULL AND yo_ad_bu IS NULL";
        $noadmit = "ji_ad_ko='m' AND (ji_ad_ka IS NULL OR ji_ad_ka!='m') AND (ji_ad_bu IS NULL OR ji_ad_bu!='m')";
    } else if( $res_list[$r][2] == 47 || $res_list[$r][2] == 70 || $res_list[$r][2] == 95 ) {
        $noinput = "yo_ad_ka IS NULL";
        $noadmit = "ji_ad_bu='m' AND (ji_ad_ka IS NULL OR ji_ad_ka!='m')";
    } else if( $res_list[$r][2] == 46 || $res_list[$r][2] == 50 ) {
        $noinput = "yo_ad_ka!=''";
        $noadmit = "ji_ad_ka='m'";
    } else {
        $noinput = $noadmit = $deploy;
    }
    $where_noinput = "WHERE {$noinput1} AND {$noinput} AND {$deploy}";
    $where_noadmit = "WHERE {$noadmit} AND {$deploy}";
    
    // ������̤���ϼ���
    $query = "SELECT DISTINCT date, deploy FROM over_time_report $where_noinput";
    $res_noinput  = array();
    $rows_noinput = getResult($query, $res_noinput);
/**    
    // ������̤��ǧ����
    $query = "SELECT DISTINCT date, deploy FROM over_time_report $where_noadmit";
    $res_noadmit  = array();
    $rows_noadmit = getResult($query, $res_noadmit);
/**/    
    $uid = $res_list[$r][0];
    // �᡼�����ɥ쥹����
    $query = "SELECT trim(name), trim(mailaddr) FROM user_detailes LEFT OUTER JOIN user_master USING(uid)";
    $where = "WHERE uid='{$uid}'";  // uid
//    $where = "WHERE uid='300667'";  // uid �����ѹ� ����꡼�����ϡ������Ȳ�
    $query .= $where;   // SQL query ʸ�δ���
    $res_mail = array();
    if( getResult($query, $res_mail) <= 0 ) continue; // �᡼�륢�ɥ쥹�����ԲĤʤ鼡��
    
    // �᡼�����������
    $sendna = $res_mail[0][0];  // ̾��
//    $sendna = $res_list[$r][3]; // ̾�� �����ѹ� ����꡼�����ϡ������Ȳ�
    $mailad = $res_mail[0][1];  // �᡼�륢�ɥ쥹
    $_SESSION['u_mailad']  = $mailad;
    $to_addres = $mailad;
    $add_head = "";
    $attenSubject = "{$sendna} �� ��̤���ϡ� ����ֳ���ȿ����ꤪ�Τ餻"; // ���衧 
    $message = "{$sendna} ��\n\n";
    $message .= "����ֳ���ȿ���ʻĶȷ������";
    
    if( $rows_noinput <= 0 ) continue; // ��ǧ�Ԥ�̵���ʤ鼡��
    
    if( $rows_noinput <= 0 ) {
        $message .= "��̤ �� �ϡ�����ޤ���\n\n";
    } else {
        $message .= "̤���Ϥ� {$rows_noinput} �濫��ޤ���\n";
        $message .= "------------------------------------------------------------------\n";
        for( $n=0; $n<$rows_noinput; $n++ ) {
            $week   = array(' (��)',' (��)',' (��)',' (��)',' (��)',' (��)',' (��)');
            $date   = $res_noinput[$n][0];
            $day_no = date('w', strtotime($date));
            $date   = $res_noinput[$n][0] . $week[$day_no];
            $message .= "���������{$date}\t����̾��{$res_noinput[$n][1]}\n";
        }
        $message .= "------------------------------------------------------------------\n";
        $message .= "���Ϥ���褦Ϣ���Ʋ�������\n\n";
    }
/**    
    if( $rows_noadmit <= 0 ) {
        $message .= "����ǧ�Ԥ�������ޤ���\n\n";
    } else {
        $message .= "����ǧ�Ԥ���{$rows_noadmit} �濫��ޤ�����ǧ�����򤪴ꤤ���ޤ���\n\n";
        // ��ǧ�ڡ����Υ��ɥ쥹(Uid)��ɽ��������å��Ǿ�ǧ�ڡ�����
        $message .= "http://masterst/per_appli/over_time_work_report/over_time_work_report_Main.php?calUid={$res_list[$r][0]}&showMenu=Judge&select_radio=3\n\n";
    }
/**/    
    $message .= "�ʾ塣";
    
    if (mb_send_mail($to_addres, $attenSubject, $message, $add_head)) {
        // ���ʼԤؤΥ᡼�������������¸
        //$this->setAttendanceMailHistory($serial_no, $atten[$i]);
    }
    ///// Debug
    //if ($cancel) {
    //    if ($i == 0) mb_send_mail('tnksys@nitto-kohki.co.jp', $attenSubject, $message, $add_head);
    //}
}

/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, "commit");
// echo $query . "\n";
fclose($fpa);      ////// �����ѥ�����߽�λ
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߽�λ

exit();

?>
