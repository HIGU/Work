<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-4.3.4-cgi -q �� php-4.3.7-cgi -q                    //
// ����ǡ��� ��ưFTP Download  �ٵ빹������ե����� UKWLIB/W#MIPROV        //
//                                  AS/400 ----> Web Server (PHP)           //
// Copyright(C) 2003-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// �ѹ�����                                                                 //
// 2003/11/19 �������� act_miprov_get_ftp.php                               //
//            http �� cli�Ǥ��ѹ������褦�� requier_once �����л����     //
// 2003/11/20 uniqe key ��̵������ �������ǥ����å����� insert�Τߤ��ѹ�    //
// 2004/01/05 mb_convert_encoding�� 'auto'��'SJIS' ���ѹ� $rec++�ΰ����ѹ�  //
// 2004/02/06 ������(�׾���)����������Ʊ���������å����ɲ�                  //
// 2004/04/05 header('Location: http:' . WEB_HOST . ACT -->                 //
//                                  header('Location: ' . H_WEB_HOST . ACT  //
// 2004/06/07 php-4.3.6-cgi -q �� php-4.3.7-cgi -q  �С�����󥢥å�        //
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

///// ����������μ���
if (isset($_SESSION['act_ymd'])) {
    $yyyymmdd = $_SESSION['act_ymd'];
} else {
    $_SESSION['s_sysmsg'] = "��������������ꤵ��Ƥ��ޤ���";
    header('Location: ' . $act_referer);
    exit();
}

/////////// Download file AS/400 & Save file
$down_file = 'UKWLIB/W#MIPROV';
$save_file = 'W#MIPROV.TXT';

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
    $_SESSION['s_sysmsg'] .= 'ftp_connect() error --> �ٵ�ɼ �������<br>';
    fwrite($fpa,"$log_date ftp_connect() error --> �ٵ�ɼ �������\n");
}

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    $_SESSION['s_sysmsg'] .= 'db_connect() error';
    fwrite($fpa, "$log_date db_connect() error \n");
    exit();
}

