#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-4.3.9-cli -c php4.ini                               //
// ������ ���ʺ߸� ����(history) �ޥ����� ���󹹿� �� cli��                 //
//                      5.0.4-cli --- 5.1.6-cli �ޤǤ�Ⱦ�ѥ��ʤ�NG          //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2004-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/12/15 Created  parts_stock_history_master_ftp_cli.php               //
// 2004/12/17 ���ʤ������ʤ��Զ���к���php(5.0.x) �� php-4.3.9-cli���ѹ� //
//           parts_stock_sync_control�ơ��֥���ɲä��쥳��������Ƿ����ɲ� //
// 2004/12/20 rename()����Ѥ��Ƽ���ߥǡ�������еڤ������ǡ����Υ����å�  //
//            �����Ѥ� echo ʸ�򥳥���                                    //
// 2004/12/27 FTPž������Υ��顼�����å����ѹ�(�쥳���ɿ���sync�ι���̵ͭ) //
// 2005/02/04 AS�����UPLOAD file �Υ�å��ˤ�����������å����ɲ�        //
// 2005/07/26 ����Υǡ��������AS�ǡ�����¸�ߤ������Τ� flock��'r+'���ѹ�  //
// 2006/08/28 php-4.3.9-cli �� php(�����Ȥ�5.1.6) simplate.so ��DSO module//
//            �Ǽ����褦�ˤ������� module API=20050922 �� php API=20020429//
//            �����ʤ��ʤä�����Ϥ�Ⱦ�ѥ��ʤ������ʤ����᲼�����к�    //
// 2006/08/29 simplate.so ��DSO module �Ǽ��������� php4�� -c php4.ini�ɲ�//
// 2006/09/05 ʸ�������θ�����fgetcsv()��LANG�Ķ��ѿ�������Ǥ������ʬ���� //
//            cron������ե�����(as400get_ftp)��LANG=ja_JP,eucJP���ɲä��б�//
// 2007/08/03 �߸ˡ�ͭ�����ޥ��ʥ��ꥹ��(parts_stock_avail_minus)�������ɲ� //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
// ini_set('max_execution_time', 1200);    // ����¹Ի���=20ʬ
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');                // �����ѥ�������
$fpa = fopen('/tmp/parts_stock.log', 'a');      // �����ѥ��ե�����ؤν���ߤǥ����ץ�

// FTP�Υ������åȥե�����
$target_file1 = 'UKWLIB/W#HIZHST.TXT';               // download file1
$target_file2 = 'UKWLIB/W#MIBZMT.TXT';               // download file2
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
$as_file1   = '/home/guest/daily/W#HIZHST.TXT';     // AS/400 file1
$as_file2   = '/home/guest/daily/W#MIBZMT.TXT';     // AS/400 file2
$save_file1 = '/home/guest/daily/HIZHST.txt';       // save file1
$save_file2 = '/home/guest/daily/MIBZMT.txt';       // save file2

if (!file_exists($as_file1)) endJOB($fpa);          // AS�����UPLOAD file ��¸�ߥ����å�
if (!file_exists($as_file2)) endJOB($fpa);

if (file_exists($save_file1)) unlink($save_file1);  // ����Υǡ�������
if (file_exists($save_file2)) unlink($save_file2);  // ����Υǡ�������

if (!($fp1=fopen($as_file1, 'r+'))) {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date $as_file1 �������ץ�Ǥ��ʤ��Τǽ�λ���ޤ��� \n");
    endJOB($fpa); // open ����ʤ���н�λ
} else {
    if (!flock($fp1, LOCK_EX)) {
        fclose($fp1);
        $log_date = date('Y-m-d H:i:s');
        fwrite($fpa, "$log_date $as_file1 ����å��Ǥ��ʤ��Τǽ�λ���ޤ��� \n");
        endJOB($fpa);   // ��å��Ǥ��ʤ��Τǽ�λ
    }
}
if (!($fp2=fopen($as_file2, 'r+'))) {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date $as_file2 �������ץ�Ǥ��ʤ��Τǽ�λ���ޤ��� \n");
    endJOB($fpa); // open ����ʤ���н�λ
} else {
    if (!flock($fp2, LOCK_EX)) {
        fclose($fp2);
        $log_date = date('Y-m-d H:i:s');
        fwrite($fpa, "$log_date $as_file2 ����å��Ǥ��ʤ��Τǽ�λ���ޤ��� \n");
        endJOB($fpa);   // ��å��Ǥ��ʤ��Τǽ�λ
    }
}
fclose($fp1);   // ��å����褿�Τǳ���
fclose($fp2);   // ���Υ�͡����������

if (!@rename($as_file1, $save_file1) ) {             // �ե�����̾�ѹ�
    // $as_file1�Υե������¸�ߥ����å��򤹤�Ⱦ��̵���ˤʤ�(AS�����Ѥ��Ƥ���)���� no check
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date $as_file1 rename() NG \n");
    // endJOB($fpa);
}
if (!@rename($as_file2, $save_file2) ) {             // �ե�����̾�ѹ�
    // $as_file2�Υե������¸�ߥ����å��򤹤�Ⱦ��̵���ˤʤ�(AS�����Ѥ��Ƥ���)���� no check
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date $as_file2 rename() NG \n");
    // endJOB($fpa);
}



/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date db_connect() error \n");
    // echo "$log_date db_connect() error \n";
    endJOB($fpa);
}
$today = date('Ymd');
///// �߸˷���Υ쥳��������
$query_ctl = "SELECT to_char(sync_date, 'YYYYMMDD'), sync_no FROM parts_stock_sync_control WHERE rec_no=1";
$res_ctl = array();
if (getResultTrs($con, $query_ctl, $res_ctl) > 0) {
    if ($today == $res_ctl[0][0]) {
        $history_rec = $res_ctl[0][1];
    } else {
        $history_rec = 0;           // �����Υǡ����Ǥʤ���Х��ꥢ��
    }
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date sync_control 1 error \n");
    // echo "$log_date sync_control 1 error \n";
    endJOB($fpa);
}
///// �߸˥ޥ������Υ쥳��������
$query_ctl = "SELECT to_char(sync_date, 'YYYYMMDD'), sync_no FROM parts_stock_sync_control WHERE rec_no=2";
$res_ctl = array();
if (getResultTrs($con, $query_ctl, $res_ctl) > 0) {
    if ($today == $res_ctl[0][0]) {
        $master_rec = $res_ctl[0][1];
    } else {
        $master_rec = 0;           // �����Υǡ����Ǥʤ���Х��ꥢ��
    }
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date sync_control 2 error \n");
    // echo "$log_date sync_control 2 error \n";
    endJOB($fpa);
}

