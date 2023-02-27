<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ��ޥ������Υ᡼�륢�ɥ쥹 �Ȳ񡦥��ƥʥ�                          //
//                             Client interface  MVC Controller �� Main ��  //
// Copyright (C) 2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/11/15 Created   mailAddress_Main.php                                //
// 2007/06/15 view_file_name(__FILE__)���ɲ� (�����ȥ�����Υ��ɥ쥹)       //
//////////////////////////////////////////////////////////////////////////////
// ini_set('mbstring.http_output', 'UTF-8');           // ajax�ǻ��Ѥ�����
// ini_set('error_reporting', E_STRICT);               // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('error_reporting', E_ALL);                  // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');                     // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');        // zend 1.X ����ѥ� php4�θߴ��⡼��
// ob_start('ob_gzhandler');                           // ���ϥХåե���gzip����
// session_start();                                    // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

// require_once ('../function.php');                // access_log()���ǻ���
require_once ('../MenuHeader.php');                 // TNK ������ menu class
require_once ('../ControllerHTTP_Class.php');       // TNK ������ MVC Controller Class
// require_once ('../CalendarClass.php');           // �����������饹 �������塼��ǻ���
require_once ('mail/mailAddress_Controller.php');   // MVC �� Controller��
require_once ('mail/mailAddress_Model.php');        // MVC �� Model��
// access_log();                                       // Script Name �ϼ�ư����
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
echo view_file_name(__FILE__);

///// Main�� �� main()���
function main()
{
    ///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(0);                     // ǧ�ڥ����å� -1=ǧ�ڤʤ�, 0=���� 1=��Ĺ 2=��Ĺ�ʾ� 3=���ɥߥ�
    // �Ȳ�ϰ��̰ʾ��OK �Խ���2�ʾ�
    
    ////////////// ����������
    // $menu->set_site(INDEX_INDUST, 1);            // ����������ʤ�
    ////////////// �꥿���󥢥ɥ쥹����
    // $menu->set_RetUrl(EQUIP_MENU);               // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    
    //////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    // $menu->set_title('�Ұ��ޥ������Υ᡼�륢�ɥ쥹�Ȳ��Խ�');
    
    //////////// �ꥯ�����ȥ��֥������Ȥμ���
    $request = new Request();
    if ($request->get('condition') == '') $request->add('condition', 'genzai');
    
    //////////// �ꥶ��ȤΥ��󥹥�������
    $result = new Result();
    
    //////////// �ӥ��ͥ���ǥ����Υ��󥹥�������
    $model = new mailAddress_Model($request);
    
    //////////// ����ȥ��顼���Υ��󥹥�������
    $controller = new mailAddress_Controller($menu, $request, $result, $model);
    
    //////////// Client�ؽ���[show()]
    $controller->display($menu, $request, $result, $model);
}
main();

// ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
