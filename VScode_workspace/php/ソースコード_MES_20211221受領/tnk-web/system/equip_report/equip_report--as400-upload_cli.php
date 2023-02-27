#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ������ž����(��¤��) �ǡ��� ��ưFTP UPLOAD CLI��                         //
// Web Server (PHP) ----> AS/400                                            //
// Copyright (C) 2004-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2004/08/05 Created   equip_report--as400-upload_cli.php                  //
// 2005/07/06 error������t��fclose()/header()�����ս�ȴ���Ƥ����Τ��ɲ�     //
// 2007/03/29 ������ž����Υơ��֥�equip_upload�Υ쥤�������ѹ��ڤӥ��ޥ꡼//
//            equip_upload_summary �ɲäˤ����å����ɲ��ѹ�             //
//            equip_upload �ɹ��߻��� ORDER BY �� from_time ASC �ɲ�        //
// 2007/03/30 equip_upload_summary �ơ��֥�� �ȥ�󥶥����������ɲ�      //
// 2007/03/31 ROLLBACK �� ��λ�ե饰�Σ������å����ɲ�                      //
// 2007/04/07 AS/400�˵�ǡ��������뤫�����å�����ؿ�old_data_check()�ɲ�  //
// 2007/04/11 ���١����ޥ꡼�η��������ɲ�    CLI�Ǥ����               //
//      equip_report--as400-upload.php �� equip_report--as400-upload_cli.php//
// 2007/05/02 ���٤Τߤ� ORDER BY �����ä��������ޥ꡼�ˤ� ORDER BY ���ɲ�  //
//          ��餫�飲�Ķ����ޤǤ�����ε�����ž����Ϲ������ʤ����å��ɲ�//
// 2007/05/07 ��¾�����ѤΥ���ȥ���ե�����Υǡ��������å����ɲ�        //
// 2007/08/31 AS/400�Ȥ�FTP���顼����Τ���ftpPutCheckAndExecute()���ɲ�    //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ
// session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

$currentFullPathName = realpath(dirname(__FILE__));
require_once ("{$currentFullPathName}/../../function.php");
// access_log();                               // Script Name �ϼ�ư����

$log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
$fpa = fopen('/tmp/equip_report.log', 'a'); ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�

// ��¾�����ѥ���ȥ���ե�����
define('AS_CTGMAP', 'UKWLIB/C#TGMAP');      // ������ž���󥳥�ȥ���
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
define('C_TGMAP', "{$currentFullPathName}/backup/C#TGMAP.TXT"); // ������ž���󥳥�ȥ���

// FTP�Υ�⡼�ȥե�����
define('REMOTE_F1', 'UKWLIB/TGMATMP');      // AS/400�μ����ե����� ����
define('REMOTE_F2', 'UKWLIB/TGMADVP');      // AS/400�μ����ե����� ���ޥ꡼
// ������α�ž���� ���� �ե�����
define('UPLOAD_F1', "{$currentFullPathName}/backup/equip_upload.log");         // ������ž���� ���� text�ǡ���
// ������α�ž���� ���ޥ꡼ �ե�����
define('UPLOAD_F2', "{$currentFullPathName}/backup/equip_upload_summary.log"); // ������ž���� ���ޥ꡼ text�ǡ���

/////////// AS/400�ε�ǡ���������å�
if (!old_data_check($fpa, $currentFullPathName)) {
    $log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
    fwrite($fpa, "$log_date ������ž���� �ε�ǡ����� AS/400 �˻ĤäƤ��ޤ��Τǽ�������ߤ��ޤ����� \n");
    fclose($fpa);   ///// �����ѥ��ե�����Υ�����
    exit();
}

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'BEGIN');
    query_affected_trans($con, 'LOCK equip_upload');            // ���� �ơ��֥���å�����
    query_affected_trans($con, 'LOCK equip_upload_summary');    // ���ޥ꡼ �ơ��֥���å�����
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    fclose($fpa);   ///// �����ѥ��ե�����Υ�����
    exit();
}

