#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-5.0.2-cli                                           //
// ����ǡ���(MICCC CC����TNKCC����) ��ưFTP Download cron�ǽ����� cli��    //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2004-2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/12/08 Created  daily_miccc_ftp_cli.php                              //
// 2004/12/13 #!/usr/local/bin/php-5.0.2-cli �� php (������5.0.3RC2)���ѹ�  //
// 2009/12/18 ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤ��ɲ�           ��ë //
// 2010/01/19 �᡼���ʬ����䤹������١����ա�����������ɲ�       ��ë //
// 2010/01/20 $log_date�������'�Ǥ�̵��"�ʤΤǽ���                    ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
// ini_set('max_execution_time', 1200);    // ����¹Ի���=20ʬ
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');    ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤǥ����ץ�
fwrite($fpb, "�ã����ʣԣΣˣã����ʤΥơ��֥빹��\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/daily_miccc_ftp_cli.php\n");

// FTP�Υ������åȥե�����
$target_file = 'UKWLIB/W#MICCC';        // download file
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
$save_file = '/home/www/html/tnk-web/system/backup/W#MICCC.TXT';     // save file

// ���ͥ���������(FTP��³�Υ����ץ�)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        if (ftp_get($ftp_stream, $save_file, $target_file, FTP_ASCII)) {
            echo "$log_date �ã����ʣԣΣˣã����� ftp_get download OK ", $target_file, "��", $save_file, "\n";
            fwrite($fpa,"$log_date �ã����ʣԣΣˣã����� ftp_get download OK " . $target_file . '��' . $save_file . "\n");
            fwrite($fpb,"$log_date �ã����ʣԣΣˣã����� ftp_get download OK " . $target_file . '��' . $save_file . "\n");
        } else {
            echo "$log_date �ã����ʣԣΣˣã����� ftp_get() error ", $target_file, "\n";
            fwrite($fpa,"$log_date �ã����ʣԣΣˣã����� ftp_get() error " . $target_file . "\n");
            fwrite($fpb,"$log_date �ã����ʣԣΣˣã����� ftp_get() error " . $target_file . "\n");
        }
    } else {
        echo "$log_date �ã����ʣԣΣˣã����� ftp_login() error \n";
        fwrite($fpa,"$log_date �ã����ʣԣΣˣã����� ftp_login() error \n");
        fwrite($fpb,"$log_date �ã����ʣԣΣˣã����� ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    echo "$log_date �ã����ʣԣΣˣã����� ftp_connect() error --> MICCC\n";
    fwrite($fpa,"$log_date �ã����ʣԣΣˣã����� ftp_connect() error --> MICCC\n");
    fwrite($fpb,"$log_date �ã����ʣԣΣˣã����� ftp_connect() error --> MICCC\n");
}



/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    fwrite($fpb, "$log_date db_connect() error \n");
    echo "$log_date �ã����ʣԣΣˣã����� db_connect() error \n";
    exit();
}
// MICCC�ե����� ������� �������
$file_orign  = $save_file;
$file_debug  = '/home/www/html/tnk-web/system/debug/debug-MICCC.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    $del_ok = 0;    // DELETE�ѥ����󥿡�OK
    $del_ng = 0;    // DELETE�ѥ����󥿡�NG
    $sql_del = 'DELETE FROM miccc';
    if ( ($del_ok=query_affected_trans($con, $sql_del)) <= 0) {
        fwrite($fpa, "$log_date MICCC�κ���оݥǡ���������ޤ���\n");
        fwrite($fpb, "$log_date MICCC�κ���оݥǡ���������ޤ���\n");
        echo "$log_date MICCC�κ���оݥǡ���������ޤ���\n";
    }
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 50, '_');     // �¥쥳���ɤ�13�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���'_'�����
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num < 2) {
            if ($num == 0 || $num == 1) {   // php-4.3.5��0�֤� php-4.3.6��1���֤����ͤˤʤä���fgetcsv�λ����ѹ��ˤ��
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
                fwrite($fpb, "$log_date AS/400 del record=$rec \n");
                // echo "$log_date AS/400 del record=$rec \n";
            } else {
                fwrite($fpa, "$log_date field not 2 record=$rec \n");
                fwrite($fpb, "$log_date field not 2 record=$rec \n");
                // echo "$log_date field not 2 record=$rec \n";
            }
           continue;
        }
        // if (!isset($data[1])) $data[1]='';    // 'D'=CC 'E'=TNKCC�����åȤ���Ƥ��뤫�����å�
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        }
        ///// ��Ͽ
        $query = "INSERT INTO miccc (mipn, miccc)
                        VALUES(
                            '{$data[0]}',
                            '{$data[1]}' 
                        )
        ";
        if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
            fwrite($fpa, "$log_date �����ֹ�:{$data[0]} MICCC:{$data[1]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
            fwrite($fpb, "$log_date �����ֹ�:{$data[0]} MICCC:{$data[1]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
            // echo "$log_date �����ֹ�:{$data[0]} MICCC:{$data[1]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n";
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
    fclose($fp);
    fclose($fpw);       // debug
    fwrite($fpa, "$log_date MICCC�ι������ǡ������ : $del_ok �������ޤ�����\n");
    fwrite($fpa, "$log_date MICCC�ι��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date MICCC�ι��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpb, "$log_date MICCC�ι������ǡ������ : $del_ok �������ޤ�����\n");
    fwrite($fpb, "$log_date MICCC�ι��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date MICCC�ι��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    echo "$log_date MICCC�ι������ǡ������ : $del_ok �������ޤ�����\n";
    echo "$log_date MICCC�ι��� : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date MICCC�ι��� : {$ins_ok}/{$rec} �� �ɲ� \n";
} else {
    fwrite($fpa,"$log_date �ե�����$file_orign ������ޤ���!\n");
    fwrite($fpb,"$log_date �ե�����$file_orign ������ޤ���!\n");
    echo "{$log_date}: file:{$file_orign} ������ޤ���!\n";
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');
// echo $query . "\n";  // debug
fclose($fpa);      ////// �����ѥ�����߽�λ
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߽�λ
?>
