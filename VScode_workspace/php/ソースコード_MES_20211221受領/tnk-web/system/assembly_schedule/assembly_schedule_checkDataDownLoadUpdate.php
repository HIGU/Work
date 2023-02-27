#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�ײ�ǡ�����ʬ��AS/400�Ȥδ����ǡ������ CHECK DATA DOWNLOAD CLI�� //
// AS/400 ----> Web Server (PHP)  �ȥ��åץǡ���                            //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/05/28 Created  assembly_schedule_checkDataDownloadUpdate.php        //
// 2007/09/12 AS/400�Ȥ�FTP���顼����Τ���ftpGetCheckAndExecute()���ɲ�    //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
// ini_set('max_execution_time', 1200);    // ����¹Ի���=20ʬ

$currentFullPathName = realpath(dirname(__FILE__));
require_once ("{$currentFullPathName}/../../function.php");

$log_date = date('Y-m-d H:i:s');        ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');    ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�

fwrite($fpa, "$log_date ��Ω�����ײ����������ʬ��ʬ�ǡ���DOWNLOAD���������� \n");

// ��¾�����ѥ���ȥ���ե�����
define('CMIPPL', 'UKWLIB/C#MIPPL');        // ��Ω�����ײ��������������ȥ���
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
define('C_MIPPL', "{$currentFullPathName}/backup/C#MIPPL.TXT");

// FTP�Υ������åȥե�����2
define('D_TIALLC', 'UKWLIB/W#MIPPLN');      // �����ײ���ե����� download file
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
define('S_TIALLC', "{$currentFullPathName}/backup/W#MIPPLN.TXT");  // �����ײ���ե����� save file

