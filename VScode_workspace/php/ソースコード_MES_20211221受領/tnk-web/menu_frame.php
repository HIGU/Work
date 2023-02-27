<?php
//////////////////////////////////////////////////////////////////////////////
// �������칩�� �ȥåץե졼������                                          //
// Copyright (C) 2002-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// History                                                                  //
// 2002/08/26 Created   menu_frame.php                                      //
// 2002/08/26 ���å����������ɲ� & register_globals = Off �б�            //
// 2002/09/21 cols="18%,*" �� 16% �� �ѹ� 800 X 600 �б�                    //
// 2003/11/11 cols="16%,*" �� 14% �� �ѹ� »��ɽ���򹭤����̤Ǹ��뤿��      //
// 2003/11/15 cols="14%,*" �� 12% �� �ѹ� menu_site.php��font��11pt��9pt    //
// 2003/12/15 cols="12%,*" �� 10% �� �ѹ� site_view �� On/Off �����ɲ�      //
// 2004/07/21 <title>�����Ѥ���Ⱦ�Ѥ�TNK Web System���ѹ� ������ʸ�����ѹ�//
// 2005/08/31 base_class ����Ѥ����饤����ȤΥ�����ɥ����֤���¸����     //
// 2005/09/05 setWinOpen()�᥽�åɤ��ɲ� menuOn/Off�����å��ˤ��Unload�б� //
//            onLoad='menu.setWinOpen()'���б�                              //
// 2005/09/07 site_menu On/Off�Τ���� noSwitch�ե饰�ɲ�(base_class.js����)//
// 2005/09/13 siteɽ���ν���ͤ�$_SESSION['site_view'] = 'on' �� 'off'���ѹ�//
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
// ini_set('display_errors', '1');         // Error ɽ�� ON debug �� ��꡼���女����
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('function.php');          // TNK Web ������function
require_once ('MenuHeader.php');        // TNK ������ menu class
access_log();                           // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);              // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
if (!isset($_REQUEST['name'])) {        // site_menu On/Off �Ǥʤ����
    $menu->set_site(0, 0);              // site_index=0(̤����) site_id=0(̤����)
}
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('TNK Web System');

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

////////////// �¹ԥ�����ץ�̾�μ���
if ( isset($_REQUEST['name']) ) {
    $exec_name = $_REQUEST['name'];
} else {
    $exec_name = TOP_MENU;  // �����
}

////////////// ɽ���� On Off ���ꡦ����
if (!isset($_SESSION['site_view'])) $_SESSION['site_view'] = 'off';  // �����
///// name �� noSwitch ��ե饰�˻ȤäƤ��뤳�Ȥ����
if ( (isset($_REQUEST['name'])) && (!isset($_REQUEST['noSwitch'])) ) {
    if ($_SESSION['site_view'] == 'on') {
        $_SESSION['site_view'] = 'off';
        $frame_cols = '0%,*';
    } else {
        $_SESSION['site_view'] = 'on';
        $frame_cols = '10%,*';
    }
} else {
    if ($_SESSION['site_view'] == 'on') {
        $frame_cols = '10%,*';
    } else {
        $frame_cols = '0%,*';
    }
}

////////////// �꥿���󥢥ɥ쥹�ѤΥ���ǥå����ν����
$_SESSION['ScriptStack'] = 0;         // Start��0���� menu.php�� Stack=1�Ȥʤ�

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
    "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><%=$menu->out_title()%></title>
<%=$menu->out_jsBaseClass()%>
<script type='text/javascript' src='menu_frame.js?id=<%=$uniq%>'></script>
</head>
<frameset name='topFrame' cols='<%=$frame_cols%>' border='0' onUnload='menu.win_close()' onLoad='menu.setWinOpen(); menu.siteMenuView();' onHelp='return false'>
    <frame src='menu_site.php' name='menu_site' scrolling='no'>
    <frame src='<%=$exec_name%>' name='application'>
</frameset>
<noframes>
<p>�������칩��(��)��Web�����ȤǤϥե졼���Ȥ�����ˤʤäƤ��ޤ���</p>
<p>�ե졼�����Ѥ��ʤ�����ˤ��Ƥ�������ѹ����Ʋ�������</p>
<p>̤�б��Υ֥饦�����ξ����б��֥饦�������ѹ����Ʋ�������<p>
</noframes>
</html>
