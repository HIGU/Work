#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ����ǡ���(ñ������ե�����) ��ưFTP Download cron�ǽ����� cgi��         //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2004-2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/05/14 Created  parts_cost_ftp.php                                   //
// 2004/06/03 parts_cost_history��primary key(uniq key)��lot_no���ɲ�       //
//            begin commit �ڤ� FTP �� ������Ͽ�Ѥ˥����ȥ�����           //
// 2004/06/07 php-4.3.6-cgi -q �� php-4.3.7-cgi -q  �С�����󥢥åפ�ȼ��  //
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
fwrite($fpb, "ñ������ǡ����ι���\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/parts_cost_ftp.php\n");
echo "/home/www/html/tnk-web/system/daily/parts_cost_ftp.php\n";
        
// FTP�Υ������åȥե�����
define('D_HICOST', 'UKWLIB/W#HICOST');      // ñ������ե����� download file
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
define('S_HICOST', '/home/www/html/tnk-web/system/backup/W#HICOST.TXT');  // ñ������ե����� save file

// ���ͥ���������(FTP��³�Υ����ץ�)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// ñ������ե�����
        if (ftp_get($ftp_stream, S_HICOST, D_HICOST, FTP_ASCII)) {
            echo "$log_date ñ������ ftp_get download OK ", D_HICOST, "��", S_HICOST, "\n";
            fwrite($fpa,"$log_date ñ������ ftp_get download OK " . D_HICOST . '��' . S_HICOST . "\n");
            fwrite($fpb,"$log_date ñ������ ftp_get download OK " . D_HICOST . '��' . S_HICOST . "\n");
        } else {
            echo "$log_date ñ������ ftp_get() error ", D_HICOST, "\n";
            fwrite($fpa,"$log_date ñ������ ftp_get() error " . D_HICOST . "\n");
            fwrite($fpb,"$log_date ñ������ ftp_get() error " . D_HICOST . "\n");
        }
    } else {
        echo "$log_date ñ������ ftp_login() error \n";
        fwrite($fpa,"$log_date ñ������ ftp_login() error \n");
        fwrite($fpb,"$log_date ñ������ ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    echo "$log_date ftp_connect() error --> ñ������\n";
    fwrite($fpa,"$log_date ftp_connect() error --> ñ������\n");
    fwrite($fpb,"$log_date ftp_connect() error --> ñ������\n");
}



/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');    // ������Ͽ�Ѥˤϥ����ȥ�����
} else {
    fwrite($fpa, "$log_date ñ������ db_connect() error \n");
    fwrite($fpb, "$log_date ñ������ db_connect() error \n");
    echo "$log_date ñ������ db_connect() error \n";
    exit();
}
// ñ������ե����� ������� �������
$file_orign  = S_HICOST;
$file_debug  = '/home/www/html/tnk-web/system/debug/debug-HICOST.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 200, '_');     // �¥쥳���ɤ�75�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���'_'�����
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num != 13) {
            if ($num == 0 || $num == 1) {   // php-4.3.5��0�֤� php-4.3.6��1���֤����ͤˤʤä���fgetcsv�λ����ѹ��ˤ��
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
                fwrite($fpb, "$log_date AS/400 del record=$rec \n");
                echo "$log_date AS/400 del record=$rec \n";
            } else {
                fwrite($fpa, "$log_date field not 13 record=$rec \n");
                fwrite($fpb, "$log_date field not 13 record=$rec \n");
                echo "$log_date field not 13 record=$rec \n";
            }
           continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        }
        
        $query_chk = sprintf("SELECT parts_no FROM parts_cost_history
                                WHERE parts_no='%s' and reg_no=%d and vendor='%s' and pro_no=%d and lot_no=%d",
                                $data[0], $data[1], $data[3], $data[4], $data[8]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "INSERT INTO parts_cost_history (parts_no, reg_no, pro_mark, vendor, pro_no, mtl_cond,
                            kubun, pro_kubun, lot_no, lot_str, lot_end, lot_cost, as_regdate)
                      VALUES(
                      '{$data[0]}',
                       {$data[1]},
                      '{$data[2]}',
                      '{$data[3]}',
                       {$data[4]},
                      '{$data[5]}',
                      '{$data[6]}',
                      '{$data[7]}',
                       {$data[8]},
                       {$data[9]},
                       {$data[10]},
                       {$data[11]},
                       {$data[12]})";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
            $query = "UPDATE parts_cost_history SET
                            parts_no    ='{$data[0]}',
                            reg_no      = {$data[1]} ,
                            pro_mark    ='{$data[2]}',
                            vendor      ='{$data[3]}',
                            pro_no      = {$data[4]} ,
                            mtl_cond    ='{$data[5]}',
                            kubun       ='{$data[6]}',
                            pro_kubun   ='{$data[7]}',
                            lot_no      = {$data[8]} ,
                            lot_str     = {$data[9]} ,
                            lot_end     = {$data[10]},
                            lot_cost    = {$data[11]},
                            as_regdate  = {$data[12]}
                WHERE parts_no='{$data[0]}' and reg_no={$data[1]} and vendor='{$data[3]}' and pro_no={$data[4]} and lot_no={$data[8]}";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
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
    fwrite($fpa, "$log_date ñ������ι���:{$data[1]} : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date ñ������ι���:{$data[1]} : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date ñ������ι���:{$data[1]} : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpb, "$log_date ñ������ι���:{$data[1]} : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date ñ������ι���:{$data[1]} : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpb, "$log_date ñ������ι���:{$data[1]} : {$upd_ok}/{$rec} �� �ѹ� \n");
    echo "$log_date ñ������ι���:{$data[1]} : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date ñ������ι���:{$data[1]} : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date ñ������ι���:{$data[1]} : {$upd_ok}/{$rec} �� �ѹ� \n";
} else {
    fwrite($fpa,"$log_date �ե�����$file_orign ������ޤ���!\n");
    fwrite($fpb,"$log_date �ե�����$file_orign ������ޤ���!\n");
    echo "{$log_date}: file:{$file_orign} ������ޤ���!\n";
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');    // ������Ͽ�Ѥˤϥ����ȥ�����
// echo $query . "\n";  // debug
fclose($fpa);      ////// �����ѥ�����߽�λ
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߽�λ

?>
