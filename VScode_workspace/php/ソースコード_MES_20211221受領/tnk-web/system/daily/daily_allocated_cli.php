#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// �����ǡ����Υꥢ�륿���๹������ ����(daily)����                         //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/02/13 Created  daily_allocated_cli.php                              //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��cli��)
    // ���ߤ�CLI�Ǥ�default='1', SAPI�Ǥ�default='0'�ˤʤäƤ��롣CLI�ǤΤߥ�����ץȤ����ѹ�����롣
// ini_set('display_errors','1');              // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ

$flag = substr(date('i'), 1, 1);            // crond �ǣ�ʬ��ߤˤ������
    //echo "------------------------------------------------------------------------\n";
if ($flag == '0') {     // 00ʬ/10/20/30...
    /******** �ȥ�󥶥������ե����� W#TIALLC�ι��� *********/
    echo `/home/www/html/tnk-web/system/daily/allocated_parts_realTime.php`;
    //echo "------------------------------------------------------------------------\n";
} else {                // 05ʬ/15/25/35...
    /******** AS/400�ǥ����å��ѤߤΥǡ������餤����Ū�˹������� *********/
    echo `/home/www/html/tnk-web/system/daily/allocated_parts_ftp2.php`;
    //echo "------------------------------------------------------------------------\n";
    
    /******** Web Server ���� �����å��ѥǡ������� *********/
    echo `/home/www/html/tnk-web/system/daily/allocated_parts_checkDataUpLoad.php`;
    //echo "------------------------------------------------------------------------\n";
}
?>
