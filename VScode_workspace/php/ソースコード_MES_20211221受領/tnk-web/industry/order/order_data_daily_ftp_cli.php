#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ����ǡ���(��ʸ��ȯ�ԥǡ���) ��ưFTP Download cron�ǽ����� cli��         //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2004-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2004/11/11 Created  order_data_daily_ftp_cli.php                         //
// 2004/11/18 ��ʸ�ֹ�� 1 00000 ? �������UPDATE���� ��                    //
//                 vendor��ޤ᤿�������򳰤���vendor��UPDATE�оݤˤ���   //
// 2004/12/10 order_process���ɲä�ɬ�פʻ� locate='30   ' ���ɲ�           //
// 2005/05/31 php-5.0.2-cli �� php (�ѹ�����5.0.4)��˺ǿ��Ǥ��ѹ�          //
//            order_data_difference_update_cron.php�Ȥ�deadlock���򤱤�sleep//
// 2005/07/25 �������� ����ȥ���ե�������å��Υ��å����ɲ�         //
// 2007/07/30 AS/400�Ȥ�FTP error �б��Τ��� ftpCheckAndExecute()�ؿ����ɲ� //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ(CLI�Ǥ��оݳ�)
require_once ('/home/www/html/tnk-web/function.php');

sleep(5);  // order_data_difference_update_cron.php �ι����� deadlock�򵯤������Ե�������

$log_date = date('Y-m-d H:i:s');        ///// Ʊ���ѥ�������
$fpa = fopen('/tmp/order_data_daily.log', 'a');    ///// Ʊ���ѥ��ե�����ؤν���ߤǥ����ץ�

// FTP�Υ������åȥե�����
$target_file = 'UKWLIB/W#TIORDR';       // ��ʸ��ȯ�ԥե����� download file
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
$save_file = '/home/www/html/tnk-web/system/backup/W#TIORDR.TXT';     // ��ʸ������ʬ�ե����� save file

