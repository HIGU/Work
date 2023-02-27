<?php
//////////////////////////////////////////////////////////////////////////////
// ����ǡ���(��ʸ��ȯ�ԥǡ���) ��ưFTP Download & Update                   //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2004-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/09/29 Created  order_data_ftp_update.php                            //
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
$target_file = 'UKWLIB/W#MIORDD';       // ��ʸ��ȯ�ԥե����� download file
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
$save_file = 'backup/W#MIORDD.TXT';     // ��ʸ��ȯ�ԥե����� save file

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
    echo "ftp_connect() error --> ��ʸ��ȯ��F\n";
    fwrite($fpa,"$log_date ftp_connect() error --> ��ʸ��ȯ��F\n");
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
// ��ʸ��ȯ�ԥե����� ������� �������
$file_orign  = $save_file;
$file_debug  = 'backup/debug-MIORDD.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 220, '_');     // �¥쥳���ɤ�206�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���'_'�����
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num < 20) {    // �ºݤˤ� 21 ����(�Ǹ夬�ʤ���礬���뤿��)
            if ($num == 0 || $num == 1) {   // php-4.3.5��0�֤� php-4.3.6��1���֤����ͤˤʤä���fgetcsv�λ����ѹ��ˤ��
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
                echo "$log_date AS/400 del record=$rec \n";
            } else {
                fwrite($fpa, "$log_date field not 20 record=$rec \n");
                echo "$log_date field not 20 record=$rec \n";
            }
           continue;
        }
        if (!isset($data[20])) $data[20]='';    // �����ֹ椬���åȤ���Ƥ��뤫�����å�
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        }
        $query_chk = sprintf("SELECT order_seq FROM order_data
                                WHERE order_seq=%d",
                                $data[0]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "INSERT INTO order_data
                      VALUES(
                       {$data[0]} ,
                      '{$data[1]}',
                       {$data[2]} ,
                       {$data[3]} ,
                       {$data[4]} ,
                       {$data[5]} ,
                      '{$data[6]}',
                      '{$data[7]}',
                       {$data[8]} ,
                       {$data[9]} ,
                       {$data[10]} ,
                       {$data[11]} ,
                       {$data[12]} ,
                       {$data[13]} ,
                       {$data[14]} ,
                       {$data[15]} ,
                       {$data[16]} ,
                       {$data[17]} ,
                       {$data[18]} ,
                      '{$data[19]}',
                      '{$data[20]}')
            ";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date ȯ���ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                echo "$log_date ȯ���ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n";
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
            $query = "UPDATE order_data SET
                            order_seq   = {$data[0]} ,
                            uke_no      ='{$data[1]}',
                            date_issue  = {$data[2]} ,
                            pre_seq     = {$data[3]} ,
                            sei_no      = {$data[4]} ,
                            order_no    = {$data[5]} ,
                            parts_no    ='{$data[6]}',
                            vendor      ='{$data[7]}',
                            order_q     = {$data[8]} ,
                            order_price = {$data[9]} ,
                            delivery    = {$data[10]} ,
                            uke_date    = {$data[11]} ,
                            uke_q       = {$data[12]} ,
                            ken_date    = {$data[13]} ,
                            genpin      = {$data[14]} ,
                            siharai     = {$data[15]} ,
                            cut_date    = {$data[16]} ,
                            cut_genpin  = {$data[17]} ,
                            cut_siharai = {$data[18]} ,
                            cut_kubun   ='{$data[19]}',
                            kouji_no    ='{$data[20]}'
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
    fwrite($fpa, "$log_date ��ʸ��ȯ��F�ι���:{$data[2]} : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date ��ʸ��ȯ��F�ι���:{$data[2]} : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date ��ʸ��ȯ��F�ι���:{$data[2]} : {$upd_ok}/{$rec} �� �ѹ� \n");
    echo "$log_date ��ʸ��ȯ��F�ι���:{$data[2]} : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date ��ʸ��ȯ��F�ι���:{$data[2]} : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date ��ʸ��ȯ��F�ι���:{$data[2]} : {$upd_ok}/{$rec} �� �ѹ� \n";
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
