#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ������� �ǡ��� ��ưFTP UPLOAD CLI��  (�ǿ���������������Ͽ�ѡ�      //
// Web Server (PHP) ----> AS/400                                            //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/11/12 Created   material_cost--as400-upload_cli_all.php             //
// 2010/11/24 ��Ω��57.00���ä��Τ�assy_rate���ѹ�                        //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ
// session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

$currentFullPathName = realpath(dirname(__FILE__));
require_once ("{$currentFullPathName}/../../function.php");
// access_log();                               // Script Name �ϼ�ư����

$log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
$fpa = fopen('/tmp/material_report.log', 'a'); ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�

// ��¾�����ѥ���ȥ���ե�����
define('AS_CTIGMP', 'UKWLIB/C#TIGMP');      // ������񥳥�ȥ���
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
define('C_TIGMP', "{$currentFullPathName}/backup/C#TIGMP.TXT"); // ������񥳥�ȥ���

// FTP�Υ�⡼�ȥե�����
define('REMOTE_F1', 'UKWLIB/TIGMOTP');      // AS/400�μ����ե����� ����
// ������α�ž���� ���� �ե�����
define('UPLOAD_F1', "{$currentFullPathName}/backup/material_cost_upload.log");         // ������� text�ǡ���

/////////// AS/400�ε�ǡ���������å�
if (!old_data_check($fpa, $currentFullPathName)) {
    $log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
    fwrite($fpa, "$log_date ������� �ε�ǡ����� AS/400 �˻ĤäƤ��ޤ��Τǽ�������ߤ��ޤ����� \n");
    fclose($fpa);   ///// �����ѥ��ե�����Υ�����
    exit();
}

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'BEGIN');
    query_affected_trans($con, 'LOCK material_cost_summary');    // ���ޥ꡼ �ơ��֥���å�����
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    fclose($fpa);   ///// �����ѥ��ե�����Υ�����
    exit();
}

$where = '';

