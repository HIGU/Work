#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// �����ǡ����Σ�ʬ���AS/400�ȥǡ�����󥯽���                             //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/08/29 Created  industry_two_minutes_cli.php                         //
//            industry/order/order_data_difference_update_cron.php          //
//                                               ��ʸ��ȯ�ԥǡ�����ʬ�ι��� //
//            industry/order/order_data_truncation_update_cron.php          //
//                                             ��ʸ�����ڤ�ǡ�����ʬ�ι��� //
//            industry/order/order_data_bun_ftp_cli.php                     //
//                                     ���ա�������ʬǼ��ɼ(������ʸ��ȯ��) //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��cli��)

require_once ('/home/www/html/tnk-web/tnk_func.php');
if (day_off(mktime())) exit;                // ��Ҥ��ٶ����ʤ齪λ

sleep(1);  // ���ʬ��

/******** ��ʸ��ȯ�ԥǡ�����ʬ�ι��� *********/
`/home/www/html/tnk-web/industry/order/order_data_difference_update_cron.php`;
// echo `/home/www/html/tnk-web/industry/order/order_data_difference_update_cron.php`;
// echo "------------------------------------------------------------------------\n";

/******** ��ʸ�����ڤ�ǡ�����ʬ�ι��� *********/
`/home/www/html/tnk-web/industry/order/order_data_truncation_update_cron.php`;
// echo `/home/www/html/tnk-web/industry/order/order_data_truncation_update_cron.php`;
// echo "------------------------------------------------------------------------\n";

/******** ���ա�������ʬǼ��ɼ(������ʸ��ȯ��) ���� *********/
`/home/www/html/tnk-web/industry/order/order_data_bun_ftp_cli.php`;
// echo `/home/www/html/tnk-web/industry/order/order_data_bun_ftp_cli.php`;
// echo "------------------------------------------------------------------------\n";

?>
