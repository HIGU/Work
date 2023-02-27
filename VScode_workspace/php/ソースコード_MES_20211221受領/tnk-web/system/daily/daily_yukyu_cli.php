#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-5.0.2-cli                                           //
// ͭ�������������(daily)����   AS/400 UKWLIB/W#HPKYDY                     //
// RUNQRY     QRY(UKPLIB/Q#HPKYDY)                                          //
// \FTPTNK    USER(AS400) ASFILE(W#HPKYDY) LIB(UKWLIB)                      //
//            PCFILE(W#HPKYDY.TXT) MODE(TXT)                                //
//   AS/400 ----> Web Server (PHP) PCIX��FTPž���Ѥ�ʪ�򹹿�����            //
// Copyright(C) 2015-2015 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2015/01/29 �������� aden_daily_cli.php���¤ daily_yukyu_cli.php         //
// 2015/02/18 daily_cli.php���Ȥ߹���١�AS/400���ޥ�������ɲ�             //
// 2015/03/16 �ѡ��ȡ��Ұ���ϫƯ�����ɲ�                                    //
// 2018/09/25 �Ұ��ζ�̳���֤�AS���ɲä��줿��ե�����ɿ���14�ˤ���        //
//            data[12]��9���ä���data[13]��data[11]�������褦�ˤ���       //
//            �����ѤʤΤ�2018/09/25�Ǹ������ƥ����Ȳ��                  //
// 2018/11/02 ����Ұ��ζ�̳���֤�data[11]���Ȥ߹�����ΤǾ嵭�����Ȳ��  //
//            ������                                                        //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��cli��)
// ini_set('display_errors','1');              // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');        ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤǥ����ץ�
fwrite($fpb, "ͭ���������ι���\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/daily_yukyu_cli.php\n");

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date ͭ��ι��� db_connect() error \n");
    fwrite($fpb, "$log_date ͭ��ι��� db_connect() error \n");
    echo "$log_date ͭ��ι��� db_connect() error \n\n";
    exit();
}
///////// ͭ�����ե�����ι��� �������
$file_orign  = '/home/guest/daily/W#HPKYDY.TXT';
$file_backup = '/home/guest/daily/backup/W#HPKYDY-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-HPKYDY.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 300, '_', '`');     // �¥쥳���ɤ�75�Х��� �ǥ�ߥ��� '_'������������� field�Ϥ��Ҥ�'`'�Хå���������
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        
        /* 2018/09/25 ��ǥ����Ȳ�� ������13�����򥳥��Ȳ� �� ����
        if ($num != 14) {           // �ե�����ɿ��Υ����å�
            fwrite($fpa, "$log_date field not 14 record=$rec \n");
            fwrite($fpb, "$log_date field not 14 record=$rec \n");
            continue;
        }
        */
        
        if ($num != 13) {           // �ե�����ɿ��Υ����å�
            fwrite($fpa, "$log_date field not 13 record=$rec \n");
            fwrite($fpb, "$log_date field not 13 record=$rec \n");
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
        $data[11] = $data[11] * 1;
        $data[12] = $data[12] * 1;
        
        /* 2018/09/25 ��ǥ����Ȳ�� �� ����
        $data[13] = $data[13] * 1;
        if ($data[12] == 9) {
            $data[11] = $data[13];
        }
        */
        
        $query_chk = sprintf("SELECT uid FROM paid_holiday_master WHERE uid='%s' and ki=%d", $data[0], $data[1]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "INSERT INTO paid_holiday_master (uid, ki, before_day, current_day, day_holiday, half_holiday,
                      time_holiday, total_holiday, update_ym, str_ymd, end_ymd, work_time_p, work_time_s)
                      VALUES(
                      '{$data[0]}',
                       {$data[1]} ,
                      '{$data[2]}',
                      '{$data[3]}',
                      '{$data[4]}',
                      '{$data[5]}',
                      '{$data[6]}',
                      '{$data[7]}',
                       {$data[8]} ,
                       {$data[9]} ,
                       {$data[10]} ,
                       {$data[11]} ,
                       {$data[12]})";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �Ұ��ֹ�:{$data[0]} : ��:{$data[1]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �����ֹ�:{$data[0]} : ��:{$data[1]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
            $query = "UPDATE paid_holiday_master SET
                            uid           ='{$data[0]}',
                            ki            = {$data[1]} ,
                            before_day    ='{$data[2]}',
                            current_day   ='{$data[3]}',
                            day_holiday   ='{$data[4]}',
                            half_holiday  = {$data[5]} ,
                            time_holiday  = {$data[6]} ,
                            total_holiday ='{$data[7]}',
                            update_ym     = {$data[8]} ,
                            str_ymd       = {$data[9]} ,
                            end_ymd       = {$data[10]},
                            work_time_p   = {$data[11]},
                            work_time_s   = {$data[12]}
                      where uid='{$data[0]}' and ki={$data[1]}";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �Ұ��ֹ�:{$data[0]} : ��:{$data[1]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �Ұ��ֹ�:{$data[0]} : ��:{$data[1]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
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
    fwrite($fpa, "$log_date ͭ��ι��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date ͭ��ι��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date ͭ��ι��� : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpb, "$log_date ͭ��ι��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date ͭ��ι��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpb, "$log_date ͭ��ι��� : {$upd_ok}/{$rec} �� �ѹ� \n");
    echo "$log_date ͭ��ι��� : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date ͭ��ι��� : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date ͭ��ι��� : {$upd_ok}/{$rec} �� �ѹ� \n";
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
    fwrite($fpa, "$log_date : ͭ�����ι����ե����� {$file_orign} ������ޤ���\n");
    fwrite($fpb, "$log_date : ͭ�����ι����ե����� {$file_orign} ������ޤ���\n");
    echo "$log_date : ͭ�����ι����ե����� {$file_orign} ������ޤ���\n";
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');
fclose($fpa);      ////// �����ѥ�����߽�λ
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߽�λ
?>
