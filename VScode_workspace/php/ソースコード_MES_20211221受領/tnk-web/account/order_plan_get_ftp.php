<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-4.3.4-cgi -q �� php-4.3.7-cgi -q �� php(�ǿ�)��     //
// ����ǡ��� ��ưFTP Download  ȯ��ײ�ե����� UKWLIB/W#MIOPLN            //
//                                          AS/400 ----> Web Server (PHP)   //
// Copyright (C) 2003-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/11/20 Created  order_plan_get_ftp.php                               //
//            http �� cli�Ǥ��ѹ������褦�� requier_once �����л����     //
// 2004/01/05 field����19�ξ�礬���뤿�� �����å���20��19���ѹ�            //
// 2004/03/24 php-4.3.5RC4 �ˤ������ᤫ�� AS/400 del record �Υ��å��ɲ�  //
// 2004/04/05 header('Location: http:' . WEB_HOST . ACT -->                 //
//                                  header('Location: ' . H_WEB_HOST . ACT  //
// 2004/04/09 php-4.3.6RC2�ǹ���fgetcsv()�λ��ͤ��ѹ�����$num�Υ����å��ѹ� //
// 2004/06/07 php-4.3.6-cgi -q �� php-4.3.7-cgi -q  �С�����󥢥å�        //
// 2005/04/28 AS/400�ǡ����κ���쥳���ɤ��б� ��¤�ֹ椬̵��ʪ����       //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug ��
// ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
ini_set('max_execution_time', 1200);    // ����¹Ի��� 1200=20ʬ
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('/home/www/html/tnk-web/function.php');
require_once ('/home/www/html/tnk-web/tnk_func.php');   // account_group_check()�ǻ���
access_log();                           // Script Name ��ư����
// $_SESSION['site_index'] = 20;           // ��������ط�=20 �»�״ط�=10 �Ǹ�Υ�˥塼�� 99 �����
// $_SESSION['site_id']    = 10;           // ���̥�˥塼̵�� (0 <=)

//////////// �ƽи��μ���
$act_referer = $_SESSION['act_referer'];

//////////// ǧ�ڥ����å�
if (account_group_check() == FALSE) {
    // $_SESSION['s_sysmsg'] = '���ʤ��ϵ��Ĥ���Ƥ��ޤ���!<br>�����Ԥ�Ϣ���Ʋ�����!';
    $_SESSION['s_sysmsg'] = "Accounting Group �θ��¤�ɬ�פǤ���";
    header('Location: ' . $act_referer);
    exit();
}

$log_date = date('Y-m-d H:i:s');                ///// �����ѥ�������
$fpa = fopen('/tmp/act_payable.log', 'a');      ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
$_SESSION['s_sysmsg'] = '';     // �����

/////////// Download file AS/400 & Save file
$down_file = 'UKWLIB/W#MIOPLN';
$save_file = 'W#MIOPLN.TXT';

