#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ����ñ���졼�ȷ���ޥ�������ưFTP Download cron �ǽ����ѥ��ޥ�ɥ饤���� //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2013-2013 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// AS UKSLIB/QCLSRC \TNKDAILYC��LOOP�����˰ʲ�����Ͽ���뤳��                //
// SNDF       RCDFMT(TITLE)                                                 //
// SNDF       RCDFMT(TANRATE)                                               //
// RUNQRY     QRY(UKPLIB/Q#TANRATE)                                         //
// \FTPTNK    USER(AS400) ASFILE(W#TANRATE) PCFILE(W#TANRATE.CSV) MODE(CSV) //
// Changed histoy                                                           //
// 2013/05/27 Created  parts_rate_history_update_ftp.php                    //
// 2013/06/05 AS�Υץ���ब�ְ�äƤ����Τ�����                          //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��cli��)
    // ���ߤ�CLI�Ǥ�default='1', SAPI�Ǥ�default='0'�ˤʤäƤ��롣CLI�ǤΤߥ�����ץȤ����ѹ�����롣
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');    ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤǥ����ץ�
fwrite($fpb, "����ñ���졼�ȷ���ι���\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/parts_rate_history_update_ftp.php \n");

/////// ��������� �ѿ� �����
$flag1 = '';        // �����¹ԥե饰 ���
$flag2 = '';        // �����¹ԥե饰 �����ƥ�
$flag3 = '';        // �����¹ԥե饰 ���ʻų�
$flag4 = '';        // �����¹ԥե饰 ϫ̳�񡦷���
$b     = 0;         // �ƥ����ȥե�����Υ쥳���ɿ�
$c     = 0;
$d     = 0;
$e     = 0;

// ñ���졼�ȶ�ʬ ������� �������
$file_name  = '/home/guest/daily/W#TANRATE.CSV';
$file_temp  = '/home/guest/daily/Q#TANRATE.tmp';
$file_write = '/home/guest/daily/Q#TANRATE.txt';

///// ����Υǡ�������
if (file_exists($file_write)) {
    unlink($file_write);
}
if (file_exists($file_name)) {            // �ե������¸�ߥ����å�
    $fp = fopen($file_name, 'r');
    $fpw = fopen($file_temp, 'a');
    while (1) {
        $data=fgets($fp,200);
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
    while (FALSE !== ($data = fgetcsv($fp, 300, ',')) ) {    // CSV file �Ȥ����ɹ���
        if ($data[0] == '') continue;   // ���Ԥν���
        //$data[2] = str_replace('"', '', $data[2]);  // �ʤ�����"��������֤������Τȡ�ޤǽ���ޤ��ΤǺ������
                                                    // �嵭�ϲ���pg_escape_string()����������Ǥ���
        //$data[2] = pg_escape_string($data[2]);      // ̾��
        ///// data[0]�����ֹ��data[4]��Ͽ���϶�̳�Υ롼��奨�������פ���ɬ�פ�̵��
        fwrite($fpw,"{$data[0]}\t{$data[1]}\t{$data[2]}\n");
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
        echo "$log_date DownLoad File $file_name ��Backup�Ǥ��ޤ���\n";
    }
    if (!rename($file_temp, "{$file_temp}.bak")) {
        echo "$log_date DownLoad File $file_temp ��Backup�Ǥ��ޤ���\n";
    }
    // exit(); // debug��
}


/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');    // ������Ͽ�Ѥˤϥ����ȥ�����
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    fwrite($fpb, "$log_date db_connect() error \n");
    echo "$log_date db_connect() error \n";
    exit();
}

