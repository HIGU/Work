#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ���ʥޥ�������Ϣ ��缫ưFTP Download ������˥塼��ư������             //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2009 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed histoy                                                           //
// 2009/12/25 Created  product_master_get_ftp.php                           //
// 2009/12/28 �ƥ����Ѥ˰���ƽ�����ѹ������ᤷ��                     ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
// ini_set('max_execution_time', 1200);    // ����¹Ի���=20ʬ
require_once ('/home/www/html/tnk-web/function.php');

echo "------------------------------------------------------------------------\n";

/******** ���ʥ��롼�ץ����ɤι��� *********/
echo `/home/www/html/tnk-web/system/daily/product_code_get_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** ���ʥ��롼�ץ����ɥޥ������ι��� *********/
echo `/home/www/html/tnk-web/system/daily/product_code_master_get_ftp.php`;
echo "------------------------------------------------------------------------\n";

?>
