#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�����إå����ե����� (MGUHTML3) ��� ������ FTP CLI��                //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/01 Created  assembly_time_header_ftp_cli.php                     //
// 2007/05/16 ��ư�����Ѥ� FTP CLI�� �����  �������ٹ����Ѥ�PRG��Ǹ�˼¹�//
// 2007/07/30 AS/400�Ȥ�FTP error �б��Τ��� ftpCheckAndExecute()�ؿ����ɲ� //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI�ǤʤΤ�ɬ�פʤ�

$currentFullPathName = realpath(dirname(__FILE__));
require_once ("{$currentFullPathName}/../../function.php");

$log_date = date('Y-m-d H:i:s');        ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');    ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�

// FTP�Υ������åȥե�����
$target_file = 'UKWLIB/W#MGUHTM';           // AS/400�ե����� download
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
$save_file = "{$currentFullPathName}/backup/W#MGUHTM.TXT";     // download file ����¸��

// ���ͥ���������(FTP��³�Υ����ץ�)
$ftp_flg = false;   // ž�������������ԥե饰
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// �������åȥե�����
        if (ftpCheckAndExecute($ftp_stream, $save_file, $target_file, FTP_ASCII)) {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa,"$log_date ��Ω�����إå��� ftp_get download OK " . $target_file . '��' . $save_file . "\n");
            $ftp_flg = true;
        } else {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa,"$log_date ��Ω�����إå��� ftp_get() error " . $target_file . "\n");
        }
    } else {
        $log_date = date('Y-m-d H:i:s');
        fwrite($fpa,"$log_date ��Ω�����إå��� ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa,"$log_date ��Ω�����إå��� ftp_connect() error \n");
}
if (!$ftp_flg) {
    fclose($fpa);      ////// �����ѥ�����߽�λ
    exit();
}



/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'BEGIN');    // ������Ͽ�Ѥˤϥ����ȥ�����
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date ��Ω�����إå��� db_connect() error \n");
    exit();
}
///// �������
$file_orign  = $save_file;
$file_backup = "{$currentFullPathName}/backup/W#MGUHTM-BAK.TXT";
$file_debug  = "{$currentFullPathName}/debug/debug-MGUHTM.TXT";
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    while (($data = fgetcsv($fp, 100, '_')) !== FALSE) {
        ///// �¥쥳���ɤ�57�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���('_'������С�)
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
            SELECT assy_no FROM assembly_time_header
            WHERE assy_no='{$data[0]}' AND reg_no={$data[1]}
        ";
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "
                INSERT INTO assembly_time_header
                    (assy_no, reg_no, setdate, regdate, std_lot, pick_time)
                VALUES(
                    '{$data[0]}',
                     {$data[1]} ,
                     {$data[2]} ,
                     {$data[3]} ,
                     {$data[4]} ,
                     {$data[5]} )
            ";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date ASSY�ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
                UPDATE assembly_time_header
                SET
                    assy_no     ='{$data[0]}',
                    reg_no      = {$data[1]} ,
                    setdate     = {$data[2]} ,
                    regdate     = {$data[3]} ,
                    std_lot     = {$data[4]} ,
                    pick_time   = {$data[5]}
                WHERE assy_no='{$data[0]}' AND reg_no={$data[1]}
            ";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date ASSY�ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
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
                ///// ��Ω�������٤κ���򤳤��Ǽ¹�(�إå������б�����ʣ���쥳���ɤ���)
                $query = "
                    DELETE FROM assembly_standard_time WHERE assy_no='{$data[0]}' AND reg_no={$data[1]}
                ";
                query_affected_trans($con, $query);     // �����ѥ����꡼�μ¹�
            }
        }
    }
    fclose($fp);
    fclose($fpw);       // debug
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date ��Ω�����إå����ι���:{$data[1]} : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date ��Ω�����إå����ι���:{$data[1]} : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date ��Ω�����إå����ι���:{$data[1]} : {$upd_ok}/{$rec} �� �ѹ� \n");
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
query_affected_trans($con, 'COMMIT');    // ������Ͽ�Ѥˤϥ����ȥ�����
// echo $query . "\n";  // debug
fclose($fpa);      ////// �����ѥ�����߽�λ

/////////// ��Ω�������٤ι�������
`{$currentFullPathName}/assembly_standard_time_ftp_cli.php`;

exit();

function ftpCheckAndExecute($stream, $local_file, $as400_file, $ftp)
{
    $errorLogFile = '/tmp/as400FTP_Error.log';
    $trySec = 10;
    
    $flg = @ftp_get($stream, $local_file, $as400_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_get Error try1 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    sleep($trySec);
    
    $flg = @ftp_get($stream, $local_file, $as400_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_get Error try2 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    sleep($trySec);
    
    $flg = @ftp_get($stream, $local_file, $as400_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_get Error try3 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    
    return $flg;
}
?>
