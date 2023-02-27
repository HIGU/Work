#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ���ؼ��إå������������٥ե����� FTP Download cron�ǽ���ͽ�� cgi��     //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2004-2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/06/29 Created  equip_work_inst_ftp.php                              //
// 2004/07/29 "rollback" �� 'rollback' ���ѹ�  4.3.7��4.3.8���ѹ�           //
// 2004/11/18 php-5.0.2-cli�إС�����󥢥å� *�����륹����ץȤ��б����ѹ� //
// 2004/12/13 #!/usr/local/bin/php-5.0.2-cli �� php (������5.0.3RC2)���ѹ�  //
// 2009/12/18 ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤ��ɲ�           ��ë //
// 2010/01/19 �᡼���ʬ����䤹������١����ա�����������ɲ�       ��ë //
// 2010/01/20 $log_date�������'�ǤϤʤ�"�ʤΤǽ���                    ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
ini_set('max_execution_time', 1200);    // ����¹Ի���=20ʬ
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');    ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤǥ����ץ�
fwrite($fpb, "���ؼ��Υإå��������٥ǡ����ι���\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/equip_work_inst_ftp.php\n");
echo "/home/www/html/tnk-web/system/daily/equip_work_inst_ftp.php\n";
        
// FTP�Υ������åȥե�����
define('D_MGIPRD', 'UKWLIB/W#MGIPRD');      // ���ؼ��إå����ե����� download file
define('D_MGIROT', 'UKWLIB/W#MGIROT');      // ���ؼ��������٥ե����� download file
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
define('S_MGIPRD', '/home/www/html/tnk-web/system/backup/W#MGIPRD.TXT');  // ���ؼ��إå����ե����� save file
define('S_MGIROT', '/home/www/html/tnk-web/system/backup/W#MGIROT.TXT');  // ���ؼ��������٥ե����� save file

