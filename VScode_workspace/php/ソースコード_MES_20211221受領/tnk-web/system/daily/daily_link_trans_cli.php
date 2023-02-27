#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-5.0.2-cli                                           //
// Ϣ����Ϣ��ɽ����(daily)����                                            //
// AS/400 UKWLIB/W#RENURIKA����ݶ����                                     //
//        UKWLIB/W#RKAIOUNK����ݶ��껦��� NKIT �ʳ�                       //
//        UKWLIB/W#RENKEINK�������Ϣ NK                                    //
//        UKWLIB/W#RENKEISK�������Ϣ SNK                                   //
//        UKWLIB/W#RENKEIMT�������Ϣ MT                                    //
//        UKWLIB/W#RENKEIIT�������Ϣ NKIT                                  //
//   AS/400 ----> Web Server (PHP) PCIX��FTPž���Ѥ�ʪ�򹹿�����            //
// Copyright(C) 2017-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// \FTPTNK USER(AS400) ASFILE(W#RENURIKA) LIB(UKWLIB)                       //
//         PCFILE(W#RENURIKA.TXT) MODE(TXT)                                 //
// Changed history                                                          //
// 2017/10/12 �������� daily_link_trans_cli.php                             //
// 2017/10/18 �����ߤޤ�PGM��λ                                           //
// �Ĥ�ϡ���ݶ⡦����¾������ݶ�ʤɤ����˷׻����뤳��               //
// �����߻��˷׻��򤷤ơ��Ȳ���̤Ǥϼ�����������ˤ��Ƥ���               //
// 2018/10/29 $del_fg�����λ��������ܤΥǡ����������Ƥ��ޤ��١�����     //
// 2019/02/05 ����Τߤη׻���˺��Ƽ�����Ǥ��ʤ��ä��ΤǼ�ư�����դ�    //
//            �Ѥ��ƶ���Ū�˷׻�                                            //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��cli��)
// ini_set('display_errors','1');              // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');        ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤǥ����ץ�
fwrite($fpb, "��ݶ����ι���\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/daily_link_trans_cli.php\n");

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date ��ݶ���� db_connect() error \n");
    fwrite($fpb, "$log_date ��ݶ���� db_connect() error \n");
    echo "$log_date ��ݶ���� db_connect() error \n\n";
    exit();
}

///////// ��ݶ����ե�����ι��� �������
$file_orign  = '/home/guest/daily/W#RENURIKA.TXT';
$file_backup = '/home/guest/daily/backup/W#RENURIKA-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-RENURIKA.TXT';
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
        if ($num != 10) {           // �ե�����ɿ��Υ����å�
            fwrite($fpa, "$log_date field not 14 record=$rec \n");
            fwrite($fpb, "$log_date field not 14 record=$rec \n");
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
        
        $query_chk = sprintf("SELECT * FROM link_trans_sales WHERE sales_code='%s' and sales_ym=%d", $data[0], $data[1]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "INSERT INTO link_trans_sales (sales_code, sales_ym, sales_kuri, sales_kei, sales_kai, sales_zan, sales_tou, sales_syo, sales_cho, sales_chozei)
                      VALUES(
                      '{$data[0]}',
                       {$data[1]} ,
                       {$data[2]} ,
                       {$data[3]} ,
                       {$data[4]} ,
                       {$data[5]} ,
                       {$data[6]} ,
                       {$data[7]} ,
                       {$data[8]} ,
                       {$data[9]})";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date ��ݶ����:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date ��ݶ����:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
            $query = "UPDATE link_trans_sales SET
                            sales_code   ='{$data[0]}',
                            sales_ym     = {$data[1]} ,
                            sales_kuri   = {$data[2]} ,
                            sales_kei    = {$data[3]} ,
                            sales_kai    = {$data[4]} ,
                            sales_zan    = {$data[5]} ,
                            sales_tou    = {$data[6]} ,
                            sales_syo    = {$data[7]} ,
                            sales_cho    = {$data[8]} ,
                            sales_chozei = {$data[9]}
                      where sales_code='{$data[0]}' and sales_ym={$data[1]}";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date ��ݶ����:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date ��ݶ����:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
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
    fwrite($fpa, "$log_date ��ݶ���� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date ��ݶ���� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date ��ݶ���� : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpb, "$log_date ��ݶ���� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date ��ݶ���� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpb, "$log_date ��ݶ���� : {$upd_ok}/{$rec} �� �ѹ� \n");
    echo "$log_date ��ݶ���� : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date ��ݶ���� : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date ��ݶ���� : {$upd_ok}/{$rec} �� �ѹ� \n";
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
    fwrite($fpa, "$log_date : ��ݶ����ι����ե����� {$file_orign} ������ޤ���\n");
    fwrite($fpb, "$log_date : ��ݶ����ι����ե����� {$file_orign} ������ޤ���\n");
    echo "$log_date : ��ݶ����ι����ե����� {$file_orign} ������ޤ���\n";
}

///////// ��ݶ��껦��� NKIT �ʳ��ե�����ι��� �������
$file_orign  = '/home/guest/daily/W#RKAIOUNK.TXT';
$file_backup = '/home/guest/daily/backup/W#RKAIOUNK-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-RKAIOUNK.TXT';
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
        if ($num != 14) {           // �ե�����ɿ��Υ����å�
            fwrite($fpa, "$log_date field not 14 record=$rec \n");
            fwrite($fpb, "$log_date field not 14 record=$rec \n");
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
        
        $query_chk = sprintf("SELECT * FROM link_trans_offset WHERE den_ymd=%d and den_no=%d and den_eda=%d and den_gyo=%d and den_loan='%s' and den_account='%s' and den_break='%s' and den_money=%f and den_summary1='%s' and den_summary2='%s' and den_id='%s' and den_iymd=%d and den_ki=%d and den_code='%s'", $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[9], $data[10], $data[11], $data[12], $data[13]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "INSERT INTO link_trans_offset (den_ymd, den_no, den_eda, den_gyo, den_loan, den_account, den_break, den_money, den_summary1, den_summary2, den_id, den_iymd, den_ki, den_code, den_kin)
                      VALUES(
                       {$data[0]} ,
                       {$data[1]} ,
                       {$data[2]} ,
                       {$data[3]} ,
                      '{$data[4]}',
                      '{$data[5]}',
                      '{$data[6]}',
                       {$data[7]} ,
                      '{$data[8]}',
                      '{$data[9]}',
                      '{$data[10]}',
                       {$data[11]} ,
                       {$data[12]} ,
                      '{$data[13]}',
                      0)";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date ��ݶ��껦��� NKIT �ʳ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date ��ݶ��껦��� NKIT �ʳ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
            $query = "UPDATE link_trans_offset SET
                            den_ymd      = {$data[0]} ,
                            den_no       = {$data[1]} ,
                            den_eda      = {$data[2]} ,
                            den_gyo      = {$data[3]} ,
                            den_loan     ='{$data[4]}',
                            den_account  ='{$data[5]}',
                            den_break    ='{$data[6]}',
                            den_money    = {$data[7]} ,
                            den_summary1 ='{$data[8]}',
                            den_summary2 ='{$data[9]}',
                            den_id       ='{$data[10]}',
                            den_iymd     = {$data[11]} ,
                            den_ki       = {$data[12]} ,
                            den_code     ='{$data[13]}'
                      where den_ymd={$data[0]} and den_no={$data[1]} and den_eda={$data[2]} and den_gyo={$data[3]} and den_loan='{$data[4]}' and den_account='{$data[5]}' and den_break='{$data[6]}' and den_money={$data[7]} and den_summary1='{$data[8]}' and den_summary2='{$data[9]}' and den_id='{$data[10]}' and den_iymd={$data[11]} and den_ki={$data[12]} and den_code='{$data[13]}'";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date ��ݶ��껦��� NKIT �ʳ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date ��ݶ��껦��� NKIT �ʳ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
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
    fwrite($fpa, "$log_date ��ݶ��껦��� NKIT �ʳ� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date ��ݶ��껦��� NKIT �ʳ� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date ��ݶ��껦��� NKIT �ʳ� : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpb, "$log_date ��ݶ��껦��� NKIT �ʳ� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date ��ݶ��껦��� NKIT �ʳ� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpb, "$log_date ��ݶ��껦��� NKIT �ʳ� : {$upd_ok}/{$rec} �� �ѹ� \n");
    echo "$log_date ��ݶ��껦��� NKIT �ʳ� : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date ��ݶ��껦��� NKIT �ʳ� : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date ��ݶ��껦��� NKIT �ʳ� : {$upd_ok}/{$rec} �� �ѹ� \n";
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
    fwrite($fpa, "$log_date : ��ݶ��껦��� NKIT �ʳ��ι����ե����� {$file_orign} ������ޤ���\n");
    fwrite($fpb, "$log_date : ��ݶ��껦��� NKIT �ʳ��ι����ե����� {$file_orign} ������ޤ���\n");
    echo "$log_date : ��ݶ��껦��� NKIT �ʳ��ι����ե����� {$file_orign} ������ޤ���\n";
}

// ��۷׻�������դ�������ն�ۤ��ʤ���ΤΤ�
$query_chk = sprintf("SELECT * FROM link_trans_offset WHERE den_kin='0'");
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// ����ն�ۤ��ʤ����ϲ��⤷�ʤ�
} else {
    ///// ���̵������ update ����
    for ($r=0; $r<$rows; $r++) {
        if ($res[$r][4] == '1') {
            $kin = $res[$r][7] * -1;
        } else {
            $kin = $res[$r][7];
        }
        $query = "UPDATE link_trans_offset SET
                    den_kin = {$kin}
                    where den_ymd={$res[$r][0]} and den_no={$res[$r][1]} and den_eda={$res[$r][2]} and den_gyo={$res[$r][3]} and den_loan='{$res[$r][4]}' and den_account='{$res[$r][5]}' and den_break='{$res[$r][6]}' and den_money={$res[$r][7]} and den_summary1='{$res[$r][8]}' and den_summary2='{$res[$r][9]}' and den_id='{$res[$r][10]}' and den_iymd={$res[$r][11]} and den_ki={$res[$r][12]} and den_code='{$res[$r][13]}'";
        query_affected_trans($con, $query);
    }
}

///////// �����Ϣ NK�ե�����ι��� �������
$file_orign  = '/home/guest/daily/W#RENKEINK.TXT';
$file_backup = '/home/guest/daily/backup/W#RENKEINK-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-RENKEINK.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    $del_fg = 0;    // ����ե饰
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 200, '|');     // �¥쥳���ɤ�183�Х��� �ǥ�ߥ��� '|' �������������'_'�ϻȤ��ʤ�
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num != 14) {           // �ե�����ɿ��Υ����å�
            fwrite($fpa, "$log_date field not 14 record=$rec \n");
            fwrite($fpb, "$log_date field not 14 record=$rec \n");
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
        
        $query_chk = sprintf("SELECT * FROM link_trans_expense_nk WHERE den_ki=%d", $data[12]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $del_fg = 1;
            $query = "INSERT INTO link_trans_expense_nk (den_ymd, den_no, den_eda, den_gyo, den_loan, den_account, den_break, den_money, den_summary1, den_summary2, den_id, den_iymd, den_ki, den_cname, den_kin)
                      VALUES(
                       {$data[0]} ,
                       {$data[1]} ,
                       {$data[2]} ,
                       {$data[3]} ,
                      '{$data[4]}',
                      '{$data[5]}',
                      '{$data[6]}',
                       {$data[7]} ,
                      '{$data[8]}',
                      '{$data[9]}',
                      '{$data[10]}',
                       {$data[11]} ,
                       {$data[12]} ,
                      '{$data[13]}',
                      0)";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �����Ϣ NK:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �����Ϣ NK:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
            if ($del_fg == 0) {
                $query_del = sprintf("DELETE FROM link_trans_expense_nk WHERE den_ki=%d", $data[12]);
                query_affected_trans($con, $query_del);
                $del_fg = 1;
            }
            ///// ����� update ����
            $query = "INSERT INTO link_trans_expense_nk (den_ymd, den_no, den_eda, den_gyo, den_loan, den_account, den_break, den_money, den_summary1, den_summary2, den_id, den_iymd, den_ki, den_cname, den_kin)
                      VALUES(
                       {$data[0]} ,
                       {$data[1]} ,
                       {$data[2]} ,
                       {$data[3]} ,
                      '{$data[4]}',
                      '{$data[5]}',
                      '{$data[6]}',
                       {$data[7]} ,
                      '{$data[8]}',
                      '{$data[9]}',
                      '{$data[10]}',
                       {$data[11]} ,
                       {$data[12]} ,
                      '{$data[13]}',
                      0)";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �����Ϣ NK:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �����Ϣ NK:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
    fwrite($fpa, "$log_date �����Ϣ NK : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date �����Ϣ NK : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date �����Ϣ NK : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpb, "$log_date �����Ϣ NK : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date �����Ϣ NK : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpb, "$log_date �����Ϣ NK : {$upd_ok}/{$rec} �� �ѹ� \n");
    echo "$log_date �����Ϣ NK : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date �����Ϣ NK : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date �����Ϣ NK : {$upd_ok}/{$rec} �� �ѹ� \n";
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
    fwrite($fpa, "$log_date : �����Ϣ NK�ι����ե����� {$file_orign} ������ޤ���\n");
    fwrite($fpb, "$log_date : �����Ϣ NK�ι����ե����� {$file_orign} ������ޤ���\n");
    echo "$log_date : �����Ϣ NK�ι����ե����� {$file_orign} ������ޤ���\n";
}

// ��۷׻�������դ�������ն�ۤ��ʤ���ΤΤ�
$query_chk = sprintf("SELECT * FROM link_trans_expense_nk WHERE den_kin='0'");
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// ����ն�ۤ��ʤ����ϲ��⤷�ʤ�
} else {
    ///// ���̵������ update ����
    for ($r=0; $r<$rows; $r++) {
        if ($res[$r][4] == '1') {
            $kin = $res[$r][7];
        } else {
            $kin = $res[$r][7] * -1;
        }
        $query = "UPDATE link_trans_expense_nk SET
                    den_kin = {$kin}
                    where den_ymd={$res[$r][0]} and den_no={$res[$r][1]} and den_eda={$res[$r][2]} and den_gyo={$res[$r][3]} and den_loan='{$res[$r][4]}' and den_account='{$res[$r][5]}' and den_break='{$res[$r][6]}' and den_money={$res[$r][7]} and den_summary1='{$res[$r][8]}' and den_summary2='{$res[$r][9]}' and den_id='{$res[$r][10]}' and den_iymd={$res[$r][11]} and den_ki={$res[$r][12]} and den_cname='{$res[$r][13]}'";
        query_affected_trans($con, $query);
    }
}

