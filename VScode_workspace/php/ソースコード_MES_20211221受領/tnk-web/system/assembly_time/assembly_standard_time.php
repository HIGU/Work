#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�������٥ե����� (MGUJTML) ��� ������ CLI��                         //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/01 Created  assembly_standard_time.php                           //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI�ǤʤΤ�ɬ�פʤ�
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');    ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�

/*****************************************************************************************
// FTP�Υ������åȥե�����
$target_file = 'UKWLIB/W#MGUJTM';           // AS/400�ե����� download
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
$save_file = '/home/www/html/tnk-web/system/backup/W#MGUJTM.TXT';     // download file ����¸��

// ���ͥ���������(FTP��³�Υ����ץ�)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// �������åȥե�����
        if (ftp_get($ftp_stream, $save_file, $target_file, FTP_ASCII)) {
            // echo 'ftp_get download OK ', $target_file, '��', $save_file, "\n";
            fwrite($fpa,"$log_date ftp_get download OK " . $target_file . '��' . $save_file . "\n");
        } else {
            // echo 'ftp_get() error ', $target_file, "\n";
            fwrite($fpa,"$log_date ftp_get() error " . $target_file . "\n");
        }
    } else {
        // echo "ftp_login() error \n";
        fwrite($fpa,"$log_date ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    // echo "ftp_connect() error --> ��Ω��������\n";
    fwrite($fpa,"$log_date ftp_connect() error --> ��Ω��������\n");
}
*****************************************************************************************/



/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    //query_affected_trans($con, 'begin');    // ������Ͽ�Ѥˤϥ����ȥ�����
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    echo "$log_date db_connect() error \n";
    exit();
}
///// �������
$save_file = '/home/guest/monthly/MGUJTML.CSV';     // FTP�ν�����Ԥ����Ϥ��ιԤ���
$file_orign  = $save_file;
$file_backup = '/home/www/html/tnk-web/system/backup/W#MGUJTM-BAK.TXT';
$file_debug  = '/home/www/html/tnk-web/system/debug/debug-MGUJTM.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    while (($data = fgetcsv($fp, 100, ',')) !== FALSE) {
        ///// �¥쥳���ɤ�60�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���','�����('_'������С��ǤϤʤ�)
        $rec++;
        
        if ($data[0] == '') {
            fwrite($fpa, "$log_date AS/400 del record=$rec \n");
            echo "$log_date AS/400 del record=$rec \n";
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
            SELECT assy_no FROM assembly_standard_time
            WHERE assy_no='{$data[0]}' AND reg_no={$data[1]} AND pro_no={$data[2]}
        ";
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "
                INSERT INTO assembly_standard_time
                    (assy_no, reg_no, pro_no, pro_mark, line_no, ext_no, ext_assy, assy_time, setup_time, man_count)
                VALUES(
                    '{$data[0]}',
                     {$data[1]} ,
                     {$data[2]} ,
                    '{$data[3]}',
                    '{$data[4]}',
                    '{$data[5]}',
                    '{$data[6]}',
                     {$data[7]} ,
                     {$data[8]} ,
                     {$data[9]} )
            ";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date ASSY�ֹ�:{$data[0]}, ��Ͽ�ֹ�:{$data[1]}, �����ֹ�:{$data[2]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                echo "$log_date ASSY�ֹ�:{$data[0]}, ��Ͽ�ֹ�:{$data[1]}, �����ֹ�:{$data[2]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n";
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
                UPDATE assembly_standard_time
                SET
                    assy_no     ='{$data[0]}',
                    reg_no      = {$data[1]} ,
                    pro_no      = {$data[2]} ,
                    pro_mark    ='{$data[3]}',
                    line_no     ='{$data[4]}',
                    ext_no      ='{$data[5]}',
                    ext_assy    ='{$data[6]}',
                    assy_time   = {$data[7]} ,
                    setup_time  = {$data[8]} ,
                    man_count   = {$data[9]}
                WHERE assy_no='{$data[0]}' AND reg_no={$data[1]} AND pro_no={$data[2]}
            ";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date ASSY�ֹ�:{$data[0]}, ��Ͽ�ֹ�:{$data[1]}, �����ֹ�:{$data[2]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                echo "$log_date ASSY�ֹ�:{$data[0]}, ��Ͽ�ֹ�:{$data[1]}, �����ֹ�:{$data[2]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n";
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
    fwrite($fpa, "$log_date ��Ω�������٤ι���:{$data[1]} : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date ��Ω�������٤ι���:{$data[1]} : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date ��Ω�������٤ι���:{$data[1]} : {$upd_ok}/{$rec} �� �ѹ� \n");
    echo "$log_date ��Ω�������٤ι���:{$data[1]} : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date ��Ω�������٤ι���:{$data[1]} : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date ��Ω�������٤ι���:{$data[1]} : {$upd_ok}/{$rec} �� �ѹ� \n";
    if ($rec_ng == 0) {     // ����ߥ��顼���ʤ���� �ե��������
        if (file_exists($file_backup)) {
            unlink($file_backup);       // Backup �ե�����κ��
        }
        if (!rename($file_orign, $file_backup)) {
            // echo "$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n";
            fwrite($fpa,"$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n");
        }
    }
} else {
    fwrite($fpa,"$log_date �ե�����$file_orign ������ޤ���!\n");
    echo "{$log_date}: file:{$file_orign} ������ޤ���!\n";
}
/////////// commit �ȥ�󥶥������λ
//query_affected_trans($con, 'commit');    // ������Ͽ�Ѥˤϥ����ȥ�����
// echo $query . "\n";  // debug
fclose($fpa);      ////// �����ѥ�����߽�λ
?>