// ���ͥ���������(FTP��³�Υ����ץ�)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        if (ftp_get($ftp_stream, $save_file, $down_file, FTP_ASCII)) {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>ftp_get download ���� $down_file �� $save_file </font><br>";
            fwrite($fpa,"$log_date ftp_get download ���� $down_file �� $save_file \n");
        } else {
            $_SESSION['s_sysmsg'] .=  "ftp_get() error $down_file <br>";
            fwrite($fpa,"$log_date ftp_get() error $down_file \n");
        }
    } else {
        $_SESSION['s_sysmsg'] .=  'ftp_login() error<br>';
        fwrite($fpa,"$log_date ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    $_SESSION['s_sysmsg'] .= 'ftp_connect() error --> ȯ��ײ�<br>';
    fwrite($fpa,"$log_date ftp_connect() error --> ȯ��ײ� \n");
}

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    $_SESSION['s_sysmsg'] .= 'db_connect() error';
    fwrite($fpa, "$log_date db_connect() error \n");
    exit();
}
// ȯ��ײ� ������� �������
$file_orign  = 'W#MIOPLN.TXT';
$file_backup = 'backup/W#MIOPLN-BAK.TXT';
$file_test   = 'debug/debug-MIOPLN.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    $pre_sei_no = '9999999';    // ������¤�ֹ�(����ͤ�9999999���оݳ��ˤ���)
    $del_ok = 0;    // DELETE�ѥ����󥿡�
    $del2_ok = 0;   // DELETE�ѥ����󥿡�(ȯ��������)
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 300, '_');     // �¥쥳���ɤ�129�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���'_'�����
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num == 19) {
            $data[19] = '';     // �ե�����ɿ����̾�20����19�λ������뤿�ᡢ�����б���20���ܤΥե�����ɤ����
        } elseif ($num < 19) {
            if ($num == 0 || $num == 1) {   // php-4.3.5��0�֤� php-4.3.6��1���֤����ͤˤʤä���fgetcsv�λ����ѹ��ˤ��
                $_SESSION['s_sysmsg'] .= "<font color='yellow'>AS/400 del record=$rec </font><br>";
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
            } else {
                $_SESSION['s_sysmsg'] .= "field error record=$rec <br>";
                fwrite($fpa, "$log_date field error record=$rec  num=$num \n");
            }
            continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        }
        
        $query_chk = sprintf("SELECT sei_no FROM order_plan WHERE sei_no=%d", $data[0]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "INSERT INTO order_plan (sei_no, order5, parts_no, mecin, so_kubun, order_q,
                      utikiri, nyuko, plan_date, last_delv, order_ku, plan_cond, locate, zan_q, div,
                      tan_no, kouji_no, org_delv, hakou, kubun)
                      VALUES(
                       {$data[0]} ,
                       {$data[1]} ,
                      '{$data[2]}',
                      '{$data[3]}',
                      '{$data[4]}',
                       {$data[5]} ,
                       {$data[6]} ,
                       {$data[7]} ,
                       {$data[8]} ,
                       {$data[9]} ,
                      '{$data[10]}',
                      '{$data[11]}',
                      '{$data[12]}',
                       {$data[13]} ,
                      '{$data[14]}',
                      '{$data[15]}',
                      '{$data[16]}',
                       {$data[17]} ,
                       {$data[18]} ,
                      '{$data[19]}' )";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                $_SESSION['s_sysmsg'] .= "{$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!<br>";
                fwrite($fpa, "$log_date ��¤�ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
            ///// ��Ͽ���� update ����
            $query = "UPDATE order_plan SET sei_no={$data[0]}, order5={$data[1]}, parts_no='{$data[2]}',
                      mecin='{$data[3]}', so_kubun='{$data[4]}', order_q={$data[5]}, utikiri={$data[6]},
                      nyuko={$data[7]}, plan_date={$data[8]}, last_delv={$data[9]}, order_ku='{$data[10]}',
                      plan_cond='{$data[11]}', locate='{$data[12]}', zan_q={$data[13]},
                      div='{$data[14]}', tan_no='{$data[15]}', kouji_no='{$data[16]}', org_delv={$data[17]},
                      hakou={$data[18]}, kubun='{$data[19]}'
                where sei_no={$data[0]}";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                $_SESSION['s_sysmsg'] .= "{$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n";
                fwrite($fpa, "$log_date ��¤�ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
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
        ///// AS/400��Ǻ�����줿ʪ�ν���
        $del_sql = "DELETE FROM order_plan WHERE sei_no > {$pre_sei_no} AND sei_no < {$data[0]}";
        if (($del_rec = query_affected_trans($con, $del_sql)) < 0) {     // �����ѥ����꡼�μ¹�
            $_SESSION['s_sysmsg'] .= "{$rec}:�쥳�����ܤ�DELETE�˼��Ԥ��ޤ���!\n";
            fwrite($fpa, "$log_date ��¤�ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�DELETE�˼��Ԥ��ޤ���!\n");
        } else {
            $del_ok += $del_rec;
        }
        ///// ȯ�������٤κ������
        $del_sql = "DELETE FROM order_process WHERE sei_no > {$pre_sei_no} AND sei_no < {$data[0]}";
        if (($del2_rec = query_affected_trans($con, $del_sql)) < 0) {     // �����ѥ����꡼�μ¹�
            $_SESSION['s_sysmsg'] .= "{$rec}:�쥳������ ȯ�������٤�DELETE�˼��Ԥ��ޤ���!\n";
            fwrite($fpa, "$log_date ��¤�ֹ�:{$data[0]} : {$rec}:�쥳������ ȯ�������٤�DELETE�˼��Ԥ��ޤ���!\n");
        } else {
            $del2_ok += $del2_rec;
        }
        $pre_sei_no = $data[0];     // ���ν����Τ�����¸
    }
    fclose($fp);
    fclose($fpw);       // debug
    $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$rec_ok}/{$rec} �ﹹ�����ޤ�����</font><br><br>";
    $_SESSION['s_sysmsg'] .= "<font color='white'>{$ins_ok}/{$rec} �� �ɲ�<br>";
    $_SESSION['s_sysmsg'] .= "{$upd_ok}/{$rec} �� �ѹ�<br>";
    $_SESSION['s_sysmsg'] .= "{$del_ok}/{$rec} �� ���<br>";
    $_SESSION['s_sysmsg'] .= "�������� {$del2_ok}/{$rec} �� ���</font>";
    fwrite($fpa, "$log_date ȯ��ײ�ι���:{$data[0]} : $rec_ok/$rec �ﹹ�����ޤ�����\n");
    fwrite($fpa, "$log_date ȯ��ײ�ι���:{$data[0]} : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date ȯ��ײ�ι���:{$data[0]} : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpa, "$log_date ȯ��ײ�ι���:{$data[0]} : {$del_ok}/{$rec} �� ��� \n");
    fwrite($fpa, "$log_date ȯ�������٤ι���:{$data[0]} : {$del2_ok}/{$rec} �� ��� \n");
    if ($rec_ng == 0) {     // ����ߥ��顼���ʤ���� �ե��������
        if (file_exists($file_backup)) {
            unlink($file_backup);       // Backup �ե�����κ��
        }
        if (!rename($file_orign, $file_backup)) {
            $_SESSION['s_sysmsg'] .= "$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n";
            fwrite($fpa,"$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n");
        }
    }
} else {
    $_SESSION['s_sysmsg'] .= "ȯ��ײ�ե����� {$file_orign} ������ޤ���!";
    fwrite($fpa,"$log_date �ե����� $file_orign ������ޤ���!\n");
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');
// echo $query . "\n";  // debug
fclose($fpa);      ////// �����ѥ�����߽�λ

header('Location: ' . H_WEB_HOST . ACT . 'order_plan_view.php');   // �����å��ꥹ�Ȥ�
// header('Location: http://masterst.tnk.co.jp/account/order_plan_view.php');
// header('Location: ' . $act_referer);
exit();
?>
