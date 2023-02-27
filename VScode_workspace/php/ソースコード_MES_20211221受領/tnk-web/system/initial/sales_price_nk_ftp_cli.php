#!/usr/local/bin/php-5.0.2-cli
<?php
//////////////////////////////////////////////////////////////////////////////
// ���칩��λ���ñ���ե����� ���ڤ����ñ�� �ǿ� ��ưFTP Download �� cli�� //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2004-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/10/29 Created  sales_price_nk_ftp_cli.php                           //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
ini_set('max_execution_time', 1200);    // ����¹Ի���=20ʬ
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');    ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�

// FTP�Υ������åȥե�����
$target_file = 'UKWLIB/W#MGUSI@L';      // ����ñ���ǿ��ե����� download file ����ξ���W#HGUSI@L
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
$save_file = '/home/guest/daily/W#MGUSI.TXT';      // ����ñ���ǿ��ե����� save file

/********************************************
// ���ͥ���������(FTP��³�Υ����ץ�)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// ��ʸ��ȯ�ԥե�����
        if (ftp_get($ftp_stream, $save_file, $target_file, FTP_ASCII)) {
            echo 'ftp_get download ���� ', $target_file, '��', $save_file, "\n";
            fwrite($fpa,"$log_date ftp_get download ���� " . $target_file . '��' . $save_file . "\n");
        } else {
            echo 'ftp_get() error ', $target_file, "\n";
            fwrite($fpa,"$log_date ftp_get() error " . $target_file . "\n");
        }
    } else {
        echo "ftp_login() error \n";
        fwrite($fpa,"$log_date ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    echo "ftp_connect() error --> ����ñ��F\n";
    fwrite($fpa,"$log_date ftp_connect() error --> ����ñ��F\n");
}
********************************************/



/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    echo "$log_date db_connect() error \n";
    exit();
}
// ��ʸ��ȯ�ԥե����� ������� �������
$file_orign  = $save_file;
$file_debug  = '/home/guest/daily/debug/debug-MGUSI.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 100, '_');     // �¥쥳���ɤ�75�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���'_'�����
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num < 9) {    // �ºݤˤ� 9 ����(�Ǹ夬�ʤ���礬���뤿��)
            if ($num == 0 || $num == 1) {   // php-4.3.5��0�֤� php-4.3.6��1���֤����ͤˤʤä���fgetcsv�λ����ѹ��ˤ��
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
                echo "$log_date AS/400 del record=$rec \n";
            } else {
                fwrite($fpa, "$log_date field not 20 record=$rec \n");
                echo "$log_date field not 20 record=$rec \n";
            }
           continue;
        }
        $data[6] = trim($data[6]);    // ���ͤ�̵�̤�;�����
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        }
        $query_chk = sprintf("SELECT parts_no FROM sales_price_nk
                                WHERE parts_no='%s'",
                                $data[0]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "INSERT INTO sales_price_nk
                      VALUES(
                      '{$data[0]}',
                       {$data[1]} ,
                      '{$data[2]}',
                       {$data[3]} ,
                      '{$data[4]}',
                       {$data[5]} ,
                      '{$data[6]}',
                      '{$data[7]}')
                      -- {$data[8]} ) ����ϼ���ޤʤ�
            ";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                echo "$log_date �����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n";
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
            $query = "UPDATE sales_price_nk SET
                            parts_no    ='{$data[0]}',
                            price       = {$data[1]} ,
                            nk_kubun    ='{$data[2]}',
                            regdate     = {$data[3]} ,
                            div         ='{$data[4]}',
                            lot         = {$data[5]} ,
                            note        ='{$data[6]}',
                            reg_kubun   ='{$data[7]}'
                            -- mushou      = {$data[8]}
                WHERE parts_no='{$data[0]}'
            ";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                echo "$log_date �����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n";
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
    fwrite($fpa, "$log_date ����ñ���ι���:{$data[2]} : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date ����ñ���ι���:{$data[2]} : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date ����ñ���ι���:{$data[2]} : {$upd_ok}/{$rec} �� �ѹ� \n");
    echo "$log_date ����ñ���ι���:{$data[2]} : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date ����ñ���ι���:{$data[2]} : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date ����ñ���ι���:{$data[2]} : {$upd_ok}/{$rec} �� �ѹ� \n";
} else {
    fwrite($fpa,"$log_date �ե�����$file_orign ������ޤ���!\n");
    echo "{$log_date}: file:{$file_orign} ������ޤ���!\n";
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');
// echo $query . "\n";  // debug
fclose($fpa);      ////// �����ѥ�����߽�λ
?>
