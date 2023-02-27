#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-5.0.2-cli                                           //
// ̤ʧ��׾�����ۥǡ�������(daily)����                                    //
// AS/400 UKWLIB/W#SYOMIHA1                                                 //
// AS/400 UKWLIB/W#SYOMIHA2                                                 //
//   AS/400 ----> Web Server (PHP) PCIX��FTPž���Ѥ�ʪ�򹹿�����            //
// Copyright(C) 2017-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// \FTPTNK USER(AS400) ASFILE(W#SYOMIHA1) LIB(UKWLIB)                       //
//        PCFILE(W#SYOMIHA1.TXT) MODE(TXT)                                  //
// Changed history                                                          //
// 2021/04/22 �������� daily_sales_tax_miharai_cli.php                      //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��cli��)
// ini_set('display_errors','1');              // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');        ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤǥ����ץ�
fwrite($fpb, "̤ʧ��׾�����ۥǡ����ι���\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/daily_sales_tax_miharai_cli.php\n");

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date ������̤ʧ���ʧ���٥ǡ��� db_connect() error \n");
    fwrite($fpb, "$log_date ������̤ʧ���ʧ���٥ǡ��� db_connect() error \n");
    echo "$log_date ������̤ʧ���ʧ���٥ǡ��� db_connect() error \n\n";
    exit();
}
///////// ������̤ʧ���ʧ���٤ι��� �������
$file_orign  = '/home/guest/daily/W#SYOMIHA1.TXT';
$file_backup = '/home/guest/daily/backup/W#SYOMIHA1-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-SYOMIHA1.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug �ѥե�����Υ����ץ�
    $chk_cnt = 0;       // �������
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 300, '|');     // �¥쥳���ɤ�183�Х��� �ǥ�ߥ��� '|' �������������'_'�ϻȤ��ʤ�
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
        
        if ($chk_cnt == 0) {    // ���
            $chk_cnt = 1;
            $query_chk = sprintf("SELECT * FROM sales_tax_payment_list WHERE rep_ki=%d", $data[1]);
            if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                ///// ��Ͽ�ʤ� insert ����
                $query = "INSERT INTO  sales_tax_payment_list (rep_ymd, rep_ki, rep_buy, rep_tax, rep_ren, rep_eda, rep_tik)
                          VALUES(
                           {$data[0]} ,
                           {$data[1]} ,
                           {$data[2]} ,
                           {$data[3]} ,
                          '{$data[4]}',
                          '{$data[5]}',
                          '{$data[6]}'
                           )";
                if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                    fwrite($fpa, "$log_date ������̤ʧ���ʧ���٥ǡ���:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                    fwrite($fpb, "$log_date ������̤ʧ���ʧ���٥ǡ���:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
                ///// ��Ͽ���� DELETE���Ƥ���INSERT
                $query = sprintf("DELETE from sales_tax_payment_list WHERE rep_ki=%d", $data[1]);
                query_affected_trans($con, $query);      // �����ѥ����꡼�μ¹�
                ///// insert ����
                $query = "INSERT INTO  sales_tax_payment_list (rep_ymd, rep_ki, rep_buy, rep_tax, rep_ren, rep_eda, rep_tik)
                          VALUES(
                           {$data[0]} ,
                           {$data[1]} ,
                           {$data[2]} ,
                           {$data[3]} ,
                          '{$data[4]}',
                          '{$data[5]}',
                          '{$data[6]}'
                           )";
                if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                    fwrite($fpa, "$log_date ������̤ʧ���ʧ���٥ǡ���:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                    fwrite($fpb, "$log_date ������̤ʧ���ʧ���٥ǡ���:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
            }
        } else {
            $chk_cnt = 1;
            ///// ��Ͽ�ʤ� insert ����
            $query = "INSERT INTO  sales_tax_payment_list (rep_ymd, rep_ki, rep_buy, rep_tax, rep_ren, rep_eda, rep_tik)
                      VALUES(
                       {$data[0]} ,
                       {$data[1]} ,
                       {$data[2]} ,
                       {$data[3]} ,
                      '{$data[4]}',
                      '{$data[5]}',
                      '{$data[6]}'
                       )";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date ������̤ʧ���ʧ���٥ǡ���:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date ������̤ʧ���ʧ���٥ǡ���:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
        }
    }
    fclose($fp);
    fclose($fpw);       // debug
    fwrite($fpa, "$log_date ������̤ʧ���ʧ���٥ǡ��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date ������̤ʧ���ʧ���٥ǡ��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date ������̤ʧ���ʧ���٥ǡ��� : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpb, "$log_date ������̤ʧ���ʧ���٥ǡ��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date ������̤ʧ���ʧ���٥ǡ��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpb, "$log_date ������̤ʧ���ʧ���٥ǡ��� : {$upd_ok}/{$rec} �� �ѹ� \n");
    echo "$log_date ������̤ʧ���ʧ���٥ǡ��� : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date ������̤ʧ���ʧ���٥ǡ��� : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date ������̤ʧ���ʧ���٥ǡ��� : {$upd_ok}/{$rec} �� �ѹ� \n";
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
    fwrite($fpa, "$log_date : ������̤ʧ���ʧ���٥ǡ����ι����ե����� {$file_orign} ������ޤ���\n");
    fwrite($fpb, "$log_date : ������̤ʧ���ʧ���٥ǡ����ι����ե����� {$file_orign} ������ޤ���\n");
    echo "$log_date : ������̤ʧ���ʧ���٥ǡ����ե����� {$file_orign} ������ޤ���\n";
}

