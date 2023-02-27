#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ���߸����� ��ͭ�����Υ��ޥ꡼�ե����� (SIDZKIL4) ��� ������ CLI��     //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/05/17 Created  inventory_average_summary_cli.php                    //
// 2007/06/09 �߸�0�ˤʤä���Τ��оݳ��Τ��ᡢ�������˰�ö���ƺ�����ɲ�   //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI�ǤʤΤ�ɬ�פʤ�

$currentFullPathName = realpath(dirname(__FILE__));
require_once ("{$currentFullPathName}/../../function.php");

$log_date = date('Y-m-d H:i:s');        ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');    ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�

/*****************************************************************************************
// FTP�Υ������åȥե�����
$target_file = 'UKWLIB/W#SIDZKI';           // AS/400�ե����� download
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
$save_file = "{$currentFullPathName}/backup/W#SIDZKI.TXT";     // download file ����¸��

// ���ͥ���������(FTP��³�Υ����ץ�)
$ftp_flg = false;   // ž�������������ԥե饰
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// �������åȥե�����
        if (ftp_get($ftp_stream, $save_file, $target_file, FTP_ASCII)) {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa,"$log_date ���߸˥��ޥ꡼ ftp_get download OK " . $target_file . '��' . $save_file . "\n");
            $ftp_flg = true;
        } else {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa,"$log_date ���߸˥��ޥ꡼ ftp_get() error " . $target_file . "\n");
        }
    } else {
        $log_date = date('Y-m-d H:i:s');
        fwrite($fpa,"$log_date ���߸˥��ޥ꡼ ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa,"$log_date ���߸˥��ޥ꡼ ftp_connect() error \n");
}
if (!$ftp_flg) {
    fclose($fpa);      ////// �����ѥ�����߽�λ
    exit();
}
*****************************************************************************************/



/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    //query_affected_trans($con, 'BEGIN');    // ������Ͽ�Ѥˤϥ����ȥ�����
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date ���߸˥��ޥ꡼ db_connect() error \n");
    exit();
}
///// �������
$save_file = '/home/guest/monthly/W#SIDZKI.TXT';     // FTP�ν�����Ԥ����Ϥ��ιԤ���
$file_orign  = $save_file;
$file_backup = "{$currentFullPathName}/backup/W#SIDZKI-BAK.TXT";
$file_debug  = "{$currentFullPathName}/debug/debug-SIDZKI.TXT";
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    // �������˴�¸�Υǡ�������
    $del_sql = "
        DELETE FROM inventory_average_summary
    ";
    $delRec = query_affected_trans($con, $del_sql);
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date ���߸˥��ޥ꡼ ����Υǡ����� $delRec �� �����λ \n");
    echo "$log_date ���߸˥��ޥ꡼ ����Υǡ����� $delRec �� �����λ \n";
    
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    while (($data = fgetcsv($fp, 100, ',')) !== FALSE) {
        $rec++;
        
        if ($data[0] == '') {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa, "$log_date AS/400 del record=$rec \n");
            continue;
        }
        $num  = count($data);       // �ե�����ɿ��μ���
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
            $data[$f] = addslashes($data[$f]);          // "'"�����ǡ����ˤ������\�ǥ��������פ���
            //if ($f == 1) {
            //    $data[$f] = mb_convert_kana($data[$f]); // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
            //}
        }
        
        $query_chk = "
            SELECT parts_no FROM inventory_average_summary
            WHERE parts_no='{$data[1]}'
        ";
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "
                INSERT INTO inventory_average_summary
                    (div, parts_no, invent_pcs, month_pickup_avr, hold_monthly_avr, once_pickup_avr, hold_pickup_avr)
                VALUES(
                    '{$data[0]}',
                    '{$data[1]}',
                     {$data[2]} ,
                     {$data[3]} ,
                     {$data[4]} ,
                     {$data[5]} ,
                     {$data[6]} )
            ";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                $log_date = date('Y-m-d H:i:s');
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
            $query = "
                UPDATE inventory_average_summary
                SET
                    div                 ='{$data[0]}',
                    parts_no            ='{$data[1]}',
                    invent_pcs          = {$data[2]} ,
                    month_pickup_avr    = {$data[3]} ,
                    hold_monthly_avr    = {$data[4]} ,
                    once_pickup_avr     = {$data[5]} ,
                    hold_pickup_avr     = {$data[6]}
                WHERE parts_no='{$data[1]}'
            ";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                $log_date = date('Y-m-d H:i:s');
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
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date ���߸˥��ޥ꡼�ι��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date ���߸˥��ޥ꡼�ι��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date ���߸˥��ޥ꡼�ι��� : {$upd_ok}/{$rec} �� �ѹ� \n");
    echo "$log_date ���߸˥��ޥ꡼�ι��� : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date ���߸˥��ޥ꡼�ι��� : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date ���߸˥��ޥ꡼�ι��� : {$upd_ok}/{$rec} �� �ѹ� \n";
    if ($rec_ng == 0) {     // ����ߥ��顼���ʤ���� �ե��������
        if (file_exists($file_backup)) {
            unlink($file_backup);       // Backup �ե�����κ��
        }
        if (!rename($file_orign, $file_backup)) {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa,"$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n");
            echo "$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n";
        }
    }
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa,"$log_date �ե�����$file_orign ������ޤ���!\n");
    echo "$log_date �ե�����$file_orign ������ޤ���!\n";
}
/////////// commit �ȥ�󥶥������λ
//query_affected_trans($con, 'COMMIT');    // ������Ͽ�Ѥˤϥ����ȥ�����
// echo $query . "\n";  // debug
fclose($fpa);      ////// �����ѥ�����߽�λ

?>