///// ��餫�飲�Ķ����ޤǤ�����ε�����ž����Ϲ������ʤ�
///// ��¤�ݤλųݡ�ê�������Τ���ʻųݥꥹ�Ȥ�����Ѳ����Ƥ��ޤ������
if (workingDayCheck($con, date('Ymd')) <= 2) {
    $dateStart = date('Ym') . '01';
    $where = "WHERE work_date < {$dateStart}";
} else {
    $where = '';
}

///// ������ž����� ���� ���åץ����ѥơ��֥뤫��ǡ�������
$query = "
    SELECT  
        siji_no
        , work_date
        , mac_no
        , koutei
        , from_time
        , to_time
        , cut_time
        , mac_state
    FROM
        equip_upload
    {$where}
    ORDER BY
        work_date ASC, mac_no ASC, siji_no ASC, koutei ASC, from_time ASC
";
$res = array();
if ( ($rows=getResultTrs($con, $query, $res)) < 1) {
    $log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
    fwrite($fpa, "$log_date ������ž���� ���٤� ����ǡ���������ޤ��� \n");
    fclose($fpa);   ///// �����ѥ��ե�����Υ�����
    query_affected_trans($con, 'ROLLBACK');
    exit();
} else {
    $fp = fopen(UPLOAD_F1, 'w');     ///// ������ ���� �ե�����ؤν���ߤǥ����ץ�
    for ($i=0; $i<$rows; $i++) {
        $log_record = sprintf("%5s%8s%4s%2s%4s%4s%4s%1s\n", $res[$i][0], $res[$i][1], $res[$i][2], $res[$i][3], $res[$i][4], $res[$i][5], $res[$i][6], $res[$i][7]);
        if (fwrite($fp, $log_record) == FALSE) {
            $log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
            fwrite($fpa, "$log_date ���٤�UPLOAD�ǡ������� error \n");
            fclose($fpa);   ///// �����ѥ��ե�����Υ�����
            query_affected_trans($con, 'ROLLBACK');
            exit();
        }
    }
    $sql = "
        INSERT INTO equip_upload_history
        SELECT * FROM equip_upload {$where}
        ;
        DELETE FROM equip_upload {$where}
    ";
    query_affected_trans($con, $sql);
}
fclose($fp);   ///// ���� �ե�����Υ�����
$log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
fwrite($fpa, "$log_date ������ž���� ���٥ǡ��� $rows �� \n");


///// ������ž����� ���ޥ꡼ ���åץ����ѥơ��֥뤫��ǡ�������
$query = "
    SELECT
        work_date       ,
        mac_no          ,
        siji_no         ,
        koutei          ,
        item_code       ,
        plan_time       ,
        running_time    ,
        repair_time     ,
        edge_time       ,
        stop_time       ,
        idling_time     ,
        auto_time       ,
        others_time     ,
        ok_item_num     ,
        ng_item_num     ,
        plan_num        ,
        end_flg         ,
        ng_code         ,
        stop_count      ,
        plan_count      ,
        repair_count    ,
        processing_date ,
        injection_item  ,
        injection       
    FROM
        equip_upload_summary
    {$where}
    ORDER BY
        work_date ASC, mac_no ASC, siji_no ASC, koutei ASC
