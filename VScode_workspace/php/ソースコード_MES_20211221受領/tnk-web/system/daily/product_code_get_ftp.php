#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ���ʥ��롼�ץ����� ��ưFTP Download cron �ǽ�����       ���ޥ�ɥ饤���� //
// AS/400 ----> Web Server (PHP)                                            //
// AS UKSLIB/QCLSRC \TNKDAILYC��LOOP�����˰ʲ�����Ͽ���뤳��                //
// SNDF       RCDFMT(TITLE)                                                 //
// SNDF       RCDFMT(MSHMAS)                                                //
// RUNQRY     QRY(UKPLIB/Q#MSHMAS)                                          //
// Copyright (C) 2009-2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed histoy                                                           //
// 2009/11/19 Created  product_code_get_ftp.php                             //
// 2009/12/25 FTP��ľ�ܥǡ������������褦���ѹ�                           //
// 2009/12/28 �����Ȥ�AS�ؤ��Ȥ߹��ߤ��ɲ�(AS�ؤ��ȹ��ߤ�̤�»�)          //
// 2010/01/19 �᡼���ʬ����䤹������١����ա�����������ɲ�            //
// 2010/01/20 $log_date�������'�ǤϤʤ�"�ʤΤǽ���                         //
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
fwrite($fpb, "���ʥ��롼�ץ����ɤι���\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/product_code_get_ftp.php \n");

// FTP�Υ������åȥե�����
define('MSHMAS', 'UKWLIB/W#MSHMAS');        // ���ʥ��롼�ץ����ɥե�����
define('W_MSHMAS', '/home/www/html/tnk-web/system/backup/W#MSHMAS.TXT');  // ���ʥ��롼�ץ����ɤ�Download�ե�����

// ���ͥ���������(FTP��³�Υ����ץ�)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        /*** ���ʥ��롼�ץ����ɥǡ��� ***/
        if (ftp_get($ftp_stream, W_MSHMAS, MSHMAS, FTP_ASCII)) {
            echo "$log_date ���ʥ��롼�ץ����� ftp_get download OK ", MSHMAS, "��", W_MSHMAS, "\n";
            fwrite($fpa,"$log_date ���ʥ��롼�ץ����� ftp_get download OK " . MSHMAS . '��' . W_MSHMAS . "\n");
            fwrite($fpb,"$log_date ���ʥ��롼�ץ����� ftp_get download OK " . MSHMAS . '��' . W_MSHMAS . "\n");
        } else {
            echo "$log_date ���ʥ��롼�ץ����� ftp_get() error ", MSHMAS, "\n";
            fwrite($fpa,"$log_date ���ʥ��롼�ץ����� ftp_get() error " . MSHMAS . "\n");
            fwrite($fpb,"$log_date ���ʥ��롼�ץ����� ftp_get() error " . MSHMAS . "\n");
        }
    } else {
        echo "$log_date ���ʥ��롼�ץ����� ftp_login() error \n";
        fwrite($fpa,"$log_date ���ʥ��롼�ץ����� ftp_login() error \n");
        fwrite($fpb,"$log_date ���ʥ��롼�ץ����� ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    echo "$log_date ftp_connect() error --> ���ʥ��롼�ץ�����\n";
    fwrite($fpa,"$log_date ftp_connect() error --> ���ʥ��롼�ץ�����\n");
    fwrite($fpb,"$log_date ftp_connect() error --> ���ʥ��롼�ץ�����\n");
}

/////// ��������� �ѿ� �����\
$flag1 = '';        // �����¹ԥե饰 ���
$flag2 = '';        // �����¹ԥե饰 �����ƥ�
$flag3 = '';        // �����¹ԥե饰 ���ʻų�
$flag4 = '';        // �����¹ԥե饰 ϫ̳�񡦷���
$b     = 0;         // �ƥ����ȥե�����Υ쥳���ɿ�
$c     = 0;
$d     = 0;
$e     = 0;

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');    // ������Ͽ�Ѥˤϥ����ȥ�����
} else {
    fwrite($fpa, "$log_date ���ʥ��롼�ץ����� db_connect() error \n");
    fwrite($fpb, "$log_date ���ʥ��롼�ץ����� db_connect() error \n");
    echo "$log_date ���ʥ��롼�ץ����� db_connect() error \n";
    exit();
}

