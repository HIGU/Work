#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ���� ��ư���������ƥ�2 �� to DataBase cgi-cli�� FWServer 1.30�б�      //
// Copyright (C) 2003-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/07/02 Created  equip_auto_log2data_ftp.php                          //
// 2003/07/03 CLI�Ǥ�Function��ͭ���뤿��require_once()�����л�����ѹ�   //
// 2004/01/30 �ơ��֥��equip_work_log2 ���ѹ��������� ���å����б������� //
// 2004/02/10 FWServer �� V1.30�� nfs �����ѤǤ��ʤ����� FTP �����ؤ���     //
//            equip_auto_log2data_cmd.php �� equip_auto_log2data_ftp ���ѹ� //
// 2004/03/04 BCD�ǡ�����̵������̵�����Ÿ�off=0�Υ��å����ɲ� $state //
//            csv_flg != 1 �� csv_flg = 3 ���ѹ��� FWS=2 �ȶ��̤���         //
// 2004/03/11 rollback��exit()�򥳥��ȥ����ȣ���Υǡ�����NG��¾��OK���б�//
//            $ftp_flg ���դ��ƣ��椬�ΣǤǤ⽪λ���ʤ��褦���ѹ�           //
// 2004/03/15 Counter���ʤ���˼�ư��̵�ͤ��Υ����å��򤷰㤦���ϼ�ư�� //
//            ʪ������μ���������ʤΤ�state����ȿ����㤤���Ǥ��ǽ����   //
//            ���뤿�ᾭ��Ū�ˤ�̵����������                                //
// 2004/03/25 ftp��login�˼��Ԥ��뤳�Ȥ�����(fwserver2�Τ�)�Τ��н� sleep   //
//              2004-03-17 19:16:01 fwserver2��FTP��login�˼��Ԥ��ޤ�����   //
//            �ƥ���Ū�� fwserver2��login��ʬ�� sleep(10)���ɲä���ȥ饤   //
// 2004/03/29 @ftp_login()�ˤ��ư�ȯ�ܤΥ��顼��å����������������롣      //
//            Counter�ν񤭹��ߤ˼��Ԥ� debug ʸ���ɲ� $mac_state $query    //
//            FWS1����=�Ÿ�ON/OFF����ʤ� FWS2����=�Ÿ�ON/OFF������ɲ�     //
// 2004/04/01 �嵭�� ftp �ط��Υ��顼�� php-4.3.5 �ΥХ��Ǥ��ä���4.3.6��OK //
// 2004/06/21 4.3.6-cgi �� 4.3.7-cgi ���ѹ�  Netmoni���������ߴ�λ        //
// 2004/07/12 state_check_netmoni() Netmoni & FWS ���������� �����å�����   //
// 2004/07/15 Netmoni�����פε�����ʪ������� equip_mac_state_log2 �˼���� //
// 2004/07/26 equip_mac_state_log2�ν���ߥ��顼log���Ƥ��ѹ�$query�����   //
//            FWS1/2 ���ν���߻��˥إå���������������å�����UPDATE���� //
// 2004/07/27 equip_mac_state_log2�ϲ�ư��ˤ�����餺24���ֵ�Ͽ���ѹ�      //
// 2004/08/23 substr(microtime(), 2, 6)�򥳥��Ȥˤ�date('Ymd His')���ѹ�  //
//            Netmoni�������Ÿ�OFF�к���work_cnt������Υǡ�������Ѥ����ѹ�//
// 2004/10/08 Netmoni4�Ÿ�OFF���ä�����Ÿ�OFFor���Ǥλ�work_cnt��ݻ�����//
// 2004/11/29 FWS3��5���ɲ�(1����)                                          //
// 2004/12/13 #!/usr/local/bin/php-5.0.2-cli �� php (������5.0.3RC2)���ѹ�  //
// 2005/02/15 sleep(10) �� sleep(2) ���ѹ� ����û�̤Τ��ᡣ�嵭�ϸ��� 5.0.3 //
// 2005/02/16 state_check_netmoni() �ѥ�᡼�������ɲ� $ftp_con(���ȥ꡼��) //
// 2005/02/17 cron�Ǽ¹ԤʤΤ�¾�Υץ�����٤��θ���ƣ������ٱ䤹�롣    //
// 2005/02/21 ���ż¹��ɻߤΤ�������å��ѥե��������å����ȹ�          //
// 2005/02/25 FTP�Ǽ��Ԥ������ϣ����Ԥĥ��å��ϰ�̣��̵���ΤǺ��1��Τ�//
// 2005/05/10 SQL��select equip_work_log2 �� equip_index()�����ƻ��Ѥ���    //
//                 equip_mac_state_log2 �� equip_index2()�����ƻ��Ѥ���     //
// 2005/10/07 Netmoni��FTP��ΰ���ե����뤬���󲿤餫�Υȥ�֥�Ǻ���Ǥ���//
//            �˻ĤäƤ�������б����ɲ� 952(������:����ε����ե�����) //
// 2006/03/03 �ؼ��ֹ椬�Ѥ�ä����ν�����߾����ѹ�(�μ¤�0�ˤ���SQLʸ)//
// 2007/06/15 Web�δ�����˥塼���鼫ư�����������椹�뤿����å��ɲ�   //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
ini_set('max_execution_time', 30);          // ����¹Ի���=30�� CLI�Ǥ�ɬ�פʤ������
require_once ('/home/www/html/tnk-web/equip/equip_function.php');

$check_file    = '/home/www/html/tnk-web/equip/check_file';
$auto_log_stop = '/home/www/html/tnk-web/equip/equip_auto_log_stop';    // 2007/06/15 ADD
if (file_exists($check_file)) {
    exit(); // ���Υץ�������λ���Ƥ��ʤ��Τǥ���󥻥�
} elseif (file_exists($auto_log_stop)) {
    exit(); // ��ư�������Υ��ȥå׻ؼ��Τ��Ὢλ 2007/06/15 ADD
} else {
    fopen($check_file, 'a');    // �����å��ѥե���������
}
sleep(8);      // cron�Ǽ¹ԤʤΤ�¾�Υץ�����٤��θ���ƣ������ٱ䤹�롣2007/06/15 10��8��

/****************************** FWS1���� *********************************/
$ftp_flg = true;
////////// FTP CONNECT
if ( !($ftp_con = ftp_connect(FWS1)) ) {   // �ƥ����Ѥ˥�ƥ��ɽ��
    $error_msg = date('Y-m-d H:i:s ') . "fwserver1��FTP����³�˼��Ԥ��ޤ�����";
    `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
    $ftp_flg = false;
} else {
    if (!@ftp_login($ftp_con, FWS_USER, FWS_PASS)) {      // 2004/03/29 ���顼��å�����������
        $error_msg = date('Y-m-d H:i:s ') . "FWS1��FTP��login�˼��Ԥ��ޤ�����";
        `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
        $ftp_flg = false;
    }
}

