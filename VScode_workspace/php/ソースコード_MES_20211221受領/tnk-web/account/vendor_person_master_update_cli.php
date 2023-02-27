#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-4.3.9-cli -c php4.ini                               //
// #!/usr/local/bin/php-4.3.4-cgi -q                                        //
// �٥�����ޥ�����(ô����)�ι���  AS400 UKWLIB/W#MIWKCKTA                  //
// AS/400 ----> Web Server (PHP) FTPž�����Բ� EBCDIC���Ѵ�������ʤ�����   //
// Copyright (C) 2017-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2017/05/26 Created  vendor_person_master_update.php                      //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI�Ǥ�ɬ�פʤ�
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('/home/www/html/tnk-web/function.php');
// require_once ('/home/www/html/tnk-web/tnk_func.php');   // account_group_check()�ǻ���
// access_log();                               // Script Name ��ư����
// $_SESSION['site_index'] = 20;               // �»�״ط�=10 �Ǹ�Υ�˥塼�� 99 �����
// $_SESSION['site_id']    = 10;               // ���̥�˥塼̵�� (0 <=)

if (!isset($_ENV['LANG'])) {
    $_SESSION['s_sysmsg'] = 'LANG �Ķ��ѿ������ꤵ��Ƥ��ޤ��� ��ߤ��ޤ���';
    exit();
    // $_ENV['LANG'] = 'ja_JP.eucJP';
}

//////////// �ƽи��μ���
// $act_referer = $_SESSION['act_referer'];

//////////// ǧ�ڥ����å�
// if (account_group_check() == FALSE) {
//     $_SESSION['s_sysmsg'] = "Accounting Group �θ��¤�ɬ�פǤ���";
//     header('Location: ' . $act_referer);
//     exit();
// }

$log_date = date('Y-m-d H:i:s');                    ///// �����ѥ�������
$fpa = fopen('/tmp/monthly_update.log', 'a');       ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
// $_SESSION['s_sysmsg'] = '';     // �����

