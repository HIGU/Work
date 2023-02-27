<?php
//////////////////////////////////////////////////////////////////////////////
// ���������� ����2 (������) �ե��󥯥å���� �ե�����                       //
// Copyright (C) 2002-2018 Kazuhiro Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/02/15 Created  ���������ط� equip_function.php                      //
// 2003/06/19 equip_header_to_csv()����¼α�ʳ����б�                       //
// 2003/07/01 �����ξ��֤�ʸ������֤� �����꡼�����å��� �ɲ�            //
// 2003/07/02 equip_state_r()�Σ���̵�ͱ�ž���֤��ѹ� Netmoni�ˤ��碌��   //
//            equip_working_chk()�� Netmoni �ʳ����б� equip_header�����   //
// 2003/07/03 CLI�Ǥ�Function��ͭ���뤿��require_once()�����л�����ѹ�   //
// 2003/07/07 state_check()�����꡼�����å���Ŭ�������å����ʥ��å�     //
// 2004/03/05 �ơ��֥���ǤذܹԤΰ١��ƴؿ����ѹ� equip_work_log2��      //
// 2004/06/19 netmoni�ط��� FTP ž�������� equip_header_to_csv()            //
// 2004/06/24 header file ������ equip_working_chk() equip_header_field()   //
// 2004/07/12 state_check_netmoni() Netmoni & FWS ���������� �����å�����   //
// 2004/07/14 equip_header_to_csv()��masterst�ʳ���FTPž�����ʤ��������ѹ�  //
// 2004/07/21 state_check()ʪ�����椬�Ÿ�OFF�Ǥ��ʼ����Ʊ�ͤ˸ξ㽤�����ɲ�//
// 2004/07/26 equip_machine_state()��tnk_auto_log�ǻ��ѤΤ���$_SESSION��chk //
// 2004/10/22 state_check_netmoni()��ftp_close()��ȴ���Ƥ����Τ�����        //
// 2004/11/29 FWS3��7���ɲ�(1�����5����ޤ�)                               //
// 2004/12/14 yaxis_min()��40000Ķ���ξ�������ɲ�                          //
// 2005/02/15 state_check_netmoni()��ftp_connect()/ftp_login�Ǽ��Ԥ�������//
//            sleep(2)���ô��ٱ䤵������å��ɲ�                          //
// 2005/02/16 state_check_netmoni() �ѥ�᡼�������ɲ� $ftp_con(���ȥ꡼��) //
// 2006/03/02 yaxis_min()��1,700,000Ķ���ξ����ɲ� default=500,000        //
// 2006/03/03 del_equip_header_work()��equip_work_log2 WHERE����Ŭ��      //
// 2006/03/27 break_equip_header()��         ��                             //
// 2006/06/12 state_check()���������Ǥ��ȵ��������褦���ѹ�           //
// 2007/06/27 realpath(dirname(__FILE__)) ���ѹ�                            //
// 2018/05/18 ��������ɲá������ɣ�����Ͽ����Ū��7���ѹ�            ��ë //
// 2018/12/25 �������﫤�SUS��ʬΥ���塹�ΰ١�                      ��ë //
//////////////////////////////////////////////////////////////////////////////
// session_start();     // �ƽи����������Ƥ��������ǰ�Τ��� ��������ϥ��顼
require_once (realpath(dirname(__FILE__)) . "/../function.php");    // ������define.php pgsql.php �� require()���Ƥ��롣

// ����������Netmoni�� ��Ư���ϤΥإå����ե����롣
define('EQUIP_INDEX',  '/home/netmoni4/data/input.csv');
// ����������Netmoni�� ���ǡ�����¸�ǥ��쥯�ȥ�
define('EQUIP_LOG_DIR', '/home/netmoni4/data/');
// ����������Netmoni�� ���Хå����å���¸�ǥ��쥯�ȥ�
define("EQUIP_BACKUP_DIR","/home/netmoni4/data/backup/");
// �����ξ��֣������������ ��α��NC Netmoni ��     0:�Ÿ�OFF 1:��ư��ž 2:���顼�� 3:����� �϶���
define("M_STAT_MAX_NO",15);
// �����ξ��֣������������ �����꡼�����å���        ��
define("R_STAT_MAX_NO",11);

// FTP�ط������    Netmoni
define('NET_HOST', '10.1.3.145');           // �������åȥۥ���
define('NET_USER', 'netmoni4');             // ��³�桼����̾
define('NET_PASS', 'netmoni');              // �ѥ����
define('REMOTE_INDEX', 'input.csv');        // FTPž�����ѹ��Τ����ɲ�
define('LOCAL_NAME', '/home/netmoni4/data/input.csv');   // ������Υե�ѥ��ե�����̾
// define('REMOTE_LOG', '134924268CP01037-501500.csv');  �����ֹ�+�ؼ��ֹ�+�����ֹ�+����+�ײ��

// FTP�ط������    fws1  fwserver1.tnk.co.jp
define('FWS1', '10.1.3.41');                // 7���쿿�
//                  fws2  fwserver2.tnk.co.jp
define('FWS2', '10.1.3.42');                // 7���쿿�
//                  fws3  fwserver3.tnk.co.jp
define('FWS3', '10.1.3.43');                // 7����SUS
//                  fws4  fwserver4.tnk.co.jp
define('FWS4', '10.1.3.44');                // 4����
//                  fws5  fwserver5.tnk.co.jp
define('FWS5', '10.1.3.45');                // 1����
//                  fws6  fwserver6.tnk.co.jp
define('FWS6', '10.1.3.46');                // 5����
//                  fws7  fwserver7.tnk.co.jp
define('FWS7', '10.1.3.47');                // 5����

define('FWS_USER', 'fws');                  // ��³�桼����̾
define('FWS_PASS', 'fws');                  // �ѥ����
define('REMOTE_DIR', '/home/fws/usr/');     // ��⡼�ȥǥ��쥯�ȥ�
define('LOCAL_DIR',  '/home/fws/');         // ������ǥ��쥯�ȥ�
///// file �ϰʲ��ν�
// file=(mac_no)_work_state.log
// file=(mac_no)-bcd1.log
// file=(mac_no)-bcd2.log
// file=(mac_no)-bcd4.log
// file=(mac_no)-bcd8.log
// file=(mac_no)_work_cnt.log

