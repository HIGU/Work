#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ����Ǽ������ AS/400<-->TNK�����С�Ʊ�� ��� ������                       //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright(C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed histoy                                                           //
// 2007/04/26 Created   order_delivery_answer_get_ftp-ini.php               //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI�Ǥ�ɬ�פʤ�
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// ��������
$fpa = fopen('/tmp/nippo.log', 'a');    ///// ���ե�����ؤν���ߤǥ����ץ�

// ��¾�����ѥ���ȥ���ե�����
define('CHIDLIV', 'UKWLIB/C#HIDLIV');   // ����Ǽ����������ȥ���
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
define('C_HIDLIV', '/home/www/html/tnk-web/system/backup/C#HIDLIV.TXT');  // ����Ǽ����������ȥ���

// �������åȥե�����
define('HIDLIV', 'UKWLIB/W#HIDLIV');    // ����Ǽ�������ե�����
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
define('W_HIDLIV', '/home/www/html/tnk-web/system/backup/W#HIDLIV.TXT');  // ����Ǽ������
// AS/400�Υե��������ˤ��뤿��Υ��ߡ��ե�����̾
define('LOCAL_FILE', '/home/www/html/tnk-web/system/backup/W#HIDLIV-clear.TXT');


/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    // query_affected_trans($con, 'BEGIN');
} else {
    $log_date = date('Y-m-d H:i:s');        ///// ��������
    fwrite($fpa,"$log_date db_connect() error \n");
    exit();
}
// Ʊ������ �������
if (file_exists(W_HIDLIV)) {         // �ե������¸�ߥ����å�
    $fp = fopen(W_HIDLIV, 'r');
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    while (1) {
        $data = fgetcsv($fp, 50, ',');     // �¥쥳���ɤ�31�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���'_'�����
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num < 4) continue;     // �ե�����ɿ��Υ����å�
        if (!$data[0]) continue;    // �ײ襪������(��¤�ֹ�)��0�Τ�Τ�����Τǥ����å�
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
        }
        
        $query_chk = "
            SELECT * FROM order_delivery_answer
            WHERE sei_no={$data[0]} AND order_no={$data[1]} AND vendor='{$data[2]}'
        ";
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "
                INSERT INTO order_delivery_answer (sei_no, order_no, vendor, delivery)
                VALUES({$data[0]}, {$data[1]}, '{$data[2]}', {$data[3]})
            ";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                $log_date = date('Y-m-d H:i:s');        ///// ��������
                fwrite($fpa,"$log_date ����Ǽ������ : sei_no={$data[0]} AND order_no={$data[1]} AND vendor='{$data[2]}' delivery={$data[3]} ".($rec).":�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                // query_affected_trans($con, 'ROLLBACK');     // transaction rollback
                $rec_ng++;
            } else {
                $rec_ok++;
            }
        } else {
            ///// ��Ͽ���� update ����
            $query = "
                UPDATE order_delivery_answer SET sei_no={$data[0]}, order_no={$data[1]}, vendor='{$data[2]}',
                    delivery={$data[3]}
                WHERE sei_no={$data[0]} AND order_no={$data[1]} AND vendor='{$data[2]}'
            ";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                $log_date = date('Y-m-d H:i:s');        ///// ��������
                fwrite($fpa,"$log_date ����Ǽ������ : sei_no={$data[0]} AND order_no={$data[1]} AND vendor='{$data[2]}' delivery={$data[3]} ".($rec).":�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                // query_affected_trans($con, 'ROLLBACK');     // transaction rollback
                $rec_ng++;
            } else {
                $rec_ok++;
            }
        }
    }
    fclose($fp);
    $log_date = date('Y-m-d H:i:s');        ///// ��������
    fwrite($fpa, "$log_date ����Ǽ������ : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    
} else {
    fwrite($fpa, "$log_date �ե����� " . W_HIDLIV . " ������ޤ���!\n");
}


fclose($fpa);      ////// �����ѥ�����߽�λ
/////////// commit �ȥ�󥶥������λ
// query_affected_trans($con, 'COMMIT');



function checkControlFile($fpa, $ctl_file)
{
    $fp = fopen($ctl_file, 'r');
    $data = fgets($fp, 20);     // �¥쥳���ɤ�11�Х��ȤʤΤǤ���ä�;͵��
    if (feof($fp)) {
        return false;
    } else {
        $log_date = date('Y-m-d H:i:s');        ///// ��������
        fwrite($fpa, "$log_date ����Ǽ������ : ����ü���� {$data}");
        return true;
    }
}
?>
