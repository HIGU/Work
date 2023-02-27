#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-4.3.4-cgi -q �� php-4.3.7-cgi -q �� php(�ǿ�)��     //
// ȯ��ײ�ե�����(AS/400 UKWLIB/W#MIOPLN)�μ�ư������                     //
//                                          AS/400 ----> Web Server (PHP)   //
// Copyright (C) 2003-2017 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
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
//            �Ǹ�Υ쥳����(��¤�ֹ�)�ʹߤ����оݤˤ��Ƥ��ʤ��Τ�AS/400��//
//            ž�����˥��顼������������Υ쥳���ɤǽ���������������    //
// 2005/05/07 ������˥塼�� http�Ǽ¹Ԥ��Ƥ���ʪ�� CLI�Ǥ��ѹ�             //
// 2005/05/11 ���ޥ�ɥ饤������Ѥ� echo ʸ�򥳥��ȥ�����                //
// 2006/04/26 ���̹����Τ��� BEGIN COMMIT �򥳥���                        //
// 2006/11/08 checkTableChange()���ɲä��ƥǡ������ѹ�����Ƥ���ʪ�Τ߹�����//
// 2017/06/12 ��ʸ����⼨�ǡ����κ�����ɲ�                           ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
ini_set('max_execution_time', 1200);        // ����¹Ի��� 1200=20ʬ
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');            // �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');        // �����ѥ��ե�����ؤν���ߤǥ����ץ�

/////////// Download file AS/400 & Save file
// FTP�Υ������åȥե�����
$target_file = 'UKWLIB/W#MIOPLN';           // ȯ��ײ�ե����� download file
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
$save_file = '/home/www/html/tnk-web/system/backup/W#MIOPLN.TXT';   // ȯ��ײ�ե����� save file

