#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ���ץ�����δ����ʸ������ӽ� ����   (MISOCFL1,MIUSERL) ��� ������ CLI�� //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/12/10 Created  inspectionPrintUpdate_cli.php                        //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI�ǤʤΤ�ɬ�פʤ�

$currentFullPathName = realpath(dirname(__FILE__));
require_once ("{$currentFullPathName}/../../function.php");

$log_date = date('Y-m-d H:i:s');        ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');    ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�

////////// �桼����̾��Ⱦ�ѥ������ʤ����뤿��FTPž���Ϥ��ʤ�

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    //query_affected_trans($con, 'BEGIN');    // ������Ͽ�Ѥˤϥ����ȥ�����
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date ���ץ���������ν��ֹ桦�桼���� db_connect() error \n");
    exit();
}
///// assy_develop_user �����ץ鳫ȯ�ե�����ι���
$file_orign  = '/home/guest/monthly/MISOCFL1.CSV';
$file_backup = "{$currentFullPathName}/backup/MISOCFL1-BAK.TXT";
$file_debug  = "{$currentFullPathName}/debug/debug-MISOCFL1.TXT";
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    $noUpdate = 0;  // ̤�ѹ������󥿡�
    while (($data = fgetcsv($fp, 50, ',')) !== FALSE) {
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
            SELECT assy_no FROM assy_develop_user
            WHERE assy_no='{$data[0]}' AND dev_no='{$data[1]}' AND appro_no='{$data[2]}' AND user_no='{$data[3]}'
        ";
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "
                INSERT INTO assy_develop_user (assy_no, dev_no, appro_no, user_no)
                VALUES(
                    '{$data[0]}',
                    '{$data[1]}',
                    '{$data[2]}',
                    '{$data[3]}')
            ";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date �����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
            $noUpdate++;
            continue;
            ///// ��Ͽ���� update ����
            $query = "
                UPDATE assy_develop_user
                SET
                    assy_no             ='{$data[0]}',
                    dev_no              ='{$data[1]}',
                    appro_no            ='{$data[3]}',
                    user_no             ='{$data[3]}'
                WHERE assy_no='{$data[0]}' AND dev_no='{$data[1]}' AND appro_no='{$data[2]}' AND user_no='{$data[3]}'
            ";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date �����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
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
    fwrite($fpa, "$log_date �����ץ鳫ȯ�ե�����ι��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date �����ץ鳫ȯ�ե�����ι��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date �����ץ鳫ȯ�ե�����ι��� : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpa, "$log_date �����ץ鳫ȯ�ե�����ι��� : {$noUpdate}/{$rec} �� ̤�ѹ� \n");
    echo "$log_date �����ץ鳫ȯ�ե�����ι��� : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date �����ץ鳫ȯ�ե�����ι��� : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date �����ץ鳫ȯ�ե�����ι��� : {$upd_ok}/{$rec} �� �ѹ� \n";
    echo "$log_date �����ץ鳫ȯ�ե�����ι��� : {$noUpdate}/{$rec} �� ̤�ѹ� \n";
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
// fclose($fpa);      ////// �����ѥ�����߽�λ



///// assy_develop_user_code ���襳���ɥơ��֥�(�Σ���)�ι���
$file_orign  = '/home/guest/monthly/MIUSERL.CSV';
$file_backup = "{$currentFullPathName}/backup/MIUSERL-BAK.TXT";
$file_debug  = "{$currentFullPathName}/debug/debug-MIUSERL.TXT";
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    $noUpdate = 0;  // ̤�ѹ������󥿡�
    while (($data = fgetcsv($fp, 50, ',')) !== FALSE) {
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
            SELECT user_no FROM assy_develop_user_code
            WHERE user_no='{$data[0]}'
        ";
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "
                INSERT INTO assy_develop_user_code (user_no, user_name)
                VALUES(
                    '{$data[0]}',
                    '{$data[1]}')
            ";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date �桼�����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
            $query_chk = "
                SELECT user_no FROM assy_develop_user_code
                WHERE user_no='{$data[0]}' AND user_name='{$data[1]}'
            ";
            if (getUniResTrs($con, $query_chk, $res_chk) > 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                // �ǡ������ѹ��ʤ�
                $noUpdate++;
                continue;
            }
            ///// ��Ͽ����ǡ������ѹ����� update ����
            $query = "
                UPDATE assy_develop_user_code
                SET
                    user_no             ='{$data[0]}',
                    user_name           ='{$data[1]}'
                WHERE user_no='{$data[0]}' AND user_name='{$data[1]}'
            ";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date �桼�����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
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
    fwrite($fpa, "$log_date ���襳���ɥơ��֥�(�Σ���)�ι��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date ���襳���ɥơ��֥�(�Σ���)�ι��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date ���襳���ɥơ��֥�(�Σ���)�ι��� : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpa, "$log_date ���襳���ɥơ��֥�(�Σ���)�ι��� : {$noUpdate}/{$rec} �� ̤�ѹ� \n");
    echo "$log_date ���襳���ɥơ��֥�(�Σ���)�ι��� : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date ���襳���ɥơ��֥�(�Σ���)�ι��� : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date ���襳���ɥơ��֥�(�Σ���)�ι��� : {$upd_ok}/{$rec} �� �ѹ� \n";
    echo "$log_date ���襳���ɥơ��֥�(�Σ���)�ι��� : {$noUpdate}/{$rec} �� ̤�ѹ� \n";
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