///////// �����Ϣ SNK�ե�����ι��� �������
$file_orign  = '/home/guest/daily/W#RENKEISK.TXT';
$file_backup = '/home/guest/daily/backup/W#RENKEISK-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-RENKEISK.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    $del_fg = 0;    // ����ե饰
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 200, '|');     // �¥쥳���ɤ�183�Х��� �ǥ�ߥ��� '|' �������������'_'�ϻȤ��ʤ�
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num != 14) {           // �ե�����ɿ��Υ����å�
            fwrite($fpa, "$log_date field not 14 record=$rec \n");
            fwrite($fpb, "$log_date field not 14 record=$rec \n");
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
        
        $query_chk = sprintf("SELECT * FROM link_trans_expense_snk WHERE den_ki=%d", $data[12]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $del_fg = 1;
            $query = "INSERT INTO link_trans_expense_snk (den_ymd, den_no, den_eda, den_gyo, den_loan, den_account, den_break, den_money, den_summary1, den_summary2, den_id, den_iymd, den_ki, den_cname, den_kin)
                      VALUES(
                       {$data[0]} ,
                       {$data[1]} ,
                       {$data[2]} ,
                       {$data[3]} ,
                      '{$data[4]}',
                      '{$data[5]}',
                      '{$data[6]}',
                       {$data[7]} ,
                      '{$data[8]}',
                      '{$data[9]}',
                      '{$data[10]}',
                       {$data[11]} ,
                       {$data[12]} ,
                      '{$data[13]}',
                      0)";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �����Ϣ SNK:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �����Ϣ SNK:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
            if ($del_fg == 0) {
                $query_del = sprintf("DELETE FROM link_trans_expense_snk WHERE den_ki=%d", $data[12]);
                query_affected_trans($con, $query_del);
                $del_fg = 1;
            }
            ///// ����� update ����
            $query = "INSERT INTO link_trans_expense_snk (den_ymd, den_no, den_eda, den_gyo, den_loan, den_account, den_break, den_money, den_summary1, den_summary2, den_id, den_iymd, den_ki, den_cname, den_kin)
                      VALUES(
                       {$data[0]} ,
                       {$data[1]} ,
                       {$data[2]} ,
                       {$data[3]} ,
                      '{$data[4]}',
                      '{$data[5]}',
                      '{$data[6]}',
                       {$data[7]} ,
                      '{$data[8]}',
                      '{$data[9]}',
                      '{$data[10]}',
                       {$data[11]} ,
                       {$data[12]} ,
                      '{$data[13]}',
                      0)";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �����Ϣ SNK:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �����Ϣ SNK:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
    fwrite($fpa, "$log_date �����Ϣ SNK : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date �����Ϣ SNK : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date �����Ϣ SNK : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpb, "$log_date �����Ϣ SNK : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date �����Ϣ SNK : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpb, "$log_date �����Ϣ SNK : {$upd_ok}/{$rec} �� �ѹ� \n");
    echo "$log_date �����Ϣ SNK : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date �����Ϣ SNK : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date �����Ϣ SNK : {$upd_ok}/{$rec} �� �ѹ� \n";
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
    fwrite($fpa, "$log_date : �����Ϣ SNK�ι����ե����� {$file_orign} ������ޤ���\n");
    fwrite($fpb, "$log_date : �����Ϣ SNK�ι����ե����� {$file_orign} ������ޤ���\n");
    echo "$log_date : �����Ϣ SNK�ι����ե����� {$file_orign} ������ޤ���\n";
}

// ��۷׻�������դ�������ն�ۤ��ʤ���ΤΤ�
$query_chk = sprintf("SELECT * FROM link_trans_expense_snk WHERE den_kin='0'");
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// ����ն�ۤ��ʤ����ϲ��⤷�ʤ�
} else {
    ///// ���̵������ update ����
    for ($r=0; $r<$rows; $r++) {
        if ($res[$r][4] == '1') {
            $kin = $res[$r][7];
        } else {
            $kin = $res[$r][7] * -1;
        }
        $query = "UPDATE link_trans_expense_snk SET
                    den_kin = {$kin}
                    where den_ymd={$res[$r][0]} and den_no={$res[$r][1]} and den_eda={$res[$r][2]} and den_gyo={$res[$r][3]} and den_loan='{$res[$r][4]}' and den_account='{$res[$r][5]}' and den_break='{$res[$r][6]}' and den_money={$res[$r][7]} and den_summary1='{$res[$r][8]}' and den_summary2='{$res[$r][9]}' and den_id='{$res[$r][10]}' and den_iymd={$res[$r][11]} and den_ki={$res[$r][12]} and den_cname='{$res[$r][13]}'";
        query_affected_trans($con, $query);
    }
}

///////// �����Ϣ MT�ե�����ι��� �������
$file_orign  = '/home/guest/daily/W#RENKEIMT.TXT';
$file_backup = '/home/guest/daily/backup/W#RENKEIMT-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-RENKEIMT.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    $del_fg = 0;    // ����ե饰
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 200, '|');     // �¥쥳���ɤ�183�Х��� �ǥ�ߥ��� '|' �������������'_'�ϻȤ��ʤ�
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num != 14) {           // �ե�����ɿ��Υ����å�
            fwrite($fpa, "$log_date field not 14 record=$rec \n");
            fwrite($fpb, "$log_date field not 14 record=$rec \n");
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
        
        $query_chk = sprintf("SELECT * FROM link_trans_expense_mt WHERE den_ki=%d", $data[12]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $del_fg = 1;
            $query = "INSERT INTO link_trans_expense_mt (den_ymd, den_no, den_eda, den_gyo, den_loan, den_account, den_break, den_money, den_summary1, den_summary2, den_id, den_iymd, den_ki, den_cname, den_kin)
                      VALUES(
                       {$data[0]} ,
                       {$data[1]} ,
                       {$data[2]} ,
                       {$data[3]} ,
                      '{$data[4]}',
                      '{$data[5]}',
                      '{$data[6]}',
                       {$data[7]} ,
                      '{$data[8]}',
                      '{$data[9]}',
                      '{$data[10]}',
                       {$data[11]} ,
                       {$data[12]} ,
                      '{$data[13]}',
                      0)";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �����Ϣ MT:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �����Ϣ MT:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
            if ($del_fg == 0) {
                $query_del = sprintf("DELETE FROM link_trans_expense_mt WHERE den_ki=%d", $data[12]);
                query_affected_trans($con, $query_del);
                $del_fg = 1;
            }
            ///// ����� update ����
            $query = "INSERT INTO link_trans_expense_mt (den_ymd, den_no, den_eda, den_gyo, den_loan, den_account, den_break, den_money, den_summary1, den_summary2, den_id, den_iymd, den_ki, den_cname, den_kin)
                      VALUES(
                       {$data[0]} ,
                       {$data[1]} ,
                       {$data[2]} ,
                       {$data[3]} ,
                      '{$data[4]}',
                      '{$data[5]}',
                      '{$data[6]}',
                       {$data[7]} ,
                      '{$data[8]}',
                      '{$data[9]}',
                      '{$data[10]}',
                       {$data[11]} ,
                       {$data[12]} ,
                      '{$data[13]}',
                      0)";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �����Ϣ MT:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �����Ϣ MT:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
    fwrite($fpa, "$log_date �����Ϣ MT : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date �����Ϣ MT : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date �����Ϣ MT : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpb, "$log_date �����Ϣ MT : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date �����Ϣ MT : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpb, "$log_date �����Ϣ MT : {$upd_ok}/{$rec} �� �ѹ� \n");
    echo "$log_date �����Ϣ MT : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date �����Ϣ MT : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date �����Ϣ MT : {$upd_ok}/{$rec} �� �ѹ� \n";
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
    fwrite($fpa, "$log_date : �����Ϣ MT�ι����ե����� {$file_orign} ������ޤ���\n");
    fwrite($fpb, "$log_date : �����Ϣ MT�ι����ե����� {$file_orign} ������ޤ���\n");
    echo "$log_date : �����Ϣ MT�ι����ե����� {$file_orign} ������ޤ���\n";
}

// ��۷׻�������դ�������ն�ۤ��ʤ���ΤΤ�
$query_chk = sprintf("SELECT * FROM link_trans_expense_mt WHERE den_kin='0'");
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// ����ն�ۤ��ʤ����ϲ��⤷�ʤ�
} else {
    ///// ���̵������ update ����
    for ($r=0; $r<$rows; $r++) {
        if ($res[$r][4] == '1') {
            $kin = $res[$r][7];
        } else {
            $kin = $res[$r][7] * -1;
        }
        $query = "UPDATE link_trans_expense_mt SET
                    den_kin = {$kin}
                    where den_ymd={$res[$r][0]} and den_no={$res[$r][1]} and den_eda={$res[$r][2]} and den_gyo={$res[$r][3]} and den_loan='{$res[$r][4]}' and den_account='{$res[$r][5]}' and den_break='{$res[$r][6]}' and den_money={$res[$r][7]} and den_summary1='{$res[$r][8]}' and den_summary2='{$res[$r][9]}' and den_id='{$res[$r][10]}' and den_iymd={$res[$r][11]} and den_ki={$res[$r][12]} and den_cname='{$res[$r][13]}'";
        query_affected_trans($con, $query);
    }
}

///////// �����Ϣ NKIT�ե�����ι��� �������
$file_orign  = '/home/guest/daily/W#RENKEIIT.TXT';
$file_backup = '/home/guest/daily/backup/W#RENKEIIT-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-RENKEIIT.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    $del_fg = 0;    // ����ե饰
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 200, '|');     // �¥쥳���ɤ�183�Х��� �ǥ�ߥ��� '|' �������������'_'�ϻȤ��ʤ�
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num != 14) {           // �ե�����ɿ��Υ����å�
            fwrite($fpa, "$log_date field not 14 record=$rec \n");
            fwrite($fpb, "$log_date field not 14 record=$rec \n");
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
        
        $query_chk = sprintf("SELECT * FROM link_trans_expense_nkit WHERE den_ki=%d", $data[12]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $del_fg = 1;
            $query = "INSERT INTO link_trans_expense_nkit (den_ymd, den_no, den_eda, den_gyo, den_loan, den_account, den_break, den_money, den_summary1, den_summary2, den_id, den_iymd, den_ki, den_cname, den_kin)
                      VALUES(
                       {$data[0]} ,
                       {$data[1]} ,
                       {$data[2]} ,
                       {$data[3]} ,
                      '{$data[4]}',
                      '{$data[5]}',
                      '{$data[6]}',
                       {$data[7]} ,
                      '{$data[8]}',
                      '{$data[9]}',
                      '{$data[10]}',
                       {$data[11]} ,
                       {$data[12]} ,
                      '{$data[13]}',
                      0)";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �����Ϣ NKIT:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �����Ϣ NKIT:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
            if ($del_fg == 0) {
                $query_del = sprintf("DELETE FROM link_trans_expense_nkit WHERE den_ki=%d", $data[12]);
                query_affected_trans($con, $query_del);
                $del_fg = 1;
            }
            ///// ����� update ����
            $query = "INSERT INTO link_trans_expense_nkit (den_ymd, den_no, den_eda, den_gyo, den_loan, den_account, den_break, den_money, den_summary1, den_summary2, den_id, den_iymd, den_ki, den_cname, den_kin)
                      VALUES(
                       {$data[0]} ,
                       {$data[1]} ,
                       {$data[2]} ,
                       {$data[3]} ,
                      '{$data[4]}',
                      '{$data[5]}',
                      '{$data[6]}',
                       {$data[7]} ,
                      '{$data[8]}',
                      '{$data[9]}',
                      '{$data[10]}',
                       {$data[11]} ,
                       {$data[12]} ,
                      '{$data[13]}',
                      0)";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �����Ϣ NKIT:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �����Ϣ NKIT:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
    fwrite($fpa, "$log_date �����Ϣ NKIT : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date �����Ϣ NKIT : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date �����Ϣ NKIT : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpb, "$log_date �����Ϣ NKIT : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date �����Ϣ NKIT : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpb, "$log_date �����Ϣ NKIT : {$upd_ok}/{$rec} �� �ѹ� \n");
    echo "$log_date �����Ϣ NKIT : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date �����Ϣ NKIT : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date �����Ϣ NKIT : {$upd_ok}/{$rec} �� �ѹ� \n";
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
    fwrite($fpa, "$log_date : �����Ϣ NKIT�ι����ե����� {$file_orign} ������ޤ���\n");
    fwrite($fpb, "$log_date : �����Ϣ NKIT�ι����ե����� {$file_orign} ������ޤ���\n");
    echo "$log_date : �����Ϣ NKIT�ι����ե����� {$file_orign} ������ޤ���\n";
}

// ��۷׻�������դ�������ն�ۤ��ʤ���ΤΤ�
$query_chk = sprintf("SELECT * FROM link_trans_expense_nkit WHERE den_kin='0'");
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// ����ն�ۤ��ʤ����ϲ��⤷�ʤ�
} else {
    ///// ���̵������ update ����
    for ($r=0; $r<$rows; $r++) {
        if ($res[$r][4] == '1') {
            $kin = $res[$r][7];
        } else {
            $kin = $res[$r][7] * -1;
        }
        $query = "UPDATE link_trans_expense_nkit SET
                    den_kin = {$kin}
                    where den_ymd={$res[$r][0]} and den_no={$res[$r][1]} and den_eda={$res[$r][2]} and den_gyo={$res[$r][3]} and den_loan='{$res[$r][4]}' and den_account='{$res[$r][5]}' and den_break='{$res[$r][6]}' and den_money={$res[$r][7]} and den_summary1='{$res[$r][8]}' and den_summary2='{$res[$r][9]}' and den_id='{$res[$r][10]}' and den_iymd={$res[$r][11]} and den_ki={$res[$r][12]} and den_cname='{$res[$r][13]}'";
        query_affected_trans($con, $query);
    }
}

/*
// ��۷׻�������դ�������ն�ۤ��ʤ���ΤΤ�
$query_chk = sprintf("SELECT * FROM manufacture_cost_cal WHERE den_kin='0'");
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// ����ն�ۤ��ʤ����ϲ��⤷�ʤ�
} else {
    ///// ���̵������ update ����
    for ($r=0; $r<$rows; $r++) {
        if ($res[$r][13] == '���ʻ���') {   // ���ʻų���ݶ�ξ�� �߼ڶ�ʬ[3]��1�λ���椬�� ����ʳ��Ϥ��Τޤ�
            if ($res[$r][4] == '1') {
                $kin = $res[$r][7] * -1;
            } else {
                $kin = $res[$r][7];
            }
        } else {    //����ʳ��ξ�� �߼ڶ�ʬ[3]��1�λ����Τޤ� ����ʳ�����椬�դˤʤ�
            if ($res[$r][4] == '1') {
                $kin = $res[$r][7];
            } else {
                $kin = $res[$r][7] * -1;
            }
        }
        $query = "UPDATE manufacture_cost_cal SET
                    den_kin = {$kin}
                    where den_ymd={$res[$r][0]} and den_no={$res[$r][1]} and den_eda={$res[$r][2]} and den_gyo={$res[$r][3]} and den_loan='{$res[$r][4]}' and den_account='{$res[$r][5]}' and den_break='{$res[$r][6]}' and den_money={$res[$r][7]} and den_summary1='{$res[$r][8]}' and den_summary2='{$res[$r][9]}' and den_id='{$res[$r][10]}' and den_iymd={$res[$r][11]} and den_ki={$res[$r][12]} and den_cname='{$res[$r][13]}'";
        query_affected_trans($con, $query);
    }
}
*/

// ��������Ʒ���׻� �����Ƿ׻����ƾȲ���̤ǤϾȲ����

