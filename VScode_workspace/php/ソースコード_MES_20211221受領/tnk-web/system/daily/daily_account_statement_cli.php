#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-5.0.2-cli                                           //
// �軻��׻��Ѵ��������������ɽ(daily)����                                //
// AS/400 UKWLIB/W#KESSISHO                                                 //
// AS/400 UKWLIB/W#UCHIURI                                                  //
// AS/400 UKWLIB/W#UCHIKAI                                                  //
// AS/400 UKWLIB/W#UCHITAI                                                  //
//   AS/400 ----> Web Server (PHP) PCIX��FTPž���Ѥ�ʪ�򹹿�����            //
// Copyright(C) 2017-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// \FTPTNK USER(AS400) ASFILE(W#KESSISHO) LIB(UKWLIB)                       //
//         PCFILE(W#KESSISHO.TXT) MODE(TXT)                                 //
// ̤ʧ���Ϸ�������̤ʧ�ȥå�10��¹�                                   //
// Changed history                                                          //
// 2020/06/22 �������� daily_account_statement_cli.php                      //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��cli��)
// ini_set('display_errors','1');              // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');        ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤǥ����ץ�
fwrite($fpb, "�軻��׻��Ѵ��������������ɽ�ǡ����ι���\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/daily_account_statement_cli.php\n");

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ��� db_connect() error \n");
    fwrite($fpb, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ��� db_connect() error \n");
    echo "$log_date �軻��׻��Ѵ��������������ɽ�ǡ��� db_connect() error \n\n";
    exit();
}
///////// �軻��׻��Ѵ��������������ɽ��ݶ�ǡ����ι��� �������
$file_orign  = '/home/guest/daily/W#UCHIURI.TXT';
$file_backup = '/home/guest/daily/backup/W#UCHIURI-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-UCHIURI.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 200, '|');     // �¥쥳���ɤ�183�Х��� �ǥ�ߥ��� '|' �������������'_'�ϻȤ��ʤ�
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num != 7) {           // �ե�����ɿ��Υ����å�
            fwrite($fpa, "$log_date field not 7 record=$rec \n");
            fwrite($fpb, "$log_date field not 7 record=$rec \n");
            continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ� auto��NG(��ư�Ǥϥ��󥳡��ǥ��󥰤�ǧ���Ǥ��ʤ�)
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            /////// EUC-JP �إ��󥳡��ǥ��󥰤����Ⱦ�ѥ��ʤ� ���饤����Ȥ�Windows��ʤ�����ʤ��Ȥ���
            // if ($f == 3) {
            //     $data[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
            // }
        }
        
        $query_chk = sprintf("SELECT * FROM financial_report_cal WHERE rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $data[0], $data[1], $data[2], $data[3]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "INSERT INTO financial_report_cal (rep_ymd, rep_summary1, rep_summary2, rep_gin, rep_cri, rep_de, rep_cr)
                      VALUES(
                       {$data[0]} ,
                      '{$data[1]}',
                      '{$data[2]}',
                      '{$data[3]}',
                       {$data[4]} ,
                       {$data[5]} ,
                       {$data[6]}
                       )";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ���:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ���:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
            $query = "UPDATE financial_report_cal SET
                            rep_cri      = {$data[4]} ,
                            rep_de       = {$data[5]} ,
                            rep_cr       = {$data[6]}
                      where rep_ymd={$data[0]} and rep_summary1='{$data[1]}' and rep_summary2='{$data[2]}' and rep_gin='{$data[3]}'";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ���:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ���:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
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
    fwrite($fpa, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ��� : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpb, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpb, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ��� : {$upd_ok}/{$rec} �� �ѹ� \n");
    echo "$log_date �軻��׻��Ѵ��������������ɽ�ǡ��� : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date �軻��׻��Ѵ��������������ɽ�ǡ��� : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date �軻��׻��Ѵ��������������ɽ�ǡ��� : {$upd_ok}/{$rec} �� �ѹ� \n";
    if ($rec_ng == 0) {     // ����ߥ��顼���ʤ���� �ե��������
        if (file_exists($file_backup)) {
            unlink($file_backup);       // Backup �ե�����κ��
        }
        if (!rename($file_orign, $file_backup)) {
            fwrite($fpa, "$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n");
            fwrite($fpb, "$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n");
            echo "$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n";
        }
    }
} else {
    fwrite($fpa, "$log_date : �軻��׻��Ѵ��������������ɽ�ǡ����ι����ե����� {$file_orign} ������ޤ���\n");
    fwrite($fpb, "$log_date : �軻��׻��Ѵ��������������ɽ�ǡ����ι����ե����� {$file_orign} ������ޤ���\n");
    echo "$log_date : �軻��׻��Ѵ��������������ɽ�ǡ����ե����� {$file_orign} ������ޤ���\n";
}


///////// �軻��׻��Ѵ��������������ɽ��ݶ�ǡ����ι��� �������
$file_orign  = '/home/guest/daily/W#UCHIKAI.TXT';
$file_backup = '/home/guest/daily/backup/W#UCHIKAI-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-UCHIKAI.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 200, '|');     // �¥쥳���ɤ�183�Х��� �ǥ�ߥ��� '|' �������������'_'�ϻȤ��ʤ�
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        if ($rec == 11) {
            break;
        }
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num != 7) {           // �ե�����ɿ��Υ����å�
            fwrite($fpa, "$log_date field not 7 record=$rec \n");
            fwrite($fpb, "$log_date field not 7 record=$rec \n");
            continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ� auto��NG(��ư�Ǥϥ��󥳡��ǥ��󥰤�ǧ���Ǥ��ʤ�)
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            /////// EUC-JP �إ��󥳡��ǥ��󥰤����Ⱦ�ѥ��ʤ� ���饤����Ȥ�Windows��ʤ�����ʤ��Ȥ���
            // if ($f == 3) {
            //     $data[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
            // }
        }
        /*
        $yyyy = substr($data[0], 0,4);
        $mm   = substr($data[0], 4,2);
        if ($mm == '01') {
            $yyyy = ($yyyy - 1);
            $mm   = 12;
        } else {
            $mm   = $mm - 1;
            if($mm == '03') {
                $mm = '03';
            } elseif($mm == '06') {
                $mm = '06';
            } elseif($mm == '09') {
                $mm = '09';
            }
        }
        $data[0] = $yyyy . $mm;
        */
        $data[3] = $rec;
        $query_chk = sprintf("SELECT * FROM financial_report_cal WHERE rep_ymd=%d and rep_summary2='%s' and rep_gin='%s'", $data[0], $data[2], $data[3]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "INSERT INTO financial_report_cal (rep_ymd, rep_summary1, rep_summary2, rep_gin, rep_cri, rep_de, rep_cr)
                      VALUES(
                       {$data[0]} ,
                      '{$data[1]}',
                      '{$data[2]}',
                      '{$data[3]}',
                       {$data[4]} ,
                       {$data[5]} ,
                       {$data[6]}
                       )";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ���:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ���:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
            $query = "UPDATE financial_report_cal SET
                            rep_summary1 ='{$data[1]}',
                            rep_cri      = {$data[4]} ,
                            rep_de       = {$data[5]} ,
                            rep_cr       = {$data[6]}
                      where rep_ymd={$data[0]} and rep_summary2='{$data[2]}' and rep_gin='{$data[3]}'";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ���:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ���:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
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
    fwrite($fpa, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ��� : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpb, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpb, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ��� : {$upd_ok}/{$rec} �� �ѹ� \n");
    echo "$log_date �軻��׻��Ѵ��������������ɽ�ǡ��� : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date �軻��׻��Ѵ��������������ɽ�ǡ��� : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date �軻��׻��Ѵ��������������ɽ�ǡ��� : {$upd_ok}/{$rec} �� �ѹ� \n";
    if ($rec_ng == 0) {     // ����ߥ��顼���ʤ���� �ե��������
        if (file_exists($file_backup)) {
            unlink($file_backup);       // Backup �ե�����κ��
        }
        if (!rename($file_orign, $file_backup)) {
            fwrite($fpa, "$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n");
            fwrite($fpb, "$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n");
            echo "$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n";
        }
    }
} else {
    fwrite($fpa, "$log_date : �軻��׻��Ѵ��������������ɽ�ǡ����ι����ե����� {$file_orign} ������ޤ���\n");
    fwrite($fpb, "$log_date : �軻��׻��Ѵ��������������ɽ�ǡ����ι����ե����� {$file_orign} ������ޤ���\n");
    echo "$log_date : �軻��׻��Ѵ��������������ɽ�ǡ����ե����� {$file_orign} ������ޤ���\n";
}
// �٥�����ޥ������ι��� �������
$file_orign  = '/home/guest/daily/W#UCHIMIHA.TXT';
$file_temp   = 'W#UCHIMIHA-TEMP.TXT';
$file_backup = '/home/guest/daily/backup/W#UCHIMIHA-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-UCHIMIHA.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp = fopen($file_orign, 'r');
        ///////////// SJIS �� EUC �Ѵ����å� START (SJIS��EUC�ˤʤ�ʸ����NULL�Х��Ȥ��Ѵ������������)
    $fp_conv = fopen($file_temp, 'w');  // EUC ���Ѵ���
    while (!(feof($fp))) {
        $data = fgets($fp, 500);
        $data = mb_convert_encoding($data, 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
        $data = str_replace("\0", ' ', $data);                      // NULL�Х��Ȥ�SPACE���Ѵ�
        $data = mb_ereg_replace('��', '�ʳ���', $data);             // �����¸ʸ���򵬳�ʸ�����ѹ�
        $data = preg_replace("/( |��)/", "", $data);
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
        
        $data = fgetcsv($fp, 500, "_");     // �¥쥳���ɤ�150�Х��� �ǥ�ߥ��ϥ��֤��饢��������������ѹ�
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        if ($rec == 11) {
            break;
        }
        
        $num  = count($data);       // �ե�����ɿ��μ���
        /*
        if ($num < 8) {
            $rec_no = $rec;     // �ºݤΥ쥳�����ֹ� ���$rec++����褦�ˤ����Τǡ����Τޤޤǣϣ�
            // $_SESSION['s_sysmsg'] .= "field not 6&7 record=$rec_no <br>";
            fwrite($fpa, "$log_date field not 6&7 record=$rec_no \n");
                ////////////////////////////////////////// Debug start
                for ($f=0; $f<$num; $f+
        }
        */
        for ($f=0; $f<$num; $f++) {
            // $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        }
        
        $data[5] = $rec;
        $query_chk = sprintf("SELECT * FROM financial_report_cal WHERE rep_ymd=%d and rep_summary1='%s' and rep_de=%d", $data[0], $data[1], $data[5]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "INSERT INTO financial_report_cal (rep_ymd, rep_summary1, rep_summary2, rep_gin, rep_cri, rep_de, rep_cr)
                      VALUES(
                       {$data[0]} ,
                      '{$data[1]}',
                      '{$data[2]}',
                      '{$data[3]}',
                       {$data[4]} ,
                       {$data[5]} ,
                       {$data[6]}
                       )";
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
            $query = "UPDATE financial_report_cal SET
                            rep_summary2 = '{$data[2]}' ,
                            rep_cri      = {$data[4]} ,
                            rep_de       = {$data[5]} ,
                            rep_cr       = {$data[6]}
                      where rep_ymd={$data[0]} and rep_summary1='{$data[1]}' and rep_de={$data[5]}";
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
    echo "̤ʧTOP10��{$rec_ok}/{$rec} ����Ͽ���ޤ�����";
    echo "̤ʧTOP10��{$ins_ok}/{$rec} �� �ɲ�";
    echo "̤ʧTOP10��{$upd_ok}/{$rec} �� �ѹ�";
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

///////// �軻��׻��Ѵ��������������ɽ�����Ƿ׻��ǡ����ι��� �������
$file_orign  = '/home/guest/daily/W#KESSISHO.TXT';
$file_backup = '/home/guest/daily/backup/W#KESSISHO-BAK.TXT';
$file_temp   = '/home/guest/daily/W#KESSISHO-TEMP.TXT';
$file_test   = '/home/guest/daily/debug/debug-W#KESSISHO.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug �ѥե�����Υ����ץ�
    $fp_conv = fopen($file_temp, 'w');  // EUC ���Ѵ���
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 500, '|');     // �¥쥳���ɤ�183�Х��� �ǥ�ߥ��� '|' �������������'_'�ϻȤ��ʤ�
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        if ($rec == 11) {
            break;
        }
        
        $num  = count($data);       // �ե�����ɿ��μ���
            
        if ($num != 7) {           // �ե�����ɿ��Υ����å�
            //echo "$log_date �ƥ���$rec\n";
            echo "$log_date �ƥ���$data[2]\n";
            fwrite($fpa, "$log_date field not 7 record=$rec \n");
            fwrite($fpb, "$log_date field not 7 record=$rec \n");
            continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ� auto��NG(��ư�Ǥϥ��󥳡��ǥ��󥰤�ǧ���Ǥ��ʤ�)
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            $data[$f] = str_replace("\0", ' ', $data[$f]);                      // NULL�Х��Ȥ�SPACE���Ѵ�
            /////// EUC-JP �إ��󥳡��ǥ��󥰤����Ⱦ�ѥ��ʤ� ���饤����Ȥ�Windows��ʤ�����ʤ��Ȥ���
            // if ($f == 3) {
            //     $data[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
            // }
        }
        if ($data[1]=='') {
            $data[1] = $data[3];
            $data[2] = $data[4];
        }
        $data[3] = $data[6];
        $data[4] = $data[5];
        $data[5] = 0;
        $data[6] = 0;
        
        $query_chk = sprintf("SELECT * FROM financial_report_cal WHERE rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $data[0], $data[1], $data[2], $data[3]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "INSERT INTO financial_report_cal (rep_ymd, rep_summary1, rep_summary2, rep_gin, rep_cri, rep_de, rep_cr)
                      VALUES(
                       {$data[0]} ,
                      '{$data[1]}',
                      '{$data[2]}',
                      '{$data[3]}',
                       {$data[4]} ,
                       {$data[5]} ,
                       {$data[6]}
                       )";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ���:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ���:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
            $query = "UPDATE financial_report_cal SET
                            rep_cri      = {$data[4]} ,
                            rep_de       = {$data[5]} ,
                            rep_cr       = {$data[6]}
                      where rep_ymd={$data[0]} and rep_summary1='{$data[1]}' and rep_summary2='{$data[2]}' and rep_gin='{$data[3]}'";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ���:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ���:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
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
    fwrite($fpa, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ��� : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpb, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpb, "$log_date �軻��׻��Ѵ��������������ɽ�ǡ��� : {$upd_ok}/{$rec} �� �ѹ� \n");
    echo "$log_date �軻��׻��Ѵ��������������ɽ�ǡ��� : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date �軻��׻��Ѵ��������������ɽ�ǡ��� : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date �軻��׻��Ѵ��������������ɽ�ǡ��� : {$upd_ok}/{$rec} �� �ѹ� \n";
    if ($rec_ng == 0) {     // ����ߥ��顼���ʤ���� �ե��������
        if (file_exists($file_backup)) {
            unlink($file_backup);       // Backup �ե�����κ��
        }
        if (!rename($file_orign, $file_backup)) {
            fwrite($fpa, "$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n");
            fwrite($fpb, "$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n");
            echo "$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n";
        }
    }
} else {
    fwrite($fpa, "$log_date : �軻��׻��Ѵ��������������ɽ�ǡ����ι����ե����� {$file_orign} ������ޤ���\n");
    fwrite($fpb, "$log_date : �軻��׻��Ѵ��������������ɽ�ǡ����ι����ե����� {$file_orign} ������ޤ���\n");
    echo "$log_date : �軻��׻��Ѵ��������������ɽ�ǡ����ե����� {$file_orign} ������ޤ���\n";
}

///////// �軻��׻��Ѵ��������������ɽ�࿦���հ����ǡ����ι��� �������
$file_orign  = '/home/guest/daily/W#UCHITAI.TXT';
$file_backup = '/home/guest/daily/backup/W#UCHITAI-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-UCHITAI.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 200, '|');     // �¥쥳���ɤ�183�Х��� �ǥ�ߥ��� '|' �������������'_'�ϻȤ��ʤ�
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num != 7) {           // �ե�����ɿ��Υ����å�
            fwrite($fpa, "$log_date field not 7 record=$rec \n");
            fwrite($fpb, "$log_date field not 7 record=$rec \n");
            continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ� auto��NG(��ư�Ǥϥ��󥳡��ǥ��󥰤�ǧ���Ǥ��ʤ�)
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            /////// EUC-JP �إ��󥳡��ǥ��󥰤����Ⱦ�ѥ��ʤ� ���饤����Ȥ�Windows��ʤ�����ʤ��Ȥ���
            // if ($f == 3) {
            //     $data[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
            // }
        }
        
        $query_chk = sprintf("SELECT * FROM financial_report_cal WHERE rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s' and rep_de=%d", $data[0], $data[1], $data[2], $data[3], $data[5]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "INSERT INTO financial_report_cal (rep_ymd, rep_summary1, rep_summary2, rep_gin, rep_cri, rep_de, rep_cr)
                      VALUES(
                       {$data[0]} ,
                      '{$data[1]}',
                      '{$data[2]}',
                      '{$data[3]}',
                       {$data[4]} ,
                       {$data[5]} ,
                       {$data[6]}
                       )";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �軻��׻��Ѵ��������������ɽ�࿦���հ����ǡ���:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �軻��׻��Ѵ��������������ɽ�࿦���հ����ǡ���:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
            $query = "UPDATE financial_report_cal SET
                            rep_cri      = {$data[4]} ,
                            rep_cr       = {$data[6]}
                      where rep_ymd={$data[0]} and rep_summary1='{$data[1]}' and rep_summary2='{$data[2]}' and rep_gin='{$data[3]}' and rep_de={$data[5]}";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �軻��׻��Ѵ��������������ɽ�࿦���հ����ǡ���:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �軻��׻��Ѵ��������������ɽ�࿦���հ����ǡ���:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
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
    fwrite($fpa, "$log_date �軻��׻��Ѵ��������������ɽ�࿦���հ����ǡ��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date �軻��׻��Ѵ��������������ɽ�࿦���հ����ǡ��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date �軻��׻��Ѵ��������������ɽ�࿦���հ����ǡ��� : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpb, "$log_date �軻��׻��Ѵ��������������ɽ�࿦���հ����ǡ��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date �軻��׻��Ѵ��������������ɽ�࿦���հ����ǡ��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpb, "$log_date �軻��׻��Ѵ��������������ɽ�࿦���հ����ǡ��� : {$upd_ok}/{$rec} �� �ѹ� \n");
    echo "$log_date �軻��׻��Ѵ��������������ɽ�࿦���հ����ǡ��� : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date �軻��׻��Ѵ��������������ɽ�࿦���հ����ǡ��� : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date �軻��׻��Ѵ��������������ɽ�࿦���հ����ǡ��� : {$upd_ok}/{$rec} �� �ѹ� \n";
    if ($rec_ng == 0) {     // ����ߥ��顼���ʤ���� �ե��������
        if (file_exists($file_backup)) {
            unlink($file_backup);       // Backup �ե�����κ��
        }
        if (!rename($file_orign, $file_backup)) {
            fwrite($fpa, "$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n");
            fwrite($fpb, "$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n");
            echo "$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n";
        }
    }
} else {
    fwrite($fpa, "$log_date : �軻��׻��Ѵ��������������ɽ�࿦���հ����ǡ����ι����ե����� {$file_orign} ������ޤ���\n");
    fwrite($fpb, "$log_date : �軻��׻��Ѵ��������������ɽ�࿦���հ����ǡ����ι����ե����� {$file_orign} ������ޤ���\n");
    echo "$log_date : �軻��׻��Ѵ���������������࿦���հ���ɽ�ǡ����ե����� {$file_orign} ������ޤ���\n";
}

/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');
fclose($fpa);      ////// �����ѥ�����߽�λ
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߽�λ
?>