/////// ʪ������ǥ����꡼�����å���Ŭ����Ƚ�Ǥ��������ֹ���֤�
/////// Netmoni�б��� 0:�Ÿ�OFF 1:��ư��ž 2:���顼�� 3:����� ��FWS�����ȶ�����ʬ��
/////// ���Ѥ��Ƹ�ϥ����꡼�����å���Ȥ�
/////// �ʲ��ϥϡ��ɿ��椬���Τ˽��Ϥ���Ƥ����������Ȥ���
/////// 2005/02/16 �ѥ�᡼�������ɲ� $ftp_con(FTP���ȥ꡼��)
function state_check_netmoni($ftp_con, $state_p, $mac_no)
{
    $query = "select csv_flg from equip_machine_master2 where mac_no=$mac_no limit 1";
    getUniResult($query, $csv_flg);
    switch ($csv_flg) {
    case 101:         // 101=Net&FWS �б���
    // case 102:      // ���������������б�
    // case 103:      // ���������������б�
        break;
    default:
        return $state_p;
    }
    
    if ($ftp_con == false) {
        return $state_p;    // FTP��³�Ǥ��ʤ����(����Netmoniñ�Ȥξ��)��ʪ������򤽤Τޤ��֤�
    } else {
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
        $state_bcd = 0;                                     // ����� = �Ÿ�OFF
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
    }
    ///// ʪ������ȥ����꡼�����å��Ȥξ������å�
    if ($state_p == 0) {            // �Ÿ�OFF(ʪ������)
        switch ($state_bcd) {
        case (5):                   // �ʼ���
            return(5);
            break;
        default:                    // ����¾���Ÿ�OFF
            return(0);
        }
    } elseif ($state_p == 1) {      // ��ž��(ʪ������)
        switch ($state_bcd) {
        case (1):                   // ��ư��ž
            return(1);
            break;
        case (4):                   // �ȵ���
            return(4);
            break;
        case (5):                   // �ʼ���
            return(5);
            break;
        case (8):                   // ̵�ͱ�ž
            return(8);
            break;
        default:                    // ����¾�ϼ�ư��ž
            return(1);
        }
    } elseif ($state_p == 2) {      // ���顼��(ʪ������)
        switch ($state_bcd) {
        case (2):                   // ���顼��
            return(2);
            break;
        case (5):                   // �ʼ���
            return(5);
            break;
        case (6):                   // �ξ㽤��
            return(6);
            break;
        case (7):                   // �϶��
            return(7);
            break;
        case (10):                  // �ʼ��Ԥ�
            return(10);
            break;
        case (11):                  // �����Ԥ�
            return(11);
            break;
        default:                    // ����¾�������
            return(3);
        }
    } elseif ($state_p == 3) {      // �����(ʪ������)
        switch ($state_bcd) {
        case (2):                   // �������϶��Ԥ�
            return(2);
            break;
        case (3):                   // �����
            return(3);
            break;
        case (4):                   // �ȵ���
            return(4);
            break;
        case (5):                   // �ʼ���
            return(5);
            break;
        case (6):                   // �ξ㽤��
            return(6);
            break;
        case (7):                   // �϶��
            return(7);
            break;
        case (10):                  // �ʼ��Ԥ�
            return(10);
            break;
        case (11):                  // �����Ԥ�
            return(11);
            break;
        case (9):                   // ����
            return(9);
            break;
        default:                    // ����¾�������
            return(3);
        }
    } else {                        // ����¾�μ�ư�ǽФ�ʪ������� Net��ư����λ
        return(9);                  // ����(̵��ʤȤ���)
    }
}


/////// ʪ������ǥ����꡼�����å���Ŭ����Ƚ�Ǥ��������ֹ���֤�
/////// �ʲ��ϥϡ��ɿ��椬���Τ˽��Ϥ���Ƥ����������Ȥ���
function state_check($state_p, $state_bcd)
{
    if ($state_p == 1) {            // ��ž��(ʪ������)
        switch ($state_bcd) {
        case (1):                   // ��ư��ž
            return(1);
            break;
        case (4):                   // �ȵ���
            return(4);
            break;
        case (5):                   // �ʼ���
            return(5);
            break;
        case (8):                   // ̵�ͱ�ž
            return(8);
            break;
        default:                    // ����¾�ϼ�ư��ž
            return(1);
        }
    } elseif ($state_p == 3) {      // �����(ʪ������)
        switch ($state_bcd) {
        case (3):                   // �����
            return(3);
            break;
        case (2):                   // ���顼��(ʪ�����椬̵����������꡼�����å��Ǽ��)
            return(2);
            break;
        case (4):                   // �ȵ���
            return(4);
            break;
        case (5):                   // �ʼ���
            return(5);
            break;
        case (6):                   // �ξ㽤��
            return(6);
            break;
        case (7):                   // �϶��
            return(7);
            break;
        case (9):                   // ����
            return(9);
            break;
        case (10):                   // �ʼ��Ԥ�
            return(10);
            break;
        case (11):                   // �����Ԥ�
            return(11);
            break;
        default:                    // ����¾�������
            return(3);
        }
    } elseif ($state_p == 2) {      // ���顼��(ʪ������)���ߤޤ�����Ϥʤ�(ͽ��)
        switch ($state_bcd) {
        case (2):                   // ���顼��
            return(2);
            break;
        case (5):                   // �ʼ���
            return(5);
            break;
        case (6):                   // �ξ㽤��
            return(6);
            break;
        case (7):                   // �϶��
            return(7);
            break;
        default:
            return(3);
        }
    } else {                        // �Ÿ�OFF(ʪ������)
        switch ($state_bcd) {
        case (5):                   // �ʼ���
            return(5);
            break;
        case (6):                   // �ξ㽤��
            return(6);
            break;
        default:                    // ����¾���Ÿ�OFF
            return(0);
        }
    }
}


