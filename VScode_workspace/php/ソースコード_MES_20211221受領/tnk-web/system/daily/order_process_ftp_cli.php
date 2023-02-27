#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ȯ��ǡ����������� ��ư����FTP�� Download cron�ǽ����� cli��             //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2004-2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/09/14 Created  order_process_ftp_cli.php                            //
// 2004/11/18 php-5.0.2-cli�إС�����󥢥å� *�����륹����ץȤ��б����ѹ� //
// 2004/12/13 #!/usr/local/bin/php-5.0.2-cli �� php (������5.0.3RC2)���ѹ�  //
// 2005/05/11 ���ޥ�ɥ饤������Ѥ� echo ʸ�򥳥��ȥ�����                //
// 2005/06/01 ͽ��������ǽ���ɲ� ��ʸ�ֹ�=100000? (ͽ��κ��)          //
// 2005/07/25 �������� ����ȥ���ե�������å��Υ��å����ɲ�         //
// 2005/07/29 table�Υ�ˡ��������� sei_no,order_no ���ѹ� vendor�򳰤���   //
// 2006/04/26 ���̹����Τ��� BEGIN COMMIT �򥳥���                        //
// 2006/11/08 checkTableChange()���ɲä��ƥǡ������ѹ�����Ƥ���ʪ�Τ߹�����//
// 2007/07/30 �ǡ�������ʣ���Ƥ�����Υ�å��������å��������å���  //
// 2009/12/18 ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤ��ɲ�           ��ë //
// 2010/01/15 �᡼��˥�å�������̵���ä��١�echo���ɲ�               ��ë //
// 2010/01/19 �᡼���ʬ����䤹������١����ա�����������ɲ�       ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI�ǤʤΤ�ɬ�פʤ���
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');            // �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');        // �����ѥ��ե�����ؤν���ߤǥ����ץ�
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤǥ����ץ�
fwrite($fpb, "ȯ��ɸ�๩���ǡ����ι���\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/order_process_ftp_cli.php\n");
echo "/home/www/html/tnk-web/system/daily/order_process_ftp_cli.php\n";

// FTP�Υ������åȥե�����
$target_file = 'UKWLIB/W#MIORDR';           // ȯ�������٥ե����� download file
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
$save_file = '/home/www/html/tnk-web/system/backup/W#MIORDR.TXT';     // ȯ�������٥ե����� save file

// ���ͥ���������(FTP��³�Υ����ץ�)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// ȯ�������٥ե�����
        if (ftp_get($ftp_stream, $save_file, $target_file, FTP_ASCII)) {
            // echo 'ftp_get download OK ', $target_file, '��', $save_file, "\n";
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa,"$log_date ȯ�������� ftp_get download OK " . $target_file . '��' . $save_file . "\n");
            fwrite($fpb,"$log_date ȯ�������� ftp_get download OK " . $target_file . '��' . $save_file . "\n");
            echo "$log_date ȯ�������� ftp_get download OK " . $target_file . '��' . $save_file . "\n";
        } else {
            // echo 'ftp_get() error ', $target_file, "\n";
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa,"$log_date ȯ�������� ftp_get() error " . $target_file . "\n");
            fwrite($fpb,"$log_date ȯ�������� ftp_get() error " . $target_file . "\n");
            echo "$log_date ȯ�������� ftp_get() error " . $target_file . "\n";
        }
    } else {
        // echo "ftp_login() error \n";
        $log_date = date('Y-m-d H:i:s');
        fwrite($fpa,"$log_date ȯ�������� ftp_login() error \n");
        fwrite($fpb,"$log_date ȯ�������� ftp_login() error \n");
        echo "$log_date ȯ�������� ftp_login() error \n";
    }
    ftp_close($ftp_stream);
} else {
    // echo "ftp_connect() error --> ȯ��������\n";
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa,"$log_date ftp_connect() error --> ȯ��������\n");
    fwrite($fpb,"$log_date ftp_connect() error --> ȯ��������\n");
    echo "$log_date ftp_connect() error --> ȯ��������\n";
}


