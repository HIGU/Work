<?php
//////////////////////////////////////////////////////////////////////////////
// ����(daily weekly)���衡��  (�ºݤˤ�daily�ǽ������Ƥ���)                //
// Copyright (C) 2002-2010      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2002/02/22 Created  system_daily.php                                     //
// 2002/08/08 ���å����������ѹ�                                          //
// 2002/11/11 stderr(2)��stdout(1) 2>&1 �� �ɲ�                             //
// 2002/12/03 �����ȥ�˥塼���ɲäΤ��� site_index �� site_id �ɲ�         //
// 2003/06/04 miitem �� last_date last_user ʬ�� \t \N �ˤ��ƽ�����ɲ�     //
// 2003/06/20 miitem �� psql ���� insert update Transaction �������ѹ�      //
// 2003/11/28 miitem ��$str_flg��\r���л��ꥻ�åȤ������ιԤ��Զ���к�     //
//            miitem �ν���߼��Ի��� break �����ͤ��ѹ������̥��顼�б�    //
//            miitem ��2002/02/ �����Υǡ��������ƥ���С��Ȥ��ʤ�������    //
// 2003/12/22 miitem ������̾��Ⱦ�ѥ��ʤΤޤ޻��Ѥ���褦���ѹ� ���        //
// 2004/01/08 ϫ̳�񡦷��񥵥ޥ꡼�����ʻųݤ� \copy�λ��ͤ��Ѥ�� (V7.4)   //
//            ERROR:end-of-copy marker does not match previous newline style//
//            ���ΰ� CRLF �� LF �Τߤ˥ե��������������褦�˥��å��ѹ�  //
// 2004/01/13 ML�� fgetcsv()�λ��ͤ��Ѥ�ä������Τ�miitem�ˤ�嵭��Ŧ��    //
// 2004/10/15 AS/400����PCIX����Ѥ���FTPž�������ؤ������� trim()�����    //
// 2005/03/04 dir�ѹ� /home/www/html/monthly/ �� /home/guest/monthly/       //
// 2005/10/06 ���ʻųݥ��ޥ꡼��ϫ̳�񡦷��񥵥ޥ꡼��ʬΥ����DBServer���б�//
// 2007/10/05 ϫ̳�񡦷��񥵥ޥ꡼�������˵�ǡ����Υ����å��򤷺�����ɲ�  //
// 2007/10/16 �嵭�ν�����̤Υ�å������� \n �� \\n �ؽ���                 //
// 2010/05/19 �δ���μ����ߤ��ɲ�                                   ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ini_set('max_execution_time', 180);        // ����¹Ի���=3ʬ
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
ob_start();  //Warning: Cannot add header ���к��Τ����ɲá�
require_once ('../function.php');
require_once ('../tnk_func.php');
access_log();                               // Script Name �ϼ�ư����

$_SESSION['site_index'] = 99;               // �Ǹ�Υ�˥塼�ˤ��뤿�� 99 �����
$_SESSION['site_id'] = 10;                  // ���̥�˥塼̵�� (0 < �Ǥ���)

if ($_SESSION['Auth'] <= 2) {
    $_SESSION['s_sysmsg'] = '�����ƥ������˥塼�ϴ����ԤΤ߻��ѤǤ��ޤ���';
    header('Location: http:' . WEB_HOST);
    exit();
}
/////// ��������� �ѿ� �����
$msg   = "";        // ��å�����
$flag1 = "";        // �����¹ԥե饰 ���
$flag2 = "";        // �����¹ԥե饰 �����ƥ�
$flag3 = "";        // �����¹ԥե饰 ���ʻų�
$flag4 = "";        // �����¹ԥե饰 ϫ̳�񡦷���
$flag5 = "";        // �����¹ԥե饰 �δ���
$b     = 0;         // �ƥ����ȥե�����Υ쥳���ɿ�
$c     = 0;
$d     = 0;
$e     = 0;
$f     = 0;