// �ٵ�ɼ ������� ������� �������
$file_orign  = 'W#MIPROV.TXT';
$file_backup = 'backup/W#MIPROV-BAK.TXT';
$file_test   = 'debug/debug-MIPROV.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    ///////// �����������Ͽ�ѤߤΥ����å�
    $data = fgetcsv($fp, 300, '_');     // �¥쥳���ɤ�187�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���'_'�����
    $query_chk = sprintf("SELECT act_date FROM act_miprov WHERE act_date=%d ", $data[15]);
    if (getUniResTrs($con, $query_chk, $res_chk) > 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
        /////////// ������(������)����Ͽ�ѤߤΥ����å�
        $_SESSION['s_sysmsg'] .= sprintf("�ٵ�ɼ�ϴ��˳���ѤߤǤ���<br>��������%s", format_date($data[15]) );
        fwrite($fpa, "$log_date �ٵ�ɼ�ϴ��˳���ѤߤǤ���������{$data[15]}\n");
        query_affected_trans($con, "rollback");     // transaction rollback
        header('Location: ' . $act_referer);
        exit();
    }
    fclose($fp);
    $fp = fopen($file_orign, 'r');
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 300, '_');     // �¥쥳���ɤ�187�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���'_'�����
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num != 23) {
            $_SESSION['s_sysmsg'] .= "field not 23 record=$rec <br>";
            fwrite($fpa, "$log_date field not 23 record=$rec \n");
            continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        }
        ///// �������Υ����å�
        if ($data[15] != $yyyymmdd) {
            $_SESSION['s_sysmsg'] .= sprintf("�ǡ�����������������㤤�ޤ���<br>��������%s", format_date($data[15]) );
            fwrite($fpa, "$log_date �ǡ�����������������㤤�ޤ���������{$data[15]}\n");
            query_affected_trans($con, "rollback");     // transaction rollback
            header('Location: ' . $act_referer);
            exit();
        }
        
        ///////// �祭�������� �������ΤߤΥ����å��Ȥ��� Unique key ��̵������
        // $query_chk = sprintf("SELECT act_date FROM act_miprov WHERE act_date=%d and _sei_no='%s'
        //                       and parts_no='%s' and prov_no='%s'", $data[15], $data[6], $data[7], $data[2]);
        // if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
        ///// ��Ͽ�ʤ� insert ����
        $query = "INSERT INTO act_miprov (mpvpn, mpvpd, prov_no, prov_date, div, vendor,
                  _sei_no, parts_no, sozai, mecin, mtl_cond, require, prov, prov_tan,
                  delete, act_date, parts_name, note, zai_kubun, kamoku, r_kubun, gai_price, tax_kubun)
                  VALUES(
                  '{$data[0]}',
                   {$data[1]} ,
                  '{$data[2]}',
                   {$data[3]} ,
                  '{$data[4]}',
                  '{$data[5]}',
                  '{$data[6]}',
                  '{$data[7]}',
                  '{$data[8]}',
                  '{$data[9]}',
                  '{$data[10]}',
                   {$data[11]} ,
                   {$data[12]} ,
                   {$data[13]} ,
                  '{$data[14]}',
                   {$data[15]} ,
                  '{$data[16]}',
                  '{$data[17]}',
                  '{$data[18]}',
                   {$data[19]} ,
                  '{$data[20]}',
                   {$data[21]} ,
                  '{$data[22]}' )";
        if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
            $_SESSION['s_sysmsg'] .= "{$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!<br>";
            fwrite($fpa, "$log_date �ײ��ֹ�:{$data[6]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
            /***********************
        } else {
            ///// ��Ͽ���� update ����
            $query = "UPDATE act_miprov SET mpvpn='{$data[0]}', mpvpd={$data[1]}, prov_no='{$data[2]}',
                      prov_date={$data[3]}, div='{$data[4]}', vendor='{$data[5]}', _sei_no='{$data[6]}',
                      parts_no='{$data[7]}', sozai='{$data[8]}', mecin='{$data[9]}', mtl_cond='{$data[10]}',
                      require={$data[11]}, prov={$data[12]}, prov_tan={$data[13]},
                      delete='{$data[14]}', act_date={$data[15]}, parts_name='{$data[16]}', note='{$data[17]}',
                      zai_kubun='{$data[18]}', kamoku={$data[19]}, r_kubun='{$data[20]}',
                      gai_price={$data[21]}, tax_kubun='{$data[22]}'
                where act_date={$data[15]} and _sei_no='{$data[6]}' and parts_no='{$data[7]}' and prov_no='{$data[2]}'";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                $_SESSION['s_sysmsg'] .= "{$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n";
                fwrite($fpa, "$log_date �ײ��ֹ�:{$data[6]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
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
            **************************/
    }
    fclose($fp);
    fclose($fpw);       // debug
    $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$rec_ok}/{$rec} ����Ͽ���ޤ�����</font><br><br>";
    $_SESSION['s_sysmsg'] .= "<font color='white'>{$ins_ok}/{$rec} �� �ɲ�<br>";
    $_SESSION['s_sysmsg'] .= "{$upd_ok}/{$rec} �� �ѹ�</font>";
    fwrite($fpa, "$log_date �ٵ�ɼ�ι���:{$data[2]} : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date �ٵ�ɼ�ι���:{$data[2]} : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date �ٵ�ɼ�ι���:{$data[2]} : {$upd_ok}/{$rec} �� �ѹ� \n");
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
    $_SESSION['s_sysmsg'] .= "�ٵ�ɼ�ե����� {$file_orign} ������ޤ���!";
    fwrite($fpa,"$log_date �ե�����$file_orign ������ޤ���!\n");
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, "commit");
// echo $query . "\n";  // debug
fclose($fpa);      ////// �����ѥ�����߽�λ

header('Location: ' . H_WEB_HOST . ACT . 'act_miprov_view.php');   // �����å��ꥹ�Ȥ�
// header('Location: http://masterst.tnk.co.jp/account/act_miprov_view.php');
// header('Location: ' . $act_referer);
exit();
?>