// ���ͥ���������(FTP��³�Υ����ץ�)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        if (ftp_get($ftp_stream, $save_file, $target_file, FTP_ASCII)) {
            // echo '$log_date ftp_get download OK ', $target_file, '��', $save_file, "\n";
            fwrite($fpa,"$log_date ftp_get download ���� $target_file �� $save_file \n");
        } else {
            // echo '$log_date ftp_get() error ', $target_file, "\n";
            fwrite($fpa,"$log_date ftp_get() error $target_file \n");
        }
    } else {
        // echo "$log_date ftp_login() error \n";
        fwrite($fpa,"$log_date ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    // echo "$log_date ftp_connect() error --> ȯ��������\n";
    fwrite($fpa,"$log_date ftp_connect() error --> ȯ��ײ� \n");
}

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    // query_affected_trans($con, 'BEGIN');
} else {
    // echo "$log_date db_connect() error \n";
    fwrite($fpa, "$log_date db_connect() error \n");
    exit();
}
// ȯ��ײ� ������� �������
$file_orign  = $save_file;
$file_backup = '/home/www/html/tnk-web/system/backup/W#MIOPLN-BAK.TXT';
$file_test   = '/home/www/html/tnk-web/system/debug/debug-MIOPLN.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    $noChg  = 0;    // ̤�ѹ������󥿡�
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
                // echo "$log_date AS/400 del record=$rec \n";
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
            } else {
                // echo "$log_date field error 19 LT record=$rec \n";
                fwrite($fpa, "$log_date field error record=$rec  num=$num \n");
            }
            continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        }
        
        $query_chk = sprintf("SELECT * FROM order_plan WHERE sei_no=%d", $data[0]);
        if (getResultTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
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
                // echo "$log_date {$rec}:�쥳�����ܤ�Insert�˼��Ԥ��ޤ���!\n";
                fwrite($fpa, "$log_date ��¤�ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�Insert�˼��Ԥ��ޤ���!\n");
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
            if (checkTableChange($data, $res_chk[0])) {
                $noChg++;
                // AS/400��Ǻ�����줿ʪ�ν����Τ��� continue �򥳥��Ȥˤ���
                // continue;
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
                    // echo "$log_date {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n";
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
        }
        ///// AS/400��Ǻ�����줿ʪ�ν���
        $del_sql = "DELETE FROM order_plan WHERE sei_no > {$pre_sei_no} AND sei_no < {$data[0]}";
        if (($del_rec = query_affected_trans($con, $del_sql)) < 0) {     // �����ѥ����꡼�μ¹�
            // echo "$log_date {$rec}:�쥳�����ܤ�DELETE�˼��Ԥ��ޤ���!\n";
            fwrite($fpa, "$log_date ��¤�ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�DELETE�˼��Ԥ��ޤ���!\n");
        } else {
            $del_ok += $del_rec;
        }
        ///// ȯ�������٤κ������
        $del_sql = "DELETE FROM order_process WHERE sei_no > {$pre_sei_no} AND sei_no < {$data[0]}";
        if (($del2_rec = query_affected_trans($con, $del_sql)) < 0) {     // �����ѥ����꡼�μ¹�
            // echo "$log_date {$rec}:�쥳������ ȯ�������٤�DELETE�˼��Ԥ��ޤ���!\n";
            fwrite($fpa, "$log_date ��¤�ֹ�:{$data[0]} : {$rec}:�쥳������ ȯ�������٤�DELETE�˼��Ԥ��ޤ���!\n");
        } else {
            $del2_ok += $del2_rec;
        }
        /*
        ///// �⼨��κ���������ɲ� 2017/06/12��
        $chk_sql = "SELECT * FROM order_process WHERE sei_no = {$data[0]} and plan_cond='O'";
        if (($chk_rec = query_affected_trans($con, $chk_sql)) < 0) {     // �����ѥ����꡼�μ¹�
            // echo "$log_date {$rec}:�쥳������ ȯ�������٤�DELETE�˼��Ԥ��ޤ���!\n";
            //fwrite($fpa, "$log_date ��¤�ֹ�:{$data[0]} : {$rec}:�쥳������ ȯ�������٤�DELETE�˼��Ԥ��ޤ���!\n");
        } else {
            //$del2_ok += $del2_rec;
            $chk2_sql = "SELECT * FROM order_process WHERE sei_no = {$data[0]} and plan_cond='R'";
            if (($chk2_rec = query_affected_trans($con, $chk2_sql)) < 0) {     // �����ѥ����꡼�μ¹�
            } else {
                $del2_sql = "DELETE FROM order_process WHERE sei_no = {$data[0]} AND plan_cond='R'";
                if (($del2_rec = query_affected_trans($con, $del2_sql)) < 0) {     // �����ѥ����꡼�μ¹�
                } else {
                }
            }
        }
        */
        $pre_sei_no = $data[0];     // ���ν����Τ�����¸
    }
    fclose($fp);
    fclose($fpw);       // debug
    // echo "$log_date ȯ��ײ�ι���:{$rec_ok}/{$rec} �ﹹ�����ޤ�����\n";
    // echo "$log_date ȯ��ײ�ι���:{$ins_ok}/{$rec} �� �ɲ�\n";
    // echo "$log_date ȯ��ײ�ι���:{$upd_ok}/{$rec} �� �ѹ�\n";
    // echo "$log_date ȯ��ײ�ι���:{$del_ok}/{$rec} �� ���\n";
    // echo "$log_date ȯ�������٤ι��� {$del2_ok}/{$rec} �� ���\n";
    fwrite($fpa, "$log_date ȯ��ײ�ι���:{$data[0]} : $rec_ok/$rec �ﹹ�����ޤ�����\n");
    fwrite($fpa, "$log_date ȯ��ײ�ι���:{$data[0]} : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date ȯ��ײ�ι���:{$data[0]} : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpa, "$log_date ȯ��ײ�ι���:{$data[0]} : {$noChg}/{$rec} �� ̤�ѹ� \n");
    fwrite($fpa, "$log_date ȯ��ײ�ι���:{$data[0]} : {$del_ok}/{$rec} �� ��� \n");
    fwrite($fpa, "$log_date ȯ�������٤ι���:{$data[0]} : {$del2_ok}/{$rec} �� ��� \n");
    if ($rec_ng == 0) {     // ����ߥ��顼���ʤ���� �ե��������
        if (file_exists($file_backup)) {
            unlink($file_backup);       // Backup �ե�����κ��
        }
        if (!rename($file_orign, $file_backup)) {
            // echo "$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n";
            fwrite($fpa,"$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n");
        }
    }
} else {
    // echo "$log_date ȯ��ײ�ե����� {$file_orign} ������ޤ���!\n";
    fwrite($fpa,"$log_date �ե����� $file_orign ������ޤ���!\n");
}
/////////// commit �ȥ�󥶥������λ
// query_affected_trans($con, 'COMMIT');
fclose($fpa);      ////// �����ѥ�����߽�λ

exit();

/***** �ơ��֥뤬�ѹ�����Ƥ������false���֤�     *****/
/***** ��������Ӥ���ǡ���������ȥơ��֥������   *****/
function checkTableChange($data, $res)
{
    for ($i=0; $i<20; $i++) {
        // ��Ӥ˼���򤹤륹�ڡ�������
        if (trim($data[$i]) != trim($res[$i])) {
            return false;
        }
    }
    return true;
}

?>