// ��� ������� �������       // ���ߤ� FTP ž�������ؤ���������Ѥ��Ƥ��ʤ�
$file_name = "/home/www/html/daily/HIUURI.CSV";
$file_write = "/home/www/html/daily/HIUURI.txt";
if (file_exists($file_name)) {            // �ե������¸�ߥ����å�
    $fp = fopen($file_name,"r");
    $fpw = fopen($file_write,"a");
    while (FALSE!==($data = fgetc($fp)) ) {           // ��ʸ�� �ɹ�
        $data = mb_convert_encoding($data, "EUC-JP", "SJIS");       // SJIS��EUC-JP���Ѵ�
        switch ($data) {
        case '"':
            break;
        case ',':
            fwrite($fpw,"\t");
            break;
        case "\r":
            $b++;
            fwrite($fpw,"\t\\N\t\\N\r");      // last_date last_user ʬ�� \t \N �ˤ��ƽ����
            break;
        default:
            fwrite($fpw,$data);
            break;
        }
    }
    fclose($fp);
    fclose($fpw);
    unlink($file_name);     // ����ե�������� CSV
}

// ��� �������       // ���ߤ� FTP ž�������ؤ���������Ѥ��Ƥ��ʤ�
$file_name = "/home/www/html/daily/HIUURI.txt";
if (file_exists($file_name)) {            // �ե������¸�ߥ����å�
    ///////////////////////////////////////////////// stderr(2)����stdout(1)�� 2>&1
    $result1 = `/usr/local/pgsql/bin/psql TnkSQL < /home/www/html/daily/hiuuri 2>&1`;
    unlink($file_name);     // ����ե�������� txt
    $flag1 = 1;
}



// �����ƥ�ޥ����� ��ñ�̽��� �������
// $file_name = "/home/www/html/weekly/Q#MIITEM.CSV";
$file_name  = "/home/guest/daily/Q#MIITEM.CSV";
$file_temp  = "/home/guest/daily/Q#MIITEM.tmp";
$file_write = "/home/guest/daily/Q#MIITEM.txt";
if (file_exists($file_name)) {            // �ե������¸�ߥ����å�
    $fp = fopen($file_name,"r");
    $fpw = fopen($file_temp,"a");
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
    $fp = fopen($file_temp,"r");
    $fpw = fopen($file_write,"a");
    $str_flg = 0;       // ʸ����ե�������⤫�ɤ����Υե饰
    while (FALSE!==($data = fgetc($fp)) ) {           // ��ʸ�� �ɹ�
        switch ($data) {
        case '"':
            if ($str_flg == 0) {
                $str_flg = 1;       // ʸ������ե�����ɤ˥��å�
            } else {
                $str_flg = 0;       // ʸ���󳰥ե�����ɤ˥��å�
            }
            break;
        case ',':
            if ($str_flg == 0)           // ʸ���󳰤� ',' ����ޤʤ饿�֤��ѹ�
                fwrite($fpw,"\t");
            else
                fwrite($fpw,$data); // ʸ������� ',' ����ޤʤ餽�Τޤ޽񤭹���
            break;
        case "\n":
            fwrite($fpw,"\t\\N\t\\N\n");      // last_date last_user ʬ�� \t \N �ˤ��ƽ����
            $str_flg = 0;   // CR �򸡽Ф�����ʸ����ե饰��ꥻ�å�(������"���к�)
            break;
        case "\r":
            break;                          // CR ���ɤ����Ф�
        default:
            fwrite($fpw,$data);
            break;
        }
    }
    fclose($fp);
    fclose($fpw);
    unlink($file_name);     // ����ե�������� CSV
    unlink($file_temp);     // ����ե�������� tmp
}

// �����ƥ�ޥ����� ��ñ�̽���
$file_name = "/home/guest/daily/Q#MIITEM.txt";
if (file_exists($file_name)) {            // �ե������¸�ߥ����å�
    $rowcsv = 0;        // read file record number
    $row_in = 0;        // insert record number �����������˥�����ȥ��å�
    $row_up = 0;        // update record number   ��
    $miitem_ng_flg = FALSE;      // �ģ½���ߣΣǥե饰
    if ( ($fp = fopen($file_name, 'r')) ) {
        /////////// begin �ȥ�󥶥�����󳫻�
        if ( !($con = db_connect()) ) {
            $msg .= "�ǡ����١�������³�Ǥ��ޤ���<br>";
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
                        $msg .= "miitem insert error rec No.=$rowcsv <br>";
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
                        $msg .= "miitem update error rec No.=$rowcsv <br>";
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
            query_affected_trans($con, "rollback");     // transaction rollback
        } else {
            query_affected_trans($con, "commit");       // ����ߴ�λ
        }
    } else {
        $msg .= "Q#MIITEM.txt�򥪡��ץ����ޤ���<br>";
    }
    /**********
      ///////////////////////////////////////////////// stderr(2)����stdout(1)�� 2>&1
    $result2 = `/usr/local/pgsql/bin/psql TnkSQL < /home/www/html/weekly/qmiitem 2>&1`;
    unlink($file_name);
    ***********/
    fclose($fp);
    if ( !($miitem_ng_flg) ) {
        unlink($file_name);     // ����ե�������� txt
    }
    $flag2 = 1;
}



