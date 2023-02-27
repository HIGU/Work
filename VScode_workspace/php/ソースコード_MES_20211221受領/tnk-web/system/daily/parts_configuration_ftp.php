#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ����ǡ���(��¤������ɽ[���ʹ���]) ��ưFTP Download cron�ǽ����� cgi��   //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2004-2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/05/24 Created  parts_configuration_ftp.php                          //
// 2004/05/25 ����������Ͽ����λ�����������ե������ MISKST��HISKST�ѹ� //
// 2004/06/07 php-4.3.6-cgi -q �� php-4.3.7-cgi -q  �С�����󥢥åפ�ȼ��  //
// 2004/11/18 php-5.0.2-cli�إС�����󥢥å� *�����륹����ץȤ��б����ѹ� //
// 2004/12/13 #!/usr/local/bin/php-5.0.2-cli �� php (������5.0.3RC2)���ѹ�  //
// 2009/12/18 ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤ��ɲ�           ��ë //
// 2010/01/19 �᡼���ʬ����䤹������١����ա�����������ɲ�       ��ë //
// 2010/01/20 $log_date�������'�Ǥ�̵��"�ʤΤǽ���                    ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
ini_set('max_execution_time', 1200);    // ����¹Ի���=20ʬ
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');    ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤǥ����ץ�
fwrite($fpb, "��¤������ɽ �����ǡ����ι���\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/parts_configuration_ftp.php\n");
echo "/home/www/html/tnk-web/system/daily/parts_configuration_ftp.php\n";

// FTP�Υ������åȥե�����
define('D_HISKST', 'UKWLIB/W#HISKST');      // ��¤�����ʹ����ե����� download file
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
define('S_HISKST', '/home/www/html/tnk-web/system/backup/W#HISKST.TXT');  // ��¤�����ʹ����ե����� save file