// ���ͥ���������(FTP��³�Υ����ץ�)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// ��Ω�����ײ��������������ȥ���ե���������å�
        if (ftpGetCheckAndExecute($ftp_stream, C_MIPPL, CMIPPL, FTP_ASCII)) {
            $log_date = date('Y-m-d H:i:s');        ///// ��������
            fwrite($fpa,"$log_date ftp_get ����ȥ��� download OK " . CMIPPL . '��' . C_MIPPL . "\n");
            if (checkControlFile($fpa, C_MIPPL)) {
                fwrite($fpa,"$log_date ����ȥ���ե�����˥ǡ���������Τǽ�λ���ޤ���\n");
                ftp_close($ftp_stream);
                fclose($fpa);      ////// ������λ
                exit();
            }
        } else {
            $log_date = date('Y-m-d H:i:s');        ///// ��������
            fwrite($fpa,"$log_date ftp_get() ����ȥ��� error " . CMIPPL . "\n");
            ftp_close($ftp_stream);
            fclose($fpa);      ////// ������λ
            exit();
        }
        ///// ��Ω�������ե�����
        if (ftpGetCheckAndExecute($ftp_stream, S_TIALLC, D_TIALLC, FTP_ASCII)) {
            $log_date = date('Y-m-d H:i:s');
            // echo 'ftp_get download OK ', D_TIALLC, '��', S_TIALLC, "\n";
            fwrite($fpa,"$log_date ftp_get download OK " . D_TIALLC . '��' . S_TIALLC . "\n");
        } else {
            $log_date = date('Y-m-d H:i:s');
            // echo 'ftp_get() error ', D_TIALLC, "\n";
            fwrite($fpa,"$log_date ftp_get() error " . D_TIALLC . "\n");
        }
    } else {
        $log_date = date('Y-m-d H:i:s');
        // echo "ftp_login() error \n";
        fwrite($fpa,"$log_date ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    $log_date = date('Y-m-d H:i:s');
    // echo "ftp_connect() error --> �������\n";
    fwrite($fpa,"$log_date ftp_connect() error --> �������\n");
}



/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'BEGIN');
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date db_connect() error \n");
    // echo "$log_date db_connect() error \n";
    exit();
}
// ��Ω�������ե����� ������� �������
$file_orign  = S_TIALLC;
$file_debug  = "{$currentFullPathName}/debug/debug-MIPPLN.TXT";
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    $del_ok = 0;    // DELETE�ѥ����󥿡�
    $del_old = 0;   // ������ѥ����󥿡�
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 120, '_');     // �¥쥳���ɤ�95�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���'_'�����
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num != 15) {
            if ($num == 0 || $num == 1) {   // php-4.3.5��0�֤� php-4.3.6��1���֤����ͤˤʤä���fgetcsv�λ����ѹ��ˤ��
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
                // echo "$log_date AS/400 del record=$rec \n";
            } else {
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date field not 15 record=$rec \n");
                // echo "$log_date field not 11 record=$rec \n";
            }
           continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        }
        ////////// ���ơ����������å� (A=�ɲ�ʬ C=�ѹ�ʬ D=���ʬ)
        if ($data[0] == 'C') {
            ///// ��Ͽ���� update ����
            $query = "UPDATE assembly_schedule SET
                            parts_no    ='{$data[2]}',
                            syuka       = {$data[3]} ,
                            chaku       = {$data[4]} ,
                            kanryou     = {$data[5]} ,
                            plan        = {$data[6]} ,
                            cut_plan    = {$data[7]} ,
                            kansei      = {$data[8]} ,
                            nyuuko      ='{$data[9]}',
                            sei_kubun   ='{$data[10]}',
                            line_no     ='{$data[11]}',
                            p_kubun     ='{$data[12]}',
                            assy_site   ='{$data[13]}',
                            dept        ='{$data[14]}'
                WHERE plan_no='{$data[1]}'";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date �ײ��ֹ�:{$data[1]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                // echo "$log_date �ײ��ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n";
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
        } elseif ($data[0] == 'D') {    //////// D=���ʬ�ν���
            $query_chk = sprintf("SELECT plan_no FROM assembly_schedule WHERE plan_no='%s'", $data[1]);
            if (getUniResTrs($con, $query_chk, $res_chk) > 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                ////// ��Ͽ���� ����¹�
                $query_del = sprintf("DELETE FROM assembly_schedule WHERE plan_no='%s'", $data[1]);
                if ( ($del_num = query_affected_trans($con, $query_del)) != 1) {  // �����ѥ����꡼�μ¹�
                    $log_date = date('Y-m-d H:i:s');
                    fwrite($fpa, "$log_date �ײ��ֹ�:{$data[1]} : {$rec}������쥳���ɿ�{$del_num}:�쥳�����ܤ�DELETE�˼��Ԥ��ޤ���!\n");
                    // echo "$log_date �ײ��ֹ�:{$data[0]} : {$rec}������쥳���ɿ�{$del_num}:�쥳�����ܤ�DELETE�˼��Ԥ��ޤ���!\n";
                    // query_affected_trans($con, "rollback");     // transaction rollback
                    $rec_ng++;
                    ////////////////////////////////////////// Debug start
                    for ($f=0; $f<$num; $f++) {
                        fwrite($fpw,"'{$data[$f]}',");      // debug
                    }
                    fwrite($fpw,"\n");                      // debug
                    fwrite($fpw, "$query_del \n");              // debug
                    break;                                  // debug
                    ////////////////////////////////////////// Debug end
                } else {
                    $rec_ok++;
                    $del_ok++;
                }
            } else {
                $del_old++;
            }
        }
    }
    fclose($fp);
    fclose($fpw);       // debug
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date ��Ω�������ι���:{$data[1]} : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date ��Ω�������ι���:{$data[1]} : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date ��Ω�������ι���:{$data[1]} : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpa, "$log_date ��Ω�������ι���:{$data[1]} : {$del_ok}/{$rec} �� ��� \n");
    fwrite($fpa, "$log_date ��Ω�������ι���:{$data[1]} : {$del_old}/{$rec} �� ����� \n");
    // echo "$log_date ��Ω�������ι���:{$data[1]} : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    // echo "$log_date ��Ω�������ι���:{$data[1]} : {$ins_ok}/{$rec} �� �ɲ� \n";
    // echo "$log_date ��Ω�������ι���:{$data[1]} : {$upd_ok}/{$rec} �� �ѹ� \n";
    // echo "$log_date ��Ω�������ι���:{$data[1]} : {$del_ok}/{$rec} �� ��� \n";
    // echo "$log_date ��Ω�������ι���:{$data[1]} : {$del_old}/{$rec} �� ����� \n";
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa,"$log_date �ե�����$file_orign ������ޤ���!\n");
    // echo "{$log_date}: file:{$file_orign} ������ޤ���!\n";
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'COMMIT');
// echo $query . "\n";  // debug
fclose($fpa);      ////// �����ѥ�����߽�λ

exit();


function checkControlFile($fpa, $ctl_file)
{
    $fp = fopen($ctl_file, 'r');
    $data = fgets($fp, 20);     // �¥쥳���ɤ�11�Х��ȤʤΤǤ���ä�;͵��
    if (feof($fp)) {
        return false;
    } else {
        $log_date = date('Y-m-d H:i:s');        ///// ��������
        fwrite($fpa, "$log_date ��Ω�ײ���������� : ���Ѿ����� {$data}");
        return true;
    }
}

function ftpGetCheckAndExecute($stream, $local_file, $as400_file, $ftp)
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