// �߸˷��� ������� �������
$file_orign  = $save_file1;
$file_debug  = '/home/guest/daily/debug/debug-HIZHST.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 100, '_');     // �¥쥳���ɤ�93�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���'_'�����
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        if ($rec <= $history_rec) continue;     // ���˹����Ѥߤ����
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num < 13) {    // �ºݤˤ� 9 ����(�Ǹ夬�ʤ���礬���뤿��)
            if ($num == 0 || $num == 1) {   // php-4.3.5��0�֤� php-4.3.6��1���֤����ͤˤʤä���fgetcsv�λ����ѹ��ˤ��
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
                // echo "$log_date AS/400 del record=$rec \n";
            } else {
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date field not 13 record=$rec \n");
                // echo "$log_date field not 13 record=$rec \n";
            }
           continue;
        }
        $data[6]  = trim($data[6]);    // ��ɼ�ֹ��̵�̤�;�����
        $data[7]  = trim($data[7]);    // Ŧ��(�ײ��ֹ�)��̵�̤�;�����
        $data[10] = trim($data[10]);   // ���ͤ�̵�̤�;�����
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        }
        // ���쥳���ɤǹ�����������å�
        if ($rec == 1) {
            if ($data[12] != $today) {  // �����ι����ǡ����������å�
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date ������:{$data[12]} : �����ι����ǡ����Ǥʤ�!\n");
                // echo "$log_date ������:{$data[12]} : �����ι����ǡ����Ǥʤ�!\n";
                query_affected_trans($con, 'rollback');     // transaction rollback
                endJOB($fpa);
            }
        }
        ///// ��Ͽ�ʤ� insert ����
        $query = "INSERT INTO parts_stock_history
                  VALUES(
                  '{$data[0]}',     -- �����ֹ�
                  '{$data[1]}',     -- ABC��ʬ
                   {$data[2]} ,     -- �߸˰�ư��
                  '{$data[3]}',     -- ��������
                  '{$data[4]}',     -- ʧ������
                  '{$data[5]}',     -- ��ɼ��ʬ
                  '{$data[6]}',     -- ��ɼ�ֹ�
                  '{$data[7]}',     -- Ŧ��(�ײ��ֹ���)
                   {$data[8]} ,     -- NK�߸�
                   {$data[9]} ,     -- TNK�߸�
                  '{$data[10]}',    -- ����
                   {$data[11]} ,    -- �ǡ�����(��Ģ��)
                   {$data[12]})     -- ������
        ";
        if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa, "$log_date �����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
            // echo "$log_date �����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n";
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
    }
    fclose($fp);
    fclose($fpw);       // debug
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date �߸˷���ι��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date �߸˷���ι��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date �߸˷���ι��� : {$upd_ok}/{$rec} �� �ѹ� \n");
    // echo "$log_date �߸˷���ι��� : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    // echo "$log_date �߸˷���ι��� : {$ins_ok}/{$rec} �� �ɲ� \n";
    // echo "$log_date �߸˷���ι��� : {$upd_ok}/{$rec} �� �ѹ� \n";
    ///// �߸˷���Υ���ȥ���ơ��֥�ι���
    if ($rec > $history_rec) {   // FTPž������Υ��顼�����å�
        $query_ctl = "UPDATE parts_stock_sync_control SET sync_date=CURRENT_TIMESTAMP, sync_no={$rec}, pre_sync_no={$history_rec} WHERE rec_no=1";
        if (query_affected_trans($con, $query_ctl) <= 0) {      // �����ѥ����꡼�μ¹�
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa, "$log_date sync_control 1 UPDATE error \n");
            // echo "$log_date sync_control 1 UPDATE error \n";
            query_affected_trans($con, 'rollback');     // transaction rollback
            endJOB($fpa);
        }
    }
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa,"$log_date �ե�����$file_orign ������ޤ���!\n");
    // echo "{$log_date}: file:{$file_orign} ������ޤ���!\n";
}



