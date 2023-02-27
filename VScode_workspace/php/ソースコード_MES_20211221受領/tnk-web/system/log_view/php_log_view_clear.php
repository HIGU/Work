<?php
//////////////////////////////////////////////////////////////////////////////
// php �Υ��顼��ɽ�������ꥢ                                             //
// Copyright(C) 2004-2007  Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/04/23 Created  php_log_view_clear.php                               //
// 2004/06/07 /tmp/php_error �ե����뤬̵���ä����ν������ɲ�             //
// 2004/07/25 MenuHeader class ����Ѥ��ƶ��̥�˥塼��ǧ���������ѹ�       //
//            iframe��php_error��apache error_log��apache access_log��ɽ��  //
// 2004/12/25 style='overflow:hidden;' (-xyξ��)���ɲ�                      //
// 2005/01/14 F2/F12��������뤿����б��� document.body.focus() ���ɲ�     //
// 2005/01/25 clear_access_log �ܥ�����ɲä����Υ��ƥʥ󥹤򤹤�       //
// 2005/12/10 E_ALL �� E_STRICT ���ѹ� access_log�Υե�����̾�ѹ�           //
// 2006/10/05 php5��UP�Τ��� =& new �� = new �� & ����                    //
// 2007/04/21 ��ƣ��Ҥ����Ѥ�ǧ�ڥ����å����ɲ�                            //
// 2007/07/13 ����ɽ�����å����ɲá������Х�ɽ������ؿ�ɽ����          //
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
    
    //////////// �ꥯ�����ȤΥ��󥹥��󥹤�����
    $request = new Request();
    //////////// �ꥶ��ȤΥ��󥹥��󥹤�����
    $result = new Result();
    ///// ����ȥ��顼����
    controller($menu, $request, $result);
    ///// ���饤����Ȥؽ���
    display($menu, $request, $result);
}

function controller($menu, $request, $result)
{
    //////////// php_error clear �ܥ��󤬲����줿��
    if ($request->get('clear_php') != '') {
        `/bin/cat /tmp/php_error >> /tmp/save_php_error.log`;
        `> /tmp/php_error`;
    }
    //////////// apache error clear �ܥ��󤬲����줿��
    if ($request->get('clear_apache') != '') {
        `/bin/cat /usr/local/apache2/logs/error_log >> /tmp/save_apache_error.log`;
        `> /usr/local/apache2/logs/error_log`;
    }
    //////////// access_log clear �ܥ��󤬲����줿��
    if ($request->get('clear_access_log') != '') {
        `/bin/cat /usr/local/apache2/logs/access_log >> /tmp/save_access_log`;
        `> /usr/local/apache2/logs/access_log`;
    }
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
    $result->add('php_flg', $php_flg);
}

function display($menu, $request, $result)
{
    // ����å����к�
    $uniq = uniqid('menu');
    /////////// HTML Header ����Ϥ��ƥ���å��������
    $menu->out_html_header();
    require_once ('php_log_view.php');
}

main();

?>
