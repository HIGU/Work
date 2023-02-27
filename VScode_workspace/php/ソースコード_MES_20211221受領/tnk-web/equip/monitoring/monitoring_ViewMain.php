<?php
////////////////////////////////////////////////////////////////////////////////
// ������Ư�����ؼ����ƥʥ�                                               //
//                                               MVC View �� �ꥹ��ɽ��(Main) //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/03/24 Created monitoring_ViewMain.php                                 //
// 2021/03/24 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
require_once ('../../function.php');    // TNK ������ function
require_once ('../../MenuHeader.php');  // TNK ������ menu class

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();               // ǧ�ڥ����å���ԤäƤ���

//////////// �ե졼��θƽ���Υ��������(frame)̾�ȥ��ɥ쥹����
$menu->set_frame('Header', EQUIP2 . 'monitoring/monitoring_ViewHeader.php');
$menu->set_frame('List'  , EQUIP2 . 'monitoring/monitoring_ViewList.php');
// �ե졼���Ǥ� $menu->set_action()�ǤϤʤ�$menu->set_frame()����Ѥ���

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" 
    "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<title><?php echo $menu->out_title() ?></title>
</head>

<frameset rows='155,*'>
    <frame src= '<?= $menu->out_frame('Header'), "?{$_SERVER['QUERY_STRING']}", "&mode=Header" ?>' name='Header' scrolling='no'>
    <frame src= '<?= $menu->out_frame('List'), "?{$_SERVER['QUERY_STRING']}" ?>' name='List'>
</frameset>
</html>
<?php ob_end_flush();   // ���ϥХåե���gzip���� END ?>
