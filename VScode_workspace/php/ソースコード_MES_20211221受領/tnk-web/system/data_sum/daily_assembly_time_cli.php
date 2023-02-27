#!/usr/loca l/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω��Ȼ���(daily)����  (�������塼�顼���)                            //
// Copyright (C) 2020-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2020/02/03 Created   daily_assembly_time_cli.php                         //
// 2020/02/25 �Ѵ���Ͽ�ʤ����ײ�NO��1ʸ���ܤ�CLT�ǤϤʤ�����C�ˤ���褦   //
//            �ѹ���@�ײ���Ѵ���Ͽ�ʤ�����                               //
// 2020/09/29 �ǡ������Ϥ���ݴɤ��������ѹ�                            //
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

// ��Ω��Ȼ��� ������� �������
// �ե�����̾�ϥ������塼�顼¦�Ƿ���ΤǷ����ľ��
// $file_name = '/home/www/html/weekly/Q#MIITEM.CSV';
// �ʲ���������
//$file_name  = '/home/guest/daily/FLEXSCHE/CRESULT_WEB.CSV';
//$file_temp  = '/home/guest/daily/CRESULT_WEB.tmp';
//$file_write = '/home/guest/daily/CRESULT_WEB.txt';

$file_name  = '/home/guest/daily/CRESULT_WEB.CSV';
$file_temp  = '/home/guest/daily/CRESULT_WEB.tmp';
$file_write = '/home/guest/daily/CRESULT_WEB.txt';
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
        if ($data[0] == '' && $data[1] == '') continue;   // ���Ԥν���
        $data[1] = str_replace('"', '', $data[1]);  // �ʤ�����"��������֤������Τȡ�ޤǽ���ޤ��ΤǺ������
                                                    // �嵭�ϲ���pg_escape_string()����������Ǥ���
        //$data[1] = pg_escape_string($data[1]);      // ��̾
        //$data[2] = pg_escape_string($data[2]);      // ���
        //$data[3] = pg_escape_string($data[3]);      // �Ƶ���
        ///// data[0]�����ֹ��data[4]��Ͽ���϶�̳�Υ롼��奨�������פ���ɬ�פ�̵��
        fwrite($fpw,"{$data[0]}\t{$data[1]}\t{$data[2]}\t{$data[3]}\t{$data[4]}\t{$data[5]}\t{$data[6]}\t{$data[7]}\n");
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