// ����׻�ǯ�����
$target_ym   = date('Ym');          //201710  ���������ΤߺƷ׻�
//$target_ym   = 201901;
//$target_ym   = 201709;              // �ƥ�����
$b_target_ym = $target_ym;          //201709
$b_mm  = substr($b_target_ym, -2, 2);
if ($b_mm == '01') {
    $b_target_ym = $b_target_ym - 100;  // ����Ǥ���ФȤꤢ�����ޥ��ʥ�100����ǯ
    $b_target_ym = $b_target_ym + 11;   // ����˥ץ饹11��12��
} else {
    $b_target_ym = $b_target_ym - 1;    // ����ʳ��Ǥ���Хޥ��ʥ�1������
}
// �������ѻ�ʧ���׻�������
$n_target_ym = $target_ym;          //201711
$n_mm  = substr($n_target_ym, -2, 2);
if ($n_mm == '12') {
    $n_target_ym = $n_target_ym + 100;  // ������Ǥ���ФȤꤢ�����ץ饹100����ǯ
    $n_target_ym = $n_target_ym - 11;   // ����˥ޥ��ʥ�11��1��
} else {
    $n_target_ym = $n_target_ym + 1;    // ����ʳ��Ǥ���Хץ饹1�����
}

// ����׻���������׻�
$bb_target_ym = $b_target_ym;          //201708
$bb_mm  = substr($bb_target_ym, -2, 2);
if ($bb_mm == '01') {
    $bb_target_ym = $bb_target_ym - 100;  // ����Ǥ���ФȤꤢ�����ޥ��ʥ�100����ǯ
    $bb_target_ym = $bb_target_ym + 11;   // ����˥ץ饹11��12��
} else {
    $bb_target_ym = $bb_target_ym - 1;    // ����ʳ��Ǥ���Хޥ��ʥ�1������
}

// �׻���ǯ����
$str_ymd = $target_ym . '00';
$end_ymd = $target_ym . '99';

$b_str_ymd = $b_target_ym . '00';
$b_end_ymd = $b_target_ym . '99';

// ���ס����Ѵط��׻�
// link_trans_expense_nk   ����襳����00001����ݤΥ����ɤ������den_summary1��2��[0500] DB��ʬ���Ƥ�ΤǻȤ�ʤ�
// link_trans_expense_snk  ����襳����00005����ݤΥ����ɤ������den_summary1��2��[0501] DB��ʬ���Ƥ�ΤǻȤ�ʤ�
// link_trans_expense_mt   ����襳����00004����ݤΥ����ɤ������den_summary1��2��[0502] DB��ʬ���Ƥ�ΤǻȤ�ʤ�
// link_trans_expense_nkit ����襳����00101����ݤΥ����ɤ������den_summary1��2��[0503] DB��ʬ���Ƥ�ΤǻȤ�ʤ�

// ���׷׻�
// �Ƽ����ζ�۳�Ǽ
$shueki_money   = array();   // ���[0][0]��00001�μ�����© [0][1]��00001�μ��������⡢[1][0]��00005�μ�����© [1][1]��00005�μ���������
$b_shueki_money = array();   // ���[0][0]��00001�μ�����© [0][1]��00001�μ��������⡢[1][0]��00005�μ�����© [1][1]��00005�μ���������

// ������©
// NK�׻�
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='9101' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[0][0] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[0][0] = 0;
    } else {
        $b_shueki_money[0][0] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='9101' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[0][0] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[0][0] = 0;
    } else {
        $shueki_money[0][0] = $res[0][0];
    }
}
// SNK�׻�
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='9101' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[1][0] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[1][0] = 0;
    } else {
        $b_shueki_money[1][0] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='9101' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[1][0] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[1][0] = 0;
    } else {
        $shueki_money[1][0] = $res[0][0];
    }
}
// MT�׻�
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='9101' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[2][0] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[2][0] = 0;
    } else {
        $b_shueki_money[2][0] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='9101' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[2][0] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[2][0] = 0;
    } else {
        $shueki_money[2][0] = $res[0][0];
    }
}
// NKIT�׻�
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='9101' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[3][0] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[3][0] = 0;
    } else {
        $b_shueki_money[3][0] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='9101' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[3][0] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[3][0] = 0;
    } else {
        $shueki_money[3][0] = $res[0][0];
    }
}
// ����������
// NK�׻�
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='9102' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[0][1] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[0][1] = 0;
    } else {
        $b_shueki_money[0][1] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='9102' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[0][1] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[0][1] = 0;
    } else {
        $shueki_money[0][1] = $res[0][0];
    }
}
// SNK�׻�
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='9102' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[1][1] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[1][1] = 0;
    } else {
        $b_shueki_money[1][1] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='9102' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[1][1] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[1][1] = 0;
    } else {
        $shueki_money[1][1] = $res[0][0];
    }
}
// MT�׻�
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='9102' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[2][1] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[2][1] = 0;
    } else {
        $b_shueki_money[2][1] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='9102' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[2][1] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[2][1] = 0;
    } else {
        $shueki_money[2][1] = $res[0][0];
    }
}
// NKIT�׻�
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='9102' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[3][1] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[3][1] = 0;
    } else {
        $b_shueki_money[3][1] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='9102' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[3][1] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[3][1] = 0;
    } else {
        $shueki_money[3][1] = $res[0][0];
    }
}
// ���¼��� 9103 20 �Ǹ���
// NK�׻�
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='9103' and den_break='20' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[0][2] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[0][2] = 0;
    } else {
        $b_shueki_money[0][2] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='9103' and den_break='20' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[0][2] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[0][2] = 0;
    } else {
        $shueki_money[0][2] = $res[0][0];
    }
}
// SNK�׻�
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='9103' and den_break='20' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[1][2] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[1][2] = 0;
    } else {
        $b_shueki_money[1][2] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='9103' and den_break='20' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[1][2] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[1][2] = 0;
    } else {
        $shueki_money[1][2] = $res[0][0];
    }
}
// MT�׻�
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='9103' and den_break='20' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[2][2] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[2][2] = 0;
    } else {
        $b_shueki_money[2][2] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='9103' and den_break='20' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[2][2] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[2][2] = 0;
    } else {
        $shueki_money[2][2] = $res[0][0];
    }
}
// NKIT�׻�
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='9103' and den_break='20' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[3][2] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[3][2] = 0;
    } else {
        $b_shueki_money[3][2] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='9103' and den_break='20' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[3][2] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[3][2] = 0;
    } else {
        $shueki_money[3][2] = $res[0][0];
    }
}
// ͭ���ٵ�
// NK
$b_shueki_money[0][3] = 0;
$shueki_money[0][3]   = 0;
// SNK
$b_shueki_money[1][3] = 0;
$shueki_money[1][3]   = 0;
// MT
$b_shueki_money[2][3] = 0;
$shueki_money[2][3]   = 0;
// NKIT
$b_shueki_money[3][3] = 0;
$shueki_money[3][3]   = 0;
// �����
// NK
$b_shueki_money[0][4] = 0;
$shueki_money[0][4]   = 0;
// SNK
$b_shueki_money[1][4] = 0;
$shueki_money[1][4]   = 0;
// MT
$b_shueki_money[2][4] = 0;
$shueki_money[2][4]   = 0;
// NKIT
$b_shueki_money[3][4] = 0;
$shueki_money[3][4]   = 0;
// ������
// NK�׻�
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE ((den_account='9103' and den_break='00') or (den_account='9107' and den_break='00')) and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[0][5] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[0][5] = 0;
    } else {
        $b_shueki_money[0][5] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE ((den_account='9103' and den_break='00') or (den_account='9107' and den_break='00')) and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[0][5] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[0][5] = 0;
    } else {
        $shueki_money[0][5] = $res[0][0];
    }
}
// SNK�׻�
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE ((den_account='9103' and den_break='00') or (den_account='9107' and den_break='00')) and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[1][5] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[1][5] = 0;
    } else {
        $b_shueki_money[1][5] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE ((den_account='9103' and den_break='00') or (den_account='9107' and den_break='00')) and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[1][5] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[1][5] = 0;
    } else {
        $shueki_money[1][5] = $res[0][0];
    }
}
// MT�׻�
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE ((den_account='9103' and den_break='00') or (den_account='9107' and den_break='00')) and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[2][5] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[2][5] = 0;
    } else {
        $b_shueki_money[2][5] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE ((den_account='9103' and den_break='00') or (den_account='9107' and den_break='00')) and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[2][5] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[2][5] = 0;
    } else {
        $shueki_money[2][5] = $res[0][0];
    }
}
// NKIT�׻�
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE ((den_account='9103' and den_break='00') or (den_account='9107' and den_break='00')) and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[3][5] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[3][5] = 0;
    } else {
        $b_shueki_money[3][5] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE ((den_account='9103' and den_break='00') or (den_account='9107' and den_break='00')) and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[3][5] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[3][5] = 0;
    } else {
        $shueki_money[3][5] = $res[0][0];
    }
}
// ������Ͽ����
// ��������
// SQL��ͭ���� �����襳����
$input_code   = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
// SQL��ͭ���� ����̾
$input_kamoku = array('������©','����������','���¼���','ͭ���ٵ�','�����','������');
$code_num     = 4;                  // �����襳���ɿ� 4
$kamoku_num   = 6;                  // ���ܿ� 6

// ����
$input_ym     = $b_target_ym;       // SQL��ͭ���� ���դγ�Ǽ
for ($r=0; $r<$code_num; $r++) {
    for ($c=0; $c<$kamoku_num; $c++) {
        $query_chk = getQueryStatement1($input_ym, $input_code[$r], $input_kamoku[$c]);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            ///// ��Ͽ�ʤ� insert ����
            $query = getQueryStatement2($input_ym, $input_code[$r], $input_kamoku[$c], $b_shueki_money[$r][$c]);
            query_affected_trans($con, $query);
        } else {
            ///// ��Ͽ���� update ����
            $query = getQueryStatement3($input_ym, $input_code[$r], $input_kamoku[$c], $b_shueki_money[$r][$c]);
            query_affected_trans($con, $query);
        }
    }
}
// ����
$input_ym     = $target_ym;       // SQL��ͭ���� ���դγ�Ǽ
for ($r=0; $r<$code_num; $r++) {
    for ($c=0; $c<$kamoku_num; $c++) {
        $query_chk = getQueryStatement1($input_ym, $input_code[$r], $input_kamoku[$c]);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            ///// ��Ͽ�ʤ� insert ����
            $query = getQueryStatement2($input_ym, $input_code[$r], $input_kamoku[$c], $shueki_money[$r][$c]);
            query_affected_trans($con, $query);
        } else {
            ///// ��Ͽ���� update ����
            $query = getQueryStatement3($input_ym, $input_code[$r], $input_kamoku[$c], $shueki_money[$r][$c]);
            query_affected_trans($con, $query);
        }
    }
}

// ���ѷ׻�
// link_trans_expense_nk   ����襳����00001����ݤΥ����ɤ������den_summary1��2��[0500] DB��ʬ���Ƥ�ΤǻȤ�ʤ�
// link_trans_expense_snk  ����襳����00005����ݤΥ����ɤ������den_summary1��2��[0501] DB��ʬ���Ƥ�ΤǻȤ�ʤ�
// link_trans_expense_mt   ����襳����00004����ݤΥ����ɤ������den_summary1��2��[0502] DB��ʬ���Ƥ�ΤǻȤ�ʤ�
// link_trans_expense_nkit ����襳����00101����ݤΥ����ɤ������den_summary1��2��[0503] DB��ʬ���Ƥ�ΤǻȤ�ʤ�

// �Ƽ����ζ�۳�Ǽ
$hiyo_money   = array();   // ���[0][0]��00001�μ�����© [0][1]��00001�μ��������⡢[1][0]��00005�μ�����© [1][1]��00005�μ���������
$b_hiyo_money = array();   // ���[0][0]��00001�μ�����© [0][1]��00001�μ��������⡢[1][0]��00005�μ�����© [1][1]��00005�μ���������

// ��ʧ��©
// NK�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='8201' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[0][0] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[0][0] = 0;
    } else {
        $b_hiyo_money[0][0] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='8201' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[0][0] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[0][0] = 0;
    } else {
        $hiyo_money[0][0] = $res[0][0];
    }
}
// SNK�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='8201' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[1][0] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[1][0] = 0;
    } else {
        $b_hiyo_money[1][0] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='8201' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[1][0] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[1][0] = 0;
    } else {
        $hiyo_money[1][0] = $res[0][0];
    }
}
// MT�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='8201' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[2][0] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[2][0] = 0;
    } else {
        $b_hiyo_money[2][0] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='8201' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[2][0] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[2][0] = 0;
    } else {
        $hiyo_money[2][0] = $res[0][0];
    }
}
// NKIT�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='8201' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[3][0] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[3][0] = 0;
    } else {
        $b_hiyo_money[3][0] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='8201' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[3][0] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[3][0] = 0;
    } else {
        $hiyo_money[3][0] = $res[0][0];
    }
}

// �̿���
// NK�׻�
// ����
// NK�Τ߲���ʳ��Ȳ����ʬ����(�����ǽ��������)
// ����ʳ�
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7503' and den_id<>'C' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[0][1] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[0][1] = 0;
    } else {
        $b_hiyo_money[0][1] = $res[0][0];
    }
}
// ����Τ�
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7503' and den_id='C' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[0][1] += 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[0][1] += 0;
    } else {
        $b_hiyo_money[0][1] += round($res[0][0]/1.08);
    }
}
// ����
// NK�Τ߲���ʳ��Ȳ����ʬ����(�����ǽ��������)
// ����ʳ�
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7503' and den_id<>'C' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[0][1] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[0][1] = 0;
    } else {
        $hiyo_money[0][1] = $res[0][0];
    }
}
// ����Τ�
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7503' and den_id='C' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[0][1] += 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[0][1] += 0;
    } else {
        $hiyo_money[0][1] += round($res[0][0]/1.08);
    }
}
// SNK�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7503' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[1][1] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[1][1] = 0;
    } else {
        $b_hiyo_money[1][1] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7503' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[1][1] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[1][1] = 0;
    } else {
        $hiyo_money[1][1] = $res[0][0];
    }
}
// MT�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7503' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[2][1] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[2][1] = 0;
    } else {
        $b_hiyo_money[2][1] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7503' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[2][1] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[2][1] = 0;
    } else {
        $hiyo_money[2][1] = $res[0][0];
    }
}
// NKIT�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7503' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[3][1] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[3][1] = 0;
    } else {
        $b_hiyo_money[3][1] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7503' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[3][1] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[3][1] = 0;
    } else {
        $hiyo_money[3][1] = $res[0][0];
    }
}

// �����������
// NK�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7527' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[0][2] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[0][2] = 0;
    } else {
        $b_hiyo_money[0][2] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7527' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[0][2] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[0][2] = 0;
    } else {
        $hiyo_money[0][2] = $res[0][0];
    }
}
// SNK�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7527' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[1][2] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[1][2] = 0;
    } else {
        $b_hiyo_money[1][2] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7527' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[1][2] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[1][2] = 0;
    } else {
        $hiyo_money[1][2] = $res[0][0];
    }
}
// MT�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7527' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[2][2] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[2][2] = 0;
    } else {
        $b_hiyo_money[2][2] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7527' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[2][2] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[2][2] = 0;
    } else {
        $hiyo_money[2][2] = $res[0][0];
    }
}
// NKIT�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7527' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[3][2] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[3][2] = 0;
    } else {
        $b_hiyo_money[3][2] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7527' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[3][2] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[3][2] = 0;
    } else {
        $hiyo_money[3][2] = $res[0][0];
    }
}