// ����ñ���졼�ȷ��� �������
$file_name = '/home/guest/daily/Q#TANRATE.txt';
$file_name_bak = '/home/guest/daily/Q#TANRATE-bak.txt';
if (file_exists($file_name)) {            // �ե������¸�ߥ����å�
    $rowcsv = 0;        // read file record number
    $row_in = 0;        // insert record number �����������˥�����ȥ��å�
    $row_up = 0;        // update record number   ��
    $rec_ok = 0;        // �������������
    $msshg3_ng_flg = FALSE;      // �ģ½���ߣΣǥե饰
    if ( ($fp = fopen($file_name, 'r')) ) {
        while ($data = fgetcsv($fp, 200, "\t")) {
        // while ($data = fgetcsv($fp, 200, "_")) {     // FTP��³��
            if ($data[0] == '') continue;   // ���Ԥν���
            // $num = count($data);     // CSV File �� field ��
            $rowcsv++;
            //$data[2] = addslashes($data[2]);    // "'"�����ǡ����ˤ������\�ǥ��������פ���
            //$data[2] = trim($data[2]);          // ̾�Τ�����Υ��ڡ������� AS/400��PCIX����Ѥ���FTPž���Τ���
            ///////// ��Ͽ�ѤߤΥ����å�
            $query_chk = sprintf("select rate_div from parts_rate_history where parts_no='%s' and reg_no='%s'", $data[0],$data[1]);
            if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                ///// ��Ͽ�ʤ� insert ����
                $query = sprintf("insert into parts_rate_history (parts_no,reg_no,rate_div)
                        values('%s','%s','%s')", $data[0],$data[1],$data[2]);
                if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                    echo "$log_date {$rowcsv}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n";
                    fwrite($fpa, "$log_date {$rowcsv}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                    fwrite($fpb, "$log_date {$rowcsv}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                    $msshg3_ng_flg = TRUE;
                    break;          // NG �Τ���ȴ����
                } else {
                    $row_in++;      // insert ����
                    $rec_ok++;      // �������������
                }
            } else {
                ///// ��Ͽ���� update ����
                $query = sprintf("update parts_rate_history set rate_div='%s'
                        where parts_no='%s' and reg_no='%s'", $data[2], $data[0], $data[1]);
                if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                    echo "$log_date {$rowcsv}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n";
                    fwrite($fpa, "$log_date {$rowcsv}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                    fwrite($fpb, "$log_date {$rowcsv}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                    $msshg3_ng_flg = TRUE;
                    break;          // NG �Τ���ȴ����
                } else {
                    $row_up++;      // update ����
                    $rec_ok++;      // �������������
                }
            }
        }
    } else {
        echo "Q#TANRATE.txt�򥪡��ץ����ޤ���\n";
        fwrite($fpa,"".$rowcsv."W#TANRATE.txt�򥪡��ץ����ޤ���\n");
        fwrite($fpb,"".$rowcsv."W#TANRATE.txt�򥪡��ץ����ޤ���\n");
    }
    /**********
      ///////////////////////////////////////////////// stderr(2)����stdout(1)�� 2>&1
    $result2 = `/usr/local/pgsql/bin/psql TnkSQL < /home/www/html/weekly/qmiitem 2>&1`;
    unlink($file_name);
    ***********/
    fclose($fp);
    if (file_exists($file_name_bak)) unlink($file_name_bak);    // ����ΥХå����åפ���
    if (!rename($file_name, $file_name_bak)) {                  // ����Υǡ�����Хå����å�
        echo "$log_date {$file_name} ��Backup�Ǥ��ޤ���\n";
        fwrite($fpa,"".$log_date." ".$file_name." ��Backup�Ǥ��ޤ���\n");
        fwrite($fpb,"".$log_date." ".$file_name." ��Backup�Ǥ��ޤ���\n");
    }
    $flag2 = 1;
}


// ��å��������֤�
if ($flag2==1) {
    echo "$log_date ����ñ���졼�ȷ���ι���: $rec_ok/$rowcsv ����Ͽ���ޤ�����\n";
    echo "$log_date ����ñ���졼�ȷ���ι���: {$row_in}/{$rowcsv} �� �ɲ� \n";
    echo "$log_date ����ñ���졼�ȷ���ι���: {$row_up}/{$rowcsv} �� �ѹ� \n";
    fwrite($fpa, "$log_date ����ñ���졼�ȷ���ι���: $rec_ok/$rowcsv ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date ����ñ���졼�ȷ���ι���: {$row_in}/{$rowcsv} �� �ɲ� \n");
    fwrite($fpa, "$log_date ����ñ���졼�ȷ���ι���: {$row_up}/{$rowcsv} �� �ѹ� \n");
    fwrite($fpb, "$log_date ����ñ���졼�ȷ���ι���: $rec_ok/$rowcsv ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date ����ñ���졼�ȷ���ι���: {$row_in}/{$rowcsv} �� �ɲ� \n");
    fwrite($fpb, "$log_date ����ñ���졼�ȷ���ι���: {$row_up}/{$rowcsv} �� �ѹ� \n");
} else {
    echo "{$log_date} ����ñ���졼�ȷ���ι����ǡ���������ޤ���\n";
    fwrite($fpa, "$log_date ����ñ���졼�ȷ���ι����ǡ���������ޤ���\n");
    fwrite($fpb, "$log_date ����ñ���졼�ȷ���ι����ǡ���������ޤ���\n");
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');    // ������Ͽ�Ѥˤϥ����ȥ�����
fclose($fpa);      ////// �����ѥ�����߽�λ
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߽�λ

?>