///// ������Ѥλ�����ǡ��� ����ץ�󥰥��������ꡣ
function sampling($log_cnt)
{
    switch (TRUE) {             ////////// true �ˤ����caseʬ����������������뎡
    case ($log_cnt <= 10):
        return(10);             // 10��
        break;
    case ($log_cnt <= 60):
        return(30);             // 30��
        break;
    case ($log_cnt <= 100):
        return(60);             //  1ʬ
        break;
    case ($log_cnt <= 200):
        return(120);            //  2ʬ
        break;
    case ($log_cnt <= 2000):
        return(600);            // 10ʬ
        break;
    case ($log_cnt <= 4000):
        return(3600);           // 60ʬ
        break;
    case ($log_cnt <= 8000):
        return(7200);           // 120ʬ
        break;
    case ($log_cnt <= 16000):
        return(14400);           // 240ʬ�Υ���ץ�� 2003/07/11 �ѹ�
        break;
    default:
        return(28800);           // 480ʬ�Υ���ץ�� 2003/07/11 �ɲ�
    }
}


// ������Ѥ�Y��min����ؿ� max����ꤷ�Ƽ�ư��min���֤���
// JpGraph �ΥС���������Ĵ����ɬ��
function yaxis_min($max_data)
{
    switch (TRUE) {                       // true �ˤ����caseʬ����������������뎡
    case ($max_data <= 0):
        return(-5);
        break;
    case ($max_data <= 5):
        return(-1);
        break;
    case ($max_data <= 10):
        return(-2);
        break;
    case ($max_data <= 40):
        return(-10);
        break;
    case ($max_data <= 150):
        return(-20);
        break;
    case ($max_data < 350):
        return(-50);
        break;
    case ($max_data < 700):
        return(-100);
        break;
    case ($max_data < 1500):
        return(-200);
        break;
    case ($max_data < 3800):
        return(-500);                   // ���ꥸ�ʥ�� -400 ��Ĵ���Ѥ� �礭��ˤ��Ƥ���������ʤ��褦�Ǥ���
        break;
    case ($max_data < 8000):
        return(-1000);                  // 2003/07/11 8000 ��Ķ�����Τ��ɲ�
        break;
    case ($max_data < 13000):
        return(-2000);                  // 2003/07/19 16000 ��Ķ�����Τ��ɲ�
        break;
    case ($max_data < 15000):
        return(-4000);                  // 2005/02/23 �ɲ�
        break;
    case ($max_data < 40000):
        return(-5000);                  // 2004/12/14 40000 ��Ķ�����Τ��ɲ�
        break;
    case ($max_data < 60000):
        return(-10000);                 // 2004/12/17 60000 ��Ķ�����Τ��ɲ�
        break;
    case ($max_data < 100000):
        return(-20000);                 // 2004/12/17 ͽ¬���ɲ�
        break;
    case ($max_data < 150000):
        return(-40000);                 // 2005/01/25 �ɲ�
        break;
    case ($max_data < 300000):
        return(-50000);                 // 2005/03/11 �ɲ�
        break;
    case ($max_data < 800000):
        return(-100000);                // 2005/07/22 �ɲ�
        break;
    case ($max_data < 1700000):
        return(-200000);                // 2006/03/02 �ɲ�
        break;
    default:
        return(-500000);                // 2006/03/02 �ѹ�
    }
}


// ���������β�Ư���ϤΥإå����ե����롣�ƥ�����
define("EQUIP_TEST","/home/netmoni4/data/test.csv");

// ��¼α�ΣΣ��Ѥ�CSV File �� ���Ϥ��� equip_header ��work_flg IS TRUE ��end_timestamp IS NULL ��
//   equip_machine_master �� csv_flg = 1 or csv_flg = 101(Net&FWS) ������å����뎡 2004/07/13 Add k.kobayashi
function equip_header_to_csv()
{
    $query = "select mac_no
                    ,siji_no
                    ,parts_no
                    ,koutei
                    ,plan_cnt
                from 
                    equip_work_log2_header
                left outer join
                    equip_machine_master2
                using(mac_no)
                where
                    work_flg IS TRUE and
                    end_timestamp IS NULL and
                    (csv_flg = 1 or csv_flg = 101)
                order by str_timestamp
            ";
    $res = array();
    if ( ($rows=getResult($query,$res)) >= 0) {     // �ǡ����١����Υإå������ CSV ����
        if ( ($fp=fopen(EQUIP_INDEX,"w")) ) {;
            for ($i=0; $i<$rows; $i++) {
                $data = NULL;
                for ($f=0; $f<5; $f++) {
                    $data .= $res[$i][$f];
                    if ($f != 4) {
                        $data .= ",";
                    } else {
                        // $data .= "\r\n";
                        $data .= "\n";  // ������������ѹ� FTP�����ؤ�������
                    }
                }
                if (fwrite($fp,$data) == -1) {
                    $_SESSION['s_sysmsg'] = "CSV File Write Error No:$r";   // debug��
                    fclose($fp);
                    return FALSE;       // CSV �ե�����ν���߼���
                }
            }
            fclose($fp);
        } else {
            $_SESSION['s_sysmsg'] = "CSV File Open Error";                  // debug��
            return FALSE;   // CSV �ե�����Υ����ץ�˼���
        }
    } else {
        $_SESSION['s_sysmsg'] = 'equip_machine_master SQL Error';           // debug��
        return FALSE;   // �����ޥ��������ɹ�����
    }
    // $_SESSION['s_sysmsg'] = "CSV �ե�����ν��������";           // debug��
    ////////// testerst ���Υƥ����Ѥ��б����뤿��ºݤ� FTPž���Ϥ��ʤ��ǽ�λ����
    if ($_SERVER['SERVER_ADDR'] != '10.1.3.252') {      // masterst(www.tnk.co.jp)�ʳ��ϼ¹Ԥ��ʤ�
        return TRUE;
    }
    ///////// FTP ž�� ����
    // ���ͥ���������(FTP��³�Υ����ץ�)
    if ($ftp_stream = ftp_connect(NET_HOST)) {
        if (ftp_login($ftp_stream, NET_USER, NET_PASS)) {
            ///// Netmoni Server �إ���ȥ���ե�����(�̿����ǻؼ�)������
            if (ftp_put($ftp_stream, REMOTE_INDEX, LOCAL_NAME, FTP_ASCII)) {
                ///// �������ν����ϸ��ߤʤ�
            } else {
                $_SESSION['s_sysmsg'] = REMOTE_INDEX . ' upload Error';
            }
        } else {
            $_SESSION['s_sysmsg'] = 'ftp_login() Error';
        }
        ftp_close($ftp_stream);
    } else {
        $_SESSION['s_sysmsg'] = 'ftp_connect() error';
    }
    return TRUE;    // CSV file ���� OK & FTP ž�� OK
}


