<?php
//////////////////////////////////////////////////////////////////////////////
// »�� ͽ¬ ��˥塼                                                       //
// Copyright (C) 2011-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2011/07/12 Created  profit_loss_estimate_menu.php                        //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����

require_once ('../../function.php');           // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');           // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ��� menu_bar()�ǻ���
require_once ('../../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(10, 999);                   // site_index=10(»�ץ�˥塼) site_id=999(�����Ȥ򳫤�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(MENU);                 // �̾�ϻ��ꤹ��ɬ�פϤʤ�(�ȥåץ�˥塼)
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('»��ͽ¬��˥塼');
//////////// ɽ�������
$menu->set_caption('»��ͽ¬�ξȲ�');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('»��ͽ¬�Ȳ�����',   PL . '/pl_estimate/profit_loss_estimate_Main.php');
$menu->set_action('»��ͽ¬�Ȳ�',   PL . '/pl_estimate/profit_loss_estimate_view_Main.php');

//////////////// �ƥ��󥫡����ѿ��ǥ��åȤ��� �ؿ�������Υ����С��إåɤ򣱲�ǺѤޤ��뤿��
$uniq = uniqid('menu');

unset($_SESSION['act_offset']);     // ���祳���ɥơ��֥�ǻ��Ѥ���offset�ͤ���
unset($_SESSION['cd_offset']);      // �����ɥơ��֥�ǻ��Ѥ���offset�ͤ���

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ�������� 
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
-->
</style>
</head>
<body onLoad='document.mhForm.backwardStack.focus()' style='overflow:hidden;'>
    <center>
<?php echo $menu->out_title_border()?>
        
        <table width='100%'>
            <tr>
                <td align='center'><img src='<?php echo IMG ?>t_nitto_logo2.gif' width=348 height=83></td>
            </tr>
            <tr>
                <td align='center' class='caption_font'>
                    <?php echo $menu->out_caption(), "\n" ?>
                </td>
            </tr>
        </table>
        
        <br>
        
        <table width='70%' border='0'> <!-- width�Ǵֳ֤�Ĵ�� -->
        <tr>
            <!-- /////////////// left view ////////////// -->
        <td align='center' valign='top'>
            <table border='0'>
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('»��ͽ¬�Ȳ�')?>'>
                        <td align='center'>
                            <input type='image' alt='�»�פ�ͽ¬�Ȳ�(�Ȳ�Τ�)' border=0 src='<?php echo menu_bar('../menu_tmp/menu_item_pl_estimate_view.png', ' »�� ͽ¬ �Ȳ�', 14) . "?id=$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self()?>'>
                        <td align='center'>
                            <input type='image' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self()?>'>
                        <td align='center'>
                            <input type='image' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self()?>'>
                        <td align='center'>
                            <input type='image' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self()?>'>
                        <td align='center'>
                            <input type='image' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self()?>'>
                        <td align='center'>
                            <input type='image' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <!--
                <tr>
                    <form method='post' action='<?php echo $menu->out_self()?>'>
                        <td align='center'>
                            <input type='image' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                -->
                
            </table>
        </td>
            <!-- /////////////// right view ////////////// -->
        <td align='center' valign='top'>
            <table border='0'>
                <tr>
                    <form method='post' action='<?php echo $menu->out_self()?>'>
                        <td align='center'>
                            <input type='image' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self()?>'>
                        <td align='center'>
                            <input type='image' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self()?>'>
                        <td align='center'>
                            <input type='image' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self()?>'>
                        <td align='center'>
                            <input type='image' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self()?>'>
                        <td align='center'>
                            <input type='image' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('»��ͽ¬�Ȳ�����')?>'>
                        <td align='center'>
                            <input type='image' alt='�»�פ�ͽ¬�Ȳ�(���ٷ׻�)' border=0 src='<?php echo menu_bar('../menu_tmp/menu_item_pl_estimate_once_view.png', ' »�� ͽ¬ �Ȳ�(����)', 14) . "?id=$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
            </table>
        </td>
        </tr>
        </table>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
