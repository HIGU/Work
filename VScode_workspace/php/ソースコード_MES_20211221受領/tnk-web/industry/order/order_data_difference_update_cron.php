#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ��ʸ��ȯ�ԥǡ�����ʬ�������� ��ưFTP Download & Update                   //
// (���ա�����ʬ)     AS/400 ----> Web Server (PHP)   cli(cron)��           //
// Copyright (C) 2004-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2004/10/13 Created  order_data_difference_update_cron.php                //
// 2004/10/21 order_data��ken_date�򥹥����塼���10ʬ�����˹������Ƥ��뤿��//
//         ��������ken_date��'0'�ǹ����褬�������դ����äƤ�����繹�����ʤ�//
// 2005/05/25 php-5.0.2-cli �� php (�ǿ���) �ѹ�����php 5.0.4               //
//            order_data �򹹿��������� order_process ��Ʊ�����뤿�ṹ��//
// 2005/05/30 order_process �ι����оݤ���¤�ֹ����ʸ�ֹ椬�����Τ˸���  //
// 2005/07/25 �������� ����ȥ���ե�������å��Υ��å����ɲ�         //
// 2007/02/14 checkTableChange()����������˹������Ƥ�����Ϲ������ʤ�    //
// 2007/07/30 AS/400�Ȥ�FTP error �б��Τ��� ftpCheckAndExecute()�ؿ����ɲ� //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��cli��)
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');            // �����ѥ�������
$fpa = fopen('/tmp/order_data_difference.log', 'a');    ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�

// FTP�Υ������åȥե�����
$target_file = 'UKWLIB/W#HIMKUK';       // ��ʸ��ȯ�ԥե����� download file
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
$save_file = '/home/www/html/tnk-web/industry/order/backup/W#HIMKUK.TXT';     // ��ʸ��ȯ�ԥե����� save file

// ���ͥ���������(FTP��³�Υ����ץ�)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// ��ʸ��ȯ�ԥե�����
        if (ftpCheckAndExecute($ftp_stream, $save_file, $target_file, FTP_ASCII)) {
            fwrite($fpa,"$log_date ftp_get download ���� " . $target_file . '��' . $save_file . "\n");
        } else {
            fwrite($fpa,"$log_date ftp_get() error " . $target_file . "\n");
            exit;
        }
    } else {
        fwrite($fpa,"$log_date ftp_login() error \n");
        exit;
    }
    ftp_close($ftp_stream);
} else {
    fwrite($fpa,"$log_date ftp_connect() error --> $target_file\n");
    exit;
}

/////////// �������� ����ȥ���ե�������å�
do {
    if ($fp_ctl = fopen('/tmp/order_process_lock', 'w')) {
        flock($fp_ctl, LOCK_EX);
        fwrite($fp_ctl, "$log_date " . __FILE__ . "\n");
        break;
    } else {
        sleep(5);   // ����ߤǥ����ץ����ʤ���У����Ե�
        continue;
    }
} while (0);

/////////// begin �ȥ�󥶥�����󳫻�
$log_date = date('Y-m-d H:i:s');            // �����ѥ�������
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    exit;
}
// ��ʸ��ȯ�ԥե����� ��ʬ�������� �������
$file_orign  = $save_file;
$file_debug  = '/home/www/html/tnk-web/industry/order/backup/debug-HIMKUK.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    $upd2_ok= 0;    // UPDATE�ѥ����󥿡�2
    $notupd = 0;    // ���������ѥ����󥿡�
    $upd_no = 0;    // �󹹿������󥿡�
    $log_date = date('Y-m-d H:i:s');            // �����ѥ�������
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
            } else {
                fwrite($fpa, "$log_date field not 20 record=$rec \n");
            }
           continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        }
        $query_chk = sprintf("SELECT order_seq, uke_no, uke_date, uke_q, ken_date, genpin, siharai FROM order_data
                                WHERE order_seq=%d",
                                $data[0]);
        if (getResultTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert �ϻ��Ѥ��� ���顼�Ȥ���(��ʬ�����Τ���)
            $notupd++;
            // fwrite($fpa, "$log_date ȯ��Ϣ�֡�{$data[0]} �����Ĥ���ʤ� �������� \n");
        } else {
            ///// ��Ͽ���� update ����
            if ( ($res_chk[0][4] != 0) && ($data[4] == 0) ) {  // $res_chk[0][4]=ken_date
                $upd_no++;
                continue;   // �����������Ѥˤ������
            }
            if (checkTableChange($data, $res_chk[0])) {
                $upd_no++;
                continue;   // ���˹����ѤߤΤ��ṹ�����ʤ�
            }
            $query = "UPDATE order_data SET
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
                $query = "SELECT sei_no, order_no, vendor FROM order_data WHERE order_seq={$data[0]}";
                if (getResultTrs($con, $query, $res) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                    fwrite($fpa, "$log_date ȯ���ֹ�:{$data[0]} : ����¤�ֹ桦��ʸ�ֹ桦�٥�����μ����˼��Ԥ��ޤ���!\n");
                } else {
                    if ( ($res[0][0] != 0) && ($res[0][1] != 0) ) { // ��¤�ֹ����ʸ�ֹ椬�����Τ��о�
                        $query = "SELECT sum(genpin), sum(siharai), sum(cut_genpin), sum(cut_siharai) FROM order_data WHERE sei_no={$res[0][0]} and order_no={$res[0][1]} and vendor='{$res[0][2]}'";
                        if (getResultTrs($con, $query, $res2) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                            fwrite($fpa, "$log_date ��¤�ֹ�:{$res[0][0]} : ��ʸ�ֹ�:{$res[0][1]} : �٥����:{$res[0][2]} �ι�׸��ʿ�����ʧ���μ����˼��Ԥ��ޤ���!\n");
                        } else {
                            $query = "UPDATE order_process SET genpin={$res2[0][0]}, siharai={$res2[0][1]}, cut_genpin={$res2[0][2]}, cut_siharai={$res2[0][3]} WHERE sei_no={$res[0][0]} and order_no={$res[0][1]} and vendor='{$res[0][2]}'";
                            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                                fwrite($fpa, "$log_date UPDATE�˼��Ԥ��ޤ���!\n  SQLʸ={$query}\n");
                            } else {
                                $upd2_ok++;
                            }
                        }
                    }
                }
            }
        }
    }
    fclose($fp);
    fclose($fpw);       // debug
    fwrite($fpa, "$log_date ��ʸ��ȯ��F�κ�ʬ����:{$data[2]} : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date ��ʸ��ȯ��F�κ�ʬ����:{$data[2]} : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpa, "$log_date ��ʸ��ȯ��F�κ�ʬ����:{$data[2]} : {$notupd}/{$rec} �� �������� \n");
    fwrite($fpa, "$log_date ��ʸ��ȯ��F�κ�ʬ����:{$data[2]} : {$upd_no}/{$rec} �� �󹹿� \n");
    fwrite($fpa, "$log_date ȯ�������٤κ�ʬ����:{$data[2]}: {$upd2_ok}/{$rec} �� �ѹ� \n");
} else {
    fwrite($fpa,"$log_date �ե�����$file_orign ������ޤ���!\n");
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');
fclose($fp_ctl);   ////// Exclusive�ѥե����륯����
fclose($fpa);      ////// �����ѥ�����߽�λ

exit();

/***** �ơ��֥뤬�ѹ�����Ƥ������false���֤�     *****/
/***** ��������Ӥ���ǡ���������ȥơ��֥������   *****/
function checkTableChange($data, $res)
{
    for ($i=1; $i<7; $i++) {    // $data[6]�ޤǤ�7colmun������å�����
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