// ��̳��������
// NK�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7526' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[0][3] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[0][3] = 0;
    } else {
        $b_hiyo_money[0][3] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7526' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[0][3] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[0][3] = 0;
    } else {
        $hiyo_money[0][3] = $res[0][0];
    }
}
// SNK�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7526' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[1][3] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[1][3] = 0;
    } else {
        $b_hiyo_money[1][3] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7526' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[1][3] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[1][3] = 0;
    } else {
        $hiyo_money[1][3] = $res[0][0];
    }
}
// MT�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7526' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[2][3] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[2][3] = 0;
    } else {
        $b_hiyo_money[2][3] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7526' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[2][3] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[2][3] = 0;
    } else {
        $hiyo_money[2][3] = $res[0][0];
    }
}
// NKIT�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7526' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[3][3] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[3][3] = 0;
    } else {
        $b_hiyo_money[3][3] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7526' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[3][3] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[3][3] = 0;
    } else {
        $hiyo_money[3][3] = $res[0][0];
    }
}

// ��̳������
// NK�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7512' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[0][4] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[0][4] = 0;
    } else {
        $b_hiyo_money[0][4] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7512' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[0][4] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[0][4] = 0;
    } else {
        $hiyo_money[0][4] = $res[0][0];
    }
}
// SNK�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7512' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[1][4] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[1][4] = 0;
    } else {
        $b_hiyo_money[1][4] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7512' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[1][4] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[1][4] = 0;
    } else {
        $hiyo_money[1][4] = $res[0][0];
    }
}
// MT�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7512' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[2][4] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[2][4] = 0;
    } else {
        $b_hiyo_money[2][4] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7512' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[2][4] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[2][4] = 0;
    } else {
        $hiyo_money[2][4] = $res[0][0];
    }
}
// NKIT�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7512' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[3][4] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[3][4] = 0;
    } else {
        $b_hiyo_money[3][4] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7512' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[3][4] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[3][4] = 0;
    } else {
        $hiyo_money[3][4] = $res[0][0];
    }
}
// ���²�¤��
// NK�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7509' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[0][5] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[0][5] = 0;
    } else {
        $b_hiyo_money[0][5] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7509' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[0][5] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[0][5] = 0;
    } else {
        $hiyo_money[0][5] = $res[0][0];
    }
}
// SNK�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7509' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[1][5] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[1][5] = 0;
    } else {
        $b_hiyo_money[1][5] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7509' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[1][5] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[1][5] = 0;
    } else {
        $hiyo_money[1][5] = $res[0][0];
    }
}
// MT�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7509' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[2][5] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[2][5] = 0;
    } else {
        $b_hiyo_money[2][5] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7509' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[2][5] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[2][5] = 0;
    } else {
        $hiyo_money[2][5] = $res[0][0];
    }
}
// NKIT�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7509' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[3][5] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[3][5] = 0;
    } else {
        $b_hiyo_money[3][5] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7509' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[3][5] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[3][5] = 0;
    } else {
        $hiyo_money[3][5] = $res[0][0];
    }
}
// ��ƻ��Ǯ��
// NK�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7531' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[0][6] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[0][6] = 0;
    } else {
        $b_hiyo_money[0][6] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7531' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[0][6] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[0][6] = 0;
    } else {
        $hiyo_money[0][6] = $res[0][0];
    }
}
// SNK�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7531' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[1][6] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[1][6] = 0;
    } else {
        $b_hiyo_money[1][6] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7531' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[1][6] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[1][6] = 0;
    } else {
        $hiyo_money[1][6] = $res[0][0];
    }
}
// MT�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7531' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[2][6] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[2][6] = 0;
    } else {
        $b_hiyo_money[2][6] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7531' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[2][6] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[2][6] = 0;
    } else {
        $hiyo_money[2][6] = $res[0][0];
    }
}
// NKIT�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7531' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[3][6] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[3][6] = 0;
    } else {
        $b_hiyo_money[3][6] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7531' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[3][6] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[3][6] = 0;
    } else {
        $hiyo_money[3][6] = $res[0][0];
    }
}
// �������
// NK�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7522' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[0][7] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[0][7] = 0;
    } else {
        $b_hiyo_money[0][7] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7522' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[0][7] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[0][7] = 0;
    } else {
        $hiyo_money[0][7] = $res[0][0];
    }
}
// SNK�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7522' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[1][7] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[1][7] = 0;
    } else {
        $b_hiyo_money[1][7] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7522' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[1][7] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[1][7] = 0;
    } else {
        $hiyo_money[1][7] = $res[0][0];
    }
}
// MT�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7522' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[2][7] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[2][7] = 0;
    } else {
        $b_hiyo_money[2][7] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7522' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[2][7] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[2][7] = 0;
    } else {
        $hiyo_money[2][7] = $res[0][0];
    }
}
// NKIT�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7522' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[3][7] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[3][7] = 0;
    } else {
        $b_hiyo_money[3][7] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7522' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[3][7] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[3][7] = 0;
    } else {
        $hiyo_money[3][7] = $res[0][0];
    }
}
// �������
// NK�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7536' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[0][8] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[0][8] = 0;
    } else {
        $b_hiyo_money[0][8] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7536' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[0][8] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[0][8] = 0;
    } else {
        $hiyo_money[0][8] = $res[0][0];
    }
}
// SNK�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7536' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[1][8] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[1][8] = 0;
    } else {
        $b_hiyo_money[1][8] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7536' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[1][8] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[1][8] = 0;
    } else {
        $hiyo_money[1][8] = $res[0][0];
    }
}
// MT�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7536' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[2][8] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[2][8] = 0;
    } else {
        $b_hiyo_money[2][8] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7536' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[2][8] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[2][8] = 0;
    } else {
        $hiyo_money[2][8] = $res[0][0];
    }
}
// NKIT�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7536' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[3][8] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[3][8] = 0;
    } else {
        $b_hiyo_money[3][8] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7536' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[3][8] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[3][8] = 0;
    } else {
        $hiyo_money[3][8] = $res[0][0];
    }
}
// ������
// NK�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7538' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[0][9] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[0][9] = 0;
    } else {
        $b_hiyo_money[0][9] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7538' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[0][9] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[0][9] = 0;
    } else {
        $hiyo_money[0][9] = $res[0][0];
    }
}
// SNK�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7538' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[1][9] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[1][9] = 0;
    } else {
        $b_hiyo_money[1][9] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7538' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[1][9] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[1][9] = 0;
    } else {
        $hiyo_money[1][9] = $res[0][0];
    }
}
// MT�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7538' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[2][9] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[2][9] = 0;
    } else {
        $b_hiyo_money[2][9] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7538' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[2][9] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[2][9] = 0;
    } else {
        $hiyo_money[2][9] = $res[0][0];
    }
}
// NKIT�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7538' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[3][9] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[3][9] = 0;
    } else {
        $b_hiyo_money[3][9] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7538' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[3][9] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[3][9] = 0;
    } else {
        $hiyo_money[3][9] = $res[0][0];
    }
}
// �¼���
// NK�׻�
// ����
// NK�Τ߲���ʳ��Ȳ����ʬ����(�����ǽ��������)
// ����ʳ�
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7540' and den_id<>'C' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[0][10] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[0][10] = 0;
    } else {
        $b_hiyo_money[0][10] = $res[0][0];
    }
}
// ����Τ�
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7540' and den_id='C' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[0][10] += 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[0][10] += 0;
    } else {
        $b_hiyo_money[0][10] += round($res[0][0]/1.08);
    }
}
// ����
// NK�Τ߲���ʳ��Ȳ����ʬ����(�����ǽ��������)
// ����ʳ�
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7540' and den_id<>'C' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[0][10] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[0][10] = 0;
    } else {
        $hiyo_money[0][10] = $res[0][0];
    }
}
// ����Τ�
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7540' and den_id='C' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[0][10] += 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[0][10] += 0;
    } else {
        $hiyo_money[0][10] += round($res[0][0]/1.08);
    }
}
// SNK�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7540' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[1][10] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[1][10] = 0;
    } else {
        $b_hiyo_money[1][10] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7540' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[1][10] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[1][10] = 0;
    } else {
        $hiyo_money[1][10] = $res[0][0];
    }
}
// MT�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7540' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[2][10] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[2][10] = 0;
    } else {
        $b_hiyo_money[2][10] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7540' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[2][10] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[2][10] = 0;
    } else {
        $hiyo_money[2][10] = $res[0][0];
    }
}
// NKIT�׻�
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7540' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[3][10] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[3][10] = 0;
    } else {
        $b_hiyo_money[3][10] = $res[0][0];
    }
}
// ����
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7540' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[3][10] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[3][10] = 0;
    } else {
        $hiyo_money[3][10] = $res[0][0];
    }
}

// ������Ͽ����
// ��������
// SQL��ͭ���� �����襳����
$input_code   = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
// SQL��ͭ���� ����̾
$input_kamoku = array('��ʧ��©','�̿���','�����������','��̳��������','��̳������','���²�¤��','��ƻ��Ǯ��','�������','�������','������','�¼���');
$code_num     = 4;                  // �����襳���ɿ� 4
$kamoku_num   = 11;                 // ���ܿ� 11

// ����
$input_ym     = $b_target_ym;       // SQL��ͭ���� ���դγ�Ǽ
for ($r=0; $r<$code_num; $r++) {
    for ($c=0; $c<$kamoku_num; $c++) {
        $query_chk = getQueryStatement1($input_ym, $input_code[$r], $input_kamoku[$c]);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            ///// ��Ͽ�ʤ� insert ����
            $query = getQueryStatement2($input_ym, $input_code[$r], $input_kamoku[$c], $b_hiyo_money[$r][$c]);
            query_affected_trans($con, $query);
        } else {
            ///// ��Ͽ���� update ����
            $query = getQueryStatement3($input_ym, $input_code[$r], $input_kamoku[$c], $b_hiyo_money[$r][$c]);
            query_affected_trans($con, $query);
        }
    }
}
// ����
$input_ym     = $target_ym;       // SQL��ͭ���� ���դγ�Ǽ
for ($r=0; $r<$code_num; $r++) {
    for ($c=0; $c<$kamoku_num; $c++) {
        $query_chk = getQueryStatement1($input_ym, $input_code[$r], $input_kamoku[$c]);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            ///// ��Ͽ�ʤ� insert ����
            $query = getQueryStatement2($input_ym, $input_code[$r], $input_kamoku[$c], $hiyo_money[$r][$c]);
            query_affected_trans($con, $query);
        } else {
            ///// ��Ͽ���� update ����
            $query = getQueryStatement3($input_ym, $input_code[$r], $input_kamoku[$c], $hiyo_money[$r][$c]);
            query_affected_trans($con, $query);
        }
    }
}

// ���⡦������׻�
// ��������
// SQL��ͭ���� �����襳����
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
// SQL��ͭ���� ����̾
$input_div = array('C','L','T','SSL','NKB');            // �ɲäξ���NKB��Ǹ�ˤ����
$code_num  = 4;                 // �����襳���ɿ� 4
$div_num   = 5;                 // ���ܿ� 5

$b_sum_money = 0;               // ����ɰʳ���������
$sum_money   = 0;               // ���ɰʳ���������
$sales_money = array();         // �ݴ���
$b_sales_money = array();       // �����ݴ���
$total_money = array();         // ����ݴ���
$b_total_money = array();       // �������ݴ���

// ����ǡ�������
// ����
for ($r=0; $r<$code_num; $r++) {
    $b_sum_money = 0;
    for ($c=0; $c<$div_num; $c++) {
        if( $r == 3 ) {     // NKIT �ξ���ɬ�פʤ��Τ�0�ˤ��롣
            $b_sales_money[$r][$c] = 0;
            $b_total_money[$r]     = 0;
        } else {
            if( $c <> 4 ) {     // NKB�ʳ� �ξ��
                $query_chk = getQueryStatement4($b_str_ymd, $b_end_ymd, $input_code[$r], $input_div[$c]);
                if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
                    $b_sales_money[$r][$c] = 0;
                    $b_sum_money          += 0;
                } else {
                    if ($res[0][0] == '') {
                        $b_sales_money[$r][$c] = 0;
                        $b_sum_money          += 0;
                    } else {
                        $b_sales_money[$r][$c] = $res[0][0];
                        $b_sum_money          += $res[0][0];
                    }
                }
            } else {            // NKB �ξ��
                $query_chk = sprintf("SELECT round(sales_kei/1.08) FROM link_trans_sales WHERE  sales_code='%s' and sales_ym=%d", $input_code[$r], $b_target_ym);
                if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
                    $b_total_money[$r] = 0;
                } else {
                    if ($res[0][0] == '') {
                        $b_total_money[$r] = 0;
                    } else {
                        $b_total_money[$r] = $res[0][0];
                    }
                }
                $b_sales_money[$r][$c] = $b_total_money[$r] - $b_sum_money;
            }
        }
    }
}
// ����
for ($r=0; $r<$code_num; $r++) {
    $sum_money = 0;
    for ($c=0; $c<$div_num; $c++) {
        if( $r == 3 ) {     // NKIT �ξ���ɬ�פʤ��Τ�0�ˤ��롣
            $sales_money[$r][$c] = 0;
            $total_money[$r]     = 0;
        } else {
            if( $c <> 4 ) {     // NKB�ʳ� �ξ��
                $query_chk = getQueryStatement4($str_ymd, $end_ymd, $input_code[$r], $input_div[$c]);
                if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
                    $sales_money[$r][$c] = 0;
                    $sum_money          += 0;
                } else {
                    if ($res[0][0] == '') {
                        $sales_money[$r][$c] = 0;
                        $sum_money          += 0;
                    } else {
                        $sales_money[$r][$c] = $res[0][0];
                        $sum_money          += $res[0][0];
                    }
                }
            } else {            // NKB �ξ��
                $query_chk = sprintf("SELECT round(sales_kei/1.08) FROM link_trans_sales WHERE  sales_code='%s' and sales_ym=%d", $input_code[$r], $target_ym);
                if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
                    $total_money[$r] = 0;
                } else {
                    if ($res[0][0] == '') {
                        $total_money[$r] = 0;
                    } else {
                        $total_money[$r] = $res[0][0];
                    }
                }
                $sales_money[$r][$c] = $total_money[$r] - $sum_money;
            }
        }
    }
}

