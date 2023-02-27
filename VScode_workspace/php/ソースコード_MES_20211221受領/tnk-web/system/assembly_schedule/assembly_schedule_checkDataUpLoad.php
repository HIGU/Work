#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�ײ�ǡ�����ʬ��AS/400�Ȥδ����ǡ������ CHECK DATA UPLOAD   CLI�� //
// Web Server (PHP) ----> AS/400 AS/400�Υե������ ���� = *ALL �Ǻ���      //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/05/28 Created  assembly_schedule_checkDataUpLoad.php                //
// 2007/05/29 ������������������  �������袪�������� ���ѹ�                 //
// 2007/07/30 AS/400�Ȥ�FTP error�б��Τ���ftp???CheckAndExecute()�ؿ����ɲ�//
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI�Ǥϴط��ʤ�

$currentFullPathName = realpath(dirname(__FILE__));
require_once ("{$currentFullPathName}/../../function.php");

// sleep(3);      // industry_hourly_cli.php����¹ԤʤΤǥ�����

$log_date = date('Y-m-d H:i:s');            // �����ѥ�������
$fpLog = fopen('/tmp/nippo.log', 'a');      // �����ѥ��ե�����ؤν���ߤǥ����ץ�

fwrite($fpLog, "$log_date ��Ω�����ײ����������ʬ�ǡ���ȴ�Ф���UPLOAD���� \n");

/////////// �������λ���
$year = date('Y'); $month = date('m');
if ($month == 1) {
    $month = 12;
    $year -= 1;
} else {
    $month -= 2;    // 2������
    $month = sprintf('%02d', $month);
}
$startDate = ($year . $month . '01');
/////////// ��λ���λ���
$year = date('Y'); $month = date('m');
$month += 5;        // 5������
if ($month > 12) {
    $month -= 12;
    $year  += 1;
}
$month = sprintf('%02d', $month);
$endDate = ($year . $month . '01');

$query = "
    SELECT
        plan_no, parts_no, syuka, chaku, kanryou, plan, cut_plan, kansei, nyuuko, sei_kubun, line_no, p_kubun, assy_site, dept
    FROM
        assembly_schedule
    WHERE
        kanryou >= {$startDate} AND kanryou < {$endDate} AND plan_no LIKE '@%' AND (plan-cut_plan-kansei) > 0
        -- AND assy_site='01111'
";
if ( ($rows=getResult2($query, $res)) <= 0) {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpLog, "$log_date ��Ω�����ײ� �оȥǡ���������ޤ���$startDate �� $endDate \n");
    fclose($fpLog);
    exit();
}
define('UPLOADF', "{$currentFullPathName}/backup/W#MIPPLS.TXT");  // ��Ω���������������ե����� Check File
$fp = fopen(UPLOADF, 'w');
for ($i=0; $i<$rows; $i++) {
    $data = sprintf('%8s_%9s_%8s_%8s_%8s_%8s_%8s_%8s_%2s_%1s_%4s_%1s_%5s_%1s',
        $res[$i][0], $res[$i][1], $res[$i][2], $res[$i][3], $res[$i][4], $res[$i][5], $res[$i][6],
        $res[$i][7], $res[$i][8], $res[$i][9], $res[$i][10], $res[$i][11], $res[$i][12], $res[$i][13]);
    fwrite($fp,"{$data}\n");
}
fclose($fp);

// ��¾�����ѥ���ȥ���ե�����
define('CMIPPL', 'UKWLIB/C#MIPPL');        // ��Ω�����ײ��������������ȥ���
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
define('C_MIPPL', "{$currentFullPathName}/backup/C#MIPPL.TXT");

// FTP�Υ�⡼�ȥե�����
define('AS400_FILE', 'UKWLIB/W#MIPPLS');    // ��Ω�����ײ���������������å��ե����� Remote File
// FTP�Υ�����ե�����
define('LOCAL_FILE', UPLOADF);              // ��Ω�����ײ���������������å��ե����� Local File

// ���ͥ���������(FTP��³�Υ����ץ�)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// ��Ω�����ײ��������������ȥ���ե���������å�
        if (ftpGetCheckAndExecute($ftp_stream, C_MIPPL, CMIPPL, FTP_ASCII)) {
            $log_date = date('Y-m-d H:i:s');        ///// ��������
            fwrite($fpLog,"$log_date ftp_get ����ȥ��� download OK " . CMIPPL . '��' . C_MIPPL . "\n");
            if (checkControlFile($fpLog, C_MIPPL)) {
                fwrite($fpLog,"$log_date ����ȥ���ե�����˥ǡ���������Τǽ�λ���ޤ���\n");
                ftp_close($ftp_stream);
                fclose($fpLog);      ////// ������λ
                exit();
            }
        } else {
            $log_date = date('Y-m-d H:i:s');        ///// ��������
            fwrite($fpLog,"$log_date ftp_get() ����ȥ��� error " . CMIPPL . "\n");
            ftp_close($ftp_stream);
            fclose($fpLog);      ////// ������λ
            exit();
        }
        ///// ��Ω�����������å��ե�����Υ��åץ���
        if (ftpPutCheckAndExecute($ftp_stream, AS400_FILE, LOCAL_FILE, FTP_ASCII)) {
            // echo 'ftp_put UPLOAD OK ', LOCAL_FILE, '��', AS400_FILE, "\n";
            $log_date = date('Y-m-d H:i:s');            // �����ѥ�������
            fwrite($fpLog,"$log_date ��Ω�����ײ����������ʬftp_put UPLOAD OK ���:{$rows} " . LOCAL_FILE . '��' . AS400_FILE . "\n");
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
    // echo "ftp_connect() error --> ��Ω�����������å���ư��������\n";
    $log_date = date('Y-m-d H:i:s');            // �����ѥ�������
    fwrite($fpLog,"$log_date ftp_connect() error ���:{$rows} --> ��Ω�����������å���ư��������\n");
}


fclose($fpLog);      ////// ������߽�λ

exit();


function checkControlFile($fpa, $ctl_file)
{
    $fp = fopen($ctl_file, 'r');
    $data = fgets($fp, 20);     // �¥쥳���ɤ�11�Х��ȤʤΤǤ���ä�;͵��
    if (feof($fp)) {
        return false;
    } else {
        $log_date = date('Y-m-d H:i:s');        ///// ��������
        fwrite($fpa, "$log_date ��Ω�ײ���������� : ���Ѿ����� {$data}");
        return true;
    }
}

function ftpGetCheckAndExecute($stream, $local_file, $as400_file, $ftp)
{
    $errorLogFile = '/tmp/as400FTP_Error.log';
    $trySec = 10;
    
    $flg = @ftp_get($stream, $local_file, $as400_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_get Error try1 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    sleep($trySec);
    
    $flg = @ftp_get($stream, $local_file, $as400_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_get Error try2 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    sleep($trySec);
    
    $flg = @ftp_get($stream, $local_file, $as400_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_get Error try3 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    
    return $flg;
}


function ftpPutCheckAndExecute($stream, $as400_file, $local_file, $ftp)
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