";
$res = array();
if ( ($rows=getResultTrs($con, $query, $res)) < 1) {
    $log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
    fwrite($fpa, "$log_date ������ž���� ���ޥ꡼�� ����ǡ���������ޤ��� \n");
    fclose($fpa);   ///// �����ѥ��ե�����Υ�����
    query_affected_trans($con, 'ROLLBACK');
    exit();
} else {
    $fp = fopen(UPLOAD_F2, 'w');     ///// ���� ���ޥ꡼ �ե�����ؤν���ߤǥ����ץ�
    for ($i=0; $i<$rows; $i++) {
        if ($res[$i][16] != 'E') $res[$i][16] = ' ' ;   // ��λ�ե饰(end_flg)������å��������äƤ��ޤ��к�
        $log_record = sprintf("%8s%4s%8s%2s%9s%4s%4s%4s%4s%4s%4s%4s%4s%5s%5s%5s%1s%2s%3s%3s%3s%8s%7s%9s\n",
            $res[$i][0], $res[$i][1], $res[$i][2], $res[$i][3], $res[$i][4], $res[$i][5], $res[$i][6], $res[$i][7],
            $res[$i][8], $res[$i][9], $res[$i][10], $res[$i][11], $res[$i][12], $res[$i][13], $res[$i][14], $res[$i][15],
            $res[$i][16], $res[$i][17], $res[$i][18], $res[$i][19], $res[$i][20], $res[$i][21], $res[$i][22], $res[$i][23]
        );
        if (fwrite($fp, $log_record) == FALSE) {
            $log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
            fwrite($fpa, "$log_date ���ޥ꡼��UPLOAD�ǡ������� error \n");
            fclose($fpa);   ///// �����ѥ��ե�����Υ�����
            query_affected_trans($con, 'ROLLBACK');
            exit();
        }
    }
    $sql = "
        INSERT INTO equip_upload_summary_history
        SELECT * FROM equip_upload_summary {$where}
        ;
        DELETE FROM equip_upload_summary {$where}
    ";
    query_affected_trans($con, $sql);
}
fclose($fp);   ///// ���ޥ꡼ �ե�����Υ�����
$log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
fwrite($fpa, "$log_date ������ž���� ���ޥ꡼�ǡ��� $rows �� \n");



/////////// UPLOAD�ǡ������� OK
$log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
fwrite($fpa, "$log_date ������ž���� ��UPLOAD�ǡ������� OK \n");



////////// FTP ž������
// OK NG �ե饰���å�
$ftp_flg = false;
// ������ž���� ���٤ȥ��ޥ꡼������ǡ���¸�ߥ����å�
if (file_exists(UPLOAD_F1) && file_exists(UPLOAD_F2)) {
    // ���ͥ���������(FTP��³�Υ����ץ�)
    if ($ftp_stream = ftp_connect(AS400_HOST)) {
        if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
            ///// ����ȥ���ե�����Υ쥳���ɥ����å�����¾�����
            if (ftpGetCheckAndExecute($ftp_stream, C_TGMAP, AS_CTGMAP, FTP_ASCII)) {
                $log_date = date('Y-m-d H:i:s');        ///// ��������
                fwrite($fpa,"$log_date ftp_get ����ȥ��� download OK " . AS_CTGMAP . '��' . C_TGMAP . "\n");
                if (checkControlFile($fpa, C_TGMAP)) {
                    fwrite($fpa,"$log_date ����ȥ���ե�����˥ǡ���������ǽ�λ���ޤ���\n");
                    ftp_close($ftp_stream);
                    query_affected_trans($con, 'ROLLBACK');
                    fclose($fpa);      ////// ������λ
                    exit();
                }
            } else {
                $log_date = date('Y-m-d H:i:s');        ///// ��������
                fwrite($fpa,"$log_date ftp_get() ����ȥ��� error " . AS_CTGMAP . "\n");
                ftp_close($ftp_stream);
                query_affected_trans($con, 'ROLLBACK');
                fclose($fpa);      ////// ������λ
                exit();
            }
            ///// ������ž���� ���٥ǡ�����UPLOAD����
            if (ftpPutCheckAndExecute($ftp_stream, REMOTE_F1, UPLOAD_F1, FTP_ASCII)) {
                $log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
                fwrite($fpa,"$log_date ������ž���� ���� ftp_put upload OK " . UPLOAD_F1 . '��' . REMOTE_F1 . "\n");
                $ftp_flg = true;
            } else {
                $log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
                fwrite($fpa,"$log_date ������ž���� ���� ftp_put() upload error " . REMOTE_F1 . "\n");
            }
            ///// ������ž���� ���ޥ꡼�ǡ�����UPLOAD����
            if (ftpPutCheckAndExecute($ftp_stream, REMOTE_F2, UPLOAD_F2, FTP_ASCII)) {
                $log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
                fwrite($fpa,"$log_date ������ž���� ���ޥ꡼ ftp_put upload OK " . UPLOAD_F2 . '��' . REMOTE_F2 . "\n");
                if ($ftp_flg) $ftp_flg = true; else $ftp_flg = false;
            } else {
                $log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
                fwrite($fpa,"$log_date ������ž���� ���ޥ꡼ ftp_put() upload error " . REMOTE_F2 . "\n");
                $ftp_flg = false;
            }
        } else {
            $log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
            fwrite($fpa,"$log_date ������ž���� ftp_login() error \n");
        }
        ftp_close($ftp_stream);
    } else {
        $log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
        fwrite($fpa,"$log_date ������ž���� ftp_connect() error\n");
    }
} else {
    $log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
    fwrite($fpa,"$log_date ������ž���� �������ϥ��ޥ꡼ ����ե����뤬����ޤ���\n");
}