// ���ͥ���������(FTP��³�Υ����ץ�)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        if (ftpCheckAndExecute($ftp_stream, $save_file, $target_file, FTP_ASCII)) {
            fwrite($fpa,"$log_date ftp_get download OK " . $target_file . '��' . $save_file . "\n");
        } else {
            fwrite($fpa,"$log_date ftp_get() error " . $target_file . "\n");
        }
    } else {
        fwrite($fpa,"$log_date ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    fwrite($fpa,"$log_date ftp_connect() error --> ��ʸ������ʬ\n");
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
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    echo "$log_date db_connect() error \n";
    exit();
}
// ��ʸ������ʬȯ�ԥե����� Ʊ������ �������
$today = date('Ymd');   // ȯ�����Υ��å�
$file_orign  = $save_file;
$file_debug  = '/home/www/html/tnk-web/system/debug/debug-TIORDR.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 100, '_');     // �¥쥳���ɤ�82�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���'_'�����
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num < 9) {    // �ºݤˤ� 21 ����(�Ǹ夬�ʤ���礬���뤿��)
            if ($num == 0 || $num == 1) {   // php-4.3.5��0�֤� php-4.3.6��1���֤����ͤˤʤä���fgetcsv�λ����ѹ��ˤ��
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
            } else {
                fwrite($fpa, "$log_date field not 9 record=$rec \n");
            }
           continue;
        } elseif ($data[0] == '0000000') {
            // ���칩���ͭ���ٵ������Τ���
           continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        }
        ///////////// �����ֹ�򥻥åȤ��롣
        $sql = "select kouji_no from order_plan where sei_no = {$data[2]}";
        $kouji_no = '';     // �����
        if (getUniResTrs($con, $sql, $kouji_no) <= 0) {
            fwrite($fpa, "$log_date order_plan Not kouji_no sei_no={$data[2]} \n");
        }
        ///// ��������ʸ�ֹ�� �㡧1492151 �������UPDATE����
        $sql = "select order_no from order_process where sei_no={$data[2]} and order_no={$data[3]} and vendor='{$data[5]}'";
        if (getUniResTrs($con, $sql, $res_chk) > 0) {
            ///// UPDATE �¹�
            $query = "UPDATE order_process SET
                            order_date  = {$today},
                            delivery    = {$data[8]},
                            plan_cond   = 'O',
                            order_no    = {$data[3]}
                        where
                            sei_no={$data[2]} and order_no={$data[3]} and vendor='{$data[5]}'
            ";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date order_process UPDATE NG:\n{$query}\n");
            }
        } else {
            //////////// order_process ����ʸ�ֹ�� 100000? �������UPDATE����
            $serch_order = ('100000' . substr($data[3], -1) );  // �����ֹ���ɲ�
            $sql = "select order_no from order_process where sei_no={$data[2]} and order_no={$serch_order}";
            if (getUniResTrs($con, $sql, $res_chk) > 0) {
                ///// UPDATE �¹�
                $query = "UPDATE order_process SET
                                order_date  =  {$today}     ,
                                delivery    =  {$data[8]}   ,
                                plan_cond   = 'O'           ,
                                order_no    =  {$data[3]}   ,
                                vendor      = '{$data[5]}'
                            where
                                sei_no={$data[2]} and order_no={$serch_order}
                ";
                if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                    fwrite($fpa, "$log_date order_process UPDATE NG:\n{$query}\n");
                }
            } else {
                ///// INSERT �¹�   (��ư�ˤ��ȯ������ݼ�����Ԥä������б�)
                $query = "INSERT INTO order_process
                                (
                                    order_no,
                                    vendor  ,
                                    sei_no  ,
                                    parts_no,
                                    order_q ,
                                    order_price,
                                    delivery,
                                    plan_cond,
                                    locate,
                                    order_date
                                )
                            values
                                (
                                     {$data[3]} ,
                                    '{$data[5]}',
                                     {$data[2]} ,
                                    '{$data[4]}',
                                     {$data[6]} ,
                                     {$data[7]} ,
                                     {$data[8]} ,
                                    'O',
                                    '30   ',
                                    {$today}
                                )
                ";
                if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                    fwrite($fpa, "$log_date order_process INSERT NG:\n{$query}\n");
                }
            }
        }
        $query_chk = sprintf("SELECT order_seq FROM order_data
                                WHERE order_seq=%d",
                                $data[0]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "INSERT INTO order_data
                            (
                                order_seq   ,
                                pre_seq     ,
                                sei_no      ,
                                order_no    ,
                                parts_no    ,
                                vendor      ,
                                order_q     ,
                                order_price ,
                                delivery    ,
                                date_issue  ,
                                kouji_no    ,
                                uke_date    ,
                                ken_date    ,
                                cut_date    ,
                                cut_genpin  ,
                                cut_siharai
                            )
                      VALUES
                      (
                           {$data[0]} ,
                           {$data[1]} ,
                           {$data[2]} ,
                           {$data[3]} ,
                          '{$data[4]}',
                          '{$data[5]}',
                           {$data[6]} ,
                           {$data[7]} ,
                           {$data[8]} ,
                           {$today}   ,
                          '{$kouji_no}',
                           0,
                           0,
                           0,
                           0,
                           0
                      )
            ";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date ȯ���ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
            ///// ��Ͽ���� update ���Ѥ��� �ȤФ���
            $upd_ok++;
        }
    }
    fclose($fp);
    fclose($fpw);       // debug
    fwrite($fpa, "$log_date order_data Chk_ok/recorde: {$rec_ok}/{$rec} ���о�\n");
    fwrite($fpa, "$log_date order_data INSERT/recorde: {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date order_data   NOT UPDATE  : {$upd_ok}/{$rec} �� \n");
} else {
    fwrite($fpa,"$log_date �ե�����$file_orign ������ޤ���!\n");
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');
// echo $query . "\n";  // debug
fclose($fp_ctl);   ////// Exclusive�ѥե����륯����
fclose($fpa);      ////// �����ѥ�����߽�λ

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