// �߸˥ޥ����� ������� �������
$file_orign  = $save_file2;
$file_debug  = '/home/guest/daily/debug/debug-MIBZMT.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 100, '_');     // �¥쥳���ɤ�85�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���'_'�����
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        if ($rec <= $master_rec) continue;      // ���˹����Ѥߤ����
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num < 10) {    // �ºݤˤ� 9 ����(�Ǹ夬�ʤ���礬���뤿��)
            if ($num == 0 || $num == 1) {   // php-4.3.5��0�֤� php-4.3.6��1���֤����ͤˤʤä���fgetcsv�λ����ѹ��ˤ��
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
                // echo "$log_date AS/400 del record=$rec \n";
            } else {
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date field not 10 record=$rec \n");
                // echo "$log_date field not 10 record=$rec \n";
            }
           continue;
        }
        $data[7]  = trim($data[7]);    // ê�֤�̵�̤�;�����
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        }
        $query_chk = "SELECT parts_no FROM parts_stock_master
                                WHERE parts_no='{$data[0]}'
        ";
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "INSERT INTO parts_stock_master
                      VALUES(
                      '{$data[0]}',     -- �����ֹ�
                      '{$data[1]}',     -- ABC��ʬ
                       {$data[2]} ,     -- NK�߸�
                       {$data[3]} ,     -- TNK�߸�
                       {$data[4]} ,     -- ����߸�NK
                       {$data[5]} ,     -- ����߸�TNK
                      '{$data[6]}',     -- stock_id
                      '{$data[7]}',     -- ê��
                       {$data[8]} ,     -- Ĵ����
                       {$data[9]} ,     -- ��Ͽ��
                       {$data[10]})     -- ������
            ";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date �����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                // echo "$log_date �����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n";
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
            $query = "UPDATE parts_stock_master SET
                            parts_no      ='{$data[0]}',
                            abc_kubun     ='{$data[1]}',
                            nk_stock      = {$data[2]} ,
                            tnk_stock     = {$data[3]} ,
                            pre_nk_stock  = {$data[4]} ,
                            pre_tnk_stock = {$data[5]} ,
                            stock_id      ='{$data[6]}',
                            tnk_tana      ='{$data[7]}',
                            adj_date      = {$data[8]} ,
                            reg_date      = {$data[9]} ,
                            upd_date      = {$data[10]}
                WHERE parts_no='{$data[0]}'
            ";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date �����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                // echo "$log_date �����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n";
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
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date �߸˥ޥ������ι��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date �߸˥ޥ������ι��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date �߸˥ޥ������ι��� : {$upd_ok}/{$rec} �� �ѹ� \n");
    // echo "$log_date �߸˥ޥ������ι��� : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    // echo "$log_date �߸˥ޥ������ι��� : {$ins_ok}/{$rec} �� �ɲ� \n";
    // echo "$log_date �߸˥ޥ������ι��� : {$upd_ok}/{$rec} �� �ѹ� \n";
    ///// �߸˥ޥ������Υ���ȥ���ơ��֥�ι���
    if ($rec > $master_rec) {   // FTPž������Υ��顼�����å�
        $query_ctl = "UPDATE parts_stock_sync_control SET sync_date=CURRENT_TIMESTAMP, sync_no={$rec}, pre_sync_no={$master_rec} WHERE rec_no=2";
        if (query_affected_trans($con, $query_ctl) <= 0) {      // �����ѥ����꡼�μ¹�
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa, "$log_date sync_control 2 UPDATE error \n");
            // echo "$log_date sync_control 2 UPDATE error \n";
            query_affected_trans($con, 'rollback');     // transaction rollback
            endJOB($fpa);
        }
    }
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa,"$log_date �ե�����$file_orign ������ޤ���!\n");
    // echo "{$log_date}: file:{$file_orign} ������ޤ���!\n";
}
/////////// commit �ȥ�󥶥������λ
if ($rec_ng == 0) {
    query_affected_trans($con, 'commit');
    parts_stock_avail_minus_update($con, $fpa);     // �߸ˡ�ͭ�����ޥ��ʥ��ꥹ�Ȥι���
} else {
    query_affected_trans($con, 'rollback');
}
// echo $query . "\n";  // debug
endJOB($fpa);       ////// �����ѥ�����߽�λ


/***** ���̽�λ���� *****/
function endJOB($fpa)
{
    fclose($fpa);
    exit();
}

/***** �߸ˡ�ͭ�����ޥ��ʥ��ꥹ�Ȥι��� *****/
function parts_stock_avail_minus_update($con, $fpa)
{
    $query = "
        BEGIN;
        DELETE FROM parts_stock_avail_minus_table;
        INSERT INTO parts_stock_avail_minus_table SELECT * FROM parts_stock_avail_minus(0);
        COMMIT;
    ";
    query_affected_trans($con, $query);
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa,"$log_date �߸ˡ�ͭ�����ѿ��ޥ��ʥ��ꥹ�Ȥ򹹿����ޤ�����parts_stock_avail_minus_table\n");
}
?>
