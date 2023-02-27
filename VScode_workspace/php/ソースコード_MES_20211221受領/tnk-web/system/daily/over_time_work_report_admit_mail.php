#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ����ֳ���ȿ���ǧ�Ԥ�����᡼������ cron.d tnk_daily �����Ǽ¹�       //
// �����λ�����˥᡼������ ������ ��ǧ�Ԥ�����������ǧ�Ԥ�����᡼��     //
// Copyright (C) 2021-2021 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2021/10/06 Created   over_time_work_report_admit_mail.php                //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
//ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');    ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤǥ����ץ�
fwrite($fpb, "����ֳ���ȿ���ǧ�Ԥ�����᡼������\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/over_time_work_report_admit_mail.php\n");
echo "/home/www/html/tnk-web/system/daily/over_time_work_report_admit_mail.php\n";

if ($con = db_connect()) {
    query_affected_trans($con, "begin");
} else {
    echo "$log_date ����ֳ���ȿ���ǧ�Ԥ�����᡼������ db_connect() error \n";
    fwrite($fpa,"$log_date ����ֳ���ȿ���ǧ�Ԥ�����᡼������ db_connect() error \n");
    fwrite($fpb,"$log_date ����ֳ���ȿ���ǧ�Ԥ�����᡼������ db_connect() error \n");
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
            SELECT          uid, ct.act_id, ud.pid, trim(name)
            FROM            user_detailes   AS ud
            LEFT OUTER JOIN cd_table        AS ct   USING(uid)
            WHERE           retire_date IS NULL AND $where
         ";
$res_list = array();
if( ($rows_list = getResult($query, $res_list)) <= 0) exit(); // �����ԲĤʤ齪λ��

for( $r=0; $r<$rows_list; $r++ ) {
    $bu_act = 0;    // �����
    // ������
    $where = "WHERE yo_ad_rt!='-1' AND ";
    if( $res_list[$r][1] == 600 ) {  // ����Ĺ
        if( $res_list[$r][2] == 95 ) {  // ������Ĺ
            $res_list[$r][1] = 582; // ��¤����act_id���åȡ����Ƚ�Ǥ���ݤ˻��ѡ�
            $where .= "yo_ad_st=1 AND yo_ad_bu='m' AND (deploy='��¤�� ��¤����' OR deploy='��¤�� ��¤����')";
        } else {
            $where .= "yo_ad_st=2 AND yo_ad_ko='m' AND (deploy IS NOT NULL)";
        }
    } else if( $res_list[$r][1] == 610 ) {   // ������
        $where .= "yo_ad_st=1 AND yo_ad_bu='m' AND (deploy='��̳��' OR deploy='���ʴ�����')";
    } else if( $res_list[$r][1] == 605 || $res_list[$r][1] == 650 || $res_list[$r][1] == 651 || $res_list[$r][1] == 660 ) { // �ɣӣϻ�̳�� ������ ��̳�� ��̳ ��̳
        $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='��̳��')";
        $bu_act = 610;
    } else if( $res_list[$r][1] == 670 ) {   // ������ ���ʴ�����
        $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='���ʴ�����')";
        $bu_act = 610;
    } else if( $res_list[$r][1] == 501 ) {   // ������
        $where .= "yo_ad_st=1 AND yo_ad_bu='m' AND (deploy='�ʼ��ݾڲ�' OR deploy='���Ѳ�')";
    } else if( $res_list[$r][1] == 174 || $res_list[$r][1] == 517 || $res_list[$r][1] == 537 || $res_list[$r][1] == 581 ) { // ������ �ʼ�������
        $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='�ʼ��ݾڲ�')";
        $bu_act = 501;
    } else if( $res_list[$r][1] == 173 || $res_list[$r][1] == 515 || $res_list[$r][1] == 535 ) { // ������ ���Ѳ�
        $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='���Ѳ�')";
        $bu_act = 501;
    } else if( $res_list[$r][1] == 582 ) { // ��¤��
        $where .= "yo_ad_st=1 AND yo_ad_bu='m' AND (deploy='��¤�� ��¤����' OR deploy='��¤�� ��¤����')";
    } else if( $res_list[$r][1] == 518 || $res_list[$r][1] == 519 || $res_list[$r][1] == 556 || $res_list[$r][1] == 520 ) { // ��¤�� ��¤����
        $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='��¤�� ��¤����')";
        $bu_act = 582;
    } else if( $res_list[$r][1] == 547 || $res_list[$r][1] == 528 || $res_list[$r][1] == 527 ) { // ��¤�� ��¤����
        $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='��¤�� ��¤����')";
        $bu_act = 582;
    } else if( $res_list[$r][1] == 500 ) { // ������
        $where .= "yo_ad_st=1 AND yo_ad_bu='m' AND (deploy='���������� �ײ衦���㷸' OR deploy='���������� ��෸' OR deploy='���ץ���Ω�� ɸ�෸�ͣ�' OR deploy='���ץ���Ω�� ɸ�෸�ȣ�' OR deploy='���ץ���Ω�� ����' OR deploy='��˥���Ω��')";
    } else if( $res_list[$r][1] == 545 || $res_list[$r][1] == 512 || $res_list[$r][1] == 532 || $res_list[$r][1] == 513|| $res_list[$r][1] == 533 || $res_list[$r][1] == 514 || $res_list[$r][1] == 534 ) { // ������ ����������
        $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='���������� �ײ衦���㷸' OR deploy='���������� ��෸')";
        $bu_act = 500;
    } else if( $res_list[$r][1] == 176 || $res_list[$r][1] == 522 || $res_list[$r][1] == 523 || $res_list[$r][1] == 525 ) { // ������ ���ץ���Ω��
        $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='���ץ���Ω�� ɸ�෸�ͣ�' OR deploy='���ץ���Ω�� ɸ�෸�ȣ�' OR deploy='���ץ���Ω�� ����')";
        $bu_act = 500;
    } else if( $res_list[$r][1] == 551 || $res_list[$r][1] == 175 || $res_list[$r][1] == 572 ) { // ������ ��˥���Ω��
        $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='��˥���Ω��')";
        $bu_act = 500;
    } else {
        $where .= "(deploy IS NULL)";   // ���顼
    }
    // ��ǧ�Ԥ��������
    $query = "SELECT DISTINCT date, deploy FROM over_time_report $where";
    $res_count = array();
    $rows_ken  = getResult($query, $res_count);
    
    if( $rows_ken <= 0 ) continue; // ��ǧ�Ԥ�̵���ʤ鼡��
    
    // �Ժߥ����å�����
    $superiors = false;         // ��Ĺ���Υե饰�ʽ������
    $date = date('Ymd');        // ���������ռ���
    $uid = $res_list[$r][0];    // ���Ȥ�UID
    $query = "
                SELECT uid FROM working_hours_report_data_new
                WHERE  uid='$uid' AND working_date='$date' AND (absence!='00' OR str_time='0000' OR end_time!='0000')
             ";
    $res = array();
    if( getResult2($query, $res) > 0 && $res_list[$r][2] != 110 ) {
        $kojyo = false;     // ����Ĺ���Υե饰�ʽ������
        if( $res_list[$r][2]==46 || $res_list[$r][2]==50 ) {
            // ��Ĺ�ˤʤ�Τǡ���Ĺ�γ�ǧ���Ժߤʤ鹩��Ĺ�ޤ�
            for( $n=0; $n<$rows_list; $n++ ) {
                if( $res_list[$n][1] == $bu_act ) {
                    $uid = $res_list[$n][0];
                    break; // ���Ȥ���Ĺ �ޤ�
                }
            }
            $query = "
                        SELECT uid FROM working_hours_report_data_new
                        WHERE  uid='$uid' AND working_date='$date' AND (absence!='00' OR str_time='0000' OR end_time!='0000')
                     ";
            $res = array();
            if( getResult2($query, $res) <= 0 ) {
                $superiors = true;  // ��Ĺ���Υե饰��ON��
            } else {
                $kojyo = true;  // ����Ĺ���Υե饰��ON��
            }
        } else {
            $kojyo = true;  // ����Ĺ���Υե饰��ON��
        }
        // ����Ĺ�����å�
        if( $kojyo ) {
            for( $n=0; $n<$rows_list; $n++ ) {
                if( $res_list[$n][1] == 600 ) {
                    $uid = $res_list[$n][0];
                    break; // ����Ĺ �ޤ�
                }
            }
            $query = "
                        SELECT uid FROM working_hours_report_data_new
                        WHERE  uid='$uid' AND working_date='$date' AND (absence!='00' OR str_time='0000' OR end_time!='0000')
                     ";
            $res = array();
            if( getResult2($query, $res) <= 0 ) {
                $superiors = true;  // ��Ĺ���Υե饰��ON��
            }
        }
    }
    
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
//    if( $superiors ) $sendna = $res_list[$n][3];    // ̾�� �����ѹ� ����꡼�����ϡ������Ȳ�
    if( $superiors ) {
        $attenSubject = "{$sendna} �� ���Ժ�̤��ǧ�� ����ֳ���ȿ����ꤪ�Τ餻"; // ���衧 
    } else {
        $attenSubject = "{$sendna} �� ��̤��ǧ�� ����ֳ���ȿ���ꤪ�Τ餻";
    }
    $message = "{$sendna} ��\n\n";
    if( $superiors ) {
        $message .= "{$res_list[$r][3]} �� �Ժߤΰ١������\n\n";
        $message .= "����ֳ���ȿ���ʻ��������˾�ǧ�����򤪴ꤤ���ޤ���\n\n";
        $message .= "http://masterst/per_appli/over_time_work_report/over_time_work_report_Main.php?calUid={$uid}&showMenu=Judge&select_radio=2\n\n";
    } else {
        $message .= "����ֳ���ȿ���ʻ���������";
        if( $rows_ken <= 0 ) {
            $message .= "��ǧ�Ԥ��Ϥ���ޤ���\n\n";
        } else {
            $message .= "��ǧ�Ԥ��� {$rows_ken} �濫��ޤ���\n\n";
            $message .= "��ǧ�����򤪴ꤤ���ޤ���\n\n";
            // ��ǧ�ڡ����Υ��ɥ쥹(Uid)��ɽ��������å��Ǿ�ǧ�ڡ�����
            $message .= "http://masterst/per_appli/over_time_work_report/over_time_work_report_Main.php?calUid={$res_list[$r][0]}&showMenu=Judge\n\n";
        }
    }
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
