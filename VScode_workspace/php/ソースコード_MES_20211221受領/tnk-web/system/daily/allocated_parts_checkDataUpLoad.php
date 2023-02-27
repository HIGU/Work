#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// �����ǡ�����AS/400�Ȥδ����ǡ�����󥯤Τ��� CHECK DATA UPLOAD   CLI��   //
// Web Server (PHP) ----> AS/400 AS/400�Υե������ ���� = *ALL �Ǻ���      //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/02/09 Created  allocated_parts_checkDataUpLoad.php                  //
// 2007/02/14 ��������ľ���Ǽ�������UPLOAD�������˽�����ɲ�          //
// 2007/02/28 ���Ѱ����Υ��ƥʥ��б��Τ���ʲ���SQLʸ�򥳥���         //
//         AND (plan_no LIKE 'C%' OR plan_no LIKE 'L%' OR plan_no LIKE '@%')//
// 2007/07/30 AS/400�Ȥ�FTP error �б��Τ��� ftpCheckAndExecute()�ؿ����ɲ� //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI�Ǥϴط��ʤ�
require_once ('/home/www/html/tnk-web/function.php');
sleep(3);      // cron�Ǽ¹ԤʤΤ�¾�Υץ����Ȥζ�����θ���ƣ������ٱ䤹�롣

$log_date = date('Y-m-d H:i:s');            // �����ѥ�������
$fpLog = fopen('/tmp/nippo.log', 'a');      // �����ѥ��ե�����ؤν���ߤǥ����ץ�

$query = "
    SELECT plan_no, parts_no, assy_no, unit_qt, allo_qt, sum_qt, assy_str FROM allocated_parts
    WHERE parts_no NOT LIKE '9%' AND (allo_qt-sum_qt) > 0
        -- AND (plan_no LIKE 'C%' OR plan_no LIKE 'L%' OR plan_no LIKE '@%')
    ORDER BY parts_no DESC, (allo_qt-sum_qt) DESC LIMIT 50000 OFFSET 0
";
//        (plan_no LIKE 'C%' OR plan_no LIKE 'L%' OR plan_no LIKE '@%')
//    ORDER BY parts_no DESC, (allo_qt-sum_qt) DESC LIMIT 100 OFFSET 40650
if ( ($rows=getResult2($query, $res)) <= 0) {
    fwrite($fpLog,"$log_date ���������å��ѥǡ���������ޤ���Ǥ�����\n");
    fclose($fpLog);      ////// ������߽�λ
    exit();
}
define('UPLOADF', '/home/www/html/tnk-web/system/backup/W#MIALLS.TXT');  // �������ʥե����� Check File
$fp = fopen(UPLOADF, 'w');
for ($i=0; $i<$rows; $i++) {
    $data = sprintf('%8s_%9s_%9s_%8s_%7s_%7s_%8s', $res[$i][0], $res[$i][1], $res[$i][2], $res[$i][3], $res[$i][4], $res[$i][5], $res[$i][6]);
    fwrite($fp,"{$data}\n");
}
fclose($fp);

// FTP�Υ�⡼�ȥե�����
define('AS400_FILE', 'UKWLIB/W#MIALLS');    // ���������å��ե����� Remote File
// FTP�Υ�����ե�����
define('LOCAL_FILE', UPLOADF);              // ���������å��ե����� Local File

// ���ͥ���������(FTP��³�Υ����ץ�)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// ���������å��ե�����Υ��åץ���
        if (ftpCheckAndExecute($ftp_stream, AS400_FILE, LOCAL_FILE, FTP_ASCII)) {
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

function ftpCheckAndExecute($stream, $as400_file, $local_file, $ftp)
{
    $errorLogFile = '/tmp/as400FTP_Error.log';
    $trySec = 10;
    
    $flg = @ftp_put($stream, $as400_file, $local_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_put Error try1 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    sleep($trySec);
    
    $flg = @ftp_put($stream, $as400_file, $local_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_put Error try2 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    sleep($trySec);
    
    $flg = @ftp_put($stream, $as400_file, $local_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_put Error try3 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    
    return $flg;
}
?>
