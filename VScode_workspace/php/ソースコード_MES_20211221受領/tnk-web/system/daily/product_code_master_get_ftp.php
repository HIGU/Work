#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ���ʥ��롼�ץ����ɥޥ�������ưFTP Download cron �ǽ����ѥ��ޥ�ɥ饤���� //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2009-2011 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// AS UKSLIB/QCLSRC \TNKDAILYC��LOOP�����˰ʲ�����Ͽ���뤳��                //
// SNDF       RCDFMT(TITLE)                                                 //
// SNDF       RCDFMT(MSHGNM)                                                //
// RUNQRY     QRY(UKPLIB/Q#MSHGNM)                                          //
// \FTPTNK    USER(AS400) ASFILE(W#MSHGNM) PCFILE(Q#MSHGNM.CSV) MODE(CSV)   //
// Changed histoy                                                           //
// 2009/11/19 Created  product_code_master_get_ftp.php                      //
// 2009/12/25 FTP��ľ�ܥǡ������������褦���ѹ�                           //
// 2009/12/28 FTP��ľ�ܥǡ�������������ʸ����������Τ�CSV�������᤹  ��ë//
// 2010/01/15 AS������Ϥ���CSV��W#�ˤ��Ƥ��ޤä��ΤǤ�������ѹ�       ��ë//
// 2011/05/31 ɽ����MSHGNM����AS�Υե������ºݤ�MSSHG3���ѹ�          ��ë//
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
fwrite($fpb, "���ʥ��롼�ץ����ɥޥ������ι���\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/product_code_master_get_ftp.php \n");

//////////////////////////// ʸ�����������FTP��³�ϻ��Ѥ��ʤ�
// FTP�Υ������åȥե�����
//define('MSHGNM', 'UKWLIB/W#MSHGNM');        // ���ʥ��롼�ץ����ɥޥ������ե�����
//define('W_MSHGNM', '/home/www/html/tnk-web/system/backup/W#MSHGNM.TXT');  // ���ʥ��롼�ץ����ɥޥ�������Download�ե�����

// ���ͥ���������(FTP��³�Υ����ץ�)
//if ($ftp_stream = ftp_connect(AS400_HOST)) {
//    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
//        /*** ���ʥ��롼�ץ����ɥޥ������ǡ��� ***/
//        if (ftp_get($ftp_stream, W_MSHGNM, MSHGNM, FTP_ASCII)) {
//            echo 'ftp_get download OK ', MSHGNM, '��', W_MSHGNM, "\n";
//            fwrite($fpa,"$log_date ftp_get download OK " . MSHGNM . '��' . W_MSHGNM . "\n");
//            fwrite($fpb,"$log_date ftp_get download OK " . MSHGNM . '��' . W_MSHGNM . "\n");
//        } else {
//            echo 'ftp_get() error ', MSHGNM, "\n";
//            fwrite($fpa,"$log_date ftp_get() error " . MSHGNM . "\n");
//            fwrite($fpb,"$log_date ftp_get() error " . MSHGNM . "\n");
//        }
//    } else {
//        echo "ftp_login() error \n";
//        fwrite($fpa,"$log_date ftp_login() error \n");
//        fwrite($fpb,"$log_date ftp_login() error \n");
//    }
//    ftp_close($ftp_stream);
//} else {
//    echo "ftp_connect() error --> ���ʥ��롼�ץ����ɥޥ�����\n";
//    fwrite($fpa,"$log_date ftp_connect() error --> ���ʥ��롼�ץ����ɥޥ�����\n");
//    fwrite($fpb,"$log_date ftp_connect() error --> ���ʥ��롼�ץ����ɥޥ�����\n");
//}

/////// ��������� �ѿ� �����
$flag1 = '';        // �����¹ԥե饰 ���
$flag2 = '';        // �����¹ԥե饰 �����ƥ�
$flag3 = '';        // �����¹ԥե饰 ���ʻų�
$flag4 = '';        // �����¹ԥե饰 ϫ̳�񡦷���
$b     = 0;         // �ƥ����ȥե�����Υ쥳���ɿ�
$c     = 0;
$d     = 0;
$e     = 0;

