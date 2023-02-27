#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω ���� ����ι��� �Хå��� (HIKANS\2) ��� ������ CLI��               //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/09 Created  assembly_completion_history_ftp_cli.php              //
//            ��ˡ���������̵������ INSERT �Τߤν��� ���Τ����ե����å� //
// 2007/05/15 ��ư�����б��Ǥ��ѹ� (FTP����Ѥ���)                          //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI�ǤʤΤ�ɬ�פʤ�

$currentFullPathName = realpath(dirname(__FILE__));
require_once ("{$currentFullPathName}/../../function.php");

$log_date = date('Y-m-d H:i:s');        ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');    ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�

// FTP�Υ������åȥե�����
$target_file = 'UKWLIB/W#HIKANS';           // AS/400�ե����� download
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
$save_file = "{$currentFullPathName}/backup/W#HIKANS.TXT";      // download file ����¸��

// ���ͥ���������(FTP��³�Υ����ץ�)
$ftp_flg = false;   // ž�������������ԥե饰
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// �������åȥե�����
        if (ftp_get($ftp_stream, $save_file, $target_file, FTP_ASCII)) {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa,"$log_date ��Ω�������� ftp_get download OK " . $target_file . '��' . $save_file . "\n");
            $ftp_flg = true;
        } else {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa,"$log_date ��Ω�������� ftp_get() error " . $target_file . "\n");
        }
    } else {
        $log_date = date('Y-m-d H:i:s');
        fwrite($fpa,"$log_date ��Ω�������� ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa,"$log_date ftp_connect() error --> ��Ω��������ե�����\n");
}
if (!$ftp_flg) {
    fclose($fpa);      ////// �����ѥ�����߽�λ
    exit();
}


/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'BEGIN');
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date db_connect() error \n");
    echo "$log_date ��Ω�������� db_connect() error \n";
    exit();
}
///// �������
$startCheck = 0;    // ���Υե饰
$file_orign  = $save_file;
$file_backup = "{$currentFullPathName}/backup/W#HIKANS-BAK.TXT";
$file_debug  = "{$currentFullPathName}/debug/debug-W#HIKANS.TXT";
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    while (($data = fgetcsv($fp, 100, '_')) !== FALSE) {
        ///// �¥쥳���ɤ�50��70�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���('_'������С�)
        $rec++;
        
        if ($data[0] == '') {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa, "$log_date AS/400 del record=$rec \n");
            echo "$log_date AS/400 del record=$rec \n";
            continue;
        }
        $num  = count($data);       // �ե�����ɿ��μ���
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
            $data[$f] = addslashes($data[$f]);          // "'"�����ǡ����ˤ������\�ǥ��������פ���
            //if ($f == 6) {          // ����
            //    $data[$f] = mb_convert_kana($data[$f]); // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
            //}
        }
        if ($startCheck == 0) {
            $startCheck = 1;
            $query_chk = "
                SELECT plan_no FROM assembly_completion_history
                WHERE comp_date = {$data[3]}
            ";
            if (getUniResTrs($con, $query_chk, $res_chk) > 0) {     // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                /***
                fwrite($fpa, "$log_date {$data[3]}���δ������ϴ�����Ͽ����Ƥ��ޤ��� \n");
                break;
                ***/
                $del_sql = "DELETE FROM assembly_completion_history WHERE comp_date = {$data[3]}";
                if (($del_cnt=query_affected_trans($con, $del_sql)) <= 0) { // �����ѥ����꡼�μ¹�
                    $log_date = date('Y-m-d H:i:s');
                    fwrite($fpa, "$log_date {$data[3]}���ν�ʣ�����������Ǥ��ޤ���Ǥ����� \n");
                    break;
                }
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date {$data[3]}���ν�ʣ������ {$del_cnt} ��������Ƽ¹Ԥ��ޤ��� \n");
            }
        }
        $query = "
            INSERT INTO assembly_completion_history
                (plan_no, assy_no, line_group, comp_date, comp_pcs, comp_no, in_no)
            VALUES(
                '{$data[0]}',   -- �ײ��ֹ�
                '{$data[1]}',   -- �����ֹ�
                '{$data[2]}',   -- �����ѥ饤��
                 {$data[3]} ,   -- ������
                 {$data[4]} ,   -- ������
                 {$data[5]} ,   -- ��Ω��λ�ֹ�
                '{$data[6]}')   -- ���˾��
        ";
        if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa, "$log_date �ײ��ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
    }
    fclose($fp);
    fclose($fpw);       // debug
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date ��Ω��������ե�����ι��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date ��Ω��������ե�����ι��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date ��Ω��������ե�����ι��� : {$upd_ok}/{$rec} �� �ѹ� \n");
    if ($rec_ng == 0) {     // ����ߥ��顼���ʤ���� �ե��������
        if (file_exists($file_backup)) {
            unlink($file_backup);       // Backup �ե�����κ��
        }
        if (!rename($file_orign, $file_backup)) {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa,"$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n");
        }
    }
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa,"$log_date �ե�����$file_orign ������ޤ���!\n");
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'COMMIT');
// echo $query . "\n";  // debug
fclose($fpa);      ////// �����ѥ�����߽�λ
?>
