#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// Web-EDI ���顼�� ��ưFTP Download cron �ǽ�����       ���ޥ�ɥ饤���� //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2010-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed histoy                                                           //
// 2010/03/30 Created  webedi_error_log_get_ftp.php                         //
// 2010/05/07 �������ɥ쥹��Yasuhiro_Maeda@nitto-kohki.co.jp���ɲ�          //
//            ��NK����S�� ���ġ�                                            //
// 2010/05/07 �������ɥ쥹��kazumi_yoshinari@nitto-kohki.co.jp���ɲ�        //
// 2016/04/13 �������ɥ쥹��yoshimitsu_izawa@nitto-kohki.co.jp���ɲ�        //
// 2017/06/09 �������ɥ쥹����ukobai@nitto-kohki.co.jp����                //
// 2019/03/19 �������ɥ쥹���龮��ë����Ĺ�������滳��Ĺ����ë��Ĺ���ɲ�  //
// 2020/02/28 �������ɥ쥹��ryota_waki@nitto-kohki.co.jp���ɲ�              //
// 2020/04/20 �������ɥ쥹�˺�ƣ������ɲá��滳�����Ĥ���                //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��cli��)
    // ���ߤ�CLI�Ǥ�default='1', SAPI�Ǥ�default='0'�ˤʤäƤ��롣CLI�ǤΤߥ�����ץȤ����ѹ�����롣
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// �����ѥ�������
$log_name_a = '/tmp/edierror.log';
$fpa = fopen($log_name_a, 'w+');    ///// ���ƤΥ� w=���Υ���ä�
fwrite($fpa, "--------------------------------------------------------------------------------------------\n");
fwrite($fpa, "�ţģɥ����ƥࡡ�ǡ����򴹥��顼��ݡ��ȡ�AS������顼�� \n");
fwrite($fpa, "--------------------------------------------------------------------------------------------\n");
fwrite($fpa, "�쥳���ɥ��顼�ΰ١�AS�ǰʲ��Υǡ�����������ޤ���Ǥ�����\n");
fwrite($fpa, "AS�μ���ǡ������ǧ���б���ԤäƤ���������\n");
fwrite($fpa, "\n");

// FTP�Υ������åȥե�����
//define('F6ERRFP', 'UKFLIB/F6ERRFP');        // ���ʥ��롼�ץ����ɥե�����
//define('W_F6ERRFP', '/home/www/html/tnk-web/system/backup/W#F6ERRFP.TXT');  // ���ʥ��롼�ץ����ɤ�Download�ե�����

// ���ͥ���������(FTP��³�Υ����ץ�)
//if ($ftp_stream = ftp_connect(AS400_HOST)) {
    //if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        /*** ���ʥ��롼�ץ����ɥǡ��� ***/
        //if (ftp_get($ftp_stream, W_F6ERRFP, F6ERRFP, FTP_ASCII)) {
            //echo "$log_date ���ʥ��롼�ץ����� ftp_get download OK ", F6ERRFP, "��", W_F6ERRFP, "\n";
            //fwrite($fpa,"$log_date ���ʥ��롼�ץ����� ftp_get download OK " . F6ERRFP . '��' . W_F6ERRFP . "\n");
            //fwrite($fpb,"$log_date ���ʥ��롼�ץ����� ftp_get download OK " . F6ERRFP . '��' . W_F6ERRFP . "\n");
        //} else {
            //echo "$log_date ���ʥ��롼�ץ����� ftp_get() error ", F6ERRFP, "\n";
            //fwrite($fpa,"$log_date ���ʥ��롼�ץ����� ftp_get() error " . F6ERRFP . "\n");
            //fwrite($fpb,"$log_date ���ʥ��롼�ץ����� ftp_get() error " . F6ERRFP . "\n");
        //}
    //} else {
        //echo "$log_date ���ʥ��롼�ץ����� ftp_login() error \n";
        //fwrite($fpa,"$log_date ���ʥ��롼�ץ����� ftp_login() error \n");
        //fwrite($fpb,"$log_date ���ʥ��롼�ץ����� ftp_login() error \n");
    //}
    //ftp_close($ftp_stream);