// ��Ω��Ȼ��ּ��� �������
// �ե�����̾�ϥ������塼�顼¦�Ƿ���ΤǷ����ľ��
$file_name = '/home/guest/daily/CRESULT_WEB.txt';
$file_name_bak = '/home/guest/daily/backup/CRESULT_WEB-bak.txt';
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
                //$data[1] = addslashes($data[1]);    // "'"�����ǡ����ˤ������\�ǥ��������פ���
                //$data[1] = trim($data[1]);          // ����̾������Υ��ڡ������� AS/400��PCIX����Ѥ���FTPž���Τ���
                //$data[2] = trim($data[2]);          // ���̾������Υ��ڡ�������
                //$data[3] = trim($data[3]);          // �Ƶ��������Υ��ڡ�������
                // group_no $data[0]���Ѵ�
                $chk = "select group_no from assembly_line_change where line_no='{$data[0]}'";
                if (getUniResult($chk, $group_no) <= 0) {    // ���롼��No�ޥ������ˤ��뤫
                    // ��Ͽ�ʤ����
                    // �ײ�No�Υ����å�
                    if (substr($data[1], 0, 1) == 'C') {
                        $data[0] = 5;
                    } elseif (substr($data[1], 0, 1) == 'L') {
                        $data[0] = 9;
                    } elseif (substr($data[1], 0, 1) == 'T') {
                        $data[0] = 11;
                    } else {    // �Ѵ���Ͽ�ʤ���@�ײ�ξ��ϤȤꤢ�������ץ��
                        $query_chk = sprintf("select parts_no FROM assembly_schedule WHERE plan_no='%s'", $data[1]);
                        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                            // �ײ�No�����Ĥ���ʤ���ФȤꤢ�������ץ��
                            $data[0] = 5;
                        } else {
                            // �ײ�No���������ֹ���������ʸ���ܤ�Ƚ��
                            if (substr($res_chk[0], 0, 1) == 'C') {
                                $data[0] = 5;
                            } elseif (substr($res_chk[0], 0, 1) == 'L') {
                                $data[0] = 9;
                            } elseif (substr($res_chk[0], 0, 1) == 'T') {
                                $data[0] = 11;
                            } else {
                                // �����ֹ�Ǥ�Ƚ�̤Ǥ��ʤ���Х��ץ�
                                $data[0] = 5;
                            }
                        }
                    }
                } else {
                    // ��Ͽ������
                    $data[0] = $group_no;
                }
                ///////// ��Ͽ�ѤߤΥ����å�
                $query_chk = sprintf("select serial_no from assembly_process_time where plan_no='%s' and user_id='%s' and str_time='%s'", $data[1], $data[2], $data[3]);
                if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                    ///// ��Ͽ�ʤ� insert ����
                    $query = sprintf("insert into assembly_process_time (group_no, plan_no, user_id, str_time, end_time, plan_all_pcs, assy_time, plan_pcs)
                            values('%s','%s','%s','%s','%s',%d,'%s',%d)", $data[0],$data[1],$data[2],$data[3],$data[4],$data[5],$data[6],$data[7]);
                    if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                        $msg .= "assembly_process_time insert error rec No.=$rowcsv \n";
                        $miitem_ng_flg = TRUE;
                        break;          // NG �Τ���ȴ����
                    } else {
                        $row_in++;      // insert ����
                    }
                } else {
                /*
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
                */
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
        $msg .= "Q#TEST.txt�򥪡��ץ����ޤ���\n";
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
    $msg .= "{$log_date} ��Ω��Ȼ��ֹ���\n";
    $msg .= "{$log_date} insert $row_in ��\n";
    $msg .= "{$log_date} update $row_up ��\n";
    $msg .= "{$log_date} CSV_file $rowcsv ��\n";
    $msg .= "{$log_date} Original $c ��\n";
} else {
    $msg .= "{$log_date}:��Ω��Ȼ��֤ι����ǡ���������ޤ���\n";
}

// ��Ω��Ȼ��� ������� �������
// �ե�����̾�ϥ������塼�顼¦�Ƿ���ΤǷ����ľ��
// $file_name = '/home/www/html/weekly/Q#MIITEM.CSV';
// �ʲ���������
//$file_name  = '/home/guest/daily/FLEXSCHE/LRESULT_WEB.CSV';
//$file_temp  = '/home/guest/daily/LRESULT_WEB.tmp';
//$file_write = '/home/guest/daily/LRESULT_WEB.txt';

$file_name  = '/home/guest/daily/LRESULT_WEB.CSV';
$file_temp  = '/home/guest/daily/LRESULT_WEB.tmp';
$file_write = '/home/guest/daily/LRESULT_WEB.txt';
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
        if ($data[0] == '' && $data[1] == '') continue;   // ���Ԥν���
        $data[1] = str_replace('"', '', $data[1]);  // �ʤ�����"��������֤������Τȡ�ޤǽ���ޤ��ΤǺ������
                                                    // �嵭�ϲ���pg_escape_string()����������Ǥ���
        //$data[1] = pg_escape_string($data[1]);      // ��̾
        //$data[2] = pg_escape_string($data[2]);      // ���
        //$data[3] = pg_escape_string($data[3]);      // �Ƶ���
        ///// data[0]�����ֹ��data[4]��Ͽ���϶�̳�Υ롼��奨�������פ���ɬ�פ�̵��
        fwrite($fpw,"{$data[0]}\t{$data[1]}\t{$data[2]}\t{$data[3]}\t{$data[4]}\t{$data[5]}\t{$data[6]}\t{$data[7]}\n");
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