// ���ʥ��롼�ץ����� ������� �������
// $file_name = '/home/www/html/weekly/Q#MIITEM.CSV';
$file_name  = '/home/guest/daily/W#MSHGNM.CSV';
$file_temp  = '/home/guest/daily/Q#MSHGNM.tmp';
$file_write = '/home/guest/daily/Q#MSHGNM.txt';

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
        $data[1] = str_replace('"', '', $data[1]);  // �ʤ�����"��������֤������Τȡ�ޤǽ���ޤ��ΤǺ������
                                                    // �嵭�ϲ���pg_escape_string()����������Ǥ���
        $data[1] = pg_escape_string($data[1]);      // ��̾
        ///// data[0]�����ֹ��data[4]��Ͽ���϶�̳�Υ롼��奨�������פ���ɬ�פ�̵��
        fwrite($fpw,"{$data[0]}\t{$data[1]}\n");
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

// ���ʥ��롼�ץ����ɥޥ����� �������
$file_name = '/home/guest/daily/Q#MSHGNM.txt';
$file_name_bak = '/home/guest/daily/Q#MSHGNM-bak.txt';
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
            $data[1] = addslashes($data[1]);    // "'"�����ǡ����ˤ������\�ǥ��������פ���
            $data[1] = trim($data[1]);          // ����̾������Υ��ڡ������� AS/400��PCIX����Ѥ���FTPž���Τ���
            ///////// ��Ͽ�ѤߤΥ����å�
            $query_chk = sprintf("select mhgcd from msshg3 where mhgcd='%s'", $data[0]);
            if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                ///// ��Ͽ�ʤ� insert ����
                $query = sprintf("insert into msshg3 (mhgcd, mhgnm)
                        values('%s','%s')", $data[0],$data[1]);
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
                $query = sprintf("update msshg3 set mhgcd='%s', mhgnm='%s'
                        where mhgcd='%s'", $data[0], $data[1], $data[0]);
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
        echo "Q#MSHGNM.txt�򥪡��ץ����ޤ���\n";
        fwrite($fpa,"".$rowcsv."W#MSHGNM.txt�򥪡��ץ����ޤ���\n");
        fwrite($fpb,"".$rowcsv."W#MSHGNM.txt�򥪡��ץ����ޤ���\n");
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
    echo "$log_date ���ʥ��롼�ץ����ɥޥ������ι���: $rec_ok/$rowcsv ����Ͽ���ޤ�����\n";
    echo "$log_date ���ʥ��롼�ץ����ɥޥ������ι���: {$row_in}/{$rowcsv} �� �ɲ� \n";
    echo "$log_date ���ʥ��롼�ץ����ɥޥ������ι���: {$row_up}/{$rowcsv} �� �ѹ� \n";
    fwrite($fpa, "$log_date ���ʥ��롼�ץ����ɥޥ������ι���: $rec_ok/$rowcsv ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date ���ʥ��롼�ץ����ɥޥ������ι���: {$row_in}/{$rowcsv} �� �ɲ� \n");
    fwrite($fpa, "$log_date ���ʥ��롼�ץ����ɥޥ������ι���: {$row_up}/{$rowcsv} �� �ѹ� \n");
    fwrite($fpb, "$log_date ���ʥ��롼�ץ����ɥޥ������ι���: $rec_ok/$rowcsv ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date ���ʥ��롼�ץ����ɥޥ������ι���: {$row_in}/{$rowcsv} �� �ɲ� \n");
    fwrite($fpb, "$log_date ���ʥ��롼�ץ����ɥޥ������ι���: {$row_up}/{$rowcsv} �� �ѹ� \n");
} else {
    echo "{$log_date} ���ʥ��롼�ץ����ɥޥ������ι����ǡ���������ޤ���\n";
    fwrite($fpa, "$log_date ���ʥ��롼�ץ����ɥޥ������ι����ǡ���������ޤ���\n");
    fwrite($fpb, "$log_date ���ʥ��롼�ץ����ɥޥ������ι����ǡ���������ޤ���\n");
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');    // ������Ͽ�Ѥˤϥ����ȥ�����
fclose($fpa);      ////// �����ѥ�����߽�λ
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߽�λ

?>
