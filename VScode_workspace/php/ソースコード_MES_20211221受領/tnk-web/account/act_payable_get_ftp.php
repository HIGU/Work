<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-4.3.4-cgi -q �� php-4.3.7-cgi -q                    //
// ����ǡ��� ��ưFTP Download  ��ݥҥ��ȥ� UKWLIB/W#HIBCTR                //
//                                          AS/400 ----> Web Server (PHP)   //
// Copyright (C) 2003-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// �ѹ�����                                                                 //
// 2003/11/17 �������� act_payable_get_ftp.php                              //
//            http �� cli�Ǥ��ѹ������褦�� requier_once �����л����     //
// 2004/01/05 mb_convert_encoding�� 'auto'��'SJIS' ���ѹ� $rec++�ΰ����ѹ�  //
// 2004/02/06 ������(�׾���)����������Ʊ���������å����ɲ�                  //
// 2004/04/05 header('Location: http:' . WEB_HOST . ACT -->                 //
//                                  header('Location: ' . H_WEB_HOST . ACT  //
// 2004/04/19 php-4.3.6�ǹ���fgetcsv()�λ��ͤ��ѹ����� $num �Υ����å��ѹ�  //
// 2004/06/07 php-4.3.6-cgi -q �� php-4.3.7-cgi -q  �С�����󥢥å�        //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug ��
// ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
ini_set('max_execution_time', 1200);    // ����¹Ի��� 1200=20ʬ
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('/home/www/html/tnk-web/function.php');
require_once ('/home/www/html/tnk-web/tnk_func.php');   // account_group_check()�ǻ���
access_log();                           // Script Name ��ư����
// $_SESSION['site_index'] = 20;           // ��������ط�=20 �»�״ط�=10 �Ǹ�Υ�˥塼�� 99 �����
// $_SESSION['site_id']    = 10;           // ���̥�˥塼̵�� (0 <=)

//////////// �ƽи��μ���
$act_referer = $_SESSION['act_referer'];

//////////// ǧ�ڥ����å�
if (account_group_check() == FALSE) {
    // $_SESSION['s_sysmsg'] = '���ʤ��ϵ��Ĥ���Ƥ��ޤ���!<br>�����Ԥ�Ϣ���Ʋ�����!';
    $_SESSION['s_sysmsg'] = "Accounting Group �θ��¤�ɬ�פǤ���";
    header('Location: ' . $act_referer);
    exit();
}

$log_date = date('Y-m-d H:i:s');                ///// �����ѥ�������
$fpa = fopen('/tmp/act_payable.log', 'a');      ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
$_SESSION['s_sysmsg'] = '';     // �����

///// ����������μ���
if (isset($_SESSION['act_ymd'])) {
    $yyyymmdd = $_SESSION['act_ymd'];
} else {
    $_SESSION['s_sysmsg'] = "��������������ꤵ��Ƥ��ޤ���";
    header('Location: ' . $act_referer);
    exit();
}

/////////// Download file AS/400 & Save file
$down_file = 'UKWLIB/W#HIBCTR';
$save_file = 'W#HIBCTR.TXT';