//} else {
    //echo "$log_date ftp_connect() error --> ���ʥ��롼�ץ�����\n";
    //fwrite($fpa,"$log_date ftp_connect() error --> ���ʥ��롼�ץ�����\n");
    //fwrite($fpb,"$log_date ftp_connect() error --> ���ʥ��롼�ץ�����\n");
//}

/////// ��������� �ѿ� �����
//$flag1 = '';        // �����¹ԥե饰 ���
$flag2 = '';        // �����¹ԥե饰 �����ƥ�
//$flag3 = '';        // �����¹ԥե饰 ���ʻų�
//$flag4 = '';        // �����¹ԥե饰 ϫ̳�񡦷���
//$b     = 0;         // �ƥ����ȥե�����Υ쥳���ɿ�
$c     = 0;
//$d     = 0;
//$e     = 0;

// EDI���顼 �᡼���������� �������
$file_name  = '/home/guest/daily/W#F6ERR.CSV';
$file_temp  = '/home/guest/daily/Q#F6ERR.tmp';
$file_write = '/home/guest/daily/Q#F6ERR.txt';
///// ����Υǡ�������
if (file_exists($file_write)) {
    unlink($file_write);
}
if (file_exists($file_name)) {            // �ե������¸�ߥ����å�
    $fp = fopen($file_name, 'r');
    $fpw = fopen($file_temp, 'a');
    while (1) {
        $data =fgets($fp,400);
        $data = mb_convert_encoding($data, 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
        $data = mb_convert_kana($data, 'KV', 'EUC-JP'); // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ� (DB��¸�������ѤǾȲ����ɬ�פ˱�����Ⱦ���Ѵ�����)
        fwrite($fpw,$data);
        $c++;
        if (feof($fp)) {
            $c--;
            break;
        }
    }
    fclose($fp);
    fclose($fpw);
    $fp = fopen($file_temp, 'r');
    $fpw = fopen($file_write, 'a');
    while (FALSE !== ($data = fgetcsv($fp, 400, ',')) ) {    // CSV file �Ȥ����ɹ���
        if ($data[0] == '') continue;   // ���Ԥν���
        $data[8] = str_replace('\'', '"', $data[8]);  // '������ȥ��顼�ˤʤ�Τ�"���Ѵ�����
        $data[9] = str_replace('\'', '"', $data[9]);  // '������ȥ��顼�ˤʤ�Τ�"���Ѵ�����
        //$data[1] = pg_escape_string($data[1]);      // ��̾
        $data[6] = str_replace('��', 'NO', $data[6]);  // '��'��ʸ�������б�
        $data[8] = str_replace('��', 'NO', $data[8]);  // '��'��ʸ�������б�
        $data[9] = str_replace('��', 'NO', $data[9]);  // '��'��ʸ�������б�
        //$data[9] = '��';
        ///// data[0]�����ֹ��data[4]��Ͽ���϶�̳�Υ롼��奨�������פ���ɬ�פ�̵��
        fwrite($fpw,"{$data[0]}\t{$data[1]}\t{$data[2]}\t{$data[3]}\t{$data[4]}\t{$data[5]}\t{$data[6]}\t{$data[7]}\t{$data[8]}\t{$data[9]}\n");
        ///// ʸ������(��̾��)��","�����ä����� fgetcsv()�ˤޤ����롣
    }
    fclose($fp);
    fclose($fpw);
    // unlink($file_name);     // ����ե�������� CSV
    // unlink($file_temp);     // ����ե�������� tmp
    if (file_exists("{$file_name}.bak")) {
        unlink("{$file_name}.bak");         // ����Υǡ�������
    }
    if (file_exists("{$file_temp}.bak")) {
        unlink("{$file_temp}.bak");         // ����Υǡ�������
    }
    if (!rename($file_name, "{$file_name}.bak")) {
        //echo "$log_date DownLoad File $file_name ��Backup�Ǥ��ޤ���\n";
    }
    if (!rename($file_temp, "{$file_temp}.bak")) {
        //echo "$log_date DownLoad File $file_temp ��Backup�Ǥ��ޤ���\n";
    }
    // exit(); // debug��
}


/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');    // ������Ͽ�Ѥˤϥ����ȥ�����
} else {
    //fwrite($fpa, "$log_date db_connect() error \n");
    //fwrite($fpb, "$log_date db_connect() error \n");
    //echo "$log_date db_connect() error \n";
    exit();
}

