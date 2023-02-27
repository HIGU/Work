<?php
//////////////////////////////////////////////////////////////////////////////
// php �Υ��顼��ɽ������ư���ꥢ                                         //
// Copyright(C) 2020-2020  Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// http://masterst/system/log_view/php_log_view_clear_hand.php��¹�        //
// Changed history                                                          //
// 2020/09/15 Created  php_log_view_clear_hand.php                          //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);           // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('error_reporting', E_ALL);              // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('display_errors', '1');                 // Error ɽ�� ON debug �� ��꡼���女����
session_start();                                // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
ob_start('ob_gzhandler');                       // ���ϥХåե���gzip����

require_once ('../../function.php');            // TNK ������ function
require_once ('../../MenuHeader.php');          // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php');// TNK ������ MVC Controller Class
access_log();                                   // Script Name ��ư����

function main()
{
    ///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    if ($_SESSION['User_ID'] == '300161') {     // ��ƣ��Ҥ���ξ��ϥƥ��ȴĶ�������Τǰ��̥桼������
        $menu = new MenuHeader(0);              // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    } else {
        $menu = new MenuHeader(3);              // ǧ�ڥ����å�3=admin�ʾ� �����=TOP_MENU �����ȥ�̤����
    }
    
    ////////////// ����������
    $menu->set_site(99, 41);                    // site_index=99(�����ƥ������˥塼) site_id=41(�������å�)
    ////////////// �꥿���󥢥ɥ쥹����
    // $menu->set_RetUrl(SYS_MENU);
    //////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $menu->set_title('Administrator php apache log check');
    //////////// ɽ�������
    $menu->set_caption('php apache log view');
    //////////// �ƽ����action̾�ȥ��ɥ쥹����
    // $menu->set_action('action_name', SYS. 'script_name.php');
}

    //////////// php_error clear �ܥ��󤬲����줿��
        `/bin/cat /tmp/php_error >> /tmp/save_php_error.log`;
        `> /tmp/php_error`;
    //////////// apache error clear �ܥ��󤬲����줿��
        `/bin/cat /usr/local/apache2/logs/error_log >> /tmp/save_apache_error.log`;
        `> /usr/local/apache2/logs/error_log`;
    //////////// access_log clear �ܥ��󤬲����줿��
        `/bin/cat /usr/local/apache2/logs/access_log >> /tmp/save_access_log`;
        `> /usr/local/apache2/logs/access_log`;
    //////////// php ���ǡ�����ͭ��̵������
    $php = '/tmp/php_error';
    if (file_exists($php)) {
        $php_error_log = `/bin/cat $php`;
        if ($php_error_log == '') {
            $php_flg = false;
        } else {
            $php_flg = true;
        }
    } else {
        $php_flg = false;
    }


?>
