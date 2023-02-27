#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ����ǡ���(�������ʥե�����) ��ưFTP Download cron�ǽ����� cgi��         //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/02/08 Created  allocated_parts_ftp.php��allocated_parts_realTime.php//
//            if ($data[10] != date('Ymd'))����ʬ�Ǥʤ�������Ф����ɲ�     //
// 2006/11/08 checkTableChange()���ɲä��ƥǡ������ѹ�����Ƥ���ʪ�Τ߹�����//
// 2007/09/11 AS/400�Ȥ�FTP error �б��Τ��� ftpCheckAndExecute()�ؿ����ɲ� //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
// ini_set('max_execution_time', 1200);    // ����¹Ի���=20ʬ CLI�Ǥϴط��ʤ�
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');    ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�

// FTP�Υ������åȥե�����
define('D_TIALLC', 'UKWLIB/W#TIALLC');      // �������ʥե����� download file
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
define('S_TIALLC', '/home/www/html/tnk-web/system/backup/W#TIALLC.TXT');  // �������ʥե����� save file

// ���ͥ���������(FTP��³�Υ����ץ�)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// �������ʥե�����
        if (ftpCheckAndExecute($ftp_stream, S_TIALLC, D_TIALLC, FTP_ASCII)) {
            // echo 'ftp_get download OK ', D_TIALLC, '��', S_TIALLC, "\n";
            fwrite($fpa,"$log_date ftp_get download OK " . D_TIALLC . '��' . S_TIALLC . "\n");
        } else {
            // echo 'ftp_get() error ', D_TIALLC, "\n";
            fwrite($fpa,"$log_date ftp_get() error " . D_TIALLC . "\n");
        }
    } else {
        // echo "ftp_login() error \n";
        fwrite($fpa,"$log_date ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    // echo "ftp_connect() error --> �������\n";
    fwrite($fpa,"$log_date ftp_connect() error --> �������\n");
}



/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    // echo "$log_date db_connect() error \n";
    exit();
}
// �������ʥե����� ������� �������
$file_orign  = S_TIALLC;
$file_debug  = '/home/www/html/tnk-web/system/debug/debug-TIALLC.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    $noChg  = 0;    // ̤�ѹ������󥿡�
    $del_ok = 0;    // DELETE�ѥ����󥿡�
    $del_old = 0;   // ������ѥ����󥿡�
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 200, '_');     // �¥쥳���ɤ�95�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���'_'�����
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num != 11) {
            if ($num == 0 || $num == 1) {   // php-4.3.5��0�֤� php-4.3.6��1���֤����ͤˤʤä���fgetcsv�λ����ѹ��ˤ��
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
                // echo "$log_date AS/400 del record=$rec \n";
            } else {
                fwrite($fpa, "$log_date field not 11 record=$rec \n");
                // echo "$log_date field not 11 record=$rec \n";
            }
           continue;
        }
        if ($data[10] != date('Ymd')) {
            continue;   // ����ʬ�Ǥʤ�������Ф� allocated_parts_ftp.php�Ȥΰ㤤�Ϥ���
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        }
        ////////// ���ơ����������å� (A=�ɲ�ʬ C=�ѹ�ʬ D=���ʬ)
        if ($data[9] == 'A' || $data[9] == 'C') {
            $query_chk = sprintf("SELECT * FROM allocated_parts
                                    WHERE plan_no='%s' and parts_no='%s'",
                                    $data[0], $data[1]);
            if (getResultTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                ///// ��Ͽ�ʤ� insert ����
                $query = "INSERT INTO allocated_parts (plan_no, parts_no, assy_no, unit_qt, allo_qt, sum_qt,
                                assy_str, cond, price, as_regdate)
                          VALUES(
                          '{$data[0]}',
                          '{$data[1]}',
                          '{$data[2]}',
                           {$data[3]} ,
                           {$data[4]} ,
                           {$data[5]} ,
                           {$data[6]} ,
                          '{$data[7]}',
                           {$data[8]},
                           {$data[10]})";
                if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                    fwrite($fpa, "$log_date �ײ��ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                    // echo "$log_date �ײ��ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n";
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
                if (checkTableChange($data, $res_chk[0])) {
                    $noChg++;
                    continue;
                }
                ///// ��Ͽ���� update ����
                $query = "UPDATE allocated_parts SET
                                plan_no     ='{$data[0]}',
                                parts_no    ='{$data[1]}',
                                assy_no     ='{$data[2]}',
                                unit_qt     = {$data[3]} ,
                                allo_qt     = {$data[4]} ,
                                sum_qt      = {$data[5]} ,
                                assy_str    = {$data[6]} ,
                                cond        ='{$data[7]}',
                                price       = {$data[8]} ,
                                as_regdate  = {$data[10]}
                    WHERE plan_no='{$data[0]}' and parts_no='{$data[1]}'";
                if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                    fwrite($fpa, "$log_date �ײ��ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
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
            }
        } else {    //////// D=���ʬ�ν���
            $query_chk = sprintf("SELECT parts_no FROM allocated_parts
                                    WHERE plan_no='%s' and parts_no='%s'",
                                    $data[0], $data[1]);
            if (getUniResTrs($con, $query_chk, $res_chk) > 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                ////// ��Ͽ���� ����¹�
                $query_del = sprintf("DELETE FROM allocated_parts
                                    WHERE plan_no='%s' and parts_no='%s'",
                                    $data[0], $data[1]);
                if ( ($del_num = query_affected_trans($con, $query_del)) != 1) {  // �����ѥ����꡼�μ¹�
                    fwrite($fpa, "$log_date �ײ��ֹ�:{$data[0]} : {$rec}������쥳���ɿ�{$del_num}:�쥳�����ܤ�DELETE�˼��Ԥ��ޤ���!\n");
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
    fwrite($fpa, "$log_date �������ʤι���:{$data[1]} : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date �������ʤι���:{$data[1]} : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date �������ʤι���:{$data[1]} : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpa, "$log_date �������ʤι���:{$data[1]} : {$noChg}/{$rec} �� ̤�ѹ� \n");
    fwrite($fpa, "$log_date �������ʤι���:{$data[1]} : {$del_ok}/{$rec} �� ��� \n");
    fwrite($fpa, "$log_date �������ʤι���:{$data[1]} : {$del_old}/{$rec} �� ����� \n");
    // echo "$log_date �������ʤι���:{$data[1]} : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    // echo "$log_date �������ʤι���:{$data[1]} : {$ins_ok}/{$rec} �� �ɲ� \n";
    // echo "$log_date �������ʤι���:{$data[1]} : {$upd_ok}/{$rec} �� �ѹ� \n";
    // echo "$log_date �������ʤι���:{$data[1]} : {$del_ok}/{$rec} �� ��� \n";
    // echo "$log_date �������ʤι���:{$data[1]} : {$del_old}/{$rec} �� ����� \n";
} else {
    fwrite($fpa,"$log_date �ե�����$file_orign ������ޤ���!\n");
    // echo "{$log_date}: file:{$file_orign} ������ޤ���!\n";
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, "commit");
// echo $query . "\n";  // debug
fclose($fpa);      ////// �����ѥ�����߽�λ

exit();

/***** �ơ��֥뤬�ѹ�����Ƥ������false���֤�     *****/
/***** ��������Ӥ���ǡ���������ȥơ��֥������   *****/
function checkTableChange($data, $res)
{
    for ($i=0; $i<9; $i++) {    // $data[8]�ޤǤ�9colmun������å�����
        // ��Ӥ˼���򤹤륹�ڡ�������
        if (trim($data[$i]) != trim($res[$i])) {
            return false;
        }
    }
    return true;
}

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
