<?php
//////////////////////////////////////////////////////////////////////////////
// ������ư���������ƥ�ι��������˥塼                                   //
// Copyright (C) 2004-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/09/27 Created   equip_factory_select.php                            //
// 2004/12/25 style='overflow:hidden;' (-xyξ��)���ɲ�                      //
// 2005/01/14 F2/F12������ͭ���������б��Τ��� document.body.focus()���ɲ�  //
// 2005/08/02 �ƥ�˥塼�֤�<br>�쥤�����Ȥ�<div>&nbsp;</div>���ѹ�NN�б�   //
// 2006/03/14 equip_menu.css ����                                         //
// 2018/05/18 ��������ɲá������ɣ�����Ͽ����Ū��7���ѹ�            ��ë //
// 2018/12/25 �������﫤�SUS��ʬΥ���塹�ΰ١�                      ��ë //
//            ������ȣ������ɽ�����塹�ΰ١�                              //
// 2021/06/22 �����ΰ١���ö����������򥳥��Ȳ���                   ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����

require_once ('equip_function.php');        // �����ط����� (������function.php��ƽФ��Ƥ���)
require_once ('../tnk_func.php');           // menu_bar() �ǻ���
require_once ('../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0, TOP_MENU);        // ǧ�ڥ����å�0=���̰ʾ� �����=''TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(40, 0);                     // site_index=40(������˥塼2) site_id=0(site�򳫤��ʤ�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(TOP_MENU);
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('������ư���������ƥ� ��˥塼');

//////////// ɽ�������
$menu->set_caption('�������� ��˥塼');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('equip_menu', EQUIP_MENU2);
$menu->set_action('equip_menu_moni', EQUIP2.'equip_menu_moni.php');

//////////////// �ƥ��󥫡����ѿ��ǥ��åȤ��� �ؿ�������Υ����С��إåɤ򣱲�ǺѤޤ��뤿��
$uniq = uniqid('menu');

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_css() ?>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ�������� -->
<link rel='stylesheet' href='equip_menu.css?<?php echo $uniq ?>' type='text/css' media='screen'>

<script type='text/javascript'>
<!--
function set_focus()
{
    // document.body.focus();   // F2/F12������ͭ���������б�
    // document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
}
// -->
</script>
</head>
<body style='overflow:hidden;' onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        
        <table width='100%'>
            <tr>
                <td align='center'><img src='<?= IMG ?>t_nitto_logo2.gif' width=348 height=83></td>
            </tr>
            <tr>
                <td align='center' class='caption_font'>
                    <?= $menu->out_caption(), "\n" ?>
                </td>
            </tr>
        </table>
        
        <br>
        
        <table width='70%' border='0'> <!-- width�Ǵֳ֤�Ĵ�� -->
        <tr>
            <!-- /////////////// left view ////////////// -->
        <td align='center' valign='top'>
            <table border='0'>
                <!--
                <tr>
                    <form method='post' action='<?= $menu->out_action('equip_menu'), '?factory=4' ?>'>
                        <td align='center'>
                            <input type='image' alt='���������� ���������ƥ� ������ ��˥塼' border=0
                            src='<?php echo menu_bar('menu_tmp/menu_item_equip_menu4f.png', '  �� �� ������', 14) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                -->
                <tr>
                    <form method='post' action='<?= $menu->out_action('equip_menu_moni'), '?factory=6' ?>'>
                        <td align='center'>
                            <input type='image' alt='���������� ���������ƥ� ������ ��˥塼' border=0
                            src='<?php echo menu_bar('menu_tmp/menu_item_equip_menu6f.png', '  �� �� ������', 14) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                <tr>
                    <form method='post' action='<?= $menu->out_action('equip_menu'), '?factory=7' ?>'>
                        <td align='center'>
                            <input type='image' alt='���������� ���������ƥ� ������(���) ��˥塼' border=0
                            src='<?php echo menu_bar('menu_tmp/menu_item_equip_menu7f1.png', '  �� �� ������(���)', 13) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                <!--
                <tr>
                    <form method='post' action='<?= $menu->out_action('equip_menu'), '?factory=' ?>'>
                        <td align='center'>
                            <input type='image' alt='���������� ���������ƥ� ������ ��˥塼' border=0
                            src='<?php echo menu_bar('menu_tmp/menu_item_equip_menu.png', '  �� �� ������', 14) . "?$uniq" ?>'>
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
                <!--
                <tr>
                    <form method='post' action='<?= $menu->out_action('equip_menu'), '?factory=5' ?>'>
                        <td align='center'>
                            <input type='image' alt='���������� ���������ƥ� ������ ��˥塼' border=0
                            src='<?php echo menu_bar('menu_tmp/menu_item_equip_menu5f.png', '  �� �� ������', 14) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                -->
                <tr>
                    <form method='post' action='<?= $menu->out_self() ?>'>
                        <td align='center'>
                            <input type='image' name='post' alt='���Υ����ƥ�' border=0
                            src='<?= IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?= $menu->out_action('equip_menu'), '?factory=8' ?>'>
                        <td align='center'>
                            <input type='image' alt='���������� ���������ƥ� ������(SUS) ��˥塼' border=0
                            src='<?php echo menu_bar('menu_tmp/menu_item_equip_menu7f2.png', '  �� �� ������(SUS)', 13) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                <!--
                <tr>
                    <form method='post' action='<?= $menu->out_self() ?>'>
                        <td align='center'>
                            <input type='image' name='post' alt='���Υ����ƥ�' border=0
                            src='<?= IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                -->
            </table>
        </td>
        </tr>
        </table>
    </center>
</body>
</html>
<?= $menu->out_site_java() ?>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
