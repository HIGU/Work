#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-5.0.2-cli                                           //
// ��������(���������)����(daily)����   AS/400 UKWLIB/W#MIADIM             //
//   AS/400 ----> Web Server (PHP) PCIX��FTPž���Ѥ�ʪ�򹹿�����            //
// Copyright(C) 2004-2020 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2004/10/15 �������� aden_daily_cli.php aden_master_update.php���¤      //
// �ѹ�����   http �� cli�Ǥ��ѹ������褦�� requier_once �����л����     //
//            AS/400�� RUNQRY QRY(UKPLIB/Q#MIADIM) �Ǽ¹Ԥ����Τޤ޹������� //
// 2003/11/28 ���򥳥��Ȥˤ��Ƥ����Τ� monthly_update.log �ˤ����ɲ�    //
// 2004/01/05 �ǡ�����"������fgetcsv�Υ��ץ�����'`'�ե�����ɰϤ��Ҥ��ѹ� //
// 2004/01/20 ��������θ��ե�����Υǥ��쥯�ȥ��ѹ� /home/guest/daily ��   //
// 2004/04/05 header('Location: http:' . WEB_HOST . 'account/?????' -->     //
//                                  header('Location: ' . H_WEB_HOST . ACT  //
// 2004/10/15 cron��Ȥä��������塼����ѹ�                                //
// 2004/12/13 #!/usr/local/bin/php-5.0.2-cli �� php (������5.0.3RC2)���ѹ�  //
// 2009/12/18 ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤ��ɲ�           ��ë //
// 2010/01/19 �᡼���ʬ����䤹������١����ա�����������ɲ�       ��ë //
// 2020/08/17 ����̾��_�����äƤ����Τ����ä��Τ�|���ѹ�             ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��cli��)
// ini_set('display_errors','1');              // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');        ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤǥ����ץ�
fwrite($fpb, "��������ι���\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/daily_aden_cli.php\n");

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date �����ι��� db_connect() error \n");
    fwrite($fpb, "$log_date �����ι��� db_connect() error \n");
    echo "$log_date �����ι��� db_connect() error \n\n";
    exit();
}
///////// ��������ե�����ι��� �������
$file_orign  = '/home/guest/daily/W#MIADIM.TXT';
$file_backup = '/home/guest/daily/backup/W#MIADIM-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-MIADIM.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 200, '|', '`');     // �¥쥳���ɤ�117�Х��� �ǥ�ߥ��� '|' field�Ϥ��Ҥ�'`'�Хå���������
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num != 13) {           // �ե�����ɿ��Υ����å�
            fwrite($fpa, "$log_date field not 13 record=$rec \n");
            fwrite($fpb, "$log_date field not 13 record=$rec \n");
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
        
        $query_chk = sprintf("SELECT aden_no FROM aden_master WHERE aden_no='%s' and eda_no=%d", $data[0], $data[1]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "INSERT INTO aden_master (aden_no, eda_no, parts_no, sale_name, plan_no, approval,
                      ropes_no, kouji_no, order_q, order_price, espoir_deli, delivery, publish_day)
                      VALUES(
                      '{$data[0]}',
                       {$data[1]} ,
                      '{$data[2]}',
                      '{$data[3]}',
                      '{$data[4]}',
                      '{$data[5]}',
                      '{$data[6]}',
                      '{$data[7]}',
                       {$data[8]} ,
                       {$data[9]} ,
                       {$data[10]} ,
                       {$data[11]} ,
                       {$data[12]} )";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
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
            $query = "UPDATE aden_master SET
                            aden_no    ='{$data[0]}',
                            eda_no     = {$data[1]} ,
                            parts_no   ='{$data[2]}',
                            sale_name  ='{$data[3]}',
                            plan_no    ='{$data[4]}',
                            approval   ='{$data[5]}',
                            ropes_no   ='{$data[6]}',
                            kouji_no   ='{$data[7]}',
                            order_q    = {$data[8]} ,
                            order_price= {$data[9]} ,
                            espoir_deli= {$data[10]},
                            delivery   = {$data[11]},
                            publish_day= {$data[12]}
                      where aden_no='{$data[0]}' and eda_no={$data[1]}";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �����ֹ�:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
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
    fwrite($fpa, "$log_date �����ι��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date �����ι��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date �����ι��� : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpb, "$log_date �����ι��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date �����ι��� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpb, "$log_date �����ι��� : {$upd_ok}/{$rec} �� �ѹ� \n");
    echo "$log_date �����ι��� : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date �����ι��� : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date �����ι��� : {$upd_ok}/{$rec} �� �ѹ� \n";
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
    fwrite($fpa, "$log_date : ��������ι����ե����� {$file_orign} ������ޤ���\n");
    fwrite($fpb, "$log_date : ��������ι����ե����� {$file_orign} ������ޤ���\n");
    echo "$log_date : ��������ι����ե����� {$file_orign} ������ޤ���\n";
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');
fclose($fpa);      ////// �����ѥ�����߽�λ
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߽�λ
?>