/********************
/////////// Download file AS/400 & Save file
$down_file = 'UKWLIB/W#HIBCTR';
$save_file = 'W#HIBCTR.TXT';

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
    $_SESSION['s_sysmsg'] .= 'ftp_connect() error --> ����������<br>';
    fwrite($fpa,"$log_date ftp_connect() error --> ����������\n");
}
*********************/

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
//     $_SESSION['s_sysmsg'] .= 'db_connect() error';
    fwrite($fpa, "$log_date db_connect() error \n");
    exit();
}
// �٥�����ޥ������ι��� �������
$file_orign  = '/home/guest/monthly/W#MIWKCKTA.TXT';
$file_temp   = 'W#MIWKCKTA-TEMP.TXT';
$file_backup = 'backup/W#MIWKCKTA-BAK.TXT';
$file_test   = 'debug/debug-MIWKCKTA.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp = fopen($file_orign, 'r');
        ///////////// SJIS �� EUC �Ѵ����å� START (SJIS��EUC�ˤʤ�ʸ����NULL�Х��Ȥ��Ѵ������������)
    $fp_conv = fopen($file_temp, 'w');  // EUC ���Ѵ���
    while (!(feof($fp))) {
        $data = fgets($fp, 300);
        $data = mb_convert_encoding($data, 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
        $data = str_replace("\0", ' ', $data);                      // NULL�Х��Ȥ�SPACE���Ѵ�
        $data = mb_ereg_replace('��', '�ʳ���', $data);             // �����¸ʸ���򵬳�ʸ�����ѹ�
        fwrite($fp_conv, $data);
    }
    fclose($fp);
    fclose($fp_conv);
    $fp = fopen($file_temp, 'r');       // EUC ���Ѵ���Υե�����
        ///////////// SJIS �� EUC �Ѵ����å� END
    $fpw = fopen($file_test, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 300, "_");     // �¥쥳���ɤ�150�Х��� �ǥ�ߥ��ϥ��֤��饢��������������ѹ�
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        /*
        if ($num < 6) {
            $rec_no = $rec;     // �ºݤΥ쥳�����ֹ� ���$rec++����褦�ˤ����Τǡ����Τޤޤǣϣ�
            // $_SESSION['s_sysmsg'] .= "field not 6&7 record=$rec_no <br>";
            fwrite($fpa, "$log_date field not 6&7 record=$rec_no \n");
                ////////////////////////////////////////// Debug start
                for ($f=0; $f<$num; $f++) {
                    fwrite($fpw,"'{$data[$f]}'\n");     // debug
                }
                fwrite($fpw,"\n");                      // debug
                break;                                  // debug
                ////////////////////////////////////////// Debug end
            continue;
        } elseif ($num == 6) {
            $data[6] = '';      // ��ɽ�Ԥ�blank
        }
        */
        for ($f=0; $f<$num; $f++) {
            // $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        }
        $query_chk = sprintf("SELECT vendor FROM vendor_person_master WHERE vendor='%s' AND div='%s'", $data[0], $data[1]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "INSERT INTO vendor_person_master (vendor, div, uid)
                      VALUES(
                      '{$data[0]}',
                      '{$data[1]}',
                      '{$data[2]}')";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                // $_SESSION['s_sysmsg'] .= "{$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!<br>";
                fwrite($fpa, "$log_date ȯ����̾:{$data[1]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
            $query = "UPDATE vendor_person_master SET vendor='{$data[0]}', div='{$data[1]}', uid='{$data[2]}'
                      where vendor='{$data[0]}' AND div='{$data[1]}'";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                // $_SESSION['s_sysmsg'] .= "{$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n";
                fwrite($fpa, "$log_date ȯ����̾:{$data[1]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
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
    fclose($fp);
    fclose($fpw);       // debug
    // $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$rec_ok}/{$rec} ����Ͽ���ޤ�����</font><br><br>";
    // $_SESSION['s_sysmsg'] .= "<font color='white'>{$ins_ok}/{$rec} �� �ɲ�<br>";
    // $_SESSION['s_sysmsg'] .= "{$upd_ok}/{$rec} �� �ѹ�</font>";
    echo "<font color='yellow'>{$rec_ok}/{$rec} ����Ͽ���ޤ�����</font><br><br>";
    echo "<font color='white'>{$ins_ok}/{$rec} �� �ɲ� <br>";
    echo "{$upd_ok}/{$rec} �� �ѹ�</font>";
    fwrite($fpa, "$log_date ȯ����ι���:{$data[1]} : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date ȯ����ι���:{$data[1]} : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date ȯ����ι���:{$data[1]} : {$upd_ok}/{$rec} �� �ѹ� \n");
    if ($rec_ng == 0) {     // ����ߥ��顼���ʤ���� �ե��������
        if (file_exists($file_backup)) {
            unlink($file_backup);       // Backup �ե�����κ��
            unlink($file_temp);         // temp �ե�����κ��
        }
        if (!rename($file_orign, $file_backup)) {
            // $_SESSION['s_sysmsg'] .= "$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n";
            fwrite($fpa,"$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n");
        }
    }
} else {
    // $_SESSION['s_sysmsg'] .= "<font color='yellow'>�ȥ�󥶥������ե����뤬����ޤ���</font>";
    fwrite($fpa,"$log_date : ȯ����ι����ե����� {$file_orign} ������ޤ���\n");
    echo 'ȯ����ι����ե����뤬����ޤ���';
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');
// echo $query . "\n";  // debug
fwrite($fpa,"$log_date : LANG = {$_ENV['LANG']}\n");    // fgetcsv()�Ѥ�LANG�Ķ��ѿ��γ�ǧ
fclose($fpa);      ////// �����ѥ�����߽�λ

// header('Location: ' . H_WEB_HOST . ACT . 'vendor_master_view.php');   // �����å��ꥹ�Ȥ�
exit();
?>