// ���ͥ���������(FTP��³�Υ����ץ�)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        if (ftp_get($ftp_stream, $save_file, $down_file, FTP_ASCII)) {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>ftp_get download ���� $down_file �� $save_file </font><br>";
            fwrite($fpa,"$log_date ftp_get download ���� $down_file �� $save_file \n");
        } else {
            $_SESSION['s_sysmsg'] .=  "ftp_get() error $down_file <br>";
            fwrite($fpa,"$log_date ftp_get() error $down_file \n");
        }
    } else {
        $_SESSION['s_sysmsg'] .=  'ftp_login() error<br>';
        fwrite($fpa,"$log_date ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    $_SESSION['s_sysmsg'] .= 'ftp_connect() error --> ����������<br>';
    fwrite($fpa,"$log_date ftp_connect() error --> ����������\n");
}

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    $_SESSION['s_sysmsg'] .= 'db_connect() error';
    fwrite($fpa, "$log_date db_connect() error \n");
    exit();
}
// ��ݶ������� ������� �������
$file_orign  = 'W#HIBCTR.TXT';
$file_backup = 'backup/W#HIBCTR-BAK.TXT';
$file_test   = 'debug/debug-HIBCTR.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 300, '_');     // �¥쥳���ɤ�163�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���'_'�����
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num != 25) {
            if ($num == 0 || $num == 1) {   // php-4.3.5��0�֤� php-4.3.6��1���֤����ͤˤʤä���fgetcsv�λ����ѹ��ˤ��
                $_SESSION['s_sysmsg'] .= "<font color='yellow'>AS/400 del record=$rec </font><br>";
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
            } else {
                $_SESSION['s_sysmsg'] .= "field not 25 record=$rec <br>";
                fwrite($fpa, "$log_date field not 25 record=$rec \n");
            }
           continue;
        }
        /*****
        if ($num != 25) {
            $_SESSION['s_sysmsg'] .= "field not 25 record=$rec <br>";
            fwrite($fpa, "$log_date field not 25 record=$rec \n");
            continue;
        }
        *****/
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        }
        ///// �������Υ����å�
        if ($data[5] != $yyyymmdd) {    // ������(�׾���)����������Ʊ���������å�
            $_SESSION['s_sysmsg'] .= sprintf("�ǡ�����������������㤤�ޤ���<br>��������%s", format_date($data[15]) );
            fwrite($fpa, "$log_date �ǡ�����������������㤤�ޤ���������{$data[15]}\n");
            query_affected_trans($con, "rollback");     // transaction rollback
            header('Location: ' . $act_referer);
            exit();
        }
        
        if ($data[9] == '') {
            $data[9] = $data[2];    // ��ʸ����̵����м��մ��������
        }
        $data[2] = ($data[2] - 44); // ���ڤμ��մ����Ѵ�
        $data[9] = ($data[9] - 44); // ���ڤμ��մ����Ѵ�
        $query_chk = sprintf("SELECT uke_no FROM act_payable WHERE act_date=%d and type_no=%d
                                     and seq=%d and uke_no='%s'", $data[5], $data[0], $data[24], $data[1]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "INSERT INTO act_payable (type_no, uke_no, uke_ki, uke_date, ken_date, act_date,
                      vendor, parts_no, order_no, order_ki, koutei, mtl_cond, order_price, genpin, siharai,
                      div, kamoku, sei_no, h_pay_date, pay_date, r_kubun, gai_price, tax_kubun, act_id, seq)
                      VALUES(
                       {$data[0]},
                      '{$data[1]}',
                       {$data[2]},
                       {$data[3]},
                       {$data[4]},
                       {$data[5]},
                      '{$data[6]}',
                      '{$data[7]}',
                      '{$data[8]}',
                       {$data[9]},
                      '{$data[10]}',
                      '{$data[11]}',
                       {$data[12]},
                       {$data[13]},
                       {$data[14]},
                      '{$data[15]}',
                       {$data[16]},
                       {$data[17]},
                       {$data[18]},
                       {$data[19]},
                      '{$data[20]}',
                       {$data[21]},
                      '{$data[22]}',
                      '{$data[23]}',
                       {$data[24]})";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                $_SESSION['s_sysmsg'] .= "{$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!<br>";
                fwrite($fpa, "$log_date �����ֹ�:{$data[1]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                // query_affected_trans($con, "rollback");     // transaction rollback
                $rec_ng++;
                ////////////////////////////////////////// Debug start
                for ($f=0; $f<$num; $f++) {
                    fwrite($fpw,"'{$data[$f]}',");      // debug
                }
                fwrite($fpw,"\n");                      // debug
                fwrite($fpw, "$query \n");              // debug
                break;                                  // debug
                ////////////////////////////////////////// Debug end
            } else {
                $rec_ok++;
                $ins_ok++;
            }
        } else {
            ///// ��Ͽ���� update ����
            $query = "UPDATE act_payable SET type_no={$data[0]}, uke_no='{$data[1]}', uke_ki={$data[2]},
                      uke_date={$data[3]}, ken_date={$data[4]}, act_date={$data[5]}, vendor='{$data[6]}',
                      parts_no='{$data[7]}', order_no='{$data[8]}', order_ki={$data[9]}, koutei='{$data[10]}',
                      mtl_cond='{$data[11]}', order_price={$data[12]}, genpin={$data[13]},
                      siharai={$data[14]}, div='{$data[15]}', kamoku={$data[16]}, sei_no={$data[17]},
                      h_pay_date={$data[18]}, pay_date={$data[19]}, r_kubun='{$data[20]}',
                      gai_price={$data[21]}, tax_kubun='{$data[22]}', act_id='{$data[23]}', seq={$data[24]}
                where act_date={$data[5]} and type_no={$data[0]} and seq={$data[24]} and uke_no='{$data[1]}'";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                $_SESSION['s_sysmsg'] .= "{$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n";
                fwrite($fpa, "$log_date �����ֹ�:{$data[1]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                // query_affected_trans($con, "rollback");     // transaction rollback
                $rec_ng++;
                ////////////////////////////////////////// Debug start
                for ($f=0; $f<$num; $f++) {
                    fwrite($fpw,"'{$data[$f]}',");      // debug
                }
                fwrite($fpw,"\n");                      // debug
                fwrite($fpw, "$query \n");              // debug
                break;                                  // debug
                ////////////////////////////////////////// Debug end
            } else {
                $rec_ok++;
                $upd_ok++;
            }
        }
    }
    fclose($fp);
    fclose($fpw);       // debug
    $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$rec_ok}/{$rec} ����Ͽ���ޤ�����</font><br><br>";
    $_SESSION['s_sysmsg'] .= "<font color='white'>{$ins_ok}/{$rec} �� �ɲ�<br>";
    $_SESSION['s_sysmsg'] .= "{$upd_ok}/{$rec} �� �ѹ�</font>";
    fwrite($fpa, "$log_date ��ݤι���:{$data[1]} : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date ��ݤι���:{$data[1]} : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date ��ݤι���:{$data[1]} : {$upd_ok}/{$rec} �� �ѹ� \n");
    if ($rec_ng == 0) {     // ����ߥ��顼���ʤ���� �ե��������
        if (file_exists($file_backup)) {
            unlink($file_backup);       // Backup �ե�����κ��
        }
        if (!rename($file_orign, $file_backup)) {
            $_SESSION['s_sysmsg'] .= "$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n";
            fwrite($fpa,"$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n");
        }
    }
} else {
    $_SESSION['s_sysmsg'] .= "��ݥե����� {$file_orign} ������ޤ���!";
    fwrite($fpa,"$log_date �ե�����$file_orign ������ޤ���!\n");
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, "commit");
// echo $query . "\n";  // debug
fclose($fpa);      ////// �����ѥ�����߽�λ

header('Location: ' . H_WEB_HOST . ACT . 'act_payable_view.php');   // �����å��ꥹ�Ȥ�
// header('Location: http://masterst.tnk.co.jp/account/act_payable_view.php');
// header('Location: ' . $act_referer);
exit();
?>
