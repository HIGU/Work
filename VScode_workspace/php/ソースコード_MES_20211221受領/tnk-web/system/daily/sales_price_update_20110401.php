#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ���칩��λ���ñ������ �Хå��� (MGUSI@P) ��� ������ CLI��              //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/08 Created  sales_price_update_cli.php                           //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI�ǤʤΤ�ɬ�פʤ�
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');    ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�

/*****************************************************************************************
// FTP�Υ������åȥե�����
$target_file = 'UKWLIB/MGUSI@P';           // AS/400�ե����� download
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
$save_file = '/home/www/html/tnk-web/system/backup/MGUSI@P.TXT';     // download file ����¸��

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
    // echo "ftp_connect() error --> ����ñ���ե�����\n";
    fwrite($fpa,"$log_date ftp_connect() error --> ����ñ���ե�����\n");
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
$save_file = '/home/guest/monthly/MGUSI@P.CSV';     // FTP�ν�����Ԥ����Ϥ��ιԤ���
$file_orign  = $save_file;
$file_backup = '/home/www/html/tnk-web/system/backup/MGUSI@P-BAK.TXT';
$file_debug  = '/home/www/html/tnk-web/system/debug/debug-MGUSI@P.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    while (($data = fgetcsv($fp, 100, ',')) !== FALSE) {
        ///// �¥쥳���ɤ�50��70�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���','�����('_'������С��ǤϤʤ�)
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
            if ($f == 6) {          // ����
                $data[$f] = mb_convert_kana($data[$f]); // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
            }
        }
        
        $query_chk = "
            SELECT parts_no FROM sales_price_nk_20110401
            WHERE parts_no='{$data[0]}'
        ";
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "
                INSERT INTO sales_price_nk_20110401
                    (parts_no, price, nk_kubun, regdate, div, lot, note, reg_kubun)
                VALUES(
                    '{$data[0]}',
                     {$data[1]} ,
                    '{$data[2]}',
                     {$data[3]} ,
                    '{$data[4]}',
                     {$data[5]} ,
                    '{$data[6]}',
                    '{$data[7]}')
            ";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                echo "$log_date �����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n";
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
                UPDATE sales_price_nk_20110401
                SET
                    parts_no    ='{$data[0]}',
                    price       = {$data[1]} ,
                    nk_kubun    ='{$data[2]}',
                    regdate     = {$data[3]} ,
                    div         ='{$data[4]}',
                    lot         ='{$data[5]}',
                    note        ='{$data[6]}',
                    reg_kubun   ='{$data[7]}'
                WHERE parts_no='{$data[0]}'
            ";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                echo "$log_date �����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n";
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
    fwrite($fpa, "$log_date ����ñ���ե�����ι���:{$data[1]} : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date ����ñ���ե�����ι���:{$data[1]} : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date ����ñ���ե�����ι���:{$data[1]} : {$upd_ok}/{$rec} �� �ѹ� \n");
    echo "$log_date ����ñ���ե�����ι���:{$data[1]} : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date ����ñ���ե�����ι���:{$data[1]} : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date ����ñ���ե�����ι���:{$data[1]} : {$upd_ok}/{$rec} �� �ѹ� \n";
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
