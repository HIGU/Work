#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// �����ǡ����Σ���ʬ�� �� ����ʬ ��AS/400�ȥǡ�����󥯽���                //
// Copyright (C) 2005-2015 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/05/09 Created  industry_hourly_cli.php                              //
//            ȯ�������٥ե����롦ȯ��ײ�ե�����                        //
// 2005/05/11 ���ޥ�ɥ饤������Ѥ� echo ʸ�򥳥��ȥ�����                //
// 2005/05/30 order_data_daily_ftp_cli.php �ι����� deadlock�򵯤������Ե�  //
// 2005/08/11 ��Ҥ��ٶ����ʤ�¹Ԥ�����λ������å��ɲ�                  //
// 2006/05/08 home/www/ �� /home/www/ �˽���                                //
// 2007/02/19 ȯ��ײ�κ��ۥǡ��������������ɲ�(ȯ��Ĥ��������٤�̵�����)//
// 2007/02/20 sleep(1800)���ɲ�                                             //
// 2007/04/26 �嵭��sleep�ʲ��򳰤��ƹ���Ǽ�������ǡ�����Ʊ���¹Ԥ��ɲ�     //
// 2007/04/27 ����Ǽ��������Ʊ���򣲲�Ԥ��褦���ѹ�sleep(600)����ʬ��      //
// 2007/05/07 ������ž����μ�ư�������ɲá������ȼ���嵭��600��720(12ʬ)��//
// 2007/05/15 ��Ω�������������ʬ�μ�ư��Ͽ �ɲ�                           //
// 2007/05/16 ��Ω����������ʬ�μ�ư��Ͽ �ɲ�                               //
// 2007/05/22 ��Ω�����ײ�Ρ����������ײ�Υ����å��������ɲ�              //
// 2007/05/29 �嵭����Ω�����ײ����ǡ������åץ��ɤ�AS¦�����å����ѹ�  //
// 2010/11/08 �������μ�ư�������ɲ�                                 ��ë //
// 2010/11/12 �������μ�ư��������������ѹ��ΰ٥����Ȳ�         ��ë //
// 2015/03/20 ͭ��ľ���ι������ɲ�(����ʬ��20ʬ�ݤ����)             ��ë //
//            Cron��Ǽ¹Ԥ��ѹ�                                            //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��cli��)
// ini_set('display_errors','1');              // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ

require_once ('/home/www/html/tnk-web/tnk_func.php');
if (day_off(mktime())) exit;                // ��Ҥ��ٶ����ʤ齪λ
/////////// ����ǡ����Ƽ����Υ��ե��������Ȥ��������̾�����Ǥ�Ƽ����ѥ��˥ǡ���������뤿���
$log_name_a = '/tmp/industry_hourly_test.log';
$fpa = fopen($log_name_a, 'a');    // ���ƤΥ� w=���Υ���ä�
/////////// �ǡ����١����ȥ��ͥ�������Ω
if ( !($con = funcConnect()) ) {
    fwrite($fpa, "$log_date funcConnect() error \n");
    fclose($fpa);      ////// �����ѥ�����߽�λ
    exit;
}
fwrite($fpa, "************************************************************************\n");
fwrite($fpa, "cron 1���֤����μ¹� �ƥ��ȥ�\n");
fwrite($fpa, "/home/www/html/tnk-web/system/daily/industry_hourly_cli.php\n");
fwrite($fpa, "************************************************************************\n");

fwrite($fpa, "------------------------------------------------------------------------\n");

fclose($fpa);      ////// ����ǡ����Ƽ����Υ��ե�����������λ
// echo "------------------------------------------------------------------------\n";

sleep(50);  // order_data_daily_ftp_cli.php �ι����� deadlock�򵯤������Ե�������
// �嵭�ϸ��� order_process_lock �ե������ Exclusive ���Ƥ���Τǳ����Ƥ�OK

