#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ����(daily)����  (�ƥ�����ץȤ�require�����)                           //
// Copyright (C) 2004-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/10/15 Created  daily_cli.php                                        //
//            �����ƥ�ޥ����� �ã����ʥޥ����� ��������  �߸˷��򡦥ޥ�����//
// 2004/12/08 MICCC ���ɲ�                                                  //
// 2004/12/13 #!/usr/local/bin/php-5.0.2-cli �� php (������5.0.3RC2)���ѹ�  //
// 2005/02/07 �߸˷��򡦺߸˥ޥ�����������ʬ�ι��������ɲ�                  //
// 2009/12/28 ���ʥ��롼�ץޥ�������Ϣ�ι������ɲ�                     ��ë //
// 2010/01/14 ��Ω�����ޥ������μ�ư��Ͽ���ɲ�                         ��ë //
// 2010/01/19 �᡼���ʬ����䤹������١����ա�����������ɲ�       ��ë //
// 2011/11/17 ����ñ���μ�ư�������ɲ�                                 ��ë //
// 2013/01/29 ��Ŭ�����Ϣ���μ�ư�������ɲ�                         ��ë //
// 2015/02/13 ͭ���������μ�ư�������ɲ�                             ��ë //
// 2016/09/15 ��������ê����ۤι������ɲ�                             ��ë //
// 2017/06/14 A���ܺپ���daily_aden_details_cli.php���ɲ�              ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��cli��)
    // ���ߤ�CLI�Ǥ�default='1', SAPI�Ǥ�default='0'�ˤʤäƤ��롣CLI�ǤΤߥ�����ץȤ����ѹ�����롣
// ini_set('display_errors','1');              // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ
echo "************************************************************************\n";
echo "����(daily)����\n";
echo "/home/www/html/tnk-web/system/daily/daily_cli.php\n";
echo "************************************************************************\n";

echo "------------------------------------------------------------------------\n";

/******** �����ƥ�ޥ������ι��� *********/
echo "�����ƥ�ޥ������ι���\n";
echo "/home/www/html/tnk-web/system/daily/daily_miitem_cli.php\n";
echo `/home/www/html/tnk-web/system/daily/daily_miitem_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** �ã����ʣԣΣˣã����ʤΥơ��֥빹�� *********/
echo "�ã����ʣԣΣˣã����ʤΥơ��֥빹��\n";
echo "/home/www/html/tnk-web/system/daily/daily_miccc_ftp_cli.php\n";
echo `/home/www/html/tnk-web/system/daily/daily_miccc_ftp_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** ��������ι��� *********/
echo "��������ι���\n";
echo "/home/www/html/tnk-web/system/daily/daily_aden_cli.php\n";
echo `/home/www/html/tnk-web/system/daily/daily_aden_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** �����ܺپ���ι��� *********/
echo "�����ܺپ���ι���\n";
echo "/home/www/html/tnk-web/system/daily/daily_aden_details_cli.php\n";
echo `/home/www/html/tnk-web/system/daily/daily_aden_details_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** �߸˷��򡦺߸˥ޥ���������ʬ�ι���(�ĶȻ���18:00�ʹߤ��б�) *********/
echo "�߸˷��򡦺߸˥ޥ���������ʬ�ι���(�ĶȻ���18:00�ʹߤ��б�)\n";
echo "/home/www/html/tnk-web/system/daily/parts_stock_history_master_ftp_cli3.php\n";
echo `/home/www/html/tnk-web/system/daily/parts_stock_history_master_ftp_cli3.php`;
echo "------------------------------------------------------------------------\n";

/******** ��������ê����ۤι��� *********/
echo "��������ê����ۤι���\n";
echo "/home/www/html/tnk-web/system/daily/daily_stock_cli.php\n";
echo `/home/www/html/tnk-web/system/daily/daily_stock_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** ���ʥ��롼�ץ����ɤι��� *********/
echo "���ʥ��롼�ץ����ɤι���\n";
echo "/home/www/html/tnk-web/system/daily/product_code_get_ftp.php\n";
echo `/home/www/html/tnk-web/system/daily/product_code_get_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** ���ʥ��롼�ץ����ɥޥ������ι��� *********/
echo "���ʥ��롼�ץ����ɥޥ������ι���\n";
echo "/home/www/html/tnk-web/system/daily/product_code_master_get_ftp.php\n";
echo `/home/www/html/tnk-web/system/daily/product_code_master_get_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** ��Ω�����ޥ������μ�ư��Ͽ *********/
echo "��Ω�����ޥ������μ�ư��Ͽ\n";
echo "/home/www/html/tnk-web/system/assembly_time/assembly_process_master_cli_once.php\n";
echo `/home/www/html/tnk-web/system/assembly_time/assembly_process_master_cli_once.php`;
echo "------------------------------------------------------------------------\n";

/******** ����ñ���ι��� *********/
echo "����ñ���ι���\n";
echo "/home/www/html/tnk-web/system/daily/sales_price_update_cli.php\n";
echo `/home/www/html/tnk-web/system/daily/sales_price_update_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** ��Ŭ�����Ϣ���ι��� *********/
echo "��Ŭ�����Ϣ���ι���\n";
echo "/home/www/html/tnk-web/system/daily/claim_disposal_details_update_cli.php\n";
echo `/home/www/html/tnk-web/system/daily/claim_disposal_details_update_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** ñ���졼�ȶ�ʬ�ޥ������ι��� *********/
echo "ñ���졼�ȶ�ʬ�ޥ������ι���\n";
echo "/home/www/html/tnk-web/system/daily/parts_ratediv_master_update_ftp.php\n";
echo `/home/www/html/tnk-web/system/daily/parts_ratediv_master_update_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** ����ñ���졼�ȷ���ޥ������ι��� *********/
echo "����ñ���졼�ȷ���ޥ������ι���\n";
echo "/home/www/html/tnk-web/system/daily/parts_rate_history_update_ftp.php\n";
echo `/home/www/html/tnk-web/system/daily/parts_rate_history_update_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** ͭ��ٲ˼�������ι��� *********/
echo "ͭ��ٲ˼�������ι���\n";
echo "/home/www/html/tnk-web/system/daily/daily_yukyu_cli.php\n";
echo `/home/www/html/tnk-web/system/daily/daily_yukyu_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** ������ͽ����ݴ� *********/
echo "������ͽ����ݴ�\n";
echo "/home/www/html/tnk-web/system/daily/sales_actual_set_plan.php\n";
echo `/home/www/html/tnk-web/system/daily/sales_actual_set_plan.php`;
echo "------------------------------------------------------------------------\n";
?>