if ($ftp_flg) {
////////// ��ư��˴ط��ʤ� �����ޥ������ƻ뤹�뵡���ֹ����� (ʪ�����֤�24���ִƻ뤹�뤿��)
$query = "select mac_no, csv_flg from equip_machine_master2 where csv_flg = 2 and survey = 'Y'";
                        // Netmoni=1 fwserver1=2 fwserver2=3 fwserver3=4 fwserver4=5 ... , Net&fws1=101
$res_key = array();
if ( ($rows_key = getResult($query, $res_key)) >= 1) {      // �쥳���ɤ����ʾ夢���
    for ($i=0; $i<$rows_key; $i++) {                        // ���쥳���ɤ��Ľ���
        ///// insert �� �ѿ� �����
        $mac_no   = $res_key[$i]['mac_no'];
        $csv_flg  = $res_key[$i]['csv_flg'];
        /////////// FTP��Υե������¸�ߥ����å�
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}_work_state.log") != -1) {
            /////////// FTP rename
            if (ftp_rename($ftp_con, "/home/fws/usr/{$mac_no}_work_state.log", "/home/fws/usr/{$mac_no}_work_state.tmp")) {
                /////////// FTP Download
                if (ftp_get($ftp_con, "/home/fws/{$mac_no}_work_state.log", "/home/fws/usr/{$mac_no}_work_state.tmp", FTP_ASCII)) {
                    // echo 'FTP��Download���������ޤ�����';
                    ftp_delete($ftp_con, "/home/fws/usr/{$mac_no}_work_state.tmp");  // ��ե�����Ϻ��
                } else {
                    // echo 'FTP��Download�˼��Ԥ��ޤ�����';
                    `/bin/rm -f /home/fws/{$mac_no}_work_state.log`;
                }
            } else {
                // echo 'FTP��rename�˼��Ԥ��ޤ�����';
                `/bin/rm -f /home/fws/{$mac_no}_work_state.log`;
            }
        }
        /////////// begin �ȥ�󥶥�����󳫻�
        if ($con = db_connect()) {
            query_affected_trans($con, 'begin');
            ///// State Log File Check        ���ߤξ���(��ž�桦�����)����������� �������Ÿ�OFF�����ͽ��
            $state_file = "/home/fws/{$mac_no}_work_state.log";     // �ǥ��쥯�ȥꡦ�ե�����̾����
            $state_temp = "/home/fws/{$mac_no}_work_state.tmp";     // Rename �ѥǥ��쥯�ȥꡦ�ե�����̾����
            if (file_exists($state_file)) {                         // State Log File �������
                if (rename($state_file, $state_temp)) {
                    $fp = fopen ($state_temp,'r');
                    $row  = 0;                                  // ���쥳����
                    $data = array();                            // ǯ����,����,�ù���
                    while ($data[$row] = fgetcsv ($fp, 50, ',')) {
                        if ($data[$row][0] != '') {
                            $row++;
                        }
                    }
                    for ($j=0; $j<$row; $j++) {         // Status File �˥쥳���ɤ�����о��֤�����������
                        if ($data[$j][2] == 'auto') {   // �ե����뤫��ʪ�������ֹ�����
                            $state_p = 1;               // ��ž��(��ư��ž)
                        } elseif ($data[$j][2] == 'stop') {
                            $state_p = 3;               // �����
                        } elseif ($data[$j][2] == 'on') {
                            $state_p = 3;               // �Ÿ�ON�ξ���Default�ͤ������=3
                        } else {
                            $state_p = 0;               // �Ÿ�OFF "off" ��ͽ��
                        }
                        $date_time = $data[$j][0] . " " . $data[$j][1];     // timestamp�����ѿ����� ���
                        $state_name = equip_machine_state($mac_no, $state_p, $txt_color, $bg_color);
                        $query = "insert into equip_mac_state_log2
                            (mac_no, state, date_time, state_name, state_type)
                            values($mac_no, $state_p, '$date_time', '$state_name', $csv_flg)";
                        if (query_affected_trans($con, $query) <= 0) {
                            $temp_msg = "$query\n date_time:$date_time mac_no:$mac_no state:$state_p j=$j";
                            `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                            // query_affected_trans($con, "rollback");         // transaction rollback
                            // exit();
                        }
                    }
                    unlink($state_temp);        // ����ե�����κ��
                } else {
                    echo "���ơ������ե������ rename() �˼���\n";
                }
            }
            ///// State Log File ������λ
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, 'commit');
        } else {
            echo "�ǡ����١�������³1�˼���\n";
            exit();
        }
    }
}
////////// ��ư��ε������إå����ե����뤫����� & �����ޥ��������鵡��̾�����
$query = "select mac_no, siji_no, koutei, parts_no, plan_cnt, mac_name, csv_flg from equip_work_log2_header
        left outer join equip_machine_master2 using(mac_no)
        where work_flg is TRUE and csv_flg = 2 and survey = 'Y'
";      // Netmoni=1&101 fwserver1=2 fwserver2=3 fwserver3=4 fwserver4=5 fwserver5=6 ...
$res_key = array();
if ( ($rows_key = getResult($query, $res_key)) >= 1) {      // �쥳���ɤ����ʾ夢���
    for ($i=0; $i<$rows_key; $i++) {                        // ���쥳���ɤ��Ľ���
        ///// insert �� �ѿ� �����
        $mac_no   = $res_key[$i]['mac_no'];
        $siji_no  = $res_key[$i]['siji_no'];
        $parts_no = $res_key[$i]['parts_no'];
        $koutei   = $res_key[$i]['koutei'];
        $plan_cnt = $res_key[$i]['plan_cnt'];
        $mac_name = $res_key[$i]['mac_name'];
        $csv_flg  = $res_key[$i]['csv_flg'];
        /////////// FTP��Υե������¸�ߥ����å�
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd1") != -1) {
            /////////// FTP Download
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd1", "/home/fws/usr/{$mac_no}-bcd1", FTP_ASCII)) {
                // echo 'FTP��Download�˼��Ԥ��ޤ�����';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd1")) {   // ��ե����뤬����к��
                unlink("/home/fws/{$mac_no}-bcd1");
            }
        }
        /////////// FTP��Υե������¸�ߥ����å�
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd2") != -1) {
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd2", "/home/fws/usr/{$mac_no}-bcd2", FTP_ASCII)) {
                // echo 'FTP��Download�˼��Ԥ��ޤ�����';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd2")) {   // ��ե����뤬����к��
                unlink("/home/fws/{$mac_no}-bcd2");
            }
        }
        /////////// FTP��Υե������¸�ߥ����å�
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd4") != -1) {
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd4", "/home/fws/usr/{$mac_no}-bcd4", FTP_ASCII)) {
                // echo 'FTP��Download�˼��Ԥ��ޤ�����';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd4")) {   // ��ե����뤬����к��
                unlink("/home/fws/{$mac_no}-bcd4");
            }
        }
        /////////// FTP��Υե������¸�ߥ����å�
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd8") != -1) {
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd8", "/home/fws/usr/{$mac_no}-bcd8", FTP_ASCII)) {
                // echo 'FTP��Download�˼��Ԥ��ޤ�����';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd8")) {   // ��ե����뤬����к��
                unlink("/home/fws/{$mac_no}-bcd8");
            }
        }
        ///// State File Check BCD�黻  ���ߤξ��֤����
        $bcd1_file = "/home/fws/{$mac_no}-bcd1";            // BCD�黻�ѥե�����̾����
        $bcd2_file = "/home/fws/{$mac_no}-bcd2";
        $bcd4_file = "/home/fws/{$mac_no}-bcd4";
        $bcd8_file = "/home/fws/{$mac_no}-bcd8";
        $state_bcd = 0;                                     // �����
        if (file_exists($bcd1_file)) {
            $state_bcd += 1;
        }
        if (file_exists($bcd2_file)) {
            $state_bcd += 2;
        }
        if (file_exists($bcd4_file)) {
            $state_bcd += 4;
        }
        if (file_exists($bcd8_file)) {
            $state_bcd += 8;
        }
        $query = "select state from equip_mac_state_log2 where equip_index2(mac_no,date_time) < '{$mac_no}99999999999999' order by equip_index2(mac_no,date_time) DESC limit 1";
        if (getUniResult($query, $state_p) > 0) {
            // ʪ�����椬���åȤ���Ƥ����
            $state = state_check($state_p, $state_bcd);   // ʪ�����ֿ���ȥ����å��ξ��֤Ȥ�Ŭ���ͤ�����å�
        } else {
            $state = 0;     // ���֥ǡ�����̵���Τ�̵�����Ÿ�off=0
        }
        /////////// FTP��Υե������¸�ߥ����å�
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}_work_cnt.log") != -1) {
            /////////// FTP rename
            if (ftp_rename($ftp_con, "/home/fws/usr/{$mac_no}_work_cnt.log", "/home/fws/usr/{$mac_no}_work_cnt.tmp")) {
                /////////// FTP Download
                if (ftp_get($ftp_con, "/home/fws/{$mac_no}_work_cnt.log", "/home/fws/usr/{$mac_no}_work_cnt.tmp", FTP_ASCII)) {
                    // echo 'FTP��Download���������ޤ�����';
                    ftp_delete($ftp_con, "/home/fws/usr/{$mac_no}_work_cnt.tmp");  // ��ե�����Ϻ��
                } else {
                    // echo 'FTP��Download�˼��Ԥ��ޤ�����';
                    `/bin/rm -f /home/fws/{$mac_no}_work_cnt.log`;
                }
            } else {
                // echo 'FTP��rename�˼��Ԥ��ޤ�����';
                `/bin/rm -f /home/fws/{$mac_no}_work_cnt.log`;
            }
        }
        /////////// begin �ȥ�󥶥�����󳫻�
        if ($con = db_connect()) {
            query_affected_trans($con, 'begin');
            ///// Counter File Check        ���ߤβù��������������
            $cnt_file = "/home/fws/{$mac_no}_work_cnt.log";     // �ǥ��쥯�ȥꡦ�ե�����̾����
            $cnt_temp = "/home/fws/{$mac_no}_work_cnt.tmp";     // Rename �ѥǥ��쥯�ȥꡦ�ե�����̾����
            if (file_exists($cnt_file)) {                       // Counter File �������
                if (rename($cnt_file, $cnt_temp)) {
                    $fp = fopen ($cnt_temp,'r');
                    $row  = 0;                                  // ���쥳����
                    $data = array();                            // ǯ����,����,�ù���
                    while ($data[$row] = fgetcsv ($fp, 50, ',')) {
                        if ($data[$row][0] != '') {
                            $row++;
                        }
                    }
                    if ($row >= 1) {            // Counter File �˥쥳���ɤ�����о��֤Ȳù��������
                        ///// ���ߤΥǡ����١����κǿ��쥳���ɤ������
                        $query = "
                            SELECT mac_state, work_cnt FROM equip_work_log2
                            WHERE
                                equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                                AND
                                equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                            ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC limit 1
                        ";
                        $res = array();
                        if ( ($rows = getResult($query, $res)) >= 1) {  // ����βù����˥ץ饹���ƽ����
                            for ($j=0; $j<$row; $j++) {
                                $work_cnt  = $res[0]['work_cnt'] + ($j + 1);      // Counter UP
                                $date_time = $data[$j][0] . ' ' . $data[$j][1];     // PostgreSQL��TIMESTAMP�����ѹ�
                                if ( ($state == 1) || ($state == 8) || ($state == 5) ) {     // ��ư��ž��̵�ͱ�ž�����ʼ���ΤϤ��ʤΤǥ����å�����ɬ�פʤ�����
                                    $mac_state = $state;
                                } else {
                                    $mac_state = 1;     // Counter���ʤ�Ǥ���Τ˼�ư�Ǥ�̵�������ʼ�Ǥ�ʤ����϶���Ū�˼�ư��ž�ˤ���
                                }
                                $query = "insert into equip_work_log2
                                    (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                    values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                                if (query_affected_trans($con, $query) <= 0) {
                                    $temp_msg = "FWS1 Counter�ν񤭹��ߤ˼��� �쥳����:$j : $mac_no : $date_time : $mac_state \n";
                                    $temp_msg .= $query;    // debug ��
                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    // query_affected_trans($con, 'rollback');         // transaction rollback
                                    // exit();
                                }
                            }
                        } else {                    // �ǡ����١��������Τ���̵���˽����
                            for ($j=0; $j<$row; $j++) {
                                $work_cnt  =  ($j + 1);   // ���ξ��Ϥ������㤦
                                $date_time = $data[$j][0] . ' ' . $data[$j][1];     // PostgreSQL��TIMESTAMP�����ѹ�
                                // $mac_state = $state;     // ���ξ��ϲ��Υǡ����������ǽ�����⤤����ʲ���ɬ��
                                if ( ($state == 1) || ($state == 8) || ($state == 5) ) {     // ��ư��ž��̵�ͱ�ž�����ʼ���ΤϤ��ʤΤǥ����å�����ɬ�פʤ�����
                                    $mac_state = $state;
                                } else {
                                    $mac_state = 1;     // Counter���ʤ�Ǥ���Τ˼�ư�Ǥ�̵�������ʼ�Ǥ�ʤ����϶���Ū�˼�ư��ž�ˤ���
                                }
                                $query = "insert into equip_work_log2
                                    (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                    values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                                if (query_affected_trans($con, $query) <= 0) {
                                    $temp_msg = "fwserver1 ���ǡ����١����ν񤭹��ߤ˼��� �쥳����:$j\n$query";
                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    // query_affected_trans($con, 'rollback');         // transaction rollback
                                    // exit();
                                } else {
                                    $query = "select str_timestamp from equip_work_log2_header
                                            where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
                                    ";
                                    if (getUniResTrs($con, $query, $str_timestamp) > 0) {
                                        $query = "select case when cast('$date_time' as TIMESTAMP) < cast('$str_timestamp' as TIMESTAMP)
                                                then (select 1) else (select 0) end";
                                        if (getUniResTrs($con, $query, $check_time) > 0) {
                                            if ($check_time == 1) {
                                                $query = "update equip_work_log2_header set str_timestamp='{$date_time}'
                                                        where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                                                if (query_affected_trans($con, $query) <= 0) {
                                                    $temp_msg = "���ǡ�����������Ӥ�Header��UPDATE�˼��� mac_no:{$mac_no}";
                                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } else {                    // Counter File �˥쥳���ɤ��ʤ��ΤǾ��֤Τߥ����å��������
                        ///// ���ߤΥǡ����١����κǿ��쥳���ɤ������
                        $query = "
                            SELECT mac_state, work_cnt FROM equip_work_log2
                            WHERE
                                equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                                AND
                                equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                            ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC limit 1
                        ";
                        $res = array();
                        if ( ($rows = getResult($query, $res)) >= 1) {  // �쥳���ɤ�����о��֤�����å�����
                            if ($res[0]['mac_state'] != $state) {       // ���֤��㤨�н����
                                $work_cnt  = $res[0]['work_cnt'];       // ����βù�����Ȥ�
                                // $date_time  = date('Ymd His.');             // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                                // $date_time .= substr(microtime(), 2, 6);    // TIMESTAMP(6)�ǽ�ʣ���������
                                $date_time  = date('Ymd His');          // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                                $mac_state = $state;
                                $query = "insert into equip_work_log2
                                    (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                    values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                                if (query_affected_trans($con, $query) <= 0) {
                                    $temp_msg = "fwserver1 Counter File �����뤬�쥳���ɤ�̵�����Υǡ����١����ν񤭹��ߤ˼��� mac_no={$mac_no}";
                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    // query_affected_trans($con, 'rollback');         // transaction rollback
                                    // exit();
                                }
                            }
                        } else {            // ���Τ���̵���˽����
                            $work_cnt  = 0;             // ���ξ��ϣ�
                            $date_time = date('Ymd His');   // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                            $mac_state = $state;
                            $query = "insert into equip_work_log2
                                (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                            if (query_affected_trans($con, $query) <= 0) {
                                $temp_msg = "fwserver1 Counter File �����뤬�쥳���ɤ�̵�����ν��ǡ����١����ν񤭹��ߤ˼��� mac_no={$mac_no}";
                                `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                // query_affected_trans($con, 'rollback');         // transaction rollback
                                // exit();
                            } else {
                                $query = "select str_timestamp from equip_work_log2_header
                                        where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
                                ";
                                if (getUniResTrs($con, $query, $str_timestamp) > 0) {
                                    $query = "select case when cast('$date_time' as TIMESTAMP) < cast('$str_timestamp' as TIMESTAMP)
                                            then (select 1) else (select 0) end";
                                    if (getUniResTrs($con, $query, $check_time) > 0) {
                                        if ($check_time == 1) {
                                            $query = "update equip_work_log2_header set str_timestamp='{$date_time}'
                                                    where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                                            if (query_affected_trans($con, $query) <= 0) {
                                                $temp_msg = "���ǡ�����������Ӥ�Header��UPDATE�˼��� mac_no:{$mac_no}";
                                                `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    unlink($cnt_temp);      // ����ե�����κ��
                } else {
                    echo "�����󥿡��ե������ rename() �˼���\n";
                }
            } else {                    // Counter File ���ʤ��ΤǾ��֤Τ߽����
                ///// ���ߤΥǡ����١����κǿ��쥳���ɤ������
                $query = "
                    SELECT mac_state, work_cnt FROM equip_work_log2
                    WHERE
                        equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                        AND
                        equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                    ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC limit 1
                ";
                $res = array();
                if ( ($rows = getResult($query, $res)) >= 1) {  // �쥳���ɤ�����о��֤�����å�����
                    if ($res[0]['mac_state'] != $state) {       // ���֤��㤨�н����
                        $work_cnt  = $res[0]['work_cnt'];       // ����βù����򤽤Τޤ޻Ȥ�
                        // $date_time  = date('Ymd His.');             // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                        // $date_time .= substr(microtime(), 2, 6);    // TIMESTAMP(6)�ǽ�ʣ���������
                        $date_time  = date('Ymd His');          // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                        $mac_state = $state;
                        $query = "insert into equip_work_log2
                            (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                            values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                        if (query_affected_trans($con, $query) <= 0) {
                            $temp_msg = date('Y-m-d H:i:s ') . "fwserver1 equip_work_log2 �ؾ��ֽ���ߤ˼���";
                            `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                            // query_affected_trans($con, 'rollback');         // transaction rollback
                            // exit();
                        }
                    }
                } else {        // ���Τ���̵���˽����
                    $work_cnt  = 0;             // ���ξ��ϣ�
                    $date_time = date('Ymd His');       // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                    $mac_state = $state;
                    $query = "insert into equip_work_log2
                        (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                        values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                    if (query_affected_trans($con, $query) <= 0) {
                        $temp_msg = date('Y-m-d H:i:s ') . "fwserver1 equip_work_log2 �ؽ��ξ��ֽ���ߤ˼���";
                        `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                        // query_affected_trans($con, 'rollback');         // transaction rollback
                        // exit();
                    } else {
                        $query = "select str_timestamp from equip_work_log2_header
                                where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
                        ";
                        if (getUniResTrs($con, $query, $str_timestamp) > 0) {
                            $query = "select case when cast('$date_time' as TIMESTAMP) < cast('$str_timestamp' as TIMESTAMP)
                                    then (select 1) else (select 0) end";
                            if (getUniResTrs($con, $query, $check_time) > 0) {
                                if ($check_time == 1) {
                                    $query = "update equip_work_log2_header set str_timestamp='{$date_time}'
                                            where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                                    if (query_affected_trans($con, $query) <= 0) {
                                        $temp_msg = "���ǡ�����������Ӥ�Header��UPDATE�˼��� mac_no:{$mac_no}";
                                        `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, 'commit');
        } else {
            echo "�ǡ����١�������³1�˼���\n";
            exit();
        }
    }
}
////////// FTP Close
if ($ftp_con) {
    ftp_close($ftp_con);
}
}   // $ftp_flg End





/****************************** FWS2���� *********************************/
$ftp_flg = true;
////////// FTP CONNECT
if ( !($ftp_con = ftp_connect(FWS2)) ) {   // �ƥ����Ѥ˥�ƥ��ɽ��
    $error_msg = date('Y-m-d H:i:s ') . "fwserver2��FTP����³�˼��Ԥ��ޤ�����";
    `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
    $ftp_flg = false;
} else {
    if (!@ftp_login($ftp_con, FWS_USER, FWS_PASS)) {      // 2004/03/29 ���顼��å�����������
        $error_msg = date('Y-m-d H:i:s ') . "FWS2��FTP��login�˼��Ԥ��ޤ�����";
        `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
        $ftp_flg = false;
    }
}

if ($ftp_flg) {
////////// ��ư��˴ط��ʤ� �����ޥ������ƻ뤹�뵡���ֹ����� (ʪ�����֤�24���ִƻ뤹�뤿��)
$query = "select mac_no, csv_flg from equip_machine_master2 where csv_flg = 3 and survey = 'Y'";
                        // Netmoni=1 fwserver1=2 fwserver2=3 fwserver3=4 fwserver4=5 ... , Net&fws1=101
$res_key = array();
if ( ($rows_key = getResult($query, $res_key)) >= 1) {      // �쥳���ɤ����ʾ夢���
    for ($i=0; $i<$rows_key; $i++) {                        // ���쥳���ɤ��Ľ���
        ///// insert �� �ѿ� �����
        $mac_no   = $res_key[$i]['mac_no'];
        $csv_flg  = $res_key[$i]['csv_flg'];
        /////////// FTP��Υե������¸�ߥ����å�
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}_work_state.log") != -1) {
            /////////// FTP rename
            if (ftp_rename($ftp_con, "/home/fws/usr/{$mac_no}_work_state.log", "/home/fws/usr/{$mac_no}_work_state.tmp")) {
                /////////// FTP Download
                if (ftp_get($ftp_con, "/home/fws/{$mac_no}_work_state.log", "/home/fws/usr/{$mac_no}_work_state.tmp", FTP_ASCII)) {
                    // echo 'FTP��Download���������ޤ�����';
                    ftp_delete($ftp_con, "/home/fws/usr/{$mac_no}_work_state.tmp");  // ��ե�����Ϻ��
                } else {
                    // echo 'FTP��Download�˼��Ԥ��ޤ�����';
                    `/bin/rm -f /home/fws/{$mac_no}_work_state.log`;
                }
            } else {
                // echo 'FTP��rename�˼��Ԥ��ޤ�����';
                `/bin/rm -f /home/fws/{$mac_no}_work_state.log`;
            }
        }
        /////////// begin �ȥ�󥶥�����󳫻�
        if ($con = db_connect()) {
            query_affected_trans($con, 'begin');
            ///// State Log File Check        ���ߤξ���(��ž�桦�����)����������� �Ÿ�OFF�����OK
            $state_file = "/home/fws/{$mac_no}_work_state.log";     // �ǥ��쥯�ȥꡦ�ե�����̾����
            $state_temp = "/home/fws/{$mac_no}_work_state.tmp";     // Rename �ѥǥ��쥯�ȥꡦ�ե�����̾����
            if (file_exists($state_file)) {                         // State Log File �������
                if (rename($state_file, $state_temp)) {
                    $fp = fopen ($state_temp,'r');
                    $row  = 0;                                  // ���쥳����
                    $data = array();                            // ǯ����,����,�ù���
                    while ($data[$row] = fgetcsv ($fp, 50, ',')) {
                        if ($data[$row][0] != '') {
                            $row++;
                        }
                    }
                    for ($j=0; $j<$row; $j++) {         // Status File �˥쥳���ɤ�����о��֤�����������
                        if ($data[$j][2] == 'auto') {   // �ե����뤫��ʪ�������ֹ�����
                            $state_p = 1;               // ��ž��(��ư��ž)
                        } elseif ($data[$j][2] == 'stop') {
                            $state_p = 3;               // �����
                        } elseif ($data[$j][2] == 'on') {
                            $state_p = 3;               // �Ÿ�ON�ξ���Default�ͤ������=3
                        } else {
                            $state_p = 0;               // �Ÿ�OFF "off" ��ͽ��
                        }
                        $date_time = $data[$j][0] . " " . $data[$j][1];     // timestamp�����ѿ����� ���
                        $state_name = equip_machine_state($mac_no, $state_p, $txt_color, $bg_color);
                        $query = "insert into equip_mac_state_log2
                            (mac_no, state, date_time, state_name, state_type)
                            values($mac_no, $state_p, '$date_time', '$state_name', $csv_flg)";
                        if (query_affected_trans($con, $query) <= 0) {
                            $temp_msg = "$query\n date_time:$date_time mac_no:$mac_no state:$state_p j=$j";
                            `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                            // query_affected_trans($con, "rollback");         // transaction rollback
                            // exit();
                        }
                    }
                    unlink($state_temp);        // ����ե�����κ��
                } else {
                    echo "���ơ������ե������ rename() �˼���\n";
                }
            }
            ///// State Log File ������λ
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, 'commit');
        } else {
            echo "�ǡ����١�������³2�˼���\n";
            exit();
        }
    }
}
////////// ��ư��ε������إå����ե����뤫����� & �����ޥ��������鵡��̾�����
$query = "select mac_no, siji_no, koutei, parts_no, plan_cnt, mac_name, csv_flg from equip_work_log2_header
        left outer join equip_machine_master2 using(mac_no)
        where work_flg is TRUE and csv_flg = 3 and survey = 'Y'
";      // Netmoni=1&101 fwserver1=2 fwserver2=3 fwserver3=4 fwserver4=5 fwserver5=6 ...
$res_key = array();
if ( ($rows_key = getResult($query, $res_key)) >= 1) {      // �쥳���ɤ����ʾ夢���
    for ($i=0; $i<$rows_key; $i++) {                        // ���쥳���ɤ��Ľ���
        ///// insert �� �ѿ� �����
        $mac_no   = $res_key[$i]['mac_no'];
        $siji_no  = $res_key[$i]['siji_no'];
        $parts_no = $res_key[$i]['parts_no'];
        $koutei   = $res_key[$i]['koutei'];
        $plan_cnt = $res_key[$i]['plan_cnt'];
        $mac_name = $res_key[$i]['mac_name'];
        $csv_flg  = $res_key[$i]['csv_flg'];
        /////////// FTP��Υե������¸�ߥ����å�
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd1") != -1) {
            /////////// FTP Download
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd1", "/home/fws/usr/{$mac_no}-bcd1", FTP_ASCII)) {
                // echo 'FTP��Download�˼��Ԥ��ޤ�����';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd1")) {   // ��ե����뤬����к��
                unlink("/home/fws/{$mac_no}-bcd1");
            }
        }
        /////////// FTP��Υե������¸�ߥ����å�
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd2") != -1) {
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd2", "/home/fws/usr/{$mac_no}-bcd2", FTP_ASCII)) {
                // echo 'FTP��Download�˼��Ԥ��ޤ�����';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd2")) {   // ��ե����뤬����к��
                unlink("/home/fws/{$mac_no}-bcd2");
            }
        }
        /////////// FTP��Υե������¸�ߥ����å�
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd4") != -1) {
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd4", "/home/fws/usr/{$mac_no}-bcd4", FTP_ASCII)) {
                // echo 'FTP��Download�˼��Ԥ��ޤ�����';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd4")) {   // ��ե����뤬����к��
                unlink("/home/fws/{$mac_no}-bcd4");
            }
        }
        /////////// FTP��Υե������¸�ߥ����å�
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd8") != -1) {
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd8", "/home/fws/usr/{$mac_no}-bcd8", FTP_ASCII)) {
                // echo 'FTP��Download�˼��Ԥ��ޤ�����';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd8")) {   // ��ե����뤬����к��
                unlink("/home/fws/{$mac_no}-bcd8");
            }
        }
        ///// State File Check BCD�黻  ���ߤξ��֤����
        $bcd1_file = "/home/fws/{$mac_no}-bcd1";            // BCD�黻�ѥե�����̾����
        $bcd2_file = "/home/fws/{$mac_no}-bcd2";
        $bcd4_file = "/home/fws/{$mac_no}-bcd4";
        $bcd8_file = "/home/fws/{$mac_no}-bcd8";
        $state_bcd = 0;                                     // �����
        if (file_exists($bcd1_file)) {
            $state_bcd += 1;
        }
        if (file_exists($bcd2_file)) {
            $state_bcd += 2;
        }
        if (file_exists($bcd4_file)) {
            $state_bcd += 4;
        }
        if (file_exists($bcd8_file)) {
            $state_bcd += 8;
        }
        $query = "select state from equip_mac_state_log2 where equip_index2(mac_no,date_time) < '{$mac_no}99999999999999' order by equip_index2(mac_no,date_time) DESC limit 1";
        if (getUniResult($query, $state_p) > 0) {
            // ʪ�����椬���åȤ���Ƥ����
            $state = state_check($state_p, $state_bcd);   // ʪ�����ֿ���ȥ����å��ξ��֤Ȥ�Ŭ���ͤ�����å�
        } else {
            $state = 0;     // ���֥ǡ�����̵���Τ�̵�����Ÿ�off=0
        }
        /////////// FTP��Υե������¸�ߥ����å�
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}_work_cnt.log") != -1) {
            /////////// FTP rename
            if (ftp_rename($ftp_con, "/home/fws/usr/{$mac_no}_work_cnt.log", "/home/fws/usr/{$mac_no}_work_cnt.tmp")) {
                /////////// FTP Download
                if (ftp_get($ftp_con, "/home/fws/{$mac_no}_work_cnt.log", "/home/fws/usr/{$mac_no}_work_cnt.tmp", FTP_ASCII)) {
                    // echo 'FTP��Download���������ޤ�����';
                    ftp_delete($ftp_con, "/home/fws/usr/{$mac_no}_work_cnt.tmp");  // ��ե�����Ϻ��
                } else {
                    // echo 'FTP��Download�˼��Ԥ��ޤ�����';
                    `/bin/rm -f /home/fws/{$mac_no}_work_cnt.log`;
                }
            } else {
                // echo 'FTP��rename�˼��Ԥ��ޤ�����';
                `/bin/rm -f /home/fws/{$mac_no}_work_cnt.log`;
            }
        }
        /////////// begin �ȥ�󥶥�����󳫻�
        if ($con = db_connect()) {
            query_affected_trans($con, 'begin');
            ///// Counter File Check        ���ߤβù��������������
            $cnt_file = "/home/fws/{$mac_no}_work_cnt.log";     // �ǥ��쥯�ȥꡦ�ե�����̾����
            $cnt_temp = "/home/fws/{$mac_no}_work_cnt.tmp";     // Rename �ѥǥ��쥯�ȥꡦ�ե�����̾����
            if (file_exists($cnt_file)) {                       // Counter File �������
                if (rename($cnt_file, $cnt_temp)) {
                    $fp = fopen ($cnt_temp,'r');
                    $row  = 0;                                  // ���쥳����
                    $data = array();                            // ǯ����,����,�ù���
                    while ($data[$row] = fgetcsv ($fp, 50, ',')) {
                        if ($data[$row][0] != '') {
                            $row++;
                        }
                    }
                    if ($row >= 1) {            // Counter File �˥쥳���ɤ�����о��֤Ȳù��������
                        ///// ���ߤΥǡ����١����κǿ��쥳���ɤ������
                        $query = "
                            SELECT mac_state, work_cnt FROM equip_work_log2
                            WHERE
                                equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                                AND
                                equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                            ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC limit 1
                        ";
                        $res = array();
                        if ( ($rows = getResult($query, $res)) >= 1) {  // ����βù����˥ץ饹���ƽ����
                            for ($j=0; $j<$row; $j++) {
                                $work_cnt  = $res[0]['work_cnt'] + ($j + 1);      // Counter UP
                                $date_time = $data[$j][0] . ' ' . $data[$j][1];     // PostgreSQL��TIMESTAMP�����ѹ�
                                if ( ($state == 1) || ($state == 8) || ($state == 5) ) {     // ��ư��ž��̵�ͱ�ž�����ʼ���ΤϤ��ʤΤǥ����å�����ɬ�פʤ�����
                                    $mac_state = $state;
                                } else {
                                    $mac_state = 1;     // Counter���ʤ�Ǥ���Τ˼�ư�Ǥ�̵�������ʼ�Ǥ�ʤ����϶���Ū�˼�ư��ž�ˤ���
                                }
                                $query = "insert into equip_work_log2
                                    (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                    values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                                if (query_affected_trans($con, $query) <= 0) {
                                    $temp_msg = "FWS2 Counter�ν񤭹��ߤ˼��� �쥳����:$j : $mac_no : $date_time : $mac_state \n";
                                    $temp_msg .= $query;    // debug ��
                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    // query_affected_trans($con, 'rollback');         // transaction rollback
                                    // exit();
                                }
                            }
                        } else {                    // �ǡ����١��������Τ���̵���˽����
                            for ($j=0; $j<$row; $j++) {
                                $work_cnt  =  ($j + 1);   // ���ξ��Ϥ������㤦
                                $date_time = $data[$j][0] . ' ' . $data[$j][1];     // PostgreSQL��TIMESTAMP�����ѹ�
                                // $mac_state = $state;     // ���ξ��ϲ��Υǡ����������ǽ�����⤤����ʲ���ɬ��
                                if ( ($state == 1) || ($state == 8) || ($state == 5) ) {     // ��ư��ž��̵�ͱ�ž�����ʼ���ΤϤ��ʤΤǥ����å�����ɬ�פʤ�����
                                    $mac_state = $state;
                                } else {
                                    $mac_state = 1;     // Counter���ʤ�Ǥ���Τ˼�ư�Ǥ�̵�������ʼ�Ǥ�ʤ����϶���Ū�˼�ư��ž�ˤ���
                                }
                                $query = "insert into equip_work_log2
                                    (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                    values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                                if (query_affected_trans($con, $query) <= 0) {
                                    $temp_msg = "fwserver2 ���ǡ����١����ν񤭹��ߤ˼��� �쥳����:$j\n$query";
                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    // query_affected_trans($con, 'rollback');         // transaction rollback
                                    // exit();
                                } else {
                                    $query = "select str_timestamp from equip_work_log2_header
                                            where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
                                    ";
                                    if (getUniResTrs($con, $query, $str_timestamp) > 0) {
                                        $query = "select case when cast('$date_time' as TIMESTAMP) < cast('$str_timestamp' as TIMESTAMP)
                                                then (select 1) else (select 0) end";
                                        if (getUniResTrs($con, $query, $check_time) > 0) {
                                            if ($check_time == 1) {
                                                $query = "update equip_work_log2_header set str_timestamp='{$date_time}'
                                                        where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                                                if (query_affected_trans($con, $query) <= 0) {
                                                    $temp_msg = "���ǡ�����������Ӥ�Header��UPDATE�˼��� mac_no:{$mac_no}";
                                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } else {                    // Counter File �˥쥳���ɤ��ʤ��ΤǾ��֤Τߥ����å��������
                        ///// ���ߤΥǡ����١����κǿ��쥳���ɤ������
                        $query = "
                            SELECT mac_state, work_cnt FROM equip_work_log2
                            WHERE
                                equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                                AND
                                equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                            ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC limit 1
                        ";
                        $res = array();
                        if ( ($rows = getResult($query, $res)) >= 1) {  // �쥳���ɤ�����о��֤�����å�����
                            if ($res[0]['mac_state'] != $state) {       // ���֤��㤨�н����
                                $work_cnt  = $res[0]['work_cnt'];       // ����βù�����Ȥ�
                                // $date_time  = date('Ymd His.');             // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                                // $date_time .= substr(microtime(), 2, 6);    // TIMESTAMP(6)�ǽ�ʣ���������
                                $date_time  = date('Ymd His');          // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                                $mac_state = $state;
                                $query = "insert into equip_work_log2
                                    (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                    values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                                if (query_affected_trans($con, $query) <= 0) {
                                    $temp_msg = "fwserver2 Counter File �����뤬�쥳���ɤ�̵�����Υǡ����١����ν񤭹��ߤ˼��� mac_no={$mac_no}";
                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    // query_affected_trans($con, 'rollback');         // transaction rollback
                                    // exit();
                                }
                            }
                        } else {            // ���Τ���̵���˽����
                            $work_cnt  = 0;             // ���ξ��ϣ�
                            $date_time = date('Ymd His');   // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                            $mac_state = $state;
                            $query = "insert into equip_work_log2
                                (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                            if (query_affected_trans($con, $query) <= 0) {
                                $temp_msg = "fwserver2 Counter File �����뤬�쥳���ɤ�̵�����ν��ǡ����١����ν񤭹��ߤ˼��� mac_no={$mac_no}";
                                `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                // query_affected_trans($con, 'rollback');         // transaction rollback
                                // exit();
                            } else {
                                $query = "select str_timestamp from equip_work_log2_header
                                        where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
                                ";
                                if (getUniResTrs($con, $query, $str_timestamp) > 0) {
                                    $query = "select case when cast('$date_time' as TIMESTAMP) < cast('$str_timestamp' as TIMESTAMP)
                                            then (select 1) else (select 0) end";
                                    if (getUniResTrs($con, $query, $check_time) > 0) {
                                        if ($check_time == 1) {
                                            $query = "update equip_work_log2_header set str_timestamp='{$date_time}'
                                                    where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                                            if (query_affected_trans($con, $query) <= 0) {
                                                $temp_msg = "���ǡ�����������Ӥ�Header��UPDATE�˼��� mac_no:{$mac_no}";
                                                `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    unlink($cnt_temp);      // ����ե�����κ��
                } else {
                    echo "�����󥿡��ե������ rename() �˼���\n";
                }
            } else {                    // Counter File ���ʤ��ΤǾ��֤Τ߽����
                ///// ���ߤΥǡ����١����κǿ��쥳���ɤ������
                $query = "
                    SELECT mac_state, work_cnt FROM equip_work_log2
                    WHERE
                        equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                        AND
                        equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                    ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC limit 1
                ";
                $res = array();
                if ( ($rows = getResult($query, $res)) >= 1) {  // �쥳���ɤ�����о��֤�����å�����
                    if ($res[0]['mac_state'] != $state) {       // ���֤��㤨�н����
                        $work_cnt  = $res[0]['work_cnt'];       // ����βù����򤽤Τޤ޻Ȥ�
                        // $date_time  = date('Ymd His.');             // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                        // $date_time .= substr(microtime(), 2, 6);    // TIMESTAMP(6)�ǽ�ʣ���������
                        $date_time  = date('Ymd His');          // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                        $mac_state = $state;
                        $query = "insert into equip_work_log2
                            (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                            values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                        if (query_affected_trans($con, $query) <= 0) {
                            $temp_msg = date('Y-m-d H:i:s ') . "fwserver2 equip_work_log2 �ؾ��ֽ���ߤ˼���";
                            `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                            // query_affected_trans($con, 'rollback');         // transaction rollback
                            // exit();
                        }
                    }
                } else {        // ���Τ���̵���˽����
                    $work_cnt  = 0;             // ���ξ��ϣ�
                    $date_time = date('Ymd His');       // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                    $mac_state = $state;
                    $query = "insert into equip_work_log2
                        (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                        values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                    if (query_affected_trans($con, $query) <= 0) {
                        $temp_msg = date('Y-m-d H:i:s ') . "fwserver2 equip_work_log2 �ؽ��ξ��ֽ���ߤ˼���";
                        `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                        // query_affected_trans($con, 'rollback');         // transaction rollback
                        // exit();
                    } else {
                        $query = "select str_timestamp from equip_work_log2_header
                                where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
                        ";
                        if (getUniResTrs($con, $query, $str_timestamp) > 0) {
                            $query = "select case when cast('$date_time' as TIMESTAMP) < cast('$str_timestamp' as TIMESTAMP)
                                    then (select 1) else (select 0) end";
                            if (getUniResTrs($con, $query, $check_time) > 0) {
                                if ($check_time == 1) {
                                    $query = "update equip_work_log2_header set str_timestamp='{$date_time}'
                                            where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                                    if (query_affected_trans($con, $query) <= 0) {
                                        $temp_msg = "���ǡ�����������Ӥ�Header��UPDATE�˼��� mac_no:{$mac_no}";
                                        `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, 'commit');
        } else {
            echo "�ǡ����١�������³2�˼���\n";
            exit();
        }
    }
}
////////// FTP Close
if ($ftp_con) {
    ftp_close($ftp_con);
}
}   // $ftp_flg End





/****************************** Netmoni OR (Netmoni & FWS1)���� *********************************/
$ftp_flg = true;
////////// FTP CONNECT Netmoni
if ( !($ftp_con = ftp_connect(NET_HOST)) ) {   // Netmoni4��IP���ɥ쥹
    $error_msg = date('Y-m-d H:i:s ') . "Netmoni��FTP����³�˼��Ԥ��ޤ�����";
    `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
    // echo 'FTP����³�˼��Ԥ��ޤ�����';
    $ftp_flg = false;
} else {
    if (!@ftp_login($ftp_con, NET_USER, NET_PASS)) {      // 2004/03/29 ���顼��å�����������
        $error_msg = date('Y-m-d H:i:s ') . "Netmoni��FTP��login�˼��Ԥ��ޤ�����";
        `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
        $ftp_flg = false;
    }
}
////////// FTP CONNECT FWS1
if ( !($ftp_con_fws1 = ftp_connect(FWS1)) ) {
    $error_msg = date('Y-m-d H:i:s ') . "FWS1 ftp_connect error state_check_netmoni()";
    `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
    $ftp_flg = false;
} else {
    if (!@ftp_login($ftp_con_fws1, FWS_USER, FWS_PASS)) {
        $error_msg = date('Y-m-d H:i:s ') . "FWS1 ftp_login error state_check_netmoni()";
        `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
        $ftp_flg = false;
    }
}

if ($ftp_flg) {
    ////////// ��ư��ε������إå����ե����뤫����� & �����ޥ��������鵡��̾�����
    $query = "select mac_no, siji_no, koutei, parts_no, plan_cnt, mac_name, csv_flg from equip_work_log2_header
            left outer join equip_machine_master2 using(mac_no)
            where work_flg is TRUE and (csv_flg = 101 or csv_flg = 1) and survey = 'Y'";
                 // Netmoni=1 FWS1=2 FWS2=3  ... 101=Netmoni & FWS �����ζ��̲�
    $res_key = array();
    if ( ($rows_key = getResult($query, $res_key)) >= 1) {      // �쥳���ɤ����ʾ夢���
        for ($i=0; $i<$rows_key; $i++) {                        // ���쥳���ɤ��Ľ���
            ///// insert �� �ѿ� �����
            $mac_no   = $res_key[$i]['mac_no'];
            $siji_no  = $res_key[$i]['siji_no'];
            $parts_no = $res_key[$i]['parts_no'];
            $koutei   = $res_key[$i]['koutei'];
            $plan_cnt = $res_key[$i]['plan_cnt'];
            $mac_name = $res_key[$i]['mac_name'];
            $csv_flg  = $res_key[$i]['csv_flg'];
            $log_name = "{$mac_no}{$siji_no}{$parts_no}{$koutei}{$plan_cnt}.csv";   // ��⡼�ȤΥ��ե�����̾
            $log_temp = "{$mac_no}{$siji_no}{$parts_no}{$koutei}{$plan_cnt}.tmp";   // ��⡼�Ȥΰ���ե�����̾
            /////////// FTP��Υե������¸�ߥ����å�
            if (ftp_size($ftp_con, $log_name) != -1) {
                /////////// FTP��ΰ���ե������¸�ߥ����å�(����ȥ�֥�Ǻ���Ǥ����ĤäƤ�������б�)
                if (ftp_size($ftp_con, $log_temp) != -1) {
                    ftp_delete($ftp_con, $log_temp);  // ����ε����ե�����Ϻ��
                }
                /////////// FTP rename
                if (ftp_rename($ftp_con, $log_name, $log_temp)) {
                    /////////// FTP Download
                    if (ftp_get($ftp_con, "/home/netmoni4/data/{$log_name}", $log_temp, FTP_ASCII)) {
                        // echo 'FTP��Download���������ޤ�����';
                        ftp_delete($ftp_con, $log_temp);  // ��ե�����Ϻ��
                    } else {
                        $error_msg = date('Y-m-d H:i:s ') . "Netmoni��ftp_get()�˼��Ԥ��ޤ�����";
                        `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
                    }
                } else {
                    $error_msg = date('Y-m-d H:i:s ') . "Netmoni��ftp_rename()�˼��Ԥ��ޤ�����";
                    `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
                }
            }
            ///////////// Download����log��DB����Ͽ
            $file_name = EQUIP_LOG_DIR . $log_name;
            // �ե����¸�ߥ����å�
            if (file_exists($file_name)) {          // �ե������¸�ߥ����å�
                $fp      = fopen ($file_name, 'r');
                $data    = array();
                $flag    = array();
                $sel_cnt = 1;           // �Ѳ��Τ��ä��ǡ���(�ǡ����١����������ѷ��)
                $row     = 0;           // ���쥳����(�ܣ���ɬ��)
                if ($con = db_connect()) {
                    query_affected_trans($con, 'begin');
                }
                while ($data[$row] = fgetcsv ($fp, 200, ',')) {
                    $query_chk = "SELECT state
                                    FROM
                                        equip_mac_state_log2
                                    WHERE
                                        equip_index2(mac_no, date_time) < '{$mac_no}99999999999999'
                                    ORDER BY equip_index2(mac_no, date_time) DESC
                                    limit 1
                    ";
                    if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                        // ���Τ���ǡ���̵���ȸ��ʤ�
                        $res_chk = -1;  // ���ꤨ�ʤ����ͤ򥻥å�
                    }
                    if ($res_chk != $data[$row][5]) {   // ���֤��Ѳ����Ƥ��뤫�����å� �Ѳ����Ƥ���н����
                        $date_time = $data[$row][1] . " " . $data[$row][2];     // timestamp�����ѿ����� ���
                        switch ($data[$row][5]) {
                        case 0:
                            $state_name = "�Ÿ�OFF";
                            break;
                        case 1:
                            $state_name = "��ư��ž";
                            break;
                        case 2:
                            $state_name = "���顼��";
                            break;
                        case 3:
                            $state_name = "�����";
                            break;
                        case 4:
                            $state_name = "Net��ư";
                            break;
                        case 5:
                            $state_name = "Net��λ";
                            break;
                        default:
                            $state_name = "̤��Ͽ";
                        }
                        $query = "insert into equip_mac_state_log2
                                    (mac_no, state, date_time, state_name, state_type)
                                    values($mac_no, {$data[$row][5]}, '$date_time', '$state_name', $csv_flg)
                        ";
                        if (query_affected_trans($con, $query) <= 0) {
                            $temp_msg = "$query\n date_time:$date_time mac_no:$mac_no state:{$data[$row][5]} j=$j";
                            `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                            // query_affected_trans($con, "rollback");         // transaction rollback
                        }
                    }
                    $data[$row][5] = state_check_netmoni($ftp_con_fws1, $data[$row][5], $mac_no);   // ʪ�����ֿ���ȥ����å��ξ��֤Ȥ�Ŭ���ͤ�����å� 2004/07/12 Add k.kobayashi
                    if ($row == 0) {            // ���Υ쥳���ɤʤ�̵���ǥե饰��Ω�Ƥ�
                        $flag[$row] = 1;
                        $sel_cnt++;
                    } elseif ( ($data[$row][5]!=$data[$row-1][5]) || ($data[$row][10]!=$data[$row-1][10]) || ($data[$row][14]!=$data[$row-1][14]) ) {
                        $flag[$row] = 1;
                        $sel_cnt++;
                    } else {
                        $flag[$row] = 0;
                    }
                    $row++;
                }
                fclose($fp);
                unlink($file_name);      // ���ե��������
                
                ///// equip_work_log2 �ؤν����
                for ($cnt=0; $cnt<$row; $cnt++) {   // row �����
                    $mac_no    = $data[$cnt][4];                            // �����ֹ�
                    $date_time = $data[$cnt][1] . ' ' . $data[$cnt][2];     // TIMESTAMP������Ͽ
                    $mac_state = $data[$cnt][5];                            // �����ξ���
                    $query = "select mac_state
                                    ,work_cnt
                                from
                                    equip_work_log2
                                where
                                    equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                                and
                                    equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                                order by
                                    equip_index(mac_no, siji_no, koutei, date_time) DESC
                                offset 0 limit 1
                    ";
                    $res = array();
                    if ( ($rows=getResultTrs($con, $query, $res)) >= 1) {
                        if ( ($mac_state == 0) || ($mac_state == 9) ) {     // �Ÿ�OFF or ���Ǥ��ä�������work_cnt��ݻ�����
                            $work_cnt = $res[0][1];             // ����work_cnt�μ���
                        } else {
                            $work_cnt = $data[$cnt][10];        // �Ÿ�OFF�Ǥʤ����CSV�βù����������
                        }
                    } else {
                        $work_cnt  = $data[$cnt][10];       // ��������ʤ�����CSV�βù����������
                        $res[0][0] = '';                    // ������Τ�
                        $res[0][1] = '';                    //     ��
                    }
                    ///// mac_state �� work_cnt���㤨�н����
                    if( ($res[0][0] != $mac_state) || ($res[0][1] != $work_cnt) ) {
                        $insert_qry = "insert into equip_work_log2
                                (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)
                        ";
                        if (query_affected_trans($con, $insert_qry) <= 0) {
                            $temp_msg = date('Y/m/d H:i:s', mktime()) . "$file_name_backup : ����ߥ��顼: $mac_no :" . ($cnt+1);
                            `echo "$temp_msg" >> /tmp/equip_netmoni_write_error.log`;
                        }
                    }
                }
                ///// �ǡ����١����򥳥ߥåȤ��ƽ�λ
                query_affected_trans($con, 'commit');
            }
        }
    }
}

////////// FTP Close
if ($ftp_con) {
    ftp_close($ftp_con);
}
////////// FTP FWS1 Close
if ($ftp_con_fws1) {
    ftp_close($ftp_con_fws1);
}



















/****************************** FWS3���� *********************************/
$ftp_flg = true;
////////// FTP CONNECT
if ( !($ftp_con = ftp_connect(FWS3)) ) {   // �ƥ����Ѥ˥�ƥ��ɽ��
    $error_msg = date('Y-m-d H:i:s ') . "fwserver3��FTP����³�˼��Ԥ��ޤ�����";
    `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
    $ftp_flg = false;
} else {
    if (!@ftp_login($ftp_con, FWS_USER, FWS_PASS)) {      // 2004/03/29 ���顼��å�����������
        $error_msg = date('Y-m-d H:i:s ') . "FWS3��FTP��login�˼��Ԥ��ޤ�����";
        `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
        $ftp_flg = false;
    }
}

if ($ftp_flg) {
////////// ��ư��˴ط��ʤ� �����ޥ������ƻ뤹�뵡���ֹ����� (ʪ�����֤�24���ִƻ뤹�뤿��)
$query = "select mac_no, csv_flg from equip_machine_master2 where csv_flg = 4 and survey = 'Y'";
                        // Netmoni=1 fwserver1=2 fwserver2=3 fwserver3=4 fwserver4=5 ... , Net&fws1=101
$res_key = array();
if ( ($rows_key = getResult($query, $res_key)) >= 1) {      // �쥳���ɤ����ʾ夢���
    for ($i=0; $i<$rows_key; $i++) {                        // ���쥳���ɤ��Ľ���
        ///// insert �� �ѿ� �����
        $mac_no   = $res_key[$i]['mac_no'];
        $csv_flg  = $res_key[$i]['csv_flg'];
        /////////// FTP��Υե������¸�ߥ����å�
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}_work_state.log") != -1) {
            /////////// FTP rename
            if (ftp_rename($ftp_con, "/home/fws/usr/{$mac_no}_work_state.log", "/home/fws/usr/{$mac_no}_work_state.tmp")) {
                /////////// FTP Download
                if (ftp_get($ftp_con, "/home/fws/{$mac_no}_work_state.log", "/home/fws/usr/{$mac_no}_work_state.tmp", FTP_ASCII)) {
                    // echo 'FTP��Download���������ޤ�����';
                    ftp_delete($ftp_con, "/home/fws/usr/{$mac_no}_work_state.tmp");  // ��ե�����Ϻ��
                } else {
                    // echo 'FTP��Download�˼��Ԥ��ޤ�����';
                    `/bin/rm -f /home/fws/{$mac_no}_work_state.log`;
                }
            } else {
                // echo 'FTP��rename�˼��Ԥ��ޤ�����';
                `/bin/rm -f /home/fws/{$mac_no}_work_state.log`;
            }
        }
        /////////// begin �ȥ�󥶥�����󳫻�
        if ($con = db_connect()) {
            query_affected_trans($con, 'begin');
            ///// State Log File Check        ���ߤξ���(��ž�桦�����)����������� �Ÿ�OFF�����OK
            $state_file = "/home/fws/{$mac_no}_work_state.log";     // �ǥ��쥯�ȥꡦ�ե�����̾����
            $state_temp = "/home/fws/{$mac_no}_work_state.tmp";     // Rename �ѥǥ��쥯�ȥꡦ�ե�����̾����
            if (file_exists($state_file)) {                         // State Log File �������
                if (rename($state_file, $state_temp)) {
                    $fp = fopen ($state_temp,'r');
                    $row  = 0;                                  // ���쥳����
                    $data = array();                            // ǯ����,����,�ù���
                    while ($data[$row] = fgetcsv ($fp, 50, ',')) {
                        if ($data[$row][0] != '') {
                            $row++;
                        }
                    }
                    for ($j=0; $j<$row; $j++) {         // Status File �˥쥳���ɤ�����о��֤�����������
                        if ($data[$j][2] == 'auto') {   // �ե����뤫��ʪ�������ֹ�����
                            $state_p = 1;               // ��ž��(��ư��ž)
                        } elseif ($data[$j][2] == 'stop') {
                            $state_p = 3;               // �����
                        } elseif ($data[$j][2] == 'on') {
                            $state_p = 3;               // �Ÿ�ON�ξ���Default�ͤ������=3
                        } else {
                            $state_p = 0;               // �Ÿ�OFF "off" ��ͽ��
                        }
                        $date_time = $data[$j][0] . " " . $data[$j][1];     // timestamp�����ѿ����� ���
                        $state_name = equip_machine_state($mac_no, $state_p, $txt_color, $bg_color);
                        $query = "insert into equip_mac_state_log2
                            (mac_no, state, date_time, state_name, state_type)
                            values($mac_no, $state_p, '$date_time', '$state_name', $csv_flg)";
                        if (query_affected_trans($con, $query) <= 0) {
                            $temp_msg = "$query\n date_time:$date_time mac_no:$mac_no state:$state_p j=$j";
                            `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                            // query_affected_trans($con, "rollback");         // transaction rollback
                            // exit();
                        }
                    }
                    unlink($state_temp);        // ����ե�����κ��
                } else {
                    echo "���ơ������ե������ rename() �˼���\n";
                }
            }
            ///// State Log File ������λ
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, 'commit');
        } else {
            echo "�ǡ����١�������³2�˼���\n";
            exit();
        }
    }
}
////////// ��ư��ε������إå����ե����뤫����� & �����ޥ��������鵡��̾�����
$query = "select mac_no, siji_no, koutei, parts_no, plan_cnt, mac_name, csv_flg from equip_work_log2_header
        left outer join equip_machine_master2 using(mac_no)
        where work_flg is TRUE and csv_flg = 4 and survey = 'Y'
";
        // Netmoni=1&101 fwserver1=2 fwserver2=3 fwserver3=4 fwserver4=5 fwserver5=6 ...
$res_key = array();
if ( ($rows_key = getResult($query, $res_key)) >= 1) {      // �쥳���ɤ����ʾ夢���
    for ($i=0; $i<$rows_key; $i++) {                        // ���쥳���ɤ��Ľ���
        ///// insert �� �ѿ� �����
        $mac_no   = $res_key[$i]['mac_no'];
        $siji_no  = $res_key[$i]['siji_no'];
        $parts_no = $res_key[$i]['parts_no'];
        $koutei   = $res_key[$i]['koutei'];
        $plan_cnt = $res_key[$i]['plan_cnt'];
        $mac_name = $res_key[$i]['mac_name'];
        $csv_flg  = $res_key[$i]['csv_flg'];
        /////////// FTP��Υե������¸�ߥ����å�
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd1") != -1) {
            /////////// FTP Download
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd1", "/home/fws/usr/{$mac_no}-bcd1", FTP_ASCII)) {
                // echo 'FTP��Download�˼��Ԥ��ޤ�����';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd1")) {   // ��ե����뤬����к��
                unlink("/home/fws/{$mac_no}-bcd1");
            }
        }
        /////////// FTP��Υե������¸�ߥ����å�
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd2") != -1) {
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd2", "/home/fws/usr/{$mac_no}-bcd2", FTP_ASCII)) {
                // echo 'FTP��Download�˼��Ԥ��ޤ�����';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd2")) {   // ��ե����뤬����к��
                unlink("/home/fws/{$mac_no}-bcd2");
            }
        }
        /////////// FTP��Υե������¸�ߥ����å�
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd4") != -1) {
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd4", "/home/fws/usr/{$mac_no}-bcd4", FTP_ASCII)) {
                // echo 'FTP��Download�˼��Ԥ��ޤ�����';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd4")) {   // ��ե����뤬����к��
                unlink("/home/fws/{$mac_no}-bcd4");
            }
        }
        /////////// FTP��Υե������¸�ߥ����å�
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd8") != -1) {
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd8", "/home/fws/usr/{$mac_no}-bcd8", FTP_ASCII)) {
                // echo 'FTP��Download�˼��Ԥ��ޤ�����';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd8")) {   // ��ե����뤬����к��
                unlink("/home/fws/{$mac_no}-bcd8");
            }
        }
        ///// State File Check BCD�黻  ���ߤξ��֤����
        $bcd1_file = "/home/fws/{$mac_no}-bcd1";            // BCD�黻�ѥե�����̾����
        $bcd2_file = "/home/fws/{$mac_no}-bcd2";
        $bcd4_file = "/home/fws/{$mac_no}-bcd4";
        $bcd8_file = "/home/fws/{$mac_no}-bcd8";
        $state_bcd = 0;                                     // �����
        if (file_exists($bcd1_file)) {
            $state_bcd += 1;
        }
        if (file_exists($bcd2_file)) {
            $state_bcd += 2;
        }
        if (file_exists($bcd4_file)) {
            $state_bcd += 4;
        }
        if (file_exists($bcd8_file)) {
            $state_bcd += 8;
        }
        $query = "select state from equip_mac_state_log2 where equip_index2(mac_no,date_time) < '{$mac_no}99999999999999' order by equip_index2(mac_no,date_time) DESC limit 1";
        if (getUniResult($query, $state_p) > 0) {
            // ʪ�����椬���åȤ���Ƥ����
            $state = state_check($state_p, $state_bcd);   // ʪ�����ֿ���ȥ����å��ξ��֤Ȥ�Ŭ���ͤ�����å�
        } else {
            $state = 0;     // ���֥ǡ�����̵���Τ�̵�����Ÿ�off=0
        }
        /////////// FTP��Υե������¸�ߥ����å�
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}_work_cnt.log") != -1) {
            /////////// FTP rename
            if (ftp_rename($ftp_con, "/home/fws/usr/{$mac_no}_work_cnt.log", "/home/fws/usr/{$mac_no}_work_cnt.tmp")) {
                /////////// FTP Download
                if (ftp_get($ftp_con, "/home/fws/{$mac_no}_work_cnt.log", "/home/fws/usr/{$mac_no}_work_cnt.tmp", FTP_ASCII)) {
                    // echo 'FTP��Download���������ޤ�����';
                    ftp_delete($ftp_con, "/home/fws/usr/{$mac_no}_work_cnt.tmp");  // ��ե�����Ϻ��
                } else {
                    // echo 'FTP��Download�˼��Ԥ��ޤ�����';
                    `/bin/rm -f /home/fws/{$mac_no}_work_cnt.log`;
                }
            } else {
                // echo 'FTP��rename�˼��Ԥ��ޤ�����';
                `/bin/rm -f /home/fws/{$mac_no}_work_cnt.log`;
            }
        }
        /////////// begin �ȥ�󥶥�����󳫻�
        if ($con = db_connect()) {
            query_affected_trans($con, 'begin');
            ///// Counter File Check        ���ߤβù��������������
            $cnt_file = "/home/fws/{$mac_no}_work_cnt.log";     // �ǥ��쥯�ȥꡦ�ե�����̾����
            $cnt_temp = "/home/fws/{$mac_no}_work_cnt.tmp";     // Rename �ѥǥ��쥯�ȥꡦ�ե�����̾����
            if (file_exists($cnt_file)) {                       // Counter File �������
                if (rename($cnt_file, $cnt_temp)) {
                    $fp = fopen ($cnt_temp,'r');
                    $row  = 0;                                  // ���쥳����
                    $data = array();                            // ǯ����,����,�ù���
                    while ($data[$row] = fgetcsv ($fp, 50, ',')) {
                        if ($data[$row][0] != '') {
                            $row++;
                        }
                    }
                    if ($row >= 1) {            // Counter File �˥쥳���ɤ�����о��֤Ȳù��������
                        ///// ���ߤΥǡ����١����κǿ��쥳���ɤ������
                        $query = "
                            SELECT mac_state, work_cnt FROM equip_work_log2
                            WHERE
                                equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                                AND
                                equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                            ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC limit 1
                        ";
                        $res = array();
                        if ( ($rows = getResult($query, $res)) >= 1) {  // ����βù����˥ץ饹���ƽ����
                            for ($j=0; $j<$row; $j++) {
                                $work_cnt  = $res[0]['work_cnt'] + ($j + 1);      // Counter UP
                                $date_time = $data[$j][0] . ' ' . $data[$j][1];     // PostgreSQL��TIMESTAMP�����ѹ�
                                if ( ($state == 1) || ($state == 8) || ($state == 5) ) {     // ��ư��ž��̵�ͱ�ž�����ʼ���ΤϤ��ʤΤǥ����å�����ɬ�פʤ�����
                                    $mac_state = $state;
                                } else {
                                    $mac_state = 1;     // Counter���ʤ�Ǥ���Τ˼�ư�Ǥ�̵�������ʼ�Ǥ�ʤ����϶���Ū�˼�ư��ž�ˤ���
                                }
                                $query = "insert into equip_work_log2
                                    (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                    values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                                if (query_affected_trans($con, $query) <= 0) {
                                    $temp_msg = "FWS2 Counter�ν񤭹��ߤ˼��� �쥳����:$j : $mac_no : $date_time : $mac_state \n";
                                    $temp_msg .= $query;    // debug ��
                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    // query_affected_trans($con, 'rollback');         // transaction rollback
                                    // exit();
                                }
                            }
                        } else {                    // �ǡ����١��������Τ���̵���˽����
                            for ($j=0; $j<$row; $j++) {
                                $work_cnt  =  ($j + 1);   // ���ξ��Ϥ������㤦
                                $date_time = $data[$j][0] . ' ' . $data[$j][1];     // PostgreSQL��TIMESTAMP�����ѹ�
                                // $mac_state = $state;     // ���ξ��ϲ��Υǡ����������ǽ�����⤤����ʲ���ɬ��
                                if ( ($state == 1) || ($state == 8) || ($state == 5) ) {     // ��ư��ž��̵�ͱ�ž�����ʼ���ΤϤ��ʤΤǥ����å�����ɬ�פʤ�����
                                    $mac_state = $state;
                                } else {
                                    $mac_state = 1;     // Counter���ʤ�Ǥ���Τ˼�ư�Ǥ�̵�������ʼ�Ǥ�ʤ����϶���Ū�˼�ư��ž�ˤ���
                                }
                                $query = "insert into equip_work_log2
                                    (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                    values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                                if (query_affected_trans($con, $query) <= 0) {
                                    $temp_msg = "fwserver3 ���ǡ����١����ν񤭹��ߤ˼��� �쥳����:$j\n$query";
                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    // query_affected_trans($con, 'rollback');         // transaction rollback
                                    // exit();
                                } else {
                                    $query = "select str_timestamp from equip_work_log2_header
                                            where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
                                    ";
                                    if (getUniResTrs($con, $query, $str_timestamp) > 0) {
                                        $query = "select case when cast('$date_time' as TIMESTAMP) < cast('$str_timestamp' as TIMESTAMP)
                                                then (select 1) else (select 0) end";
                                        if (getUniResTrs($con, $query, $check_time) > 0) {
                                            if ($check_time == 1) {
                                                $query = "update equip_work_log2_header set str_timestamp='{$date_time}'
                                                        where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                                                if (query_affected_trans($con, $query) <= 0) {
                                                    $temp_msg = "���ǡ�����������Ӥ�Header��UPDATE�˼��� mac_no:{$mac_no}";
                                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } else {                    // Counter File �˥쥳���ɤ��ʤ��ΤǾ��֤Τߥ����å��������
                        ///// ���ߤΥǡ����١����κǿ��쥳���ɤ������
                        $query = "
                            SELECT mac_state, work_cnt FROM equip_work_log2
                            WHERE
                                equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                                AND
                                equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                            ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC limit 1
                        ";
                        $res = array();
                        if ( ($rows = getResult($query, $res)) >= 1) {  // �쥳���ɤ�����о��֤�����å�����
                            if ($res[0]['mac_state'] != $state) {       // ���֤��㤨�н����
                                $work_cnt  = $res[0]['work_cnt'];       // ����βù�����Ȥ�
                                // $date_time  = date('Ymd His.');             // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                                // $date_time .= substr(microtime(), 2, 6);    // TIMESTAMP(6)�ǽ�ʣ���������
                                $date_time  = date('Ymd His');          // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                                $mac_state = $state;
                                $query = "insert into equip_work_log2
                                    (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                    values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                                if (query_affected_trans($con, $query) <= 0) {
                                    $temp_msg = "fwserver3 Counter File �����뤬�쥳���ɤ�̵�����Υǡ����١����ν񤭹��ߤ˼��� mac_no={$mac_no}";
                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    // query_affected_trans($con, 'rollback');         // transaction rollback
                                    // exit();
                                }
                            }
                        } else {            // ���Τ���̵���˽����
                            $work_cnt  = 0;             // ���ξ��ϣ�
                            $date_time = date('Ymd His');   // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                            $mac_state = $state;
                            $query = "insert into equip_work_log2
                                (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                            if (query_affected_trans($con, $query) <= 0) {
                                $temp_msg = "fwserver3 Counter File �����뤬�쥳���ɤ�̵�����ν��ǡ����١����ν񤭹��ߤ˼��� mac_no={$mac_no}";
                                `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                // query_affected_trans($con, 'rollback');         // transaction rollback
                                // exit();
                            } else {
                                $query = "select str_timestamp from equip_work_log2_header
                                        where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
                                ";
                                if (getUniResTrs($con, $query, $str_timestamp) > 0) {
                                    $query = "select case when cast('$date_time' as TIMESTAMP) < cast('$str_timestamp' as TIMESTAMP)
                                            then (select 1) else (select 0) end";
                                    if (getUniResTrs($con, $query, $check_time) > 0) {
                                        if ($check_time == 1) {
                                            $query = "update equip_work_log2_header set str_timestamp='{$date_time}'
                                                    where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                                            if (query_affected_trans($con, $query) <= 0) {
                                                $temp_msg = "���ǡ�����������Ӥ�Header��UPDATE�˼��� mac_no:{$mac_no}";
                                                `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    unlink($cnt_temp);      // ����ե�����κ��
                } else {
                    echo "�����󥿡��ե������ rename() �˼���\n";
                }
            } else {                    // Counter File ���ʤ��ΤǾ��֤Τ߽����
                ///// ���ߤΥǡ����١����κǿ��쥳���ɤ������
                $query = "
                    SELECT mac_state, work_cnt FROM equip_work_log2
                    WHERE
                        equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                        AND
                        equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                    ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC limit 1
                ";
                $res = array();
                if ( ($rows = getResult($query, $res)) >= 1) {  // �쥳���ɤ�����о��֤�����å�����
                    if ($res[0]['mac_state'] != $state) {       // ���֤��㤨�н����
                        $work_cnt  = $res[0]['work_cnt'];       // ����βù����򤽤Τޤ޻Ȥ�
                        // $date_time  = date('Ymd His.');             // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                        // $date_time .= substr(microtime(), 2, 6);    // TIMESTAMP(6)�ǽ�ʣ���������
                        $date_time  = date('Ymd His');          // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                        $mac_state = $state;
                        $query = "insert into equip_work_log2
                            (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                            values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                        if (query_affected_trans($con, $query) <= 0) {
                            $temp_msg = date('Y-m-d H:i:s ') . "fwserver3 equip_work_log2 �ؾ��ֽ���ߤ˼���";
                            `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                            // query_affected_trans($con, 'rollback');         // transaction rollback
                            // exit();
                        }
                    }
                } else {        // ���Τ���̵���˽����
                    $work_cnt  = 0;             // ���ξ��ϣ�
                    $date_time = date('Ymd His');       // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                    $mac_state = $state;
                    $query = "insert into equip_work_log2
                        (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                        values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                    if (query_affected_trans($con, $query) <= 0) {
                        $temp_msg = date('Y-m-d H:i:s ') . "fwserver3 equip_work_log2 �ؽ��ξ��ֽ���ߤ˼���";
                        `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                        // query_affected_trans($con, 'rollback');         // transaction rollback
                        // exit();
                    } else {
                        $query = "select str_timestamp from equip_work_log2_header
                                where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
                        ";
                        if (getUniResTrs($con, $query, $str_timestamp) > 0) {
                            $query = "select case when cast('$date_time' as TIMESTAMP) < cast('$str_timestamp' as TIMESTAMP)
                                    then (select 1) else (select 0) end";
                            if (getUniResTrs($con, $query, $check_time) > 0) {
                                if ($check_time == 1) {
                                    $query = "update equip_work_log2_header set str_timestamp='{$date_time}'
                                            where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                                    if (query_affected_trans($con, $query) <= 0) {
                                        $temp_msg = "���ǡ�����������Ӥ�Header��UPDATE�˼��� mac_no:{$mac_no}";
                                        `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, 'commit');
        } else {
            echo "�ǡ����١�������³2�˼���\n";
            exit();
        }
    }
}
////////// FTP Close
if ($ftp_con) {
    ftp_close($ftp_con);
}
}   // $ftp_flg End





/****************************** FWS4���� *********************************/
$ftp_flg = true;
////////// FTP CONNECT
if ( !($ftp_con = ftp_connect(FWS4)) ) {   // �ƥ����Ѥ˥�ƥ��ɽ��
    $error_msg = date('Y-m-d H:i:s ') . "fwserver4��FTP����³�˼��Ԥ��ޤ�����";
    `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
    $ftp_flg = false;
} else {
    if (!@ftp_login($ftp_con, FWS_USER, FWS_PASS)) {      // 2004/03/29 ���顼��å�����������
        $error_msg = date('Y-m-d H:i:s ') . "FWS4��FTP��login�˼��Ԥ��ޤ�����";
        `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
        $ftp_flg = false;
    }
}

if ($ftp_flg) {
////////// ��ư��˴ط��ʤ� �����ޥ������ƻ뤹�뵡���ֹ����� (ʪ�����֤�24���ִƻ뤹�뤿��)
$query = "select mac_no, csv_flg from equip_machine_master2 where csv_flg = 5 and survey = 'Y'";
                        // Netmoni=1 fwserver1=2 fwserver2=3 fwserver3=4 fwserver4=5 ... , Net&fws1=101
$res_key = array();
if ( ($rows_key = getResult($query, $res_key)) >= 1) {      // �쥳���ɤ����ʾ夢���
    for ($i=0; $i<$rows_key; $i++) {                        // ���쥳���ɤ��Ľ���
        ///// insert �� �ѿ� �����
        $mac_no   = $res_key[$i]['mac_no'];
        $csv_flg  = $res_key[$i]['csv_flg'];
        /////////// FTP��Υե������¸�ߥ����å�
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}_work_state.log") != -1) {
            /////////// FTP rename
            if (ftp_rename($ftp_con, "/home/fws/usr/{$mac_no}_work_state.log", "/home/fws/usr/{$mac_no}_work_state.tmp")) {
                /////////// FTP Download
                if (ftp_get($ftp_con, "/home/fws/{$mac_no}_work_state.log", "/home/fws/usr/{$mac_no}_work_state.tmp", FTP_ASCII)) {
                    // echo 'FTP��Download���������ޤ�����';
                    ftp_delete($ftp_con, "/home/fws/usr/{$mac_no}_work_state.tmp");  // ��ե�����Ϻ��
                } else {
                    // echo 'FTP��Download�˼��Ԥ��ޤ�����';
                    `/bin/rm -f /home/fws/{$mac_no}_work_state.log`;
                }
            } else {
                // echo 'FTP��rename�˼��Ԥ��ޤ�����';
                `/bin/rm -f /home/fws/{$mac_no}_work_state.log`;
            }
        }
        /////////// begin �ȥ�󥶥�����󳫻�
        if ($con = db_connect()) {
            query_affected_trans($con, 'begin');
            ///// State Log File Check        ���ߤξ���(��ž�桦�����)����������� �Ÿ�OFF�����OK
            $state_file = "/home/fws/{$mac_no}_work_state.log";     // �ǥ��쥯�ȥꡦ�ե�����̾����
            $state_temp = "/home/fws/{$mac_no}_work_state.tmp";     // Rename �ѥǥ��쥯�ȥꡦ�ե�����̾����
            if (file_exists($state_file)) {                         // State Log File �������
                if (rename($state_file, $state_temp)) {
                    $fp = fopen ($state_temp,'r');
                    $row  = 0;                                  // ���쥳����
                    $data = array();                            // ǯ����,����,�ù���
                    while ($data[$row] = fgetcsv ($fp, 50, ',')) {
                        if ($data[$row][0] != '') {
                            $row++;
                        }
                    }
                    for ($j=0; $j<$row; $j++) {         // Status File �˥쥳���ɤ�����о��֤�����������
                        if ($data[$j][2] == 'auto') {   // �ե����뤫��ʪ�������ֹ�����
                            $state_p = 1;               // ��ž��(��ư��ž)
                        } elseif ($data[$j][2] == 'stop') {
                            $state_p = 3;               // �����
                        } elseif ($data[$j][2] == 'on') {
                            $state_p = 3;               // �Ÿ�ON�ξ���Default�ͤ������=3
                        } else {
                            $state_p = 0;               // �Ÿ�OFF "off" ��ͽ��
                        }
                        $date_time = $data[$j][0] . " " . $data[$j][1];     // timestamp�����ѿ����� ���
                        $state_name = equip_machine_state($mac_no, $state_p, $txt_color, $bg_color);
                        $query = "insert into equip_mac_state_log2
                            (mac_no, state, date_time, state_name, state_type)
                            values($mac_no, $state_p, '$date_time', '$state_name', $csv_flg)";
                        if (query_affected_trans($con, $query) <= 0) {
                            $temp_msg = "$query\n date_time:$date_time mac_no:$mac_no state:$state_p j=$j";
                            `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                            // query_affected_trans($con, "rollback");         // transaction rollback
                            // exit();
                        }
                    }
                    unlink($state_temp);        // ����ե�����κ��
                } else {
                    echo "���ơ������ե������ rename() �˼���\n";
                }
            }
            ///// State Log File ������λ
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, 'commit');
        } else {
            echo "�ǡ����١�������³2�˼���\n";
            exit();
        }
    }
}
////////// ��ư��ε������إå����ե����뤫����� & �����ޥ��������鵡��̾�����
$query = "select mac_no, siji_no, koutei, parts_no, plan_cnt, mac_name, csv_flg from equip_work_log2_header
        left outer join equip_machine_master2 using(mac_no)
        where work_flg is TRUE and csv_flg = 5 and survey = 'Y'
";
        // Netmoni=1&101 fwserver1=2 fwserver2=3 fwserver3=4 fwserver4=5 fwserver5=6 ...
$res_key = array();
if ( ($rows_key = getResult($query, $res_key)) >= 1) {      // �쥳���ɤ����ʾ夢���
    for ($i=0; $i<$rows_key; $i++) {                        // ���쥳���ɤ��Ľ���
        ///// insert �� �ѿ� �����
        $mac_no   = $res_key[$i]['mac_no'];
        $siji_no  = $res_key[$i]['siji_no'];
        $parts_no = $res_key[$i]['parts_no'];
        $koutei   = $res_key[$i]['koutei'];
        $plan_cnt = $res_key[$i]['plan_cnt'];
        $mac_name = $res_key[$i]['mac_name'];
        $csv_flg  = $res_key[$i]['csv_flg'];
        /////////// FTP��Υե������¸�ߥ����å�
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd1") != -1) {
            /////////// FTP Download
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd1", "/home/fws/usr/{$mac_no}-bcd1", FTP_ASCII)) {
                // echo 'FTP��Download�˼��Ԥ��ޤ�����';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd1")) {   // ��ե����뤬����к��
                unlink("/home/fws/{$mac_no}-bcd1");
            }
        }
        /////////// FTP��Υե������¸�ߥ����å�
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd2") != -1) {
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd2", "/home/fws/usr/{$mac_no}-bcd2", FTP_ASCII)) {
                // echo 'FTP��Download�˼��Ԥ��ޤ�����';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd2")) {   // ��ե����뤬����к��
                unlink("/home/fws/{$mac_no}-bcd2");
            }
        }
        /////////// FTP��Υե������¸�ߥ����å�
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd4") != -1) {
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd4", "/home/fws/usr/{$mac_no}-bcd4", FTP_ASCII)) {
                // echo 'FTP��Download�˼��Ԥ��ޤ�����';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd4")) {   // ��ե����뤬����к��
                unlink("/home/fws/{$mac_no}-bcd4");
            }
        }
        /////////// FTP��Υե������¸�ߥ����å�
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd8") != -1) {
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd8", "/home/fws/usr/{$mac_no}-bcd8", FTP_ASCII)) {
                // echo 'FTP��Download�˼��Ԥ��ޤ�����';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd8")) {   // ��ե����뤬����к��
                unlink("/home/fws/{$mac_no}-bcd8");
            }
        }
        ///// State File Check BCD�黻  ���ߤξ��֤����
        $bcd1_file = "/home/fws/{$mac_no}-bcd1";            // BCD�黻�ѥե�����̾����
        $bcd2_file = "/home/fws/{$mac_no}-bcd2";
        $bcd4_file = "/home/fws/{$mac_no}-bcd4";
        $bcd8_file = "/home/fws/{$mac_no}-bcd8";
        $state_bcd = 0;                                     // �����
        if (file_exists($bcd1_file)) {
            $state_bcd += 1;
        }
        if (file_exists($bcd2_file)) {
            $state_bcd += 2;
        }
        if (file_exists($bcd4_file)) {
            $state_bcd += 4;
        }
        if (file_exists($bcd8_file)) {
            $state_bcd += 8;
        }
        $query = "select state from equip_mac_state_log2 where equip_index2(mac_no,date_time) < '{$mac_no}99999999999999' order by equip_index2(mac_no,date_time) DESC limit 1";
        if (getUniResult($query, $state_p) > 0) {
            // ʪ�����椬���åȤ���Ƥ����
            $state = state_check($state_p, $state_bcd);   // ʪ�����ֿ���ȥ����å��ξ��֤Ȥ�Ŭ���ͤ�����å�
        } else {
            $state = 0;     // ���֥ǡ�����̵���Τ�̵�����Ÿ�off=0
        }
        /////////// FTP��Υե������¸�ߥ����å�
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}_work_cnt.log") != -1) {
            /////////// FTP rename
            if (ftp_rename($ftp_con, "/home/fws/usr/{$mac_no}_work_cnt.log", "/home/fws/usr/{$mac_no}_work_cnt.tmp")) {
                /////////// FTP Download
                if (ftp_get($ftp_con, "/home/fws/{$mac_no}_work_cnt.log", "/home/fws/usr/{$mac_no}_work_cnt.tmp", FTP_ASCII)) {
                    // echo 'FTP��Download���������ޤ�����';
                    ftp_delete($ftp_con, "/home/fws/usr/{$mac_no}_work_cnt.tmp");  // ��ե�����Ϻ��
                } else {
                    // echo 'FTP��Download�˼��Ԥ��ޤ�����';
                    `/bin/rm -f /home/fws/{$mac_no}_work_cnt.log`;
                }
            } else {
                // echo 'FTP��rename�˼��Ԥ��ޤ�����';
                `/bin/rm -f /home/fws/{$mac_no}_work_cnt.log`;
            }
        }
        /////////// begin �ȥ�󥶥�����󳫻�
        if ($con = db_connect()) {
            query_affected_trans($con, 'begin');
            ///// Counter File Check        ���ߤβù��������������
            $cnt_file = "/home/fws/{$mac_no}_work_cnt.log";     // �ǥ��쥯�ȥꡦ�ե�����̾����
            $cnt_temp = "/home/fws/{$mac_no}_work_cnt.tmp";     // Rename �ѥǥ��쥯�ȥꡦ�ե�����̾����
            if (file_exists($cnt_file)) {                       // Counter File �������
                if (rename($cnt_file, $cnt_temp)) {
                    $fp = fopen ($cnt_temp,'r');
                    $row  = 0;                                  // ���쥳����
                    $data = array();                            // ǯ����,����,�ù���
                    while ($data[$row] = fgetcsv ($fp, 50, ',')) {
                        if ($data[$row][0] != '') {
                            $row++;
                        }
                    }
                    if ($row >= 1) {            // Counter File �˥쥳���ɤ�����о��֤Ȳù��������
                        ///// ���ߤΥǡ����١����κǿ��쥳���ɤ������
                        $query = "
                            SELECT mac_state, work_cnt FROM equip_work_log2
                            WHERE
                                equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                                AND
                                equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                            ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC limit 1
                        ";
                        $res = array();
                        if ( ($rows = getResult($query, $res)) >= 1) {  // ����βù����˥ץ饹���ƽ����
                            for ($j=0; $j<$row; $j++) {
                                $work_cnt  = $res[0]['work_cnt'] + ($j + 1);      // Counter UP
                                $date_time = $data[$j][0] . ' ' . $data[$j][1];     // PostgreSQL��TIMESTAMP�����ѹ�
                                if ( ($state == 1) || ($state == 8) || ($state == 5) ) {     // ��ư��ž��̵�ͱ�ž�����ʼ���ΤϤ��ʤΤǥ����å�����ɬ�פʤ�����
                                    $mac_state = $state;
                                } else {
                                    $mac_state = 1;     // Counter���ʤ�Ǥ���Τ˼�ư�Ǥ�̵�������ʼ�Ǥ�ʤ����϶���Ū�˼�ư��ž�ˤ���
                                }
                                $query = "insert into equip_work_log2
                                    (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                    values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                                if (query_affected_trans($con, $query) <= 0) {
                                    $temp_msg = "FWS2 Counter�ν񤭹��ߤ˼��� �쥳����:$j : $mac_no : $date_time : $mac_state \n";
                                    $temp_msg .= $query;    // debug ��
                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    // query_affected_trans($con, 'rollback');         // transaction rollback
                                    // exit();
                                }
                            }
                        } else {                    // �ǡ����١��������Τ���̵���˽����
                            for ($j=0; $j<$row; $j++) {
                                $work_cnt  =  ($j + 1);   // ���ξ��Ϥ������㤦
                                $date_time = $data[$j][0] . ' ' . $data[$j][1];     // PostgreSQL��TIMESTAMP�����ѹ�
                                // $mac_state = $state;     // ���ξ��ϲ��Υǡ����������ǽ�����⤤����ʲ���ɬ��
                                if ( ($state == 1) || ($state == 8) || ($state == 5) ) {     // ��ư��ž��̵�ͱ�ž�����ʼ���ΤϤ��ʤΤǥ����å�����ɬ�פʤ�����
                                    $mac_state = $state;
                                } else {
                                    $mac_state = 1;     // Counter���ʤ�Ǥ���Τ˼�ư�Ǥ�̵�������ʼ�Ǥ�ʤ����϶���Ū�˼�ư��ž�ˤ���
                                }
                                $query = "insert into equip_work_log2
                                    (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                    values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                                if (query_affected_trans($con, $query) <= 0) {
                                    $temp_msg = "fwserver4 ���ǡ����١����ν񤭹��ߤ˼��� �쥳����:$j\n$query";
                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    // query_affected_trans($con, 'rollback');         // transaction rollback
                                    // exit();
                                } else {
                                    $query = "select str_timestamp from equip_work_log2_header
                                            where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
                                    ";
                                    if (getUniResTrs($con, $query, $str_timestamp) > 0) {
                                        $query = "select case when cast('$date_time' as TIMESTAMP) < cast('$str_timestamp' as TIMESTAMP)
                                                then (select 1) else (select 0) end";
                                        if (getUniResTrs($con, $query, $check_time) > 0) {
                                            if ($check_time == 1) {
                                                $query = "update equip_work_log2_header set str_timestamp='{$date_time}'
                                                        where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                                                if (query_affected_trans($con, $query) <= 0) {
                                                    $temp_msg = "���ǡ�����������Ӥ�Header��UPDATE�˼��� mac_no:{$mac_no}";
                                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } else {                    // Counter File �˥쥳���ɤ��ʤ��ΤǾ��֤Τߥ����å��������
                        ///// ���ߤΥǡ����١����κǿ��쥳���ɤ������
                        $query = "
                            SELECT mac_state, work_cnt FROM equip_work_log2
                            WHERE
                                equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                                AND
                                equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                            ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC limit 1
                        ";
                        $res = array();
                        if ( ($rows = getResult($query, $res)) >= 1) {  // �쥳���ɤ�����о��֤�����å�����
                            if ($res[0]['mac_state'] != $state) {       // ���֤��㤨�н����
                                $work_cnt  = $res[0]['work_cnt'];       // ����βù�����Ȥ�
                                // $date_time  = date('Ymd His.');             // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                                // $date_time .= substr(microtime(), 2, 6);    // TIMESTAMP(6)�ǽ�ʣ���������
                                $date_time  = date('Ymd His');          // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                                $mac_state = $state;
                                $query = "insert into equip_work_log2
                                    (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                    values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                                if (query_affected_trans($con, $query) <= 0) {
                                    $temp_msg = "fwserver4 Counter File �����뤬�쥳���ɤ�̵�����Υǡ����١����ν񤭹��ߤ˼��� mac_no={$mac_no}";
                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    // query_affected_trans($con, 'rollback');         // transaction rollback
                                    // exit();
                                }
                            }
                        } else {            // ���Τ���̵���˽����
                            $work_cnt  = 0;             // ���ξ��ϣ�
                            $date_time = date('Ymd His');   // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                            $mac_state = $state;
                            $query = "insert into equip_work_log2
                                (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                            if (query_affected_trans($con, $query) <= 0) {
                                $temp_msg = "fwserver4 Counter File �����뤬�쥳���ɤ�̵�����ν��ǡ����١����ν񤭹��ߤ˼��� mac_no={$mac_no}";
                                `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                // query_affected_trans($con, 'rollback');         // transaction rollback
                                // exit();
                            } else {
                                $query = "select str_timestamp from equip_work_log2_header
                                        where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
                                ";
                                if (getUniResTrs($con, $query, $str_timestamp) > 0) {
                                    $query = "select case when cast('$date_time' as TIMESTAMP) < cast('$str_timestamp' as TIMESTAMP)
                                            then (select 1) else (select 0) end";
                                    if (getUniResTrs($con, $query, $check_time) > 0) {
                                        if ($check_time == 1) {
                                            $query = "update equip_work_log2_header set str_timestamp='{$date_time}'
                                                    where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                                            if (query_affected_trans($con, $query) <= 0) {
                                                $temp_msg = "���ǡ�����������Ӥ�Header��UPDATE�˼��� mac_no:{$mac_no}";
                                                `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    unlink($cnt_temp);      // ����ե�����κ��
                } else {
                    echo "�����󥿡��ե������ rename() �˼���\n";
                }
            } else {                    // Counter File ���ʤ��ΤǾ��֤Τ߽����
                ///// ���ߤΥǡ����١����κǿ��쥳���ɤ������
                $query = "
                    SELECT mac_state, work_cnt FROM equip_work_log2
                    WHERE
                        equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                        AND
                        equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                    ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC limit 1
                ";
                $res = array();
                if ( ($rows = getResult($query, $res)) >= 1) {  // �쥳���ɤ�����о��֤�����å�����
                    if ($res[0]['mac_state'] != $state) {       // ���֤��㤨�н����
                        $work_cnt  = $res[0]['work_cnt'];       // ����βù����򤽤Τޤ޻Ȥ�
                        // $date_time  = date('Ymd His.');             // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                        // $date_time .= substr(microtime(), 2, 6);    // TIMESTAMP(6)�ǽ�ʣ���������
                        $date_time  = date('Ymd His');          // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                        $mac_state = $state;
                        $query = "insert into equip_work_log2
                            (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                            values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                        if (query_affected_trans($con, $query) <= 0) {
                            $temp_msg = date('Y-m-d H:i:s ') . "fwserver4 equip_work_log2 �ؾ��ֽ���ߤ˼���";
                            `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                            // query_affected_trans($con, 'rollback');         // transaction rollback
                            // exit();
                        }
                    }
                } else {        // ���Τ���̵���˽����
                    $work_cnt  = 0;             // ���ξ��ϣ�
                    $date_time = date('Ymd His');       // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                    $mac_state = $state;
                    $query = "insert into equip_work_log2
                        (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                        values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                    if (query_affected_trans($con, $query) <= 0) {
                        $temp_msg = date('Y-m-d H:i:s ') . "fwserver4 equip_work_log2 �ؽ��ξ��ֽ���ߤ˼���";
                        `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                        // query_affected_trans($con, 'rollback');         // transaction rollback
                        // exit();
                    } else {
                        $query = "select str_timestamp from equip_work_log2_header
                                where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
                        ";
                        if (getUniResTrs($con, $query, $str_timestamp) > 0) {
                            $query = "select case when cast('$date_time' as TIMESTAMP) < cast('$str_timestamp' as TIMESTAMP)
                                    then (select 1) else (select 0) end";
                            if (getUniResTrs($con, $query, $check_time) > 0) {
                                if ($check_time == 1) {
                                    $query = "update equip_work_log2_header set str_timestamp='{$date_time}'
                                            where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                                    if (query_affected_trans($con, $query) <= 0) {
                                        $temp_msg = "���ǡ�����������Ӥ�Header��UPDATE�˼��� mac_no:{$mac_no}";
                                        `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, 'commit');
        } else {
            echo "�ǡ����١�������³2�˼���\n";
            exit();
        }
    }
}
////////// FTP Close
if ($ftp_con) {
    ftp_close($ftp_con);
}
}   // $ftp_flg End





/****************************** FWS5���� *********************************/
$ftp_flg = true;
////////// FTP CONNECT
if ( !($ftp_con = ftp_connect(FWS5)) ) {   // �ƥ����Ѥ˥�ƥ��ɽ��
    $error_msg = date('Y-m-d H:i:s ') . "fwserver5��FTP����³�˼��Ԥ��ޤ�����";
    `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
    $ftp_flg = false;
} else {
    if (!@ftp_login($ftp_con, FWS_USER, FWS_PASS)) {      // 2004/03/29 ���顼��å�����������
        $error_msg = date('Y-m-d H:i:s ') . "FWS5��FTP��login�˼��Ԥ��ޤ�����";
        `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
        $ftp_flg = false;
    }
}

if ($ftp_flg) {
////////// ��ư��˴ط��ʤ� �����ޥ������ƻ뤹�뵡���ֹ����� (ʪ�����֤�24���ִƻ뤹�뤿��)
$query = "select mac_no, csv_flg from equip_machine_master2 where csv_flg = 6 and survey = 'Y'";
                        // Netmoni=1 fwserver1=2 fwserver2=3 fwserver3=4 fwserver4=5 ... , Net&fws1=101
$res_key = array();
if ( ($rows_key = getResult($query, $res_key)) >= 1) {      // �쥳���ɤ����ʾ夢���
    for ($i=0; $i<$rows_key; $i++) {                        // ���쥳���ɤ��Ľ���
        ///// insert �� �ѿ� �����
        $mac_no   = $res_key[$i]['mac_no'];
        $csv_flg  = $res_key[$i]['csv_flg'];
        /////////// FTP��Υե������¸�ߥ����å�
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}_work_state.log") != -1) {
            /////////// FTP rename
            if (ftp_rename($ftp_con, "/home/fws/usr/{$mac_no}_work_state.log", "/home/fws/usr/{$mac_no}_work_state.tmp")) {
                /////////// FTP Download
                if (ftp_get($ftp_con, "/home/fws/{$mac_no}_work_state.log", "/home/fws/usr/{$mac_no}_work_state.tmp", FTP_ASCII)) {
                    // echo 'FTP��Download���������ޤ�����';
                    ftp_delete($ftp_con, "/home/fws/usr/{$mac_no}_work_state.tmp");  // ��ե�����Ϻ��
                } else {
                    // echo 'FTP��Download�˼��Ԥ��ޤ�����';
                    `/bin/rm -f /home/fws/{$mac_no}_work_state.log`;
                }
            } else {
                // echo 'FTP��rename�˼��Ԥ��ޤ�����';
                `/bin/rm -f /home/fws/{$mac_no}_work_state.log`;
            }
        }
        /////////// begin �ȥ�󥶥�����󳫻�
        if ($con = db_connect()) {
            query_affected_trans($con, 'begin');
            ///// State Log File Check        ���ߤξ���(��ž�桦�����)����������� �Ÿ�OFF�����OK
            $state_file = "/home/fws/{$mac_no}_work_state.log";     // �ǥ��쥯�ȥꡦ�ե�����̾����
            $state_temp = "/home/fws/{$mac_no}_work_state.tmp";     // Rename �ѥǥ��쥯�ȥꡦ�ե�����̾����
            if (file_exists($state_file)) {                         // State Log File �������
                if (rename($state_file, $state_temp)) {
                    $fp = fopen ($state_temp,'r');
                    $row  = 0;                                  // ���쥳����
                    $data = array();                            // ǯ����,����,�ù���
                    while ($data[$row] = fgetcsv ($fp, 50, ',')) {
                        if ($data[$row][0] != '') {
                            $row++;
                        }
                    }
                    for ($j=0; $j<$row; $j++) {         // Status File �˥쥳���ɤ�����о��֤�����������
                        if ($data[$j][2] == 'auto') {   // �ե����뤫��ʪ�������ֹ�����
                            $state_p = 1;               // ��ž��(��ư��ž)
                        } elseif ($data[$j][2] == 'stop') {
                            $state_p = 3;               // �����
                        } elseif ($data[$j][2] == 'on') {
                            $state_p = 3;               // �Ÿ�ON�ξ���Default�ͤ������=3
                        } else {
                            $state_p = 0;               // �Ÿ�OFF "off" ��ͽ��
                        }
                        $date_time = $data[$j][0] . " " . $data[$j][1];     // timestamp�����ѿ����� ���
                        $state_name = equip_machine_state($mac_no, $state_p, $txt_color, $bg_color);
                        $query = "insert into equip_mac_state_log2
                            (mac_no, state, date_time, state_name, state_type)
                            values($mac_no, $state_p, '$date_time', '$state_name', $csv_flg)";
                        if (query_affected_trans($con, $query) <= 0) {
                            $temp_msg = "$query\n date_time:$date_time mac_no:$mac_no state:$state_p j=$j";
                            `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                            // query_affected_trans($con, "rollback");         // transaction rollback
                            // exit();
                        }
                    }
                    unlink($state_temp);        // ����ե�����κ��
                } else {
                    echo "���ơ������ե������ rename() �˼���\n";
                }
            }
            ///// State Log File ������λ
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, 'commit');
        } else {
            echo "�ǡ����١�������³2�˼���\n";
            exit();
        }
    }
}
////////// ��ư��ε������إå����ե����뤫����� & �����ޥ��������鵡��̾�����
$query = "select mac_no, siji_no, koutei, parts_no, plan_cnt, mac_name, csv_flg from equip_work_log2_header
        left outer join equip_machine_master2 using(mac_no)
        where work_flg is TRUE and csv_flg = 6 and survey = 'Y'
";
        // Netmoni=1&101 fwserver1=2 fwserver2=3 fwserver3=4 fwserver4=5 fwserver5=6 ...
$res_key = array();
if ( ($rows_key = getResult($query, $res_key)) >= 1) {      // �쥳���ɤ����ʾ夢���
    for ($i=0; $i<$rows_key; $i++) {                        // ���쥳���ɤ��Ľ���
        ///// insert �� �ѿ� �����
        $mac_no   = $res_key[$i]['mac_no'];
        $siji_no  = $res_key[$i]['siji_no'];
        $parts_no = $res_key[$i]['parts_no'];
        $koutei   = $res_key[$i]['koutei'];
        $plan_cnt = $res_key[$i]['plan_cnt'];
        $mac_name = $res_key[$i]['mac_name'];
        $csv_flg  = $res_key[$i]['csv_flg'];
        /////////// FTP��Υե������¸�ߥ����å�
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd1") != -1) {
            /////////// FTP Download
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd1", "/home/fws/usr/{$mac_no}-bcd1", FTP_ASCII)) {
                // echo 'FTP��Download�˼��Ԥ��ޤ�����';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd1")) {   // ��ե����뤬����к��
                unlink("/home/fws/{$mac_no}-bcd1");
            }
        }
        /////////// FTP��Υե������¸�ߥ����å�
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd2") != -1) {
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd2", "/home/fws/usr/{$mac_no}-bcd2", FTP_ASCII)) {
                // echo 'FTP��Download�˼��Ԥ��ޤ�����';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd2")) {   // ��ե����뤬����к��
                unlink("/home/fws/{$mac_no}-bcd2");
            }
        }
        /////////// FTP��Υե������¸�ߥ����å�
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd4") != -1) {
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd4", "/home/fws/usr/{$mac_no}-bcd4", FTP_ASCII)) {
                // echo 'FTP��Download�˼��Ԥ��ޤ�����';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd4")) {   // ��ե����뤬����к��
                unlink("/home/fws/{$mac_no}-bcd4");
            }
        }
        /////////// FTP��Υե������¸�ߥ����å�
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd8") != -1) {
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd8", "/home/fws/usr/{$mac_no}-bcd8", FTP_ASCII)) {
                // echo 'FTP��Download�˼��Ԥ��ޤ�����';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd8")) {   // ��ե����뤬����к��
                unlink("/home/fws/{$mac_no}-bcd8");
            }
        }
        ///// State File Check BCD�黻  ���ߤξ��֤����
        $bcd1_file = "/home/fws/{$mac_no}-bcd1";            // BCD�黻�ѥե�����̾����
        $bcd2_file = "/home/fws/{$mac_no}-bcd2";
        $bcd4_file = "/home/fws/{$mac_no}-bcd4";
        $bcd8_file = "/home/fws/{$mac_no}-bcd8";
        $state_bcd = 0;                                     // �����
        if (file_exists($bcd1_file)) {
            $state_bcd += 1;
        }
        if (file_exists($bcd2_file)) {
            $state_bcd += 2;
        }
        if (file_exists($bcd4_file)) {
            $state_bcd += 4;
        }
        if (file_exists($bcd8_file)) {
            $state_bcd += 8;
        }
        $query = "select state from equip_mac_state_log2 where equip_index2(mac_no,date_time) < '{$mac_no}99999999999999' order by equip_index2(mac_no,date_time) DESC limit 1";
        if (getUniResult($query, $state_p) > 0) {
            // ʪ�����椬���åȤ���Ƥ����
            $state = state_check($state_p, $state_bcd);   // ʪ�����ֿ���ȥ����å��ξ��֤Ȥ�Ŭ���ͤ�����å�
        } else {
            $state = 0;     // ���֥ǡ�����̵���Τ�̵�����Ÿ�off=0
        }
        /////////// FTP��Υե������¸�ߥ����å�
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}_work_cnt.log") != -1) {
            /////////// FTP rename
            if (ftp_rename($ftp_con, "/home/fws/usr/{$mac_no}_work_cnt.log", "/home/fws/usr/{$mac_no}_work_cnt.tmp")) {
                /////////// FTP Download
                if (ftp_get($ftp_con, "/home/fws/{$mac_no}_work_cnt.log", "/home/fws/usr/{$mac_no}_work_cnt.tmp", FTP_ASCII)) {
                    // echo 'FTP��Download���������ޤ�����';
                    ftp_delete($ftp_con, "/home/fws/usr/{$mac_no}_work_cnt.tmp");  // ��ե�����Ϻ��
                } else {
                    // echo 'FTP��Download�˼��Ԥ��ޤ�����';
                    `/bin/rm -f /home/fws/{$mac_no}_work_cnt.log`;
                }
            } else {
                // echo 'FTP��rename�˼��Ԥ��ޤ�����';
                `/bin/rm -f /home/fws/{$mac_no}_work_cnt.log`;
            }
        }
        /////////// begin �ȥ�󥶥�����󳫻�
        if ($con = db_connect()) {
            query_affected_trans($con, 'begin');
            ///// Counter File Check        ���ߤβù��������������
            $cnt_file = "/home/fws/{$mac_no}_work_cnt.log";     // �ǥ��쥯�ȥꡦ�ե�����̾����
            $cnt_temp = "/home/fws/{$mac_no}_work_cnt.tmp";     // Rename �ѥǥ��쥯�ȥꡦ�ե�����̾����
            if (file_exists($cnt_file)) {                       // Counter File �������
                if (rename($cnt_file, $cnt_temp)) {
                    $fp = fopen ($cnt_temp,'r');
                    $row  = 0;                                  // ���쥳����
                    $data = array();                            // ǯ����,����,�ù���
                    while ($data[$row] = fgetcsv ($fp, 50, ',')) {
                        if ($data[$row][0] != '') {
                            $row++;
                        }
                    }
                    if ($row >= 1) {            // Counter File �˥쥳���ɤ�����о��֤Ȳù��������
                        ///// ���ߤΥǡ����١����κǿ��쥳���ɤ������
                        $query = "
                            SELECT mac_state, work_cnt FROM equip_work_log2
                            WHERE
                                equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                                AND
                                equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                            ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC limit 1
                        ";
                        $res = array();
                        if ( ($rows = getResult($query, $res)) >= 1) {  // ����βù����˥ץ饹���ƽ����
                            for ($j=0; $j<$row; $j++) {
                                $work_cnt  = $res[0]['work_cnt'] + ($j + 1);      // Counter UP
                                $date_time = $data[$j][0] . ' ' . $data[$j][1];     // PostgreSQL��TIMESTAMP�����ѹ�
                                if ( ($state == 1) || ($state == 8) || ($state == 5) ) {     // ��ư��ž��̵�ͱ�ž�����ʼ���ΤϤ��ʤΤǥ����å�����ɬ�פʤ�����
                                    $mac_state = $state;
                                } else {
                                    $mac_state = 1;     // Counter���ʤ�Ǥ���Τ˼�ư�Ǥ�̵�������ʼ�Ǥ�ʤ����϶���Ū�˼�ư��ž�ˤ���
                                }
                                $query = "insert into equip_work_log2
                                    (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                    values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                                if (query_affected_trans($con, $query) <= 0) {
                                    $temp_msg = "FWS2 Counter�ν񤭹��ߤ˼��� �쥳����:$j : $mac_no : $date_time : $mac_state \n";
                                    $temp_msg .= $query;    // debug ��
                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    // query_affected_trans($con, 'rollback');         // transaction rollback
                                    // exit();
                                }
                            }
                        } else {                    // �ǡ����١��������Τ���̵���˽����
                            for ($j=0; $j<$row; $j++) {
                                $work_cnt  =  ($j + 1);   // ���ξ��Ϥ������㤦
                                $date_time = $data[$j][0] . ' ' . $data[$j][1];     // PostgreSQL��TIMESTAMP�����ѹ�
                                // $mac_state = $state;     // ���ξ��ϲ��Υǡ����������ǽ�����⤤����ʲ���ɬ��
                                if ( ($state == 1) || ($state == 8) || ($state == 5) ) {     // ��ư��ž��̵�ͱ�ž�����ʼ���ΤϤ��ʤΤǥ����å�����ɬ�פʤ�����
                                    $mac_state = $state;
                                } else {
                                    $mac_state = 1;     // Counter���ʤ�Ǥ���Τ˼�ư�Ǥ�̵�������ʼ�Ǥ�ʤ����϶���Ū�˼�ư��ž�ˤ���
                                }
                                $query = "insert into equip_work_log2
                                    (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                    values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                                if (query_affected_trans($con, $query) <= 0) {
                                    $temp_msg = "fwserver5 ���ǡ����١����ν񤭹��ߤ˼��� �쥳����:$j\n$query";
                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    // query_affected_trans($con, 'rollback');         // transaction rollback
                                    // exit();
                                } else {
                                    $query = "select str_timestamp from equip_work_log2_header
                                            where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
                                    ";
                                    if (getUniResTrs($con, $query, $str_timestamp) > 0) {
                                        $query = "select case when cast('$date_time' as TIMESTAMP) < cast('$str_timestamp' as TIMESTAMP)
                                                then (select 1) else (select 0) end";
                                        if (getUniResTrs($con, $query, $check_time) > 0) {
                                            if ($check_time == 1) {
                                                $query = "update equip_work_log2_header set str_timestamp='{$date_time}'
                                                        where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                                                if (query_affected_trans($con, $query) <= 0) {
                                                    $temp_msg = "���ǡ�����������Ӥ�Header��UPDATE�˼��� mac_no:{$mac_no}";
                                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } else {                    // Counter File �˥쥳���ɤ��ʤ��ΤǾ��֤Τߥ����å��������
                        ///// ���ߤΥǡ����١����κǿ��쥳���ɤ������
                        $query = "
                            SELECT mac_state, work_cnt FROM equip_work_log2
                            WHERE
                                equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                                AND
                                equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                            ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC limit 1
                        ";
                        $res = array();
                        if ( ($rows = getResult($query, $res)) >= 1) {  // �쥳���ɤ�����о��֤�����å�����
                            if ($res[0]['mac_state'] != $state) {       // ���֤��㤨�н����
                                $work_cnt  = $res[0]['work_cnt'];       // ����βù�����Ȥ�
                                // $date_time  = date('Ymd His.');             // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                                // $date_time .= substr(microtime(), 2, 6);    // TIMESTAMP(6)�ǽ�ʣ���������
                                $date_time  = date('Ymd His');          // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                                $mac_state = $state;
                                $query = "insert into equip_work_log2
                                    (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                    values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                                if (query_affected_trans($con, $query) <= 0) {
                                    $temp_msg = "fwserver5 Counter File �����뤬�쥳���ɤ�̵�����Υǡ����١����ν񤭹��ߤ˼��� mac_no={$mac_no}";
                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    // query_affected_trans($con, 'rollback');         // transaction rollback
                                    // exit();
                                }
                            }
                        } else {            // ���Τ���̵���˽����
                            $work_cnt  = 0;             // ���ξ��ϣ�
                            $date_time = date('Ymd His');   // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                            $mac_state = $state;
                            $query = "insert into equip_work_log2
                                (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                            if (query_affected_trans($con, $query) <= 0) {
                                $temp_msg = "fwserver5 Counter File �����뤬�쥳���ɤ�̵�����ν��ǡ����١����ν񤭹��ߤ˼��� mac_no={$mac_no}";
                                `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                // query_affected_trans($con, 'rollback');         // transaction rollback
                                // exit();
                            } else {
                                $query = "select str_timestamp from equip_work_log2_header
                                        where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
                                ";
                                if (getUniResTrs($con, $query, $str_timestamp) > 0) {
                                    $query = "select case when cast('$date_time' as TIMESTAMP) < cast('$str_timestamp' as TIMESTAMP)
                                            then (select 1) else (select 0) end";
                                    if (getUniResTrs($con, $query, $check_time) > 0) {
                                        if ($check_time == 1) {
                                            $query = "update equip_work_log2_header set str_timestamp='{$date_time}'
                                                    where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                                            if (query_affected_trans($con, $query) <= 0) {
                                                $temp_msg = "���ǡ�����������Ӥ�Header��UPDATE�˼��� mac_no:{$mac_no}";
                                                `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    unlink($cnt_temp);      // ����ե�����κ��
                } else {
                    echo "�����󥿡��ե������ rename() �˼���\n";
                }
            } else {                    // Counter File ���ʤ��ΤǾ��֤Τ߽����
                ///// ���ߤΥǡ����١����κǿ��쥳���ɤ������
                $query = "
                    SELECT mac_state, work_cnt FROM equip_work_log2
                    WHERE
                        equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                        AND
                        equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                    ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC limit 1
                ";
                $res = array();
                if ( ($rows = getResult($query, $res)) >= 1) {  // �쥳���ɤ�����о��֤�����å�����
                    if ($res[0]['mac_state'] != $state) {       // ���֤��㤨�н����
                        $work_cnt  = $res[0]['work_cnt'];       // ����βù����򤽤Τޤ޻Ȥ�
                        // $date_time  = date('Ymd His.');             // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                        // $date_time .= substr(microtime(), 2, 6);    // TIMESTAMP(6)�ǽ�ʣ���������
                        $date_time  = date('Ymd His');          // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                        $mac_state = $state;
                        $query = "insert into equip_work_log2
                            (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                            values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                        if (query_affected_trans($con, $query) <= 0) {
                            $temp_msg = date('Y-m-d H:i:s ') . "fwserver5 equip_work_log2 �ؾ��ֽ���ߤ˼���";
                            `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                            // query_affected_trans($con, 'rollback');         // transaction rollback
                            // exit();
                        }
                    }
                } else {        // ���Τ���̵���˽����
                    $work_cnt  = 0;             // ���ξ��ϣ�
                    $date_time = date('Ymd His');       // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                    $mac_state = $state;
                    $query = "insert into equip_work_log2
                        (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                        values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                    if (query_affected_trans($con, $query) <= 0) {
                        $temp_msg = date('Y-m-d H:i:s ') . "fwserver5 equip_work_log2 �ؽ��ξ��ֽ���ߤ˼���";
                        `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                        // query_affected_trans($con, 'rollback');         // transaction rollback
                        // exit();
                    } else {
                        $query = "select str_timestamp from equip_work_log2_header
                                where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
                        ";
                        if (getUniResTrs($con, $query, $str_timestamp) > 0) {
                            $query = "select case when cast('$date_time' as TIMESTAMP) < cast('$str_timestamp' as TIMESTAMP)
                                    then (select 1) else (select 0) end";
                            if (getUniResTrs($con, $query, $check_time) > 0) {
                                if ($check_time == 1) {
                                    $query = "update equip_work_log2_header set str_timestamp='{$date_time}'
                                            where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                                    if (query_affected_trans($con, $query) <= 0) {
                                        $temp_msg = "���ǡ�����������Ӥ�Header��UPDATE�˼��� mac_no:{$mac_no}";
                                        `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, 'commit');
        } else {
            echo "�ǡ����١�������³2�˼���\n";
            exit();
        }
    }
}
////////// FTP Close
if ($ftp_con) {
    ftp_close($ftp_con);
}
}   // $ftp_flg End





unlink($check_file);    // �����å��ѥե��������
exit();
?>
