<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�����κ�ȹ��� (��ꡦ��λ����) ������  Client interface ��          //
//                                              MVC Controller �� Main ��   //
// Copyright (C) 2005-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/30 Created   assembly_process_time_Main.php                      //
// 2005/10/21 Session Class����Ѥ� showMenu �ν���ͤ��������褦���ѹ�   //
// 2005/11/18 ���å�����group_no��¾�Υ�˥塼���Զ�礬�Ф뤿��DSgroup_no��//
//            �ܹԤ�����å����ɲá� Ʊ��������assembly_process_time.js   //
// 2006/05/19 set_action('��Ͽ�����Ȳ�' ���ɲ� �ײ����ϻ�����Ͽ�����Ȳ�     //
// 2007/03/24 material/allo_conf_parts_view.php ��                          //
//                           parts/allocate_config/allo_conf_parts_Main.php //
// 2007/06/18 tnk_func.php ��require ���ɲ� Uround()�λ��ѤΤ���            //
// 2016/12/09 set_action('��Ŭ������' ���ɲ� �����ֹ楯��å��ǸƤӽФ�   //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ WEB CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../../MenuHeader.php');               // TNK ������ menu class
require_once ('../../../ControllerHTTP_Class.php');     // TNK ������ MVC Controller Class
require_once ('../../../function.php');                 // access_log()���ǻ���
require_once ('../../../tnk_func.php');                 // day_off(), date_offset() �ǻ���
require_once ('assembly_process_time_Controller.php');  // MVC �� Controller��
require_once ('assembly_process_time_Model.php');       // MVC �� Model��
access_log();                               // Script Name �ϼ�ư����

///// Main���� main() function
function main()
{
    ///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    
    ////////////// ����������
    $menu->set_site(INDEX_INDUST, 3);           // site_index=INDEX_INDUST(������˥塼) site_id=3(��Ω�ؼ���˥塼)999(�����Ȥ򳫤�)
    ////////////// �꥿���󥢥ɥ쥹����
    // $menu->set_RetUrl(EQUIP_MENU);              // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    
    //////////// �ꥯ�����ȥ��֥������Ȥμ���
    $request = new Request();
    if ($request->get('group_no') != '') {
        setcookie('group_no', '', time() - 3600, '/');  // �쥯�å����ǡ�������(���������ˤ���)
        setcookie('DSgroup_no', $request->get('group_no'), time() + 630720000, '/');  // ��DSgroup_no��20ǯ��ͭ���ǥ��å�
    } elseif ($request->get('DSgroup_no') == '') {
        $request->add('group_no', '1');    // ����ͤ�����
    } else {
        $request->add('group_no', $request->get('DSgroup_no')); // �ǡ�����ܹԤ��� (�ǽ����ѷ���)
    }
    
    //////////// �ꥶ��ȤΥ��󥹥�������
    $result = new Result();
    
    //////////// ���å���� ���֥������Ȥμ��� (2005/10/21 Add)
    ///// ��˥塼������ showMenu �Υǡ��������å� �� ���� (Model��Controller�ǻ��Ѥ���)
    $session = new Session();
    $showMenu = $request->get('showMenu');
    if ($showMenu == '') {
        if ($session->get_local('showMenu') == '') {
            $showMenu = 'StartList';       // ���꤬�ʤ����ϰ���ɽ��ɽ��(�ä˽��)
        } else {
            $showMenu = $session->get_local('showMenu');
        }
    }
    $session->add_local('showMenu', $showMenu);
    $request->add('showMenu', $showMenu);
    
    //////////// �ӥ��ͥ���ǥ����Υ��󥹥�������
    $model = new AssemblyProcessTime_Model($request);
    
    //////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $title = "��Ω �ؼ� ��˥塼&nbsp;&nbsp;&nbsp;<span style='color:blue;'>" . $model->getGroupName($request->get('group_no')) . '</span>';
    $menu->set_title($title);
    //////////// �ƽ����action̾�ȥ��ɥ쥹����
    // $menu->set_action('��������ɽ',   INDUST . 'material/allo_conf_parts_view.php');
    $menu->set_action('��������ɽ',   INDUST . 'parts/allocate_config/allo_conf_parts_Main.php');
    $menu->set_action('��Ͽ�����Ȳ�',   INDUST . 'assembly/assembly_time_show/assembly_time_show_Main.php');
    $menu->set_action('��Ŭ������',   INDUST . 'custom_attention/claim_disposal_Main.php');
    
    //////////// ����ȥ��顼���Υ��󥹥�������
    $controller = new AssemblyProcessTime_Controller($menu, $request, $result, $model);
    
    //////////// Client�ؽ���[show()]
    $controller->display($menu, $request, $result, $model);
}
main();

ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