// ������Ͽ
$kamoku = '����';
// �����˹�碌�ưʲ����ѹ����뤳��
// SQL��ͭ���� �����襳����
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
// SQL��ͭ���� ����̾
$input_div = array('C','L','T','SSL','NKB','TOTAL');  // �ɲäξ���TOTAL��Ǹ�ˤ����
$code_num  = 4;                 // �����襳���ɿ� 4
$div_num   = 6;                 // ���ܿ� 6
// ����
$input_ym     = $b_target_ym;       // SQL��ͭ���� ���դγ�Ǽ
for ($r=0; $r<$code_num; $r++) {
    for ($c=0; $c<$div_num; $c++) {
        $query_chk = getQueryStatement5($input_ym, $input_code[$r], $kamoku);
        if ($c <> 5) {  // ��װʳ��λ�
            if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
                ///// ��Ͽ�ʤ� insert ����
                $query = getQueryStatement6($input_ym, $input_code[$r], $input_div[$c], $b_sales_money[$r][$c], $kamoku);
                query_affected_trans($con, $query);
            } else {
                ///// ��Ͽ���� update ����
                $query = getQueryStatement7($input_ym, $input_code[$r], $input_div[$c], $b_sales_money[$r][$c], $kamoku);
                query_affected_trans($con, $query);
            }
        } else {        // ��פΤȤ�
            if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
                ///// ��Ͽ�ʤ� insert ����
                $query = getQueryStatement6($input_ym, $input_code[$r], $input_div[$c], $b_total_money[$r], $kamoku);
                query_affected_trans($con, $query);
            } else {
                ///// ��Ͽ���� update ����
                $query = getQueryStatement7($input_ym, $input_code[$r], $input_div[$c], $b_total_money[$r], $kamoku);
                query_affected_trans($con, $query);
            }
        }
    }
}
// ����
$input_ym     = $target_ym;       // SQL��ͭ���� ���դγ�Ǽ
for ($r=0; $r<$code_num; $r++) {
    for ($c=0; $c<$div_num; $c++) {
        $query_chk = getQueryStatement5($input_ym, $input_code[$r], $kamoku);
        if ($c <> 5) {  // ��װʳ��λ�
            if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
                ///// ��Ͽ�ʤ� insert ����
                $query = getQueryStatement6($input_ym, $input_code[$r], $input_div[$c], $sales_money[$r][$c], $kamoku);
                query_affected_trans($con, $query);
            } else {
                ///// ��Ͽ���� update ����
                $query = getQueryStatement7($input_ym, $input_code[$r], $input_div[$c], $sales_money[$r][$c], $kamoku);
                query_affected_trans($con, $query);
            }
        } else {        // ��פΤȤ�
            if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
                ///// ��Ͽ�ʤ� insert ����
                $query = getQueryStatement6($input_ym, $input_code[$r], $input_div[$c], $total_money[$r], $kamoku);
                query_affected_trans($con, $query);
            } else {
                ///// ��Ͽ���� update ����
                $query = getQueryStatement7($input_ym, $input_code[$r], $input_div[$c], $total_money[$r], $kamoku);
                query_affected_trans($con, $query);
            }
        }
    }
}

// ������ǡ�������
// ��������
// SQL��ͭ���� �����襳����
$input_code = array('91111','01566','00040','05001');  // 91111:NK 01556:SNK 00040:MT 05001:NKIT
$code_num  = 4;                 // �����襳���ɿ� 4

$purchase_money   = array();    // �ݴ���
$b_purchase_money = array();    // �����ݴ���

// ����
$payment_ym = $target_ym;
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement8($b_str_ymd, $b_end_ymd, $payment_ym, $input_code[$r]);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        $b_purchase_money[$r] = 0;
    } else {
        if ($res[0][0] == '') {
            $b_purchase_money[$r] = 0;
        } else {
            $b_purchase_money[$r] = $res[0][0];
        }
    }
}
// ����
$payment_ym = $n_target_ym;
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement8($str_ymd, $end_ymd, $payment_ym, $input_code[$r]);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        $purchase_money[$r] = 0;
    } else {
        if ($res[0][0] == '') {
            $purchase_money[$r] = 0;
        } else {
            $purchase_money[$r] = $res[0][0];
        }
    }
}

// ��������Ͽ

$kamoku = '������';
// �����˹�碌�ưʲ����ѹ����뤳��
// SQL��ͭ���� �����襳����
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
// SQL��ͭ���� ����̾
$code_num  = 4;                 // �����襳���ɿ� 4
// ����
$input_ym     = $b_target_ym;       // SQL��ͭ���� ���դγ�Ǽ
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement9($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// ��Ͽ�ʤ� insert ����
        $query = getQueryStatement10($input_ym, $input_code[$r], $b_purchase_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// ��Ͽ���� update ����
        $query = getQueryStatement11($input_ym, $input_code[$r], $b_purchase_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
// ����
$input_ym     = $target_ym;       // SQL��ͭ���� ���դγ�Ǽ
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement9($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// ��Ͽ�ʤ� insert ����
        $query = getQueryStatement10($input_ym, $input_code[$r], $purchase_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// ��Ͽ���� update ����
        $query = getQueryStatement11($input_ym, $input_code[$r], $purchase_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}

// ��ݶ⡦�����Ϣ����׻�
// ��ݶ�׻� ������ǡ������� �� ��1.08���������ݶ�ǡ�����
// ��������
// SQL��ͭ���� �����襳����
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
// SQL��ͭ���� ���������
$tegata_code = array('001','005','004','101');  // 001:NK 005:SNK 004:MT 101:NKIT SNK��NKIT��̵���������襳���ɤȹ�碌���Ŭ��
$code_num    = 4;                 // �����襳���ɿ� 4
$tegata_num  = 4;                 // ��������襳���ɿ� 4   // �ºݤ�2�Ĥ��������襳���ɤ˹�碌��

$ap_zen_money   = array();    // �����ݴ���
$b_ap_zen_money = array();    // ������ݴ���
$ap_kei_money   = array();    // �׾��ݴ���
$b_ap_kei_money = array();    // ����׾��ݴ���
$ap_kai_money   = array();    // ��ù��ݴ���
$b_ap_kai_money = array();    // �����ù��ݴ���
$ap_zan_money   = array();    // �Ĺ��ݴ���
$b_ap_zan_money = array();    // ����Ĺ��ݴ���

// ����
$input_ym     = $b_target_ym;       // SQL��ͭ���� ���դγ�Ǽ
$zen_ym       = $bb_target_ym;      // SQL��ͭ���� ����۹�����
$account      = '3103';
for ($r=0; $r<$code_num; $r++) {
    // ������λĹ������������۶�ۤ�
    $kamoku = '��ݶ�';
    if ($zen_ym > 201703) {         // 201705�ʹߤ��̾�׻�
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_ap_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_ap_zen_money[$r] = 0;
            } else {
                $b_ap_zen_money[$r] = $res[0][0];
            }
        }
    } else {                                // 201704������Υǡ������ʤ��ΤǶ�������
        if ($input_code[$r] == '00001') {   // NK�ξ��
            $b_ap_zen_money[$r] = 12671410; // 201704������۹�
        } else if ($input_code[$r] == '00004') {   // MT�ξ��
            $b_ap_zen_money[$r] = 639416; // 201704������۹�
        } else if ($input_code[$r] == '00101') {   // NKIT�ξ��
            $b_ap_zen_money[$r] = 0; // 201704������۹�
        } else {                                   // ����ʳ��ʸ������Ǥ�SNK��
            $b_ap_zen_money[$r] = 0; // 201704������۹�
        }
    }
    // ������ǡ��� �� ��1.08���������ȯ�����
    $kamoku = '������';
    $query_chk = getQueryStatement13($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        $b_ap_kei_money[$r] = 0;
    } else {
        if ($res[0][0] == '') {
            $b_ap_kei_money[$r] = 0;
        } else {
            if ($input_code[$r] == '00101') {               // NKIT���ǹ���
                $b_ap_kei_money[$r] = $res[0][0];
            } else {
                $b_ap_kei_money[$r] = round($res[0][0] * 1.08);
            }
        }
    }
    // �껦��ۼ��� �� �����ù� SNK�ϼ������ʤ���ʬ���롢NKIT����SQL
    if ($input_code[$r] == '00005') {               // SNK�Ϥʤ��ΤǶ���Ū��0
        $b_ap_kai_money[$r] = 0;
    } elseif ($input_code[$r] == '00101') {         // NKIT�ϥǡ����ξ�꤬�㤦�Τ���SQL
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_ap_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_ap_kai_money[$r] = 0;
            } else {
                $b_ap_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // NK��MT
        if ($input_code[$r] == '00004') {               // MT��4��λ��ǡ������ʤ��ΤǶ���
            if ($input_ym == 201704) {
                $b_ap_kai_money[$r] = 690569;
            } else {
                $query_chk = getQueryStatement14($b_str_ymd, $b_end_ymd, $tegata_code[$r]);
                if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
                    $b_ap_kai_money[$r] = 0;
                } else {
                    if ($res[0][0] == '') {
                        $b_ap_kai_money[$r] = 0;
                    } else {
                        $b_ap_kai_money[$r] = $res[0][0];
                    }
                }
            }
        } else {
            $query_chk = getQueryStatement14($b_str_ymd, $b_end_ymd, $tegata_code[$r]);
            if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
                $b_ap_kai_money[$r] = 0;
            } else {
                if ($res[0][0] == '') {
                    $b_ap_kai_money[$r] = 0;
                } else {
                    $b_ap_kai_money[$r] = $res[0][0];
                }
            }
        }
    }
    // �Ǹ�˻Ĺ�׻�
    $b_ap_zan_money[$r] = $b_ap_zen_money[$r] + $b_ap_kei_money[$r] - $b_ap_kai_money[$r];
}
// ����
$input_ym     = $target_ym;       // SQL��ͭ���� ���դγ�Ǽ
$zen_ym       = $b_target_ym;     // SQL��ͭ���� ����۹�����
for ($r=0; $r<$code_num; $r++) {
    $kamoku = '��ݶ�';
    // ������λĹ������������۶�ۤ�
    if ($zen_ym > 201703) {         // 201704�ʹߤ��̾�׻�
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $ap_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $ap_zen_money[$r] = 0;
            } else {
                $ap_zen_money[$r] = $res[0][0];
            }
        }
    } else {                        // ���201703������Υǡ������ʤ��ΤǶ�������
        if ($input_code[$r] == '00001') {   // NK�ξ��
            $ap_zen_money[$r] = 12671410; // 201704������۹�
        } else if ($input_code[$r] == '00004') {   // MT�ξ��
            $ap_zen_money[$r] = 639416; // 201704������۹�
        } else if ($input_code[$r] == '00101') {   // NKIT�ξ��
            $ap_zen_money[$r] = 0; // 201704������۹�
        } else {                                   // ����ʳ��ʸ������Ǥ�SNK��
            $ap_zen_money[$r] = 0; // 201704������۹�
        }
    }
    $kamoku = '������';
    // ������ǡ��� �� ��1.08���������ȯ�����
    $query_chk = getQueryStatement13($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        $ap_kei_money[$r] = 0;
    } else {
        if ($res[0][0] == '') {
            $ap_kei_money[$r] = 0;
        } else {
            if ($input_code[$r] == '00101') {               // NKIT���ǹ���
                $ap_kei_money[$r] = $res[0][0];
            } else {
                $ap_kei_money[$r] = round($res[0][0] * 1.08);
            }
        }
    }
    // �껦��ۼ��� �� �����ù� SNK�ϼ������ʤ���ʬ���롢NKIT����SQL
    if ($input_code[$r] == '00005') {               // SNK�Ϥʤ��ΤǶ���Ū��0
        $ap_kai_money[$r] = 0;
    } elseif ($input_code[$r] == '00101') {         // NKIT�ϥǡ����ξ�꤬�㤦�Τ���SQL
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='3103' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $ap_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $ap_kai_money[$r] = 0;
            } else {
                $ap_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // NK��MT
        if ($input_code[$r] == '00004') {               // MT��4��λ��ǡ������ʤ��ΤǶ���
            if ($input_ym == 201704) {
                $ap_kai_money[$r] = 690569;
            } else {
                $query_chk = getQueryStatement14($str_ymd, $end_ymd, $tegata_code[$r]);
                if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
                    $ap_kai_money[$r] = 0;
                } else {
                    if ($res[0][0] == '') {
                        $ap_kai_money[$r] = 0;
                    } else {
                        $ap_kai_money[$r] = $res[0][0];
                    }
                }
            }
        } else {
            $query_chk = getQueryStatement14($str_ymd, $end_ymd, $tegata_code[$r]);
            if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
                $ap_kai_money[$r] = 0;
            } else {
                if ($res[0][0] == '') {
                    $ap_kai_money[$r] = 0;
                } else {
                    $ap_kai_money[$r] = $res[0][0];
                }
            }
        }
    }
    // �Ǹ�˻Ĺ�׻�
    $ap_zan_money[$r] = $ap_zen_money[$r] + $ap_kei_money[$r] - $ap_kai_money[$r];
}


// ��ݶ���Ͽ
$kamoku = '��ݶ�';
// �����˹�碌�ưʲ����ѹ����뤳��
// SQL��ͭ���� �����襳����
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
// SQL��ͭ���� ����̾
$code_num  = 4;                 // �����襳���ɿ� 4
// ����
$input_ym     = $b_target_ym;       // SQL��ͭ���� ���դγ�Ǽ
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// ��Ͽ�ʤ� insert ���� 
        $query = getQueryStatement16($input_ym, $input_code[$r], $b_ap_zen_money[$r], $b_ap_kei_money[$r], $b_ap_kai_money[$r], $b_ap_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// ��Ͽ���� update ����
        $query = getQueryStatement17($input_ym, $input_code[$r], $b_ap_zen_money[$r], $b_ap_kei_money[$r], $b_ap_kai_money[$r], $b_ap_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
// ����
$input_ym     = $target_ym;       // SQL��ͭ���� ���դγ�Ǽ
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// ��Ͽ�ʤ� insert ���� 
        $query = getQueryStatement16($input_ym, $input_code[$r], $ap_zen_money[$r], $ap_kei_money[$r], $ap_kai_money[$r], $ap_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// ��Ͽ���� update ����
        $query = getQueryStatement17($input_ym, $input_code[$r], $ap_zen_money[$r], $ap_kei_money[$r], $ap_kai_money[$r], $ap_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}

// �����Ϣ�׻�
// ̤������׻�
// ��������
// SQL��ͭ���� �����襳����
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
$code_num    = 4;                 // �����襳���ɿ� 4

$input_zen_money   = array();    // �����ݴ���
$b_input_zen_money = array();    // ������ݴ���
$input_kei_money   = array();    // �׾��ݴ���
$b_input_kei_money = array();    // ����׾��ݴ���
$input_kai_money   = array();    // ��ù��ݴ���
$b_input_kai_money = array();    // �����ù��ݴ���
$input_zan_money   = array();    // �Ĺ��ݴ���
$b_input_zan_money = array();    // ����Ĺ��ݴ���

// ����
$input_ym     = $b_target_ym;       // SQL��ͭ���� ���դγ�Ǽ
$zen_ym       = $bb_target_ym;      // SQL��ͭ���� ����۹�����
$kamoku = '̤������';
$account      = '1503';
for ($r=0; $r<$code_num; $r++) {
    // ������λĹ������������۶�ۤ�
    if ($zen_ym > 201703) {         // 201705�ʹߤ��̾�׻�
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_zen_money[$r] = 0;
            } else {
                $b_input_zen_money[$r] = $res[0][0];
            }
        }
    } else {                                // 201704������Υǡ������ʤ��ΤǶ�������
        if ($input_code[$r] == '00001') {   // NK�ξ��
            $b_input_zen_money[$r] = 427126; // 201704������۹�
        } else if ($input_code[$r] == '00004') {   // MT�ξ��
            $b_input_zen_money[$r] = 0; // 201704������۹�
        } else if ($input_code[$r] == '00101') {   // NKIT�ξ��
            $b_input_zen_money[$r] = 0; // 201704������۹�
        } else {                                   // ����ʳ��ʸ������Ǥ�SNK��
            $b_input_zen_money[$r] = 0; // 201704������۹�
        }
    }
    // ����ȯ�������
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    }
    // �����ù�
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    }
    // �Ǹ�˻Ĺ�׻�
    $b_input_zan_money[$r] = $b_input_zen_money[$r] + $b_input_kei_money[$r] - $b_input_kai_money[$r];
}
// ����
$input_ym     = $target_ym;       // SQL��ͭ���� ���դγ�Ǽ
$zen_ym       = $b_target_ym;     // SQL��ͭ���� ����۹�����
for ($r=0; $r<$code_num; $r++) {
    // ������λĹ������������۶�ۤ�
    if ($zen_ym > 201703) {         // 201705�ʹߤ��̾�׻�
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_zen_money[$r] = 0;
            } else {
                $input_zen_money[$r] = $res[0][0];
            }
        }
    } else {                                // 201704������Υǡ������ʤ��ΤǶ�������
        if ($input_code[$r] == '00001') {   // NK�ξ��
            $input_zen_money[$r] = 427126; // 201704������۹�
        } else if ($input_code[$r] == '00004') {   // MT�ξ��
            $input_zen_money[$r] = 0; // 201704������۹�
        } else if ($input_code[$r] == '00101') {   // NKIT�ξ��
            $input_zen_money[$r] = 0; // 201704������۹�
        } else {                                   // ����ʳ��ʸ������Ǥ�SNK��
            $input_zen_money[$r] = 0; // 201704������۹�
        }
    }
    // ����ȯ�������
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    }
    // �����ù�
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    }
    // �Ǹ�˻Ĺ�׻�
    $input_zan_money[$r] = $input_zen_money[$r] + $input_kei_money[$r] - $input_kai_money[$r];
}