///// �������� ���ޥ꡼ ���åץ����ѥơ��֥뤫��ǡ�������
$query = "
    SELECT
        u.assy_no                    AS �����ֹ�
        ,
        (SELECT plan_no FROM material_cost_header WHERE assy_no = u.assy_no ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                    AS ������ײ�
        ,
        (SELECT sum_price FROM material_cost_header WHERE assy_no = u.assy_no ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                    AS �ǿ��������
        ,
        (SELECT m_time FROM material_cost_header WHERE assy_no = u.assy_no ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS ���ȹ���
        ,
        (SELECT a_time FROM material_cost_header WHERE assy_no = u.assy_no ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS ��ư������
        ,        
        (SELECT g_time FROM material_cost_header WHERE assy_no = u.assy_no ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS ������
        ,
        Uround((SELECT assy_rate FROM material_cost_header WHERE assy_no = u.assy_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) * (SELECT m_time FROM material_cost_header WHERE assy_no = u.assy_no ORDER BY assy_no DESC, regdate DESC LIMIT 1), 2)                     AS ������Ω��
        ,
        Uround((SELECT a_rate FROM material_cost_header WHERE assy_no = u.assy_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) * (SELECT a_time FROM material_cost_header WHERE assy_no = u.assy_no ORDER BY assy_no DESC, regdate DESC LIMIT 1), 2)                     AS ��ư����Ω��
        ,
        Uround((SELECT assy_rate FROM material_cost_header WHERE assy_no = u.assy_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) * (SELECT g_time FROM material_cost_header WHERE assy_no = u.assy_no ORDER BY assy_no DESC, regdate DESC LIMIT 1), 2)                     AS ������Ω��
        ,
        '01111' AS ��Ω���
        ,
        'W'     AS �軻��ʬ
        ,
        (SELECT trim(substr(kanryou,3,4)) FROM assembly_schedule WHERE plan_no=(SELECT plan_no FROM material_cost_header WHERE assy_no = u.assy_no ORDER BY assy_no DESC, regdate DESC LIMIT 1))     AS ��������
        ,
        (SELECT to_char(regdate, 'YYMMDD') FROM material_cost_header WHERE assy_no = u.assy_no ORDER BY assy_no DESC, regdate DESC LIMIT 1)      AS �ɲ�����
        ,
        '20'     AS ����ǯ
        ,
        '20'     AS �ɲ�ǯ
    FROM
          material_cost_header AS u
    LEFT OUTER JOIN
          miitem AS m
    ON (u.assy_no = m.mipn)
    LEFT OUTER JOIN
          material_old_product AS mate
    ON (u.assy_no = mate.assy_no)
    WHERE mate.assy_no IS NULL AND (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE assy_no = u.assy_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NOT NULL
    AND trim(substr((SELECT plan_no FROM material_cost_header WHERE assy_no = u.assy_no ORDER BY assy_no DESC, regdate DESC LIMIT 1),1,1)) <> 'Z'
    GROUP BY u.assy_no, m.midsc
    ORDER BY u.assy_no ASC
    OFFSET 0 LIMIT 15000
";
//$query = "
//    SELECT
//        u.assyno                    AS �����ֹ�
//        ,
//        (SELECT plan_no FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
//                                    AS ������ײ�
//        ,
//        (SELECT sum_price FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
//                                    AS �ǿ��������
//        ,
//        (SELECT m_time FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS ���ȹ���
//        ,
//        (SELECT a_time FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS ��ư������
//        ,        
//        (SELECT g_time FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS ������
//        ,
//        Uround((SELECT assy_rate FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) * (SELECT m_time FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1), 2)                     AS ������Ω��
//        ,
//        Uround((SELECT a_rate FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) * (SELECT a_time FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1), 2)                     AS ��ư����Ω��
//        ,
//        Uround((SELECT assy_rate FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) * (SELECT g_time FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1), 2)                     AS ������Ω��
//        ,
//        '01111' AS ��Ω���
//        ,
//        'W'     AS �軻��ʬ
//        ,
//        (SELECT trim(substr(kanryou,3,4)) FROM assembly_schedule WHERE plan_no=(SELECT plan_no FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1))     AS ��������
//        ,
//        (SELECT to_char(regdate, 'YYMMDD') FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)      AS �ɲ�����
//        ,
//        '20'     AS ����ǯ
//        ,
//        '20'     AS �ɲ�ǯ
//    FROM
//          hiuuri AS u
//    LEFT OUTER JOIN
//          assembly_schedule AS a
//    ON (u.�ײ��ֹ� = a.plan_no)
//    LEFT OUTER JOIN
//          miitem AS m
//    ON (u.assyno = m.mipn)
//    LEFT OUTER JOIN
//          material_old_product AS mate
//    ON (u.assyno = mate.assy_no)
//    WHERE datatype='1' AND mate.assy_no IS NULL AND (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NOT NULL
//    AND trim(substr((SELECT plan_no FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1),1,1)) <> 'Z'
//    GROUP BY u.assyno, m.midsc
//    ORDER BY u.assyno ASC
//    OFFSET 0 LIMIT 15000
//";
$res = array();
if ( ($rows=getResultTrs($con, $query, $res)) < 1) {
    $log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
    fwrite($fpa, "$log_date ������� ���ޥ꡼�� ����ǡ���������ޤ��� \n");
    fclose($fpa);   ///// �����ѥ��ե�����Υ�����
    query_affected_trans($con, 'ROLLBACK');
    exit();
} else {
    $fp = fopen(UPLOAD_F1, 'w');     ///// ���� ���ޥ꡼ �ե�����ؤν���ߤǥ����ץ�
    for ($i=0; $i<$rows; $i++) {
        //if ($res[$i][16] != 'E') $res[$i][16] = ' ' ;   // ��λ�ե饰(end_flg)������å��������äƤ��ޤ��к�
        //$log_record = sprintf("%9s%8s%11.2f%7.3f%7.3f%7.3f%11.2f%11.2f%11.2f%5s%1s%4s%6s%2d%2d\n",
        //    $res[$i][0], $res[$i][1], $res[$i][2], $res[$i][3], $res[$i][4], $res[$i][5], $res[$i][6], $res[$i][7],
        //    $res[$i][8], $res[$i][9], $res[$i][10], $res[$i][11], $res[$i][12], $res[$i][13], $res[$i][14]
        //);
        $log_record = sprintf("%9s%8s%11s%10s%10s%10s%11s%11s%11s%5s%1s%4s%6s%2s%2s\n",
            $res[$i][0], $res[$i][1], $res[$i][2], $res[$i][3], $res[$i][4], $res[$i][5], $res[$i][6], $res[$i][7],
            $res[$i][8], $res[$i][9], $res[$i][10], $res[$i][11], $res[$i][12], $res[$i][13], $res[$i][14]
        );
        //$log_record = sprintf("%9s%8s\n",
        //    $res[$i][0], $res[$i][1]
        //);
        if (fwrite($fp, $log_record) == FALSE) {
            $log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
            fwrite($fpa, "$log_date ���ޥ꡼��UPLOAD�ǡ������� error \n");
            fclose($fpa);   ///// �����ѥ��ե�����Υ�����
            query_affected_trans($con, 'ROLLBACK');
            exit();
        }
    }
    //$sql = "
    //    INSERT INTO material_cost_summary_history
    //    SELECT * FROM material_cost_summary {$where}
    //    ;
    //    DELETE FROM material_cost_summary {$where}
    //";
    //query_affected_trans($con, $sql);
}
fclose($fp);   ///// ���ޥ꡼ �ե�����Υ�����
$log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
fwrite($fpa, "$log_date ������� ���ޥ꡼�ǡ��� $rows �� \n");



/////////// UPLOAD�ǡ������� OK
$log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
fwrite($fpa, "$log_date ������� ��UPLOAD�ǡ������� OK \n");



////////// FTP ž������
// OK NG �ե饰���å�
$ftp_flg = false;
// ������� ���٤ȥ��ޥ꡼������ǡ���¸�ߥ����å�
if (file_exists(UPLOAD_F1)) {
    // ���ͥ���������(FTP��³�Υ����ץ�)
    if ($ftp_stream = ftp_connect(AS400_HOST)) {
        if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
            ///// ����ȥ���ե�����Υ쥳���ɥ����å�����¾�����
            if (ftpGetCheckAndExecute($ftp_stream, C_TIGMP, AS_CTIGMP, FTP_ASCII)) {
                $log_date = date('Y-m-d H:i:s');        ///// ��������
                fwrite($fpa,"$log_date ftp_get ����ȥ��� download OK " . AS_CTIGMP . '��' . C_TIGMP . "\n");
                if (checkControlFile($fpa, C_TIGMP)) {
                    fwrite($fpa,"$log_date ����ȥ���ե�����˥ǡ���������ǽ�λ���ޤ���\n");
                    ftp_close($ftp_stream);
                    query_affected_trans($con, 'ROLLBACK');
                    fclose($fpa);      ////// ������λ
                    exit();
                }
            } else {
                $log_date = date('Y-m-d H:i:s');        ///// ��������
                fwrite($fpa,"$log_date ftp_get() ����ȥ��� error " . AS_CTIGMP . "\n");
                ftp_close($ftp_stream);
                query_affected_trans($con, 'ROLLBACK');
                fclose($fpa);      ////// ������λ
                exit();
            }
            ///// ������� ���٥ǡ�����UPLOAD����
            if (ftpPutCheckAndExecute($ftp_stream, REMOTE_F1, UPLOAD_F1, FTP_ASCII)) {
                $log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
                fwrite($fpa,"$log_date ������� ���� ftp_put upload OK " . UPLOAD_F1 . '��' . REMOTE_F1 . "\n");
                $ftp_flg = true;
            } else {
                $log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
                fwrite($fpa,"$log_date ������� ���� ftp_put() upload error " . REMOTE_F1 . "\n");
            }
            ///// ������� ���ޥ꡼�ǡ�����UPLOAD����
            //if (ftpPutCheckAndExecute($ftp_stream, REMOTE_F2, UPLOAD_F2, FTP_ASCII)) {
            //    $log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
            //    fwrite($fpa,"$log_date ������� ���ޥ꡼ ftp_put upload OK " . UPLOAD_F2 . '��' . REMOTE_F2 . "\n");
            //    if ($ftp_flg) $ftp_flg = true; else $ftp_flg = false;
            //} else {
            //    $log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
            //    fwrite($fpa,"$log_date ������� ���ޥ꡼ ftp_put() upload error " . REMOTE_F2 . "\n");
            //    $ftp_flg = false;
            //}
        } else {
            $log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
            fwrite($fpa,"$log_date ������� ftp_login() error \n");
        }
        ftp_close($ftp_stream);
    } else {
        $log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
        fwrite($fpa,"$log_date ������� ftp_connect() error\n");
    }
} else {
    $log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
    fwrite($fpa,"$log_date ������� ���٤�����ޤ���\n");
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
    define('OLD_DATA', "{$dir}/backup/material_cost_download.txt");   // save file
    
    // ���ͥ���������(FTP��³�Υ����ץ�)
    if ($ftp_stream = ftp_connect(AS400_HOST)) {
        if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
            ///// ȯ��ײ�ե�����
            if (ftpGetCheckAndExecute($ftp_stream, OLD_DATA, REMOTE_F1, FTP_ASCII)) {
                fwrite($fpa,"$log_date ��ǡ��������å��� download OK " . REMOTE_F1 . '��' . OLD_DATA . "\n");
            } else {
                fwrite($fpa,"$log_date ftp_get() error " . REMOTE_F1 . "\n");
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
        fwrite($fpa, "$log_date ������� : ����ü���� {$data}");
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