/////////// �������� ����ȥ���ե�������å�
do {
    if ($fp_ctl = fopen('/tmp/order_process_lock', 'w')) {
        flock($fp_ctl, LOCK_EX);
        $log_date = date('Y-m-d H:i:s');
        fwrite($fp_ctl, "$log_date " . __FILE__ . "\n");
        break;
    } else {
        sleep(5);   // ����ߤǥ����ץ����ʤ���У����Ե�
        continue;
    }
} while (0);

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    // query_affected_trans($con, 'BEGIN');
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date ȯ�������� db_connect() error \n");
    fwrite($fpb, "$log_date ȯ�������� db_connect() error \n");
    echo "$log_date ȯ�������� db_connect() error \n";
    exit();
}
// ȯ�������٥ե����� ������� �������
$file_orign  = $save_file;
$file_backup = '/home/www/html/tnk-web/system/backup/W#MIORDR-BAK.TXT';
$file_debug  = '/home/www/html/tnk-web/system/debug/debug-MIORDR.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    $noChg  = 0;    // ̤�ѹ������󥿡�
    $del_ok = 0;    // DELETE�ѥ����󥿡�OK
    $del_ng = 0;    // DELETE�ѥ����󥿡�NG
    ///////////// ͽ������ 2005/06/01 ADD
    $del2_rec = 0;  // ͽ�������оݿ�
    $del2_ok  = 0;  // ͽ����DELETE�ѥ����󥿡�OK
    $query_chk = "SELECT count(*) FROM order_process WHERE order_no>=1000000 and order_no<=1000009";
    if (getUniResTrs($con, $query_chk, $del2_rec) > 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
        $sql_del = "DELETE FROM order_process WHERE order_no>=1000000 and order_no<=1000009";
        $del2_ok = query_affected_trans($con, $sql_del);
    }
    ///////////// ͽ������ END
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 200, '_');     // �¥쥳���ɤ�189�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���'_'�����
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        $log_date = date('Y-m-d H:i:s');
        if ($num < 23) {
            if ($num == 0 || $num == 1) {   // php-4.3.5��0�֤� php-4.3.6��1���֤����ͤˤʤä���fgetcsv�λ����ѹ��ˤ��
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
                fwrite($fpb, "$log_date AS/400 del record=$rec \n");
                //echo "$log_date AS/400 del record=$rec \n";
            } else {
                fwrite($fpa, "$log_date field not 23 record=$rec \n");
                fwrite($fpb, "$log_date field not 23 record=$rec \n");
                //echo "$log_date field not 23 record=$rec \n";
            }
           continue;
        }
        if (!isset($data[23])) $data[23]='';    // ̵���������åȤ���Ƥ��뤫�����å�
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        }
        $query_chk = "
            SELECT * FROM order_process
                WHERE sei_no={$data[2]} and order_no={$data[0]}
        ";
            // ��� WHERE sei_no={$data[2]} and order_no={$data[0]} and vendor='{$data[1]}'
        if (($rows_chk=getResultTrs($con, $query_chk, $res_chk)) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��ʸ�ֹ��100000X --> MIORDR\2 ��1=ȯ�Զ�ʬ(1��), 00000=������ʸ�ֹ�(5��), X=�����ֹ�(1��)
            ///// �ɲä������˾嵭�δ�����ʸ�ֹ�=00000 ������å�����ȯ��ͽ��ǡ����������롣
            $order_no = ('100000' . substr(trim($data[0]), -1) );
            $query_chk = "SELECT sei_no FROM order_process WHERE sei_no={$data[2]} and order_no=$order_no";
            if (getUniResTrs($con, $query_chk, $res_chk) > 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                $sql_del = "DELETE FROM order_process WHERE sei_no={$data[2]} and order_no=$order_no";
                if (query_affected_trans($con, $sql_del) > 0) {
                    $del_ok++;
                } else {
                    $del_ng++;
                }
            }
            ///// ��Ͽ�ʤ� insert ����
            $query = "INSERT INTO order_process (order_no, vendor, sei_no, parts_no, pro_mark, mtl_cond, pro_kubun, order_price, order_date,
                            delivery, order_q, locate, kamoku, order_ku, plan_cond, masine, tatene, kiriko, genpin, siharai, cut_genpin, cut_siharai, next_pro, kensa)
                      VALUES(
                       {$data[0]} ,
                      '{$data[1]}',
                       {$data[2]} ,
                      '{$data[3]}',
                      '{$data[4]}',
                      '{$data[5]}',
                      '{$data[6]}',
                       {$data[7]} ,
                       {$data[8]},
                       {$data[9]},
                       {$data[10]} ,
                      '{$data[11]}',
                       {$data[12]} ,
                      '{$data[13]}',
                      '{$data[14]}',
                       {$data[15]} ,
                       {$data[16]} ,
                       {$data[17]} ,
                       {$data[18]} ,
                       {$data[19]} ,
                       {$data[20]} ,
                       {$data[21]} ,
                      '{$data[22]}',
                      '{$data[23]}')
            ";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date ��¤�ֹ�:{$data[2]} ��ʸ�ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date ��¤�ֹ�:{$data[2]} ��ʸ�ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                // echo "$log_date ��¤�ֹ�:{$data[2]} ��ʸ�ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n";
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
        } elseif ($rows_chk <= 1) {
            if (checkTableChange($data, $res_chk[0])) {
                $noChg++;
                continue;
            }
            ///// ��Ͽ���� update ����
            $query = "UPDATE order_process SET
                            order_no    = {$data[0]} ,
                            vendor      ='{$data[1]}',
                            sei_no      = {$data[2]} ,
                            parts_no    ='{$data[3]}',
                            pro_mark    ='{$data[4]}',
                            mtl_cond    ='{$data[5]}',
                            pro_kubun   ='{$data[6]}',
                            order_price = {$data[7]} ,
                            order_date  = {$data[8]} ,
                            delivery    = {$data[9]} ,
                            order_q     = {$data[10]} ,
                            locate      ='{$data[11]}',
                            kamoku      = {$data[12]} ,
                            order_ku    ='{$data[13]}',
                            plan_cond   ='{$data[14]}',
                            masine      = {$data[15]} ,
                            tatene      = {$data[16]} ,
                            kiriko      = {$data[17]} ,
                            genpin      = {$data[18]} ,
                            siharai     = {$data[19]} ,
                            cut_genpin  = {$data[20]} ,
                            cut_siharai = {$data[21]} ,
                            next_pro    ='{$data[22]}',
                            kensa       ='{$data[23]}'
                WHERE sei_no={$data[2]} and order_no={$data[0]}
            ";
                // ��� WHERE sei_no={$data[2]} and order_no={$data[0]} and vendor='{$data[1]}'
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date ��¤�ֹ�:{$data[2]} ��ʸ�ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date ��¤�ֹ�:{$data[2]} ��ʸ�ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                // echo "$log_date ��¤�ֹ�:{$data[2]} ��ʸ�ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n";
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
        } else {    // ���ˣ��ťǡ����ˤʤäƤ���ʪ���б�
            ///// ��Ͽ���� update ����
            $query = "UPDATE order_process SET
                            order_no    = {$data[0]} ,
                            vendor      ='{$data[1]}',
                            sei_no      = {$data[2]} ,
                            parts_no    ='{$data[3]}',
                            pro_mark    ='{$data[4]}',
                            mtl_cond    ='{$data[5]}',
                            pro_kubun   ='{$data[6]}',
                            order_price = {$data[7]} ,
                            order_date  = {$data[8]} ,
                            delivery    = {$data[9]} ,
                            order_q     = {$data[10]} ,
                            locate      ='{$data[11]}',
                            kamoku      = {$data[12]} ,
                            order_ku    ='{$data[13]}',
                            plan_cond   ='{$data[14]}',
                            masine      = {$data[15]} ,
                            tatene      = {$data[16]} ,
                            kiriko      = {$data[17]} ,
                            genpin      = {$data[18]} ,
                            siharai     = {$data[19]} ,
                            cut_genpin  = {$data[20]} ,
                            cut_siharai = {$data[21]} ,
                            next_pro    ='{$data[22]}',
                            kensa       ='{$data[23]}'
                WHERE sei_no={$data[2]} and order_no={$data[0]} and vendor='{$data[1]}'
            ";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date ��¤�ֹ�:{$data[2]} ��ʸ�ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date ��¤�ֹ�:{$data[2]} ��ʸ�ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                // echo "$log_date ��¤�ֹ�:{$data[2]} ��ʸ�ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n";
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
                $del_sql = "
                    DELETE FROM order_process
                        WHERE sei_no={$data[2]} AND order_no={$data[0]} AND vendor != '{$data[1]}'
                ";
                $log_date = date('Y-m-d H:i:s');
                if (query_affected_trans($con, $del_sql) > 0) {    // �����ѥ����꡼�μ¹�
                    fwrite($fpa, "$log_date ��¤�ֹ�:{$data[2]} ��ʸ�ֹ�:{$data[0]} vendor:{$data[1]} �ʳ��ϥǡ�������ʣ���Ƥ��뤿�������ޤ�����\n");
                    fwrite($fpb, "$log_date ��¤�ֹ�:{$data[2]} ��ʸ�ֹ�:{$data[0]} vendor:{$data[1]} �ʳ��ϥǡ�������ʣ���Ƥ��뤿�������ޤ�����\n");
                } else {
                    fwrite($fpa, "$log_date ��¤�ֹ�:{$data[2]} ��ʸ�ֹ�:{$data[0]} ��ʣ�ǡ����κ���Ǽ��Ԥ��ޤ�����\n");
                    fwrite($fpb, "$log_date ��¤�ֹ�:{$data[2]} ��ʸ�ֹ�:{$data[0]} ��ʣ�ǡ����κ���Ǽ��Ԥ��ޤ�����\n");
                }
                $rec_ok++;
                $upd_ok++;
            }
        }
    }
    fclose($fp);
    fclose($fpw);       // debug
    $del = $del_ok + $del_ng;
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date ȯ�������٤ι���:{$data[2]} : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date ȯ�������٤ι���:{$data[2]} : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date ȯ�������٤ι���:{$data[2]} : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpa, "$log_date ȯ�������٤ι���:{$data[2]} : {$noChg}/{$rec} �� ̤�ѹ� \n");
    fwrite($fpa, "$log_date ȯ��ͽ�� 100000? �κ�� : {$del_ok}/{$del} �� ��� \n");
    fwrite($fpa, "$log_date ȯ��ͽ���� 100000? �κ�� : {$del2_ok}/{$del2_rec} �� ��� \n");
    fwrite($fpb, "$log_date ȯ�������٤ι���:{$data[2]} : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date ȯ�������٤ι���:{$data[2]} : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpb, "$log_date ȯ�������٤ι���:{$data[2]} : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpb, "$log_date ȯ�������٤ι���:{$data[2]} : {$noChg}/{$rec} �� ̤�ѹ� \n");
    fwrite($fpb, "$log_date ȯ��ͽ�� 100000? �κ�� : {$del_ok}/{$del} �� ��� \n");
    fwrite($fpb, "$log_date ȯ��ͽ���� 100000? �κ�� : {$del2_ok}/{$del2_rec} �� ��� \n");
    echo "$log_date ȯ�������٤ι���:{$data[2]} : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date ȯ�������٤ι���:{$data[2]} : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date ȯ�������٤ι���:{$data[2]} : {$upd_ok}/{$rec} �� �ѹ� \n";
    echo "$log_date ȯ�������٤ι���:{$data[2]} : {$noChg}/{$rec} �� ̤�ѹ� \n";
    echo "$log_date ȯ��ͽ�� 100000? �κ�� : {$del_ok}/{$del} �� ��� \n";
    echo "$log_date ȯ��ͽ���� 100000? �κ�� : {$del2_ok}/{$del2_rec} �� ��� \n";
    if ($rec_ng == 0) {     // ����ߥ��顼���ʤ���� �ե��������
        if (file_exists($file_backup)) {
            unlink($file_backup);       // Backup �ե�����κ��
        }
        if (!rename($file_orign, $file_backup)) {
            // echo "$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n";
            fwrite($fpa,"$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n");
            fwrite($fpb,"$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n");
            echo "$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n";
        }
    }
} else {
    fwrite($fpa,"$log_date �ե�����$file_orign ������ޤ���!\n");
    fwrite($fpb,"$log_date �ե�����$file_orign ������ޤ���!\n");
    echo "{$log_date}: file:{$file_orign} ������ޤ���!\n";
}
/////////// commit �ȥ�󥶥������λ
// query_affected_trans($con, 'COMMIT');
fclose($fp_ctl);   ////// Exclusive�ѥե����륯����
fclose($fpa);      ////// �����ѥ�����߽�λ
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߽�λ
exit();

/***** �ơ��֥뤬�ѹ�����Ƥ������false���֤�     *****/
/***** ��������Ӥ���ǡ���������ȥơ��֥������   *****/
function checkTableChange($data, $res)
{
    for ($i=0; $i<23; $i++) {   // �Ǹ�θ�����NULL��¿���������
        // ��Ӥ˼���򤹤륹�ڡ�������
        if (trim($data[$i]) != trim($res[$i])) {
            return false;
        }
    }
    return true;
}

?>