// ̤��������Ͽ
// �����˹�碌�ưʲ����ѹ����뤳��
// SQL��ͭ���� �����襳����
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
// SQL��ͭ���� ����̾
$code_num  = 4;                 // �����襳���ɿ� 4
// ����
$input_ym     = $b_target_ym;       // SQL��ͭ���� ���դγ�Ǽ
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// ��Ͽ�ʤ� insert ���� 
        $query = getQueryStatement16($input_ym, $input_code[$r], $b_input_zen_money[$r], $b_input_kei_money[$r], $b_input_kai_money[$r], $b_input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// ��Ͽ���� update ����
        $query = getQueryStatement17($input_ym, $input_code[$r], $b_input_zen_money[$r], $b_input_kei_money[$r], $b_input_kai_money[$r], $b_input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
// ����
$input_ym     = $target_ym;       // SQL��ͭ���� ���դγ�Ǽ
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// ��Ͽ�ʤ� insert ���� 
        $query = getQueryStatement16($input_ym, $input_code[$r], $input_zen_money[$r], $input_kei_money[$r], $input_kai_money[$r], $input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// ��Ͽ���� update ����
        $query = getQueryStatement17($input_ym, $input_code[$r], $input_zen_money[$r], $input_kei_money[$r], $input_kai_money[$r], $input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
// Ω�ض�׻�
// ��������
// SQL��ͭ���� �����襳����
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
$code_num    = 4;                 // �����襳���ɿ� 4

$input_zen_money   = array();    // �����ݴ���
$b_input_zen_money = array();    // ������ݴ���
$input_kei_money   = array();    // �׾��ݴ���
$b_input_kei_money = array();    // ����׾��ݴ���
$input_kai_money   = array();    // ��ù��ݴ���
$b_input_kai_money = array();    // �����ù��ݴ���
$input_zan_money   = array();    // �Ĺ��ݴ���
$b_input_zan_money = array();    // ����Ĺ��ݴ���

// ����
$input_ym     = $b_target_ym;       // SQL��ͭ���� ���դγ�Ǽ
$zen_ym       = $bb_target_ym;      // SQL��ͭ���� ����۹�����
$kamoku = 'Ω�ض�';
$account      = '1505';
for ($r=0; $r<$code_num; $r++) {
    // ������λĹ������������۶�ۤ�
    if ($zen_ym > 201703) {         // 201705�ʹߤ��̾�׻�
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_zen_money[$r] = 0;
            } else {
                $b_input_zen_money[$r] = $res[0][0];
            }
        }
    } else {                                // 201704������Υǡ������ʤ��ΤǶ�������
        if ($input_code[$r] == '00001') {   // NK�ξ��
            $b_input_zen_money[$r] = 12838542; // 201704������۹�
        } else if ($input_code[$r] == '00004') {   // MT�ξ��
            $b_input_zen_money[$r] = 60440; // 201704������۹�
        } else if ($input_code[$r] == '00101') {   // NKIT�ξ��
            $b_input_zen_money[$r] = 0; // 201704������۹�
        } else {                                   // ����ʳ��ʸ������Ǥ�SNK��
            $b_input_zen_money[$r] = 17320; // 201704������۹�
        }
    }
    // ����ȯ�������
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    }
    // �����ù�
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    }
    // �Ǹ�˻Ĺ�׻�
    $b_input_zan_money[$r] = $b_input_zen_money[$r] + $b_input_kei_money[$r] - $b_input_kai_money[$r];
}
// ����
$input_ym     = $target_ym;       // SQL��ͭ���� ���դγ�Ǽ
$zen_ym       = $b_target_ym;     // SQL��ͭ���� ����۹�����
for ($r=0; $r<$code_num; $r++) {
    // ������λĹ������������۶�ۤ�
    if ($zen_ym > 201703) {         // 201705�ʹߤ��̾�׻�
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_zen_money[$r] = 0;
            } else {
                $input_zen_money[$r] = $res[0][0];
            }
        }
    } else {                                // 201704������Υǡ������ʤ��ΤǶ�������
        if ($input_code[$r] == '00001') {   // NK�ξ��
            $input_zen_money[$r] = 12838542; // 201704������۹�
        } else if ($input_code[$r] == '00004') {   // MT�ξ��
            $input_zen_money[$r] = 60440; // 201704������۹�
        } else if ($input_code[$r] == '00101') {   // NKIT�ξ��
            $input_zen_money[$r] = 0; // 201704������۹�
        } else {                                   // ����ʳ��ʸ������Ǥ�SNK��
            $input_zen_money[$r] = 17320; // 201704������۹�
        }
    }
    // ����ȯ�������
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    }
    // �����ù�
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    }
    // �Ǹ�˻Ĺ�׻�
    $input_zan_money[$r] = $input_zen_money[$r] + $input_kei_money[$r] - $input_kai_money[$r];
}

// Ω�ض���Ͽ
// �����˹�碌�ưʲ����ѹ����뤳��
// SQL��ͭ���� �����襳����
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
// SQL��ͭ���� ����̾
$code_num  = 4;                 // �����襳���ɿ� 4
// ����
$input_ym     = $b_target_ym;       // SQL��ͭ���� ���դγ�Ǽ
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// ��Ͽ�ʤ� insert ���� 
        $query = getQueryStatement16($input_ym, $input_code[$r], $b_input_zen_money[$r], $b_input_kei_money[$r], $b_input_kai_money[$r], $b_input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// ��Ͽ���� update ����
        $query = getQueryStatement17($input_ym, $input_code[$r], $b_input_zen_money[$r], $b_input_kei_money[$r], $b_input_kai_money[$r], $b_input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
// ����
$input_ym     = $target_ym;       // SQL��ͭ���� ���դγ�Ǽ
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// ��Ͽ�ʤ� insert ���� 
        $query = getQueryStatement16($input_ym, $input_code[$r], $input_zen_money[$r], $input_kei_money[$r], $input_kai_money[$r], $input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// ��Ͽ���� update ����
        $query = getQueryStatement17($input_ym, $input_code[$r], $input_zen_money[$r], $input_kei_money[$r], $input_kai_money[$r], $input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
// ̤ʧ��׻�
// ��������
// SQL��ͭ���� �����襳����
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
$code_num    = 4;                 // �����襳���ɿ� 4

$input_zen_money   = array();    // �����ݴ���
$b_input_zen_money = array();    // ������ݴ���
$input_kei_money   = array();    // �׾��ݴ���
$b_input_kei_money = array();    // ����׾��ݴ���
$input_kai_money   = array();    // ��ù��ݴ���
$b_input_kai_money = array();    // �����ù��ݴ���
$input_zan_money   = array();    // �Ĺ��ݴ���
$b_input_zan_money = array();    // ����Ĺ��ݴ���

// ����
$input_ym     = $b_target_ym;       // SQL��ͭ���� ���դγ�Ǽ
$zen_ym       = $bb_target_ym;      // SQL��ͭ���� ����۹�����
$kamoku       = '̤ʧ��';
$account      = '3105';
for ($r=0; $r<$code_num; $r++) {
    // ������λĹ������������۶�ۤ�
    if ($zen_ym > 201703) {         // 201705�ʹߤ��̾�׻�
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_zen_money[$r] = 0;
            } else {
                $b_input_zen_money[$r] = $res[0][0];
            }
        }
    } else {                                // 201704������Υǡ������ʤ��ΤǶ�������
        if ($input_code[$r] == '00001') {   // NK�ξ��
            $b_input_zen_money[$r] = 0; // 201704������۹�
        } else if ($input_code[$r] == '00004') {   // MT�ξ��
            $b_input_zen_money[$r] = 0; // 201704������۹�
        } else if ($input_code[$r] == '00101') {   // NKIT�ξ��
            $b_input_zen_money[$r] = 0; // 201704������۹�
        } else {                                   // ����ʳ��ʸ������Ǥ�SNK��
            $b_input_zen_money[$r] = 0; // 201704������۹�
        }
    }
    // ����ȯ�������
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    }
    // �����ù�
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    }
    // �Ǹ�˻Ĺ�׻�
    $b_input_zan_money[$r] = $b_input_zen_money[$r] + $b_input_kei_money[$r] - $b_input_kai_money[$r];
}
// ����
$input_ym     = $target_ym;       // SQL��ͭ���� ���դγ�Ǽ
$zen_ym       = $b_target_ym;     // SQL��ͭ���� ����۹�����
for ($r=0; $r<$code_num; $r++) {
    // ������λĹ������������۶�ۤ�
    if ($zen_ym > 201703) {         // 201705�ʹߤ��̾�׻�
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_zen_money[$r] = 0;
            } else {
                $input_zen_money[$r] = $res[0][0];
            }
        }
    } else {                                // 201704������Υǡ������ʤ��ΤǶ�������
        if ($input_code[$r] == '00001') {   // NK�ξ��
            $input_zen_money[$r] = 0; // 201704������۹�
        } else if ($input_code[$r] == '00004') {   // MT�ξ��
            $input_zen_money[$r] = 0; // 201704������۹�
        } else if ($input_code[$r] == '00101') {   // NKIT�ξ��
            $input_zen_money[$r] = 0; // 201704������۹�
        } else {                                   // ����ʳ��ʸ������Ǥ�SNK��
            $input_zen_money[$r] = 0; // 201704������۹�
        }
    }
    // ����ȯ�������
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    }
    // �����ù�
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    }
    // �Ǹ�˻Ĺ�׻�
    $input_zan_money[$r] = $input_zen_money[$r] + $input_kei_money[$r] - $input_kai_money[$r];
}

// ̤ʧ����Ͽ
// �����˹�碌�ưʲ����ѹ����뤳��
// SQL��ͭ���� �����襳����
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
// SQL��ͭ���� ����̾
$code_num  = 4;                 // �����襳���ɿ� 4
// ����
$input_ym     = $b_target_ym;       // SQL��ͭ���� ���դγ�Ǽ
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// ��Ͽ�ʤ� insert ���� 
        $query = getQueryStatement16($input_ym, $input_code[$r], $b_input_zen_money[$r], $b_input_kei_money[$r], $b_input_kai_money[$r], $b_input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// ��Ͽ���� update ����
        $query = getQueryStatement17($input_ym, $input_code[$r], $b_input_zen_money[$r], $b_input_kei_money[$r], $b_input_kai_money[$r], $b_input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
// ����
$input_ym     = $target_ym;       // SQL��ͭ���� ���դγ�Ǽ
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// ��Ͽ�ʤ� insert ���� 
        $query = getQueryStatement16($input_ym, $input_code[$r], $input_zen_money[$r], $input_kei_money[$r], $input_kai_money[$r], $input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// ��Ͽ���� update ����
        $query = getQueryStatement17($input_ym, $input_code[$r], $input_zen_money[$r], $input_kei_money[$r], $input_kai_money[$r], $input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
// ̤ʧ���ѷ׻�
// ��������
// SQL��ͭ���� �����襳����
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
$code_num    = 4;                 // �����襳���ɿ� 4

$input_zen_money   = array();    // �����ݴ���
$b_input_zen_money = array();    // ������ݴ���
$input_kei_money   = array();    // �׾��ݴ���
$b_input_kei_money = array();    // ����׾��ݴ���
$input_kai_money   = array();    // ��ù��ݴ���
$b_input_kai_money = array();    // �����ù��ݴ���
$input_zan_money   = array();    // �Ĺ��ݴ���
$b_input_zan_money = array();    // ����Ĺ��ݴ���

// ����
$input_ym     = $b_target_ym;       // SQL��ͭ���� ���դγ�Ǽ
$zen_ym       = $bb_target_ym;      // SQL��ͭ���� ����۹�����
$kamoku       = '̤ʧ����';
$account      = '3224';
for ($r=0; $r<$code_num; $r++) {
    // ������λĹ������������۶�ۤ�
    if ($zen_ym > 201703) {         // 201705�ʹߤ��̾�׻�
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_zen_money[$r] = 0;
            } else {
                $b_input_zen_money[$r] = $res[0][0];
            }
        }
    } else {                                // 201704������Υǡ������ʤ��ΤǶ�������
        if ($input_code[$r] == '00001') {   // NK�ξ��
            $b_input_zen_money[$r] = 1420678; // 201704������۹�
        } else if ($input_code[$r] == '00004') {   // MT�ξ��
            $b_input_zen_money[$r] = 0; // 201704������۹�
        } else if ($input_code[$r] == '00101') {   // NKIT�ξ��
            $b_input_zen_money[$r] = 0; // 201704������۹�
        } else {                                   // ����ʳ��ʸ������Ǥ�SNK��
            $b_input_zen_money[$r] = 0; // 201704������۹�
        }
    }
    // ����ȯ�������
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    }
    // �����ù�
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    }
    // �Ǹ�˻Ĺ�׻�
    $b_input_zan_money[$r] = $b_input_zen_money[$r] + $b_input_kei_money[$r] - $b_input_kai_money[$r];
}
// ����
$input_ym     = $target_ym;       // SQL��ͭ���� ���դγ�Ǽ
$zen_ym       = $b_target_ym;     // SQL��ͭ���� ����۹�����
for ($r=0; $r<$code_num; $r++) {
    // ������λĹ������������۶�ۤ�
    if ($zen_ym > 201703) {         // 201705�ʹߤ��̾�׻�
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_zen_money[$r] = 0;
            } else {
                $input_zen_money[$r] = $res[0][0];
            }
        }
    } else {                                // 201704������Υǡ������ʤ��ΤǶ�������
        if ($input_code[$r] == '00001') {   // NK�ξ��
            $input_zen_money[$r] = 1420678; // 201704������۹�
        } else if ($input_code[$r] == '00004') {   // MT�ξ��
            $input_zen_money[$r] = 0; // 201704������۹�
        } else if ($input_code[$r] == '00101') {   // NKIT�ξ��
            $input_zen_money[$r] = 0; // 201704������۹�
        } else {                                   // ����ʳ��ʸ������Ǥ�SNK��
            $input_zen_money[$r] = 0; // 201704������۹�
        }
    }
    // ����ȯ�������
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    }
    // �����ù�
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    }
    // �Ǹ�˻Ĺ�׻�
    $input_zan_money[$r] = $input_zen_money[$r] + $input_kei_money[$r] - $input_kai_money[$r];
}

