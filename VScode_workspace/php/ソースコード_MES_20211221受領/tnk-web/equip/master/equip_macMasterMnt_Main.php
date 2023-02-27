<?php
//////////////////////////////////////////////////////////////////////////////
// �����������ޥ����� �� �Ȳ� �� ���ƥʥ�                               //
//              MVC Controller �� Main ��                                   //
// Copyright (C) 2002-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/08/19 Created   equip_macMasterMnt_Main.php                         //
// 2002/08/08 register_globals = Off �б�                                   //
// 2003/06/17 servey(�ƻ�ե饰) Y/N ���ѹ��Ǥ��ʤ��Զ����� �ڤ�        //
//              �����ϥե������ץ�����󼰤��ѹ�                          //
// 2003/06/19 $uniq = uniqid('script')���ɲä��� JavaScript File��ɬ���ɤ�  //
// 2004/03/04 ���ǥơ��֥� equip_machine_master2 �ؤ��б�                   //
// 2004/07/12 Netmoni & FWS ���������� �����å����� ���Τ��� Net&FWS�����ɲ�//
//            CSV ������������ �ƻ������� ����̾�ѹ�                        //
// 2005/02/14 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2005/06/24 �ǥ��쥯�ȥ��ѹ� equip/ �� equip/master/                      //
// 2005/06/28 MVC��Controller�����ѹ�  equip_macMasterMnt_Controller.php    //
// 2005/08/18 �ڡ�������ǡ�����ComTableMntClass�ذܹԤ��ƥ��ץ��벽        //
// 2005/08/19 Controller��Class����Main Controller ����                   //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', '1');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ WEB CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../equip_function.php');             // �����ط��ζ��� function
require_once ('../../MenuHeader.php');              // TNK ������ menu class
require_once ('../EquipControllerHTTP.php');        // �����ط��˳�ĥ���� MVC Controller Class
require_once ('equip_macMasterMnt_Controller.php'); // MVC �� Controller��
require_once ('equip_macMasterMnt_Model.php');      // MVC �� ��ǥ���
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(40, 25);                    // site_index=40(������˥塼) site_id=25(�����ޥ�����)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(EQUIP_MENU);              // �̾�ϻ��ꤹ��ɬ�פϤʤ�

//////////// �ꥯ�����ȥ��֥������Ȥμ���
$request = new Request();
//////////// �ꥶ��ȤΥ��󥹥�������
$result = new Result();
//////////// ���å����Υ��󥹥�������
$session = new equipSession();

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title("�����ޥ����� ���ƥʥ�&nbsp;&nbsp;{$session->getFactName()}");
//////////// ɽ�������

//////////// �ӥ��ͥ���ǥ����Υ��󥹥�������
$model = new EquipMacMstMnt_Model($session->get('factory'), $request);

//////////// ����ȥ��顼���Υ��󥹥�������
$controller = new EquipMacMstMnt_Controller($menu, $request, $result, $model, $session);

//////////// ���̽���
// $controller->display();

ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
