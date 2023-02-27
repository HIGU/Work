#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-4.3.9-cli -c php4.ini                               //
// #!/usr/local/bin/php-5.0.4-cli --- 5.1.6-cli �ޤǤ�Ⱦ�ѥ��ʤ�NG          //
// �����ƥ�ޥ���������(daily)����  (system_daily.php����ʬΥ)              //
// Copyright (C) 2004-2009 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history  (system_daily.php�ε������Ĥ�)                        //
// 2003/06/04 miitem �� last_date last_user ʬ�� \t \N �ˤ��ƽ�����ɲ�     //
// 2003/06/20 miitem �� psql ���� insert update Transaction �������ѹ�      //
// 2003/11/28 miitem ��$str_flg��\r���л��ꥻ�åȤ������ιԤ��Զ���к�     //
//            miitem �ν���߼��Ի��� break �����ͤ��ѹ������̥��顼�б�    //
//            miitem ��2002/02/ �����Υǡ��������ƥ���С��Ȥ��ʤ�������    //
// 2003/12/22 miitem ������̾��Ⱦ�ѥ��ʤΤޤ޻��Ѥ���褦���ѹ� ���        //
// 2004/01/13 ML�� fgetcsv()�λ��ͤ��Ѥ�ä������Τ�miitem�ˤ�嵭��Ŧ��    //
//                                                                          //
// 2004/10/15 Created   daily_miitem_cli.php                                //
// 2004/10/15 AS/400����PCIX����Ѥ���FTPž�������ؤ������� trim()�����    //
// 2004/12/13 daily_cli.php����require()�ǸƽФ��Ƥ����Τ�``���ѹ�          //
//                  Ⱦ�ѥ��ʤ������ʤ��Զ���к� �Х�� ASSY �� ASSY      //
// 2004/12/13 #!/usr/local/bin/php-5.0.2-cli �� php (������5.0.3RC2)���ѹ�  //
// 2004/12/17 �嵭�Υ���ʸ������� php �� php-4.3.9-cli���ѹ�               //
// 2005/03/29 �Хå����åץǡ������������褦���ѹ� file_name_bak          //
// 2005/04/20 ����ʸ�����꤬php-5.0.4�ǲ�ä��줿php-4.3.9��php-5.0.4���ѹ� //
// 2005/05/18 ���ν񼰤��ѹ������٤ˤ���������ɲ�)                     //
// 2005/05/31 ����ʸ�����꤬SAPI�⥸�塼��(apache)�ǤϽ������줿��CLI�Ǥ�NG //
//            php �� php-4.3.9-cli �غ����ѹ� (â����cron�Ǽ¹Ի��Τ�NG)    //
//            ľ�ܥ��ޥ�ɥ饤��Ǽ¹Ԥ������ OK (�ʤ���)                //
// 2006/08/29 simplate.so ��DSO module �Ǽ��������� php4�� -c php4.ini�ɲ�//
// 2006/09/04 ʸ�������θ�����fgetcsv()��LANG�Ķ��ѿ�������Ǥ������ʬ���� //
//            cron������ե�����(as400get_ftp)��LANG=ja_JP,eucJP���ɲä��б�//
// 2007/01/22 AS¦����FTPž�����줿CSV�ν������ư����fgetcsv()���ѹ�       //
//            ͽ�����ʤ�2"(�����)�ǻ��Ѥ���Ƥ��ޤä�����fgetcsv�ˤޤ����� //
// 2009/12/18 ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤ��ɲ�           ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��cli��)
    // ���ߤ�CLI�Ǥ�default='1', SAPI�Ǥ�default='0'�ˤʤäƤ��롣CLI�ǤΤߥ�����ץȤ����ѹ�����롣
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ
require_once ('/home/www/html/tnk-web/function.php');
/////// ��������� �ѿ� �����
$log_date = date('Y-m-d H:i:s');        ///// �����ѥ�������
$msg   = '';        // ��å�����
$flag1 = '';        // �����¹ԥե饰 ���
$flag2 = '';        // �����¹ԥե饰 �����ƥ�
$flag3 = '';        // �����¹ԥե饰 ���ʻų�
$flag4 = '';        // �����¹ԥե饰 ϫ̳�񡦷���
$b     = 0;         // �ƥ����ȥե�����Υ쥳���ɿ�
$c     = 0;
$d     = 0;
$e     = 0;