// ���ʥ��롼�ץ����� �������
$file_name = '/home/www/html/tnk-web/system/backup/W#MSHMAS.TXT';
$file_name_bak = '/home/www/html/tnk-web/system/backup/W#MSHMAS-bak.txt';
if (file_exists($file_name)) {            // �ե������¸�ߥ����å�
    $rowcsv = 0;        // read file record number
    $row_in = 0;        // insert record number �����������˥�����ȥ��å�
    $row_up = 0;        // update record number   ��
    $rec_ok = 0;        // �������������
    $mshmas_ng_flg = FALSE;      // �ģ½���ߣΣǥե饰
    if ( ($fp = fopen($file_name, 'r')) ) {
        while ($data = fgetcsv($fp, 200, "_")) {
            if ($data[0] == '') continue;   // ���Ԥν���
            // $num = count($data);     // CSV File �� field ��
            $rowcsv++;
            $data[1] = addslashes($data[1]);    // "'"�����ǡ����ˤ������\�ǥ��������פ���
            $data[1] = trim($data[1]);          // ����̾������Υ��ڡ������� AS/400��PCIX����Ѥ���FTPž���Τ���
            ///////// ��Ͽ�ѤߤΥ����å�
            $query_chk = sprintf("select mipn from mshmas where mipn='%s'", $data[0]);
            if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                ///// ��Ͽ�ʤ� insert ����
                $query = sprintf("insert into mshmas (mipn, mhscd, mhjcd, mhshc)
                        values('%s','%s','%s','%s')", $data[0],$data[1],$data[2],$data[3]);
                if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                    echo "$log_date {$rowcsv}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n";
                    fwrite($fpa, "$log_date {$rowcsv}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                    fwrite($fpb, "$log_date {$rowcsv}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                    $mshmas_ng_flg = TRUE;
                    break;          // NG �Τ���ȴ����
                } else {
                    $row_in++;      // insert ����
                    $rec_ok++;      // �������������
                }
            } else {
                ///// ��Ͽ���� update ����
                $query = sprintf("update mshmas set mipn='%s', mhscd='%s', mhjcd='%s', mhshc='%s'
                        where mipn='%s'", $data[0], $data[1], $data[2], $data[3], $data[0]);
                if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                    echo "$log_date {$rowcsv}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n";
                    fwrite($fpa, "$log_date {$rowcsv}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                    fwrite($fpb, "$log_date {$rowcsv}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                    $mshmas_ng_flg = TRUE;
                    break;          // NG �Τ���ȴ����
                } else {
                    $row_up++;      // update ����
                    $rec_ok++;      // �������������
                }
            }
        }
    } else {
        echo "W#MSHMAS.txt�򥪡��ץ����ޤ���\n";
        fwrite($fpa,"".$rowcsv."W#MSHMAS.txt�򥪡��ץ����ޤ���\n");
        fwrite($fpb,"".$rowcsv."W#MSHMAS.txt�򥪡��ץ����ޤ���\n");
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
    echo "$log_date ���ʥ��롼�ץ����ɤι���: $rec_ok/$rowcsv ����Ͽ���ޤ�����\n";
    echo "$log_date ���ʥ��롼�ץ����ɤι���: {$row_in}/{$rowcsv} �� �ɲ� \n";
    echo "$log_date ���ʥ��롼�ץ����ɤι���: {$row_up}/{$rowcsv} �� �ѹ� \n";
    fwrite($fpa, "$log_date ���ʥ��롼�ץ����ɤι���: $rec_ok/$rowcsv ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date ���ʥ��롼�ץ����ɤι���: {$row_in}/{$rowcsv} �� �ɲ� \n");
    fwrite($fpa, "$log_date ���ʥ��롼�ץ����ɤι���: {$row_up}/{$rowcsv} �� �ѹ� \n");
    fwrite($fpb, "$log_date ���ʥ��롼�ץ����ɤι���: $rec_ok/$rowcsv ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date ���ʥ��롼�ץ����ɤι���: {$row_in}/{$rowcsv} �� �ɲ� \n");
    fwrite($fpb, "$log_date ���ʥ��롼�ץ����ɤι���: {$row_up}/{$rowcsv} �� �ѹ� \n");
} else {
    echo "{$log_date} ���ʥ��롼�ץ����ɤι����ǡ���������ޤ���\n";
    fwrite($fpa, "$log_date ���ʥ��롼�ץ����ɤι����ǡ���������ޤ���\n");
    fwrite($fpb, "$log_date ���ʥ��롼�ץ����ɤι����ǡ���������ޤ���\n");
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');    // ������Ͽ�Ѥˤϥ����ȥ�����
fclose($fpa);      ////// �����ѥ�����߽�λ
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߽�λ

?>
