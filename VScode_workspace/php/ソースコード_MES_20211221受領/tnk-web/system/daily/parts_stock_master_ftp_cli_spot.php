#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-4.3.9-cli -c php4.ini                               //
// ������ ���ʺ߸� �ޥ������Τߤ� ��ư����(����ʬ�ι���)�� cli��            //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2004-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/12/27 Created  parts_stock_master_ftp_cli_spot.php                  //
// 2004/12/20 ���ʤ������ʤ��Զ���к���php(5.0.x) �� php-4.3.9-cli���ѹ� //
//           parts_stock_sync_control�ơ��֥���ɲä��쥳��������Ƿ����ɲ� //
//            ���������˹������줿�ǡ������ư���ǹ������뤿��˺���      //
//            �ե�����̾ W#HIZHS2 W#MIBZM2 ���Ѥ��Ƥ���������             //
// ������ˡ��AS¦�� CALL IPKK151C PARM(X'020041217F') ��¹Ԥ�PCOMž����¹�//
// 2004/12/21 sync_control����Ѥ��������ʬ�Υǡ����˱ƶ����뤿����      //
// 2004/12/27 parts_stock_history_master_ftp_cli2.php ��                    //
//                             parts_stock_master_ftp_cli_spot.php ���ѹ�   //
// ������ˡ��AS¦�� CALL IPKK151C PARM(X'020050725F' 'Y')��¹Ԥ�ž���ޤ�OK //
//            �����С�¦�Υ��ޥ�ɥ饤��� ���Υ�����ץȤ�¹Ԥ���         //
// 2006/08/30 ���ޥ�ɥ饤�󥪥ץ����� -c php4.ini ���ɲ� simplate.so�б� //
// 2006/09/05 ʸ�������θ�����fgetcsv()��LANG�Ķ��ѿ�������Ǥ������ʬ���� //
//            cron������ե�����(as400get_ftp)��LANG=ja_JP,eucJP���ɲä��б�//
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
// ini_set('max_execution_time', 1200);    // ����¹Ի���=20ʬ
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');                // �����ѥ�������
$fpa = fopen('/tmp/parts_stock.log', 'a');      // �����ѥ��ե�����ؤν���ߤǥ����ץ�

// FTP�Υ������åȥե�����
$target_file1 = 'UKWLIB/W#HIZHS2.TXT';               // download file1
$target_file2 = 'UKWLIB/W#MIBZM2.TXT';               // download file2
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
$save_file1 = '/home/guest/daily/W#HIZHS2.TXT';     // save file1
$save_file2 = '/home/guest/daily/W#MIBZM2.TXT';     // save file2

/********************************************
// ���ͥ���������(FTP��³�Υ����ץ�) �߸˷���
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        if (ftp_get($ftp_stream, $save_file1, $target_file1, FTP_ASCII)) {
            echo 'ftp_get download OK ', $target_file1, '��', $save_file1, "\n";
            fwrite($fpa,"$log_date ftp_get download OK " . $target_file1 . '��' . $save_file1 . "\n");
        } else {
            echo 'ftp_get() error ', $target_file1, "\n";
            fwrite($fpa,"$log_date ftp_get() error " . $target_file1 . "\n");
        }
    } else {
        echo "ftp_login() error \n";
        fwrite($fpa,"$log_date ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    echo "ftp_connect() error --> �߸˷���\n";
    fwrite($fpa,"$log_date ftp_connect() error --> �߸˷���\n");
}

// ���ͥ���������(FTP��³�Υ����ץ�) �߸˥ޥ�����
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        if (ftp_get($ftp_stream, $save_file2, $target_file2, FTP_ASCII)) {
            echo 'ftp_get download OK ', $target_file2, '��', $save_file2, "\n";
            fwrite($fpa,"$log_date ftp_get download OK " . $target_file2 . '��' . $save_file2 . "\n");
        } else {
            echo 'ftp_get() error ', $target_file2, "\n";
            fwrite($fpa,"$log_date ftp_get() error " . $target_file2 . "\n");
        }
    } else {
        echo "ftp_login() error \n";
        fwrite($fpa,"$log_date ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    echo "ftp_connect() error --> �߸˥ޥ�����\n";
    fwrite($fpa,"$log_date ftp_connect() error --> �߸˥ޥ�����\n");
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

// �߸˥ޥ����� ������� �������
$file_orign  = $save_file2;
$file_debug  = '/home/guest/daily/debug/debug-MIBZMT2.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�  // ��η��򤬤ʤ��ʤä������������롣
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 100, '_');     // �¥쥳���ɤ�85�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���'_'�����
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num < 10) {    // �ºݤˤ� 9 ����(�Ǹ夬�ʤ���礬���뤿��)
            if ($num == 0 || $num == 1) {   // php-4.3.5��0�֤� php-4.3.6��1���֤����ͤˤʤä���fgetcsv�λ����ѹ��ˤ��
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
                echo "$log_date AS/400 del record=$rec \n";
            } else {
                fwrite($fpa, "$log_date field not 10 record=$rec \n");
                echo "$log_date field not 10 record=$rec \n";
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
                fwrite($fpa, "$log_date �����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                echo "$log_date �����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n";
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
    fwrite($fpa, "$log_date �߸˥ޥ�����2�ι��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date �߸˥ޥ�����2�ι��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date �߸˥ޥ�����2�ι��� : {$upd_ok}/{$rec} �� �ѹ� \n");
    echo "$log_date �߸˥ޥ�����2�ι��� : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date �߸˥ޥ�����2�ι��� : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date �߸˥ޥ�����2�ι��� : {$upd_ok}/{$rec} �� �ѹ� \n";
} else {
    fwrite($fpa,"$log_date �ե�����$file_orign ������ޤ���!\n");
    echo "{$log_date}: file:{$file_orign} ������ޤ���!\n";
}
/////////// commit �ȥ�󥶥������λ
if ($rec_ng == 0) {
    query_affected_trans($con, 'commit');
} else {
    query_affected_trans($con, 'rollback');
}
// echo $query . "\n";  // debug
fclose($fpa);      ////// �����ѥ�����߽�λ
?>