///////// �����ǳ۷׻�ɽ�ι��� �������
$file_orign  = '/home/guest/daily/W#SYOMIHA2.TXT';
$file_backup = '/home/guest/daily/backup/W#SYOMIHA2-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-SYOMIHA2.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug �ѥե�����Υ����ץ�
    $chk_cnt = 0;       // �������
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
            fwrite($fpa, "$log_date field not 6 record=$rec \n");
            fwrite($fpb, "$log_date field not 6 record=$rec \n");
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
        if ($chk_cnt == 0) {    // ���
            $chk_cnt = 1;
            $query_chk = sprintf("SELECT * FROM sales_tax_calculate_list WHERE rep_ki=%d", $data[1]);
            if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                ///// ��Ͽ�ʤ� insert ����
                $query = "INSERT INTO sales_tax_calculate_list (rep_ymd, rep_ki, rep_kubun, rep_kin, rep_code, rep_ren)
                          VALUES(
                           {$data[0]} ,
                           {$data[1]} ,
                          '{$data[2]}',
                           {$data[3]} ,
                          '{$data[4]}',
                           {$data[5]}
                           )";
                if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                    fwrite($fpa, "$log_date �����ǳ۷׻�ɽ�ǡ���:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                    fwrite($fpb, "$log_date �����ǳ۷׻�ɽ�ǡ���:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
                ///// ��Ͽ���� DELETE���Ƥ���INSERT
                $query = sprintf("DELETE from sales_tax_calculate_list WHERE rep_ki=%d", $data[1]);
                query_affected_trans($con, $query);      // �����ѥ����꡼�μ¹�
                ///// insert
                $query = "INSERT INTO sales_tax_calculate_list (rep_ymd, rep_ki, rep_kubun, rep_kin, rep_code, rep_ren)
                          VALUES(
                           {$data[0]} ,
                           {$data[1]} ,
                          '{$data[2]}',
                           {$data[3]} ,
                          '{$data[4]}',
                           {$data[5]}
                           )";
                if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                    fwrite($fpa, "$log_date �����ǳ۷׻�ɽ�ǡ���:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                    fwrite($fpb, "$log_date �����ǳ۷׻�ɽ�ǡ���:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
            }
        } else {
            $chk_cnt = 1;
            ///// ��Ͽ�ʤ� insert ����
            $query = "INSERT INTO sales_tax_calculate_list (rep_ymd, rep_ki, rep_kubun, rep_kin, rep_code, rep_ren)
                      VALUES(
                       {$data[0]} ,
                       {$data[1]} ,
                      '{$data[2]}',
                       {$data[3]} ,
                      '{$data[4]}',
                       {$data[5]}
                       )";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �����ǳ۷׻�ɽ�ǡ���:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �����ǳ۷׻�ɽ�ǡ���:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
        }
    }
    fclose($fp);
    fclose($fpw);       // debug
    fwrite($fpa, "$log_date �����ǳ۷׻�ɽ�ǡ��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date �����ǳ۷׻�ɽ�ǡ��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date �����ǳ۷׻�ɽ�ǡ��� : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpb, "$log_date �����ǳ۷׻�ɽ�ǡ��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date �����ǳ۷׻�ɽ�ǡ��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpb, "$log_date �����ǳ۷׻�ɽ�ǡ��� : {$upd_ok}/{$rec} �� �ѹ� \n");
    echo "$log_date �����ǳ۷׻�ɽ�ǡ��� : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date �����ǳ۷׻�ɽ�ǡ��� : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date �����ǳ۷׻�ɽ�ǡ��� : {$upd_ok}/{$rec} �� �ѹ� \n";
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
    fwrite($fpa, "$log_date : �����ǳ۷׻�ɽ�ǡ����ι����ե����� {$file_orign} ������ޤ���\n");
    fwrite($fpb, "$log_date : �����ǳ۷׻�ɽ�ǡ����ι����ե����� {$file_orign} ������ޤ���\n");
    echo "$log_date : �����ǳ۷׻�ɽ�ǡ����ե����� {$file_orign} ������ޤ���\n";
}

/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');
fclose($fpa);      ////// �����ѥ�����߽�λ
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߽�λ
?>
