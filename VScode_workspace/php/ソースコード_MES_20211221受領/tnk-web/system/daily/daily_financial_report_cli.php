#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-5.0.2-cli                                           //
// �軻��׻��Ѵ���ǡ�������(daily)����                                    //
// AS/400 UKWLIB/W#KESKISHU                                                 //
// AS/400 UKWLIB/W#KESGEKEI                                                 //
//   AS/400 ----> Web Server (PHP) PCIX��FTPž���Ѥ�ʪ�򹹿�����            //
// Copyright(C) 2017-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// \FTPTNK USER(AS400) ASFILE(W#KESKISHU) LIB(UKWLIB)                       //
//         PCFILE(W#KESKISHU.TXT) MODE(TXT)                                 //
// Changed history                                                          //
// 2018/06/14 �������� daily_financial_report_cli.php                       //
// 2018/07/05 ��ץǡ����μ������ɲ�                                        //
// 2018/10/17 ��ԥ����ɤ��ʤ��Ȥ��ޤ����פǤ��ʤ��Τ��ɲ�                  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��cli��)
// ini_set('display_errors','1');              // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');        ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤǥ����ץ�
fwrite($fpb, "�軻��׻��Ѵ���ǡ����ι���\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/daily_financial_report_cli.php\n");

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date �軻��׻��Ѵ���ǡ��� db_connect() error \n");
    fwrite($fpb, "$log_date �軻��׻��Ѵ���ǡ��� db_connect() error \n");
    echo "$log_date �軻��׻��Ѵ���ǡ��� db_connect() error \n\n";
    exit();
}
///////// ���� A/C ͭ���ٵ�ե�����ι��� �������
$file_orign  = '/home/guest/daily/W#KESKISHU.TXT';
$file_backup = '/home/guest/daily/backup/W#KESKISHU-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-KESKISHU.TXT';
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
                fwrite($fpa, "$log_date �軻��׻��Ѵ���ǡ���:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �軻��׻��Ѵ���ǡ���:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
                fwrite($fpa, "$log_date �軻��׻��Ѵ���ǡ���:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �軻��׻��Ѵ���ǡ���:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
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
    fwrite($fpa, "$log_date �軻��׻��Ѵ���ǡ��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date �軻��׻��Ѵ���ǡ��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date �軻��׻��Ѵ���ǡ��� : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpb, "$log_date �軻��׻��Ѵ���ǡ��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date �軻��׻��Ѵ���ǡ��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpb, "$log_date �軻��׻��Ѵ���ǡ��� : {$upd_ok}/{$rec} �� �ѹ� \n");
    echo "$log_date �軻��׻��Ѵ���ǡ��� : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date �軻��׻��Ѵ���ǡ��� : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date �軻��׻��Ѵ���ǡ��� : {$upd_ok}/{$rec} �� �ѹ� \n";
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
    fwrite($fpa, "$log_date : �軻��׻��Ѵ���ǡ����ι����ե����� {$file_orign} ������ޤ���\n");
    fwrite($fpb, "$log_date : �軻��׻��Ѵ���ǡ����ι����ե����� {$file_orign} ������ޤ���\n");
    echo "$log_date : �軻��׻��Ѵ���ǡ����ե����� {$file_orign} ������ޤ���\n";
}

///////// ���� A/C ͭ���ٵ�ե�����ι��� �������
$file_orign  = '/home/guest/daily/W#KESGEKEI.TXT';
$file_backup = '/home/guest/daily/backup/W#KESGEKEI-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-KESGEKEI.TXT';
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
        if ($num != 6) {           // �ե�����ɿ��Υ����å�
            fwrite($fpa, "$log_date field not 5 record=$rec \n");
            fwrite($fpb, "$log_date field not 5 record=$rec \n");
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
        
        $query_chk = sprintf("SELECT * FROM financial_report_month WHERE rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $data[2], $data[0], $data[1], $data[5]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "INSERT INTO financial_report_month (rep_summary1, rep_summary2, rep_ymd, rep_de, rep_cr, rep_gin)
                      VALUES(
                      '{$data[0]}',
                      '{$data[1]}',
                       {$data[2]} ,
                       {$data[3]} ,
                       {$data[4]} ,
                      '{$data[5]}'
                       )";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �軻��׻��ѷ��ɽ�ǡ���:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �軻��׻��ѷ��ɽ�ǡ���:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
            $query = "UPDATE financial_report_month SET
                            rep_de       = {$data[3]} ,
                            rep_cr       = {$data[4]}
                      where rep_summary1='{$data[0]}' and rep_summary2='{$data[1]}' and rep_ymd={$data[2]} and rep_gin='{$data[5]}'";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �軻��׻��ѷ��ɽ�ǡ���:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �軻��׻��ѷ��ɽ�ǡ���:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
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
    fwrite($fpa, "$log_date �軻��׻��ѷ��ɽ�ǡ��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date �軻��׻��ѷ��ɽ�ǡ��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date �軻��׻��ѷ��ɽ�ǡ��� : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpb, "$log_date �軻��׻��ѷ��ɽ�ǡ��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date �軻��׻��ѷ��ɽ�ǡ��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpb, "$log_date �軻��׻��ѷ��ɽ�ǡ��� : {$upd_ok}/{$rec} �� �ѹ� \n");
    echo "$log_date �軻��׻��ѷ��ɽ�ǡ��� : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date �軻��׻��ѷ��ɽ�ǡ��� : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date �軻��׻��ѷ��ɽ�ǡ��� : {$upd_ok}/{$rec} �� �ѹ� \n";
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
    fwrite($fpa, "$log_date : �軻��׻��ѷ��ɽ�ǡ����ι����ե����� {$file_orign} ������ޤ���\n");
    fwrite($fpb, "$log_date : �軻��׻��ѷ��ɽ�ǡ����ι����ե����� {$file_orign} ������ޤ���\n");
    echo "$log_date : �軻��׻��ѷ��ɽ�ǡ����ե����� {$file_orign} ������ޤ���\n";
}

/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');
fclose($fpa);      ////// �����ѥ�����߽�λ
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߽�λ
?>