// ���ʻų�(�����ºݤȴ���ɸ��)���ޥ꡼�ե����� �ºݶ�ۤ�Ĥ��ि��Υե�����Ȥ��ƻ��� �������
$file_name = "/home/guest/monthly/Q#SGKSIKP.CSV";
$file_temp = "/home/guest/monthly/Q#SGKSIKP.tmp";
$file_write = "/home/guest/monthly/Q#SGKSIKP.txt";
if (file_exists($file_name)) {            // �ե������¸�ߥ����å�
    $fp = fopen($file_name,"r");
    $fpw = fopen($file_temp,"a");
    while (1) {
        $data=fgets($fp,200);
        $data = mb_convert_encoding($data, "EUC-JP", "SJIS");       // SJIS��EUC-JP���Ѵ�
        //  Ⱦ�ѥ��ʥǡ����ʤ� $data_KV = mb_convert_kana($data);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        fwrite($fpw,$data);
        $d++;
        if (feof($fp)) {
            $d--;
            break;
        }
    }
    fclose($fp);
    fclose($fpw);
    $fp = fopen($file_temp,"r");
    $fpw = fopen($file_write,"a");
    while (FALSE!==($data = fgetc($fp)) ) {           // ��ʸ�� �ɹ�
        switch ($data) {
        case '"':
            break;
        case ',':
            fwrite($fpw,"\t");
            break;
        case "\n":
            fwrite($fpw,"\t\\N\t\\N\n");    // last_date last_user ʬ�� \t \N �ˤ��ƽ����
            break;
        case "\r":
            break;                          // CR ���ɤ����Ф�
        default:
            fwrite($fpw,$data);
            break;
        }
    }
    fclose($fp);
    fclose($fpw);
    unlink($file_name);     // ����ե�������� CSV
    unlink($file_temp);     // ����ե�������� tmp
}

// ���ʻų�(�����ºݤȴ���ɸ��)���ޥ꡼�ե�����  ��ñ�̽���
$file_name = "/home/guest/monthly/Q#SGKSIKP.txt";
if (file_exists($file_name)) {            // �ե������¸�ߥ����å�
    //////////////////////////////////////////////////// stderr(2)����stdout(1)�� 2>&1
    $result3 = `/usr/local/pgsql/bin/psql -h 10.1.3.247 TnkSQL < /home/guest/monthly/sgksikp 2>&1`;
    unlink($file_name);     // ����ե�������� txt
    $flag3 = 1;
}


// ϫ̳�񡦷��񥵥ޥ꡼�ե����� download �������
$file_name = "/home/guest/monthly/AAYLAWL2.CSV";
$file_temp = "/home/guest/monthly/AAYLAWL2.tmp";
$file_write = "/home/guest/monthly/aaylawl2.txt";
if (file_exists($file_name)) {            // �ե������¸�ߥ����å�
    $fp = fopen($file_name,"r");
    $fpw = fopen($file_temp,"a");
    while (1) {
        $data=fgets($fp,200);
        $data = mb_convert_encoding($data, "EUC-JP", "SJIS");       // SJIS��EUC-JP���Ѵ�
        //  Ⱦ�ѥ��ʥǡ����ʤ� $data_KV = mb_convert_kana($data);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        fwrite($fpw,$data);
        $e++;
        if (feof($fp)) {
            $e--;
            break;
        }
    }
    fclose($fp);
    fclose($fpw);
    $fp = fopen($file_temp,"r");
    $fpw = fopen($file_write,"a");
    while (FALSE!==($data = fgetc($fp)) ) {           // ��ʸ�� �ɹ�
        switch ($data) {
        case '"':
            break;
        case ',':
            fwrite($fpw,"\t");
            break;
        case "\n":
            fwrite($fpw,"\t\\N\t\\N\n");    // last_date last_user ʬ�� \t \N �ˤ��ƽ����
            break;
        case "\r":
            break;                          // CR ���ɤ����Ф�
        default:
            fwrite($fpw,$data);
            break;
        }
    }
    fclose($fp);
    fclose($fpw);
    unlink($file_name);     // ����ե�������� CSV
    unlink($file_temp);     // ����ե�������� tmp
}

