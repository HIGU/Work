<?php
//////////////////////////////////////////////////////////////////////////////
// ê���ե�����(����������б�)�ι���   AS/400 UKWLIB/W#MVTNPT            //
//   AS/400 ----> Web Server (PHP) FTPž�����Բ� EBCDIC���Ѵ�������ʤ����� //
// Copyright (C) 2003-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/11/21 Created  inventory_month_update.php                           //
//                              vendor_master_update.php������˻��Ѥ���    //
//            http �� cli�Ǥ��ѹ������褦�� requier_once �����л����     //
//            AS/400 �� UKPLIB/Q#MIWKCK RUNQRY �Ǽ¹Ԥ���ü����ž��         //
// 2003/11/28 ���򥳥��Ȥˤ��Ƥ����Τ� monthly_update.log �ˤ����ɲ�    //
// 2003/12/04 inventory_month_end �� inventory_monthly ���ѹ�(ǯ���ɲ�)     //
// 2004/04/05 header('Location: http:' . WEB_HOST . 'account/?????' -->     //
//                                  header('Location: ' . H_WEB_HOST . ACT  //
// 2005/02/09 �ǥ��쥯�ȥ���ѹ� account/ �� account/inventory/ ��          //
// 2005/03/04 dir�ѹ� /home/www/html/weekly/ �� /home/guest/monthly/       //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('/home/www/html/tnk-web/function.php');
require_once ('/home/www/html/tnk-web/tnk_func.php');   // account_group_check()�ǻ���
access_log();                           // Script Name ��ư����

//////////// �ƽи��μ���
$act_referer = $_SESSION['act_referer'];

//////////// ǧ�ڥ����å�
if (account_group_check() == FALSE) {
    // $_SESSION['s_sysmsg'] = '���ʤ��ϵ��Ĥ���Ƥ��ޤ���!<br>�����Ԥ�Ϣ���Ʋ�����!';
    $_SESSION['s_sysmsg'] = "Accounting Group �θ��¤�ɬ�פǤ���";
    header('Location: ' . $act_referer);
    exit();
}

/////////// ��о�ǯ������
if (isset($_SESSION['act_ym'])) {
    $act_ym = $_SESSION['act_ym'];
} else {
    $_SESSION['s_sysmsg'] = '��о�ǯ����ꤵ��Ƥ��ޤ���!';
    header('Location: ' . $act_referer);
    exit();
}

$log_date = date('Y-m-d H:i:s');                    ///// �����ѥ�������
$fpa = fopen('/tmp/monthly_update.log', 'a');       ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
$_SESSION['s_sysmsg'] = '';     // �����

/********************
/////////// Download file AS/400 & Save file
$down_file = 'UKWLIB/W#MVTNPT';
$save_file = 'W#MVTNPT.TXT';

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
    $_SESSION['s_sysmsg'] .= 'ftp_connect() error --> ê�������<br>';
    fwrite($fpa,"$log_date ftp_connect() error --> ê�������\n");
}
*********************/

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    $_SESSION['s_sysmsg'] .= 'db_connect() error';
    fwrite($fpa, "$log_date db_connect() error \n");
    exit();
}
///////// ê���ե�����ι��� �������
$file_orign  = '/home/guest/monthly/W#MVTNPT.TXT';
$file_backup = '../backup/W#MVTNPT-BAK.TXT';
$file_test   = '../debug/debug-MVTNPT.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 200, "_");     // �¥쥳���ɤ�65�Х��� �ǥ�ߥ��ϥ���
        if (feof($fp)) {
            break;
        }
        // if (!isset($data[6])) {
        //     $data[6] = '';      // AS/400�˥ǡ�����̵����礬���뤿�� �������ο��� query�Τ��ᤫ��
        // }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num != 7) {
            $_SESSION['s_sysmsg'] .= "field not 7 record=$rec <br>";
            fwrite($fpa, "$log_date field not 7 record=$rec \n");
            continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        }
        
        $query_chk = sprintf("SELECT parts_no FROM inventory_monthly WHERE invent_ym={$act_ym} and parts_no='%s'", $data[0]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "INSERT INTO inventory_monthly (invent_ym, parts_no, par_code, zen_zai, tou_zai, gai_tan, nai_tan, num_div)
                      VALUES(
                        $act_ym   ,
                      '{$data[0]}',
                      '{$data[1]}',
                       {$data[2]} ,
                       {$data[3]} ,
                       {$data[4]} ,
                       {$data[5]} ,
                      '{$data[6]}')";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                $_SESSION['s_sysmsg'] .= "{$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!<br>";
                fwrite($fpa, "$log_date �����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
            $query = "UPDATE inventory_monthly SET invent_ym={$act_ym}, parts_no='{$data[0]}', par_code='{$data[1]}',
                        zen_zai={$data[2]}, tou_zai={$data[3]}, gai_tan={$data[4]}, nai_tan={$data[5]},
                        num_div='{$data[6]}'
                      where invent_ym={$act_ym} and parts_no='{$data[0]}'";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                $_SESSION['s_sysmsg'] .= "{$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n";
                fwrite($fpa, "$log_date �����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
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
    $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$rec_ok}/{$rec} ����Ͽ���ޤ�����</font><br><br>";
    $_SESSION['s_sysmsg'] .= "<font color='white'>{$ins_ok}/{$rec} �� �ɲ�<br>";
    $_SESSION['s_sysmsg'] .= "{$upd_ok}/{$rec} �� �ѹ�</font>";
    fwrite($fpa, "$log_date ê���ι���:{$data[1]} : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date ê���ι���:{$data[1]} : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date ê���ι���:{$data[1]} : {$upd_ok}/{$rec} �� �ѹ� \n");
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
    $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ê���ǡ���������ޤ���</font>";
    fwrite($fpa,"$log_date : ê���ι����ե����� {$file_orign} ������ޤ���\n");
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');
// echo $query . "\n";  // debug
fclose($fpa);      ////// �����ѥ�����߽�λ

header('Location: ' . H_WEB_HOST . ACT . 'inventory/inventory_month_view.php');   // �����å��ꥹ�Ȥ�
// header('Location: http://masterst.tnk.co.jp/account/inventory_month_view.php');
exit();
?>