// ���ͥ���������(FTP��³�Υ����ץ�)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// ���ʹ����ե�����
        if (ftp_get($ftp_stream, S_HISKST, D_HISKST, FTP_ASCII)) {
            echo "$log_date ���ʹ��� ftp_get download OK ", D_HISKST, "��", S_HISKST, "\n";
            fwrite($fpa,"$log_date ���ʹ��� ftp_get download OK " . D_HISKST . '��' . S_HISKST . "\n");
            fwrite($fpb,"$log_date ���ʹ��� ftp_get download OK " . D_HISKST . '��' . S_HISKST . "\n");
        } else {
            echo "$log_date ���ʹ��� ftp_get() error ", D_HISKST, "\n";
            fwrite($fpa,"$log_date ���ʹ��� ftp_get() error " . D_HISKST . "\n");
            fwrite($fpb,"$log_date ���ʹ��� ftp_get() error " . D_HISKST . "\n");
        }
    } else {
        echo "$log_date ���ʹ��� ftp_login() error \n";
        fwrite($fpa,"$log_date ���ʹ��� ftp_login() error \n");
        fwrite($fpb,"$log_date ���ʹ��� ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    echo "$log_date ftp_connect() error --> ���ʹ���\n";
    fwrite($fpa,"$log_date ftp_connect() error --> ���ʹ���\n");
    fwrite($fpb,"$log_date ftp_connect() error --> ���ʹ���\n");
}



/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date ���ʹ��� db_connect() error \n");
    fwrite($fpb, "$log_date ���ʹ��� db_connect() error \n");
    echo "$log_date ���ʹ��� db_connect() error \n";
    exit();
}
// ���ʹ����ե����� ������� �������
$file_orign  = S_HISKST;
$file_debug  = '/home/www/html/tnk-web/system/debug/debug-MISKST.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');     // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    $del_ok = 0;    // DELETE�ѥ����󥿡�
    $del_old = 0;   // ������ѥ����󥿡�
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 100, '_');     // �¥쥳���ɤ�33�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���'_'�����
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num != 5) {
            if ($num == 0 || $num == 1) {   // php-4.3.5��0�֤� php-4.3.6��1���֤����ͤˤʤä���fgetcsv�λ����ѹ��ˤ��
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
                fwrite($fpb, "$log_date AS/400 del record=$rec \n");
                echo "$log_date AS/400 del record=$rec \n";
            } else {
                fwrite($fpa, "$log_date field not 5 record=$rec \n");
                fwrite($fpb, "$log_date field not 5 record=$rec \n");
                echo "$log_date field not 5 record=$rec \n";
            }
           continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        }
        ////////// ���ơ����������å� (A=�ɲ�ʬ U=�ѹ�ʬ D=���ʬ) U�����
        // $data[4] = 'A';     // �����Ͽ�λ�
        if ($data[4] == 'A' || $data[4] == 'U') {
            $query_chk = sprintf("SELECT parts_no FROM parts_configuration
                                    WHERE p_parts_no='%s' and parts_no='%s'",
                                    $data[0], $data[1]);
            if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                ///// ��Ͽ�ʤ� insert ����
                $query = "INSERT INTO parts_configuration (p_parts_no, parts_no, mtl_cond, unit_qt)
                          VALUES(
                          '{$data[0]}',
                          '{$data[1]}',
                          '{$data[2]}',
                           {$data[3]})";
                if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                    fwrite($fpa, "$log_date ���ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                    fwrite($fpb, "$log_date ���ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                    echo "$log_date ���ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n";
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
                $query = "UPDATE parts_configuration SET
                                p_parts_no  ='{$data[0]}',
                                parts_no    ='{$data[1]}',
                                mtl_cond    ='{$data[2]}',
                                unit_qt     = {$data[3]}
                    WHERE p_parts_no='{$data[0]}' and parts_no='{$data[1]}'";
                if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                    fwrite($fpa, "$log_date ���ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                    fwrite($fpb, "$log_date ���ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                    echo "$log_date ���ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n";
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
        } else {    //////// D=���ʬ�ν���
            $query_chk = sprintf("SELECT parts_no FROM parts_configuration
                                    WHERE p_parts_no='%s' and parts_no='%s'",
                                    $data[0], $data[1]);
            if (getUniResTrs($con, $query_chk, $res_chk) > 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                ////// ��Ͽ���� ����¹�
                $query_del = sprintf("DELETE FROM parts_configuration
                                    WHERE p_parts_no='%s' and parts_no='%s'",
                                    $data[0], $data[1]);
                if ( ($del_num = query_affected_trans($con, $query_del)) != 1) {  // �����ѥ����꡼�μ¹�
                    fwrite($fpa, "$log_date ���ֹ�:{$data[0]} : {$rec}������쥳���ɿ�{$del_num}:�쥳�����ܤ�DELETE�˼��Ԥ��ޤ���!\n");
                    fwrite($fpb, "$log_date ���ֹ�:{$data[0]} : {$rec}������쥳���ɿ�{$del_num}:�쥳�����ܤ�DELETE�˼��Ԥ��ޤ���!\n");
                    echo "$log_date ���ֹ�:{$data[0]} : {$rec}������쥳���ɿ�{$del_num}:�쥳�����ܤ�DELETE�˼��Ԥ��ޤ���!\n";
                    // query_affected_trans($con, "rollback");     // transaction rollback
                    $rec_ng++;
                    ////////////////////////////////////////// Debug start
                    for ($f=0; $f<$num; $f++) {
                        fwrite($fpw,"'{$data[$f]}',");      // debug
                    }
                    fwrite($fpw,"\n");                      // debug
                    fwrite($fpw, "$query_del \n");              // debug
                    break;                                  // debug
                    ////////////////////////////////////////// Debug end
                } else {
                    $rec_ok++;
                    $del_ok++;
                }
            } else {
                $del_old++;
            }
        }
    }
    fclose($fp);
    fclose($fpw);       // debug
    fwrite($fpa, "$log_date ���ʹ����ι���:{$data[1]} : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date ���ʹ����ι���:{$data[1]} : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date ���ʹ����ι���:{$data[1]} : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpa, "$log_date ���ʹ����ι���:{$data[1]} : {$del_ok}/{$rec} �� ��� \n");
    fwrite($fpa, "$log_date ���ʹ����ι���:{$data[1]} : {$del_old}/{$rec} �� ����� \n");
    fwrite($fpb, "$log_date ���ʹ����ι���:{$data[1]} : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date ���ʹ����ι���:{$data[1]} : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpb, "$log_date ���ʹ����ι���:{$data[1]} : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpb, "$log_date ���ʹ����ι���:{$data[1]} : {$del_ok}/{$rec} �� ��� \n");
    fwrite($fpb, "$log_date ���ʹ����ι���:{$data[1]} : {$del_old}/{$rec} �� ����� \n");
    echo "$log_date ���ʹ����ι���:{$data[1]} : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date ���ʹ����ι���:{$data[1]} : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date ���ʹ����ι���:{$data[1]} : {$upd_ok}/{$rec} �� �ѹ� \n";
    echo "$log_date ���ʹ����ι���:{$data[1]} : {$del_ok}/{$rec} �� ��� \n";
    echo "$log_date ���ʹ����ι���:{$data[1]} : {$del_old}/{$rec} �� ����� \n";
} else {
    fwrite($fpa,"$log_date �ե�����$file_orign ������ޤ���!\n");
    fwrite($fpb,"$log_date �ե�����$file_orign ������ޤ���!\n");
    echo "{$log_date}: file:{$file_orign} ������ޤ���!\n";
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, "commit");
// echo $query . "\n";  // debug
fclose($fpa);      ////// �����ѥ�����߽�λ
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߽�λ
?>