// �����ƥ�ޥ����� ������� �������
// $file_name = '/home/www/html/weekly/Q#MIITEM.CSV';
$file_name  = '/home/guest/daily/Q#MIITEM.CSV';
$file_temp  = '/home/guest/daily/Q#MIITEM.tmp';
$file_write = '/home/guest/daily/Q#MIITEM.txt';
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
        $data[2] = pg_escape_string($data[2]);      // ���
        $data[3] = pg_escape_string($data[3]);      // �Ƶ���
        ///// data[0]�����ֹ��data[4]��Ͽ���϶�̳�Υ롼��奨�������פ���ɬ�פ�̵��
        fwrite($fpw,"{$data[0]}\t{$data[1]}\t{$data[2]}\t{$data[3]}\t{$data[4]}\n");
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

// �����ƥ�ޥ����� �������
$file_name = '/home/guest/daily/Q#MIITEM.txt';
$file_name_bak = '/home/guest/daily/backup/Q#MIITEM-bak.txt';
if (file_exists($file_name)) {            // �ե������¸�ߥ����å�
    $rowcsv = 0;        // read file record number
    $row_in = 0;        // insert record number �����������˥�����ȥ��å�
    $row_up = 0;        // update record number   ��
    $miitem_ng_flg = FALSE;      // �ģ½���ߣΣǥե饰
    if ( ($fp = fopen($file_name, 'r')) ) {
        /////////// begin �ȥ�󥶥�����󳫻�
        if ( !($con = db_connect()) ) {
            $msg .= "�ǡ����١�������³�Ǥ��ޤ���\n";
        } else {
            query_affected_trans($con, 'begin');
            while ($data = fgetcsv($fp, 200, "\t")) {
                // $num = count($data);     // CSV File �� field ��
                $rowcsv++;
                $data[1] = addslashes($data[1]);    // "'"�����ǡ����ˤ������\�ǥ��������פ���
                $data[1] = trim($data[1]);          // ����̾������Υ��ڡ������� AS/400��PCIX����Ѥ���FTPž���Τ���
                $data[2] = trim($data[2]);          // ���̾������Υ��ڡ�������
                $data[3] = trim($data[3]);          // �Ƶ��������Υ��ڡ�������
                ///////// ��Ͽ�ѤߤΥ����å�
                $query_chk = sprintf("select mipn from miitem where mipn='%s'", $data[0]);
                if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                    ///// ��Ͽ�ʤ� insert ����
                    $query = sprintf("insert into miitem (mipn, midsc, mzist, mepnt, madat)
                            values('%s','%s','%s','%s',%d)", $data[0],$data[1],$data[2],$data[3],$data[4]);
                    if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                        $msg .= "miitem insert error rec No.=$rowcsv \n";
                        $miitem_ng_flg = TRUE;
                        break;          // NG �Τ���ȴ����
                    } else {
                        $row_in++;      // insert ����
                    }
                } else {
                    ///// ��Ͽ���� update ����
                    $query = sprintf("update miitem set mipn='%s', midsc='%s', mzist='%s', mepnt='%s', madat=%d
                            where mipn='%s'", $data[0], $data[1], $data[2], $data[3], $data[4], $data[0]);
                    if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                        $msg .= "miitem update error rec No.=$rowcsv \n";
                        $miitem_ng_flg = TRUE;
                        break;          // NG �Τ���ȴ����
                    } else {
                        $row_up++;      // update ����
                    }
                }
            }
        }
        /////////// commit �ȥ�󥶥������λ
        if ($miitem_ng_flg) {
            query_affected_trans($con, 'rollback');     // transaction rollback
        } else {
            query_affected_trans($con, 'commit');       // ����ߴ�λ
        }
    } else {
        $msg .= "Q#MIITEM.txt�򥪡��ץ����ޤ���\n";
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
    }
    $flag2 = 1;
}


// ��å��������֤�
if ($flag2==1) {
    $msg .= "{$log_date} �����ƥ�ޥ���������\n";
    $msg .= "{$log_date} insert $row_in ��\n";
    $msg .= "{$log_date} update $row_up ��\n";
    $msg .= "{$log_date} CSV_file $rowcsv ��\n";
    $msg .= "{$log_date} Original $c ��\n";
} else {
    $msg .= "{$log_date}:�����ƥ�ޥ������ι����ǡ���������ޤ���\n";
}
$fpa = fopen('/tmp/nippo.log', 'a');    ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤǥ����ץ�
fwrite($fpb, "����(daily)����\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/daily_cli.php\n");
fwrite($fpb, "------------------------------------------------------------------------\n");
fwrite($fpb, "�����ƥ�ޥ������ι���\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/daily_miitem_cli.php\n");

fwrite($fpa, $msg);
fwrite($fpb, $msg);
echo "$msg";
fclose($fpa);      ////// �����ѥ�����߽�λ
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߽�λ
exit();
?>