// ��¤�ݤ�ͽ��ײ��걿ž���Ͻ��� (Transaction����)
function trans_equip_plan_to_start($m_no,$s_no,$b_no,$k_no,$p_no){
    $update_qry = "update equip_plan set plan_flg=FALSE 
        where mac_no='$m_no' and siji_no='$s_no' and buhin_no='$b_no' and koutei='$k_no'"; 
    $str_date = date('Y-m-d H:i:s');
    $insert_qry = "insert into equip_work_log2_header (mac_no, siji_no, koutei, parts_no, plan_no, str_timestamp,work_flg) 
            values($m_no, $s_no, $k_no, '$b_no', $p_no, '$str_date', TRUE)";
    if (funcConnect()) {
        execQuery('begin');
        if(execQuery($update_qry) >= 0) {
            if(execQuery($insert_qry) >= 0) {
                execQuery('commit');
                disConnectDB();
                return true;
            } else {
                execQuery('rollback');
                disConnectDB();
                $error_msg = date('Y/m/d H:i:s', mktime());
                $error_msg .= "-execQuery: $insert_qry";
                `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
            }
        } else {
            execQuery('rollback');
            disConnectDB();
            $error_msg = date('Y/m/d H:i:s', mktime());
            $error_msg .= "-execQuery: $update_qry";
            `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
        }
    } else {
        $error_msg = date('Y/m/d H:i:s', mktime());
        $error_msg .= "-funcConnect: $update_qry";
        `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
    }
    return false;
}


// ��¤�ݤ�ͽ��ײ� ��Ͽ
function add_equip_plan($m_no,$s_no,$b_no,$k_no,$p_no,$str_d,$end_d){
    $rec_date_time = mktime();
    $insert_qry = "insert into equip_plan (mac_no, siji_no, buhin_no, koutei, plan_su, plan_str,plan_end,rec_date) 
            values('$m_no','$s_no','$b_no','$k_no',$p_no,$str_d,$end_d,$rec_date_time)";
    if (funcConnect()) {
        execQuery("begin");
        if (execQuery($insert_qry) >= 0) {
            execQuery("commit");
            disConnectDB();
            return true;
        } else {
            execQuery("rollback");
            disConnectDB();
            $error_msg = date("Y/m/d H:i:s",mktime());
            $error_msg .= "-execQuery: $insert_qry";
            `echo "$error_msg" >> /tmp/equipment_write_error.log`;
        }
    } else {
        $error_msg = date("Y/m/d H:i:s",mktime());
        $error_msg .= "-funcConnect: $insert_qry";
        `echo "$error_msg" >> /tmp/equipment_write_error.log`;
    }
    return false;
}

// ��¤�ݤ�ͽ��ײ� �ǡ�������
function chg_equip_plan($pm_no,$ps_no,$pb_no,$pk_no,$m_no,$s_no,$b_no,$k_no,$p_no,$s_date,$e_date){
    $update_qry = "update equip_plan set mac_no='$m_no', siji_no='$s_no', buhin_no='$b_no',koutei='$k_no', plan_su=$p_no,plan_str=$s_date,plan_end=$e_date 
        where mac_no='$pm_no' and siji_no='$ps_no' and buhin_no='$pb_no' and koutei='$pk_no'"; 
    if (funcConnect()) {
        execQuery("begin");
        if (execQuery($update_qry) >= 0) {
            execQuery("commit");
            disConnectDB();
            return true;
        } else {
            execQuery("rollback");
            disConnectDB();
            $error_msg = date("Y/m/d H:i:s",mktime());
            $error_msg .= "-execQuery: $update_qry";
            `echo "$error_msg" >> /tmp/equipment_write_error.log`;
        }
    } else {
        $error_msg = date("Y/m/d H:i:s",mktime());
        $error_msg .= "-funcConnect: $update_qry";
        `echo "$error_msg" >> /tmp/equipment_write_error.log`;
    }
    return false;
}

// ��¤�ݤ�ͽ��ײ�ǡ������
function del_equip_plan($kikaino,$seizousiji,$buhinno,$kouteino){
    $delete_qry = "delete from equip_plan where mac_no='$kikaino' and siji_no='$seizousiji' and 
            buhin_no='$buhinno' and koutei='$kouteino'";
    if (funcConnect()) {
        execQuery("begin");
        if (execQuery($delete_qry) >= 0) {
            execQuery("commit");
            disConnectDB();
            return true;
        } else {
            execQuery("rollback");
            disConnectDB();
            $error_msg = date("Y/m/d H:i:s",mktime());
            $error_msg .= "-execQuery: $delete_qry";
            `echo "$error_msg" >> /tmp/equipment_write_error.log`;
        }
    } else {
        $error_msg = date("Y/m/d H:i:s",mktime());
        $error_msg .= "-funcConnect: $delete_qry";
        `echo "$error_msg" >> /tmp/equipment_write_error.log`;
    }
    return false;
}



// ��¤�ݤε�����ž ��λ�ؼ� equip_header ��end_timestamp�˴�λ���ֽ���� work_flg �� FALSE ��
function end_equip_header($m_no, $s_no, $b_no, $k_no, $jisseki)
{
    $end_timestamp = date('Y-m-d H:i:s');
    $update_qry = "update equip_work_log2_header set end_timestamp='$end_timestamp', work_flg=FALSE, jisseki={$jisseki}
        where mac_no={$m_no} and siji_no={$s_no} and koutei={$k_no}"; 
    if (funcConnect()) {
        execQuery('begin');
        if (execQuery($update_qry) >= 0) {
            execQuery('commit');
            disConnectDB();
        } else {
            execQuery('rollback');
            disConnectDB();
            $error_msg = date('Y/m/d H:i:s', mktime());
            $error_msg .= "-execQuery:��λ:$update_qry";
            `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
        }
    } else {
        $error_msg = date('Y/m/d H:i:s', mktime());
        $error_msg .= "-funcConnect:��λ:$update_qry";
        `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
    }
}



// ��¤�ݤε�����ž ����/�Ƴ����� �إå����ե�������� work_flg IS FALSE(����) TRUE(�Ƴ�)
function break_equip_header($m_no, $s_no, $b_no, $k_no, $flag)
{
    if ($flag == FALSE) {
        ///// �����ޥ������� csv_flg ���� Netmoni/�����꡼�����å������μ���
        $query = "select mac_name, csv_flg from equip_machine_master2 where mac_no={$m_no} limit 1";
        $res = array();
        if (($rows=getResult($query,$res))>=1) {      // �����ޥ��������鵡��̾���������
            $name = substr($res[0][0],0,10);
            $csv_flg = $res[0][1];
        } else {
            $name = "     ";
            $csv_flg = 0;       // 1�ʳ��ϥ����꡼�����å������Ȥ���
        }
        
        // equip_work_log �����ǥǡ�����񤭹��ि��ǿ��ǡ������ǧ����
            // ��SQL = where mac_no={$m_no} and siji_no={$s_no} and koutei={$k_no} and mac_state<>0 order by date_time DESC limit 1";
        $query = "select work_cnt from equip_work_log2
            WHERE
            equip_index(mac_no, siji_no, koutei, date_time) > '{$m_no}{$s_no}{$k_no}00000000000000'
            AND
            equip_index(mac_no, siji_no, koutei, date_time) < '{$m_no}{$s_no}{$k_no}99999999999999'
            AND
            mac_state != 0
            ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC
            LIMIT 1
        ";
        $res=array();
        if (($rows=getResult($query,$res))>=1) {      // �ǿ��ǡ�������������Υǡ����򥻥åȤ���
            $pre_cnt  = $res[0][0];
            if ($csv_flg == 1) {    // Netmoni���� = 15(����)
                $insert_qry = "insert into equip_work_log2 (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                            values({$m_no}, '" . date('Y-m-d H:i:s') . "', 15, $pre_cnt, {$s_no}, {$k_no})
                        ";
            } else {                // �����꡼�����å����� = 9(����)
                $insert_qry = "insert into equip_work_log2 (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                            values({$m_no}, '" . date('Y-m-d H:i:s') . "', 9, $pre_cnt, {$s_no}, {$k_no})
                        ";
            }
        } else {
            if ($csv_flg == 1) {    // Netmoni���� = 15(����)
                $insert_qry = "insert into equip_work_log2 (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                values({$m_no}, '" . date('Y-m-d H:i:s') . "', 15, 0, {$s_no}, {$k_no})
                            ";
            } else {                // �����꡼�����å����� = 9(����)
                $insert_qry = "insert into equip_work_log2 (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                values({$m_no}, '" . date('Y-m-d H:i:s') . "', 9, 0, {$s_no}, {$k_no})
                            ";
            }
        }
        if (funcConnect()) {
            execQuery('begin');
            if (execQuery($insert_qry) >= 0) {
                execQuery('commit');
                disConnectDB();
            } else {
                execQuery("rollback");
                disConnectDB();
                $error_msg = date('Y/m/d H:i:s', mktime());
                $error_msg .= "-execQuery: $insert_qry";
                `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
            }
        } else {
            $error_msg = date('Y/m/d H:i:s', mktime());
            $error_msg .= "-funcConnect: $insert_qry";
            `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
        }
        $update_qry = "update equip_work_log2_header set work_flg = FALSE where mac_no={$m_no} and siji_no={$s_no} and koutei={$k_no}";
    } else {
        $update_qry = "update equip_work_log2_header set work_flg = TRUE where mac_no={$m_no} and siji_no={$s_no} and koutei={$k_no}";
    }
    if (funcConnect()) {
        execQuery('begin');
        if (execQuery($update_qry) >= 0) {
            execQuery("commit");
            disConnectDB();
            return true;
        } else {
            execQuery('rollback');
            disConnectDB();
            $error_msg = date('Y/m/d H:i:s', mktime());
            $error_msg .= "-execQuery: $update_qry";
            `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
        }
    } else {
        $error_msg = date('Y/m/d H:i:s', mktime());
        $error_msg .= "-funcConnect: $update_qry";
        `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
    }
    return false;
}

// ��¤�ݤε�����ž���ϥǡ�����Ͽ(�إå����ե����� & ����)
function add_equip_header($kikaino, $seizousiji, $buhinno, $kouteino, $seisansuu, $str_timestamp)
{
    $insert_qry = "insert into equip_work_log2_header (mac_no, siji_no, parts_no, koutei, plan_cnt, str_timestamp,work_flg) 
            values($kikaino, $seizousiji, '$buhinno', $kouteino, $seisansuu, '$str_timestamp', TRUE)";
    if (funcConnect()) {
        execQuery('begin');
        if (execQuery($insert_qry)>=0) {
            execQuery('commit');
            disConnectDB();
            return true;
        } else {
            execQuery('rollback');
            disConnectDB();
            $error_msg  = date('Y/m/d H:i:s', mktime());
            $error_msg .= "-execQuery: $insert_qry";
            `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
        }
    } else {
        $error_msg  = date('Y/m/d H:i:s', mktime());
        $error_msg .= "-funcConnect: $insert_qry";
        `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
    }
    return false;
}

// ��¤�ݤε�����ž���ϥǡ������(�إå����ե����� & ����)(�ȥ�󥶥���������)
function del_equip_header_work($kikaino, $seizousiji, $buhinno, $kouteino)
{
    $delete_header = "delete from equip_work_log2_header where mac_no={$kikaino} and siji_no={$seizousiji} and 
                        koutei={$kouteino}";
    $delete_work = "
        DELETE FROM equip_work_log2
        WHERE
        equip_index(mac_no, siji_no, koutei, date_time) > '{$kikaino}{$seizousiji}{$kouteino}00000000000000'
        AND
        equip_index(mac_no, siji_no, koutei, date_time) < '{$kikaino}{$seizousiji}{$kouteino}99999999999999'
    ";
    if (funcConnect()) {
        execQuery('begin');
        if (execQuery($delete_header)>=0) {
            if (execQuery($delete_work)>=0) {
                execQuery('commit');
                disConnectDB();
                return true;
            } else {
                execQuery('rollback');
                disConnectDB();
                $error_msg = date('Y/m/d H:i:s', mktime());
                $error_msg .= "-execQuery: $delete_work Transaction";
                `echo "$error_msg" >> /tmp/equipment_write_error.log`;
            }
        } else {
            execQuery('rollback');
            disConnectDB();
            $error_msg = date('Y/m/d H:i:s', mktime());
            $error_msg .= "-execQuery: $delete_header Transaction";
            `echo "$error_msg" >> /tmp/equipment_write_error.log`;
        }
    } else {
        $error_msg = date('Y/m/d H:i:s', mktime());
        $error_msg .= "-funcConnect: $delete_header Transaction";
        `echo "$error_msg" >> /tmp/equipment_write_error.log`;
    }
    return false;
}


// ��¤�ݤε�����ž equip_work_log2_header equip_work_log �ǡ�������(�ȥ�󥶥������)
function chg_equip_header_work($pm_no, $ps_no, $pb_no, $pk_no, $m_no, $s_no, $b_no, $k_no, $p_no)
{
    $update_header = "update
                            equip_work_log2_header
                        set mac_no={$m_no}
                            , siji_no={$s_no}
                            , parts_no='{$b_no}'
                            , koutei={$k_no}
                            , plan_cnt={$p_no}
                        where
                            mac_no={$pm_no} and siji_no={$ps_no} and koutei={$pk_no}
                    "; 
    $update_work = "update
                            equip_work_log2
                        set mac_no={$m_no}
                            , siji_no={$s_no}
                            , koutei={$k_no}
                        where
                            mac_no={$pm_no} and siji_no={$ps_no} and koutei={$pk_no}
                    ";
    if (funcConnect()) {
        execQuery('begin');
        if (execQuery($update_header) >= 0) {
            if (execQuery($update_work) >= 0) {
                execQuery('commit');
                disConnectDB();
                return true;
            } else {
                execQuery('rollback');
                disConnectDB();
                $error_msg = date('Y/m/d H:i:s', mktime());
                $error_msg .= "-execQuery: $update_work Transaction";
                `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
            }
        } else {
            execQuery('rollback');
            disConnectDB();
            $error_msg = date('Y/m/d H:i:s', mktime());
            $error_msg .= "-execQuery: $update_header Transaction";
            `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
        }
    } else {
        $error_msg = date('Y/m/d H:i:s', mktime());
        $error_msg .= "-funcConnect: $update_header_work";
        `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
    }
    return false;
}



