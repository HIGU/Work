#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// Ĺ����α���ʤ����������ǯ/��ǯ/��ǯ���������ʤ��߸ˤˤʤäƤ���ʪ����� //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/31 Created  long_holding_parts_cli.php                           //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ(CLI�ǰʳ�)
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');            // �����ѥ�������
$log_name = '/tmp/nippo.log';
$fp = fopen($log_name, 'a+');               // ��

/////////// �ǡ����١����ȥ��ͥ�������Ω
if ( !($con = funcConnect()) ) {
    fwrite($fp, "$log_date funcConnect() error \n");
    fclose($fp);      ////// �����ѥ�����߽�λ
    exit;
}

///// ������̤��Ͽ�Υꥹ�Ȥ�DB��������������˳�Ǽ����
///// ����Υե�����ɤ� assy_no, plan_no, �����
$date1 = (date('Y') - 3) . date('md');  // 3ǯ��
$date2 = (date('Y') - 5) . date('md');  // 5ǯ��
$date3 = (date('Y') - 7) . date('md');  // 7ǯ��

///// ����ǡ������� INSERT INTO table SELECT ��� ��¹�
$regist1 = 0;
$regist2 = 0;
$regist3 = 0;
/////////// begin �ȥ�󥶥�����󳫻�
query_affected_trans($con, 'begin');
/////////// �����Υǡ�������
if (query_affected_trans($con, 'DELETE FROM long_holding_parts_work1') < 0) {   // �����ѥ����꡼�μ¹�
    query_affected_trans($con, 'rollback');         // �ȥ�󥶥������Υ���Хå�
    $log_date = date('Y-m-d H:i:s');    // �����ѥ�������
    fwrite($fp, "{$log_date} DELETE FROM long_holding_parts_work1 �˼��ԡ�\n");
    fclose($fp);      ////// �����ѥ�����߽�λ
    exit;
}
if (query_affected_trans($con, 'DELETE FROM long_holding_parts_work2') < 0) {   // �����ѥ����꡼�μ¹�
    query_affected_trans($con, 'rollback');         // �ȥ�󥶥������Υ���Хå�
    $log_date = date('Y-m-d H:i:s');    // �����ѥ�������
    fwrite($fp, "{$log_date} DELETE FROM long_holding_parts_work2 �˼��ԡ�\n");
    fclose($fp);      ////// �����ѥ�����߽�λ
    exit;
}
if (query_affected_trans($con, 'DELETE FROM long_holding_parts_work3') < 0) {   // �����ѥ����꡼�μ¹�
    query_affected_trans($con, 'rollback');         // �ȥ�󥶥������Υ���Хå�
    $log_date = date('Y-m-d H:i:s');    // �����ѥ�������
    fwrite($fp, "{$log_date} DELETE FROM long_holding_parts_work3 �˼��ԡ�\n");
    fclose($fp);      ////// �����ѥ�����߽�λ
    exit;
}
/////////// ����ʬ�ι���
$query = "
    INSERT INTO long_holding_parts_work1
    SELECT * FROM long_holding_parts({$date1}, '%', 0)
";
if ( ($regist1=query_affected_trans($con, $query)) < 0) {   // �����ѥ����꡼�μ¹�
    query_affected_trans($con, 'rollback');         // �ȥ�󥶥������Υ���Хå�
    $log_date = date('Y-m-d H:i:s');    // �����ѥ�������
    fwrite($fp, "{$log_date} INSERT INTO long_holding_parts_work1 SELECT �˼��ԡ�\n");
    fclose($fp);      ////// �����ѥ�����߽�λ
    exit;
}
$log_date = date('Y-m-d H:i:s');    // �����ѥ�������
fwrite($fp, "{$log_date} long_holding_parts_work1 �� {$regist1} �� ��ư��Ф��ޤ�����\n");

$query = "
    INSERT INTO long_holding_parts_work2
    SELECT * FROM long_holding_parts({$date2}, '%', 0)
";
if ( ($regist2=query_affected_trans($con, $query)) < 0) {   // �����ѥ����꡼�μ¹�
    query_affected_trans($con, 'rollback');         // �ȥ�󥶥������Υ���Хå�
    $log_date = date('Y-m-d H:i:s');    // �����ѥ�������
    fwrite($fp, "{$log_date} INSERT INTO long_holding_parts_work2 SELECT �˼��ԡ�\n");
    fclose($fp);      ////// �����ѥ�����߽�λ
    exit;
}
$log_date = date('Y-m-d H:i:s');    // �����ѥ�������
fwrite($fp, "{$log_date} long_holding_parts_work2 �� {$regist2} �� ��ư��Ф��ޤ�����\n");

$query = "
    INSERT INTO long_holding_parts_work3
    SELECT * FROM long_holding_parts({$date3}, '%', 0)
";
if ( ($regist3=query_affected_trans($con, $query)) < 0) {   // �����ѥ����꡼�μ¹�
    query_affected_trans($con, 'rollback');         // �ȥ�󥶥������Υ���Хå�
    $log_date = date('Y-m-d H:i:s');    // �����ѥ�������
    fwrite($fp, "{$log_date} INSERT INTO long_holding_parts_work3 SELECT �˼��ԡ�\n");
    fclose($fp);      ////// �����ѥ�����߽�λ
    exit;
}
$log_date = date('Y-m-d H:i:s');    // �����ѥ�������
fwrite($fp, "{$log_date} long_holding_parts_work3 �� {$regist3} �� ��ư��Ф��ޤ�����\n");

/////////// commit �ȥ�󥶥������Υ��ߥå�
query_affected_trans($con, 'commit');
fclose($fp);        ////// �����ѥ�����߽�λ
exit();


///// �ʲ��Ͼ���Τ���˻Ĥ�
if (rewind($fp)) {
    $to = 'tnksys@nitto-kohki.co.jp, usoumu@nitto-kohki.co.jp';
    $subject = "Ĺ����α�ʤμ�ư��з�� {$log_date}";
    $msg = fread($fp, filesize($log_name));
    $header = "From: tnksys@nitto-kohki.co.jp\r\nReply-To: tnksys@nitto-kohki.co.jp\r\n";
    mb_send_mail($to, $subject, $msg, $header);
}
?>