// ϫ̳�񡦷��񥵥ޥ꡼�ե�����  ��ñ�̽��� �ܺ��
$file_name = "/home/guest/monthly/aaylawl2.txt";
if (file_exists($file_name)) {            // �ե������¸�ߥ����å�
    $fp = fopen($file_name, 'r');
    $chk_data = fgetcsv($fp, 300, "\t");
    $query = "SELECT * FROM act_summary WHERE act_yymm = {$chk_data[2]} LIMIT 1";
    if (getUniResult($query, $res) > 0) {
        $sql = "DELETE FROM act_summary WHERE act_yymm = {$chk_data[2]}";
        $del_cnt = query_affected($sql);
        $msg .= "��¤���񥵥ޥ꡼�� {$del_cnt} �� ������Ƽ¹�\\n";
    }
    //////////////////////////////////////////////////////////////////// stderr(2)����stdout(1)�� 2>&1
    $result4 = `/usr/local/pgsql/bin/psql -h 10.1.3.247 TnkSQL < /home/guest/monthly/act_summary 2>&1`;
    unlink($file_name);     // ����ե�������� txt
    $flag4 = 1;
}

// �δ��񥵥ޥ꡼�ե����� download �������
$file_name = "/home/guest/monthly/AAYECTL6.CSV";
$file_temp = "/home/guest/monthly/AAYECTL6.tmp";
$file_write = "/home/guest/monthly/AAYECTL6.txt";
if (file_exists($file_name)) {            // �ե������¸�ߥ����å�
    $fp = fopen($file_name,"r");
    $fpw = fopen($file_temp,"a");
    while (1) {
        $data=fgets($fp,300);
        $data = mb_convert_encoding($data, "EUC-JP", "SJIS");       // SJIS��EUC-JP���Ѵ�
        //  Ⱦ�ѥ��ʥǡ����ʤ� $data_KV = mb_convert_kana($data);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        fwrite($fpw,$data);
        $f++;
        if (feof($fp)) {
            $f--;
            break;
        }
    }
    fclose($fp);
    fclose($fpw);
    $fp = fopen($file_temp,"r");
    $fpw = fopen($file_write,"a");
    while (FALSE!==($data = fgetc($fp)) ) {           // ��ʸ�� �ɹ�
        switch ($data) {
        case '"':
            break;
        case ',':
            fwrite($fpw,"\t");
            break;
        case "\n":
            fwrite($fpw,"\t\\N\t\\N\n");    // last_date last_user ʬ�� \t \N �ˤ��ƽ����
            break;
        case "\r":
            break;                          // CR ���ɤ����Ф�
        default:
            fwrite($fpw,$data);
            break;
        }
    }
    fclose($fp);
    fclose($fpw);
    unlink($file_name);     // ����ե�������� CSV
    unlink($file_temp);     // ����ե�������� tmp
}
/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');    // ������Ͽ�Ѥˤϥ����ȥ�����
} else {
    exit();
}
// �δ��񥵥ޥ꡼�ե�����  ��ñ�̽��� �ܺ��
$file_name = "/home/guest/monthly/AAYECTL6.txt";
if (file_exists($file_name)) {            // �ե������¸�ߥ����å�
    $fp = fopen($file_name, 'r');
    $chk_data = fgetcsv($fp, 300, "\t");
    $query = "SELECT * FROM act_sga_summary WHERE act_yymm = {$chk_data[1]} LIMIT 1";
    if (getUniResult($query, $res) > 0) {
        $sql = "DELETE FROM act_sga_summary WHERE act_yymm = {$chk_data[1]}";
        $del_cnt = query_affected_trans($con,$sql);
        $msg .= "�δ��񥵥ޥ꡼�� {$del_cnt} �� ������Ƽ¹�\\n";
    }
    //////////////////////////////////////////////////////////////////// stderr(2)����stdout(1)�� 2>&1
    //$result4 = `/usr/local/pgsql/bin/psql -h 10.1.3.247 TnkSQL < /home/guest/monthly/act_summary 2>&1`;
    if ( ($fp = fopen($file_name, 'r')) ) {
    $row_han = 0;       // �δ���ο�
    $row_sei = 0;       // ��¤����ο�
    $rec_ok = 0;        // �������������
    $rec_ng = 0;        // ���Կ��������
        while ($data = fgetcsv($fp, 300, "\t")) {
        // while ($data = fgetcsv($fp, 200, "_")) {     // FTP��³��
            ///////// ��Ͽ�ѤߤΥ����å�
            $query_chk = sprintf("select * from act_summary where act_ki=%d and act_yymm=%d and act_id=%d", $data[0], $data[1], $data[2]);
            if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                ///// ��Ͽ�ʤ� insert ����
                switch ($data[17]) {
                case 4:
                    $ser_month = 1;
                    $act_month = $data[3];
                    $act_sum   = $data[3];
                    $act_get   = $data[3];
                    break;
                case 5:
                    $ser_month = 2;
                    $act_month = $data[4];
                    $act_sum   = $data[3] + $data[4];
                    $act_get   = $data[4];
                    break;
                case 6:
                    $ser_month = 3;
                    $act_month = $data[5];
                    $act_sum   = $data[3] + $data[4] + $data[5];
                    $act_get   = $data[5];
                    break;
                case 7:
                    $ser_month = 4;
                    $act_month = $data[6];
                    $act_sum   = $data[3] + $data[4] + $data[5] + $data[6];
                    $act_get   = $data[6];
                    break;
                case 8:
                    $ser_month = 5;
                    $act_month = $data[7];
                    $act_sum   = $data[3] + $data[4] + $data[5] + $data[6] + $data[7];
                    $act_get   = $data[7];
                    break;
                case 9:
                    $ser_month = 6;
                    $act_month = $data[8];
                    $act_sum   = $data[3] + $data[4] + $data[5] + $data[6] + $data[7] + $data[8];
                    $act_get   = $data[8];
                    break;
                case 10:
                    $ser_month = 7;
                    $act_month = $data[9];
                    $act_sum   = $data[3] + $data[4] + $data[5] + $data[6] + $data[7] + $data[8] + $data[9];
                    $act_get   = $data[9];
                    break;
                case 11:
                    $ser_month = 8;
                    $act_month = $data[10];
                    $act_sum   = $data[3] + $data[4] + $data[5] + $data[6] + $data[7] + $data[8] + $data[9] + $data[10];
                    $act_get   = $data[10];
                    break;
                case 12:
                    $ser_month = 9;
                    $act_month = $data[11];
                    $act_sum   = $data[3] + $data[4] + $data[5] + $data[6] + $data[7] + $data[8] + $data[9] + $data[10] + $data[11];
                    $act_get   = $data[11];
                    break;
                case 1:
                    $ser_month = 10;
                    $act_month = $data[12];
                    $act_sum   = $data[3] + $data[4] + $data[5] + $data[6] + $data[7] + $data[8] + $data[9] + $data[10] + $data[11] + $data[12];
                    $act_get   = $data[12];
                    break;
                case 2:
                    $ser_month = 11;
                    $act_month = $data[13];
                    $act_sum   = $data[3] + $data[4] + $data[5] + $data[6] + $data[7] + $data[8] + $data[9] + $data[10] + $data[11] + $data[12] + $data[13];
                    $act_get   = $data[13];
                    break;
                case 3:
                    $ser_month = 12;
                    $act_month = $data[14];
                    $act_sum   = $data[3] + $data[4] + $data[5] + $data[6] + $data[7] + $data[8] + $data[9] + $data[10] + $data[11] + $data[12] + $data[13] + $data[14];
                    $act_get   = $data[14];
                    break;
                default:    // ����¾
                    $ser_month = 0;
                    $act_month = 0;
                    $act_sum   = 0;
                    $act_get   = 0;
                }
                $row_han++;     // ���δ���ο�
                $query_chk = sprintf("select * from act_sga_summary where act_ki=%d and act_yymm=%d and act_id=%d and actcod=%d and aucod=%d", $data[0], $data[1], $data[2], $data[15], $data[16]);
                if (getResultTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                    $query = sprintf("insert into act_sga_summary (act_ki, act_ser, act_yymm, act_id, act_monthly, act_sum, act_getu, actcod, aucod)
                            values(%d,%d,%d,%d,%d,%d,%d,%d,%d)", $data[0],$ser_month,$data[1],$data[2],$act_month,$act_sum,$act_get,$data[15],$data[16]);
                    if (($act_sum != 0) || ($act_month != 0)) {
                        if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                            $rec_ng++;      // ���Կ��������
                            break;          // NG �Τ���ȴ����
                        } else {
                            $rec_ok++;      // �������������
                        }
                    }
                } else {
                    $act_month = $res_chk[0][4] + $act_month;
                    $act_sum   = $res_chk[0][5] + $act_sum;
                    $act_get   = $res_chk[0][6] + $act_get;
                    if (($act_sum != 0) || ($act_month != 0)) {
                        $query = sprintf("update act_sga_summary set act_monthly=%d, act_sum=%d, act_getu=%d
                                where act_ki=%d and act_yymm=%d and act_id=%d and actcod=%d and aucod=%d", $act_month, $act_sum, $act_get,$res_chk[0][0],$res_chk[0][2],$res_chk[0][3],$res_chk[0][7],$res_chk[0][8]);
                        if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                            $rec_ng++;      // ���Կ��������
                            break;          // NG �Τ���ȴ����
                        } else {
                            $rec_ok++;      // �������������
                        }
                    }
                }
            } else {
                $row_sei++;         // �δ���ǤϤʤ�����¤�����
            }
        }
        $flag5 = 1;
    }
    unlink($file_name);     // ����ե�������� txt
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');    // ������Ͽ�Ѥˤϥ����ȥ�����

// ��å��������֤�
if ($flag1==1) {
    $msg .= "<font color='white'>���ǡ������ɲä��ޤ�����<br>";
    $msg .= $b . "��<br>";
    $msg .= $result1 . "<br></font>";
} else {
    $msg .= "<font color='yellow'>���ǡ������ɲåǡ���������ޤ���</font><br><br>";
}
if ($flag2==1) {
    $msg .= "<font color='white'>�����ƥ�ޥ���������<br>";
    $msg .= "insert $row_in ��<br>";
    $msg .= "update $row_up ��<br>";
    $msg .= "CSV_file $rowcsv ��<br>";
    $msg .= "Original $c ��<br><br></font>";
    // $msg .= $c . "��<br>";
    // $msg .= $result2 . "<br>";
} else {
    $msg .= "<font color='yellow'>�����ƥ�ޥ��������ɲåǡ���������ޤ���</font><br><br>";
}
if ($flag3==1) {
    $msg .= "<font color='white'>���ʻųݥ��ޥ꡼�ե�������ɲä��ޤ�����<br>";
    $msg .= $d . "��<br>";
    $msg .= $result3 . "<br></font>";
} else {
    $msg .= "<font color='yellow'>SGKSIKP���ɲåǡ���������ޤ���</font><br><br>";
}
if ($flag4==1) {
    $msg .= "<font color='white'>ϫ̳�񡦷��񥵥ޥ꡼�ե�������ɲä��ޤ�����<br>";
    $msg .= $e . "��<br>";
    $msg .= $result4 . '</font>';
} else {
    $msg .= "<font color='yellow'>AAYLAWL2.txt���ɲåǡ���������ޤ���</font><br>";
}
if ($flag5==1) {
    $msg .= "<font color='white'>�δ��񥵥ޥ꡼�ե�������ɲä��ޤ�����<br>";
    $msg .= $row_sei . "�� /" . $f . "�� ����¤����,<br>";
    $msg .= $row_han . "�� /" . $f . "�� ���δ���,<br>";
    $msg .= "�δ������" . $rec_ok . "�� /" . $row_han . "�� �ɲ�,<br>";
    $msg .= "�δ������" . $rec_ng . "�� /" . $row_han . "�� ����<br></font>";
} else {
    $msg .= "<font color='yellow'>AAYECTL6.txt���ɲåǡ���������ޤ���</font><br>";
}
$_SESSION["s_sysmsg"] = $msg;
header('Location: ' . H_WEB_HOST . SYS_MENU);


ob_end_flush();  //Warning: Cannot add header ���к��Τ����ɲá�

?>