// ���ͥ���������(FTP��³�Υ����ץ�)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// ���ؼ��إå����ե�����
        if (ftp_get($ftp_stream, S_MGIPRD, D_MGIPRD, FTP_ASCII)) {
            echo "$log_date ���ؼ��إå��� ftp_get download OK ", D_MGIPRD, "��", S_MGIPRD, "\n";
            fwrite($fpa,"$log_date ���ؼ��إå��� ftp_get download OK " . D_MGIPRD . '��' . S_MGIPRD . "\n");
            fwrite($fpb,"$log_date ���ؼ��إå��� ftp_get download OK " . D_MGIPRD . '��' . S_MGIPRD . "\n");
        } else {
            echo "$log_date ���ؼ��إå��� ftp_get() error ", D_MGIPRD, "\n";
            fwrite($fpa,"$log_date ���ؼ��إå��� ftp_get() error " . D_MGIPRD . "\n");
            fwrite($fpb,"$log_date ���ؼ��إå��� ftp_get() error " . D_MGIPRD . "\n");
        }
        ///// ���ؼ��������٥ե�����
        if (ftp_get($ftp_stream, S_MGIROT, D_MGIROT, FTP_ASCII)) {
            echo "$log_date ���ؼ��������� ftp_get download OK ", D_MGIROT, "��", S_MGIROT, "\n";
            fwrite($fpa,"$log_date ���ؼ��������� ftp_get download OK " . D_MGIROT . '��' . S_MGIROT . "\n");
            fwrite($fpb,"$log_date ���ؼ��������� ftp_get download OK " . D_MGIROT . '��' . S_MGIROT . "\n");
        } else {
            echo "$log_date ���ؼ��������� ftp_get() error ", D_MGIROT, "\n";
            fwrite($fpa,"$log_date ���ؼ��������� ftp_get() error " . D_MGIROT . "\n");
            fwrite($fpb,"$log_date ���ؼ��������� ftp_get() error " . D_MGIROT . "\n");
        }
    } else {
        echo "$log_date ���ؼ��������� ftp_login() error \n";
        fwrite($fpa,"$log_date ���ؼ��������� ftp_login() error \n");
        fwrite($fpb,"$log_date ���ؼ��������� ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    echo "$log_date ftp_connect() error --> ���ؼ�\n";
    fwrite($fpa,"$log_date ftp_connect() error --> ���ؼ�\n");
    fwrite($fpb,"$log_date ftp_connect() error --> ���ؼ�\n");
}



/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date ���ؼ��������� db_connect() error \n");
    fwrite($fpb, "$log_date ���ؼ��������� db_connect() error \n");
    echo "$log_date ���ؼ��������� db_connect() error \n";
    exit();
}
// ���ؼ��إå����ե����� �������
$file_orign  = S_MGIPRD;
$file_debug  = '/home/www/html/tnk-web/system/debug/debug-MGIPRD.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 200, '_');     // �¥쥳���ɤ�69�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���'_'�����
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num != 8) {
            if ($num == 0 || $num == 1) {   // php-4.3.5��0�֤� php-4.3.6��1���֤����ͤˤʤä���fgetcsv�λ����ѹ��ˤ��
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
                fwrite($fpb, "$log_date AS/400 del record=$rec \n");
                echo "$log_date AS/400 del record=$rec \n";
            } else {
                fwrite($fpa, "$log_date field not 8 record=$rec \n");
                fwrite($fpb, "$log_date field not 8 record=$rec \n");
                echo "$log_date field not 8 record=$rec \n";
            }
           continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        }
        
        $query_chk = sprintf("SELECT parts_no FROM equip_work_inst_header
                                WHERE inst_no=%d",
                                $data[0]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "INSERT INTO equip_work_inst_header (inst_no, parts_no, material, inst_date, delivery, inst_qt,
                            mate_kg, sei_no)
                      VALUES(
                         {$data[0]} ,
                        '{$data[1]}',
                        '{$data[2]}',
                         {$data[3]} ,
                         {$data[4]} ,
                         {$data[5]} ,
                         {$data[6]} ,
                         {$data[7]}
                       )";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �ؼ��ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �ؼ��ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                echo "$log_date �ؼ��ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n";
                // query_affected_trans($con, 'rollback');     // transaction rollback
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
            $query = "UPDATE equip_work_inst_header SET
                            inst_no     = {$data[0]} ,
                            parts_no    ='{$data[1]}',
                            material    ='{$data[2]}',
                            inst_date   = {$data[3]} ,
                            delivery    = {$data[4]} ,
                            inst_qt     = {$data[5]} ,
                            mate_kg     = {$data[6]} ,
                            sei_no      = {$data[7]} 
                WHERE inst_no={$data[0]}";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �ؼ��ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �ؼ��ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                echo "$log_date �ؼ��ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n";
                // query_affected_trans($con, 'rollback');     // transaction rollback
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
    fwrite($fpa, "$log_date ���ؼ��إå����ι���:{$data[1]} : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date ���ؼ��إå����ι���:{$data[1]} : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date ���ؼ��إå����ι���:{$data[1]} : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpb, "$log_date ���ؼ��إå����ι���:{$data[1]} : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date ���ؼ��إå����ι���:{$data[1]} : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpb, "$log_date ���ؼ��إå����ι���:{$data[1]} : {$upd_ok}/{$rec} �� �ѹ� \n");
    echo "$log_date ���ؼ��إå����ι���:{$data[1]} : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date ���ؼ��إå����ι���:{$data[1]} : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date ���ؼ��إå����ι���:{$data[1]} : {$upd_ok}/{$rec} �� �ѹ� \n";
} else {
    fwrite($fpa,"$log_date �ե�����$file_orign ������ޤ���!\n");
    fwrite($fpb,"$log_date �ե�����$file_orign ������ޤ���!\n");
    echo "{$log_date}: file:{$file_orign} ������ޤ���!\n";
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');



/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    fwrite($fpb, "$log_date db_connect() error \n");
    echo "$log_date db_connect() error \n";
    exit();
}
// ���ؼ��������٥ե����� �������
$file_orign  = S_MGIROT;
$file_debug  = '/home/www/html/tnk-web/system/debug/debug-MGIROT.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 200, '_');     // �¥쥳���ɤ�46�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���'_'�����
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num != 8) {
            if ($num == 0 || $num == 1) {   // php-4.3.5��0�֤� php-4.3.6��1���֤����ͤˤʤä���fgetcsv�λ����ѹ��ˤ��
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
                fwrite($fpb, "$log_date AS/400 del record=$rec \n");
                echo "$log_date AS/400 del record=$rec \n";
            } else {
                fwrite($fpa, "$log_date field not 8 record=$rec \n");
                fwrite($fpb, "$log_date field not 8 record=$rec \n");
                echo "$log_date field not 8 record=$rec \n";
            }
           continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        }
        
        $query_chk = sprintf("SELECT parts_no FROM equip_work_instruction
                                WHERE inst_no=%d and koutei=%d",
                                $data[0], $data[2]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "INSERT INTO equip_work_instruction (inst_no, koutei, parts_no, pro_mark
                                    , prog_deg, pro_cost, mac_no)
                      VALUES(
                         {$data[0]} ,
                         {$data[2]} ,
                        '{$data[1]}',
                        '{$data[3]}',
                         {$data[4]} ,
                         {$data[5]} ,
                         {$data[6]}{$data[7]}
                       )";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �ؼ��ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �ؼ��ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                echo "$log_date �ؼ��ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n";
                // query_affected_trans($con, 'rollback');     // transaction rollback
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
            $query = "UPDATE equip_work_instruction SET
                            inst_no     = {$data[0]} ,
                            koutei      = {$data[2]} ,
                            parts_no    ='{$data[1]}',
                            pro_mark    ='{$data[3]}',
                            prog_deg    = {$data[4]} ,
                            pro_cost    = {$data[5]} ,
                            mac_no      = {$data[6]}{$data[7]}
                WHERE inst_no={$data[0]} and koutei={$data[2]}";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �ؼ��ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �ؼ��ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                echo "$log_date �ؼ��ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n";
                // query_affected_trans($con, 'rollback');     // transaction rollback
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
    fwrite($fpa, "$log_date ���ؼ��������٤ι���:{$data[1]} : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date ���ؼ��������٤ι���:{$data[1]} : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date ���ؼ��������٤ι���:{$data[1]} : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpb, "$log_date ���ؼ��������٤ι���:{$data[1]} : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date ���ؼ��������٤ι���:{$data[1]} : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpb, "$log_date ���ؼ��������٤ι���:{$data[1]} : {$upd_ok}/{$rec} �� �ѹ� \n");
    echo "$log_date ���ؼ��������٤ι���:{$data[1]} : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date ���ؼ��������٤ι���:{$data[1]} : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date ���ؼ��������٤ι���:{$data[1]} : {$upd_ok}/{$rec} �� �ѹ� \n";
} else {
    fwrite($fpa,"$log_date �ե�����$file_orign ������ޤ���!\n");
    fwrite($fpb,"$log_date �ե�����$file_orign ������ޤ���!\n");
    echo "{$log_date}: file:{$file_orign} ������ޤ���!\n";
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');

fclose($fpa);      ////// �����ѥ�����߽�λ
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߽�λ
?>
