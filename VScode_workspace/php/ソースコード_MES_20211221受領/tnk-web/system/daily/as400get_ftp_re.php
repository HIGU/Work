#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
//      �쥿���� #!/usr/local/bin/php-4.3.8-cgi -q                          //
// ����ǡ��� ��ưFTP Download  cron �ǽ�����       AS400������ν�����     //
// Web����ī��ư�� as400get_ftp.php��inventory_average_summary_ftp_cli.php  //
// �ڤ�daily_cli.php��¹Ԥ��Ƥ��뤬AS�����꤬���ä�����ư�Ǽ¹Ԥ���      //
// ɬ���������˼¹Ԥ��뤳�ȡ�AS�ˤ�����ʬ�Υ���ե����뤷���ʤ��١�       //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2009-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2009/12/18 Created  as400get_ftp_re.php(as400get_ftp.php)                //
// 2009/12/25 ���ʥ��롼�ץޥ�������Ϣ�ι������ɲ�                          //
// 2010/01/14 ��Ω�����ޥ������μ�ư��Ͽ���ɲ�                              //
// 2010/01/19 �᡼���ʬ����䤹������١������ȥ������ɲ�             ��ë //
// 2011/07/15 »��ͽ¬�μ�ư�׻��������ɲ�                             ��ë //
// 2011/07/19 »��ͽ¬�Υ�󥯤��ְ�äƤ����Τ�����                   ��ë //
// 2015/03/12 daily_cli.php�μ¹Ԥ���                                     //
//            daily_cli.php���̥�˥塼�Ǽ¹�                               //
// 2020/08/18 �������μ�ư��Ͽ�򤷤ʤ��褦�˽���                     ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
// ini_set('max_execution_time', 1200);    // ����¹Ի���=20ʬ
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');            // �����ѥ�������
$log_name_a = '/tmp/as400get_ftp_re.log';
$fpa = fopen($log_name_a, 'w+');    // ���ƤΥ� w=���Υ���ä�

fwrite($fpa, "************************************************************************\n");
fwrite($fpa, "����ǡ��� �Ƽ��� Download\n");
fwrite($fpa, "/home/www/html/tnk-web/system/daily/as400get_ftp_re.php\n");
fwrite($fpa, "************************************************************************\n");

/////////// �ǡ����١����ȥ��ͥ�������Ω
if ( !($con = funcConnect()) ) {
    fwrite($fpa, "$log_date funcConnect() error \n");
    fclose($fpa);      ////// �����ѥ�����߽�λ
    exit;
}

fwrite($fpa, "------------------------------------------------------------------------\n");

echo "************************************************************************\n";
echo "����ǡ��� �Ƽ��� Download\n";
echo "/home/www/html/tnk-web/system/daily/as400get_ftp_re.php\n";
echo "************************************************************************\n";

echo "------------------------------------------------------------------------\n";

/******** ���ǡ����ι���(����ʬ)�����̤�����ǡ��� *********/
echo `/home/www/html/tnk-web/system/daily/sales_get_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** �����ײ�ǡ����ι��� *********/
echo `/home/www/html/tnk-web/system/daily/plan_get_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** ñ������ǡ����ι��� *********/
echo `/home/www/html/tnk-web/system/daily/parts_cost_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** �������ʥǡ����ι��� *********/
echo `/home/www/html/tnk-web/system/daily/allocated_parts_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** ��¤������ɽ �����ǡ����ι��� *********/
echo `/home/www/html/tnk-web/system/daily/parts_configuration_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** ���ؼ��Υإå��������٥ǡ����ι��� *********/
echo `/home/www/html/tnk-web/system/daily/equip_work_inst_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** ȯ���ɸ�๩���ǡ����ι��� *********/
echo "ȯ��ɸ�๩���ǡ����ι���\n";
echo `/home/www/html/tnk-web/system/daily/order_process_ftp_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** ��ʸ��ȯ�ԥǡ����ι��� *********/
echo `/home/www/html/tnk-web/system/daily/order_data_ftp_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** �������μ�ư��Ͽ *********/
/*
echo "�������μ�ư��Ͽ\n";
echo `/home/www/html/tnk-web/industry/material/material_auto_registry_cli.php`;
echo "------------------------------------------------------------------------\n";
*/

/******** �������κ�����μ�ư��Ͽ *********/
echo "�������κ�����μ�ư��Ͽ\n";
echo `/home/www/html/tnk-web/system/daily/parts_material_auto_registry_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** ����Ǽ�������ǡ�����Ʊ���¹�(��¾���椢��) *********/
`/home/www/html/tnk-web/system/daily/order_delivery_answer_get_ftp.php`;

/******** ��Ω�������������ʬ�μ�ư��Ͽ�ڤ�AS/400¦���ꥢ������ *********/
echo "��Ω��������μ�ư��Ͽ�ڤ�AS/400¦���ꥢ������\n";
echo `/home/www/html/tnk-web/system/assembly_completion/assembly_completion_history_ftp_cli_once.php`;
echo "------------------------------------------------------------------------\n";

/******** ��Ω����������ʬ�μ�ư��Ͽ�ڤ�AS/400¦���ꥢ������ *********/
echo "��Ω�����μ�ư��Ͽ�ڤ�AS/400¦���ꥢ������\n";
echo `/home/www/html/tnk-web/system/assembly_time/assembly_time_header_ftp_cli_once.php`;
echo "------------------------------------------------------------------------\n";

/******** ���߸˥��ޥ꡼�μ�ư�������� *********/
echo "���߸˥��ޥ꡼�μ�ư���������¹�\n";
echo `/home/www/html/tnk-web/system/inventory_average/inventory_average_summary_ftp_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** »��ͽ¬�μ�ư�׻����� *********/
echo "»��ͽ¬�μ�ư�׻������¹�\n";
echo `/home/www/html/tnk-web/system/daily/profit_loss_estimate_cal.php`;
echo "------------------------------------------------------------------------\n";

/******** ����(daily)����  *********/
//echo "����(daily)����\n";
//echo `/home/www/html/tnk-web/system/daily/daily_cli.php`;
//echo "------------------------------------------------------------------------\n";

/******** �᡼������  *********/
if (rewind($fpa)) {
    $to = 'tnksys@nitto-kohki.co.jp, usoumu@nitto-kohki.co.jp, norihisa_ooya@nitto-kohki.co.jp';
    $subject = "����ǡ����κƼ������ {$log_date}";
    $msg = fread($fpa, filesize($log_name_a));
    $header = "From: tnksys@nitto-kohki.co.jp\r\nReply-To: tnksys@nitto-kohki.co.jp\r\n";
    mb_send_mail($to, $subject, $msg, $header);
}

fclose($fpa);      ////// ����ǡ����Ƽ����ѥ�����߽�λ
?>