// ̤ʧ������Ͽ
// �����˹�碌�ưʲ����ѹ����뤳��
// SQL��ͭ���� �����襳����
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
// SQL��ͭ���� ����̾
$code_num  = 4;                 // �����襳���ɿ� 4
// ����
$input_ym     = $b_target_ym;       // SQL��ͭ���� ���դγ�Ǽ
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// ��Ͽ�ʤ� insert ���� 
        $query = getQueryStatement16($input_ym, $input_code[$r], $b_input_zen_money[$r], $b_input_kei_money[$r], $b_input_kai_money[$r], $b_input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// ��Ͽ���� update ����
        $query = getQueryStatement17($input_ym, $input_code[$r], $b_input_zen_money[$r], $b_input_kei_money[$r], $b_input_kai_money[$r], $b_input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
// ����
$input_ym     = $target_ym;       // SQL��ͭ���� ���դγ�Ǽ
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// ��Ͽ�ʤ� insert ���� 
        $query = getQueryStatement16($input_ym, $input_code[$r], $input_zen_money[$r], $input_kei_money[$r], $input_kai_money[$r], $input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// ��Ͽ���� update ����
        $query = getQueryStatement17($input_ym, $input_code[$r], $input_zen_money[$r], $input_kei_money[$r], $input_kai_money[$r], $input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
// ͭ���ٵ�̤������׻�
// ��������
// SQL��ͭ���� �����襳����
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
$code_num    = 4;                 // �����襳���ɿ� 4

$input_zen_money   = array();    // �����ݴ���
$b_input_zen_money = array();    // ������ݴ���
$input_kei_money   = array();    // �׾��ݴ���
$b_input_kei_money = array();    // ����׾��ݴ���
$input_kai_money   = array();    // ��ù��ݴ���
$b_input_kai_money = array();    // �����ù��ݴ���
$input_zan_money   = array();    // �Ĺ��ݴ���
$b_input_zan_money = array();    // ����Ĺ��ݴ���

// ����
$input_ym     = $b_target_ym;       // SQL��ͭ���� ���դγ�Ǽ
$zen_ym       = $bb_target_ym;      // SQL��ͭ���� ����۹�����
$kamoku       = 'ͭ���ٵ�̤������';
$account      = '1302';
for ($r=0; $r<$code_num; $r++) {
    // ������λĹ������������۶�ۤ�
    if ($zen_ym > 201703) {         // 201705�ʹߤ��̾�׻�
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_zen_money[$r] = 0;
            } else {
                $b_input_zen_money[$r] = $res[0][0];
            }
        }
    } else {                                // 201704������Υǡ������ʤ��ΤǶ�������
        if ($input_code[$r] == '00001') {   // NK�ξ��
            $b_input_zen_money[$r] = 0; // 201704������۹�
        } else if ($input_code[$r] == '00004') {   // MT�ξ��
            $b_input_zen_money[$r] = 0; // 201704������۹�
        } else if ($input_code[$r] == '00101') {   // NKIT�ξ��
            $b_input_zen_money[$r] = 13620603; // 201704������۹�
        } else {                                   // ����ʳ��ʸ������Ǥ�SNK��
            $b_input_zen_money[$r] = 0; // 201704������۹�
        }
    }
    // ����ȯ�������
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    }
    // �����ù�
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    }
    // �Ǹ�˻Ĺ�׻�
    $b_input_zan_money[$r] = $b_input_zen_money[$r] + $b_input_kei_money[$r] - $b_input_kai_money[$r];
}
// ����
$input_ym     = $target_ym;       // SQL��ͭ���� ���դγ�Ǽ
$zen_ym       = $b_target_ym;     // SQL��ͭ���� ����۹�����
for ($r=0; $r<$code_num; $r++) {
    // ������λĹ������������۶�ۤ�
    if ($zen_ym > 201703) {         // 201705�ʹߤ��̾�׻�
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_zen_money[$r] = 0;
            } else {
                $input_zen_money[$r] = $res[0][0];
            }
        }
    } else {                                // 201704������Υǡ������ʤ��ΤǶ�������
        if ($input_code[$r] == '00001') {   // NK�ξ��
            $input_zen_money[$r] = 0; // 201704������۹�
        } else if ($input_code[$r] == '00004') {   // MT�ξ��
            $input_zen_money[$r] = 0; // 201704������۹�
        } else if ($input_code[$r] == '00101') {   // NKIT�ξ��
            $input_zen_money[$r] = 13620603; // 201704������۹�
        } else {                                   // ����ʳ��ʸ������Ǥ�SNK��
            $input_zen_money[$r] = 0; // 201704������۹�
        }
    }
    // ����ȯ�������
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    }
    // �����ù�
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    }
    // �Ǹ�˻Ĺ�׻�
    $input_zan_money[$r] = $input_zen_money[$r] + $input_kei_money[$r] - $input_kai_money[$r];
}

// ͭ���ٵ�̤��������Ͽ
// �����˹�碌�ưʲ����ѹ����뤳��
// SQL��ͭ���� �����襳����
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
// SQL��ͭ���� ����̾
$code_num  = 4;                 // �����襳���ɿ� 4
// ����
$input_ym     = $b_target_ym;       // SQL��ͭ���� ���դγ�Ǽ
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// ��Ͽ�ʤ� insert ���� 
        $query = getQueryStatement16($input_ym, $input_code[$r], $b_input_zen_money[$r], $b_input_kei_money[$r], $b_input_kai_money[$r], $b_input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// ��Ͽ���� update ����
        $query = getQueryStatement17($input_ym, $input_code[$r], $b_input_zen_money[$r], $b_input_kei_money[$r], $b_input_kai_money[$r], $b_input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
// ����
$input_ym     = $target_ym;       // SQL��ͭ���� ���դγ�Ǽ
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// ��Ͽ�ʤ� insert ���� 
        $query = getQueryStatement16($input_ym, $input_code[$r], $input_zen_money[$r], $input_kei_money[$r], $input_kai_money[$r], $input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// ��Ͽ���� update ����
        $query = getQueryStatement17($input_ym, $input_code[$r], $input_zen_money[$r], $input_kei_money[$r], $input_kai_money[$r], $input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
// ������׻�
// ��������
// SQL��ͭ���� �����襳����
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
$code_num    = 4;                 // �����襳���ɿ� 4

$input_zen_money   = array();    // �����ݴ���
$b_input_zen_money = array();    // ������ݴ���
$input_kei_money   = array();    // �׾��ݴ���
$b_input_kei_money = array();    // ����׾��ݴ���
$input_kai_money   = array();    // ��ù��ݴ���
$b_input_kai_money = array();    // �����ù��ݴ���
$input_zan_money   = array();    // �Ĺ��ݴ���
$b_input_zan_money = array();    // ����Ĺ��ݴ���

// ����
$input_ym     = $b_target_ym;       // SQL��ͭ���� ���դγ�Ǽ
$zen_ym       = $bb_target_ym;      // SQL��ͭ���� ����۹�����
$kamoku       = '������';
$account      = '3221';
for ($r=0; $r<$code_num; $r++) {
    // ������λĹ������������۶�ۤ�
    if ($zen_ym > 201703) {         // 201705�ʹߤ��̾�׻�
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_zen_money[$r] = 0;
            } else {
                $b_input_zen_money[$r] = $res[0][0];
            }
        }
    } else {                                // 201704������Υǡ������ʤ��ΤǶ�������
        if ($input_code[$r] == '00001') {   // NK�ξ��
            $b_input_zen_money[$r] = 0; // 201704������۹�
        } else if ($input_code[$r] == '00004') {   // MT�ξ��
            $b_input_zen_money[$r] = 0; // 201704������۹�
        } else if ($input_code[$r] == '00101') {   // NKIT�ξ��
            $b_input_zen_money[$r] = 0; // 201704������۹�
        } else {                                   // ����ʳ��ʸ������Ǥ�SNK��
            $b_input_zen_money[$r] = 0; // 201704������۹�
        }
    }
    // ����ȯ�������
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    }
    // �����ù�
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    }
    // �Ǹ�˻Ĺ�׻�
    $b_input_zan_money[$r] = $b_input_zen_money[$r] + $b_input_kei_money[$r] - $b_input_kai_money[$r];
}
// ����
$input_ym     = $target_ym;       // SQL��ͭ���� ���դγ�Ǽ
$zen_ym       = $b_target_ym;     // SQL��ͭ���� ����۹�����
for ($r=0; $r<$code_num; $r++) {
    // ������λĹ������������۶�ۤ�
    if ($zen_ym > 201703) {         // 201705�ʹߤ��̾�׻�
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_zen_money[$r] = 0;
            } else {
                $input_zen_money[$r] = $res[0][0];
            }
        }
    } else {                                // 201704������Υǡ������ʤ��ΤǶ�������
        if ($input_code[$r] == '00001') {   // NK�ξ��
            $input_zen_money[$r] = 0; // 201704������۹�
        } else if ($input_code[$r] == '00004') {   // MT�ξ��
            $input_zen_money[$r] = 0; // 201704������۹�
        } else if ($input_code[$r] == '00101') {   // NKIT�ξ��
            $input_zen_money[$r] = 0; // 201704������۹�
        } else {                                   // ����ʳ��ʸ������Ǥ�SNK��
            $input_zen_money[$r] = 0; // 201704������۹�
        }
    }
    // ����ȯ�������
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    }
    // �����ù�
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    }
    // �Ǹ�˻Ĺ�׻�
    $input_zan_money[$r] = $input_zen_money[$r] + $input_kei_money[$r] - $input_kai_money[$r];
}

// ��������Ͽ
// �����˹�碌�ưʲ����ѹ����뤳��
// SQL��ͭ���� �����襳����
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
// SQL��ͭ���� ����̾
$code_num  = 4;                 // �����襳���ɿ� 4
// ����
$input_ym     = $b_target_ym;       // SQL��ͭ���� ���դγ�Ǽ
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// ��Ͽ�ʤ� insert ���� 
        $query = getQueryStatement16($input_ym, $input_code[$r], $b_input_zen_money[$r], $b_input_kei_money[$r], $b_input_kai_money[$r], $b_input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// ��Ͽ���� update ����
        $query = getQueryStatement17($input_ym, $input_code[$r], $b_input_zen_money[$r], $b_input_kei_money[$r], $b_input_kai_money[$r], $b_input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
// ����
$input_ym     = $target_ym;       // SQL��ͭ���� ���դγ�Ǽ
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// ��Ͽ�ʤ� insert ���� 
        $query = getQueryStatement16($input_ym, $input_code[$r], $input_zen_money[$r], $input_kei_money[$r], $input_kai_money[$r], $input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// ��Ͽ���� update ����
        $query = getQueryStatement17($input_ym, $input_code[$r], $input_zen_money[$r], $input_kei_money[$r], $input_kai_money[$r], $input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
// �¤��׻�
// ��������
// SQL��ͭ���� �����襳����
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
$code_num    = 4;                 // �����襳���ɿ� 4

$input_zen_money   = array();    // �����ݴ���
$b_input_zen_money = array();    // ������ݴ���
$input_kei_money   = array();    // �׾��ݴ���
$b_input_kei_money = array();    // ����׾��ݴ���
$input_kai_money   = array();    // ��ù��ݴ���
$b_input_kai_money = array();    // �����ù��ݴ���
$input_zan_money   = array();    // �Ĺ��ݴ���
$b_input_zan_money = array();    // ����Ĺ��ݴ���

// ����
$input_ym     = $b_target_ym;       // SQL��ͭ���� ���դγ�Ǽ
$zen_ym       = $bb_target_ym;      // SQL��ͭ���� ����۹�����
$kamoku       = '�¤��';
$account      = '3222';
for ($r=0; $r<$code_num; $r++) {
    // ������λĹ������������۶�ۤ�
    if ($zen_ym > 201703) {         // 201705�ʹߤ��̾�׻�
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_zen_money[$r] = 0;
            } else {
                $b_input_zen_money[$r] = $res[0][0];
            }
        }
    } else {                                // 201704������Υǡ������ʤ��ΤǶ�������
        if ($input_code[$r] == '00001') {   // NK�ξ��
            $b_input_zen_money[$r] = 0; // 201704������۹�
        } else if ($input_code[$r] == '00004') {   // MT�ξ��
            $b_input_zen_money[$r] = 0; // 201704������۹�
        } else if ($input_code[$r] == '00101') {   // NKIT�ξ��
            $b_input_zen_money[$r] = 0; // 201704������۹�
        } else {                                   // ����ʳ��ʸ������Ǥ�SNK��
            $b_input_zen_money[$r] = 0; // 201704������۹�
        }
    }
    // ����ȯ�������
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    }
    // �����ù�
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    }
    // �Ǹ�˻Ĺ�׻�
    $b_input_zan_money[$r] = $b_input_zen_money[$r] + $b_input_kei_money[$r] - $b_input_kai_money[$r];
}
// ����
$input_ym     = $target_ym;       // SQL��ͭ���� ���դγ�Ǽ
$zen_ym       = $b_target_ym;     // SQL��ͭ���� ����۹�����
for ($r=0; $r<$code_num; $r++) {
    // ������λĹ������������۶�ۤ�
    if ($zen_ym > 201703) {         // 201705�ʹߤ��̾�׻�
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_zen_money[$r] = 0;
            } else {
                $input_zen_money[$r] = $res[0][0];
            }
        }
    } else {                                // 201704������Υǡ������ʤ��ΤǶ�������
        if ($input_code[$r] == '00001') {   // NK�ξ��
            $input_zen_money[$r] = 0; // 201704������۹�
        } else if ($input_code[$r] == '00004') {   // MT�ξ��
            $input_zen_money[$r] = 0; // 201704������۹�
        } else if ($input_code[$r] == '00101') {   // NKIT�ξ��
            $input_zen_money[$r] = 0; // 201704������۹�
        } else {                                   // ����ʳ��ʸ������Ǥ�SNK��
            $input_zen_money[$r] = 0; // 201704������۹�
        }
    }
    // ����ȯ�������
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    }
    // �����ù�
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    }
    // �Ǹ�˻Ĺ�׻�
    $input_zan_money[$r] = $input_zen_money[$r] + $input_kei_money[$r] - $input_kai_money[$r];
}