// ���ʥ��롼�ץ����ɥޥ����� �������
$file_name = '/home/guest/daily/Q#F6ERR.txt';
$file_name_bak = '/home/guest/daily/Q#F6ERR-bak.txt';
if (file_exists($file_name)) {            // �ե������¸�ߥ����å�
    $rowcsv = 0;        // read file record number
    $row_in = 0;        // insert record number �����������˥�����ȥ��å�
    $row_up = 0;        // update record number   ��
    $rec_ok = 0;        // �������������
    $mshgnm_ng_flg = FALSE;      // �ģ½���ߣΣǥե饰
    if ( ($fp = fopen($file_name, 'r')) ) {
        while ($data = fgetcsv($fp, 400, "\t")) {
        // while ($data = fgetcsv($fp, 200, "_")) {     // FTP��³��
            if ($data[0] == '') continue;   // ���Ԥν���
            // $num = count($data);     // CSV File �� field ��
            $rowcsv++;
            //$data[8] = addslashes($data[8]);    // "'"�����ǡ����ˤ������\�ǥ��������פ���
            //$data[1] = trim($data[1]);          // ����̾������Υ��ڡ������� AS/400��PCIX����Ѥ���FTPž���Τ���
            ///////// ��Ͽ�ѤߤΥ����å�
            $query_chk = sprintf("select * from f6errfp where f6date='%d' and f6time='%d' and f6pgid='%s' and f6key='%s'", $data[0], $data[1], $data[2], $data[6]);
            if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                ///// ��Ͽ�ʤ� insert ����
                $query = sprintf("insert into f6errfp (f6date, f6time, f6pgid, f6job, f6user, f6jbnr, f6key, f6step, f6ems1, f6ems2)
                        values('%d','%d','%s','%s','%s','%s','%s','%s','%s','%s')", $data[0],$data[1],$data[2],$data[3],$data[4],$data[5],$data[6],$data[7],$data[8],$data[9]);
                if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                    //echo "$log_date {$rowcsv}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n";
                    //fwrite($fpa, "$log_date {$rowcsv}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                    //fwrite($fpb, "$log_date {$rowcsv}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                    $mshgnm_ng_flg = TRUE;
                    break;          // NG �Τ���ȴ����
                } else {
                    $row_in++;      // insert ����
                    $rec_ok++;      // �������������
                    $flag2  = 1;     // insert �ǡ���������Ǥ⤢���on
                    $t_ymd  = $data[0];
                    $nen    = substr($t_ymd, 0, 4);
                    $tsuki  = substr($t_ymd, 4, 2);
                    $hi     = substr($t_ymd, 6, 2);
                    $v_ymd  = $nen . "/" . $tsuki . "/" . $hi;
                    $t_time = sprintf("%06d", $data[1]); 
                    $hour   = substr($t_time, 0, 2);
                    $minu   = substr($t_time, 2, 2);
                    $seco   = substr($t_time, 4, 2);
                    $v_time = $hour . ":" . $minu . ":" . $seco;
                    fwrite($fpa, "��������������������������������������������������������������������������������������������\n");
                    fwrite($fpa, "[ȯ����]��{$v_ymd}����[ȯ������]��$v_time\n");
                    fwrite($fpa, "------------------------------------------------------------------------------------------\n");
                    fwrite($fpa, "[ȯ��PGM]����[ȯ�������]����[ȯ���桼����]����[ȯ��������ֹ�]����[STEP]\n");
                    fwrite($fpa, "$data[2]����$data[3]��������$data[4]����������$data[5]����������$data[7]\n");
                    fwrite($fpa, "------------------------------------------------------------------------------------------\n");
                    fwrite($fpa, "[��������]��$data[6]\n");
                    fwrite($fpa, "------------------------------------------------------------------------------------------\n");
                    fwrite($fpa, "[���顼��å�������]��$data[8]\n");
                    fwrite($fpa, "------------------------------------------------------------------------------------------\n");
                    fwrite($fpa, "[���顼��å�������]��$data[9]\n");
                    fwrite($fpa, "��������������������������������������������������������������������������������������������\n\n\n");
                    
                }
            } else { // UPDATE������
                ///// ��Ͽ���� update ����
                //$query = sprintf("update mshgnm set mhgcd='%s', mhgnm='%s'
                //        where mhgcd='%s'", $data[0], $data[1], $data[0]);
                //if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                //    echo "$log_date {$rowcsv}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n";
                //    fwrite($fpa, "$log_date {$rowcsv}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                //    fwrite($fpb, "$log_date {$rowcsv}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                //    $mshgnm_ng_flg = TRUE;
                //    break;          // NG �Τ���ȴ����
                //} else {
                //    $row_up++;      // update ����
                //    $rec_ok++;      // �������������
                //}
            }
        }
    } else {
        //echo "Q#MSHGNM.txt�򥪡��ץ����ޤ���\n";
        //fwrite($fpa,"".$rowcsv."W#MSHGNM.txt�򥪡��ץ����ޤ���\n");
        //fwrite($fpb,"".$rowcsv."W#MSHGNM.txt�򥪡��ץ����ޤ���\n");
    }
    /**********
      ///////////////////////////////////////////////// stderr(2)����stdout(1)�� 2>&1
    $result2 = `/usr/local/pgsql/bin/psql TnkSQL < /home/www/html/weekly/qmiitem 2>&1`;
    unlink($file_name);
    ***********/
    fclose($fp);
    if (file_exists($file_name_bak)) unlink($file_name_bak);    // ����ΥХå����åפ���
    if (!rename($file_name, $file_name_bak)) {                  // ����Υǡ�����Хå����å�
        //echo "$log_date {$file_name} ��Backup�Ǥ��ޤ���\n";
        //fwrite($fpa,"".$log_date." ".$file_name." ��Backup�Ǥ��ޤ���\n");
        //fwrite($fpb,"".$log_date." ".$file_name." ��Backup�Ǥ��ޤ���\n");
    }
}