/////////// commit �ȥ�󥶥������λ
if ($ftp_flg) {
    query_affected_trans($con, 'COMMIT');
} else {
    query_affected_trans($con, 'ROLLBACK');
}
fclose($fpa);   ///// �����ѥ��ե�����Υ�����
exit();



///////////////// AS/400 �˵�ǡ��������뤫�����å�����
function old_data_check($fpa, $dir)
{
    $log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
    // FTP�Υ������åȥե����� �嵭�ǻ��ꤵ��Ƥ���
    // ��¸��Υǥ��쥯�ȥ�ȥե�����̾
    define('OLD_DATA', "{$dir}/backup/equip_download.txt");   // save file
    
    // ���ͥ���������(FTP��³�Υ����ץ�)
    if ($ftp_stream = ftp_connect(AS400_HOST)) {
        if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
            ///// ȯ��ײ�ե�����
            if (ftpGetCheckAndExecute($ftp_stream, OLD_DATA, REMOTE_F2, FTP_ASCII)) {
                fwrite($fpa,"$log_date ��ǡ��������å��� download OK " . REMOTE_F2 . '��' . OLD_DATA . "\n");
            } else {
                fwrite($fpa,"$log_date ftp_get() error " . REMOTE_F2 . "\n");
                return false;
            }
        } else {
            fwrite($fpa,"$log_date ftp_login() error \n");
            return false;
        }
        ftp_close($ftp_stream);
    } else {
        fwrite($fpa,"$log_date ftp_connect() error -->\n");
        return false;
    }
    if (file_exists(OLD_DATA)) {         // �ե������¸�ߥ����å�
        $fpt = fopen(OLD_DATA, 'r');
        $i = 0;
        while (!(feof($fpt))) {
            $data = fgets($fpt, 300);
            if (feof($fpt)) {
                break;
            }
            $i++;
        }
        fclose($fpt);
        if ($i > 0) return false; else return true;
    }
    return true;
}

///////////////// �оݷ�αĶ������������֤�
function workingDayCheck($con, $date='')
{
    if (!$date) $date = date('Ymd');
    if (strlen($date) != 8) return false;
    if (!is_numeric($date)) return false;
    // ���Σ������饹������ �Ķ�����̤��ʤΤ�0���å�
    $i = 1; $workingDay = 0;
    $dateStart = sprintf(substr($date, 0, 6) . '%02d', $i);
    $con = db_connect();
    while ($dateStart <= $date) {
        $query = "
            SELECT bd_flg FROM company_calendar WHERE tdate='{$dateStart}'
        ";
        $bd_flg = 'f';
        getUniResTrs($con, $query, $bd_flg);
        if ($bd_flg == 't') {
            $workingDay++;
        }
        $i++;
        $dateStart = sprintf(substr($date, 0, 6) . '%02d', $i);
    }
    return $workingDay;
}

function checkControlFile($fpa, $ctl_file)
{
    $fp = fopen($ctl_file, 'r');
    $data = fgets($fp, 20);     // �¥쥳���ɤ�11�Х��ȤʤΤǤ���ä�;͵��
    if (feof($fp)) {
        return false;
    } else {
        $log_date = date('Y-m-d H:i:s');        ///// ��������
        fwrite($fpa, "$log_date ������ž���� : ����ü���� {$data}");
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
