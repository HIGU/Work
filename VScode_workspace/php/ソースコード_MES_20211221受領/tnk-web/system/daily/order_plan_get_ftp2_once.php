#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ȯ��ײ�F ��AS/400�Ȥδ����ǡ�����󥯤Τ��� CHECK DATA DOWNLOAD   CLI�� //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/02/17 Created  order_plan_get_ftp2.php                              //
//                                      allocated_parts_ftp2.php �򸵤˺��� //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
// ini_set('max_execution_time', 1200);    // ����¹Ի���=20ʬ (CLI�Ǥ�ɬ�פʤ���
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');    ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�

// FTP�Υ������åȥե�����2
define('D_MIOPLN', 'UKWLIB/W#MIOPLD');      // ȯ��ײ�ե����� download file
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
define('S_MIOPLN', '/home/www/html/tnk-web/system/backup/W#MIOPLD.TXT');  // ȯ��ײ�ե����� save file

// ���ͥ���������(FTP��³�Υ����ץ�)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// ȯ��ײ�ե�����
        if (ftp_get($ftp_stream, S_MIOPLN, D_MIOPLN, FTP_ASCII)) {
            // echo 'ftp_get download OK ', D_MIOPLN, '��', S_MIOPLN, "\n";
            fwrite($fpa,"$log_date ftp_get download OK " . D_MIOPLN . '��' . S_MIOPLN . "\n");
        } else {
            // echo 'ftp_get() error ', D_MIOPLN, "\n";
            fwrite($fpa,"$log_date ftp_get() error " . D_MIOPLN . "\n");
        }
    } else {
        // echo "ftp_login() error \n";
        fwrite($fpa,"$log_date ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    // echo "ftp_connect() error --> ȯ��ײ�κ��ۥǡ�������\n";
    fwrite($fpa,"$log_date ftp_connect() error --> ȯ��ײ�κ��ۥǡ�������\n");
}



/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    // echo "$log_date db_connect() error \n";
    exit();
}
// ȯ��ײ�ե����� ȯ��ײ�κ��ۥǡ������� �������
$file_orign  = S_MIOPLN;
$file_debug  = '/home/www/html/tnk-web/system/debug/debug-MIOPLD.TXT';
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
        $data = fgetcsv($fp, 100, '_');     // �¥쥳���ɤ�54�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���'_'�����
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num != 7) {
            if ($num == 0 || $num == 1) {   // php-4.3.5��0�֤� php-4.3.6��1���֤����ͤˤʤä���fgetcsv�λ����ѹ��ˤ��
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
                // echo "$log_date AS/400 del record=$rec \n";
            } else {
                fwrite($fpa, "$log_date field not 7 record=$rec \n");
                // echo "$log_date field not 11 record=$rec \n";
            }
           continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        }
        ////////// ���ơ����������å� (C=�ѹ�ʬ D=���ʬ) (����� A=�ɲ�ʬ �Ϥʤ�)
        if ($data[6] == 'C') {
            $query_chk = sprintf("SELECT parts_no FROM order_plan WHERE sei_no=%s", $data[0]);
            if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                ///// ��Ͽ�ʤ� �ʤˤ⤷�ʤ�
            } else {
                ///// ��Ͽ���� update ����
                $zan_q = ($data[3] - $data[4] - $data[5]);  // ��������ĤϷ׻��ǽФ�
                $query = "UPDATE order_plan SET
                                order_q     = {$data[3]} ,
                                utikiri     = {$data[4]} ,
                                nyuko       = {$data[5]} ,
                                zan_q       = {$zan_q}    
                    WHERE sei_no={$data[0]}";
                if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                    fwrite($fpa, "$log_date ��¤�ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                    // echo "$log_date ��¤�ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n";
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
        } elseif ($data[6] == 'D') {    //////// D=���ʬ�ν���
            $query_chk = sprintf("SELECT parts_no FROM order_plan WHERE sei_no=%s", $data[0]);
            if (getUniResTrs($con, $query_chk, $res_chk) > 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                ////// ��Ͽ���� ����¹�
                $query_del = sprintf("DELETE FROM order_plan WHERE sei_no=%s", $data[0]);
                if ( ($del_num = query_affected_trans($con, $query_del)) != 1) {  // �����ѥ����꡼�μ¹�
                    fwrite($fpa, "$log_date ��¤�ֹ�:{$data[0]} : {$rec}������쥳���ɿ�{$del_num}:�쥳�����ܤ�DELETE�˼��Ԥ��ޤ���!\n");
                    // echo "$log_date ��¤�ֹ�:{$data[0]} : {$rec}������쥳���ɿ�{$del_num}:�쥳�����ܤ�DELETE�˼��Ԥ��ޤ���!\n";
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
    fwrite($fpa, "$log_date ȯ��ײ�κ��ۥǡ������� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date ȯ��ײ�κ��ۥǡ������� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date ȯ��ײ�κ��ۥǡ������� : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpa, "$log_date ȯ��ײ�κ��ۥǡ������� : {$del_ok}/{$rec} �� ��� \n");
    fwrite($fpa, "$log_date ȯ��ײ�κ��ۥǡ������� : {$del_old}/{$rec} �� ����� \n");
    // echo "$log_date ȯ��ײ�κ��ۥǡ������� : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    // echo "$log_date ȯ��ײ�κ��ۥǡ������� : {$ins_ok}/{$rec} �� �ɲ� \n";
    // echo "$log_date ȯ��ײ�κ��ۥǡ������� : {$upd_ok}/{$rec} �� �ѹ� \n";
    // echo "$log_date ȯ��ײ�κ��ۥǡ������� : {$del_ok}/{$rec} �� ��� \n";
    // echo "$log_date ȯ��ײ�κ��ۥǡ������� : {$del_old}/{$rec} �� ����� \n";
} else {
    fwrite($fpa,"$log_date �ե�����$file_orign ������ޤ���!\n");
    // echo "{$log_date}: file:{$file_orign} ������ޤ���!\n";
}
$query_chk = sprintf("SELECT sei_no FROM order_plan WHERE plan_cond='R'");
$res=array();
if($rows=getResult($query_chk,$res)){
    for($i=0;$i<$rows;$i++){
        $del_chk = sprintf("SELECT * FROM order_plan WHERE sei_no=%s AND plan_cond='O'", $res[0][0]);
        if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
        } else {
            
        }
    }
}

/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, "commit");
// echo $query . "\n";  // debug
fclose($fpa);      ////// ȯ��ײ�κ��ۥǡ����ѥ�����߽�λ
?>
