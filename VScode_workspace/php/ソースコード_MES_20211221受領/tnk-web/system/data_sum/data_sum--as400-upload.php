<?php
//////////////////////////////////////////////////////////////////////////////
// �ǡ������������ǡ��� ��ưFTP UPLOAD HTTP/CGI��                         //
// Web Server (PHP) ----> AS/400                                            //
// Copyright (C) 2004-2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// �ѹ�����                                                                 //
// 2004/06/14 �������� data_sum--as400-upload.php                           //
// 2007/04/11 AS/400�˵�ǡ��������뤫�����å�����ؿ�old_data_check()�ɲ�  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
ini_set('max_execution_time', 1200);    // ����¹Ի���=20ʬ
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('/home/www/html/tnk-web/function.php');
access_log();                           // Script Name �ϼ�ư����

$log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
$fpa = fopen('/tmp/data_sum.log', 'a');     ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�

// FTP�Υ�⡼�ȥե�����
define('REMOTE_F', 'NITTO/TGDTSMP');            // AS/400�μ����ե�����
// ������Υ��ꥸ�ʥ�ե�����
define('ORIGIN_F', 'backup/data_sum_nippo.log');     // �ǡ������������ǡ���
// FTP�Υ�����ե�����
define('LOCAL_F', 'backup/data_sum_upload.log');    // ��͡����Υ�����ե�����

/////////// AS/400�ε�ǡ���������å�
if (!old_data_check($fpa)) {
    $log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
    fwrite($fpa, "$log_date �ǡ������� �ε�ǡ����� AS/400 �˻ĤäƤ��ޤ��Τǽ�������ߤ��ޤ����� \n");
    $_SESSION['s_sysmsg'] = "<span style='color:yellow;'>�ǡ������� �ε�ǡ����� AS/400 �˻ĤäƤ��ޤ��Τǽ�������ߤ��ޤ�����</span><br>";
    fclose($fpa);   ///// �����ѥ��ե�����Υ�����
    header('Location: ' . H_WEB_HOST . SYS_MENU);
    exit();
}

// ����Υ��åץ����ѤΥե����������å�
if (file_exists(LOCAL_F)) {
    unlink(LOCAL_F);        // ����Υǡ�������
}

// �ǡ������������ǡ���¸�ߥ����å�
if (file_exists(ORIGIN_F)) {
    // �ǡ������������ե�������͡��ह��
    if (rename(ORIGIN_F, LOCAL_F)) {
        // ���ͥ���������(FTP��³�Υ����ץ�)
        if ($ftp_stream = ftp_connect(AS400_HOST)) {
            if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
                ///// �ǡ������������ǡ�����UPLOAD����
                if (ftp_put($ftp_stream, REMOTE_F, LOCAL_F, FTP_ASCII)) {
                    $_SESSION['s_sysmsg'] = "<font color='white'>ftp_put upload OK " . LOCAL_F . '��' . REMOTE_F . '</font><br>';
                    fwrite($fpa,"$log_date ftp_put upload OK " . LOCAL_F . '��' . REMOTE_F . "\n");
                } else {
                    $_SESSION['s_sysmsg'] = 'ftp_put() upload error ' . REMOTE_F;
                    fwrite($fpa,"$log_date ftp_put() upload error " . REMOTE_F . "\n");
                }
            } else {
                $_SESSION['s_sysmsg'] = 'DATA SUM ftp_login() error ';
                fwrite($fpa,"$log_date DATA SUM ftp_login() error \n");
            }
            ftp_close($ftp_stream);
        } else {
            $_SESSION['s_sysmsg'] = 'DATA SUM ftp_connect() error';
            fwrite($fpa,"$log_date DATA SUM ftp_connect() error\n");
        }
    } else {
        $_SESSION['s_sysmsg'] = 'DATA SUM rename() Error';
        fwrite($fpa,"$log_date DATA SUM rename() Error\n");
    }
} else {
    $_SESSION['s_sysmsg'] = 'DATA SUM ����ե����뤬����ޤ���';
    fwrite($fpa,"$log_date DATA SUM ����ե����뤬����ޤ���\n");
}
fclose($fpa);   ///// �����ѥ��ե�����Υ�����

header('Location: ' . H_WEB_HOST . SYS_MENU);



///////////////// AS/400 �˵�ǡ��������뤫�����å�����
function old_data_check($fpa)
{
    $log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
    // FTP�Υ������åȥե����� �嵭�ǻ��ꤵ��Ƥ���
    // ��¸��Υǥ��쥯�ȥ�ȥե�����̾
    define('OLD_DATA', 'backup/dataSum_download.txt');    // save file
    
    // ���ͥ���������(FTP��³�Υ����ץ�)
    if ($ftp_stream = ftp_connect(AS400_HOST)) {
        if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
            ///// ȯ��ײ�ե�����
            if (ftp_get($ftp_stream, OLD_DATA, REMOTE_F, FTP_ASCII)) {
                fwrite($fpa, "$log_date ftp_get download OK " . REMOTE_F . '��' . OLD_DATA . "\n");
            } else {
                fwrite($fpa, "$log_date ftp_get() error " . REMOTE_F . "\n");
                return false;
            }
        } else {
            fwrite($fpa, "$log_date ftp_login() error \n");
            return false;
        }
        ftp_close($ftp_stream);
    } else {
        fwrite($fpa, "$log_date ftp_connect() error -->\n");
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
?>