// ��Ω��Ȼ��ּ��� �������
// �ե�����̾�ϥ������塼�顼¦�Ƿ���ΤǷ����ľ��
$file_name = '/home/guest/daily/LRESULT_WEB.txt';
$file_name_bak = '/home/guest/daily/backup/LRESULT_WEB-bak.txt';
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
                //$data[1] = addslashes($data[1]);    // "'"�����ǡ����ˤ������\�ǥ��������פ���
                //$data[1] = trim($data[1]);          // ����̾������Υ��ڡ������� AS/400��PCIX����Ѥ���FTPž���Τ���
                //$data[2] = trim($data[2]);          // ���̾������Υ��ڡ�������
                //$data[3] = trim($data[3]);          // �Ƶ��������Υ��ڡ�������
                // group_no $data[0]���Ѵ�
                $chk = "select group_no from assembly_line_change where line_no='{$data[0]}'";
                if (getUniResult($chk, $group_no) <= 0) {    // ���롼��No�ޥ������ˤ��뤫
                    // ��Ͽ�ʤ����
                    // �ײ�No�Υ����å�
                    if (substr($data[1], 0, 1) == 'C') {
                        $data[0] = 5;
                    } elseif (substr($data[1], 0, 1) == 'L') {
                        $data[0] = 9;
                    } elseif (substr($data[1], 0, 1) == 'T') {
                        $data[0] = 11;
                    } else {    // �Ѵ���Ͽ�ʤ���@�ײ�ξ��ϤȤꤢ�������ץ��
                        $query_chk = sprintf("select parts_no FROM assembly_schedule WHERE plan_no='%s'", $data[1]);
                        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                            // �ײ�No�����Ĥ���ʤ���ФȤꤢ�������ץ��
                            $data[0] = 5;
                        } else {
                            // �ײ�No���������ֹ���������ʸ���ܤ�Ƚ��
                            if (substr($res_chk[0], 0, 1) == 'C') {
                                $data[0] = 5;
                            } elseif (substr($res_chk[0], 0, 1) == 'L') {
                                $data[0] = 9;
                            } elseif (substr($res_chk[0], 0, 1) == 'T') {
                                $data[0] = 11;
                            } else {
                                // �����ֹ�Ǥ�Ƚ�̤Ǥ��ʤ���Х��ץ�
                                $data[0] = 5;
                            }
                        }
                    }
                } else {
                    // ��Ͽ������
                    $data[0] = $group_no;
                }
                ///////// ��Ͽ�ѤߤΥ����å�
                $query_chk = sprintf("select serial_no from assembly_process_time where plan_no='%s' and user_id='%s' and str_time='%s'", $data[1], $data[2], $data[3]);
                if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                    ///// ��Ͽ�ʤ� insert ����
                    $query = sprintf("insert into assembly_process_time (group_no, plan_no, user_id, str_time, end_time, plan_all_pcs, assy_time, plan_pcs)
                            values('%s','%s','%s','%s','%s',%d,'%s',%d)", $data[0],$data[1],$data[2],$data[3],$data[4],$data[5],$data[6],$data[7]);
                    if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                        $msg .= "assembly_process_time insert error rec No.=$rowcsv \n";
                        $miitem_ng_flg = TRUE;
                        break;          // NG �Τ���ȴ����
                    } else {
                        $row_in++;      // insert ����
                    }
                } else {
                /*
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
                */
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
        $msg .= "Q#TEST.txt�򥪡��ץ����ޤ���\n";
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
    $msg .= "{$log_date} ��Ω��Ȼ��ֹ���\n";
    $msg .= "{$log_date} insert $row_in ��\n";
    $msg .= "{$log_date} update $row_up ��\n";
    $msg .= "{$log_date} CSV_file $rowcsv ��\n";
    $msg .= "{$log_date} Original $c ��\n";
} else {
    $msg .= "{$log_date}:��Ω��Ȼ��֤ι����ǡ���������ޤ���\n";
}
/*
$fpa = fopen('/tmp/nippo.log', 'a');    ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤǥ����ץ�
fwrite($fpb, "����(daily)����\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/daily_cli.php\n");
fwrite($fpb, "------------------------------------------------------------------------\n");
fwrite($fpb, "��Ω��Ȼ��֤ι���\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/daily_miitem_cli.php\n");

fwrite($fpa, $msg);
fwrite($fpb, $msg);
echo "$msg";
fclose($fpa);      ////// �����ѥ�����߽�λ
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߽�λ
*/
exit();
?>
