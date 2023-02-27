#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// �����ǡ�����AS/400�Ȥδ����ǡ�����󥯤Τ��� CHECK DATA UPLOAD   CLI��   //
// Web Server (PHP) ----> AS/400 AS/400�Υե������ ���� = *ALL �Ǻ���      //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/02/16 Created  order_plan_checkDataUpLoad.php                       //
// 2007/02/19 order_q �� zan_q �ˤʤäƤ���ߥ�����(������order_q�����)  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI�Ǥϴط��ʤ�
require_once ('/home/www/html/tnk-web/function.php');
sleep(3);      // cron�Ǽ¹ԤʤΤ�¾�Υץ����Ȥζ�����θ���ƣ������ٱ䤹�롣

$log_date = date('Y-m-d H:i:s');            // �����ѥ�������
$fpLog = fopen('/tmp/nippo.log', 'a');      // �����ѥ��ե�����ؤν���ߤǥ����ץ�

$query = "
    SELECT sei_no, order5, plan.parts_no, plan.order_q, utikiri, nyuko
    FROM order_plan AS plan LEFT OUTER JOIN order_process USING(sei_no)
    WHERE  zan_q>0 AND order_process.order_no IS NULL
    ORDER BY sei_no ASC
";
if ( ($rows=getResult2($query, $res)) <= 0) {
    fwrite($fpLog,"$log_date ȯ��ײ�����å��ѥǡ���������ޤ���Ǥ�����\n");
    fclose($fpLog);      ////// ������߽�λ
    exit();
}
define('UPLOADF', '/home/www/html/tnk-web/system/backup/W#MIOPLS.TXT');  // ȯ��ײ�ե����� Check File
$fp = fopen(UPLOADF, 'w');
for ($i=0; $i<$rows; $i++) {
    $data = sprintf('%7s_%5s_%9s_%8s_%8s_%8s', $res[$i][0], $res[$i][1], $res[$i][2], $res[$i][3], $res[$i][4], $res[$i][5]);
    fwrite($fp,"{$data}\n");
}
fclose($fp);

// FTP�Υ�⡼�ȥե�����
define('AS400_FILE', 'UKWLIB/W#MIOPLS');    // ȯ��ײ�����å��ե����� Remote File
// FTP�Υ�����ե�����
define('LOCAL_FILE', UPLOADF);              // ȯ��ײ�����å��ե����� Local File

// ���ͥ���������(FTP��³�Υ����ץ�)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// ���������å��ե�����Υ��åץ���
        if (ftp_put($ftp_stream, AS400_FILE, LOCAL_FILE, FTP_ASCII)) {
            // echo 'ftp_put UPLOAD OK ', LOCAL_FILE, '��', AS400_FILE, "\n";
            $log_date = date('Y-m-d H:i:s');            // �����ѥ�������
            fwrite($fpLog,"$log_date ftp_put UPLOAD OK ���:{$rows} " . LOCAL_FILE . '��' . AS400_FILE . "\n");
        } else {
            // echo 'ftp_put() error ', AS400_FILE, "\n";
            $log_date = date('Y-m-d H:i:s');            // �����ѥ�������
            fwrite($fpLog,"$log_date ftp_put() error ���:{$rows} " . AS400_FILE . "\n");
        }
    } else {
        // echo "ftp_login() error \n";
        $log_date = date('Y-m-d H:i:s');            // �����ѥ�������
        fwrite($fpLog,"$log_date ftp_login() error ���:{$rows} \n");
    }
    ftp_close($ftp_stream);
} else {
    // echo "ftp_connect() error --> ���������å���ư��������\n";
    $log_date = date('Y-m-d H:i:s');            // �����ѥ�������
    fwrite($fpLog,"$log_date ftp_connect() error ���:{$rows} --> ���������å���ư��������\n");
}


fclose($fpLog);      ////// ������߽�λ

exit();


?>