// ��¤�ݤε�����ž equip_work_log �ǡ�������
function chg_equip_work_log($pm_no, $ps_no, $pb_no, $pk_no, $m_no, $s_no, $b_no, $k_no, $p_no)
{
    $update_qry = "update equip_work_log2 set mac_no={$m_no}, siji_no={$s_no}, koutei={$k_no}
        where mac_no={$pm_no} and siji_no={$ps_no} and koutei={$pk_no}"; 
    if (funcConnect()) {
        execQuery('begin');
        if (execQuery($update_qry) >= 0) {
            execQuery('commit');
            disConnectDB();
            return true;
        } else {
            execQuery('rollback');
            disConnectDB();
            $error_msg = date('Y/m/d H:i:s', mktime());
            $error_msg .= "-execQuery: $update_qry";
            `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
        }
    } else {
        $error_msg = date('Y/m/d H:i:s', mktime());
        $error_msg .= "-funcConnect: $update_qry";
        `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
    }
    return false;
}



// ��¤�ݤε�����ž���ϥǡ�������(�إå����ե����� & ����)
function chg_equip_header($pm_no, $ps_no, $pb_no, $pk_no, $m_no, $s_no, $b_no, $k_no, $p_no)
{
    $update_qry = "update equip_work_log2_header set mac_no={$m_no}, siji_no={$s_no}, parts_no='$b_no', koutei={$k_no}, plan_cnt={$p_no}
        where mac_no={$pm_no} and siji_no={$ps_no} and koutei={$pk_no}"; 
    if (funcConnect()) {
        execQuery('begin');
        if (execQuery($update_qry) >= 0) {
            execQuery('commit');
            disConnectDB();
            return true;
        } else {
            execQuery('rollback');
            disConnectDB();
            $error_msg = date('Y/m/d H:i:s', mktime());
            $error_msg .= "-execQuery: $update_qry";
            `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
        }
    } else {
        $error_msg = date('Y/m/d H:i:s', mktime());
        $error_msg .= "-funcConnect: $update_qry";
        `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
    }
    return false;
}



// ���������ε����ξ��֤�ʸ������֤�����α��NC Netmoni�Ǥ�
// ���������ε����ξ��֤�ʸ������֤��������꡼�����å���
// ���֤�̵�ͱ�ž���֤��ѹ� Netmoni �˹�碌�뤿�� ������(�Ÿ�OFF����ư��ž�����顼�ࡦ���)���̲�
function equip_machine_state($mac_no, $state, &$bg_color, &$txt_color)
{
   ///// �����ޥ���������ư��������
    $query = "select csv_flg from equip_machine_master2 where mac_no={$mac_no}";
    if (getUniResult($query, $state_type) <= 0) {
        if (isset($_SESSION)) {
            $_SESSION['s_sysmsg'] .= "�����ޥ����������ư�����μ����˼��� mac_no=$state ";    // debug ��
            //return FALSE;
        } else {
            `echo "$query" >> /tmp/equipment_write_error2.log`;
        }
    }
    if ($state_type == 1) {                             // Netmoni ������
        switch ($state) {
        case 0:
            $bg_color = "black";
            $txt_color = "white";
            return "�Ÿ�OFF";
            break;
        case 1:
            $bg_color = "green";
            $txt_color = "white";
            return "��ư��ž";
            break;
        case 2:
            $bg_color = "red";
            $txt_color = "white";
            return "���顼��";
            break;
        /*
        case 2:
            $bg_color = "red";
            $txt_color = "white";
            return "�������϶��Ԥ�";
            break;
        */
        case 3:
            $bg_color = "yellow";
            $txt_color = "black";
            return "�� �� ��";
            break;
        /*
        case 3:
            $bg_color = "yellow";
            $txt_color = "black";
            return "���祳��";
            break;
        */
        case 4:
            $bg_color = "orange";
            $txt_color = "black";
            return "Net��ư";
            break;
        case 5:
            $bg_color = "maroon";
            $txt_color = "white";
            return "Net��λ";
            break;
        case 10:
            $bg_color = "purple";
            $txt_color = "white";
            return "�� �� ��";
            break;
        /*
        case 10:
            $bg_color = "purple";
            $txt_color = "white";
            return "Ω�����";
            break;
        */
        case 11:
            $bg_color = "aqua";
            $txt_color = "black";
            return "�� �� ��";
            break;
        case 12:
            $bg_color = "gray";
            $txt_color = "white";
            return "�ξ㽤��";
            break;
        case 13:
            $bg_color = "silver";
            $txt_color = "black";
            return "�϶��";
            break;
        case 14:
            $bg_color = "blue";
            $txt_color = "white";
            return "̵�ͱ�ž";
            break;
        case 15:
            $bg_color = "magenta";
            $txt_color = "black";
            return "�桡����";
            break;
        /*
        case 15:
            $bg_color = "magenta";
            $txt_color = "black";
            return "����¾���";
            break;
        case 16:
            $bg_color = "maroon";
            $txt_color = "white";
            return "�ʼ��Ԥ�";
            break;
        case 17:
            $bg_color = "magenta";
            $txt_color = "black";
            return "�����Ԥ�";
            break;
        */
        default:
            $bg_color = "";
            $txt_color = "red";
            return "̤ �� Ͽ";
        }
    } else {                                            // ����¾(�����꡼�����å���)
        switch ($state) {
        case 0:
            $bg_color = "black";
            $txt_color = "white";
            return "�Ÿ�OFF";
            break;
        case 1:
            $bg_color = "green";
            $txt_color = "white";
            return "��ư��ž";
            break;
        case 2:
            $bg_color = "red";
            $txt_color = "white";
            return "���顼��";
            break;
        /*
        case 2:
            $bg_color = "red";
            $txt_color = "white";
            return "�������϶��Ԥ�";
            break;
        */
        case 3:
            $bg_color = "yellow";
            $txt_color = "black";
            return "�� �� ��";
            break;
        /*
        case 3:
            $bg_color = "yellow";
            $txt_color = "black";
            return "���祳��";
            break;
        */
        case 4:
            $bg_color = "purple";
            $txt_color = "white";
            return "�� �� ��";
            break;
        /*
        case 4:
            $bg_color = "purple";
            $txt_color = "white";
            return "Ω�����";
            break;
        */
        case 5:
            $bg_color = "aqua";
            $txt_color = "black";
            return "�� �� ��";
            break;
        case 6:
            $bg_color = "gray";
            $txt_color = "white";
            return "�ξ㽤��";
            break;
        case 7:
            $bg_color = "silver";
            $txt_color = "black";
            return "�϶��";
            break;
        case 8:
            $bg_color = "blue";
            $txt_color = "white";
            return "̵�ͱ�ž";
            break;
        case 9:
            $bg_color = "magenta";
            $txt_color = "black";
            return "�桡����";
            break;
        /*
        case 9:
            $bg_color = "magenta";
            $txt_color = "black";
            return "����¾���";
            break;
        */
        case 10:
            $bg_color = "orange";
            $txt_color = "black";
            return "ͽ �� ��";
            break;
        /*
        case 10:
            $bg_color = "orange";
            $txt_color = "black";
            return "�ʼ��Ԥ�";
            break;
        */
        case 11:
            $bg_color = "maroon";
            $txt_color = "white";
            return "ͽ �� ��";
            break;
        /*
        case 11:
            $bg_color = "maroon";
            $txt_color = "white";
            return "�����Ԥ�";
            break;
        */
        default:
            $bg_color = "white";    // ̤��Ͽ�ϥХå���Ʊ������ѹ�
            $txt_color = "red";
            return "̤ �� Ͽ";
        }
    }
}


// ���ߤϻ��Ѥ��Ƥ��ʤ� �嵭�� equip_machine_state()���¤
// ���������ε����ξ��֤�ʸ������֤��������꡼�����å���
// ���֤�̵�ͱ�ž���֤��ѹ� Netmoni �˹�碌�뤿�� ������(�Ÿ�OFF����ư��ž�����顼�ࡦ���)
function equip_state_r($no, &$bg_color, &$txt_color)
{
    switch ($no) {
    case 0:
        $bg_color = "black";
        $txt_color = "white";
        return "�Ÿ�OFF";
        break;
    case 1:
        $bg_color = "green";
        $txt_color = "white";
        return "��ư��ž";
        break;
    case 2:
        $bg_color = "red";
        $txt_color = "white";
        return "���顼��";
        break;
    /*
    case 2:
        $bg_color = "red";
        $txt_color = "white";
        return "�������϶��Ԥ�";
        break;
    */
    case 3:
        $bg_color = "yellow";
        $txt_color = "black";
        return "�����";
        break;
    /*
    case 3:
        $bg_color = "yellow";
        $txt_color = "black";
        return "���祳��";
        break;
    */
    case 4:
        $bg_color = "purple";
        $txt_color = "white";
        return "�ȵ���";
        break;
    /*
    case 4:
        $bg_color = "purple";
        $txt_color = "white";
        return "Ω�����";
        break;
    */
    case 5:
        $bg_color = "aqua";
        $txt_color = "black";
        return "�ʼ���";
        break;
    case 6:
        $bg_color = "gray";
        $txt_color = "white";
        return "�ξ㽤��";
        break;
    case 7:
        $bg_color = "silver";
        $txt_color = "black";
        return "�϶��";
        break;
    case 8:
        $bg_color = "blue";
        $txt_color = "white";
        return "̵�ͱ�ž";
        break;
    case 9:
        $bg_color = "magenta";
        $txt_color = "black";
        return "�� ��";
        break;
    /*
    case 9:
        $bg_color = "magenta";
        $txt_color = "black";
        return "����¾���";
        break;
    */
    case 10:
        $bg_color = "orange";
        $txt_color = "black";
        return "ͽ����";
        break;
    /*
    case 10:
        $bg_color = "orange";
        $txt_color = "black";
        return "�ʼ��Ԥ�";
        break;
    */
    case 11:
        $bg_color = "maroon";
        $txt_color = "white";
        return "ͽ����";
        break;
    /*
    case 11:
        $bg_color = "maroon";
        $txt_color = "white";
        return "�����Ԥ�";
        break;
    */
    default:
        $bg_color = "";
        $txt_color = "red";
        return "̤��Ͽ";
    }
}


// ���������Υإå����ե�����Υ쥳���ɿ����֤����ʤ����0
function equip_header_cnt()
{
    $row = 0;       // ����쥳�����ֹ�
    $fp = fopen (EQUIP_INDEX,"r");
    $data = array();
    while ($data[$row] = fgetcsv ($fp, 100, ",")) {
//      if((strlen($data[$row][0])!=4)){
//          $row--; // �ǡ���������ʤ�쥳���ɤ�ޥ��ʥ�
//      }
        $row++;
    }
    fclose ($fp);
    return $row;
}


// Ϳ����줿����(�쥳�����ֹ�)�ε�������֤����ʤ����FALSE
function equip_kikaino($no){
    $row = 0;       // ����쥳�����ֹ�
    $fp = fopen (EQUIP_INDEX,"r");
    $data = array();
    while ($data[$row] = fgetcsv ($fp, 100, ",")) {
        if($row==$no){
            fclose ($fp);
            return $data[$row][0];
        }
        $row++;
    }
    fclose ($fp);
    return FALSE;
}


// Ϳ����줿�����ε����⤬�ǡ���������ʤ�TRUE
function equip_working_chk($no)
{
    ///// �����ޥ���������ư��������
    $query = "select csv_flg from equip_machine_master2 where mac_no={$no}";
    if (getUniResult($query, $state_type) <= 0) {
        $_SESSION['s_sysmsg'] .= "�����ޥ����������ư�����μ����˼���";    // debug ��
        return FALSE;
    }
    $state_type = 2;    // header file �����줹�뤿�� �ɲ� 2004/06/24
    if ($state_type == 1) {                             // Netmoni ������
        $row1 = 0;
        $fp1 = fopen(EQUIP_INDEX, 'r');
        $data1 = array();
        while ($data1[$row1] = fgetcsv($fp1, 100, ',')) {
            if ($data1[$row1][0]==$no) {                // ��ư�����å� Netmoni Type
                fclose ($fp1);
                return TRUE;
            }
            $row1++;
        }
        fclose ($fp1);
        return FALSE;
    } else {                                            // ����¾(�����꡼�����å���)
        ///// equip_work_log2_header �ơ��֥뤫�����
        $query = "select mac_no from equip_work_log2_header where mac_no={$no} and work_flg is TRUE";
        if (getUniResult($query, $tmp) <= 0) {          // ��ư�����å�
            return FALSE;
        } else {
            return TRUE;
        }
    }
}


// �����ε�����ǥإå����ե����뤫����ե�����̾����
// �إå����ե�����˵����⤬�ʤ����NULL����
function equip_file_name_create($no)
{
    $row1 = 0;
    $fp1 = fopen (EQUIP_INDEX,"r");
    $data1 = array();
    while ($data1[$row1] = fgetcsv ($fp1, 100, ",")) {
        if ($data1[$row1][0]==$no) {
            fclose ($fp1);
            return $data1[$row1][0] . $data1[$row1][1] . $data1[$row1][2] . $data1[$row1][3] . $data1[$row1][4];
        }
        $row1++;
    }
    fclose ($fp1);
    return NULL;
}


// �����ε�����ǥإå����ե����뤫�����ե�����ɤ����֤�
// �إå����ե�����˵����⤬�ʤ����NULL����(���顼��)
function equip_header_field($no, $field)
{
   ///// �����ޥ���������ư��������
    $query = "select csv_flg from equip_machine_master2 where mac_no={$no}";
    if (getUniResult($query, $state_type) <= 0) {
        $_SESSION['s_sysmsg'] .= "�����ޥ����������ư�����μ����˼���";    // debug ��
        return FALSE;
    }
    $state_type = 2;    // header file �����줹�뤿�� �ɲ� 2004/06/24
    if ($state_type == 1) {                             // 0=��ƻ� 1=Netmoni 2=FWS1 3=FWS2 ������
        $row1 = 0;
        $fp1 = fopen (EQUIP_INDEX,"r");
        $data1 = array();
        while ($data1[$row1] = fgetcsv ($fp1, 100, ",")) {
            if ($data1[$row1][0] == $no) {
                fclose ($fp1);
                if ( ($field >= 0) && ($field <= 4) ) {
                    return $data1[$row1][$field];
                } else {
                    return NULL;
                }
            }
            $row1++;
        }
        fclose ($fp1);
        return NULL;
    } else {                                            // ����¾(�����꡼�����å���)
        ///// equip_work_log2_header �ơ��֥뤫�����
        $query = "select mac_no, siji_no, parts_no, koutei, plan_cnt from equip_work_log2_header where mac_no='$no' and work_flg is TRUE";
        if (getResult2($query, $data) <= 0) {          // ��ư�����å�
            return FALSE;
        } else {
            return $data[0][$field];
        }
    }
}

// --------------------------------------------------
// ���������ѤγƼ� ���¤�Ƚ��
// --------------------------------------------------
function equipAuthUser($function)
{
    // @session_start();
    $LoginUser = $_SESSION['User_ID'];
    $query = "select * from equip_account where function='$function' and staff='$LoginUser'";
    if (getUniResult($query, $res) > 0) {
        return true;
    } else {
        return false;
    }
}

// ------------------------------------------------------
// �ꥯ���������ϥ��å���󤫤鹩���ʬ�ȹ���̾���������
// ------------------------------------------------------
function getFactory(&$factory='')
{
    if (isset($_REQUEST['factory'])) {
        // $factory = $_REQUEST['factory'];
        $factory = @$_SESSION['factory'];
    } else {
        ///// �ꥯ�����Ȥ�̵����Х��å���󤫤鹩���ʬ��������롣(�̾�Ϥ��Υѥ�����)
        $factory = @$_SESSION['factory'];
    }
    switch ($factory) {
    case 1:
        $fact_name = '������';
        break;
    case 2:
        $fact_name = '������';
        break;
    case 4:
        $fact_name = '������';
        break;
    case 5:
        $fact_name = '������';
        break;
    case 6:
        $fact_name = '������';
        break;
    case 7:
        $fact_name = '������(���)';
        break;
    case 8:
        $fact_name = '������(SUS)';
        break;
    default:
        $fact_name = '������';
        break;
    }
    return $fact_name;
}

