#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// �����ǡ����Σ�ʬ���AS/400�ȥǡ�����󥯽���                             //
// Copyright (C) 2007-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/08/29 Created  industry_five_minutes_cli.php                        //
//            system/daily/daily_allocated_cli.php ����DATA�ꥢ�륿���๹�� //
//              (system/daily/allocated_parts_realTime.php)                 //
//              (system/daily/allocated_parts_ftp2.php)                     //
//              (system/daily/allocated_parts_checkDataUpLoad.php)          //
//            industry/order/order_data_daily_ftp_cli.php ��ʸ��ȯ�ԥǡ���  //
//            system/daily/plan_get_ftp.php �����ײ�ե�����                //
//            emp/timepro/timePro_update_cli.php �����५���ɤΥǡ�������   //
// 2010/11/08 �Î����Ѥ��������μ�ư�������ɲá����                    ��ë //
// 2016/09/30 �Х��åȥޥ������Υǡ����������ɲ�                       ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��cli��)

require_once ('/home/www/html/tnk-web/tnk_func.php');
if (day_off(mktime())) exit;                // ��Ҥ��ٶ����ʤ齪λ

sleep(2);  // ���ʬ��

/******** �����ǡ����ꥢ�륿���๹�� *********/
`/home/www/html/tnk-web/system/daily/daily_allocated_cli.php`;
// echo `/home/www/html/tnk-web/system/daily/daily_allocated_cli.php`;
// echo "------------------------------------------------------------------------\n";

/******** ��ʸ��ȯ�ԥǡ������� *********/
`/home/www/html/tnk-web/industry/order/order_data_daily_ftp_cli.php`;
// echo `/home/www/html/tnk-web/industry/order/order_data_daily_ftp_cli.php`;
// echo "------------------------------------------------------------------------\n";

/******** �����ײ�ե����빹�� *********/
`/home/www/html/tnk-web/system/daily/plan_get_ftp.php`;
// echo `/home/www/html/tnk-web/system/daily/plan_get_ftp.php`;
// echo "------------------------------------------------------------------------\n";

/******** �����५���ɤΥǡ������� *********/
`/home/www/html/tnk-web/emp/timepro/timePro_update_cli.php`;
// echo `/home/www/html/tnk-web/emp/timepro/timePro_update_cli.php`;
// echo "------------------------------------------------------------------------\n";

/******** �Х��åȥޥ������Υǡ������� *********/
`/home/www/html/tnk-web/system/daily/daily_backet_ftp_cli.php`;
// echo `/home/www/html/tnk-web/system/daily/daily_backet_ftp_cli.php`;
// echo "------------------------------------------------------------------------\n";

?>