// �¤����Ͽ
// �����˹�碌�ưʲ����ѹ����뤳��
// SQL��ͭ���� �����襳����
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
// SQL��ͭ���� ����̾
$code_num  = 4;                 // �����襳���ɿ� 4
// ����
$input_ym     = $b_target_ym;       // SQL��ͭ���� ���դγ�Ǽ
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// ��Ͽ�ʤ� insert ���� 
        $query = getQueryStatement16($input_ym, $input_code[$r], $b_input_zen_money[$r], $b_input_kei_money[$r], $b_input_kai_money[$r], $b_input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// ��Ͽ���� update ����
        $query = getQueryStatement17($input_ym, $input_code[$r], $b_input_zen_money[$r], $b_input_kei_money[$r], $b_input_kai_money[$r], $b_input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
// ����
$input_ym     = $target_ym;       // SQL��ͭ���� ���դγ�Ǽ
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// ��Ͽ�ʤ� insert ���� 
        $query = getQueryStatement16($input_ym, $input_code[$r], $input_zen_money[$r], $input_kei_money[$r], $input_kai_money[$r], $input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// ��Ͽ���� update ����
        $query = getQueryStatement17($input_ym, $input_code[$r], $input_zen_money[$r], $input_kei_money[$r], $input_kai_money[$r], $input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');
fclose($fpa);      ////// �����ѥ�����߽�λ
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߽�λ

    ///// ���ס����Ѵ�ϢSQL���ơ��ȥ��ȼ���
    // ��ʣ�����å�SQL
    function getQueryStatement1($input_ym, $input_code, $input_kamoku)
    {
       $query = "SELECT
                        revenue_money
                    FROM
                        link_trans_revenue_history
                    WHERE revenue_ym={$input_ym} and revenue_code='{$input_code}' and revenue_kamoku='{$input_kamoku}'
        ";
        return $query;
    }
    
    // insert SQL
    function getQueryStatement2($input_ym, $input_code, $input_kamoku, $input_money)
    {
        $query = "INSERT INTO 
                        link_trans_revenue_history (revenue_code, revenue_ym, revenue_kamoku, revenue_money)
                    VALUES(
                        '{$input_code}',
                         {$input_ym} ,
                        '{$input_kamoku}',
                         {$input_money})
        ";
        return $query;
    }
    // UPDATE SQL
    function getQueryStatement3($input_ym, $input_code, $input_kamoku, $input_money)
    {
        $query = "UPDATE 
                        link_trans_revenue_history 
                    SET
                        revenue_money = {$input_money}
                    WHERE revenue_ym={$input_ym} and revenue_code='{$input_code}' and revenue_kamoku='{$input_kamoku}'
        ";
        return $query;
    }
    ///// ��塦����SQL���ơ��ȥ��ȼ���
    // ����ǡ�������SQL
    function getQueryStatement4($d_start, $d_end, $customer, $div)
    {
        $query = "select
                        sum(Uround(����*ñ��,0)) as t_kingaku
                  from
                        hiuuri
                  left outer join
                        product_support_master AS groupm
                  on assyno=groupm.assy_no
                  left outer join
                        miitem as m
                  on assyno=m.mipn";

        $search = "where �׾���>=$d_start and �׾���<=$d_end";
        $search .= " and ������='{$customer}'";
        if ($div == "C") {
            $search .= " and ������='$div'";
            $search .= " and (assyno not like 'NKB%%')";
            $search .= " and (assyno not like 'SS%%')";
        } elseif ($div == "L") {
            $search .= " and ������='$div'";
            $search .= " and (assyno not like 'SS%%')";
        } elseif ($div == "SSL") {   // ��˥���������ξ��� assyno �ǥ����å�
            $search .= " and ������='L' and (assyno like 'SS%%')";
        } elseif ($div != " ") {
            $search .= " and ������='$div'";
        }
        $query = sprintf("$query %s", $search);     // SQL query ʸ�δ���
        return $query;
    }
    // ��ʣ�����å�SQL
    function getQueryStatement5($input_ym, $input_code, $input_kamoku)
    {
       $query = "SELECT
                        sales_c_money
                    FROM
                        link_trans_sales_history
                    WHERE sales_ym={$input_ym} and sales_code='{$input_code}' and sales_kamoku='{$input_kamoku}'
        ";
        return $query;
    }
    
    // insert SQL
    function getQueryStatement6($input_ym, $input_code, $input_div, $input_money, $kamoku)
    {
        if ($input_div == 'C') {
            $query = "INSERT INTO 
                            link_trans_sales_history (sales_code, sales_ym, sales_kamoku, sales_c_money)
                        VALUES(
                            '{$input_code}',
                             {$input_ym} ,
                            '{$kamoku}',
                            {$input_money})
            ";
        } elseif($input_div == 'L') {
            $query = "INSERT INTO 
                            link_trans_sales_history (sales_code, sales_ym, sales_kamoku, sales_l_money)
                        VALUES(
                            '{$input_code}',
                             {$input_ym} ,
                            '{$kamoku}',
                            {$input_money})
            ";
        } elseif($input_div == 'T') {
            $query = "INSERT INTO 
                            link_trans_sales_history (sales_code, sales_ym, sales_kamoku, sales_t_money)
                        VALUES(
                            '{$input_code}',
                             {$input_ym} ,
                            '{$kamoku}',
                            {$input_money})
            ";
        } elseif($input_div == 'SSL') {
            $query = "INSERT INTO 
                            link_trans_sales_history (sales_code, sales_ym, sales_kamoku, sales_s_money)
                        VALUES(
                            '{$input_code}',
                             {$input_ym} ,
                            '{$kamoku}',
                            {$input_money})
            ";
        } elseif($input_div == 'NKB') {
            $query = "INSERT INTO 
                            link_trans_sales_history (sales_code, sales_ym, sales_kamoku, sales_b_money)
                        VALUES(
                            '{$input_code}',
                             {$input_ym} ,
                            '{$kamoku}',
                            {$input_money})
            ";
        } elseif($input_div == 'TOTAL') {
            $query = "INSERT INTO 
                            link_trans_sales_history (sales_code, sales_ym, sales_kamoku, sales_to_money)
                        VALUES(
                            '{$input_code}',
                             {$input_ym} ,
                            '{$kamoku}',
                            {$input_money})
            ";
        }
        
        return $query;
    }
    // UPDATE SQL
    function getQueryStatement7($input_ym, $input_code, $input_div, $input_money, $kamoku)
    {
        if ($input_div == 'C') {
            $query = "UPDATE 
                            link_trans_sales_history
                        SET
                            sales_c_money = {$input_money}
                        WHERE  sales_ym={$input_ym} and sales_code='{$input_code}' and sales_kamoku='{$kamoku}'
            ";
        } elseif($input_div == 'L') {
            $query = "UPDATE 
                            link_trans_sales_history
                        SET
                            sales_l_money = {$input_money}
                        WHERE  sales_ym={$input_ym} and sales_code='{$input_code}' and sales_kamoku='{$kamoku}'
            ";
        } elseif($input_div == 'T') {
            $query = "UPDATE 
                            link_trans_sales_history
                        SET
                            sales_t_money = {$input_money}
                        WHERE  sales_ym={$input_ym} and sales_code='{$input_code}' and sales_kamoku='{$kamoku}'
            ";
        } elseif($input_div == 'SSL') {
            $query = "UPDATE 
                            link_trans_sales_history
                        SET
                            sales_s_money = {$input_money}
                        WHERE  sales_ym={$input_ym} and sales_code='{$input_code}' and sales_kamoku='{$kamoku}'
            ";
        } elseif($input_div == 'NKB') {
            $query = "UPDATE 
                            link_trans_sales_history
                        SET
                            sales_b_money = {$input_money}
                        WHERE  sales_ym={$input_ym} and sales_code='{$input_code}' and sales_kamoku='{$kamoku}'
            ";
        } elseif($input_div == 'TOTAL') {
            $query = "UPDATE 
                            link_trans_sales_history
                        SET
                            sales_to_money = {$input_money}
                        WHERE  sales_ym={$input_ym} and sales_code='{$input_code}' and sales_kamoku='{$kamoku}'
            ";
        }
        
        return $query;
    }
    // ������ǡ�������SQL
    function getQueryStatement8($d_start, $d_end, $payment_ym, $customer)
    {
        $query = "SELECT
                        SUM(Uround(order_price * siharai,0))
                    FROM
                        act_payable AS a
                    LEFT OUTER JOIN
                        vendor_master USING(vendor)
                    LEFT OUTER JOIN
                        miitem ON (parts_no = mipn)
                    LEFT OUTER JOIN
                        parts_stock_master AS m ON (m.parts_no=a.parts_no)
        ";

        $search = "WHERE uke_date>=$d_start and uke_date<=$d_end and h_pay_date=$payment_ym and vendor='{$customer}'";
        $query = sprintf("$query %s", $search);     // SQL query ʸ�δ���
        return $query;
    }
    // ��ʣ�����å�SQL
    function getQueryStatement9($input_ym, $input_code, $input_kamoku)
    {
       $query = "SELECT
                        sales_c_money
                    FROM
                        link_trans_sales_history
                    WHERE sales_ym={$input_ym} and sales_code='{$input_code}' and sales_kamoku='{$input_kamoku}'
        ";
        return $query;
    }
    
    // insert SQL
    function getQueryStatement10($input_ym, $input_code, $input_money, $kamoku)
    {
        if ($input_code == '00001') {
            $query = "INSERT INTO 
                            link_trans_sales_history (sales_code, sales_ym, sales_kamoku, sales_to_money)
                        VALUES(
                            '{$input_code}',
                             {$input_ym} ,
                            '{$kamoku}',
                            {$input_money})
            ";
        } elseif($input_code == '00005') {
            $query = "INSERT INTO 
                            link_trans_sales_history (sales_code, sales_ym, sales_kamoku, sales_t_money, sales_to_money)
                        VALUES(
                            '{$input_code}',
                             {$input_ym} ,
                            '{$kamoku}',
                            {$input_money} ,
                            {$input_money})
            ";
        } elseif($input_code == '00004') {
            $query = "INSERT INTO 
                            link_trans_sales_history (sales_code, sales_ym, sales_kamoku, sales_t_money, sales_to_money)
                        VALUES(
                            '{$input_code}',
                             {$input_ym} ,
                            '{$kamoku}',
                            {$input_money} ,
                            {$input_money})
            ";
        } elseif($input_code == '00101') {
            $query = "INSERT INTO 
                            link_trans_sales_history (sales_code, sales_ym, sales_kamoku, sales_c_money, sales_to_money)
                        VALUES(
                            '{$input_code}',
                             {$input_ym} ,
                            '{$kamoku}',
                            {$input_money} ,
                            {$input_money})
            ";
        }
        
        return $query;
    }
    // UPDATE SQL
    function getQueryStatement11($input_ym, $input_code, $input_money, $kamoku)
    {
        if ($input_code == '00001') {
            $query = "UPDATE 
                            link_trans_sales_history
                        SET
                            sales_t_money = {$input_money}
                        WHERE  sales_ym={$input_ym} and sales_code='{$input_code}' and sales_kamoku='{$kamoku}'
            ";
        } elseif($input_code == '00005') {
            $query = "UPDATE 
                            link_trans_sales_history
                        SET
                            sales_t_money  = {$input_money},
                            sales_to_money = {$input_money}
                        WHERE  sales_ym={$input_ym} and sales_code='{$input_code}' and sales_kamoku='{$kamoku}'
            ";
        } elseif($input_code == '00004') {
            $query = "UPDATE 
                            link_trans_sales_history
                        SET
                            sales_t_money  = {$input_money},
                            sales_to_money = {$input_money}
                        WHERE  sales_ym={$input_ym} and sales_code='{$input_code}' and sales_kamoku='{$kamoku}'
            ";
        } elseif($input_code == '00101') {
            $query = "UPDATE 
                            link_trans_sales_history
                        SET
                            sales_c_money  = {$input_money},
                            sales_to_money = {$input_money}
                        WHERE  sales_ym={$input_ym} and sales_code='{$input_code}' and sales_kamoku='{$kamoku}'
            ";
        }
        
        return $query;
    }
    // ��ݶ⡦�����Ϣ����
    // ����λĹ������Ƿ��۹�Ȥ���
    function getQueryStatement12($input_ym, $input_code, $input_kamoku)
    {
       $query = "SELECT
                        expense_zan
                    FROM
                        link_trans_expense_history
                    WHERE expense_ym={$input_ym} and expense_code='{$input_code}' and expense_kamoku='{$input_kamoku}'
        ";
        return $query;
    }
    // ��������� �� 1.08����ݶ��SQL
    function getQueryStatement13($input_ym, $input_code, $input_kamoku)
    {
       $query = "SELECT
                        sales_to_money
                    FROM
                        link_trans_sales_history
                    WHERE sales_ym={$input_ym} and sales_code='{$input_code}' and sales_kamoku='{$input_kamoku}'
        ";
        return $query;
    }
    
    // ��ݶ��껦�ۼ���
    function getQueryStatement14($str_ymd, $end_ymd, $input_code)
    {
       $query = "SELECT
                        -SUM(den_kin) 
                    FROM link_trans_offset
                    WHERE den_code='{$input_code}' and den_ymd>={$str_ymd} and den_ymd<={$end_ymd}
        ";
        return $query;
    }
    // ��ʣ�����å�SQL
    function getQueryStatement15($input_ym, $input_code, $input_kamoku)
    {
       $query = "SELECT
                        expense_zan
                    FROM
                        link_trans_expense_history
                    WHERE expense_ym={$input_ym} and expense_code='{$input_code}' and expense_kamoku='{$input_kamoku}'
        ";
        return $query;
    }
    
    // insert SQL
    function getQueryStatement16($input_ym, $input_code, $input_kuri, $input_kei, $input_kai, $input_zan, $kamoku)
    {
        $query = "INSERT INTO 
                            link_trans_expense_history (expense_code, expense_ym, expense_kamoku, expense_kuri, expense_kei, expense_kai, expense_zan)
                        VALUES(
                            '{$input_code}',
                             {$input_ym} ,
                            '{$kamoku}',
                            {$input_kuri},
                            {$input_kei},
                            {$input_kai},
                            {$input_zan})
        ";
        
        return $query;
    }
    // UPDATE SQL
    function getQueryStatement17($input_ym, $input_code, $input_kuri, $input_kei, $input_kai, $input_zan, $kamoku)
    {
        $query = "UPDATE 
                            link_trans_expense_history
                        SET
                            expense_kuri = {$input_kuri},
                            expense_kei  = {$input_kei},
                            expense_kai  = {$input_kai},
                            expense_zan  = {$input_zan}
                        WHERE  expense_ym={$input_ym} and expense_code='{$input_code}' and expense_kamoku='{$kamoku}'
        ";
        
        return $query;
    }
?>
