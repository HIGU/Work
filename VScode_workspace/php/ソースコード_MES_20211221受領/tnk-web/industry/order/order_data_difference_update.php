<?php
//////////////////////////////////////////////////////////////////////////////
// ��ʸ��ȯ�ԥǡ�����ʬ�������� ��ưFTP Download & Update                   //
// (���ա�����ʬ)     AS/400 ----> Web Server (PHP)         Web��           //
// Copyright (C) 2004-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/10/13 Created  order_data_difference_update.php                     //
// 2004/10/14 cli(cron)�Ǥ���������ΤǸ��ߤ���Web�Ǥϻ��Ѥ��Ƥ��ʤ�        //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug ��
// ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
ini_set('max_execution_time', 1200);    // ����¹Ի���=20ʬ
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('/home/www/html/tnk-web/function.php');
access_log();                               // Script Name �ϼ�ư����

$log_date = date('Y-m-d H:i:s');        ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');    ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�

// FTP�Υ������åȥե�����
$target_file = 'UKWLIB/W#HIMKUK';       // ��ʸ��ȯ�ԥե����� download file
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
$save_file = 'backup/W#HIMKUK.TXT';     // ��ʸ��ȯ�ԥե����� save file

// ���ͥ���������(FTP��³�Υ����ץ�)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// ��ʸ��ȯ�ԥե�����
        if (ftp_get($ftp_stream, $save_file, $target_file, FTP_ASCII)) {
            echo 'ftp_get download ���� ', $target_file, '��', $save_file, "\n";
            fwrite($fpa,"$log_date ftp_get download ���� " . $target_file . '��' . $save_file . "\n");
        } else {
            echo 'ftp_get() error ', $target_file, "\n";
            fwrite($fpa,"$log_date ftp_get() error " . $target_file . "\n");
            exit;
            header('Location: ' . H_WEB_HOST . INDUST_MENU);
        }
    } else {
        echo "ftp_login() error \n";
        fwrite($fpa,"$log_date ftp_login() error \n");
        exit;
        header('Location: ' . H_WEB_HOST . INDUST_MENU);
    }
    ftp_close($ftp_stream);
} else {
    echo "ftp_connect() error --> $target_file\n";
    fwrite($fpa,"$log_date ftp_connect() error --> $target_file\n");
    $_SESSION['s_sysmsg'] = "<font color='yellow'>AS/400����ư���Ƥ��ޤ���</font>";
    header('Location: ' . H_WEB_HOST . INDUST_MENU);
    exit;
}



/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    echo "$log_date db_connect() error \n";
    header('Location: ' . H_WEB_HOST . INDUST_MENU);
    exit;
}
// ��ʸ��ȯ�ԥե����� ��ʬ�������� �������
$file_orign  = $save_file;
$file_debug  = 'backup/debug-HIMKUK.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 100, '_');     // �¥쥳���ɤ�75�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���'_'�����
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num < 7) {
            if ($num == 0 || $num == 1) {   // php-4.3.5��0�֤� php-4.3.6��1���֤����ͤˤʤä���fgetcsv�λ����ѹ��ˤ��
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
                echo "$log_date AS/400 del record=$rec \n";
            } else {
                fwrite($fpa, "$log_date field not 20 record=$rec \n");
                echo "$log_date field not 20 record=$rec \n";
            }
           continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        }
        $query_chk = sprintf("SELECT order_seq FROM order_data
                                WHERE order_seq=%d",
                                $data[0]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert �ϻ��Ѥ��� ���顼�Ȥ���(��ʬ�����Τ���)
            fwrite($fpa, "$log_date ȯ��Ϣ�֡�{$data[0]} �����Ĥ���ʤ� error \n");
            echo "$log_date ȯ��Ϣ�֡�{$data[0]} �����Ĥ���ʤ� error \n";
        } else {
            ///// ��Ͽ���� update ����
            $query = "UPDATE order_data SET
                            order_seq   = {$data[0]} ,
                            uke_no      ='{$data[1]}',
                            uke_date    = {$data[2]} ,
                            uke_q       = {$data[3]} ,
                            ken_date    = {$data[4]} ,
                            genpin      = {$data[5]} ,
                            siharai     = {$data[6]}
                WHERE order_seq={$data[0]}
            ";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date ȯ���ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                echo "$log_date ȯ���ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n";
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
    fwrite($fpa, "$log_date ��ʸ��ȯ��F�κ�ʬ����:{$data[2]} : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date ��ʸ��ȯ��F�κ�ʬ����:{$data[2]} : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date ��ʸ��ȯ��F�κ�ʬ����:{$data[2]} : {$upd_ok}/{$rec} �� �ѹ� \n");
    echo "$log_date ��ʸ��ȯ��F�κ�ʬ����:{$data[2]} : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date ��ʸ��ȯ��F�κ�ʬ����:{$data[2]} : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date ��ʸ��ȯ��F�κ�ʬ����:{$data[2]} : {$upd_ok}/{$rec} �� �ѹ� \n";
} else {
    fwrite($fpa,"$log_date �ե�����$file_orign ������ޤ���!\n");
    echo "{$log_date}: file:{$file_orign} ������ޤ���!\n";
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');
// echo $query . "\n";  // debug
fclose($fpa);      ////// �����ѥ�����߽�λ
$_SESSION['s_sysmsg'] = "<font color='white'>Ʊ����λ���ޤ�����</font>";
header('Location: ' . H_WEB_HOST . INDUST_MENU);
?>