/******** ȯ�������٤Υơ��֥빹�� *********/
`/home/www/html/tnk-web/system/daily/order_process_ftp_cli.php`;
echo `/home/www/html/tnk-web/system/daily/order_process_ftp_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** ȯ��ײ�Υơ��֥빹�� *********/
`/home/www/html/tnk-web/system/daily/order_plan_get_ftp_cli.php`;
echo `/home/www/html/tnk-web/system/daily/order_plan_get_ftp_cli.php`;
echo "------------------------------------------------------------------------\n";


/******** ȯ��ײ�κ��ۥǡ������� *********/
/******** AS/400�ǥ����å��ѤߤΥǡ������餤����Ū�˹������� *********/
`/home/www/html/tnk-web/system/daily/order_plan_get_ftp2.php`;
echo `/home/www/html/tnk-web/system/daily/order_plan_get_ftp2.php`;
echo "------------------------------------------------------------------------\n";

/******** Web Server ���� �����å��ѥǡ������� *********/
`/home/www/html/tnk-web/system/daily/order_plan_checkDataUpLoad.php`;
echo `/home/www/html/tnk-web/system/daily/order_plan_checkDataUpLoad.php`;
echo "------------------------------------------------------------------------\n";

/******** ����Ǽ�������ǡ�����Ʊ���¹�(��¾���椢��) *********/
`/home/www/html/tnk-web/system/daily/order_delivery_answer_get_ftp.php`;
echo `/home/www/html/tnk-web/system/daily/order_delivery_answer_get_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** ������ž����μ�ư���� �¹�(��¾���椢��) *********/
`/home/www/html/tnk-web/system/equip_report/equip_report--as400-upload_cli.php`;
echo `/home/www/html/tnk-web/system/equip_report/equip_report--as400-upload_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** �������μ�ư���� �¹�(��¾���椢��) *********/
//`/home/www/html/tnk-web/system/material_cost/material_cost--as400-upload_cli.php`;
//echo `/home/www/html/tnk-web/system/material_cost/material_cost--as400-upload_cli.php`;
//echo "------------------------------------------------------------------------\n";

// /******** ��Ω�����ײ�����������ײ�Υ����å����� *********/
// `/home/www/html/tnk-web/system/assembly_schedule/assembly_schedule_checkUpdate.php`;
/******** ��Ω�����ײ�����������ײ�Υ����å��ǡ������åץ��ɤȥ�������ɸ�ι��� *********/
`/home/www/html/tnk-web/system/assembly_schedule/assembly_schedule_checkDataDownLoadUpdate.php`;
`/home/www/html/tnk-web/system/assembly_schedule/assembly_schedule_checkDataUpLoad.php`;
echo `/home/www/html/tnk-web/system/assembly_schedule/assembly_schedule_checkDataDownLoadUpdate.php`;
echo "------------------------------------------------------------------------\n";
echo `/home/www/html/tnk-web/system/assembly_schedule/assembly_schedule_checkDataUpLoad.php`;
echo "------------------------------------------------------------------------\n";

sleep(720);  // 12ʬ�ޤäƣ����ܤ�Ԥ� cron��32ʬ�ˣ�������

/******** ����Ǽ�������ǡ�����Ʊ���¹�(��¾���椢��) *********/
`/home/www/html/tnk-web/system/daily/order_delivery_answer_get_ftp.php`;
echo `/home/www/html/tnk-web/system/daily/order_delivery_answer_get_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** ������ž����μ�ư���� �¹�(��¾���椢��) *********/
`/home/www/html/tnk-web/system/equip_report/equip_report--as400-upload_cli.php`;
echo `/home/www/html/tnk-web/system/equip_report/equip_report--as400-upload_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** �������μ�ư���� �¹�(��¾���椢��) *********/
//`/home/www/html/tnk-web/system/material_cost/material_cost--as400-upload_cli.php`;
//echo `/home/www/html/tnk-web/system/material_cost/material_cost--as400-upload_cli.php`;
//echo "------------------------------------------------------------------------\n";

/******** ��Ω�������������ʬ�μ�ư��Ͽ *********/
echo `/home/www/html/tnk-web/system/assembly_completion/assembly_completion_history_ftp_cli.php`;
echo `/home/www/html/tnk-web/system/assembly_completion/assembly_completion_history_ftp_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** ��Ω����������ʬ�μ�ư��Ͽ *********/
echo `/home/www/html/tnk-web/system/assembly_time/assembly_time_header_ftp_cli.php`;
echo `/home/www/html/tnk-web/system/assembly_time/assembly_time_header_ftp_cli.php`;
echo "------------------------------------------------------------------------\n";

?>
