#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-4.3.9-cli -c php4.ini                               //
// ������ ���ʺ߸� ����(history) �ޥ����� ����ʬ�κ�ʬ�쥳���ɹ����� cli��  //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2004-2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/12/27 Created  parts_stock_history_master_ftp_cli3.php              //
// 2004/12/20 ���ʤ������ʤ��Զ���к���php(5.0.x) �� php-4.3.9-cli���ѹ� //
//           parts_stock_sync_control�ơ��֥���ɲä��쥳��������Ƿ����ɲ� //
//            ���������˹������줿�ǡ������ư���ǹ������뤿��˺���      //
//            �ե�����̾ W#HIZHS2 W#MIBZM2 ���Ѥ��Ƥ���������             //
// ������ˡ��AS¦�� CALL IPKK151C PARM(X'020041217F') ��¹Ԥ�PCOMž����¹�//
// 2004/12/21 sync_control����Ѥ��������ʬ�Υǡ����˱ƶ����뤿����      //
// 2004/12/27 ����ʬ�κ�ʬ�쥳���ɤι����Ѥ˿�������                        //
// 2006/08/30 ���ޥ�ɥ饤�󥪥ץ����� -c php4.ini ���ɲ� simplate.so�б� //
// 2006/09/05 ʸ�������θ�����fgetcsv()��LANG�Ķ��ѿ�������Ǥ������ʬ���� //
//            cron������ե�����(as400get_ftp)��LANG=ja_JP,eucJP���ɲä��б�//
// 2009/12/18 ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤ��ɲ�           ��ë //
// 2010/01/19 �᡼���ʬ����䤹������١����ա�����������ɲ�       ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
// ini_set('max_execution_time', 1200);    // ����¹Ի���=20ʬ
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');                // �����ѥ�������
$fpa = fopen('/tmp/parts_stock.log', 'a');      // �����ѥ��ե�����ؤν���ߤǥ����ץ�
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤǥ����ץ�
fwrite($fpb, "�߸˷��򡦺߸˥ޥ���������ʬ�ι���\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/parts_stock_history_master_ftp_cli3.php\n");

// FTP�Υ������åȥե�����
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
$save_file2 = '/home/guest/daily/W#MIBZOOY.CSV';     // save file2

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
    fwrite($fpa, "$log_date �߸˷��򡦺߸˥ޥ����� db_connect() error \n");
    fwrite($fpb, "$log_date �߸˷��򡦺߸˥ޥ����� db_connect() error \n");
    echo "$log_date �߸˷��򡦺߸˥ޥ����� db_connect() error \n";
    exit();
}
// �߸˥ޥ����� ������� �������
$file_orign  = $save_file2;
$file_debug  = '/home/guest/daily/debug/debug-MIBZMT_difference.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    // $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�  // ��Ǵ��˻��Ѥ��Ƥ��뤿���������ʤ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 100, ',');     // �¥쥳���ɤ�85�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���'_'�����
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        if ($rec <= $rec_tnk) continue;      // �����ѤߤΥ쥳���ɤ��ɤ����Ф�
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num < 2) {    // �ե�����ɿ������å�
            if ($num == 0 || $num == 1) {   // php-4.3.5��0�֤� php-4.3.6��1���֤����ͤˤʤä���fgetcsv�λ����ѹ��ˤ��
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
                fwrite($fpb, "$log_date AS/400 del record=$rec \n");
                echo "$log_date AS/400 del record=$rec \n";
            } else {
                fwrite($fpa, "$log_date field not 10 record=$rec \n");
                fwrite($fpb, "$log_date field not 10 record=$rec \n");
                echo "$log_date field not 10 record=$rec \n";
            }
           continue;
        }
        //$data[7]  = trim($data[7]);    // ê�֤�̵�̤�;�����
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
            
        } else {
            ///// ��Ͽ���� update ����
            $query = "UPDATE parts_stock_master SET
                            parts_no      ='{$data[0]}',
                            stock_id      ='{$data[1]}'
                WHERE parts_no='{$data[0]}'
            ";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
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
    fwrite($fpa, "$log_date TNK�����С�¦�쥳���� : $rec_tnk/$rec ����Ͽ�ѤߤǤ���\n");
    fwrite($fpa, "$log_date �߸˥ޥ�������ʬ�ι��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date �߸˥ޥ�������ʬ�ι��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date �߸˥ޥ�������ʬ�ι��� : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpb, "$log_date TNK�����С�¦�쥳���� : $rec_tnk/$rec ����Ͽ�ѤߤǤ���\n");
    fwrite($fpb, "$log_date �߸˥ޥ�������ʬ�ι��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date �߸˥ޥ�������ʬ�ι��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpb, "$log_date �߸˥ޥ�������ʬ�ι��� : {$upd_ok}/{$rec} �� �ѹ� \n");
    echo "$log_date TNK�����С�¦�쥳���� : $rec_tnk/$rec ����Ͽ�ѤߤǤ���\n";
    echo "$log_date �߸˥ޥ�������ʬ�ι��� : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date �߸˥ޥ�������ʬ�ι��� : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date �߸˥ޥ�������ʬ�ι��� : {$upd_ok}/{$rec} �� �ѹ� \n";
} else {
    fwrite($fpa,"$log_date �ե�����$file_orign ������ޤ���!\n");
    fwrite($fpb,"$log_date �ե�����$file_orign ������ޤ���!\n");
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
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߽�λ
?>
