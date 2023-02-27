<?php
//////////////////////////////////////////////////////////////////////////////
// ����������ʬ���ѥ���պ�����˥塼  ����դ�������ɽ��                   //
// Copyright (C) 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/10/05 Created   graphCreate_Main.php                                //
// 2007/10/07 ����դ���ɽ������ɽ���ɲá�Y������(����)������(�̡�)���ɲ�   //
// 2007/10/13 X����ǯ���prot1��prot2�̡�������Ǥ��륪�ץ������ɲ�       //
// 2007/11/06 »�ץ���պ�����˥塼�������������պ�����˥塼�ز�¤      //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ WEB CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php');// TNK ������ MVC Controller Class
require_once ('graphCreate_Function.php');  // ����պ�����˥塼���Ѵؿ�
access_log();                               // Script Name �ϼ�ư����

////////////// main ��������
function main()
{
    ///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    
    ////////////// ����������
    $menu->set_site(INDEX_ACT, 15);              // site_index=(������˥塼) site_id=15(���񥰥��)999(̤��)
    ////////////// �꥿���󥢥ɥ쥹����
    // $menu->set_RetUrl(SALES_MENU);              // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    //////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $menu->set_title('�������� ʬ���� �����');
    //////////// �ƽ����action̾�ȥ��ɥ쥹����
    // $menu->set_action('���ץ����',   SALES . 'uriage_graph_all_niti.php');
    
    //////////// �ꥯ�����ȤΥ��󥹥��󥹤�����
    $request = new Request();
    //////////// ���å����Υ��󥹥��󥹤�����
    $session = new Session();
    //////////// �ꥶ��ȤΥ��󥹥��󥹤�����
    $result = new Result();
    
    //////////// �ᥤ�󥳥�ȥ��顼�μ¹�
    mainController($menu, $request, $session);
    
    //////////// �ƽФ����إǡ����ᤷ
    setReturnData($menu, $session);
    
    //////////// ����պ���
    graphCreate($session, $result);
    
    //////////// �������Υڡ������� �ǡ�������
    setPageData($session->get_local('yyyymm1'), 'yyyymm1', $result);
    setPageData($session->get_local('yyyymm2'), 'yyyymm2', $result);
    
    //////////// �֥饦�����Υ���å����к���
    $uniq = $menu->set_useNotCache('graphCreate');
    ///////////// HTML Header ����Ϥ��ƥ֥饦�����Υ���å��������
    $menu->out_html_header();
    //////////// �����ɽ��
    require_once ('graphCreate_View.php');
}
main();
ob_end_flush();                 // ���ϥХåե���gzip���� END
exit();
?>