/******** �᡼������(insert�����ĤǤ⤢�ä���  *********/
fwrite($fpa, "--------------------------------------------------------------------------------------------\n");
fwrite($fpa, "                    * * ô���Ԥˤ��Ϥ��������� * *\n");
if ($flag2 == 1 ) {
    if (rewind($fpa)) {
        $to = 'jsystem2@nitto-kohki.co.jp, norihisa_ooya@nitto-kohki.co.jp, ryota_waki@nitto-kohki.co.jp, hajime_nakayama@nitto-kohki.co.jp, kazumi_yoshinari@nitto-kohki.co.jp, hiroshi_shibuya@nitto-kohki.co.jp, yoshimitsu_izawa@nitto-kohki.co.jp, Takuya_Sato@nitto-kohki.co.jp';
        // �ƥ�����
        //$to = 'norihisa_ooya@nitto-kohki.co.jp';
        $subject = "NKG-EDI System DataExchange_ErrorMail(AS¦)";
        $msg = fread($fpa, filesize($log_name_a));
        $header = "From: jsystem2@nitto-kohki.co.jp\r\n";
        mb_send_mail($to, $subject, $msg, $header);
    }
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');    // ������Ͽ�Ѥˤϥ����ȥ�����
fclose($fpa);      ////// EDI���顼�������߽�λ

?>
